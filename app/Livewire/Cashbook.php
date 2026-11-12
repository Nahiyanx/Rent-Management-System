<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasMonth;
use App\Models\Expense;
use App\Support\Financials;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cashbook')]
class Cashbook extends Component
{
    use HasMonth;

    public bool $showExpenseModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingExpenseId = null;

    public ?int $deletingExpenseId = null;

    public ?string $statusMessage = null;

    /**
     * @var array{description: string, date: string, amount: int|float|string, paid_from: string}
     */
    public array $expenseForm = [
        'description' => '',
        'date' => '',
        'amount' => '',
        'paid_from' => 'Cash',
    ];

    public function openExpense(): void
    {
        $this->resetValidation();
        $this->editingExpenseId = null;
        $this->expenseForm = [
            'description' => '',
            'date' => $this->defaultExpenseDate(),
            'amount' => '',
            'paid_from' => 'Cash',
        ];
        $this->showExpenseModal = true;
    }

    public function editExpense(int $expenseId): void
    {
        $expense = Expense::query()->find($expenseId);

        if ($expense === null) {
            return;
        }

        $this->resetValidation();
        $this->editingExpenseId = $expenseId;
        $this->expenseForm = [
            'description' => $expense->description,
            'date' => $expense->expense_date->toDateString(),
            'amount' => (string) (float) $expense->amount,
            'paid_from' => $expense->paid_from,
        ];
        $this->showExpenseModal = true;
    }

    public function saveExpense(): void
    {
        $this->validate([
            'expenseForm.date' => ['required', 'date_format:Y-m-d'],
            'expenseForm.description' => ['required', 'string', 'max:255'],
            'expenseForm.amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'expenseForm.paid_from' => ['required', Rule::in(['Cash', 'Bank'])],
        ]);

        $attributes = [
            'expense_date' => $this->expenseForm['date'],
            'description' => trim($this->expenseForm['description']),
            'amount' => round((float) $this->expenseForm['amount'], 2),
            'paid_from' => $this->expenseForm['paid_from'],
        ];

        $isEditing = $this->editingExpenseId !== null;

        DB::transaction(function () use ($attributes, $isEditing): void {
            if ($isEditing) {
                Expense::query()->whereKey($this->editingExpenseId)->update($attributes);

                return;
            }

            Expense::query()->create($attributes);
        });

        $this->showExpenseModal = false;
        $this->editingExpenseId = null;
        $this->statusMessage = $isEditing ? 'Expense updated.' : 'Expense recorded.';
    }

    public function confirmDelete(int $expenseId): void
    {
        if (! Expense::query()->whereKey($expenseId)->exists()) {
            return;
        }

        $this->deletingExpenseId = $expenseId;
        $this->showDeleteModal = true;
    }

    public function deleteExpense(): void
    {
        if ($this->deletingExpenseId === null) {
            return;
        }

        DB::transaction(function (): void {
            Expense::query()->whereKey($this->deletingExpenseId)->delete();
        });

        $this->showDeleteModal = false;
        $this->deletingExpenseId = null;
        $this->statusMessage = 'Expense deleted.';
    }

    public function closeExpenseModal(): void
    {
        $this->showExpenseModal = false;
        $this->editingExpenseId = null;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingExpenseId = null;
    }

    public function updatedMonth(): void
    {
        $this->statusMessage = null;
        $this->showExpenseModal = false;
        $this->showDeleteModal = false;
        $this->editingExpenseId = null;
        $this->deletingExpenseId = null;
    }

    public function render(): View
    {
        $data = Financials::summary($this->month);

        $expenses = Financials::expensesForMonth($this->month)
            ->map(static fn (Expense $expense): array => [
                'id' => $expense->id,
                'date' => $expense->expense_date->format('Y-m-d'),
                'date_label' => $expense->expense_date->format('d M Y'),
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'paid_from' => $expense->paid_from,
            ])
            ->values();

        return view('livewire.cashbook', [
            'data' => $data,
            'expenses' => $expenses,
            'expensesTotal' => $data['expenses'],
            'deletingExpense' => $expenses->firstWhere('id', $this->deletingExpenseId),
            'isEditing' => $this->editingExpenseId !== null,
            'monthLabel' => $this->monthLabel(),
        ]);
    }

    private function defaultExpenseDate(): string
    {
        return $this->monthStart()->isSameMonth(now())
            ? now()->toDateString()
            : $this->monthStart()->toDateString();
    }
}
