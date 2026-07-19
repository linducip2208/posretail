<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Provider;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Service — kirim pesan via ChatGo / Meta Cloud API / Generic Provider.
 *
 * Pattern provider (dynamic, user-configured via admin UI):
 * - ChatGo:         POST {base_url}/api/send   | Header: X-API-Key
 * - Meta Cloud API: POST graph.facebook.com/v22.0/{phone_id}/messages | Header: Authorization: Bearer
 * - Generic:        POST {base_url}/messages    | Header: Authorization: Bearer
 */
class WhatsAppService
{
    protected ?Provider $provider = null;
    protected ?string $apiFormat = null;

    public function __construct()
    {
        $this->provider = Provider::where('type', 'whatsapp')
            ->where('active', true)
            ->first();

        $this->apiFormat = $this->provider?->api_format ?? 'generic';
    }

    public function isConfigured(): bool
    {
        return $this->provider !== null;
    }

    public function getApiFormat(): string
    {
        return $this->apiFormat;
    }

    // ─── Public API ────────────────────────────────────────────

    public function sendReceipt(Order $order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) {
            return false;
        }

        $phone = $this->normalizePhone($customer->phone);
        $message = $this->buildReceiptMessage($order);

        return $this->send($phone, $message);
    }

    public function sendOrderStatus(Order $order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) {
            return false;
        }

        $phone = $this->normalizePhone($customer->phone);

        $statusMap = [
            'completed'  => "✅ Pesanan #{$order->order_number} telah selesai.\nTotal: Rp " . number_format($order->total_amount, 0, ',', '.'),
            'cancelled'  => "❌ Pesanan #{$order->order_number} telah dibatalkan.",
            'processing' => "⏳ Pesanan #{$order->order_number} sedang diproses.",
        ];

        $message = $statusMap[$order->order_status]
            ?? "Status pesanan #{$order->order_number}: {$order->order_status}";

        return $this->send($phone, $message);
    }

    public function sendPaymentReminder(Order $order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) {
            return false;
        }

        $phone = $this->normalizePhone($customer->phone);
        $message = "⏰ Pengingat Pembayaran\n\n" .
                   "Pesanan: #{$order->order_number}\n" .
                   "Total: Rp " . number_format($order->total_amount, 0, ',', '.') . "\n" .
                   "Status: " . ($order->payment_status === 'partial' ? 'Belum Lunas' : 'Menunggu Pembayaran') . "\n\n" .
                   "Silakan selesaikan pembayaran Anda.";

        return $this->send($phone, $message);
    }

    public function sendCustom(string $phone, string $message): bool
    {
        return $this->send($this->normalizePhone($phone), $message);
    }

    // ─── Sender dispatcher ─────────────────────────────────────

    protected function send(string $to, string $message): bool
    {
        if (!$this->provider) {
            Log::warning('WhatsApp: No active provider configured');
            return false;
        }

        return match ($this->apiFormat) {
            'chatgo'       => $this->sendViaChatGo($to, $message),
            'meta'         => $this->sendViaMeta($to, $message),
            'generic'      => $this->sendViaGeneric($to, $message),
            default        => $this->sendViaGeneric($to, $message),
        };
    }

    // ─── ChatGo format ─────────────────────────────────────────
    // POST /api/send  |  Header: X-API-Key
    // Body: { phone: "628xxx", message: "...", account_phone?: "628xxx" }

    protected function sendViaChatGo(string $to, string $message): bool
    {
        $baseUrl = rtrim($this->provider->base_url, '/');
        $apiKey = $this->decryptKey();
        $accountPhone = $this->provider->extra_headers['account_phone'] ?? null;

        try {
            $payload = [
                'phone'   => $to,
                'message' => $message,
            ];
            if ($accountPhone) {
                $payload['account_phone'] = $accountPhone;
            }

            $response = Http::withHeaders([
                'X-API-Key'     => $apiKey,
                'Content-Type'  => 'application/json',
            ])->post($baseUrl . '/api/send', $payload);

            Log::info('WhatsApp (ChatGo) sent', [
                'to'     => $to,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp (ChatGo) error: ' . $e->getMessage());
            return false;
        }
    }

    // ─── Meta Cloud API format ─────────────────────────────────
    // POST https://graph.facebook.com/v22.0/{phone_number_id}/messages
    // Header: Authorization: Bearer {access_token}

    protected function sendViaMeta(string $to, string $message): bool
    {
        $accessToken = $this->decryptKey();
        $phoneNumberId = $this->provider->extra_headers['phone_number_id'] ?? null;

        if (!$phoneNumberId) {
            Log::error('WhatsApp (Meta): phone_number_id not configured in provider extra_headers');
            return false;
        }

        $baseUrl = $this->provider->base_url
            ?: 'https://graph.facebook.com/v22.0';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type'  => 'application/json',
            ])->post(rtrim($baseUrl, '/') . '/' . $phoneNumberId . '/messages', [
                'messaging_product' => 'whatsapp',
                'to'                => $to,
                'type'              => 'text',
                'text'              => ['body' => $message],
            ]);

            Log::info('WhatsApp (Meta) sent', [
                'to'     => $to,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp (Meta) error: ' . $e->getMessage());
            return false;
        }
    }

    // ─── Generic REST format ───────────────────────────────────
    // POST {base_url}/messages  |  Header: Authorization: Bearer {key}

    protected function sendViaGeneric(string $to, string $message): bool
    {
        $baseUrl = rtrim($this->provider->base_url, '/');
        $apiKey = $this->decryptKey();

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->post($baseUrl . '/messages', [
                'to'      => $to,
                'type'    => 'text',
                'text'    => ['body' => $message],
            ]);

            Log::info('WhatsApp (Generic) sent', [
                'to'     => $to,
                'status' => $response->status(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp (Generic) error: ' . $e->getMessage());
            return false;
        }
    }

    // ─── Helpers ───────────────────────────────────────────────

    protected function decryptKey(): ?string
    {
        if (!$this->provider) {
            return null;
        }

        if ($this->provider->api_key_encrypted) {
            return decrypt($this->provider->api_key_encrypted);
        }

        return $this->provider->api_key;
    }

    protected function buildReceiptMessage(Order $order): string
    {
        $app = SystemSetting::getAppName();
        $lines = [
            "🧾 *{$app}*",
            "No: {$order->order_number}",
            "Tgl: " . $order->created_at->translatedFormat('d M Y H:i'),
            "────────────────",
        ];

        foreach ($order->orderItems as $item) {
            $name = $item->product?->name ?? 'Produk';
            $variant = $item->productVariant?->name;
            $label = $variant ? "{$name} ({$variant})" : $name;
            $lines[] = "{$item->quantity}x {$label} — Rp " . number_format($item->subtotal, 0, ',', '.');
        }

        $lines[] = "────────────────";
        if (($order->discount_amount ?? 0) > 0) {
            $lines[] = "Diskon: Rp " . number_format($order->discount_amount, 0, ',', '.');
        }
        if (($order->tax_amount ?? 0) > 0) {
            $lines[] = "Pajak: Rp " . number_format($order->tax_amount, 0, ',', '.');
        }
        $lines[] = "*Total: Rp " . number_format($order->total_amount, 0, ',', '.') . "*";

        if ($order->payment_status !== 'paid') {
            $lines[] = "";
            $lines[] = "Status: ⚠️ " . strtoupper($order->payment_status);
        }

        $footer = SystemSetting::getValue('receipt_footer', 'Terima kasih telah berbelanja!');
        $lines[] = "";
        $lines[] = "_{$footer}_";

        return implode("\n", $lines);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
