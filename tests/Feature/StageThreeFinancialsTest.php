<?php

namespace Tests\Feature;

use App\Livewire\Cashbook;
use App\Livewire\Dashboard;
use App\Models\Expense;
use App\Models\MonthlyBalance;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Support\Financials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StageThreeFinancialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_starting_balances_are_saved_with_a_previous_closing_snapshot(): void
    {
        Financials::saveStartingBalances('2026-10', 100000, 500000);
        $this->recordRentPayment('Cash', 25000, '2026-10-10', '2026-10');

        Financials::saveStartingBalances('2026-11', 125000, 500000);

        $balance = MonthlyBalance::query()->where('month', '2026-11')->firstOrFail();

        $this->assertSame('125000.00', $balance->cash_starting);
        $this->assertSame('500000.00', $balance->bank_starting);
        $this->assertFalse($balance->cash_manual);
        $this->assertFalse($balance->bank_manual);
        $this->assertSame('125000.00', $balance->cash_previous_closing);
        $this->assertSame('500000.00', $balance->bank_previous_closing);

        Financials::saveStartingBalances('2026-11', 130000, 490000);

        $summary = Financials::summary('2026-11');

        $this->assertSame(130000.0, $summary['starting_cash']);
        $this->assertSame(490000.0, $summary['starting_bank']);
        $this->assertSame(125000.0, $summary['previous_closing_cash']);
        $this->assertSame(500000.0, $summary['previous_closing_bank']);
        $this->assertSame(5000.0, $summary['cash_difference']);
        $this->assertSame(-10000.0, $summary['bank_difference']);
        $this->assertTrue($summary['cash_manual']);
        $this->assertTrue($summary['bank_manual']);

        $balance->refresh();

        $this->assertTrue($balance->cash_manual);
        $this->assertTrue($balance->bank_manual);
        $this->assertSame('125000.00', $balance->cash_previous_closing);
        $this->assertSame('500000.00', $balance->bank_previous_closing);
    }

    public function test_monthly_balances_roll_forward_from_the_previous_closing_balance(): void
    {
        Financials::saveStartingBalances('2026-10', 100000, 500000);
        $this->recordRentPayment('Cash', 25000, '2026-10-10', '2026-10');
        $this->recordRentPayment('Bank', 15000, '2026-10-11', '2026-10');

        Expense::factory()->create([
            'expense_date' => '2026-10-12',
            'amount' => 5000,
            'paid_from' => 'Cash',
        ]);
        Expense::factory()->create([
            'expense_date' => '2026-10-13',
            'amount' => 2000,
            'paid_from' => 'Bank',
        ]);

        $this->assertNull(MonthlyBalance::query()->where('month', '2026-11')->first());

        $summary = Financials::summary('2026-11');

        $this->assertSame(120000.0, $summary['starting_cash']);
        $this->assertSame(513000.0, $summary['starting_bank']);
        $this->assertSame(633000.0, $summary['starting_total']);
        $this->assertSame(120000.0, $summary['closing_cash']);
        $this->assertSame(513000.0, $summary['closing_bank']);
        $this->assertSame(0.0, $summary['cash_difference']);
        $this->assertSame(0.0, $summary['bank_difference']);
    }

    public function test_rent_payments_increase_the_selected_account_in_their_payment_month(): void
    {
        Financials::saveStartingBalances('2026-11', 1000, 2000);

        $this->recordRentPayment('Cash', 5000, '2026-11-10', '2026-11');
        $this->recordRentPayment('Bank', 7000, '2026-11-11', '2026-10');
        $this->recordRentPayment('Cash', 9000, '2026-12-01', '2026-11');

        $november = Financials::summary('2026-11');

        $this->assertSame(5000.0, $november['cash_rent']);
        $this->assertSame(7000.0, $november['bank_rent']);
        $this->assertSame(12000.0, $november['rent_received']);
        $this->assertSame(6000.0, $november['closing_cash']);
        $this->assertSame(9000.0, $november['closing_bank']);
        $this->assertSame(15000.0, $november['closing_total']);

        $december = Financials::summary('2026-12');

        $this->assertSame(6000.0, $december['starting_cash']);
        $this->assertSame(9000.0, $december['starting_bank']);
        $this->assertSame(9000.0, $december['cash_rent']);
        $this->assertSame(15000.0, $december['closing_cash']);
    }

    public function test_cash_and_bank_expenses_reduce_their_own_accounts(): void
    {
        Financials::saveStartingBalances('2026-11', 10000, 20000);

        Expense::factory()->create([
            'expense_date' => '2026-11-03',
            'amount' => 1500.25,
            'paid_from' => 'Cash',
        ]);
        Expense::factory()->create([
            'expense_date' => '2026-11-04',
            'amount' => 2000.75,
            'paid_from' => 'Bank',
        ]);

        $summary = Financials::summary('2026-11');

        $this->assertSame(1500.25, $summary['cash_expenses']);
        $this->assertSame(2000.75, $summary['bank_expenses']);
        $this->assertSame(3501.0, $summary['expenses']);
        $this->assertSame(8499.75, $summary['closing_cash']);
        $this->assertSame(17999.25, $summary['closing_bank']);
        $this->assertSame(26499.0, $summary['closing_total']);
        $this->assertSame(26499.0, round($summary['closing_cash'] + $summary['closing_bank'], 2));
    }

    public function test_backdated_transactions_affect_only_their_own_financial_month(): void
    {
        Financials::saveStartingBalances('2026-11', 10000, 20000);

        $this->recordRentPayment('Cash', 5000, '2026-10-20', '2026-11');
        Expense::factory()->create([
            'expense_date' => '2026-10-21',
            'amount' => 1000,
            'paid_from' => 'Bank',
        ]);

        $november = Financials::summary('2026-11');

        $this->assertSame(10000.0, $november['starting_cash']);
        $this->assertSame(20000.0, $november['starting_bank']);
        $this->assertSame(0.0, $november['rent_received']);
        $this->assertSame(0.0, $november['expenses']);

        $october = Financials::summary('2026-10');

        $this->assertSame(5000.0, $october['cash_rent']);
        $this->assertSame(1000.0, $october['bank_expenses']);
        $this->assertSame(5000.0, $october['closing_cash']);
        $this->assertSame(-1000.0, $october['closing_bank']);
    }

    public function test_combined_closing_balance_equals_cash_plus_bank(): void
    {
        Financials::saveStartingBalances('2026-11', 100000, 500000);

        $this->recordRentPayment('Cash', 20000, '2026-11-05', '2026-11');
        $this->recordRentPayment('Bank', 30000, '2026-11-06', '2026-11');

        Expense::factory()->create([
            'expense_date' => '2026-11-07',
            'amount' => 5000,
            'paid_from' => 'Cash',
        ]);
        Expense::factory()->create([
            'expense_date' => '2026-11-08',
            'amount' => 10000,
            'paid_from' => 'Bank',
        ]);

        $summary = Financials::summary('2026-11');

        $this->assertSame(115000.0, $summary['closing_cash']);
        $this->assertSame(520000.0, $summary['closing_bank']);
        $this->assertSame(635000.0, $summary['closing_total']);
        $this->assertSame(635000.0, round($summary['closing_cash'] + $summary['closing_bank'], 2));
    }

    public function test_expenses_are_listed_chronologically_for_the_selected_month(): void
    {
        Expense::factory()->create([
            'expense_date' => '2026-11-20',
            'description' => 'Later expense',
        ]);
        Expense::factory()->create([
            'expense_date' => '2026-11-02',
            'description' => 'Earlier expense',
        ]);
        Expense::factory()->create([
            'expense_date' => '2026-10-15',
            'description' => 'Other month',
        ]);

        $expenses = Financials::expensesForMonth('2026-11');

        $this->assertSame(['Earlier expense', 'Later expense'], $expenses->pluck('description')->all());
    }

    public function test_cashbook_creates_an_expense_and_updates_balances(): void
    {
        Financials::saveStartingBalances('2026-11', 10000, 20000);

        Livewire::test(Cashbook::class)
            ->call('openExpense')
            ->set('expenseForm.date', '2026-11-08')
            ->set('expenseForm.description', 'Plumbing repair')
            ->set('expenseForm.amount', '1250.50')
            ->set('expenseForm.paid_from', 'Cash')
            ->call('saveExpense')
            ->assertHasNoErrors()
            ->assertSet('showExpenseModal', false)
            ->assertSee('Plumbing repair');

        $expense = Expense::query()->firstOrFail();

        $this->assertSame('Plumbing repair', $expense->description);
        $this->assertSame('1250.50', $expense->amount);
        $this->assertSame('Cash', $expense->paid_from);
        $this->assertSame('2026-11-08', $expense->expense_date->toDateString());

        $summary = Financials::summary('2026-11');

        $this->assertSame(8749.5, $summary['closing_cash']);
        $this->assertSame(20000.0, $summary['closing_bank']);
    }

    public function test_cashbook_editing_an_expense_recalculates_balances(): void
    {
        Financials::saveStartingBalances('2026-11', 10000, 20000);

        $expense = Expense::factory()->create([
            'expense_date' => '2026-11-05',
            'description' => 'Cleaning',
            'amount' => 1000,
            'paid_from' => 'Cash',
        ]);

        Livewire::test(Cashbook::class)
            ->call('editExpense', $expense->id)
            ->assertSet('expenseForm.description', 'Cleaning')
            ->set('expenseForm.amount', '2500')
            ->set('expenseForm.paid_from', 'Bank')
            ->call('saveExpense')
            ->assertHasNoErrors();

        $expense->refresh();

        $this->assertSame('2500.00', $expense->amount);
        $this->assertSame('Bank', $expense->paid_from);

        $summary = Financials::summary('2026-11');

        $this->assertSame(10000.0, $summary['closing_cash']);
        $this->assertSame(17500.0, $summary['closing_bank']);
    }

    public function test_cashbook_deleting_an_expense_recalculates_balances(): void
    {
        Financials::saveStartingBalances('2026-11', 10000, 20000);

        $expense = Expense::factory()->create([
            'expense_date' => '2026-11-05',
            'amount' => 1500,
            'paid_from' => 'Cash',
        ]);

        Livewire::test(Cashbook::class)
            ->call('confirmDelete', $expense->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteExpense')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);

        $summary = Financials::summary('2026-11');

        $this->assertSame(10000.0, $summary['closing_cash']);
        $this->assertSame(20000.0, $summary['closing_bank']);
    }

    public function test_adjusting_an_earlier_month_preserves_later_starting_balances(): void
    {
        Financials::saveStartingBalances('2026-10', 100000, 500000);
        Financials::saveStartingBalances('2026-11', 100000, 500000);

        Financials::saveStartingBalances('2026-10', 90000, 500000);

        $november = Financials::summary('2026-11');

        $this->assertSame(100000.0, $november['starting_cash']);
        $this->assertSame(90000.0, $november['previous_closing_cash']);
        $this->assertSame(10000.0, $november['cash_difference']);
        $this->assertTrue($november['cash_manual']);

        $balance = MonthlyBalance::query()->where('month', '2026-11')->firstOrFail();

        $this->assertSame('100000.00', $balance->cash_starting);
        $this->assertSame('100000.00', $balance->cash_previous_closing);
    }

    public function test_dashboard_saves_starting_balances_and_shows_the_manual_difference(): void
    {
        Financials::saveStartingBalances('2026-10', 100000, 500000);
        $this->recordRentPayment('Cash', 10000, '2026-10-10', '2026-10');

        Livewire::test(Dashboard::class)
            ->call('openBalances')
            ->assertSet('balanceForm.cash', 110000)
            ->assertSet('balanceForm.bank', 500000)
            ->set('balanceForm.cash', '120000')
            ->assertSee('Manual adjustment')
            ->assertSee('Cash is higher by')
            ->call('saveBalances')
            ->assertSet('showBalancesModal', false)
            ->assertSee('Starting balances saved for November 2026.');

        $summary = Financials::summary('2026-11');

        $this->assertSame(120000.0, $summary['starting_cash']);
        $this->assertSame(10000.0, $summary['cash_difference']);
        $this->assertTrue($summary['cash_manual']);
    }

    private function recordRentPayment(string $method, float $amount, string $paidOn, string $billingMonth): RentPayment
    {
        $bill = RentBill::factory()->create([
            'billing_month' => $billingMonth,
            'house_rent' => $amount,
            'electricity' => 0,
            'gas' => 0,
            'parking' => 0,
            'service_charge' => 0,
            'other_charges' => 0,
        ]);

        return RentPayment::factory()->for($bill)->create([
            'amount' => $amount,
            'method' => $method,
            'paid_on' => $paidOn,
        ]);
    }
}
