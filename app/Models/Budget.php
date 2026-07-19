<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Budget extends Model { use HasFactory; protected $fillable = ['outlet_id', 'year', 'month', 'revenue_target', 'expense_limit', 'actual_revenue', 'actual_expense']; public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); } }
