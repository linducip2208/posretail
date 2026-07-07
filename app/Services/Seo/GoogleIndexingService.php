<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleIndexingService
{
    protected string $scope = 'https://www.googleapis.com/auth/indexing';
    protected string $tokenUri = 'https://oauth2.googleapis.com/token';
    protected string $publishUri = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

    protected ?array $credentials = null;

    public function __construct()
    {
        $path = config('seo.google_indexing.credentials_path');

        if ($path && is_file($path)) {
            $json = json_decode((string) @file_get_contents($path), true);
            if (is_array($json) && isset($json['client_email'], $json['private_key'])) {
                $this->credentials = $json;
            }
        }
    }

    public function isConfigured(): bool
    {
        return $this->credentials !== null;
    }

    public function publish(string $url, string $type = 'URL_UPDATED'): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Google Indexing API not configured'];
        }

        $token = $this->accessToken();
        if (! $token) {
            return ['success' => false, 'message' => 'Failed to obtain access token'];
        }

        try {
            $response = Http::timeout(15)
                ->withToken($token)
                ->post($this->publishUri, ['url' => $url, 'type' => $type]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::warning('GoogleIndexing publish failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function publishMany(array $urls, string $type = 'URL_UPDATED'): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Google Indexing API not configured', 'submitted' => 0];
        }

        $ok = 0;
        foreach (array_unique($urls) as $url) {
            if (($this->publish($url, $type)['success'] ?? false)) {
                $ok++;
            }
        }

        Log::info("GoogleIndexing: submitted {$ok}/" . count($urls) . ' URLs');

        return ['success' => true, 'submitted' => $ok, 'total' => count($urls)];
    }

    protected function accessToken(): ?string
    {
        return Cache::remember('google_indexing_token', 3300, function () {
            $jwt = $this->buildSignedJwt();
            if (! $jwt) {
                return null;
            }

            try {
                $response = Http::asForm()->timeout(15)->post($this->tokenUri, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

                return $response->successful() ? ($response->json('access_token') ?: null) : null;
            } catch (\Throwable $e) {
                Log::warning('GoogleIndexing token request failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    protected function buildSignedJwt(): ?string
    {
        $now = time();

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $this->credentials['client_email'],
            'scope' => $this->scope,
            'aud' => $this->tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claims)),
        ];
        $signingInput = implode('.', $segments);

        $signature = '';
        $ok = openssl_sign($signingInput, $signature, $this->credentials['private_key'], OPENSSL_ALGO_SHA256);
        if (! $ok) {
            return null;
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
