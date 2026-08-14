<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (ReturnItem $item) {
            $item->subtotal = (float) $item->unit_price * (int) $item->quantity;
        });

        static::saved(function (ReturnItem $item) {
            static::recalculateParentTotal($item->return_id);
        });

        static::deleted(function (ReturnItem $item) {
            static::recalculateParentTotal($item->return_id);
        });
    }

    protected static function recalculateParentTotal(?int $returnId): void
    {
        if (! $returnId) {
            return;
        }

        $total = static::where('return_id', $returnId)->sum('subtotal');
        Retur::where('id', $returnId)->update(['total_amount' => $total]);
    }

    protected $fillable = [
        'return_id', 'product_id', 'quantity', 'unit_price', 'subtotal',
    ];

    public function retur(): BelongsTo
    {
        return $this->belongsTo(Retur::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
