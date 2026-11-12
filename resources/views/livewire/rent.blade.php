<div class="space-y-6 lg:space-y-8">
    <x-page-header
        eyebrow="Collections · {{ $monthLabel }}"
        title="Rent"
        description="A clear view of this month's bills, payments and what remains open."
    >
        <x-month-switcher :label="$monthLabel" />

        <div class="relative w-full sm:w-[190px]">
            <select wire:model.live="buildingFilter" class="select" aria-label="Filter by building">
                <option value="all">All buildings</option>
                @foreach ($buildings as $building)
                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                @endforeach
            </select>
            <x-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-espresso/30" />
        </div>
    </x-page-header>

    <section class="card reveal reveal-1 grid overflow-hidden sm:grid-cols-[1fr_1fr_1fr_1.25fr]">
        <div class="border-b border-espresso/[0.07] p-5 sm:border-b-0 sm:border-r">
            <p class="text-[9px] font-medium uppercase tracking-[0.17em] text-espresso/30">Collected</p>
            <p class="mt-2 text-[21px] font-medium tracking-[-0.04em] text-espresso">
                <x-money :amount="$summary['collected']" />
            </p>
            <p class="mt-1 text-[10px] text-espresso/30">of <x-money :amount="$summary['billed']" /> billed</p>
        </div>

        <div class="border-b border-espresso/[0.07] p-5 sm:border-b-0 sm:border-r">
            <p class="text-[9px] font-medium uppercase tracking-[0.17em] text-espresso/30">Outstanding</p>
            <p class="mt-2 text-[21px] font-medium tracking-[-0.04em] text-wine">
                <x-money :amount="$summary['outstanding']" />
            </p>
            <p class="mt-1 text-[10px] text-espresso/30">across open bills</p>
        </div>

        <div class="border-b border-espresso/[0.07] p-5 sm:border-b-0 sm:border-r">
            <p class="text-[9px] font-medium uppercase tracking-[0.17em] text-espresso/30">Settled</p>
            <p class="mt-2 text-[21px] font-medium tracking-[-0.04em] text-espresso">
                {{ $summary['settled'] }}<span class="text-[13px] font-normal text-espresso/35"> / {{ $summary['total'] }}</span>
            </p>
            <p class="mt-1 text-[10px] text-espresso/30">bills fully paid</p>
        </div>

        <div class="p-5">
            <div class="flex items-center justify-between text-[9px] font-medium uppercase tracking-[0.15em] text-espresso/30">
                <span>Collection progress</span>
                <span class="text-espresso/55">{{ $summary['progress'] }}%</span>
            </div>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-espresso/[0.07]">
                <div class="h-full rounded-full bg-wine transition-[width] duration-700" style="width: {{ $summary['progress'] }}%"></div>
            </div>
            <p class="mt-3 text-[10px] leading-relaxed text-espresso/30">Live billing for {{ $monthLabel }}.</p>
        </div>
    </section>

    <section class="card overflow-hidden">
        <div class="hidden grid-cols-[0.55fr_1.4fr_0.8fr_0.65fr_120px] gap-4 border-b border-espresso/[0.07] px-6 py-3.5 text-[9px] font-medium uppercase tracking-[0.18em] text-espresso/30 lg:grid">
            <span>Flat</span>
            <span>Tenant</span>
            <span>Monthly bill</span>
            <span>Status</span>
            <span class="text-right">Action</span>
        </div>

        @forelse ($tenancies as $tenancy)
            @php
                $bill = $tenancy->rentBills->first();
                $status = $bill?->status() ?? 'Not billed';
            @endphp

            <div
                wire:key="bill-{{ $tenancy->id }}"
                class="group grid grid-cols-2 gap-x-4 gap-y-4 border-b border-espresso/[0.06] px-5 py-4 transition-colors duration-200 last:border-0 hover:bg-[#fffdf2]/45 sm:px-6 lg:grid-cols-[0.55fr_1.4fr_0.8fr_0.65fr_120px] lg:items-center lg:gap-4"
            >
                <div>
                    <p class="text-[8.5px] font-medium uppercase tracking-[0.15em] text-espresso/25 lg:hidden">Flat</p>
                    <p class="mt-0.5 text-[13.5px] font-medium text-espresso">{{ $tenancy->flat->unit_number }}</p>
                    <p class="text-[10.5px] text-espresso/35">{{ $tenancy->flat->building->name }}</p>
                </div>

                <div class="col-span-2 flex items-center gap-3 lg:col-span-1">
                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-espresso/[0.055] text-[9.5px] font-medium tracking-[0.08em] text-espresso/60">
                        {{ $tenancy->tenant->initials() }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-[12.5px] font-medium text-espresso">{{ $tenancy->tenant->name }}</p>
                        <p class="text-[10px] text-espresso/35">House rent <x-money :amount="$tenancy->house_rent" /></p>
                    </div>
                </div>

                <div>
                    <p class="text-[8.5px] font-medium uppercase tracking-[0.15em] text-espresso/25 lg:hidden">Monthly bill</p>
                    @if ($bill)
                        <p class="mt-0.5 text-[13px] font-medium text-espresso"><x-money :amount="$bill->total_due" /></p>
                        <p class="text-[10px] text-espresso/30">Billed {{ $monthShortLabel }}</p>
                    @else
                        <p class="mt-0.5 text-[13px] font-medium text-espresso/40">—</p>
                        <p class="text-[10px] text-espresso/30">Not billed yet</p>
                    @endif
                </div>

                <div>
                    <p class="text-[8.5px] font-medium uppercase tracking-[0.15em] text-espresso/25 lg:hidden">Status</p>
                    <div class="mt-0.5">
                        <x-status-pill :status="$status" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        wire:click="openBill({{ $tenancy->id }})"
                        class="btn btn-secondary px-4 py-2 text-[11px]"
                    >
                        {{ $status === 'Paid' ? 'View bill' : ($bill ? 'Collect' : 'Prepare') }}
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-espresso/[0.05] text-espresso/35">
                    <x-icon name="receipt-text" class="h-5 w-5" />
                </div>
                <p class="mt-4 text-[14px] font-medium text-espresso/70">No tenancies for this building</p>
                <p class="mt-1 text-[12px] text-espresso/40">Choose another building or move to a different month.</p>
            </div>
        @endforelse
    </section>

    @if ($selectedBill)
        @php
            $locked = $selectedBill->payments->isNotEmpty();
            $balance = $selectedBill->balance();
            $displayTotal = $locked ? (float) $selectedBill->total_due : $billTotal;
        @endphp

        <x-drawer
            property="showBillDrawer"
            eyebrow="Rent bill · {{ $monthLabel }}"
            :title="'Flat '.$selectedTenancy->flat->unit_number.' · '.$selectedTenancy->flat->building->name"
            :description="$selectedTenancy->tenant->name"
        >
            <div class="space-y-6">
                <x-flash :message="$statusMessage" />

                <div class="flex items-center justify-between gap-4">
                    <x-status-pill :status="$selectedBill->status()" />

                    <p class="text-[10px] uppercase tracking-[0.15em] text-espresso/30">
                        Bill #{{ str_pad((string) $selectedBill->id, 4, '0', STR_PAD_LEFT) }}
                    </p>
                </div>

                @if ($locked)
                    <div class="space-y-3.5">
                        @foreach ([
                            ['House Rent', $selectedBill->house_rent, null],
                            ['Electricity', $selectedBill->electricity, 'Manual entry'],
                            ['Gas', $selectedBill->gas, null],
                            ['Car Parking', $selectedBill->parking, null],
                            ['Service Charge', $selectedBill->service_charge, null],
                            ['Other Charges', $selectedBill->other_charges, 'Optional'],
                        ] as [$label, $amount, $meta])
                            <div class="flex items-center justify-between gap-4 text-[12.5px]">
                                <span class="flex flex-wrap items-center gap-2 text-espresso/50">
                                    {{ $label }}
                                    @if ($meta)
                                        <span class="rounded-full bg-espresso/[0.045] px-2 py-0.5 text-[8px] font-medium uppercase tracking-[0.12em] text-espresso/30">
                                            {{ $meta }}
                                        </span>
                                    @endif
                                </span>
                                <span @class(['font-medium text-espresso', 'text-espresso/25' => (float) $amount === 0.0])>
                                    @if ((float) $amount > 0)
                                        <x-money :amount="$amount" />
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-start gap-2.5 rounded-2xl border border-wine/[0.1] bg-wine/[0.04] px-4 py-3.5 text-[11px] leading-relaxed text-espresso/50">
                        <x-icon name="lock" class="mt-0.5 h-3.5 w-3.5 text-wine/60" />
                        <span>This invoice is locked because a payment has been recorded. Historical invoice amounts are preserved.</span>
                    </div>
                @else
                    <form wire:submit="saveBill" class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ([
                                ['house_rent', 'House Rent', null],
                                ['electricity', 'Electricity', 'Manual'],
                                ['gas', 'Gas', null],
                                ['parking', 'Car Parking', null],
                                ['service_charge', 'Service Charge', null],
                                ['other_charges', 'Other Charges', 'Optional'],
                            ] as [$field, $label, $meta])
                                <div>
                                    <label for="bill-{{ $field }}" class="label">
                                        {{ $label }}
                                        @if ($meta)
                                            <span class="ml-1 normal-case tracking-normal text-espresso/25">{{ $meta }}</span>
                                        @endif
                                    </label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                                        <input
                                            id="bill-{{ $field }}"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            inputmode="decimal"
                                            wire:model.live.debounce.400ms="billForm.{{ $field }}"
                                            class="input pl-9"
                                            placeholder="0"
                                        >
                                    </div>
                                    @error('billForm.'.$field)
                                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        @error('billForm.house_rent')
                            <p class="text-[11px] text-wine">{{ $message }}</p>
                        @enderror

                        <div class="flex items-end justify-between gap-4 border-t border-dashed border-espresso/[0.12] pt-5">
                            <div>
                                <p class="text-[9px] font-medium uppercase tracking-[0.17em] text-espresso/30">Total amount</p>
                                <p class="mt-1 text-[28px] font-medium tracking-[-0.045em] text-espresso">
                                    <x-money :amount="$displayTotal" />
                                </p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-secondary w-full py-2.5" wire:loading.attr="disabled" wire:target="saveBill">
                            <x-icon name="check" class="h-4 w-4" />
                            <span wire:loading.remove wire:target="saveBill">Save bill amounts</span>
                            <span wire:loading wire:target="saveBill">Saving…</span>
                        </button>
                    </form>
                @endif

                <div class="rounded-2xl border border-espresso/[0.08] bg-espresso/[0.025] p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[9px] font-medium uppercase tracking-[0.17em] text-espresso/35">
                            {{ $balance > 0 ? 'Record payment' : 'Payment complete' }}
                        </p>
                        <span class="text-[9px] uppercase tracking-[0.14em] text-espresso/25">{{ $monthShortLabel }}</span>
                    </div>

                    @if ($balance > 0)
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach ([['Cash', 'wallet'], ['Bank', 'landmark']] as [$method, $icon])
                                <button
                                    type="button"
                                    wire:click="$set('paymentForm.method', '{{ $method }}')"
                                    @class([
                                        'flex items-center justify-center gap-2 rounded-2xl border px-3 py-3 text-[11.5px] font-medium transition-all duration-200',
                                        'border-wine bg-wine text-cream shadow-[0_10px_22px_-14px_rgba(139,0,0,0.8)]' => $paymentForm['method'] === $method,
                                        'border-espresso/[0.09] bg-[#fbf7e7]/55 text-espresso/50 hover:border-espresso/[0.16]' => $paymentForm['method'] !== $method,
                                    ])
                                >
                                    <x-icon :name="$icon" class="h-4 w-4" />
                                    {{ $method }}
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="payment-date" class="label">Payment date</label>
                                <input
                                    id="payment-date"
                                    type="date"
                                    wire:model="paymentForm.paid_on"
                                    class="input"
                                >
                                @error('paymentForm.paid_on')
                                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <label for="payment-amount" class="label">Amount received</label>
                                    <button type="button" wire:click="fillFullAmount" class="mb-2 text-[9px] uppercase tracking-[0.13em] text-wine/70 transition-colors hover:text-wine">
                                        Full amount
                                    </button>
                                </div>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                                    <input
                                        id="payment-amount"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        inputmode="decimal"
                                        wire:model="paymentForm.amount"
                                        class="input pl-9"
                                        placeholder="0"
                                    >
                                </div>
                                @error('paymentForm.amount')
                                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <p class="mt-3 text-[10px] leading-relaxed text-espresso/35">
                            The billing month stays {{ $monthLabel }} even when payment is collected later.
                        </p>
                    @else
                        <div class="mt-3 flex items-center gap-2 text-[12px] text-espresso/55">
                            <x-icon name="circle-check" class="h-4 w-4 text-burgundy/60" />
                            This bill has been settled in full.
                        </div>
                    @endif
                </div>

                @if ($paymentHistory->isNotEmpty())
                    <div>
                        <p class="text-[9px] font-medium uppercase tracking-[0.17em] text-espresso/30">Payment history</p>

                        <div class="mt-3 space-y-2">
                            @foreach ($paymentHistory as $payment)
                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-espresso/[0.07] bg-[#fbf7e7]/45 px-4 py-2.5" wire:key="payment-{{ $payment->id }}">
                                    <div class="flex items-center gap-2.5">
                                        <x-icon :name="$payment->method === 'Bank' ? 'landmark' : 'wallet'" class="h-3.5 w-3.5 text-espresso/35" />
                                        <div>
                                            <p class="text-[11.5px] font-medium text-espresso/70"><x-money :amount="$payment->amount" /></p>
                                            <p class="text-[9.5px] text-espresso/35">{{ $payment->paid_on->format('d M Y') }} · {{ $payment->method }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[9px] uppercase tracking-[0.13em] text-espresso/25">Received</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-start gap-2.5 rounded-2xl border border-wine/[0.1] bg-wine/[0.04] px-4 py-3.5 text-[11px] leading-relaxed text-espresso/50">
                    <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 text-wine/60" />
                    <span>Electricity is entered manually each month — there are no meter readings. Partial payments can be recorded until the bill is settled.</span>
                </div>
            </div>

            <x-slot:footer>
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-[10px] font-medium uppercase tracking-[0.16em] text-espresso/35">
                            {{ $balance > 0 ? 'Amount due' : 'Settled in full' }}
                        </span>
                        <span class="text-[17px] font-medium text-espresso">
                            <x-money :amount="$balance" />
                        </span>
                    </div>

                    @if ($balance > 0)
                        <button
                            type="button"
                            wire:click="recordPayment"
                            wire:loading.attr="disabled"
                            wire:target="recordPayment"
                            @disabled((float) $paymentForm['amount'] <= 0)
                            class="btn btn-primary w-full py-3"
                        >
                            <x-icon name="wallet" class="h-4 w-4" />
                            <span wire:loading.remove wire:target="recordPayment">Record payment</span>
                            <span wire:loading wire:target="recordPayment">Recording…</span>
                        </button>

                        @error('paymentForm.amount')
                            <p class="text-center text-[10.5px] text-wine">{{ $message }}</p>
                        @enderror
                    @endif

                    <p class="text-center text-[9.5px] text-espresso/30">
                        {{ $balance > 0 ? 'Cash or Bank and the payment date are confirmed when you record.' : 'No balance remains on this bill.' }}
                    </p>
                </div>
            </x-slot:footer>
        </x-drawer>
    @endif
</div>
