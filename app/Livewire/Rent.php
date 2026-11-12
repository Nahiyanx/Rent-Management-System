<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasMonth;
use App\Models\Building;
use App\Models\RentBill;
use App\Models\Tenancy;
use App\Support\RentBilling;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Rent')]
class Rent extends Component
{
    use HasMonth;

    public string $buildingFilter = 'all';

    public string $statusFilter = 'all';

    public bool $showBillDrawer = false;

    public ?int $selectedTenancyId = null;

    public ?string $statusMessage = null;

    /**
     * @var array{house_rent: int|float|string, electricity: int|float|string, gas: int|float|string, parking: int|float|string, service_charge: int|float|string, other_charges: int|float|string}
     */
    public array $billForm = [
        'house_rent' => '',
        'electricity' => '',
        'gas' => '',
        'parking' => '',
        'service_charge' => '',
        'other_charges' => '',
    ];

    /**
     * @var array{method: string, paid_on: string, amount: int|float|string}
     */
    public array $paymentForm = [
        'method' => 'Cash',
        'paid_on' => '',
        'amount' => '',
    ];

    public function mount(): void
    {
        RentBilling::ensureForMonth($this->month);
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonthNoOverflow()->format('Y-m');
        $this->closeBill();
        RentBilling::ensureForMonth($this->month);
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonthNoOverflow()->format('Y-m');
        $this->closeBill();
        RentBilling::ensureForMonth($this->month);
    }

    public function openBill(int $tenancyId): void
    {
        $tenancy = Tenancy::query()
            ->with(['rentBills' => fn ($query) => $query->where('billing_month', $this->month)])
            ->find($tenancyId);

        if ($tenancy === null) {
            return;
        }

        $bill = $tenancy->rentBills->first() ?? RentBilling::billFor($tenancy, $this->month);
        $bill->loadSum('payments', 'amount')->load('payments');

        $this->resetValidation();
        $this->statusMessage = null;
        $this->selectedTenancyId = $tenancyId;
        $this->billForm = [
            'house_rent' => (string) (float) $bill->house_rent,
            'electricity' => (string) (float) $bill->electricity,
            'gas' => (string) (float) $bill->gas,
            'parking' => (string) (float) $bill->parking,
            'service_charge' => (string) (float) $bill->service_charge,
            'other_charges' => (string) (float) $bill->other_charges,
        ];

        $balance = $bill->balance();
        $this->paymentForm = [
            'method' => 'Cash',
            'paid_on' => now()->toDateString(),
            'amount' => $balance > 0 ? (string) $balance : '',
        ];

        $this->showBillDrawer = true;
    }

    public function closeBill(): void
    {
        $this->showBillDrawer = false;
        $this->selectedTenancyId = null;
        $this->statusMessage = null;
    }

    public function updatedBuildingFilter(): void
    {
        $this->closeBill();
    }

    public function updatedStatusFilter(): void
    {
        $this->closeBill();
    }

    public function saveBill(): void
    {
        $this->validate([
            'billForm.house_rent' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'billForm.electricity' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'billForm.gas' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'billForm.parking' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'billForm.service_charge' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'billForm.other_charges' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        $bill = $this->selectedBill();

        if ($bill === null) {
            return;
        }

        if ($bill->payments->isNotEmpty()) {
            $this->addError('billForm.house_rent', 'This invoice is locked because payments have already been recorded.');

            return;
        }

        $charges = [
            'house_rent' => round((float) $this->billForm['house_rent'], 2),
            'electricity' => round((float) $this->billForm['electricity'], 2),
            'gas' => round((float) $this->billForm['gas'], 2),
            'parking' => round((float) $this->billForm['parking'], 2),
            'service_charge' => round((float) $this->billForm['service_charge'], 2),
            'other_charges' => round((float) $this->billForm['other_charges'], 2),
        ];

        $bill->update($charges + ['total_due' => RentBill::totalFrom($charges)]);

        $this->billForm = array_map(
            static fn (float $amount): string => (string) $amount,
            $charges,
        );
        $this->paymentForm['amount'] = $bill->balance() > 0 ? (string) $bill->balance() : '';
        $this->statusMessage = 'Bill updated.';
    }

    public function fillFullAmount(): void
    {
        $bill = $this->selectedBill();

        if ($bill === null) {
            return;
        }

        $this->paymentForm['amount'] = $bill->balance() > 0 ? (string) $bill->balance() : '';
    }

    public function recordPayment(): void
    {
        $this->validate([
            'paymentForm.method' => ['required', Rule::in(['Cash', 'Bank'])],
            'paymentForm.paid_on' => ['required', 'date_format:Y-m-d'],
            'paymentForm.amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
        ]);

        $bill = $this->selectedBill();

        if ($bill === null) {
            return;
        }

        $balance = $bill->balance();
        $amount = round((float) $this->paymentForm['amount'], 2);

        if ($balance <= 0) {
            throw ValidationException::withMessages([
                'paymentForm.amount' => 'This bill has already been settled in full.',
            ]);
        }

        if ($amount > $balance) {
            throw ValidationException::withMessages([
                'paymentForm.amount' => 'The amount cannot exceed the remaining balance.',
            ]);
        }

        $bill->payments()->create([
            'amount' => $amount,
            'method' => $this->paymentForm['method'],
            'paid_on' => $this->paymentForm['paid_on'],
        ]);

        $bill->unsetRelation('payments');
        $bill->loadSum('payments', 'amount')->load('payments');

        $this->paymentForm['amount'] = $bill->balance() > 0 ? (string) $bill->balance() : '';
        $this->statusMessage = $bill->status() === 'Paid' ? 'Payment recorded. Bill settled.' : 'Partial payment recorded.';
    }

    private function selectedBill(): ?RentBill
    {
        if ($this->selectedTenancyId === null) {
            return null;
        }

        $tenancy = Tenancy::query()
            ->with(['rentBills' => fn ($query) => $query->where('billing_month', $this->month)])
            ->find($this->selectedTenancyId);

        $bill = $tenancy?->rentBills->first();

        if ($bill === null) {
            return null;
        }

        return $bill->loadSum('payments', 'amount')->load('payments');
    }

    public function render(): View
    {
        $monthStart = $this->monthStart();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $tenancies = Tenancy::query()
            ->with([
                'tenant',
                'flat.building',
                'rentBills' => fn ($query) => $query->where('billing_month', $this->month)
                    ->withSum('payments', 'amount')
                    ->with('payments'),
            ])
            ->where('starts_on', '<=', $monthEnd)
            ->where(function ($query) use ($monthStart): void {
                $query->whereNull('ends_on')->orWhere('ends_on', '>=', $monthStart);
            })
            ->when($this->buildingFilter !== 'all', function ($query): void {
                $query->whereHas('flat', fn ($flat) => $flat->where('building_id', $this->buildingFilter));
            })
            ->get()
            ->sortBy(
                fn (Tenancy $tenancy): string => $tenancy->flat->building->name.' '.$tenancy->flat->unit_number,
                SORT_NATURAL,
            )
            ->values();

        $bills = $tenancies
            ->map(fn (Tenancy $tenancy): ?RentBill => $tenancy->rentBills->first())
            ->filter()
            ->values();

        $statusOf = fn (Tenancy $tenancy): string => $tenancy->rentBills->first()?->status() ?? 'Not billed';

        $statusCounts = ['all' => $tenancies->count()];

        foreach ($tenancies as $tenancy) {
            $status = $statusOf($tenancy);
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }

        foreach (['Paid', 'Unpaid', 'Partially Paid', 'Not billed'] as $status) {
            $statusCounts[$status] ??= 0;
        }

        $visibleTenancies = $this->statusFilter === 'all'
            ? $tenancies
            : $tenancies->filter(fn (Tenancy $tenancy): bool => $statusOf($tenancy) === $this->statusFilter)->values();

        $billed = round((float) $bills->sum(fn (RentBill $bill): float => (float) $bill->total_due), 2);
        $collected = round((float) $bills->sum(fn (RentBill $bill): float => $bill->paidAmount()), 2);
        $outstanding = max(round($billed - $collected, 2), 0);

        $selectedTenancy = $tenancies->firstWhere('id', $this->selectedTenancyId);
        $selectedBill = $selectedTenancy?->rentBills->first();

        return view('livewire.rent', [
            'tenancies' => $visibleTenancies,
            'buildings' => Building::query()->orderBy('name')->get(),
            'statusCounts' => $statusCounts,
            'summary' => [
                'billed' => $billed,
                'collected' => $collected,
                'outstanding' => $outstanding,
                'settled' => $bills->filter(fn (RentBill $bill): bool => $bill->status() === 'Paid')->count(),
                'total' => $bills->count(),
                'progress' => $billed > 0 ? min(100, (int) round($collected / $billed * 100)) : 0,
            ],
            'selectedTenancy' => $selectedTenancy,
            'selectedBill' => $selectedBill,
            'billTotal' => RentBill::totalFrom($this->billForm),
            'monthLabel' => $this->monthLabel(),
            'monthShortLabel' => $this->monthShortLabel(),
            'paymentHistory' => $selectedBill?->payments->sortByDesc('paid_on')->values() ?? new Collection,
        ]);
    }
}
