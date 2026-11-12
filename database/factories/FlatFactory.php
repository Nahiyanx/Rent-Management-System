<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Flat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Flat>
 */
class FlatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'building_id' => Building::factory(),
            'unit_number' => fake()->unique()->numberBetween(1, 60).fake()->randomElement(['A', 'B', 'C']),
        ];
    }
}
