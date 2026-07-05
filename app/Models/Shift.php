<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasOutletScope;

class Shift extends Model
{
    use HasFactory, HasOutletScope;

    protected $fillable = [
        'outlet_id', 'user_id', 'started_at', 'ended_at',
        'starting_cash', 'ending_cash', 'expected_cash',
        'difference', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashDrawerTransactions(): HasMany
    {
        return $this->hasMany(CashDrawerTransaction::class);
    }
}
