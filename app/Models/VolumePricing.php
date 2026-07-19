<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolumePricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'customer_group_id', 'min_qty', 'max_qty',
        'unit_price', 'discount_type', 'discount_value', 'active',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function customerGroup(): BelongsTo { return $this->belongsTo(CustomerGroup::class, 'customer_group_id'); }
}
