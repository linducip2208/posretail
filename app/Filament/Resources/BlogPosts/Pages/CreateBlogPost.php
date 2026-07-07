<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Services\Seo\ContentBroadcastService;
use App\Services\Seo\GoogleIndexingService;
use App\Services\Seo\IndexNowService;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        if ($record->is_published && $record->published_at && $record->published_at <= now()) {
            $blogUrl = route('blog.show', $record->slug);
            $blogListUrl = route('blog.index');
            $homeUrl = url('/');

            try {
                $indexNow = app(IndexNowService::class);
                $indexNow->submit([$blogUrl, $blogListUrl, $homeUrl]);
            } catch (\Throwable $e) {
                report($e);
            }

            try {
                app(GoogleIndexingService::class)->publish($blogUrl);
            } catch (\Throwable $e) {
                report($e);
            }

            try {
                app(ContentBroadcastService::class)->broadcastPost(
                    $record->title,
                    $blogUrl,
                    $record->excerpt,
                    $record->featured_image ? url('storage/' . $record->featured_image) : null,
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
