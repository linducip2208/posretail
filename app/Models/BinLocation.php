<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasOutletScope;
class BinLocation extends Model { use HasFactory, HasOutletScope; protected $fillable = ['outlet_id', 'code', 'name', 'zone', 'active']; public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); } public function products(): HasMany { return $this->hasMany(Product::class, 'bin_location_id'); } }
