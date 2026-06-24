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

    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', 86400, function () {
            $pages = $this->pseo->getAllPages();

            // Add blog posts
            $blogPosts = BlogPost::published()->get();
            foreach ($blogPosts as $post) {
                $pages[] = [
                    'url' => '/blog/' . $post->slug,
                    'type' => 'blog',
                    'lastmod' => $post->updated_at->format('Y-m-d'),
                    'priority' => '0.8',
                ];
            }

            // Add static pages
            $pages[] = ['url' => '/blog', 'type' => 'static', 'lastmod' => date('Y-m-d'), 'priority' => '0.9'];
            $pages[] = ['url' => '/blog/feed.xml', 'type' => 'static', 'lastmod' => date('Y-m-d'), 'priority' => '0.5'];
            $pages[] = ['url' => '/faq', 'type' => 'static', 'lastmod' => date('Y-m-d'), 'priority' => '0.6'];
            $pages[] = ['url' => '/contact', 'type' => 'static', 'lastmod' => date('Y-m-d'), 'priority' => '0.6'];

            return $this->buildXml($pages);
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function html(): View
    {
        $pages = Cache::remember('sitemap.html', 86400, function () {
            return $this->pseo->getAllPages();
        });

        // Group by type
        $grouped = [];
        foreach ($pages as $page) {
            $grouped[$page['type']][] = $page;
        }

        $totalPages = count($pages);

        return view('pseo.sitemap', compact('grouped', 'totalPages'));
    }

    protected function buildXml(array $urls): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . $url['url'], ENT_XML1, 'UTF-8') . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($url['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . ($url['type'] === 'product' ? 'weekly' : 'monthly') . "</changefreq>\n";
            $xml .= '    <priority>' . ($url['priority'] ?? '0.5') . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }
}
