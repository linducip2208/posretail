<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class CompanyAsset extends Model { protected $table = 'assets'; use HasFactory; protected $fillable = ['asset_code', 'name', 'outlet_id', 'category', 'purchase_date', 'purchase_value', 'current_value', 'useful_life_months', 'monthly_depreciation', 'salvage_value', 'status', 'location', 'notes', 'assigned_to']; protected function casts(): array { return ['purchase_date' => 'date']; } public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); } public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); } public function maintenances(): HasMany { return $this->hasMany(AssetMaintenance::class, 'asset_id'); } }
