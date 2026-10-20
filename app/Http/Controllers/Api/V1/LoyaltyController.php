<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    use ShopApi;

    public function summary(Request $request): JsonResponse
    {
        $request->validate(['customer_id' => 'required|integer|exists:customers,id']);
        $customer = Customer::findOrFail($request->customer_id);

        $earned = (float) LoyaltyPoint::where('customer_id', $customer->id)->sum('points_earned');
        $redeemed = (float) LoyaltyPoint::where('customer_id', $customer->id)->sum('points_redeemed');

        return response()->json(['data' => [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'total_points' => (float) ($customer->total_points ?? 0),
            'points_earned' => $earned,
            'points_redeemed' => $redeemed,
            'total_spent' => (float) ($customer->total_spent ?? 0),
        ]]);
    }

    public function rewards(Request $request): JsonResponse
    {
        $request->validate(['per_page' => 'nullable|integer|min:1']);

        return $this->envelope(
            LoyaltyReward::where('active', true)->orderBy('points_required')->paginate($this->perPage($request))
        );
    }

    public function redeem(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'transaksi', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'reward_id' => 'required|integer|exists:loyalty_rewards,id',
        ]);

        $customer = Customer::findOrFail($data['customer_id']);
        $reward = LoyaltyReward::where('active', true)->findOrFail($data['reward_id']);

        if ((float) ($customer->total_points ?? 0) < (float) $reward->points_required) {
            return response()->json(['message' => 'Poin pelanggan tidak cukup.'], 422);
        }

        DB::transaction(function () use ($customer, $reward) {
            $customer->decrement('total_points', $reward->points_required);
            LoyaltyPoint::create([
                'customer_id' => $customer->id,
                'order_id' => null,
                'points_earned' => 0,
                'points_redeemed' => $reward->points_required,
                'description' => 'Tukar reward: ' . $reward->name,
            ]);
        });

        return response()->json(['data' => [
            'customer_id' => $customer->id,
            'reward' => $reward->name,
            'remaining_points' => (float) $customer->fresh()->total_points,
        ]]);
    }
}
