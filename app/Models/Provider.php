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
        'webhook_secret_encrypted',
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

    /**
     * Baca preset template payment gateway dari storage/app/payment-presets/*.json.
     * Hanya untuk autofill convenience — tidak pernah direference saat runtime.
     */
    public static function presets(): array
    {
        $path = storage_path('app/payment-presets');

        if (! is_dir($path)) {
            return [];
        }

        $presets = [];

        foreach (glob($path.'/*.json') ?: [] as $file) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded) && isset($decoded['name'])) {
                $presets[basename($file, '.json')] = $decoded;
            }
        }

        return $presets;
    }

    public function decryptApiKey(): ?string
    {
        return $this->api_key_encrypted ? decrypt($this->api_key_encrypted) : null;
    }

    public function decryptApiSecret(): ?string
    {
        return $this->api_secret_encrypted ? decrypt($this->api_secret_encrypted) : null;
    }

    public function decryptWebhookSecret(): ?string
    {
        return $this->webhook_secret_encrypted ? decrypt($this->webhook_secret_encrypted) : null;
    }
}
