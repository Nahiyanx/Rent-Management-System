<div class="space-y-6 lg:space-y-8">
    <x-page-header
        eyebrow="Overview · {{ $monthLabel }}"
        title="Dashboard"
        description="A calm view of your property finances — what came in, what went out, and where you stand."
    >
        <x-month-switcher :label="$monthLabel" />

        <button type="button" wire:click="openBalances" class="btn btn-secondary">
            <x-icon name="pencil-line" class="h-3.5 w-3.5" />
            Starting balances
        </button>
    </x-page-header>

    <x-flash :message="$statusMessage" />

    <section class="grid gap-5 xl:grid-cols-12">
        <div class="feature-card reveal p-7 sm:p-9 lg:p-10 xl:col-span-7">
            <div class="pointer-events-none absolute -right-24 -top-28 h-80 w-80 rounded-full border border-cream/[0.08]"></div>
            <div class="pointer-events-none absolute -right-10 -top-10 h-52 w-52 rounded-full border border-cream/[0.06]"></div>
            <x-brand-mark class="pointer-events-none absolute right-8 top-9 hidden h-28 w-28 opacity-[0.07] brightness-0 invert sm:block" />

            <div class="relative flex h-full flex-col">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[9.5px] font-medium uppercase tracking-[0.24em] text-cream/40">
                            Combined closing balance
                        </p>
                        <p class="mt-1.5 text-[11px] text-cream/30">{{ $monthLabel }} · after all movement</p>
                    </div>

                    <span class="shrink-0 rounded-full border border-cream/[0.14] bg-cream/[0.07] px-3 py-1 text-[9px] uppercase tracking-[0.16em] text-cream/55">
                        Month close
                    </span>
                </div>

                <p class="mt-8 text-[44px] font-medium leading-none tracking-[-0.055em] text-cream sm:text-[56px]">
                    <x-animated-money :amount="$data['closing_total']" />
                </p>

                <p class="mt-3 max-w-md text-[12px] leading-relaxed text-cream/35">
                    Cash and bank together, after this month's rent collections and expenses.
                </p>

                <div class="mt-10 grid grid-cols-2 gap-5 border-t border-cream/[0.1] pt-6 sm:gap-8">
                    <div>
                        <p class="text-[9px] font-medium uppercase tracking-[0.18em] text-cream/30">Closing cash</p>
                        <p class="mt-1.5 text-[17px] font-medium tracking-[-0.02em] text-cream/90">
                            <x-animated-money :amount="$data['closing_cash']" />
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-medium uppercase tracking-[0.18em] text-cream/30">Closing bank</p>
                        <p class="mt-1.5 text-[17px] font-medium tracking-[-0.02em] text-cream/90">
                            <x-animated-money :amount="$data['closing_bank']" />
                        </p>
                    </div>
                </div>

                <div class="mt-6 inline-flex w-fit items-center gap-2 rounded-full border border-cream/[0.08] bg-cream/[0.055] px-3.5 py-1.5 text-[10.5px] text-cream/55">
                    <x-icon name="trending-up" class="h-3.5 w-3.5 text-cream/45" />
                    <span>Net movement</span>
                    <span class="font-medium text-cream/85">
                        {{ $data['net_movement'] >= 0 ? '+' : '−' }} <x-money :amount="abs($data['net_movement'])" />
                    </span>
                </div>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 xl:col-span-5 xl:grid-cols-1">
            <div class="card reveal reveal-1 flex flex-col justify-between p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow">Rent received</p>
                        <p class="mt-3 text-[28px] font-medium leading-none tracking-[-0.045em] text-espresso">
                            <x-animated-money :amount="$data['rent_received']" />
                        </p>
                    </div>

                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-wine/[0.07] text-wine">
                        <x-icon name="hand-coins" class="h-[18px] w-[18px]" />
                    </span>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between text-[10px] text-espresso/35">
                        <span>{{ $data['settled_bills'] }} of {{ $data['total_bills'] }} bills settled</span>
                        <span class="font-medium text-espresso/50">{{ $data['collection_progress'] }}%</span>
                    </div>

                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-espresso/[0.07]">
                        <div
                            class="h-full rounded-full bg-wine transition-[width] duration-700"
                            style="width: {{ $data['collection_progress'] }}%"
                        ></div>
                    </div>

                    <p class="mt-3 text-[10.5px] text-espresso/35">
                        <x-money :amount="$data['outstanding']" /> still due across open bills
                    </p>
                </div>
            </div>

            <div class="card reveal reveal-2 flex flex-col justify-between p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow">Total expenses</p>
                        <p class="mt-3 text-[28px] font-medium leading-none tracking-[-0.045em] text-espresso">
                            <x-animated-money :amount="$data['expenses']" />
                        </p>
                    </div>

                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-espresso/[0.05] text-espresso/55">
                        <x-icon name="receipt-text" class="h-[18px] w-[18px]" />
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 border-t border-espresso/[0.07] pt-4">
                    <div>
                        <p class="text-[9px] font-medium uppercase tracking-[0.16em] text-espresso/30">Cash</p>
                        <p class="mt-1 text-[12px] font-medium text-espresso/75">
                            <x-money :amount="$data['cash_expenses']" />
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-medium uppercase tracking-[0.16em] text-espresso/30">Bank</p>
                        <p class="mt-1 text-[12px] font-medium text-espresso/75">
                            <x-money :amount="$data['bank_expenses']" />
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card reveal reveal-2 p-6 sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">{{ $monthLabel }}</p>
                <h2 class="mt-1.5 text-[21px] font-medium tracking-[-0.035em] text-espresso">Balance movement</h2>
            </div>

            <button type="button" wire:click="openBalances" class="btn btn-secondary self-start sm:self-auto">
                <x-icon name="pencil-line" class="h-3.5 w-3.5" />
                Edit starting balances
            </button>
        </div>

        <div class="mt-8 grid gap-8 md:grid-cols-[1fr_1.1fr_1fr]">
            <div>
                <p class="text-[9.5px] font-medium uppercase tracking-[0.2em] text-espresso/35">Starting</p>
                <p class="mt-2 text-[24px] font-medium tracking-[-0.04em] text-espresso">
                    <x-money :amount="$data['starting_total']" />
                </p>

                <dl class="mt-5 space-y-2.5 text-[11.5px]">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-espresso/40">Cash</dt>
                        <dd class="font-medium text-espresso/70"><x-money :amount="$data['starting_cash']" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-espresso/40">Bank</dt>
                        <dd class="font-medium text-espresso/70"><x-money :amount="$data['starting_bank']" /></dd>
                    </div>
                </dl>
            </div>

            <div class="border-t border-espresso/[0.07] pt-8 md:border-x md:border-t-0 md:px-8 md:py-0">
                <p class="text-[9.5px] font-medium uppercase tracking-[0.2em] text-espresso/35">Movement</p>

                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <span class="flex items-center gap-2 text-[11.5px] text-espresso/50">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-wine/[0.06] text-wine">
                                <x-icon name="arrow-down-left" class="h-3 w-3" />
                            </span>
                            Rent received
                        </span>
                        <span class="text-[12px] font-medium text-wine">+ <x-money :amount="$data['rent_received']" /></span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="flex items-center gap-2 text-[11.5px] text-espresso/50">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-espresso/[0.045] text-espresso/50">
                                <x-icon name="arrow-up-right" class="h-3 w-3" />
                            </span>
                            Expenses
                        </span>
                        <span class="text-[12px] font-medium text-espresso/65">− <x-money :amount="$data['expenses']" /></span>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-dashed border-espresso/[0.1] pt-4 text-[11px]">
                    <span class="text-espresso/40">Net movement</span>
                    <span class="font-medium text-espresso">
                        {{ $data['net_movement'] >= 0 ? '+' : '−' }} <x-money :amount="abs($data['net_movement'])" />
                    </span>
                </div>
            </div>

            <div>
                <p class="text-[9.5px] font-medium uppercase tracking-[0.2em] text-espresso/35">Closing</p>
                <p class="mt-2 text-[24px] font-medium tracking-[-0.04em] text-espresso">
                    <x-money :amount="$data['closing_total']" />
                </p>

                <dl class="mt-5 space-y-2.5 text-[11.5px]">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-espresso/40">Cash</dt>
                        <dd class="font-medium text-espresso/70"><x-money :amount="$data['closing_cash']" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-espresso/40">Bank</dt>
                        <dd class="font-medium text-espresso/70"><x-money :amount="$data['closing_bank']" /></dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-2 border-t border-espresso/[0.07] pt-5 text-[10px] text-espresso/30 sm:flex-row sm:items-center sm:justify-between">
            <span>Starting balance + rent received − expenses = closing balance.</span>
            <span class="uppercase tracking-[0.16em]">Live ledger</span>
        </div>
    </section>

    <x-modal
        property="showBalancesModal"
        eyebrow="Opening position"
        title="Edit starting balances"
        description="Set the cash and bank balances that {{ $monthLabel }} begins with."
    >
        @php
            $enteredCash = round((float) ($balanceForm['cash'] ?? 0), 2);
            $enteredBank = round((float) ($balanceForm['bank'] ?? 0), 2);
            $cashDifference = round($enteredCash - $data['previous_closing_cash'], 2);
            $bankDifference = round($enteredBank - $data['previous_closing_bank'], 2);
            $hasAdjustment = abs($cashDifference) >= 0.01 || abs($bankDifference) >= 0.01;
        @endphp

        <form class="space-y-5" x-on:submit.prevent>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="starting-cash" class="label">Starting cash</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                        <input
                            id="starting-cash"
                            type="number"
                            min="0"
                            step="0.01"
                            inputmode="decimal"
                            wire:model.live.debounce.250ms="balanceForm.cash"
                            class="input pl-9"
                            placeholder="0"
                        >
                    </div>
                    @error('balanceForm.cash')
                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="starting-bank" class="label">Starting bank</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                        <input
                            id="starting-bank"
                            type="number"
                            min="0"
                            step="0.01"
                            inputmode="decimal"
                            wire:model.live.debounce.250ms="balanceForm.bank"
                            class="input pl-9"
                            placeholder="0"
                        >
                    </div>
                    @error('balanceForm.bank')
                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-espresso/[0.08] bg-espresso/[0.025] px-4 py-3.5">
                <span class="text-[10px] font-medium uppercase tracking-[0.16em] text-espresso/35">Total starting balance</span>
                <span class="text-[14px] font-medium text-espresso">
                    <x-money :amount="$enteredCash + $enteredBank" />
                </span>
            </div>

            <div class="space-y-2 rounded-2xl border border-espresso/[0.08] bg-[#fbf7e7]/55 px-4 py-3.5 text-[11px] text-espresso/45">
                <div class="flex items-center justify-between gap-4">
                    <span>Previous closing cash</span>
                    <span class="font-medium text-espresso/70"><x-money :amount="$data['previous_closing_cash']" /></span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>Previous closing bank</span>
                    <span class="font-medium text-espresso/70"><x-money :amount="$data['previous_closing_bank']" /></span>
                </div>
            </div>

            @if ($hasAdjustment)
                <div class="flex items-start gap-2.5 rounded-2xl border border-wine/20 bg-wine/[0.06] px-4 py-3.5 text-[11px] leading-relaxed text-espresso/55">
                    <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-wine/70" />
                    <div>
                        <p class="font-medium text-wine">Manual adjustment</p>
                        <p class="mt-1">
                            @if (abs($cashDifference) >= 0.01)
                                Cash is {{ $cashDifference > 0 ? 'higher' : 'lower' }} by <x-money :amount="abs($cashDifference)" />.
                            @endif
                            @if (abs($bankDifference) >= 0.01)
                                Bank is {{ $bankDifference > 0 ? 'higher' : 'lower' }} by <x-money :amount="abs($bankDifference)" />.
                            @endif
                        </p>
                        <p class="mt-1">
                            This difference will be saved as a manual starting balance and kept for audit. Starting balances are not rental income.
                        </p>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-2.5 rounded-2xl border border-espresso/[0.08] bg-espresso/[0.025] px-4 py-3.5 text-[11px] leading-relaxed text-espresso/45">
                    <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-espresso/35" />
                    <span>These balances match the previous month's closing position. Starting balances are not rental income.</span>
                </div>
            @endif
        </form>

        <x-slot:footer>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[9px] uppercase tracking-[0.16em] text-espresso/30">
                    {{ $hasAdjustment ? 'Manual adjustment' : 'Rolled forward' }}
                </p>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="closeBalances" class="btn btn-secondary">Cancel</button>
                    <button type="button" wire:click="saveBalances" class="btn btn-primary">
                        <x-icon name="check" class="h-3.5 w-3.5" />
                        Save balances
                    </button>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
