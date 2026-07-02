<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
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
        }
    }
}
