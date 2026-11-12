<?php

namespace App\Livewire;

use App\Models\Building;
use App\Models\Flat;
use App\Models\Tenancy;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tenants')]
class Tenants extends Component
{
    public string $search = '';

    public bool $showTenantModal = false;

    public bool $showMoveOutModal = false;

    public ?int $editingTenantId = null;

    public ?int $moveOutTenantId = null;

    public string $moveOutDate = '';

    public string $moveOutTenantName = '';

    public ?string $statusMessage = null;

    /**
     * @var array{name: string, contact_number: string, building_id: string, flat_id: string, rent_start: string, advance_amount: int|float|string, house_rent: int|float|string, gas_charge: int|float|string, parking_charge: int|float|string, service_charge: int|float|string, notes: string}
     */
    public array $tenantForm = [
        'name' => '',
        'contact_number' => '',
        'building_id' => '',
        'flat_id' => '',
        'rent_start' => '',
        'advance_amount' => '',
        'house_rent' => '',
        'gas_charge' => '',
        'parking_charge' => '',
        'service_charge' => '',
        'notes' => '',
    ];

    public function createTenant(): void
    {
        $this->resetValidation();
        $this->statusMessage = null;
        $this->editingTenantId = null;
        $this->tenantForm = [
            'name' => '',
            'contact_number' => '',
            'building_id' => '',
            'flat_id' => '',
            'rent_start' => now()->toDateString(),
            'advance_amount' => '',
            'house_rent' => '',
            'gas_charge' => '1080',
            'parking_charge' => '',
            'service_charge' => '',
            'notes' => '',
        ];
        $this->showTenantModal = true;
    }

    public function editTenant(int $tenantId): void
    {
        $tenant = Tenant::query()->with('activeTenancy.flat')->find($tenantId);

        if ($tenant === null) {
            return;
        }

        $this->resetValidation();
        $this->statusMessage = null;
        $this->editingTenantId = $tenantId;

        $tenancy = $tenant->activeTenancy;

        $this->tenantForm = [
            'name' => $tenant->name,
            'contact_number' => $tenant->contact_number,
            'building_id' => $tenancy !== null ? (string) $tenancy->flat->building_id : '',
            'flat_id' => $tenancy !== null ? (string) $tenancy->flat_id : '',
            'rent_start' => $tenancy !== null ? $tenancy->starts_on->toDateString() : '',
            'advance_amount' => $tenancy !== null ? (string) (float) $tenancy->advance_amount : '',
            'house_rent' => $tenancy !== null ? (string) (float) $tenancy->house_rent : '',
            'gas_charge' => $tenancy !== null ? (string) (float) $tenancy->gas_charge : '',
            'parking_charge' => $tenancy !== null ? (string) (float) $tenancy->parking_charge : '',
            'service_charge' => $tenancy !== null ? (string) (float) $tenancy->service_charge : '',
            'notes' => $tenant->notes ?? '',
        ];

        $this->showTenantModal = true;
    }

    public function updatedTenantFormBuildingId(): void
    {
        $this->tenantForm['flat_id'] = '';
    }

    public function saveTenant(): void
    {
        $this->validate([
            'tenantForm.name' => ['required', 'string', 'max:120'],
            'tenantForm.contact_number' => ['required', 'string', 'max:30'],
            'tenantForm.building_id' => ['required', 'integer', 'exists:buildings,id'],
            'tenantForm.flat_id' => [
                'required',
                'integer',
                Rule::exists('flats', 'id')->where('building_id', $this->tenantForm['building_id']),
            ],
            'tenantForm.rent_start' => ['required', 'date_format:Y-m-d'],
            'tenantForm.advance_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'tenantForm.house_rent' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'tenantForm.gas_charge' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'tenantForm.parking_charge' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'tenantForm.service_charge' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'tenantForm.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = $this->editingTenantId !== null
            ? Tenant::query()->with('activeTenancy')->find($this->editingTenantId)
            : new Tenant;

        if ($tenant === null) {
            return;
        }

        $flatId = (int) $this->tenantForm['flat_id'];
        $currentTenancy = $tenant->exists ? $tenant->activeTenancy : null;

        $conflict = Tenancy::query()
            ->where('flat_id', $flatId)
            ->whereNotNull('active_marker')
            ->when($currentTenancy !== null, fn ($query) => $query->where('id', '!=', $currentTenancy->id))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'tenantForm.flat_id' => 'This flat already has an active tenancy. Choose a vacant flat.',
            ]);
        }

        $tenancyAttributes = [
            'house_rent' => round((float) $this->tenantForm['house_rent'], 2),
            'gas_charge' => round((float) ($this->tenantForm['gas_charge'] ?: 0), 2),
            'parking_charge' => round((float) ($this->tenantForm['parking_charge'] ?: 0), 2),
            'service_charge' => round((float) ($this->tenantForm['service_charge'] ?: 0), 2),
            'advance_amount' => round((float) ($this->tenantForm['advance_amount'] ?: 0), 2),
            'starts_on' => $this->tenantForm['rent_start'],
        ];

        $wasEditing = $this->editingTenantId !== null;

        DB::transaction(function () use ($tenant, $currentTenancy, $flatId, $tenancyAttributes): void {
            $tenant->fill([
                'name' => trim($this->tenantForm['name']),
                'contact_number' => trim($this->tenantForm['contact_number']),
                'notes' => trim($this->tenantForm['notes']) ?: null,
            ]);
            $tenant->save();

            if ($currentTenancy !== null && (int) $currentTenancy->flat_id === $flatId) {
                $currentTenancy->update($tenancyAttributes);

                return;
            }

            if ($currentTenancy !== null) {
                // Moving to a different flat closes the old tenancy so past
                // bills stay attached to the flat they were billed for.
                $currentTenancy->update([
                    'ends_on' => now()->toDateString(),
                    'active_marker' => null,
                ]);
            }

            Tenancy::create($tenancyAttributes + [
                'tenant_id' => $tenant->id,
                'flat_id' => $flatId,
                'ends_on' => null,
                'active_marker' => 1,
            ]);
        });

        $this->showTenantModal = false;
        $this->editingTenantId = null;
        $this->tenantForm = [
            'name' => '',
            'contact_number' => '',
            'building_id' => '',
            'flat_id' => '',
            'rent_start' => '',
            'advance_amount' => '',
            'house_rent' => '',
            'gas_charge' => '',
            'parking_charge' => '',
            'service_charge' => '',
            'notes' => '',
        ];
        $this->statusMessage = $wasEditing ? 'Tenant updated.' : 'Tenant added.';
    }

    public function closeTenantModal(): void
    {
        $this->showTenantModal = false;
    }

    public function openMoveOut(int $tenantId): void
    {
        $tenant = Tenant::query()->with('activeTenancy')->find($tenantId);

        if ($tenant === null || $tenant->activeTenancy === null) {
            return;
        }

        $this->resetValidation();
        $this->statusMessage = null;
        $this->moveOutTenantId = $tenantId;
        $this->moveOutTenantName = $tenant->name;
        $this->moveOutDate = now()->toDateString();
        $this->showMoveOutModal = true;
    }

    public function confirmMoveOut(): void
    {
        $this->validate([
            'moveOutDate' => ['required', 'date_format:Y-m-d'],
        ]);

        $tenant = Tenant::query()->with('activeTenancy')->find($this->moveOutTenantId);

        if ($tenant === null || $tenant->activeTenancy === null) {
            $this->showMoveOutModal = false;

            return;
        }

        $tenancy = $tenant->activeTenancy;

        if (Carbon::parse($this->moveOutDate)->lt($tenancy->starts_on)) {
            throw ValidationException::withMessages([
                'moveOutDate' => 'The move-out date cannot be before the tenancy started.',
            ]);
        }

        $tenancy->update([
            'ends_on' => $this->moveOutDate,
            'active_marker' => null,
        ]);

        $this->showMoveOutModal = false;
        $this->moveOutTenantId = null;
        $this->statusMessage = 'Tenancy ended. Rent history has been preserved.';
    }

    public function closeMoveOutModal(): void
    {
        $this->showMoveOutModal = false;
    }

    public function render(): View
    {
        $search = trim($this->search);

        $tenants = Tenant::query()
            ->with([
                'activeTenancy.flat.building',
                'tenancies.flat.building',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhereHas('activeTenancy.flat', fn ($flat) => $flat->where('unit_number', 'like', "%{$search}%"))
                        ->orWhereHas('activeTenancy.flat.building', fn ($building) => $building->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->get();

        $activeTenancy = $this->editingTenantId !== null
            ? Tenant::query()->with('activeTenancy')->find($this->editingTenantId)?->activeTenancy
            : null;

        return view('livewire.tenants', [
            'tenants' => $tenants,
            'totalTenants' => Tenant::query()->count(),
            'buildings' => Building::query()->orderBy('name')->get(),
            'flatOptions' => $this->flatOptions($activeTenancy),
            'isEditing' => $this->editingTenantId !== null,
            'activeTenancy' => $activeTenancy,
        ]);
    }

    /**
     * @return Collection<int, Flat>
     */
    private function flatOptions(?Tenancy $activeTenancy): Collection
    {
        $buildingId = $this->tenantForm['building_id'];

        if ($buildingId === '') {
            return collect();
        }

        return Flat::query()
            ->where('building_id', $buildingId)
            ->whereDoesntHave('activeTenancy', function ($query) use ($activeTenancy): void {
                $query->when($activeTenancy !== null, fn ($query) => $query->where('id', '!=', $activeTenancy->id));
            })
            ->get()
            ->sortBy('unit_number', SORT_NATURAL)
            ->values();
    }
}
