<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaFontTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_includes_local_fonts(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        // Font Bunny yang di-bundle Vite harus dimuat lokal (bisa di-cache SW untuk PWA offline).
        $response->assertSee('/build/assets/', false);
        $response->assertDontSee('fonts.bunny.net');
        $response->assertDontSee('fonts.googleapis.com');
    }

    public function test_pos_display_includes_local_fonts(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $response = $this->actingAs($user)->get('/pos/display');
        $response->assertStatus(200);
        $response->assertSee('/build/assets/', false);
        $response->assertDontSee('fonts.bunny.net');
    }

    public function test_manifest_valid_for_pwa(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);
        $this->assertNotEmpty($manifest['start_url']);
        $this->assertNotEmpty($manifest['icons']);
        foreach ($manifest['icons'] as $icon) {
            if (($icon['type'] ?? '') === 'image/svg+xml') {
                continue;
            }
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')), $icon['src']);
        }
    }

    public function test_service_worker_caches_local_build_assets(): void
    {
        $sw = file_get_contents(public_path('sw.js'));
        // Cache harus berversi agar HTML lama (menunjuk hash /build lama) tidak bikin layar putih.
        $this->assertStringContainsString('pos-retail-v6', $sw);
        $this->assertStringContainsString('/build/', $sw);
        // Precaching harus toleran gagal: satu aset 404 tidak boleh menggagalkan install.
        $this->assertStringNotContainsString('cache.addAll', $sw);
    }
}
