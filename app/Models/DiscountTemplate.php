<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'value', 'min_purchase',
        'buy_quantity', 'get_quantity', 'start_date', 'end_date', 'active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
