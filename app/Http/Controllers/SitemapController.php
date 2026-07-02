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

    protected int $chunkSize = 50000;

    public function __construct(PseoService $pseo)
    {
        $this->pseo = $pseo;
    }

    public function index(): Response
    {
        $xml = Cache::remember('sitemap.index', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $today = date('Y-m-d');

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            $xml .= "  <sitemap>\n    <loc>{$baseUrl}/sitemap-static.xml</loc>\n    <lastmod>{$today}</lastmod>\n  </sitemap>\n";

            $count = BlogPost::published()->count();
            if ($count > 0) {
                $xml .= "  <sitemap>\n    <loc>{$baseUrl}/sitemap-blog.xml</loc>\n    <lastmod>{$today}</lastmod>\n  </sitemap>\n";
            }

            $totalChunks = $this->pseo->getSitemapChunkCount($this->chunkSize);

            for ($i = 0; $i < $totalChunks; $i++) {
                $xml .= "  <sitemap>\n    <loc>{$baseUrl}/sitemap-pseo-{$i}.xml</loc>\n    <lastmod>{$today}</lastmod>\n  </sitemap>\n";
            }

            $xml .= '</sitemapindex>';
            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function staticSitemap(): Response
    {
        $xml = Cache::remember('sitemap.static', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');

            $static = [
                ['url' => '/', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/docs', 'priority' => '0.8', 'freq' => 'weekly'],
                ['url' => '/pos', 'priority' => '0.7', 'freq' => 'monthly'],
                ['url' => '/blog', 'priority' => '0.9', 'freq' => 'daily'],
                ['url' => '/faq', 'priority' => '0.6', 'freq' => 'monthly'],
                ['url' => '/contact', 'priority' => '0.6', 'freq' => 'monthly'],
                ['url' => '/beli-aplikasi-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/beli-source-code-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/jual-source-code-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/harga-source-code-pos', 'priority' => '0.9', 'freq' => 'daily'],
                ['url' => '/source-code-aplikasi-pos', 'priority' => '0.9', 'freq' => 'daily'],
                ['url' => '/sitemap', 'priority' => '0.5', 'freq' => 'monthly'],
            ];

            return $this->buildUrlset($static, $baseUrl);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function blogSitemap(): Response
    {
        $xml = Cache::remember('sitemap.blog', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $pages = [];

            BlogPost::published()->chunk(500, function ($posts) use (&$pages) {
                foreach ($posts as $post) {
                    $pages[] = [
                        'url' => '/blog/' . $post->slug,
                        'priority' => '0.8',
                        'freq' => 'weekly',
                        'lastmod' => $post->updated_at->format('Y-m-d'),
                    ];
                }
            });

            return $this->buildUrlset($pages, $baseUrl);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function pseoSitemap(int $chunk): Response
    {
        $cacheKey = "sitemap.pseo.{$chunk}";

        $xml = Cache::remember($cacheKey, 86400, function () use ($chunk) {
            $baseUrl = rtrim(config('app.url'), '/');
            $offset = $chunk * $this->chunkSize;

            $pages = $this->pseo->getSitemapChunk($offset, $this->chunkSize);

            return $this->buildUrlset($pages, $baseUrl);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    protected function buildUrlset(array $pages, string $baseUrl): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . $page['url'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= '    <lastmod>' . ($page['lastmod'] ?? date('Y-m-d')) . "</lastmod>\n";
            $xml .= '    <changefreq>' . ($page['freq'] ?? 'monthly') . "</changefreq>\n";
            $xml .= '    <priority>' . ($page['priority'] ?? '0.5') . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

    public function html(): View
    {
        $data = Cache::remember('sitemap.html', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $totalChunks = $this->pseo->getSitemapChunkCount($this->chunkSize);
            $totalPseoPages = $totalChunks * $this->chunkSize;
            $totalBlogPosts = BlogPost::published()->count();

            $samplePages = $this->pseo->getSitemapChunk(0, 200);
            $grouped = [];
            foreach ($samplePages as $page) {
                $type = $page['type'] ?? 'pseo';
                $grouped[$type][] = $page;
            }

            $stats = [
                'total_pseo_pages' => $totalPseoPages,
                'total_blog_posts' => $totalBlogPosts,
                'total_chunks' => $totalChunks,
                'chunk_size' => $this->chunkSize,
                'estimated_total' => $totalPseoPages + $totalBlogPosts + 12,
            ];

            return compact('grouped', 'stats');
        });

        return view('pseo.sitemap', $data);
    }
}
