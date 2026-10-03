<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Sanitasi konten saat simpan: buang <script>, event handler, javascript: URL
        static::saving(function (BlogPost $post) {
            if (is_string($post->content)) {
                $clean = preg_replace('#<script.*?>.*?</script>#is', '', $post->content);
                $clean = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean ?? '');
                $clean = preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\']*\2/i', '$1=$2#$2', $clean ?? '');
                $post->content = strip_tags($clean ?? '', '<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><blockquote><a><img><table><thead><tbody><tr><th><td><pre><code><hr>');
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeByCategory(Builder $query, string $slug): Builder
    {
        return $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
