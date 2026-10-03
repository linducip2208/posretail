<?php

namespace App\Models;

use App\Events\OrderCreated;
use App\Services\EmailService;
use App\Services\JournalService;
use App\Services\ReferralService;
use App\Services\WhatsAppService;
use App\Traits\Auditable;
use App\Traits\HasOutletScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use Auditable, HasFactory, HasOutletScope, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if ($order->isDirty('order_status') && $order->order_status === 'completed') {
                $user = $order->user;
                if ($user && $user->commission_percent > 0) {
                    $order->commission_amount = $order->total_amount * $user->commission_percent / 100;
                }
            }

            if ($order->isDirty('order_status') && $order->order_status === 'cancelled') {
                $order->commission_amount = 0;
            }
        });

        static::created(function (Order $order) {
            OrderCreated::dispatch($order);

            if ($order->order_status === 'completed') {
                JournalService::postOrderRevenue($order);
                ReferralService::awardIfEligible($order);
            }
        });

        static::updated(function (Order $order) {
            if ($order->wasChanged('order_status') && $order->order_status === 'completed') {
                JournalService::postOrderRevenue($order);
                ReferralService::awardIfEligible($order);
            }

            if ($order->wasChanged('order_status') && $order->order_status === 'cancelled') {
                static::reverseOrderCancel($order);
            }
        });
    }

    protected static function reverseOrderCancel(Order $order): void
    {
        $order->loadMissing('orderItems.product', 'orderItems.productVariant', 'payments', 'installments.schedules', 'taxInvoices', 'giftCardUsages.giftCard', 'loyaltyPoints.customer', 'paymentProofs');

        foreach ($order->orderItems as $item) {
            if ($item->product_variant_id) {
                ProductVariant::find($item->product_variant_id)?->increment('current_stock', $item->quantity);
            }
            Product::find($item->product_id)?->increment('current_stock', $item->quantity);

            StockMovement::create([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id ?? null,
                'outlet_id' => $order->outlet_id,
                'type' => 'in',
                'quantity' => $item->quantity,
                'reference_type' => 'order_cancel',
                'reference_id' => $order->id,
                'notes' => 'Pembatalan order #'.$order->order_number,
            ]);
        }

        foreach ($order->loyaltyPoints as $point) {
            if ($point->customer) {
                $point->customer->decrement('total_points', $point->points_earned);
            }
            $point->update([
                'description' => ($point->description ?? '').' [DIBATALKAN — order #'.$order->order_number.']',
            ]);
        }

        foreach ($order->payments as $payment) {
            if (in_array($payment->status, ['success', 'confirmed', 'completed'])) {
                JournalService::reversePaymentReceived($payment);
                $payment->update(['status' => 'refunded']);

                $activeShift = Shift::where('outlet_id', $order->outlet_id)
                    ->where('status', 'open')
                    ->latest('started_at')
                    ->first();

                CashDrawerTransaction::create([
                    'shift_id' => $activeShift?->id,
                    'order_id' => $order->id,
                    'type' => 'cash_out',
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method_id,
                    'notes' => 'Refund pembatalan order #'.$order->order_number,
                ]);
            }
        }

        foreach ($order->installments as $installment) {
            $installment->update(['status' => 'cancelled']);
            $installment->schedules()->update(['status' => 'cancelled']);
        }

        foreach ($order->taxInvoices as $taxInvoice) {
            $taxInvoice->update(['status' => 'voided']);
        }

        $order->paymentProofs()->where('status', 'pending')->update([
            'status' => 'rejected',
            'notes' => DB::raw("COALESCE(notes, '') || ' | Otomatis ditolak — order #{$order->order_number} dibatalkan'"),
        ]);

        Delivery::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'packed', 'shipped'])
            ->update([
                'status' => 'cancelled',
                'delivery_notes' => DB::raw("COALESCE(delivery_notes, '') || ' | Otomatis dibatalkan — order #{$order->order_number} dicancel'"),
            ]);

        MarketplaceOrder::where('order_id', $order->id)
            ->where('status', '!=', 'cancelled')
            ->update(['status' => 'cancelled']);

        foreach ($order->giftCardUsages as $usage) {
            $giftCard = $usage->giftCard;
            if ($giftCard) {
                $giftCard->increment('remaining_balance', $usage->amount_used);
                if ($giftCard->used_count > 0) {
                    $giftCard->decrement('used_count');
                }
            }
        }

        if ($order->deposit_amount > 0 && $order->customer_id) {
            $newBalance = (Customer::find($order->customer_id)?->deposit_balance ?? 0) + $order->deposit_amount;

            CustomerDeposit::create([
                'customer_id' => $order->customer_id,
                'outlet_id' => $order->outlet_id,
                'user_id' => $order->user_id,
                'type' => 'refund',
                'amount' => $order->deposit_amount,
                'balance_after' => $newBalance,
                'reference' => 'order_cancel',
                'notes' => 'Refund deposit — pembatalan order #'.$order->order_number,
            ]);

            Customer::where('id', $order->customer_id)->increment('deposit_balance', $order->deposit_amount);
        }

        JournalService::reverseOrderRevenue($order);

        $order->updateQuietly(['payment_status' => 'refunded', 'remaining_amount' => 0]);

        try {
            $whatsapp = new WhatsAppService;
            $whatsapp->sendOrderStatus($order);
        } catch (\Exception $e) {
            \Log::warning('WhatsApp notification failed on cancel: '.$e->getMessage());
        }

        try {
            if ($order->customer?->email) {
                $email = new EmailService;
                $email->sendOrderStatus($order, $order->customer->email);
            }
        } catch (\Exception $e) {
            \Log::warning('Email notification failed on cancel: '.$e->getMessage());
        }
    }

    public function scopeCompleted($query)
    {
        return $query->where('order_status', 'completed');
    }

    public function scopeExcludeCancelled($query)
    {
        return $query->where('order_status', '!=', 'cancelled');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('order_status', ['pending', 'processing', 'completed']);
    }

    protected $fillable = [
        'order_number', 'customer_id', 'outlet_id', 'user_id',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount',
        'commission_amount', 'currency', 'exchange_rate', 'payment_status', 'order_status', 'notes',
        'order_type', 'queue_number', 'deposit_amount', 'remaining_amount',
        'is_installment', 'installment_period', 'installment_count',
        'employee_id', 'order_notes',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'outlet_id' => 'integer',
            'user_id' => 'integer',
            'employee_id' => 'integer',
            'is_installment' => 'boolean',
            'installment_count' => 'integer',
            'deposit_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function taxInvoices(): HasMany
    {
        return $this->hasMany(TaxInvoice::class);
    }

    public function giftCardUsages(): HasMany
    {
        return $this->hasMany(GiftCardUsage::class);
    }
}
