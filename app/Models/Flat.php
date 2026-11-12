<?php

namespace App\Models;

use Database\Factories\FlatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['building_id', 'unit_number'])]
class Flat extends Model
{
    /** @use HasFactory<FlatFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Building, $this>
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

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
}
