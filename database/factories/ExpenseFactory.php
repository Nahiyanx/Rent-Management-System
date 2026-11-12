<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_date' => '2026-11-05',
            'description' => fake()->sentence(3),
            'amount' => 2500,
            'paid_from' => 'Cash',
        ];
    }
}
