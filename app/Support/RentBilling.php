<?php

namespace App\Support;

use App\Models\RentBill;
use App\Models\Tenancy;
use Illuminate\Support\Carbon;

/**
 * Creates the monthly rent invoices for a given billing month.
 *
 * Bills snapshot the tenancy defaults at the moment they are created, so later
 * changes to a tenant's configured charges never rewrite an existing invoice.
 */
final class RentBilling
{
    public static function ensureForMonth(string $month): void
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        Tenancy::query()
            ->where('starts_on', '<=', $end)
            ->where(function ($query) use ($start): void {
                $query->whereNull('ends_on')->orWhere('ends_on', '>=', $start);
            })
            ->each(function (Tenancy $tenancy) use ($month): void {
                self::billFor($tenancy, $month);
            });
    }

    public static function billFor(Tenancy $tenancy, string $month): RentBill
    {
        $charges = [
            'house_rent' => (float) $tenancy->house_rent,
            'electricity' => 0.0,
            'gas' => (float) $tenancy->gas_charge,
            'parking' => (float) $tenancy->parking_charge,
            'service_charge' => (float) $tenancy->service_charge,
            'other_charges' => 0.0,
        ];

        return RentBill::firstOrCreate(
            [
                'tenancy_id' => $tenancy->id,
                'billing_month' => $month,
            ],
            $charges + ['total_due' => RentBill::totalFrom($charges)],
        );
    }
}
