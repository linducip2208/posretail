<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierReturnItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (SupplierReturnItem $item) {
            $item->subtotal = (float) $item->unit_price * (int) $item->quantity;
        });

        static::saved(function (SupplierReturnItem $item) {
            static::recalculateParentTotal($item->supplier_return_id);
        });

        static::deleted(function (SupplierReturnItem $item) {
            static::recalculateParentTotal($item->supplier_return_id);
        });
    }

    protected static function recalculateParentTotal(?int $supplierReturnId): void
    {
        if (! $supplierReturnId) {
            return;
        }

        $total = static::where('supplier_return_id', $supplierReturnId)->sum('subtotal');
        SupplierReturn::where('id', $supplierReturnId)->update(['total_amount' => $total]);
    }

    protected $fillable = [
        'supplier_return_id', 'product_id', 'quantity', 'unit_price', 'subtotal', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function supplierReturn(): BelongsTo
    {
        return $this->belongsTo(SupplierReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
