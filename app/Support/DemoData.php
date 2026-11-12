<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * The fictional portfolio used to seed the local demonstration database.
 *
 * All screens read live models; these definitions only describe the sample
 * buildings, flats, tenants, bills, payments, and expenses.
 */
final class DemoData
{
    /**
     * @return array<int, array{id: int, name: string, address: string, flats: int}>
     */
    public static function buildings(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Shanti Nibash',
                'address' => 'House 12, Road 5, Dhanmondi, Dhaka',
                'flats' => 8,
            ],
            [
                'id' => 2,
                'name' => 'Meghna Court',
                'address' => 'Plot 44, Block C, Bashundhara R/A, Dhaka',
                'flats' => 12,
            ],
            [
                'id' => 3,
                'name' => 'The Orchard',
                'address' => '9 Lake View Road, Gulshan 2, Dhaka',
                'flats' => 6,
            ],
        ];
    }

    /**
     * The units registered inside each demonstration building.
     *
     * @return array<string, array<int, string>>
     */
    public static function flats(): array
    {
        return [
            'Shanti Nibash' => ['1A', '1B', '2A', '2B', '3A', '3B', '4A', '5B'],
            'Meghna Court' => ['1A', '1B', '2A', '2B', '3A', '3B', '4A', '4B', '5A', '7B', '9C', '11A'],
            'The Orchard' => ['1A', '2A', '3A', '4C', '5A', '6B'],
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, initials: string, building: string, flat: string, rent: int, phone: string, since: string}>
     */
    public static function tenants(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Arif Rahman',
                'initials' => 'AR',
                'building' => 'Shanti Nibash',
                'flat' => '3A',
                'rent' => 28000,
                'phone' => '01711 204 588',
                'since' => '2023',
            ],
            [
                'id' => 2,
                'name' => 'Farhana Islam',
                'initials' => 'FI',
                'building' => 'Shanti Nibash',
                'flat' => '5B',
                'rent' => 26500,
                'phone' => '01644 512 907',
                'since' => '2024',
            ],
            [
                'id' => 3,
                'name' => 'Nusrat Jahan',
                'initials' => 'NJ',
                'building' => 'Meghna Court',
                'flat' => '7B',
                'rent' => 35000,
                'phone' => '01822 771 430',
                'since' => '2022',
            ],
            [
                'id' => 4,
                'name' => 'Rezaul Karim',
                'initials' => 'RK',
                'building' => 'Meghna Court',
                'flat' => '11A',
                'rent' => 38000,
                'phone' => '01555 663 201',
                'since' => '2021',
            ],
            [
                'id' => 5,
                'name' => 'Tanvir Ahmed',
                'initials' => 'TA',
                'building' => 'The Orchard',
                'flat' => '2A',
                'rent' => 42000,
                'phone' => '01933 880 215',
                'since' => '2024',
            ],
            [
                'id' => 6,
                'name' => 'Sadia Noor',
                'initials' => 'SN',
                'building' => 'The Orchard',
                'flat' => '4C',
                'rent' => 45000,
                'phone' => '01777 904 612',
                'since' => '2023',
            ],
            [
                'id' => 7,
                'name' => 'Imran Hossain',
                'initials' => 'IH',
                'building' => 'Shanti Nibash',
                'flat' => '1A',
                'rent' => 25800,
                'phone' => '01311 447 802',
                'since' => '2025',
            ],
            [
                'id' => 8,
                'name' => 'Sabrina Chowdhury',
                'initials' => 'SC',
                'building' => 'Meghna Court',
                'flat' => '9C',
                'rent' => 36400,
                'phone' => '01988 225 741',
                'since' => '2024',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function bills(string $month): array
    {
        $tenants = collect(self::tenants())->keyBy('id');

        $definitions = [
            1 => ['status' => 'Paid', 'paid_via' => 'Cash', 'paid_amount' => null, 'electricity' => 2450, 'parking' => 1500, 'service_charge' => 2500, 'other' => 0],
            2 => ['status' => 'Due', 'paid_via' => null, 'paid_amount' => 0, 'electricity' => 2100, 'parking' => 0, 'service_charge' => 2500, 'other' => 500],
            3 => ['status' => 'Paid', 'paid_via' => 'Bank', 'paid_amount' => null, 'electricity' => 2850, 'parking' => 1500, 'service_charge' => 2500, 'other' => 0],
            4 => ['status' => 'Partial', 'paid_via' => 'Cash', 'paid_amount' => 25000, 'electricity' => 3100, 'parking' => 1500, 'service_charge' => 2500, 'other' => 0],
            5 => ['status' => 'Due', 'paid_via' => null, 'paid_amount' => 0, 'electricity' => 3250, 'parking' => 1500, 'service_charge' => 3000, 'other' => 0],
            6 => ['status' => 'Paid', 'paid_via' => 'Bank', 'paid_amount' => null, 'electricity' => 3400, 'parking' => 1500, 'service_charge' => 3000, 'other' => 500],
            7 => ['status' => 'Due', 'paid_via' => null, 'paid_amount' => 0, 'electricity' => 2000, 'parking' => 0, 'service_charge' => 2500, 'other' => 0],
            8 => ['status' => 'Paid', 'paid_via' => 'Cash', 'paid_amount' => null, 'electricity' => 2700, 'parking' => 1500, 'service_charge' => 2500, 'other' => 0],
        ];

        return collect($definitions)
            ->map(function (array $definition, int $tenantId) use ($tenants, $month): array {
                $tenant = $tenants->get($tenantId);

                $charges = [
                    'house_rent' => $tenant['rent'],
                    'electricity' => $definition['electricity'],
                    'gas' => 1080,
                    'parking' => $definition['parking'],
                    'service_charge' => $definition['service_charge'],
                    'other' => $definition['other'],
                ];

                $total = array_sum($charges);

                return [
                    'id' => $tenantId,
                    'tenant_id' => $tenantId,
                    'tenant' => $tenant['name'],
                    'initials' => $tenant['initials'],
                    'building' => $tenant['building'],
                    'flat' => $tenant['flat'],
                    'rent' => $tenant['rent'],
                    'charges' => $charges,
                    'total' => $total,
                    'status' => $definition['status'],
                    'paid_via' => $definition['paid_via'],
                    'paid_amount' => $definition['paid_amount'] ?? $total,
                    'due_day' => 5,
                    'month' => $month,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, day: int, description: string, paid_from: string, amount: int, date: string, date_label: string}>
     */
    public static function expenses(string $month): array
    {
        $rows = [
            ['id' => 1, 'day' => 2, 'description' => 'Building maintenance', 'paid_from' => 'Cash', 'amount' => 12500],
            ['id' => 2, 'day' => 4, 'description' => 'Water bill', 'paid_from' => 'Bank', 'amount' => 3240],
            ['id' => 3, 'day' => 6, 'description' => 'Lift servicing', 'paid_from' => 'Bank', 'amount' => 8000],
            ['id' => 4, 'day' => 9, 'description' => 'Cleaning supplies', 'paid_from' => 'Cash', 'amount' => 2150],
            ['id' => 5, 'day' => 11, 'description' => 'Electrician visit', 'paid_from' => 'Cash', 'amount' => 1800],
            ['id' => 6, 'day' => 14, 'description' => 'Security guard salary', 'paid_from' => 'Bank', 'amount' => 18000],
            ['id' => 7, 'day' => 18, 'description' => 'Roof repair', 'paid_from' => 'Bank', 'amount' => 22500],
            ['id' => 8, 'day' => 21, 'description' => 'Garden upkeep', 'paid_from' => 'Cash', 'amount' => 3600],
        ];

        return array_map(function (array $row) use ($month): array {
            $date = Carbon::createFromFormat('Y-m-d', sprintf('%s-%02d', $month, $row['day']));

            return [
                ...$row,
                'date' => $date->format('Y-m-d'),
                'date_label' => $date->format('d M Y'),
            ];
        }, $rows);
    }

    /**
     * @return array<string, int|float>
     */
    public static function dashboard(string $month): array
    {
        $bills = collect(self::bills($month));
        $expenses = collect(self::expenses($month));

        $startingCash = 142000;
        $startingBank = 586000;

        $cashCollected = (int) $bills
            ->where('paid_via', 'Cash')
            ->sum('paid_amount');
        $bankCollected = (int) $bills
            ->where('paid_via', 'Bank')
            ->sum('paid_amount');

        $cashExpenses = (int) $expenses->where('paid_from', 'Cash')->sum('amount');
        $bankExpenses = (int) $expenses->where('paid_from', 'Bank')->sum('amount');

        $closingCash = $startingCash + $cashCollected - $cashExpenses;
        $closingBank = $startingBank + $bankCollected - $bankExpenses;

        $rentCollected = $cashCollected + $bankCollected;
        $totalExpenses = $cashExpenses + $bankExpenses;
        $billedTotal = (int) $bills->sum('total');
        $outstanding = $billedTotal - $rentCollected;

        return [
            'starting_cash' => $startingCash,
            'starting_bank' => $startingBank,
            'starting_total' => $startingCash + $startingBank,
            'cash_collected' => $cashCollected,
            'bank_collected' => $bankCollected,
            'rent_collected' => $rentCollected,
            'cash_expenses' => $cashExpenses,
            'bank_expenses' => $bankExpenses,
            'expenses' => $totalExpenses,
            'closing_cash' => $closingCash,
            'closing_bank' => $closingBank,
            'closing_total' => $closingCash + $closingBank,
            'net_movement' => $rentCollected - $totalExpenses,
            'billed_total' => $billedTotal,
            'outstanding' => $outstanding,
            'settled_bills' => $bills->where('status', 'Paid')->count(),
            'total_bills' => $bills->count(),
            'collection_progress' => $billedTotal > 0 ? (int) round(($rentCollected / $billedTotal) * 100) : 0,
        ];
    }

    /**
     * @return array{collected: int, outstanding: int, billed: int, settled: int, total: int, progress: int}
     */
    public static function rentSummary(string $month): array
    {
        $data = self::dashboard($month);

        return [
            'collected' => (int) $data['rent_collected'],
            'outstanding' => (int) $data['outstanding'],
            'billed' => (int) $data['billed_total'],
            'settled' => (int) $data['settled_bills'],
            'total' => (int) $data['total_bills'],
            'progress' => (int) $data['collection_progress'],
        ];
    }
}
