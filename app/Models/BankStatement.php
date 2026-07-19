<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id', 'bank_name', 'account_number', 'transaction_date',
        'description', 'reference', 'debit', 'credit', 'balance',
        'is_matched', 'matched_transaction_id', 'matched_transaction_type',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'is_matched' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
