<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_merender_nav_untuk_owner(): void
    {
        $outlet = \App\Models\Outlet::create(['name' => 'Toko', 'code' => 'T1', 'active' => true]);
        $owner = User::factory()->create(['role' => 'owner', 'active' => true]);
        $owner->outlets()->attach($outlet->id);
        $owner->roles()->create(['name' => 'Owner', 'slug' => 'owner', 'description' => 't']);
        \App\Models\Permission::create(['name' => 'Star', 'slug' => '*', 'group' => 'special']);
        $owner->roles()->first()->permissions()->attach(
            \App\Models\Permission::where('slug', '*')->first()->id
        );

        $res = $this->actingAs($owner)->get('/admin');
        if ($res->status() !== 200) {
            fwrite(STDERR, "\nSTATUS: ".$res->status()."\n");
            fwrite(STDERR, substr(strip_tags($res->getContent()), 0, 500)."\n");
        }
        $res->assertOk();
        // pastikan ada item navigasi ter-render
        $res->assertSee('Produk');
        // workaround bug Filament collapsedGroups=null ikut ter-render
        $res->assertSee('fixSidebarCollapsedGroups', false);
    }

    public function test_user_nonaktif_ditolak_masuk_panel(): void
    {
        $mati = User::factory()->create(['role' => 'kasir', 'active' => false]);

        $this->actingAs($mati)->get('/admin')->assertForbidden();
    }
}
