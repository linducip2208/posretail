<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayablePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_payable_id', 'amount', 'payment_method',
        'reference_number', 'payment_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
        ];
    }

    public function supplierPayable(): BelongsTo
    {
        return $this->belongsTo(SupplierPayable::class);
    }
}
