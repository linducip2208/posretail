<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number', 'order_id', 'outlet_id', 'driver_id', 'status',
        'shipping_address', 'recipient_name', 'recipient_phone',
        'packed_at', 'shipped_at', 'delivered_at', 'delivery_notes',
        'signature_path', 'tracking_number',
    ];

    protected function casts(): array
    {
        return ['packed_at' => 'datetime', 'shipped_at' => 'datetime', 'delivered_at' => 'datetime'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function driver(): BelongsTo { return $this->belongsTo(User::class, 'driver_id'); }
}
