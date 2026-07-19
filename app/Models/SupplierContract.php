<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SupplierContract extends Model { use HasFactory; protected $fillable = ['supplier_id', 'contract_number', 'start_date', 'end_date', 'value', 'terms', 'payment_terms', 'status', 'document_path']; protected function casts(): array { return ['start_date' => 'date', 'end_date' => 'date']; } public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); } }
