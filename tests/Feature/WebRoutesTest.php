<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_200(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_docs_page_returns_200(): void
    {
        $response = $this->get('/docs');
        $response->assertStatus(200);
    }

    public function test_sitemap_returns_valid_xml(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
    }

    public function test_robots_txt_returns_200(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
    }

    public function test_auth_user_redirects_to_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect('/admin');
    }

    public function test_pseo_routes_exist(): void
    {
        $this->assertTrue(route('pseo.best-category', ['slug' => 'test']) !== null);
        $this->assertTrue(route('pseo.alternatives-to', ['slug' => 'test']) !== null);
    }

    public function test_admin_login_page_returns_200(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_api_login_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
    }

    public function test_api_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(401);
    }
}
