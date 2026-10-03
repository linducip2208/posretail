<?php

namespace App\Console\Commands;

use App\Services\Seo\IndexNowService;
use App\Services\PseoService;
use App\Models\BlogPost;
use Illuminate\Console\Command;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow
                            {--url= : Submit a single URL}
                            {--new : Submit only new URLs since last run}';

    protected $description = 'Submit URLs to IndexNow (Bing, Yandex, Seznam, Naver)';

    public function handle(IndexNowService $service): int
    {
        if ($this->option('url')) {
            $result = $service->submitSingle($this->option('url'));
            $this->info("Submitted 1 URL. Success: " . ($result['success'] ? 'yes' : 'no'));
            return 0;
        }

        if ($this->option('new')) {
            $this->info('Scanning URLs for new IndexNow submission...');
            $pages = app(PseoService::class)->getAllPages();
            $urls = array_map(fn (array $page) => url($page['url']), $pages);
            $urls = array_merge($urls, BlogPost::published()->pluck('slug')->map(fn ($slug) => url('/blog/'.$slug))->all());
            $this->info('Using generated sitemap pages...');
            $result = $service->submitNewOnly($urls);
            $this->info("New URLs submitted: {$result['submitted']}");
            return 0;
        }

        $this->info('Submitting default URLs to IndexNow...');
        $urls = [
            config('app.url') . '/',
            config('app.url') . '/docs',
            config('app.url') . '/blog',
            config('app.url') . '/faq',
            config('app.url') . '/contact',
        ];
        $result = $service->submit($urls);

        if ($result['success']) {
            $this->info("Successfully submitted {$result['submitted']} URLs.");
        } else {
            $this->error('Submission failed.');
        }

        return 0;
    }
}
