<?php

namespace App\Http\Middleware;

use App\Services\LicenseClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block ALL routes (admin, user, storefront, anything) until the app is paired
 * to a license. Only `/__pair*` and a small dev-allowlist are accessible
 * without a valid .license.lock for the current host.
 */
class RequirePair
{
    public function __construct(private LicenseClient $client) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $domain = strtolower($request->getHost());
        $data   = $this->client->verify($domain);

        if ($data) {
            $request->attributes->set('license', $data);
            return $next($request);
        }

        // Not paired (or invalid/tampered) — redirect to wizard
        return redirect()->to('/__pair');
    }

    private function shouldBypass(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        // Always allow the wizard itself
        if (str_starts_with($path, '/__pair')) return true;

        // Health check / debug
        if ($path === '/up') return true;
        if (str_starts_with($path, '/_debugbar')) return true;

        // Public marketing & docs (accessible without license)
        if ($path === '/' || $path === '') return true;
        if ($path === '/docs' || str_starts_with($path, '/docs/')) return true;
        if ($path === '/sitemap.xml' || str_starts_with($path, '/sitemap-')) return true;
        if ($path === '/sitemap') return true;
        if ($path === '/robots.txt') return true;
        if (str_starts_with($path, '/marketing/')) return true;

        // Blog (public content, SEO)
        if (str_starts_with($path, '/blog')) return true;

        // FAQ & Contact (public)
        if ($path === '/faq' || $path === '/contact') return true;

        // PSEO routes (public marketing pages)
        $pseoPrefixes = [
            '/beli-', '/jual-', '/harga-', '/source-code-',
            '/bandingkan/', '/compare/', '/alternatif-', '/alternatives-to-',
            '/best-', '/aplikasi-pos-', '/aplikasi-kasir-', '/aplikasi-toko-',
            '/software-kasir-', '/sistem-kasir-', '/program-kasir-',
            '/point-of-sale-', '/pos-system-', '/daftar-', '/rekomendasi-',
            '/tips-', '/cara-', '/review-', '/pos-',
        ];
        foreach ($pseoPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) return true;
        }

        // Portal customer area (accessible without license)
        if (str_starts_with($path, '/portal')) return true;

        // API & webhooks (external integrations need to call these)
        if (str_starts_with($path, '/api/')) return true;

        // Localhost dev bypass
        if (config('license.dev_bypass') && (app()->environment('local') || app()->environment('testing'))) {
            $host = $request->getHost();
            if ($this->isDevHost($host)) return true;
        }

        return false;
    }

    private function isDevHost(string $host): bool
    {
        return $host === 'localhost'
            || $host === '127.0.0.1'
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');
    }
}
