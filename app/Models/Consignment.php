<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consignment extends Model
{
    use HasFactory, \App\Traits\HasOutletScope;

    protected $fillable = [
        'consignment_number', 'supplier_id', 'product_id', 'outlet_id',
        'quantity', 'sold_quantity', 'unit_price', 'commission_percent',
        'start_date', 'settlement_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'settlement_date' => 'date'];
    }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }

    public function remaining(): int { return $this->quantity - $this->sold_quantity; }
    public function totalValue(): float { return $this->sold_quantity * $this->unit_price; }
    public function commissionAmount(): float { return $this->totalValue() * $this->commission_percent / 100; }
}
