<?php

namespace Database\Factories;

use App\Models\Flat;
use App\Models\Tenancy;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenancy>
 */
class TenancyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'flat_id' => Flat::factory(),
            'house_rent' => 28000,
            'gas_charge' => 1080,
            'parking_charge' => 1500,
            'service_charge' => 2500,
            'advance_amount' => 50000,
            'starts_on' => now()->subYear()->startOfMonth(),
            'ends_on' => null,
            'active_marker' => 1,
        ];
    }
}
