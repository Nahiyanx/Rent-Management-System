<?php

namespace App\Models;

use Database\Factories\TenancyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'tenant_id',
    'flat_id',
    'house_rent',
    'gas_charge',
    'parking_charge',
    'service_charge',
    'starts_on',
    'ends_on',
    'active_marker',
])]
class Tenancy extends Model
{
    /** @use HasFactory<TenancyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'house_rent' => 'decimal:2',
            'gas_charge' => 'decimal:2',
            'parking_charge' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'active_marker' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<Flat, $this>
     */
    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class);
    }

    /**
     * @return HasMany<RentBill, $this>
     */
    public function rentBills(): HasMany
    {
        return $this->hasMany(RentBill::class);
    }

    /**
     * @param  Builder<Tenancy>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNotNull('active_marker');
    }

    public function isActive(): bool
    {
        return $this->active_marker === 1;
    }

    /**
     * Whether this tenancy was in place at any point during the given month.
     */
    public function coversMonth(string $month): bool
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        if ($this->starts_on->greaterThan($end)) {
            return false;
        }

        return $this->ends_on === null || $this->ends_on->greaterThanOrEqualTo($start);
    }
}
