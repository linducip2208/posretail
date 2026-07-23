<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasOutletScope;

class StockMovement extends Model
{
    use HasFactory, HasOutletScope;

    protected $fillable = [
        'product_id', 'product_variant_id', 'outlet_id',
        'type', 'quantity', 'reference_type', 'reference_id', 'notes',
        'batch_number', 'expired_date',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'outlet_id' => 'integer',
            'reference_id' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
