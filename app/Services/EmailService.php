<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected ?Provider $provider = null;

    public function __construct()
    {
        $this->provider = Provider::where('type', 'email')
            ->where('active', true)
            ->first();
    }

    public function isConfigured(): bool { return $this->provider !== null; }

    public function sendInvoice(Order $order, string $toEmail): bool
    {
        if (!$this->provider || !$toEmail) return false;

        $subject = "Invoice #{$order->order_number} - " . \App\Models\SystemSetting::getAppName();
        $body = $this->buildInvoiceHtml($order);

        return $this->send($toEmail, $subject, $body);
    }

    public function sendOrderStatus(Order $order, string $toEmail): bool
    {
        if (!$this->provider || !$toEmail) return false;

        $statusLabels = [
            'completed' => 'Selesai', 'processing' => 'Diproses',
            'cancelled' => 'Dibatalkan', 'pending' => 'Menunggu',
        ];
        $status = $statusLabels[$order->order_status] ?? $order->order_status;
        $subject = "Pesanan #{$order->order_number} - {$status}";
        $body = $this->buildStatusHtml($order, $status);

        return $this->send($toEmail, $subject, $body);
    }

    public function send(string $to, string $subject, string $body): bool
    {
        if (!$this->provider) {
            Log::warning('Email: no provider configured');
            return false;
        }

        $apiKey = $this->provider->api_key_encrypted ? decrypt($this->provider->api_key_encrypted) : $this->provider->api_key;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post(rtrim($this->provider->base_url, '/') . '/send', [
                'from' => $this->provider->extra_headers['from'] ?? 'noreply@pos-retail.id',
                'to' => $to,
                'subject' => $subject,
                'html' => $body,
            ]);

            Log::info("Email sent to {$to}: {$subject}", ['status' => $response->status()]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Email send failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function buildInvoiceHtml(Order $order): string
    {
        $app = \App\Models\SystemSetting::getAppName();
        $items = $order->orderItems->map(function ($i) {
            $name = $i->product?->name ?? 'Produk';
            return "<tr><td style='padding:8px'>{$i->quantity}x {$name}</td><td style='padding:8px;text-align:right'>Rp " . number_format($i->subtotal, 0, ',', '.') . "</td></tr>";
        })->implode('');

        return "<html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px'>
            <h2 style='color:#1e40af'>{$app} — Invoice</h2>
            <p>No: <strong>{$order->order_number}</strong></p>
            <p>Tanggal: {$order->created_at->format('d M Y H:i')}</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0'>
                <thead><tr style='background:#f1f5f9'><th style='text-align:left;padding:8px'>Item</th><th style='text-align:right;padding:8px'>Subtotal</th></tr></thead>
                <tbody>{$items}</tbody>
            </table>
            <p style='font-size:18px;font-weight:bold;text-align:right'>Total: Rp " . number_format($order->total_amount, 0, ',', '.') . "</p>
            <p style='color:#64748b;font-size:12px'>Terima kasih telah berbelanja!</p>
        </body></html>";
    }

    protected function buildStatusHtml(Order $order, string $status): string
    {
        $app = \App\Models\SystemSetting::getAppName();
        return "<html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px'>
            <h2 style='color:#1e40af'>{$app}</h2>
            <p>Pesanan <strong>#{$order->order_number}</strong></p>
            <p>Status: <span style='color:#059669;font-weight:bold'>{$status}</span></p>
            <p>Total: <strong>Rp " . number_format($order->total_amount, 0, ',', '.') . "</strong></p>
        </body></html>";
    }
}
