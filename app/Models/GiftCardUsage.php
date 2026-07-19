<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCardUsage extends Model
{
    use HasFactory;

    protected $fillable = ['gift_card_id', 'order_id', 'amount_used'];

    public function giftCard(): BelongsTo { return $this->belongsTo(GiftCard::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
