<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Payment $payment) {
            if (in_array($payment->status, ['success', 'confirmed', 'completed'])) {
                \App\Services\JournalService::postPaymentReceived($payment);
            }
        });
    }

    protected $fillable = [
        'order_id', 'payment_method_id', 'amount',
        'split_index', 'reference_number', 'status', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'payment_method_id' => 'integer',
            'split_index' => 'integer',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
