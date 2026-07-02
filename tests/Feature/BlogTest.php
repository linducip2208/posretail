<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_renders(): void
    {
        BlogCategory::factory()->create(['name' => 'Tutorial', 'slug' => 'tutorial']);

        $response = $this->get('/blog');
        $response->assertStatus(200);
    }

    public function test_blog_post_detail_renders(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Tips', 'slug' => 'tips']);
        $user = User::factory()->create(['role' => 'admin']);

        $post = BlogPost::factory()->create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'content' => '<p>Test content here</p>',
            'excerpt' => 'Test excerpt',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/blog/test-blog-post');
        $response->assertStatus(200);
        $response->assertSee('Test Blog Post');
    }

    public function test_blog_category_filter_renders(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Tutorial', 'slug' => 'tutorial']);

        $response = $this->get('/blog/category/tutorial');
        $response->assertStatus(200);
    }

    public function test_unpublished_post_returns_404(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Tips', 'slug' => 'tips']);
        $user = User::factory()->create(['role' => 'admin']);

        BlogPost::factory()->create([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'content' => '<p>Draft content</p>',
            'excerpt' => 'Draft',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'is_published' => false,
            'published_at' => null,
        ]);

        $response = $this->get('/blog/draft-post');
        $response->assertStatus(404);
    }

    public function test_blog_json_ld_schema_present(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Tips', 'slug' => 'tips']);
        $user = User::factory()->create(['role' => 'admin']);

        BlogPost::factory()->create([
            'title' => 'Schema Test',
            'slug' => 'schema-test',
            'content' => '<p>Content</p>',
            'excerpt' => 'Excerpt',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/blog/schema-test');
        $response->assertSee('application/ld+json');
    }
}
