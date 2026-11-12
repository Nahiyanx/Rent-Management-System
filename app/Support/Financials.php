<?php

namespace App\Support;

use App\Models\Expense;
use App\Models\MonthlyBalance;
use App\Models\RentBill;
use App\Models\RentPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The single source of truth for monthly cash and bank balances.
 *
 * Rent payments are recognised in the month of their actual paid_on date, not
 * the month they were billed for. Expenses are recognised on their expense date.
 * A month without a saved starting balance rolls forward from the previous
 * month's closing balance.
 */
final class Financials
{
    /**
     * @return array{
     *     month: string,
     *     starting_cash: float,
     *     starting_bank: float,
     *     starting_total: float,
     *     cash_rent: float,
     *     bank_rent: float,
     *     rent_received: float,
     *     cash_expenses: float,
     *     bank_expenses: float,
     *     expenses: float,
     *     closing_cash: float,
     *     closing_bank: float,
     *     closing_total: float,
     *     net_movement: float,
     *     cash_manual: bool,
     *     bank_manual: bool,
     *     previous_closing_cash: float,
     *     previous_closing_bank: float,
     *     previous_closing_total: float,
     *     cash_difference: float,
     *     bank_difference: float,
     *     billed_total: float,
     *     outstanding: float,
     *     settled_bills: int,
     *     total_bills: int,
     *     collection_progress: int
     * }
     */
    public static function summary(string $month): array
    {
        $memo = [];

        return self::buildSummary($month, self::earliestMonth(), $memo);
    }

    /**
     * @return array{cash: float, bank: float, total: float}
     */
    public static function previousClosing(string $month): array
    {
        $previousMonth = Carbon::createFromFormat('Y-m', $month)
            ->startOfMonth()
            ->subMonthNoOverflow()
            ->format('Y-m');

        $earliest = self::earliestMonth();

        if ($earliest === null || $previousMonth < $earliest) {
            return ['cash' => 0.0, 'bank' => 0.0, 'total' => 0.0];
        }

        $memo = [];
        $previous = self::buildSummary($previousMonth, $earliest, $memo);

        return [
            'cash' => $previous['closing_cash'],
            'bank' => $previous['closing_bank'],
            'total' => $previous['closing_total'],
        ];
    }

    public static function saveStartingBalances(string $month, float $cash, float $bank): MonthlyBalance
    {
        $previous = self::previousClosing($month);
        $cash = round($cash, 2);
        $bank = round($bank, 2);

        return DB::transaction(function () use ($month, $cash, $bank, $previous): MonthlyBalance {
            return MonthlyBalance::query()->updateOrCreate(
                ['month' => $month],
                [
                    'cash_starting' => $cash,
                    'bank_starting' => $bank,
                    'cash_manual' => abs(round($cash - $previous['cash'], 2)) > 0.0,
                    'bank_manual' => abs(round($bank - $previous['bank'], 2)) > 0.0,
                    'cash_previous_closing' => $previous['cash'],
                    'bank_previous_closing' => $previous['bank'],
                ],
            );
        });
    }

    /**
     * @return Collection<int, Expense>
     */
    public static function expensesForMonth(string $month): Collection
    {
        [$start, $end] = self::monthBounds($month);

        return Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, array<string, mixed>>  $memo
     * @return array<string, mixed>
     */
    private static function buildSummary(string $month, ?string $earliest, array &$memo): array
    {
        if (isset($memo[$month])) {
            return $memo[$month];
        }

        [$start, $end] = self::monthBounds($month);
        $previous = self::previousClosingWithin($month, $earliest, $memo);

        $stored = MonthlyBalance::query()->where('month', $month)->first();

        $startingCash = $stored !== null ? round((float) $stored->cash_starting, 2) : $previous['cash'];
        $startingBank = $stored !== null ? round((float) $stored->bank_starting, 2) : $previous['bank'];

        $rentByMethod = RentPayment::query()
            ->whereBetween('paid_on', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $expensesByMethod = Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('paid_from, SUM(amount) as total')
            ->groupBy('paid_from')
            ->pluck('total', 'paid_from');

        $cashRent = round((float) ($rentByMethod['Cash'] ?? 0), 2);
        $bankRent = round((float) ($rentByMethod['Bank'] ?? 0), 2);
        $cashExpenses = round((float) ($expensesByMethod['Cash'] ?? 0), 2);
        $bankExpenses = round((float) ($expensesByMethod['Bank'] ?? 0), 2);

        $closingCash = round($startingCash + $cashRent - $cashExpenses, 2);
        $closingBank = round($startingBank + $bankRent - $bankExpenses, 2);

        $bills = RentBill::query()
            ->where('billing_month', $month)
            ->withSum('payments', 'amount')
            ->get(['id', 'total_due']);

        $billedTotal = round((float) $bills->sum('total_due'), 2);
        $billedPaid = round((float) $bills->sum(
            static fn (RentBill $bill): float => min(
                (float) ($bill->payments_sum_amount ?? 0),
                (float) $bill->total_due,
            ),
        ), 2);

        $summary = [
            'month' => $month,
            'starting_cash' => $startingCash,
            'starting_bank' => $startingBank,
            'starting_total' => round($startingCash + $startingBank, 2),
            'cash_rent' => $cashRent,
            'bank_rent' => $bankRent,
            'rent_received' => round($cashRent + $bankRent, 2),
            'cash_expenses' => $cashExpenses,
            'bank_expenses' => $bankExpenses,
            'expenses' => round($cashExpenses + $bankExpenses, 2),
            'closing_cash' => $closingCash,
            'closing_bank' => $closingBank,
            'closing_total' => round($closingCash + $closingBank, 2),
            'net_movement' => round($cashRent + $bankRent - $cashExpenses - $bankExpenses, 2),
            'cash_manual' => $stored !== null && abs(round($startingCash - $previous['cash'], 2)) >= 0.01,
            'bank_manual' => $stored !== null && abs(round($startingBank - $previous['bank'], 2)) >= 0.01,
            'previous_closing_cash' => $previous['cash'],
            'previous_closing_bank' => $previous['bank'],
            'previous_closing_total' => $previous['total'],
            'cash_difference' => round($startingCash - $previous['cash'], 2),
            'bank_difference' => round($startingBank - $previous['bank'], 2),
            'billed_total' => $billedTotal,
            'outstanding' => max(round($billedTotal - $billedPaid, 2), 0),
            'settled_bills' => $bills->filter(
                static fn (RentBill $bill): bool => (float) ($bill->payments_sum_amount ?? 0) + 0.005 >= (float) $bill->total_due,
            )->count(),
            'total_bills' => $bills->count(),
            'collection_progress' => $billedTotal > 0
                ? min(100, (int) round($billedPaid / $billedTotal * 100))
                : 0,
        ];

        $memo[$month] = $summary;

        return $summary;
    }

    /**
     * @param  array<string, array<string, mixed>>  $memo
     * @return array{cash: float, bank: float, total: float}
     */
    private static function previousClosingWithin(string $month, ?string $earliest, array &$memo): array
    {
        $previousMonth = Carbon::createFromFormat('Y-m', $month)
            ->startOfMonth()
            ->subMonthNoOverflow()
            ->format('Y-m');

        if ($earliest === null || $previousMonth < $earliest) {
            return ['cash' => 0.0, 'bank' => 0.0, 'total' => 0.0];
        }

        $previous = self::buildSummary($previousMonth, $earliest, $memo);

        return [
            'cash' => $previous['closing_cash'],
            'bank' => $previous['closing_bank'],
            'total' => $previous['closing_total'],
        ];
    }

    private static function earliestMonth(): ?string
    {
        $candidates = [
            MonthlyBalance::query()->min('month'),
            RentPayment::query()->min('paid_on'),
            Expense::query()->min('expense_date'),
        ];

        return collect($candidates)
            ->filter()
            ->map(static fn (string $value): string => Carbon::parse($value)->format('Y-m'))
            ->sort()
            ->first();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private static function monthBounds(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return [$start, $start->copy()->endOfMonth()];
    }
}
