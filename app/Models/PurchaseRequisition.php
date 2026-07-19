<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasOutletScope;

class PurchaseRequisition extends Model
{
    use HasFactory, HasOutletScope;

    protected $fillable = ['pr_number', 'outlet_id', 'requested_by', 'approved_by', 'date_needed', 'status', 'notes', 'rejection_reason', 'submitted_at', 'approved_at'];
    protected function casts(): array { return ['date_needed' => 'date', 'submitted_at' => 'datetime', 'approved_at' => 'datetime']; }
    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany { return $this->hasMany(PurchaseRequisitionItem::class); }

    public static function generateNumber(): string
    {
        $prefix = 'PR-' . now()->format('Ymd') . '-';
        $last = static::where('pr_number', 'like', $prefix . '%')
            ->orderBy('pr_number', 'desc')
            ->first();
        $next = $last ? (int) substr($last->pr_number, -4) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
