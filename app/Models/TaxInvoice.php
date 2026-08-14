<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'order_id', 'outlet_id', 'customer_npwp', 'customer_name',
        'customer_address', 'dpp', 'ppn_amount', 'total_amount', 'reference_number',
        'invoice_date', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['invoice_date' => 'date'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $prefix = 'FP-'.now()->format('Ymd').'-';
        $last = static::where('invoice_number', 'like', $prefix.'%')
            ->orderBy('invoice_number', 'desc')
            ->first();
        $next = $last ? (int) substr($last->invoice_number, -4) + 1 : 1;

        return $prefix.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
