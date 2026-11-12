<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Expense;
use App\Models\Flat;
use App\Models\MonthlyBalance;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Support\DemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PortfolioSeeder extends Seeder
{
    /**
     * Seed the demonstration portfolio.
     */
    public function run(): void
    {
        $buildings = [];

        foreach (DemoData::buildings() as $data) {
            $building = Building::firstOrCreate(
                ['name' => $data['name']],
                ['address' => $data['address']],
            );

            foreach (DemoData::flats()[$data['name']] as $unitNumber) {
                Flat::firstOrCreate([
                    'building_id' => $building->id,
                    'unit_number' => $unitNumber,
                ]);
            }

            $buildings[$data['name']] = $building;
        }

        $tenancies = [];

        foreach (DemoData::tenants() as $data) {
            $tenant = Tenant::firstOrCreate(
                ['name' => $data['name']],
                ['contact_number' => $data['phone']],
            );

            $building = $buildings[$data['building']];

            $flat = Flat::query()
                ->where('building_id', $building->id)
                ->where('unit_number', $data['flat'])
                ->firstOrFail();

            $tenancy = Tenancy::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'flat_id' => $flat->id,
                    'active_marker' => 1,
                ],
                [
                    'house_rent' => $data['rent'],
                    'gas_charge' => 1080,
                    'parking_charge' => in_array($data['name'], ['Farhana Islam', 'Imran Hossain'], true) ? 0 : 1500,
                    'service_charge' => $data['building'] === 'The Orchard' ? 3000 : 2500,
                    'starts_on' => Carbon::create((int) $data['since'], 1, 1),
                    'ends_on' => null,
                ],
            );

            $tenancies[$tenant->id] = $tenancy;
        }

        $paymentDays = [1 => 5, 3 => 6, 4 => 7, 6 => 8, 8 => 9];

        foreach (DemoData::bills('2026-11') as $data) {
            $tenancy = $tenancies[$data['tenant_id']] ?? null;

            if ($tenancy === null) {
                continue;
            }

            $bill = RentBill::firstOrCreate(
                [
                    'tenancy_id' => $tenancy->id,
                    'billing_month' => '2026-11',
                ],
                [
                    'house_rent' => $data['charges']['house_rent'],
                    'electricity' => $data['charges']['electricity'],
                    'gas' => $data['charges']['gas'],
                    'parking' => $data['charges']['parking'],
                    'service_charge' => $data['charges']['service_charge'],
                    'other_charges' => $data['charges']['other'],
                    'total_due' => $data['total'],
                ],
            );

            if ($data['status'] === 'Due') {
                continue;
            }

            RentPayment::updateOrCreate(
                ['rent_bill_id' => $bill->id],
                [
                    'amount' => $data['paid_amount'],
                    'method' => $data['paid_via'],
                    'paid_on' => Carbon::create(2026, 11, $paymentDays[$data['tenant_id']])->toDateString(),
                ],
            );
        }

        MonthlyBalance::firstOrCreate(
            ['month' => '2026-11'],
            [
                'cash_starting' => 142000,
                'bank_starting' => 586000,
                'cash_manual' => false,
                'bank_manual' => false,
                'cash_previous_closing' => 0,
                'bank_previous_closing' => 0,
            ],
        );

        foreach (DemoData::expenses('2026-11') as $data) {
            Expense::firstOrCreate(
                [
                    'expense_date' => Carbon::parse($data['date'])->startOfDay()->toDateTimeString(),
                    'description' => $data['description'],
                    'amount' => $data['amount'],
                    'paid_from' => $data['paid_from'],
                ],
            );
        }
    }
}
