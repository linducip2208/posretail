<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableResto extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = [
        'name', 'code', 'outlet_id', 'table_area_id',
        'capacity', 'status', 'sort_order', 'active',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function tableArea(): BelongsTo
    {
        return $this->belongsTo(TableArea::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
