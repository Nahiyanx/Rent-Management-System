<?php

namespace App\Models;

use Database\Factories\RentPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rent_bill_id', 'amount', 'method', 'paid_on'])]
class RentPayment extends Model
{
    /** @use HasFactory<RentPaymentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<RentBill, $this>
     */
    public function rentBill(): BelongsTo
    {
        return $this->belongsTo(RentBill::class);
    }
}
