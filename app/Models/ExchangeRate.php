<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = ['currency', 'rate', 'effective_date'];

    protected function casts(): array
    {
        return ['effective_date' => 'date'];
    }

    public static function getRate(string $currency = 'USD'): ?float
    {
        return static::where('currency', $currency)
            ->where('effective_date', '<=', now())
            ->latest('effective_date')
            ->value('rate');
    }
}
