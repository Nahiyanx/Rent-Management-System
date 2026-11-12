<?php

namespace App\Models;

use Database\Factories\RentBillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenancy_id',
    'billing_month',
    'house_rent',
    'electricity',
    'gas',
    'parking',
    'service_charge',
    'other_charges',
    'total_due',
])]
class RentBill extends Model
{
    /** @use HasFactory<RentBillFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'house_rent' => 'decimal:2',
            'electricity' => 'decimal:2',
            'gas' => 'decimal:2',
            'parking' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'other_charges' => 'decimal:2',
            'total_due' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Tenancy, $this>
     */
    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    /**
     * @return HasMany<RentPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    /**
     * The charge amounts that make up a bill, keyed by bill column.
     *
     * @return array{house_rent: float, electricity: float, gas: float, parking: float, service_charge: float, other_charges: float}
     */
    public function charges(): array
    {
        return [
            'house_rent' => (float) $this->house_rent,
            'electricity' => (float) $this->electricity,
            'gas' => (float) $this->gas,
            'parking' => (float) $this->parking,
            'service_charge' => (float) $this->service_charge,
            'other_charges' => (float) $this->other_charges,
        ];
    }

    /**
     * @param  array<string, int|float|string|null>  $charges
     */
    public static function totalFrom(array $charges): float
    {
        return round(array_sum(array_map(
            static fn (int|float|string|null $amount): float => (float) $amount,
            $charges,
        )), 2);
    }

    public function recalculateTotal(): void
    {
        $this->total_due = self::totalFrom($this->charges());
    }

    public function paidAmount(): float
    {
        $sum = $this->payments_sum_amount ?? $this->payments()->sum('amount');

        return round((float) $sum, 2);
    }

    public function balance(): float
    {
        return max(round((float) $this->total_due - $this->paidAmount(), 2), 0);
    }

    public function isLocked(): bool
    {
        return $this->payments()->exists();
    }

    public function status(): string
    {
        if ((float) $this->total_due <= 0) {
            return 'Paid';
        }

        $paid = $this->paidAmount();

        if ($paid <= 0) {
            return 'Unpaid';
        }

        return $paid + 0.005 >= (float) $this->total_due ? 'Paid' : 'Partially Paid';
    }
}
