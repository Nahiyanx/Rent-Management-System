<?php

namespace Database\Factories;

use App\Models\MonthlyBalance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonthlyBalance>
 */
class MonthlyBalanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'month' => '2026-11',
            'cash_starting' => 100000,
            'bank_starting' => 250000,
            'cash_manual' => false,
            'bank_manual' => false,
            'cash_previous_closing' => 0,
            'bank_previous_closing' => 0,
        ];
    }
}
