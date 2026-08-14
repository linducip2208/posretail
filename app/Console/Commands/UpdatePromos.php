<?php

namespace App\Console\Commands;

use App\Models\DiscountTemplate;
use App\Models\GiftCard;
use Illuminate\Console\Command;

class UpdatePromos extends Command
{
    protected $signature = 'pos:update-promos';

    protected $description = 'Auto-activate/deactivate promo (flash sale) dan voucher berdasarkan periode berlaku';

    public function handle(): int
    {
        $now = now();

        // Aktifkan promo yang sudah masuk periode
        $activated = DiscountTemplate::query()
            ->where('active', false)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->update(['active' => true]);

        // Nonaktifkan promo yang periode-nya sudah lewat
        $deactivated = DiscountTemplate::query()
            ->where('active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '<', $now)
                    ->orWhere('start_date', '>', $now);
            })
            ->update(['active' => false]);

        // Tandai voucher yang sudah kadaluarsa
        $expiredVouchers = GiftCard::query()
            ->whereIn('status', ['active', 'used'])
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', $now)
            ->update(['status' => 'expired']);

        $this->info("Promo diaktifkan: {$activated}, dinonaktifkan: {$deactivated}, voucher expired: {$expiredVouchers}");

        return self::SUCCESS;
    }
}
