<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Scopes\OutletScope;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function webhook(Request $request, string $providerCode): JsonResponse
    {
        $provider = Provider::where('is_active', true)
            ->where('type', 'payment')
            ->whereHas('paymentMethods', fn ($q) => $q->where('code', $providerCode))
            ->first();

        if (! $provider) {
            return response()->json(['message' => 'Provider not found'], 404);
        }

        if (! (new PaymentGatewayService($provider))->verifyWebhookSignature($request)) {
            return response()->json(['message' => 'Invalid webhook signature'], 401);
        }

        $service = new PaymentGatewayService($provider);
        $result = $service->processWebhook($request->all());

        return response()->json($result);
    }

    public function createTransaction(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'order_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'items' => 'nullable|array',
            'customer' => 'nullable|array',
        ]);

        $service = PaymentGatewayService::forPaymentMethod($request->payment_method_id);

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran ini tidak menggunakan payment gateway.',
            ], 400);
        }

        // Anti amount-tampering: nominal diambil dari sisa tagihan order di
        // server, bukan dari angka kiriman client. Order harus milik outlet user.
        $user = $request->user();
        // Lepas global scope agar bisa bedakan 404 vs 403 eksplisit.
        $order = \App\Models\Order::withoutGlobalScope(OutletScope::class)
            ->where('order_number', $request->order_number)->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        if (! $user->hasPermission('*') && ! in_array((int) $order->outlet_id, $user->getAccessibleOutletIds(), true)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada akses ke order ini.'], 403);
        }

        $remaining = max(0, (float) $order->total_amount - (float) $order->payments()->where('status', 'success')->sum('amount'));
        if ($remaining <= 0) {
            return response()->json(['success' => false, 'message' => 'Order sudah lunas.'], 422);
        }

        if (abs((float) $request->amount - $remaining) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal harus sama dengan sisa tagihan: '.number_format($remaining, 0, ',', '.'),
            ], 422);
        }

        $result = $service->createTransaction([
            'order_number' => $request->order_number,
            'amount' => $remaining,
            'items' => $request->items ?? [],
            'customer' => $request->customer ?? [
                'first_name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
            'description' => 'Pembayaran #' . $request->order_number,
            'finish_url' => $request->finish_url,
            'unfinish_url' => $request->unfinish_url,
        ]);

        return response()->json($result);
    }

    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'transaction_id' => 'required|string',
        ]);

        $service = PaymentGatewayService::forPaymentMethod($request->payment_method_id);

        if (! $service) {
            return response()->json(['success' => false, 'message' => 'Provider not found'], 400);
        }

        $result = $service->checkTransactionStatus($request->transaction_id);

        return response()->json($result);
    }

    public function presets(): JsonResponse
    {
        return response()->json(Provider::presets());
    }
}
