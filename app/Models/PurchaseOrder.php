<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasOutletScope;

class PurchaseOrder extends Model
{
    use HasFactory, HasOutletScope;

    protected static function booted(): void
    {
        static::updated(function (PurchaseOrder $po) {
            if ($po->wasChanged('status') && $po->status === 'received') {
                \App\Services\JournalService::postPOReceived($po);
            }
        });
    }

    protected $fillable = [
        'po_number', 'supplier_id', 'outlet_id', 'user_id',
        'total_amount', 'status', 'notes',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public static function generateNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $last = static::where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();
        $next = $last ? (int) substr($last->po_number, -4) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
