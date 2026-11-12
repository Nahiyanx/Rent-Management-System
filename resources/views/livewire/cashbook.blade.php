<div class="space-y-6 lg:space-y-8">
    <x-page-header
        eyebrow="Ledger · {{ $monthLabel }}"
        title="Cashbook"
        description="Every amount, in one quiet place — balances first, then the month's expenses."
    >
        <x-month-switcher :label="$monthLabel" />

        <button type="button" wire:click="openExpense" class="btn btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            Add expense
        </button>
    </x-page-header>

    <x-flash :message="$statusMessage" />

    <section class="grid gap-5 md:grid-cols-3">
        <div class="card reveal p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="eyebrow">Cash balance</p>
                    <p class="mt-4 text-[30px] font-medium leading-none tracking-[-0.05em] text-espresso">
                        <x-animated-money :amount="$data['closing_cash']" />
                    </p>
                </div>

                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-wine/[0.07] text-wine">
                    <x-icon name="wallet" class="h-[18px] w-[18px]" />
                </span>
            </div>

            <div class="mt-5 flex items-center justify-between border-t border-espresso/[0.07] pt-4 text-[9.5px] uppercase tracking-[0.14em] text-espresso/30">
                <span>Physical cash on hand</span>
                <span>Closing</span>
            </div>
        </div>

        <div class="card reveal reveal-1 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="eyebrow">Bank balance</p>
                    <p class="mt-4 text-[30px] font-medium leading-none tracking-[-0.05em] text-espresso">
                        <x-animated-money :amount="$data['closing_bank']" />
                    </p>
                </div>

                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-espresso/[0.05] text-espresso/55">
                    <x-icon name="landmark" class="h-[18px] w-[18px]" />
                </span>
            </div>

            <div class="mt-5 flex items-center justify-between border-t border-espresso/[0.07] pt-4 text-[9.5px] uppercase tracking-[0.14em] text-espresso/30">
                <span>Across bank accounts</span>
                <span>Closing</span>
            </div>
        </div>

        <div class="feature-card reveal reveal-2 p-6">
            <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full border border-cream/[0.08]"></div>

            <div class="relative">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[9.5px] font-medium uppercase tracking-[0.2em] text-cream/40">Combined total balance</p>
                        <p class="mt-4 text-[30px] font-medium leading-none tracking-[-0.05em] text-cream">
                            <x-animated-money :amount="$data['closing_total']" />
                        </p>
                    </div>

                    <span class="grid h-10 w-10 place-items-center rounded-2xl border border-cream/[0.1] bg-cream/[0.07] text-cream/70">
                        <x-icon name="coins" class="h-[18px] w-[18px]" />
                    </span>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-cream/[0.1] pt-4 text-[9.5px] uppercase tracking-[0.14em] text-cream/35">
                    <span>Cash + bank</span>
                    <span>Month close</span>
                </div>
            </div>
        </div>
    </section>

    <section class="card reveal reveal-2 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-espresso/[0.07] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-[16px] font-medium tracking-[-0.025em] text-espresso">Expenses</h2>
                <p class="mt-1 text-[10.5px] text-espresso/35">{{ count($expenses) }} entries · {{ $monthLabel }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 max-sm:w-full">
                <a
                    href="{{ route('cashbook.export.pdf', ['month' => $data['month']]) }}"
                    class="btn btn-secondary px-3.5 py-2 text-[11px] max-sm:flex-1"
                    title="Download the {{ $monthLabel }} cashbook as a PDF"
                    download
                >
                    <x-icon name="file-text" class="h-3.5 w-3.5" />
                    PDF
                    <span class="stage-tag">Download</span>
                </a>

                <a
                    href="{{ route('cashbook.export.xlsx', ['month' => $data['month']]) }}"
                    class="btn btn-secondary px-3.5 py-2 text-[11px] max-sm:flex-1"
                    title="Download the {{ $monthLabel }} cashbook as an Excel workbook"
                    download
                >
                    <x-icon name="file-spreadsheet" class="h-3.5 w-3.5" />
                    Excel
                    <span class="stage-tag">Download</span>
                </a>
            </div>
        </div>

        <div class="hidden grid-cols-[130px_minmax(0,1fr)_120px_130px_88px] gap-4 border-b border-espresso/[0.07] px-6 py-3.5 text-[9px] font-medium uppercase tracking-[0.18em] text-espresso/30 md:grid">
            <span>Expense date</span>
            <span>Expense description</span>
            <span>Paid from</span>
            <span class="text-right">Amount</span>
            <span class="text-right">Actions</span>
        </div>

        @forelse ($expenses as $expense)
            <div
                wire:key="expense-{{ $expense['id'] }}"
                class="group grid grid-cols-2 gap-x-4 gap-y-3 border-b border-espresso/[0.06] px-5 py-4 transition-colors duration-200 last:border-0 hover:bg-[#fffdf2]/45 md:grid-cols-[130px_minmax(0,1fr)_120px_130px_88px] md:items-center md:gap-4 md:px-6"
            >
                <div class="order-1 md:order-none">
                    <p class="text-[11px] text-espresso/45 md:text-[12px]">{{ $expense['date_label'] }}</p>
                </div>

                <div class="order-3 col-span-2 md:order-none md:col-span-1">
                    <p class="text-[13px] font-medium text-espresso">{{ $expense['description'] }}</p>
                    <p class="mt-0.5 text-[10px] text-espresso/30 md:hidden">
                        {{ $expense['date_label'] }} · Paid from {{ $expense['paid_from'] }}
                    </p>
                </div>

                <div class="order-4 md:order-none">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-espresso/[0.08] bg-espresso/[0.03] px-2.5 py-1 text-[10px] text-espresso/50">
                        <x-icon :name="$expense['paid_from'] === 'Cash' ? 'wallet' : 'landmark'" class="h-3 w-3" />
                        {{ $expense['paid_from'] }}
                    </span>
                </div>

                <div class="order-2 text-right md:order-none">
                    <p class="text-[13px] font-medium text-espresso"><x-money :amount="$expense['amount']" /></p>
                </div>

                <div class="order-5 flex justify-end gap-1 md:order-none">
                    <button
                        type="button"
                        wire:click="editExpense({{ $expense['id'] }})"
                        class="icon-btn"
                        aria-label="Edit {{ $expense['description'] }}"
                    >
                        <x-icon name="pencil-line" class="h-4 w-4" />
                    </button>

                    <button
                        type="button"
                        wire:click="confirmDelete({{ $expense['id'] }})"
                        class="icon-btn hover:bg-wine/[0.06] hover:text-wine"
                        aria-label="Delete {{ $expense['description'] }}"
                    >
                        <x-icon name="trash-2" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-espresso/[0.05] text-espresso/35">
                    <x-icon name="receipt-text" class="h-5 w-5" />
                </div>
                <p class="mt-4 text-[14px] font-medium text-espresso/70">No expenses yet</p>
                <p class="mt-1 text-[12px] text-espresso/40">Add an expense to begin this month's ledger.</p>
            </div>
        @endforelse

        <div class="flex items-center justify-between gap-4 border-t border-espresso/[0.08] bg-[#f2ebd2]/35 px-5 py-4 sm:px-6">
            <div>
                <p class="text-[9px] font-medium uppercase tracking-[0.16em] text-espresso/35">Total expenses</p>
                <p class="mt-1 text-[10px] text-espresso/30">Cash <x-money :amount="$data['cash_expenses']" /> · Bank <x-money :amount="$data['bank_expenses']" /></p>
            </div>

            <p class="text-[17px] font-medium tracking-[-0.03em] text-espresso">
                <x-money :amount="$expensesTotal" />
            </p>
        </div>
    </section>

    <x-modal
        property="showExpenseModal"
        eyebrow="Cashbook"
        :title="$isEditing ? 'Edit expense' : 'Add an expense'"
        :description="$isEditing
            ? 'Adjust this expense. Affected balances recalculate immediately.'
            : 'Record a cost against this month. Cash and bank balances update immediately.'"
    >
        <form class="space-y-5" x-on:submit.prevent>
            <div>
                <label for="expense-description" class="label">Expense description</label>
                <input
                    id="expense-description"
                    type="text"
                    wire:model="expenseForm.description"
                    class="input"
                    placeholder="e.g. Building maintenance"
                >
                @error('expenseForm.description')
                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="expense-date" class="label">Expense date</label>
                    <input
                        id="expense-date"
                        type="date"
                        wire:model="expenseForm.date"
                        class="input"
                    >
                    @error('expenseForm.date')
                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="expense-amount" class="label">Expense amount</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                        <input
                            id="expense-amount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            inputmode="decimal"
                            wire:model="expenseForm.amount"
                            class="input pl-9"
                            placeholder="0"
                        >
                    </div>
                    @error('expenseForm.amount')
                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <span class="label">Paid from</span>

                <div class="grid grid-cols-2 gap-2">
                    <label class="relative cursor-pointer">
                        <input type="radio" wire:model="expenseForm.paid_from" value="Cash" class="peer sr-only">
                        <span class="flex items-center justify-center gap-2.5 rounded-2xl border border-espresso/[0.09] bg-[#fbf7e7]/55 px-4 py-3.5 text-[12px] font-medium text-espresso/45 transition-all duration-200 peer-checked:border-wine peer-checked:bg-wine/[0.06] peer-checked:text-wine peer-focus-visible:ring-2 peer-focus-visible:ring-wine/30">
                            <x-icon name="wallet" class="h-4 w-4" />
                            Cash
                        </span>
                    </label>

                    <label class="relative cursor-pointer">
                        <input type="radio" wire:model="expenseForm.paid_from" value="Bank" class="peer sr-only">
                        <span class="flex items-center justify-center gap-2.5 rounded-2xl border border-espresso/[0.09] bg-[#fbf7e7]/55 px-4 py-3.5 text-[12px] font-medium text-espresso/45 transition-all duration-200 peer-checked:border-wine peer-checked:bg-wine/[0.06] peer-checked:text-wine peer-focus-visible:ring-2 peer-focus-visible:ring-wine/30">
                            <x-icon name="landmark" class="h-4 w-4" />
                            Bank
                        </span>
                    </label>
                </div>
                @error('expenseForm.paid_from')
                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-start gap-2.5 rounded-2xl border border-espresso/[0.08] bg-espresso/[0.025] px-4 py-3.5 text-[11px] leading-relaxed text-espresso/45">
                <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-espresso/35" />
                <span>Cash expenses reduce the cash balance; bank expenses reduce the bank balance.</span>
            </div>
        </form>

        <x-slot:footer>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[9px] uppercase tracking-[0.16em] text-espresso/30">
                    {{ $isEditing ? 'Editing entry' : 'New entry' }}
                </p>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="closeExpenseModal" class="btn btn-secondary">Cancel</button>
                    <button type="button" wire:click="saveExpense" class="btn btn-primary">
                        <x-icon name="check" class="h-3.5 w-3.5" />
                        {{ $isEditing ? 'Save changes' : 'Save expense' }}
                    </button>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>

    @if ($deletingExpense)
        <x-modal
            property="showDeleteModal"
            eyebrow="Cashbook"
            title="Delete expense?"
            :description="'This will remove “'.$deletingExpense['description'].'” from '.$monthLabel.'.'"
            max-width="sm:max-w-md"
        >
            <div class="rounded-2xl border border-wine/[0.1] bg-wine/[0.04] p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[12.5px] font-medium text-espresso">{{ $deletingExpense['description'] }}</p>
                        <p class="mt-1 text-[10.5px] text-espresso/40">{{ $deletingExpense['date_label'] }} · {{ $deletingExpense['paid_from'] }}</p>
                    </div>
                    <p class="text-[16px] font-medium text-wine"><x-money :amount="$deletingExpense['amount']" /></p>
                </div>
            </div>

            <p class="mt-4 text-[11px] leading-relaxed text-espresso/45">
                Deleting is permanent. The affected cash or bank balance will recalculate immediately.
            </p>

            <x-slot:footer>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" wire:click="closeDeleteModal" class="btn btn-secondary">Cancel</button>
                    <button type="button" wire:click="deleteExpense" class="btn btn-primary bg-espresso hover:bg-espresso">
                        <x-icon name="trash-2" class="h-3.5 w-3.5" />
                        Delete expense
                    </button>
                </div>
            </x-slot:footer>
        </x-modal>
    @endif
</div>
