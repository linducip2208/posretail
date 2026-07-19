<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasOutletScope;

class DiscountTemplate extends Model
{
    use HasFactory, HasOutletScope;

    protected bool $outletNullable = true;

    protected $fillable = [
        'name', 'type', 'value', 'min_purchase',
        'buy_quantity', 'get_quantity', 'start_date', 'end_date', 'active',
        'outlet_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
