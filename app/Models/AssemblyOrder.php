<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasOutletScope;

class AssemblyOrder extends Model
{
    use HasFactory, HasOutletScope;

    protected $fillable = [
        'assembly_number', 'product_id', 'outlet_id', 'user_id',
        'quantity', 'status', 'notes', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssemblyOrderItem::class);
    }

    public static function generateAssemblyNumber(): string
    {
        $prefix = 'ASM-' . now()->format('ymd') . '-';
        $last = static::where('assembly_number', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(assembly_number) DESC, assembly_number DESC')
            ->first();

        $num = $last ? (int) substr($last->assembly_number, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
