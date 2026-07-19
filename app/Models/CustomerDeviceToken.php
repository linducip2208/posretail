<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CustomerDeviceToken extends Model { use HasFactory; protected $fillable = ['customer_id', 'token', 'platform']; public function customer(): BelongsTo { return $this->belongsTo(Customer::class); } }
