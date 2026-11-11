<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_for_admin(): void
    {
        // active diisi default DB (true); refresh agar atribut ada di model memori.
        $user = User::factory()->create(['role' => 'admin', 'active' => true]);
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('fi-boot-guard', false);
    }
}
