<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable(['name', 'contact_number', 'notes'])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    /**
     * @return HasMany<Tenancy, $this>
     */
    public function tenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class);
    }

    /**
     * @return HasOne<Tenancy, $this>
     */
    public function activeTenancy(): HasOne
    {
        return $this->hasOne(Tenancy::class)->whereNotNull('active_marker');
    }

    public function latestTenancy(): ?Tenancy
    {
        if ($this->relationLoaded('tenancies')) {
            return $this->tenancies
                ->sortByDesc(fn (Tenancy $tenancy): int => $tenancy->starts_on?->getTimestamp() ?? 0)
                ->first();
        }

        return $this->tenancies()->latest('starts_on')->first();
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
