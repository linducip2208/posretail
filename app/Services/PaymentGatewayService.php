<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Provider;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    protected Provider $provider;

    protected array $config;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
        $this->config = $this->buildConfig();
    }

    protected function buildConfig(): array
    {
        $config = [
            'base_url' => $this->provider->base_url,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if ($this->provider->api_key_encrypted) {
            $config['api_key'] = $this->provider->decryptApiKey();
            $config['headers']['Authorization'] = 'Basic ' . base64_encode($config['api_key'] . ':');
        }

        if ($this->provider->api_secret_encrypted) {
            $config['api_secret'] = $this->provider->decryptApiSecret();
        }

        if ($this->provider->merchant_id) {
            $config['merchant_id'] = $this->provider->merchant_id;
        }

        if ($this->provider->client_id) {
            $config['client_id'] = $this->provider->client_id;
        }

        if ($this->provider->extra_headers) {
            $config['headers'] = array_merge($config['headers'], $this->provider->extra_headers);
        }

        if ($this->provider->extra_config) {
            $config = array_merge($config, $this->provider->extra_config);
        }

        return $config;
    }

    public function createTransaction(array $orderData): array
    {
        return match ($this->provider->api_format) {
            'rest-redirect' => $this->createRestRedirect($orderData),
            'rest-api' => $this->createRestApi($orderData),
            'qr-static' => $this->createQrStatic($orderData),
            default => throw new \Exception("Unsupported API format: {$this->provider->api_format}"),
        };
    }

    protected function createRestRedirect(array $orderData): array
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $orderData['order_number'],
                'gross_amount' => (int) $orderData['amount'],
            ],
            'customer_details' => $orderData['customer'] ?? [],
            'items' => $orderData['items'] ?? [],
            'callbacks' => [
                'finish' => $orderData['finish_url'] ?? config('app.url') . '/payment/finish',
                'unfinish' => $orderData['unfinish_url'] ?? config('app.url') . '/payment/unfinish',
            ],
        ];

        $response = Http::withHeaders($this->config['headers'])
            ->post($this->config['base_url'] . '/transactions', $payload);

        $this->logResponse('create_redirect', $response, $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'redirect_url' => $response->json('redirect_url'),
                'token' => $response->json('token'),
                'raw' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('error_messages.0') ?? 'Payment gateway error',
            'raw' => $response->json(),
        ];
    }

    protected function createRestApi(array $orderData): array
    {
        $externalId = $orderData['order_number'];
        $amount = (int) $orderData['amount'];

        $payload = [
            'external_id' => $externalId,
            'amount' => $amount,
            'description' => $orderData['description'] ?? 'Payment for ' . $externalId,
            'customer' => $orderData['customer'] ?? [],
            'success_redirect_url' => $orderData['finish_url'] ?? config('app.url') . '/payment/finish',
            'failure_redirect_url' => $orderData['unfinish_url'] ?? config('app.url') . '/payment/unfinish',
        ];

        $response = Http::withHeaders($this->config['headers'])
            ->post($this->config['base_url'] . '/invoices', $payload);

        $this->logResponse('create_invoice', $response, $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'redirect_url' => $response->json('invoice_url'),
                'invoice_id' => $response->json('id'),
                'raw' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message') ?? 'Payment gateway error',
            'raw' => $response->json(),
        ];
    }

    protected function createQrStatic(array $orderData): array
    {
        $payload = [
            'amount' => (int) $orderData['amount'],
            'order_id' => $orderData['order_number'],
        ];

        $response = Http::withHeaders($this->config['headers'])
            ->post($this->config['base_url'] . '/qr', $payload);

        $this->logResponse('create_qr', $response, $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'qr_code' => $response->json('qr_code'),
                'qr_string' => $response->json('qr_string'),
                'raw' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message') ?? 'QR generation failed',
            'raw' => $response->json(),
        ];
    }

    public function checkTransactionStatus(string $transactionId): array
    {
        $response = Http::withHeaders($this->config['headers'])
            ->get($this->config['base_url'] . '/transactions/' . $transactionId . '/status');

        $this->logResponse('check_status', $response);

        return [
            'success' => $response->successful(),
            'status' => $response->json('transaction_status') ?? $response->json('status'),
            'raw' => $response->json(),
        ];
    }

    public function processWebhook(array $payload): array
    {
        Log::info('Payment webhook received', [
            'provider' => $this->provider->name,
            'payload' => $payload,
        ]);

        $orderNumber = $this->extractOrderNumber($payload);
        $status = $this->extractStatus($payload);

        if (! $orderNumber) {
            return ['success' => false, 'message' => 'Order number not found in payload'];
        }

        $payment = Payment::whereHas('order', fn ($q) => $q->where('order_number', $orderNumber))
            ->latest()
            ->first();

        if (! $payment) {
            Log::warning('Payment not found for webhook', ['order_number' => $orderNumber]);
            return ['success' => false, 'message' => 'Payment not found'];
        }

        $newStatus = match ($status) {
            'settlement', 'success', 'SUCCESS', 'paid' => 'success',
            'pending', 'PENDING' => 'pending',
            'expire', 'failure', 'deny', 'cancel' => 'failed',
            'refund', 'chargeback' => 'refunded',
            default => null,
        };

        if ($newStatus) {
            $payment->update(['status' => $newStatus]);

            if ($newStatus === 'success') {
                $payment->order->update(['payment_status' => 'paid']);
            }

            Log::info('Payment status updated via webhook', [
                'order_number' => $orderNumber,
                'old_status' => $payment->getOriginal('status'),
                'new_status' => $newStatus,
            ]);
        }

        return ['success' => true, 'status' => $newStatus];
    }

    protected function extractOrderNumber(array $payload): ?string
    {
        return $payload['order_id']
            ?? $payload['external_id']
            ?? $payload['transaction_details']['order_id']
            ?? $payload['order']['order_number']
            ?? null;
    }

    protected function extractStatus(array $payload): ?string
    {
        return $payload['transaction_status']
            ?? $payload['status']
            ?? $payload['state']
            ?? null;
    }

    protected function logResponse(string $action, Response $response, ?array $payload = null): void
    {
        Log::debug("Payment gateway: {$action}", [
            'provider' => $this->provider->name,
            'format' => $this->provider->api_format,
            'status' => $response->status(),
            'payload' => $payload,
            'response' => $response->json(),
        ]);
    }

    public static function forPaymentMethod(int $paymentMethodId): ?self
    {
        $method = PaymentMethod::with('provider')->find($paymentMethodId);

        if (! $method?->provider || ! $method->is_gateway) {
            return null;
        }

        return new self($method->provider);
    }
}
