<div class="space-y-6 lg:space-y-8">
    <x-page-header
        eyebrow="Portfolio · {{ $buildings->count() }} {{ Str::plural('building', $buildings->count()) }}"
        title="Buildings"
        description="The places you look after, gathered in one quiet view."
    >
        <button type="button" wire:click="createBuilding" class="btn btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            Add building
        </button>
    </x-page-header>

    <x-flash :message="$statusMessage" />

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-5 text-[10px] uppercase tracking-[0.15em] text-espresso/35">
            <span><strong class="font-medium text-espresso/65">{{ $buildings->count() }}</strong> buildings</span>
            <span class="h-3 w-px bg-espresso/10"></span>
            <span><strong class="font-medium text-espresso/65">{{ $totalFlats }}</strong> flats</span>
        </div>

        <p class="text-[9px] uppercase tracking-[0.18em] text-espresso/25">Registered portfolio</p>
    </div>

    <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($buildings as $building)
            <article
                wire:key="building-{{ $building->id }}"
                class="card reveal group relative overflow-hidden p-6 transition-all duration-200 hover:-translate-y-0.5 hover:border-espresso/[0.13] hover:shadow-lift"
                style="animation-delay: {{ 50 + ($loop->index * 55) }}ms"
            >
                <div class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full border border-espresso/[0.04] transition-transform duration-300 group-hover:scale-110"></div>

                <div class="relative flex items-start justify-between">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-espresso text-cream/75">
                        <x-icon name="building-2" class="h-5 w-5" />
                    </span>

                    <button
                        type="button"
                        wire:click="editBuilding({{ $building->id }})"
                        class="icon-btn"
                        aria-label="Edit {{ $building->name }}"
                    >
                        <x-icon name="pencil-line" class="h-4 w-4" />
                    </button>
                </div>

                <p class="mt-6 text-[9px] font-medium uppercase tracking-[0.2em] text-espresso/25">
                    Building {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                </p>

                <h2 class="mt-1 text-[20px] font-medium tracking-[-0.035em] text-espresso">
                    {{ $building->name }}
                </h2>

                <p class="mt-2 flex items-start gap-1.5 text-[12px] leading-relaxed text-espresso/40">
                    <x-icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    {{ $building->address }}
                </p>

                <div class="mt-6 flex items-end justify-between border-t border-espresso/[0.07] pt-4">
                    <div>
                        <p class="text-[9px] font-medium uppercase tracking-[0.16em] text-espresso/25">Total flats</p>
                        <p class="mt-1 text-[19px] font-medium tracking-[-0.03em] text-espresso">{{ $building->flats_count }}</p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 text-[9px] uppercase tracking-[0.14em] text-espresso/30">
                        <span class="h-1 w-1 rounded-full bg-wine/50"></span>
                        {{ $building->occupied_flats_count }} occupied
                    </span>
                </div>
            </article>
        @empty
            <div class="card col-span-full px-6 py-16 text-center">
                <div class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-espresso/[0.05] text-espresso/35">
                    <x-icon name="building-2" class="h-5 w-5" />
                </div>
                <p class="mt-4 text-[14px] font-medium text-espresso/70">No buildings yet</p>
                <p class="mt-1 text-[12px] text-espresso/40">Add your first property to start registering flats.</p>
            </div>
        @endforelse
    </section>

    <x-modal
        property="showBuildingModal"
        eyebrow="Buildings"
        :title="$isEditing ? 'Edit building' : 'Add a building'"
        :description="$isEditing
            ? 'Update the property details or register additional flats and units.'
            : 'Add a property to your personal portfolio and register its flats.'"
    >
        <form id="building-form" wire:submit="saveBuilding" class="space-y-5">
            <div>
                <label for="building-name" class="label">Building name</label>
                <input
                    id="building-name"
                    type="text"
                    wire:model="buildingForm.name"
                    class="input"
                    placeholder="e.g. Shanti Nibash"
                    autocomplete="off"
                >
                @error('buildingForm.name')
                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="building-address" class="label">Address</label>
                <textarea
                    id="building-address"
                    rows="2"
                    wire:model="buildingForm.address"
                    class="input resize-none leading-relaxed"
                    placeholder="House, road, area, city"
                ></textarea>
                @error('buildingForm.address')
                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="building-units" class="label">Flats / unit numbers</label>
                <textarea
                    id="building-units"
                    rows="3"
                    wire:model="buildingForm.units"
                    class="input resize-none leading-relaxed"
                    placeholder="1A, 1B, 2A, 2B"
                ></textarea>
                <p class="mt-2 text-[10.5px] leading-relaxed text-espresso/35">
                    Separate unit numbers with commas. Removing a unit with tenancy history is not allowed.
                </p>
                @error('buildingForm.units')
                    <p class="mt-1.5 text-[11px] text-wine">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-start gap-2.5 rounded-2xl border border-wine/[0.1] bg-wine/[0.04] px-4 py-3.5 text-[11px] leading-relaxed text-espresso/50">
                <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 text-wine/60" />
                <span>Each unit is registered against this building. Only vacant units can be removed.</span>
            </div>

        </form>

        <x-slot:footer>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[9px] uppercase tracking-[0.16em] text-espresso/30">Saved to your portfolio</p>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="closeBuildingModal" class="btn btn-secondary">Cancel</button>
                    <button type="submit" form="building-form" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveBuilding">
                        <x-icon name="{{ $isEditing ? 'check' : 'plus' }}" class="h-4 w-4" />
                        <span wire:loading.remove wire:target="saveBuilding">{{ $isEditing ? 'Save changes' : 'Save building' }}</span>
                        <span wire:loading wire:target="saveBuilding">Saving…</span>
                    </button>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
