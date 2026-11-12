<?php

namespace Database\Factories;

use App\Models\RentBill;
use App\Models\Tenancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentBill>
 */
class RentBillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenancy_id' => Tenancy::factory(),
            'billing_month' => '2026-11',
            'house_rent' => 28000,
            'electricity' => 2450,
            'gas' => 1080,
            'parking' => 1500,
            'service_charge' => 2500,
            'other_charges' => 0,
            'total_due' => 35530,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (RentBill $bill): void {
            $bill->total_due = round(
                (float) $bill->house_rent
                + (float) $bill->electricity
                + (float) $bill->gas
                + (float) $bill->parking
                + (float) $bill->service_charge
                + (float) $bill->other_charges,
                2,
            );
        });
    }
}
