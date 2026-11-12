<div class="space-y-6 lg:space-y-8">
    <x-page-header
        eyebrow="Directory · {{ $totalTenants }} {{ Str::plural('tenant', $totalTenants) }}"
        title="Tenants"
        description="The people who make these buildings home."
    >
        <button type="button" wire:click="createTenant" class="btn btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            Add tenant
        </button>
    </x-page-header>

    <x-flash :message="$statusMessage" />

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-[320px]">
            <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-espresso/30" />
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                class="input pl-10"
                placeholder="Search tenant, building or flat"
                aria-label="Search tenants"
                autocomplete="off"
            >
        </div>

        <p class="text-[9.5px] uppercase tracking-[0.16em] text-espresso/30">
            Showing {{ $tenants->count() }} of {{ $totalTenants }} tenants
        </p>
    </div>

    <section class="card overflow-hidden">
        <div class="hidden grid-cols-[minmax(0,1.5fr)_minmax(0,1.25fr)_0.75fr_1fr_84px] gap-4 border-b border-espresso/[0.07] px-6 py-3.5 text-[9px] font-medium uppercase tracking-[0.18em] text-espresso/30 md:grid">
            <span>Tenant</span>
            <span>Building &amp; flat</span>
            <span>Monthly rent</span>
            <span>Contact</span>
            <span></span>
        </div>

        @forelse ($tenants as $tenant)
            @php
                $tenancy = $tenant->activeTenancy ?? $tenant->latestTenancy();
                $isActive = $tenant->activeTenancy !== null;
            @endphp

            <div
                wire:key="tenant-{{ $tenant->id }}"
                class="group grid grid-cols-2 gap-x-4 gap-y-4 border-b border-espresso/[0.06] px-5 py-4 transition-colors duration-200 last:border-0 hover:bg-[#fffdf2]/45 sm:px-6 md:grid-cols-[minmax(0,1.5fr)_minmax(0,1.25fr)_0.75fr_1fr_84px] md:items-center md:gap-4 md:py-3.5"
            >
                <div class="col-span-2 flex items-center gap-3.5 md:col-span-1">
                    <div @class([
                        'grid h-10 w-10 shrink-0 place-items-center rounded-full text-[10px] font-medium tracking-[0.08em]',
                        'bg-espresso text-cream' => $isActive,
                        'bg-espresso/[0.07] text-espresso/45' => ! $isActive,
                    ])>
                        {{ $tenant->initials() }}
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-[13.5px] font-medium text-espresso">{{ $tenant->name }}</p>
                            @unless ($isActive)
                                <span class="stage-tag shrink-0">Former</span>
                            @endunless
                        </div>
                        <p class="mt-0.5 text-[10.5px] text-espresso/35">
                            @if ($isActive)
                                Tenant since {{ $tenant->activeTenancy->starts_on->format('M Y') }}
                            @elseif ($tenancy?->ends_on)
                                Moved out {{ $tenancy->ends_on->format('M Y') }}
                            @else
                                No active tenancy
                            @endif
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-[8.5px] font-medium uppercase tracking-[0.15em] text-espresso/25 md:hidden">Building</p>
                    @if ($tenancy?->flat?->building)
                        <p class="mt-0.5 truncate text-[12px] text-espresso/65">{{ $tenancy->flat->building->name }}</p>
                        <p class="text-[10.5px] text-espresso/35">Flat {{ $tenancy->flat->unit_number }}</p>
                    @else
                        <p class="mt-0.5 text-[12px] text-espresso/35">Unassigned</p>
                    @endif
                </div>

                <div>
                    <p class="text-[8.5px] font-medium uppercase tracking-[0.15em] text-espresso/25 md:hidden">Monthly rent</p>
                    <p class="mt-0.5 text-[13px] font-medium text-espresso">
                        @if ($tenancy)
                            <x-money :amount="$tenancy->house_rent" />
                        @else
                            —
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-[8.5px] font-medium uppercase tracking-[0.15em] text-espresso/25 md:hidden">Contact</p>
                    <a href="tel:{{ str_replace(' ', '', $tenant->contact_number) }}" class="mt-0.5 inline-block text-[11.5px] text-espresso/50 transition-colors duration-200 hover:text-wine">
                        {{ $tenant->contact_number }}
                    </a>
                </div>

                <div class="flex justify-end gap-0.5">
                    @if ($isActive)
                        <button
                            type="button"
                            wire:click="openMoveOut({{ $tenant->id }})"
                            class="icon-btn"
                            aria-label="End tenancy for {{ $tenant->name }}"
                            title="End tenancy"
                        >
                            <x-icon name="door-open" class="h-4 w-4" />
                        </button>
                    @endif

                    <button
                        type="button"
                        wire:click="editTenant({{ $tenant->id }})"
                        class="icon-btn"
                        aria-label="Edit {{ $tenant->name }}"
                    >
                        <x-icon name="pencil-line" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-espresso/[0.05] text-espresso/35">
                    <x-icon name="search" class="h-5 w-5" />
                </div>
                <p class="mt-4 text-[14px] font-medium text-espresso/70">No tenants found</p>
                <p class="mt-1 text-[12px] text-espresso/40">Try a different name, building or flat number.</p>
            </div>
        @endforelse
    </section>

    <x-modal
        property="showTenantModal"
        eyebrow="Tenants"
        :title="$isEditing ? 'Edit tenant' : 'Add a tenant'"
        :description="$isEditing
            ? 'Update the tenant, their flat and the monthly charge defaults.'
            : 'Assign a tenant to a vacant flat and set their monthly charge defaults.'"
    >
        <form id="tenant-form" wire:submit="saveTenant" class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tenant-name" class="label">Full name</label>
                    <input
                        id="tenant-name"
                        type="text"
                        wire:model="tenantForm.name"
                        class="input"
                        placeholder="e.g. Arif Rahman"
                        autocomplete="off"
                    >
                    @error('tenantForm.name')
                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tenant-phone" class="label">Contact number</label>
                    <input
                        id="tenant-phone"
                        type="tel"
                        wire:model="tenantForm.contact_number"
                        class="input"
                        placeholder="01XXX XXXXXX"
                        autocomplete="off"
                    >
                    @error('tenantForm.contact_number')
                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tenant-building" class="label">Building</label>
                    <div class="relative">
                        <select id="tenant-building" wire:model.live="tenantForm.building_id" class="select">
                            <option value="">Select a building</option>
                            @foreach ($buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-espresso/30" />
                    </div>
                    @error('tenantForm.building_id')
                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tenant-flat" class="label">Flat</label>
                    <div class="relative">
                        <select id="tenant-flat" wire:model="tenantForm.flat_id" class="select" @disabled($flatOptions->isEmpty())>
                            <option value="">{{ $tenantForm['building_id'] === '' ? 'Choose a building first' : 'Select a vacant flat' }}</option>
                            @foreach ($flatOptions as $flat)
                                <option value="{{ $flat->id }}">Flat {{ $flat->unit_number }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-espresso/30" />
                    </div>
                    <p class="mt-1.5 text-[10px] text-espresso/30">Only vacant flats are listed.</p>
                    @error('tenantForm.flat_id')
                        <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="rounded-2xl border border-espresso/[0.08] bg-espresso/[0.022] p-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[9px] font-medium uppercase tracking-[0.17em] text-espresso/35">Monthly charge defaults</p>
                    <span class="text-[9px] uppercase tracking-[0.14em] text-espresso/25">Editable per month</span>
                </div>

                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="tenant-rent" class="label">House rent</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                            <input
                                id="tenant-rent"
                                type="number"
                                min="0"
                                step="0.01"
                                inputmode="decimal"
                                wire:model="tenantForm.house_rent"
                                class="input pl-9"
                                placeholder="0"
                            >
                        </div>
                        @error('tenantForm.house_rent')
                            <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tenant-gas" class="label">Gas bill</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                            <input
                                id="tenant-gas"
                                type="number"
                                min="0"
                                step="0.01"
                                inputmode="decimal"
                                wire:model="tenantForm.gas_charge"
                                class="input pl-9"
                                placeholder="0"
                            >
                        </div>
                        @error('tenantForm.gas_charge')
                            <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tenant-parking" class="label">Car parking</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                            <input
                                id="tenant-parking"
                                type="number"
                                min="0"
                                step="0.01"
                                inputmode="decimal"
                                wire:model="tenantForm.parking_charge"
                                class="input pl-9"
                                placeholder="0"
                            >
                        </div>
                        @error('tenantForm.parking_charge')
                            <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tenant-service" class="label">Service charge</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] text-espresso/30">৳</span>
                            <input
                                id="tenant-service"
                                type="number"
                                min="0"
                                step="0.01"
                                inputmode="decimal"
                                wire:model="tenantForm.service_charge"
                                class="input pl-9"
                                placeholder="0"
                            >
                        </div>
                        @error('tenantForm.service_charge')
                            <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <label for="tenant-notes" class="label">Notes <span class="normal-case tracking-normal text-espresso/25">(optional)</span></label>
                <textarea
                    id="tenant-notes"
                    rows="2"
                    wire:model="tenantForm.notes"
                    class="input resize-none leading-relaxed"
                    placeholder="Anything worth remembering about this tenancy"
                ></textarea>
                @error('tenantForm.notes')
                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                @enderror
            </div>

        </form>

        <x-slot:footer>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[9px] uppercase tracking-[0.16em] text-espresso/30">Tenant &amp; tenancy saved together</p>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="closeTenantModal" class="btn btn-secondary">Cancel</button>
                    <button type="submit" form="tenant-form" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveTenant">
                        <x-icon name="{{ $isEditing ? 'check' : 'plus' }}" class="h-4 w-4" />
                        <span wire:loading.remove wire:target="saveTenant">{{ $isEditing ? 'Save changes' : 'Save tenant' }}</span>
                        <span wire:loading wire:target="saveTenant">Saving…</span>
                    </button>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>

    <x-modal
        property="showMoveOutModal"
        eyebrow="Tenancy"
        title="End tenancy"
        :description="'Close the current tenancy for '.$moveOutTenantName.'. Past rent bills stay exactly as they are.'"
        max-width="sm:max-w-md"
    >
        <form id="move-out-form" wire:submit="confirmMoveOut" class="space-y-5">
            <div>
                <label for="move-out-date" class="label">Move-out date</label>
                <input
                    id="move-out-date"
                    type="date"
                    wire:model="moveOutDate"
                    class="input"
                >
                @error('moveOutDate')
                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-start gap-2.5 rounded-2xl border border-wine/[0.1] bg-wine/[0.04] px-4 py-3.5 text-[11px] leading-relaxed text-espresso/50">
                <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 text-wine/60" />
                <span>The flat becomes vacant and can be assigned to another tenant. The tenant record and all rent history remain.</span>
            </div>

        </form>

        <x-slot:footer>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[9px] uppercase tracking-[0.16em] text-espresso/30">Rent history stays</p>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="closeMoveOutModal" class="btn btn-secondary">Cancel</button>
                    <button type="submit" form="move-out-form" class="btn btn-primary" wire:loading.attr="disabled" wire:target="confirmMoveOut">
                        <x-icon name="door-open" class="h-4 w-4" />
                        <span wire:loading.remove wire:target="confirmMoveOut">End tenancy</span>
                        <span wire:loading wire:target="confirmMoveOut">Ending…</span>
                    </button>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
