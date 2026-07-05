<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOutletScope;

class Order extends Model
{
    use HasFactory, SoftDeletes, HasOutletScope;

    protected $fillable = [
        'order_number', 'customer_id', 'outlet_id', 'user_id',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount',
        'payment_status', 'order_status', 'notes',
        'order_type', 'queue_number', 'deposit_amount', 'remaining_amount',
        'is_installment', 'installment_period', 'installment_count',
        'employee_id', 'order_notes', 'table_id',
    ];

    protected function casts(): array
    {
        return [
            'is_installment' => 'boolean',
            'deposit_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(TableResto::class, 'table_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function kitchenTicket(): HasOne
    {
        return $this->hasOne(KitchenTicket::class);
    }
}
