<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform', 'platform_order_id', 'platform_invoice',
        'customer_name', 'customer_phone', 'shipping_address',
        'total_amount', 'shipping_fee', 'status',
        'raw_payload', 'items', 'order_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'items' => 'array',
            'total_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
