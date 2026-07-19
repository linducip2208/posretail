<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WriteOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'writeoff_number', 'product_id', 'product_variant_id', 'outlet_id',
        'user_id', 'quantity', 'unit_cost', 'total_loss', 'reason', 'notes',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function productVariant(): BelongsTo { return $this->belongsTo(ProductVariant::class); }
    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
