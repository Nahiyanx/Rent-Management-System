<?php

namespace App\Models;

use Database\Factories\MonthlyBalanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'month',
    'cash_starting',
    'bank_starting',
    'cash_manual',
    'bank_manual',
    'cash_previous_closing',
    'bank_previous_closing',
])]
class MonthlyBalance extends Model
{
    /** @use HasFactory<MonthlyBalanceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cash_starting' => 'decimal:2',
            'bank_starting' => 'decimal:2',
            'cash_manual' => 'boolean',
            'bank_manual' => 'boolean',
            'cash_previous_closing' => 'decimal:2',
            'bank_previous_closing' => 'decimal:2',
        ];
    }
}
