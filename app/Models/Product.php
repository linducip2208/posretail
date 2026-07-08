<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\HasOutletScope;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasOutletScope;

    protected bool $outletNullable = true;

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name);
            }

            if (filled($product->slug)) {
                $product->slug = static::uniqueSlug($product->slug, $product->getKey());
            }

            if (blank($product->sku)) {
                $prefix = 'SKU';
                $last = static::withTrashed()->where('sku', 'like', $prefix.'%')
                    ->orderByRaw('LENGTH(sku) DESC, sku DESC')->first();
                $num = $last ? (int) substr($last->sku, strlen($prefix)) + 1 : 1;
                $product->sku = $prefix . str_pad($num, 6, '0', STR_PAD_LEFT);
            }

            if (blank($product->barcode)) {
                $product->barcode = static::generateBarcode();
            }
        });
    }

    public static function generateBarcode(): string
    {
        $prefix = '899';
        $digits = $prefix;
        for ($i = 0; $i < 9; $i++) {
            $digits .= random_int(0, 9);
        }
        $checksum = static::ean13Checksum($digits);
        return $digits . $checksum;
    }

    private static function ean13Checksum(string $digits): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $digits[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    protected static function uniqueSlug(string $slug, $ignoreId = null): string
    {
        $base = Str::slug($slug) ?: 'product';
        $slug = $base;
        $i = 2;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected $fillable = [
        'name', 'slug', 'description', 'category_id', 'brand_id',
        'unit_id', 'outlet_id', 'sku', 'barcode', 'cost_price',
        'selling_price', 'wholesale_price', 'member_price',
        'min_stock', 'max_stock', 'current_stock', 'image',
        'has_variants', 'active', 'expired_date',
    ];

    protected function casts(): array
    {
        return [
            'expired_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockOpnameItems(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function stockTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }
}
