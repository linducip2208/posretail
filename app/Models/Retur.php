<?php

namespace App\Models;

use App\Services\JournalService;
use App\Traits\Auditable;
use App\Traits\HasOutletScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Retur extends Model
{
    use Auditable, HasFactory, HasOutletScope;

    protected $table = 'returns';

    protected static function booted(): void
    {
        static::creating(function (Retur $retur) {
            if (blank($retur->return_number)) {
                $retur->return_number = static::generateNumber();
            }

            if (blank($retur->type)) {
                $retur->type = 'customer_return';
            }
        });

        static::updated(function (Retur $retur) {
            if ($retur->wasChanged('status') && $retur->status === 'completed') {
                $retur->applyReturn();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = 'RT-'.now()->format('Ymd').'-';
        $last = static::where('return_number', 'like', $prefix.'%')
            ->orderBy('return_number', 'desc')
            ->first();
        $next = $last ? (int) substr($last->return_number, -4) + 1 : 1;

        return $prefix.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'return_number', 'order_id', 'outlet_id', 'user_id',
        'type', 'total_amount', 'reason', 'status', 'notes', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    /**
     * Validasi kumulatif: total yang pernah di-refund (retur completed)
     * + yang diminta tidak boleh melebihi qty yang dibeli.
     * Mencegah refund ganda melebihi pembelian (mis. beli 10, refund 6 + 5).
     *
     * @param  array<int, array{product_id:int, quantity:int}>  $rows
     */
    public static function assertCumulativeQty(Order $order, array $rows, ?int $ignoreReturId = null): void
    {
        $purchased = OrderItem::where('order_id', $order->id)
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        $refunded = ReturnItem::whereHas('retur', function ($q) use ($order, $ignoreReturId) {
            $q->where('order_id', $order->id)->where('status', 'completed');
            if ($ignoreReturId) {
                $q->where('id', '!=', $ignoreReturId);
            }
        })
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        $requested = [];
        foreach ($rows as $row) {
            $pid = (int) $row['product_id'];
            $requested[$pid] = ($requested[$pid] ?? 0) + (int) $row['quantity'];
        }

        foreach ($requested as $pid => $qty) {
            $max = (int) ($purchased[$pid] ?? 0);
            $done = (int) ($refunded[$pid] ?? 0);
            if ($qty < 1 || $done + $qty > $max) {
                throw ValidationException::withMessages([
                    'items' => "Qty refund melebihi sisa yang bisa di-refund (dibeli {$max}, sudah refund {$done}).",
                ]);
            }
        }
    }

    /**
     * Terapkan efek retur: stok kembali, stock movement, serial kembali, jurnal balik.
     * Idempotent — hanya jalan sekali (dicek via completed_at).
     */
    public function applyReturn(): void
    {
        if ($this->completed_at !== null) {
            return;
        }

        DB::transaction(function () {
            // Backstop: validasi kumulatif juga saat retur dari admin panel.
            if ($this->order_id) {
                $order = Order::where('id', $this->order_id)->lockForUpdate()->first();
                if ($order) {
                    static::assertCumulativeQty($order, $this->returnItems->map(fn ($i) => [
                        'product_id' => $i->product_id,
                        'quantity' => $i->quantity,
                    ])->toArray(), $this->id);
                }
            }

            $this->loadMissing('returnItems.product');

            foreach ($this->returnItems as $item) {
                $product = $item->product;

                if ($product) {
                    $product->increment('current_stock', $item->quantity);

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'product_variant_id' => null,
                        'outlet_id' => $this->outlet_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference_type' => 'return',
                        'reference_id' => $this->id,
                        'notes' => 'Retur penjualan #'.$this->return_number,
                    ]);
                }

                if ($product && $product->tracksSerial() && $this->order_id) {
                    $orderItemIds = OrderItem::where('order_id', $this->order_id)
                        ->where('product_id', $item->product_id)
                        ->pluck('id');

                    SerialNumber::where('product_id', $item->product_id)
                        ->whereIn('order_item_id', $orderItemIds)
                        ->where('status', 'sold')
                        ->orderBy('id')
                        ->limit($item->quantity)
                        ->update([
                            'status' => 'in_stock',
                            'order_item_id' => null,
                            'notes' => 'Kembali via retur #'.$this->return_number,
                        ]);
                }
            }

            JournalService::postSalesReturn($this);

            $this->updateQuietly(['completed_at' => now()]);
        });
    }
}
