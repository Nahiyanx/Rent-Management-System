<?php

namespace App\Livewire;

use App\Models\Building;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Buildings')]
class Buildings extends Component
{
    public bool $showBuildingModal = false;

    public ?int $editingBuildingId = null;

    public ?string $statusMessage = null;

    /**
     * @var array{name: string, address: string, units: string}
     */
    public array $buildingForm = [
        'name' => '',
        'address' => '',
        'units' => '',
    ];

    public function createBuilding(): void
    {
        $this->resetValidation();
        $this->statusMessage = null;
        $this->editingBuildingId = null;
        $this->buildingForm = ['name' => '', 'address' => '', 'units' => ''];
        $this->showBuildingModal = true;
    }

    public function editBuilding(int $buildingId): void
    {
        $building = Building::query()->with('flats')->find($buildingId);

        if ($building === null) {
            return;
        }

        $this->resetValidation();
        $this->statusMessage = null;
        $this->editingBuildingId = $buildingId;
        $this->buildingForm = [
            'name' => $building->name,
            'address' => $building->address,
            'units' => $building->flats
                ->sortBy('unit_number', SORT_NATURAL)
                ->pluck('unit_number')
                ->implode(', '),
        ];
        $this->showBuildingModal = true;
    }

    public function saveBuilding(): void
    {
        $this->validate([
            'buildingForm.name' => ['required', 'string', 'max:120'],
            'buildingForm.address' => ['required', 'string', 'max:255'],
            'buildingForm.units' => ['nullable', 'string', 'max:4000'],
        ]);

        $units = $this->parseUnits($this->buildingForm['units']);

        foreach ($units as $unit) {
            if (mb_strlen($unit) > 20) {
                throw ValidationException::withMessages([
                    'buildingForm.units' => 'Unit numbers may not be longer than 20 characters.',
                ]);
            }
        }

        $building = $this->editingBuildingId !== null
            ? Building::query()->find($this->editingBuildingId)
            : new Building;

        if ($building === null) {
            return;
        }

        DB::transaction(function () use ($building, $units): void {
            $building->fill([
                'name' => trim($this->buildingForm['name']),
                'address' => trim($this->buildingForm['address']),
            ]);
            $building->save();

            $existing = $building->flats()->get()->keyBy('unit_number');
            $keptIds = [];

            foreach ($units as $unit) {
                $flat = $existing->get($unit);

                if ($flat === null) {
                    $flat = $building->flats()->create(['unit_number' => $unit]);
                }

                $keptIds[] = $flat->id;
            }

            $building->flats()
                ->whereNotIn('id', $keptIds)
                ->whereDoesntHave('tenancies')
                ->delete();

            $blocked = $building->flats()
                ->whereNotIn('id', $keptIds)
                ->whereHas('tenancies')
                ->pluck('unit_number');

            if ($blocked->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'buildingForm.units' => 'These units have tenancy history and cannot be removed: '.$blocked->implode(', ').'.',
                ]);
            }
        });

        $wasEditing = $this->editingBuildingId !== null;

        $this->showBuildingModal = false;
        $this->editingBuildingId = null;
        $this->buildingForm = ['name' => '', 'address' => '', 'units' => ''];
        $this->statusMessage = $wasEditing ? 'Building updated.' : 'Building added.';
    }

    public function closeBuildingModal(): void
    {
        $this->showBuildingModal = false;
    }

    /**
     * @return array<int, string>
     */
    private function parseUnits(string $units): array
    {
        $parts = preg_split('/[,\n\r]+/', $units) ?: [];

        return collect($parts)
            ->map(static fn (string $unit): string => trim($unit))
            ->filter()
            ->unique(static fn (string $unit): string => mb_strtolower($unit))
            ->values()
            ->all();
    }

    public function render(): View
    {
        $buildings = Building::query()
            ->withCount('flats')
            ->withCount(['flats as occupied_flats_count' => function ($query): void {
                $query->whereHas('activeTenancy');
            }])
            ->orderBy('name')
            ->get();

        return view('livewire.buildings', [
            'buildings' => $buildings,
            'totalFlats' => $buildings->sum('flats_count'),
            'isEditing' => $this->editingBuildingId !== null,
        ]);
    }
}
