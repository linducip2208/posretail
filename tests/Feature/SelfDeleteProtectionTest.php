<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SelfDeleteProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_tidak_bisa_hapus_akun_sendiri(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $this->assertFalse(Gate::forUser($owner)->allows('delete', $owner));

        $owner->delete();
        $this->assertDatabaseHas('users', ['id' => $owner->id, 'deleted_at' => null]);
    }

    public function test_tidak_bisa_hapus_owner_terakhir(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($owner);

        $this->assertFalse(Gate::forUser($owner)->allows('delete', $owner));

        // Owner kedua boleh dihapus oleh owner pertama
        $owner2 = User::factory()->create(['role' => 'owner']);
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $owner2));
        $owner2->delete();
        $this->assertSoftDeleted('users', ['id' => $owner2->id]);

        // Non-owner boleh dihapus
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $admin));
    }

    public function test_force_delete_owner_terakhir_ditolak_model(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $owner->forceDelete();
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_outlet_tetap_butuh_akses_minimal_satu(): void
    {
        $outlet = Outlet::create(['name' => 'T', 'code' => 'T01', 'active' => true]);
        $owner = User::factory()->create(['role' => 'owner']);
        $owner->outlets()->attach($outlet->id);

        $this->assertNotEmpty($owner->fresh()->getAccessibleOutletIds());
    }
}
