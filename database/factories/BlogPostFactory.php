<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);
        return [
            'title' => $title,
            'slug' => fake()->unique()->slug(4),
            'content' => '<p>' . implode('</p><p>', fake()->paragraphs(3)) . '</p>',
            'excerpt' => fake()->sentence(15),
            'featured_image' => null,
            'category_id' => BlogCategory::factory(),
            'author_id' => User::factory(['role' => 'admin']),
            'is_published' => true,
            'published_at' => now()->subDay(),
            'meta_title' => $title,
            'meta_description' => fake()->sentence(10),
        ];
    }
}
