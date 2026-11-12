<?php

namespace App\Models;

use Database\Factories\BuildingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'address'])]
class Building extends Model
{
    /** @use HasFactory<BuildingFactory> */
    use HasFactory;

    /**
     * @return HasMany<Flat, $this>
     */
    public function flats(): HasMany
    {
        return $this->hasMany(Flat::class);
    }
}
