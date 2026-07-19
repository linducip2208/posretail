<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_number', 'table_id', 'customer_id', 'customer_name',
        'customer_phone', 'reservation_date', 'time_slot', 'guest_count',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['reservation_date' => 'date'];
    }

    public function table(): BelongsTo { return $this->belongsTo(TableResto::class, 'table_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
