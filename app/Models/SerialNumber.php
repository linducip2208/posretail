<?php

namespace App\Models;

use App\Traits\HasOutletScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialNumber extends Model
{
    use HasFactory, HasOutletScope;

    protected bool $outletNullable = true;

    protected $fillable = [
        'product_id', 'product_variant_id', 'outlet_id', 'serial_number',
        'status', 'warranty_expires_at', 'order_item_id', 'purchase_order_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'warranty_expires_at' => 'date',
        ];
    }

    public function warrantyStatus(): string
    {
        if (! $this->warranty_expires_at) {
            return 'none';
        }

        if ($this->warranty_expires_at->isPast()) {
            return 'expired';
        }

        if ($this->warranty_expires_at->lte(now()->addDays(30))) {
            return 'expiring';
        }

        return 'active';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
