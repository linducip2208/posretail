<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'value', 'outlet_id',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public static function getValue(string $key, mixed $default = null, ?int $outletId = null): mixed
    {
        $query = static::where('key', $key);

        if ($outletId !== null) {
            $value = (clone $query)->where('outlet_id', $outletId)->value('value');
            if ($value !== null) {
                return $value;
            }
        }

        return $query->orderByRaw('outlet_id IS NOT NULL')->value('value') ?? $default;
    }

    public static function setValue(string $key, mixed $value, ?int $outletId = null): void
    {
        static::updateOrCreate(
            ['key' => $key, 'outlet_id' => $outletId],
            ['value' => $value]
        );
    }

    public static function getLogoUrl(): ?string
    {
        $logo = static::getValue('app_logo');
        if (!$logo) return null;
        return asset('storage/' . $logo);
    }

    public static function getLoginIllustrationUrl(): ?string
    {
        $illustration = static::getValue('login_illustration');
        if (!$illustration) return null;
        return asset('storage/' . $illustration);
    }

    public static function getAppName(): string
    {
        return static::getValue('app_name', 'POS Retail');
    }
}

