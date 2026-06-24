<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\PseoService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SitemapController extends Controller
{
    protected PseoService $pseo;

    public function __construct(PseoService $pseo)
    {
        $this->pseo = $pseo;
    }

    /**
     * Sitemap index — limits to 10k URLs per file to avoid memory exhaustion.
     * /sitemap.xml routes to this if we use index.
     * For shared hosting, just return the first 10000 URLs directly.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // Static pages (priority)
            $static = [
                ['url' => '/', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/docs', 'priority' => '0.8', 'freq' => 'weekly'],
                ['url' => '/pos', 'priority' => '0.7', 'freq' => 'monthly'],
                ['url' => '/blog', 'priority' => '0.9', 'freq' => 'daily'],
                ['url' => '/blog/feed.xml', 'priority' => '0.5', 'freq' => 'daily'],
                ['url' => '/faq', 'priority' => '0.6', 'freq' => 'monthly'],
                ['url' => '/contact', 'priority' => '0.6', 'freq' => 'monthly'],
                ['url' => '/beli-aplikasi-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/beli-source-code-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/jual-source-code-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/harga-source-code-pos', 'priority' => '0.9', 'freq' => 'daily'],
                ['url' => '/source-code-aplikasi-pos', 'priority' => '0.9', 'freq' => 'daily'],
            ];

            foreach ($static as $s) {
                $xml .= $this->urlTag($baseUrl, $s['url'], $s['priority'], $s['freq']);
            }

            // Blog posts
            BlogPost::published()->chunk(500, function ($posts) use (&$xml, $baseUrl) {
                foreach ($posts as $post) {
                    $xml .= $this->urlTag($baseUrl, '/blog/'.$post->slug, '0.8', 'weekly', $post->updated_at->format('Y-m-d'));
                }
            });

            // PSEO pages — stream in chunks, max 5000
            $pseoPages = $this->pseo->getAllPages();
            $count = 0;
            foreach ($pseoPages as $page) {
                if ($count >= 5000) break;
                $xml .= $this->urlTag($baseUrl, $page['url'], $page['priority'] ?? '0.5', 'monthly');
                $count++;
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    protected function urlTag(string $base, string $url, string $priority, string $freq, ?string $lastmod = null): string
    {
        $s = "  <url>\n";
        $s .= '    <loc>' . htmlspecialchars($base . $url, ENT_XML1, 'UTF-8') . "</loc>\n";
        if ($lastmod) {
            $s .= '    <lastmod>' . $lastmod . "</lastmod>\n";
        } else {
            $s .= '    <lastmod>' . date('Y-m-d') . "</lastmod>\n";
        }
        $s .= '    <changefreq>' . $freq . "</changefreq>\n";
        $s .= '    <priority>' . $priority . "</priority>\n";
        $s .= "  </url>\n";
        return $s;
    }

    public function html(): View
    {
        $pages = Cache::remember('sitemap.html', 86400, function () {
            $all = $this->pseo->getAllPages();
            return array_slice($all, 0, 5000);
        });

        $grouped = [];
        foreach ($pages as $page) {
            $grouped[$page['type']][] = $page;
        }

        $totalPages = count($pages);

        return view('pseo.sitemap', compact('grouped', 'totalPages'));
    }
}
