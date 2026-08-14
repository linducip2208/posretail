<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Beri poin referral ke customer yang mereferensikan, saat referal-nya
     * menyelesaikan order pertama. Idempotent — hanya sekali per customer.
     */
    public static function awardIfEligible(Order $order): void
    {
        if ($order->order_status !== 'completed') {
            return;
        }

        $customer = $order->customer;
        if (! $customer || ! $customer->referrer_id) {
            return;
        }

        $completedCount = Order::where('customer_id', $customer->id)
            ->where('order_status', 'completed')
            ->count();

        // Hanya reward di order pertama yang selesai.
        if ($completedCount > 1) {
            return;
        }

        $alreadyAwarded = LoyaltyPoint::where('customer_id', $customer->referrer_id)
            ->where('description', 'like', '%referral%'.$customer->id.'%')
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        $referrer = Customer::find($customer->referrer_id);
        if (! $referrer) {
            return;
        }

        $points = (int) SystemSetting::getValue('referral_points', '100');

        LoyaltyPoint::create([
            'customer_id' => $referrer->id,
            'order_id' => $order->id,
            'points_earned' => $points,
            'points_redeemed' => 0,
            'balance' => ($referrer->total_points ?? 0) + $points,
            'description' => 'Bonus referral customer #'.$customer->id.' ('.$customer->name.')',
        ]);

        $referrer->increment('total_points', $points);

        Log::info('Referral reward diberikan', [
            'referrer_id' => $referrer->id,
            'referred_customer_id' => $customer->id,
            'points' => $points,
        ]);
    }
}
