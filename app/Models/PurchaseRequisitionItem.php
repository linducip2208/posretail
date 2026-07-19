<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PurchaseRequisitionItem extends Model { use HasFactory; protected $fillable = ['purchase_requisition_id', 'product_id', 'quantity', 'current_stock_snapshot', 'reason']; public function requisition(): BelongsTo { return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id'); } public function product(): BelongsTo { return $this->belongsTo(Product::class); } }
