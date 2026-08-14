<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasOutletScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SupplierReturn extends Model
{
    use Auditable, HasFactory, HasOutletScope;

    protected $fillable = [
        'return_number', 'supplier_id', 'purchase_order_id', 'outlet_id',
        'user_id', 'total_amount', 'status', 'reason', 'notes', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'received_at' => 'datetime',
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

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    public static function generateNumber(): string
    {
        $prefix = 'RS-'.now()->format('Ymd').'-';
        $last = static::where('return_number', 'like', $prefix.'%')
            ->orderBy('return_number', 'desc')
            ->first();
        $next = $last ? (int) substr($last->return_number, -4) + 1 : 1;

        return $prefix.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function markReceived(): void
    {
        if ($this->status === 'received') {
            return;
        }

        DB::transaction(function () {
            foreach ($this->items as $item) {
                Product::where('id', $item->product_id)->decrement('current_stock', $item->quantity);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => null,
                    'outlet_id' => $this->outlet_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'reference_type' => 'supplier_return',
                    'reference_id' => $this->id,
                    'notes' => 'Retur supplier #'.$this->return_number,
                ]);
            }

            if ($this->purchase_order_id && $this->total_amount > 0) {
                SupplierPayable::where('purchase_order_id', $this->purchase_order_id)
                    ->where('status', '!=', 'paid')
                    ->orderBy('id')
                    ->get()
                    ->each(function (SupplierPayable $payable) {
                        $payable->total_amount = max(0, (float) $payable->total_amount - (float) $this->total_amount);
                        if ($payable->total_amount <= $payable->paid_amount) {
                            $payable->status = 'paid';
                        }
                        $payable->save();
                    });
            }

            $this->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });
    }
}
