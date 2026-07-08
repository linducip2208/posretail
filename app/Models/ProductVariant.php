<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'name', 'sku', 'barcode',
        'cost_price', 'selling_price', 'current_stock',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductVariant $variant) {
            if (blank($variant->sku)) {
                $last = static::where('sku', 'like', 'SKU%')
                    ->orderByRaw('LENGTH(sku) DESC, sku DESC')->first();
                $num = $last ? (int) substr($last->sku, 3) + 1 : 1;
                $variant->sku = 'SKU' . str_pad($num, 6, '0', STR_PAD_LEFT);
            }

            if (blank($variant->barcode)) {
                $variant->barcode = Product::generateBarcode();
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockOpnameItems(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}
