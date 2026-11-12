<?php

namespace Database\Factories;

use App\Models\RentBill;
use App\Models\RentPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentPayment>
 */
class RentPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rent_bill_id' => RentBill::factory(),
            'amount' => 5000,
            'method' => 'Cash',
            'paid_on' => now()->toDateString(),
        ];
    }
}
