<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AssetMaintenance extends Model { use HasFactory; protected $fillable = ['asset_id', 'maintenance_date', 'cost', 'type', 'description', 'next_maintenance_date']; protected function casts(): array { return ['maintenance_date' => 'date', 'next_maintenance_date' => 'date']; } public function asset(): BelongsTo { return $this->belongsTo(CompanyAsset::class, 'asset_id'); } }
