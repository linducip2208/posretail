<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasOutletScope;

class KitchenTicket extends Model
{
    use HasFactory, HasOutletScope;

    protected $fillable = [
        'order_id', 'outlet_id', 'ticket_number', 'status',
        'items', 'notes', 'printed_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'printed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
