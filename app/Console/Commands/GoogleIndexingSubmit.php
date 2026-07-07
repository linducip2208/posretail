<?php

namespace App\Console\Commands;

use App\Services\Seo\GoogleIndexingService;
use Illuminate\Console\Command;

class GoogleIndexingSubmit extends Command
{
    protected $signature = 'seo:google-index
                            {--url= : Submit a single URL}
                            {--type=URL_UPDATED : Notification type (URL_UPDATED or URL_DELETED)}';

    protected $description = 'Notify the Google Indexing API of updated/removed URLs';

    public function handle(GoogleIndexingService $service): int
    {
        if (! $service->isConfigured()) {
            $this->warn('Google Indexing API is not configured (set GOOGLE_INDEXING_CREDENTIALS). Skipping.');
            return self::SUCCESS;
        }

        $type = $this->option('type');

        if ($url = $this->option('url')) {
            $result = $service->publish($url, $type);
            $this->info('Submitted 1 URL. Success: ' . (($result['success'] ?? false) ? 'yes' : 'no'));
            return self::SUCCESS;
        }

        $urls = [
            url('/'),
            url('/docs'),
            url('/blog'),
            url('/beli-aplikasi-pos'),
        ];

        $result = $service->publishMany($urls, $type);
        $this->info("Submitted {$result['submitted']}/{$result['total']} URLs to Google Indexing API.");

        return self::SUCCESS;
    }
}
