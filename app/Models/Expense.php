<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOutletScope;

class Expense extends Model
{
    use HasFactory, SoftDeletes, HasOutletScope;

    protected static function booted(): void
    {
        static::created(function (Expense $expense) {
            \App\Services\JournalService::postExpense($expense);
        });
    }

    protected $fillable = [
        'outlet_id', 'user_id', 'category', 'amount',
        'description', 'expense_date', 'reference_number', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
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
}
