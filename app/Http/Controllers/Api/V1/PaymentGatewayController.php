<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Provider;
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

        $result = $service->createTransaction([
            'order_number' => $request->order_number,
            'amount' => $request->amount,
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
        $path = storage_path('app/payment-presets.json');

        if (! file_exists($path)) {
            return response()->json([]);
        }

        return response()->json(json_decode(file_get_contents($path), true));
    }
}
