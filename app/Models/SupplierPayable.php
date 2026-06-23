<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPayable extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'purchase_order_id', 'invoice_number',
        'total_amount', 'paid_amount', 'due_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function payablePayments(): HasMany
    {
        return $this->hasMany(PayablePayment::class);
    }
}
