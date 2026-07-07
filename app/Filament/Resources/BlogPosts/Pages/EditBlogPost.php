<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Services\Seo\GoogleIndexingService;
use App\Services\Seo\IndexNowService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($record->is_published && $record->published_at && $record->published_at <= now()) {
            $blogUrl = route('blog.show', $record->slug);
            $homeUrl = url('/');
            $blogListUrl = route('blog.index');

            try {
                $indexNow = app(IndexNowService::class);
                $indexNow->submit([$blogUrl, $blogListUrl, $homeUrl]);

                cache()->put("indexnow_blog_{$record->id}", now()->toDateTimeString(), now()->addDays(30));
            } catch (\Throwable $e) {
                report($e);
            }

            try {
                app(GoogleIndexingService::class)->publish($blogUrl);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
