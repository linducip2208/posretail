<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'notification_type', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public static function isEnabled(int $userId, string $type): bool
    {
        $pref = static::where('user_id', $userId)->where('notification_type', $type)->first();
        return $pref ? $pref->enabled : true;
    }

    public static function types(): array
    {
        return ['low_stock' => 'Stok Rendah', 'overdue_order' => 'Order Jatuh Tempo', 'overdue_payable' => 'Hutang Jatuh Tempo', 'expiry_alert' => 'Kadaluarsa Produk', 'daily_report' => 'Laporan Harian'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
