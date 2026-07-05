<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOutletScope;

class TableArea extends Model
{
    use HasFactory, SoftDeletes, HasOutletScope;

    protected $fillable = [
        'name', 'description', 'outlet_id', 'sort_order', 'active',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(TableResto::class);
    }
}
