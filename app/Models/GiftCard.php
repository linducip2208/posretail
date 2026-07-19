<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_purchase', 'remaining_balance',
        'valid_from', 'valid_until', 'status', 'customer_id',
        'max_usage', 'used_count', 'created_by',
    ];

    protected function casts(): array
    {
        return ['valid_from' => 'date', 'valid_until' => 'date'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function usages(): HasMany { return $this->hasMany(GiftCardUsage::class); }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && now()->between($this->valid_from, $this->valid_until)
            && $this->used_count < $this->max_usage
            && $this->remaining_balance > 0;
    }
}
