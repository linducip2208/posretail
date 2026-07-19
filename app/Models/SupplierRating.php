<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SupplierRating extends Model { use HasFactory; protected $fillable = ['supplier_id', 'purchase_order_id', 'on_time', 'quality', 'price_competitiveness', 'communication', 'avg_score', 'notes']; protected static function booted(): void { static::saving(fn ($r) => $r->avg_score = round(($r->on_time + $r->quality + $r->price_competitiveness + $r->communication) / 4, 1)); } public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); } public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); } }
