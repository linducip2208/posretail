<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'api_format', 'base_url',
        'api_key_encrypted', 'api_secret_encrypted',
        'merchant_id', 'client_id',
        'extra_headers', 'extra_config',
        'is_active', 'is_default',
    ];

    protected $casts = [
        'extra_headers' => 'array',
        'extra_config' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function decryptApiKey(): ?string
    {
        return $this->api_key_encrypted ? decrypt($this->api_key_encrypted) : null;
    }

    public function decryptApiSecret(): ?string
    {
        return $this->api_secret_encrypted ? decrypt($this->api_secret_encrypted) : null;
    }
}
