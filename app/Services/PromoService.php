<?php

namespace App\Services;

use App\Models\DiscountTemplate;
use Carbon\Carbon;

/**
 * Promo engine: percent / fixed / buy_x_get_y / happy_hour.
 * Pilih diskon TERBAIK (max) agar tidak bisa di-stack abuse.
 */
class PromoService
{
    /**
     * @param array $lines [['product_id'=>int,'quantity'=>int,'unit_price'=>float,'subtotal'=>float]]
     * @return array ['discount'=>float,'promo'=>?DiscountTemplate]
     */
    public function bestDiscount(int $outletId, float $subtotal, array $lines, ?Carbon $now = null): array
    {
        $now ??= now();
        $today = $now->toDateString();

        $promos = DiscountTemplate::where('active', true)
            ->where(function ($q) use ($outletId) {
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->get();

        $best = ['discount' => 0.0, 'promo' => null];

        foreach ($promos as $promo) {
            $discount = $this->discountFor($promo, $subtotal, $lines, $now);
            if ($discount > $best['discount']) {
                $best = ['discount' => $discount, 'promo' => $promo];
            }
        }

        return $best;
    }

    protected function discountFor(DiscountTemplate $promo, float $subtotal, array $lines, Carbon $now): float
    {
        if ($subtotal < (float) ($promo->min_purchase ?? 0)) {
            return 0.0;
        }

        return match ($promo->type) {
            'percent' => round($subtotal * (float) $promo->value / 100, 2),
            'fixed' => min((float) $promo->value, $subtotal),
            'buy_x_get_y' => $this->bogoDiscount($promo, $lines),
            'happy_hour' => $this->happyHourDiscount($promo, $subtotal, $now),
            default => 0.0,
        };
    }

    protected function bogoDiscount(DiscountTemplate $promo, array $lines): float
    {
        $buy = max(1, (int) $promo->buy_quantity);
        $get = max(0, (int) $promo->get_quantity);
        if ($get <= 0) {
            return 0.0;
        }

        $discount = 0.0;
        foreach ($lines as $line) {
            // Jika promo terikat produk tertentu, skip produk lain
            if ($promo->product_id && (int) $line['product_id'] !== (int) $promo->product_id) {
                continue;
            }
            $qty = (int) $line['quantity'];
            $free = intdiv($qty, $buy + $get) * $get;
            // Sisa: jika sisa >= buy, dapat proporsional? Tidak — hanya kelipatan penuh (anti abuse)
            $discount += $free * (float) $line['unit_price'];
        }

        return round($discount, 2);
    }

    protected function happyHourDiscount(DiscountTemplate $promo, float $subtotal, Carbon $now): float
    {
        // Filter hari (1=Senin..7=Minggu, format Carbon dayOfWeekIso)
        if ($promo->days) {
            $allowed = collect(explode(',', $promo->days))->map(fn ($d) => (int) trim($d))->filter()->all();
            if ($allowed && ! in_array($now->dayOfWeekIso, $allowed, true)) {
                return 0.0;
            }
        }
        // Filter jam
        if ($promo->happy_start && $promo->happy_end) {
            $t = $now->format('H:i:s');
            if ($t < $promo->happy_start || $t > $promo->happy_end) {
                return 0.0;
            }
        }

        return round($subtotal * (float) $promo->value / 100, 2);
    }
}
