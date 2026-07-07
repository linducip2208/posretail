<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentBroadcastService
{
    public function isConfigured(): bool
    {
        return ! empty(config('seo.broadcast_webhook'));
    }

    public function broadcast(array $payload): array
    {
        $webhook = (string) config('seo.broadcast_webhook');

        if ($webhook === '') {
            return ['success' => false, 'message' => 'Broadcast webhook not configured'];
        }

        try {
            $response = Http::timeout(15)->post($webhook, array_merge([
                'site' => config('app.name'),
                'sent_at' => now()->toIso8601String(),
            ], $payload));

            return ['success' => $response->successful(), 'status' => $response->status()];
        } catch (\Throwable $e) {
            Log::warning('ContentBroadcast failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function broadcastPost(string $title, string $url, ?string $excerpt = null, ?string $image = null): array
    {
        return $this->broadcast([
            'type' => 'blog_post',
            'title' => $title,
            'url' => $url,
            'excerpt' => $excerpt,
            'image' => $image,
        ]);
    }
}
