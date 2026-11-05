<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Permission;
use App\Models\RawMaterial;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $kasir;

    protected Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kasir = User::factory()->create(['role' => 'kasir']);
        $this->outlet = Outlet::create(['name' => 'Toko Pusat', 'code' => 'TP001', 'active' => true]);
        foreach ([$this->owner, $this->kasir] as $u) {
            $u->outlets()->attach($this->outlet->id);
        }

        $star = Permission::create(['name' => 'Super Admin', 'slug' => '*', 'group' => 'special']);
        $ownerRole = Role::create(['name' => 'Owner', 'slug' => 'owner', 'description' => 'test']);
        $ownerRole->permissions()->attach($star->id);
        $this->owner->roles()->attach($ownerRole->id);

        $kasirRole = Role::create(['name' => 'Kasir', 'slug' => 'kasir', 'description' => 'test']);
        $this->kasir->roles()->attach($kasirRole->id);
    }

    public function test_kategori_bisa_simpan_lihat_dan_update(): void
    {
        $this->actingAs($this->owner, 'api');

        // Slug otomatis dari nama
        $created = $this->postJson('/api/v1/categories', ['name' => 'Minuman Dingin'])
            ->assertCreated()->json('data');
        $this->assertSame('minuman-dingin', $created['slug']);

        $this->getJson('/api/v1/categories/'.$created['id'])
            ->assertOk()->assertJsonPath('data.name', 'Minuman Dingin');

        // Nama sama → slug unik otomatis
        $kedua = $this->postJson('/api/v1/categories', ['name' => 'Minuman Dingin'])
            ->assertCreated()->json('data');
        $this->assertSame('minuman-dingin-2', $kedua['slug']);

        $updated = $this->putJson('/api/v1/categories/'.$created['id'], ['name' => 'Minuman Hangat'])
            ->assertOk()->json('data');
        $this->assertSame('minuman-hangat', $updated['slug']);

        // Tidak bisa menjadi induk diri sendiri
        $this->putJson('/api/v1/categories/'.$created['id'], ['parent_id' => $created['id']])
            ->assertStatus(422);

        // Kasir tanpa izin inventori ditolak
        $this->actingAs($this->kasir, 'api');
        $this->postJson('/api/v1/categories', ['name' => 'Coba'])->assertForbidden();
        $this->putJson('/api/v1/categories/'.$created['id'], ['name' => 'Coba'])->assertForbidden();
    }

    public function test_brand_bisa_simpan_dan_update(): void
    {
        $this->actingAs($this->owner, 'api');

        $created = $this->postJson('/api/v1/brands', ['name' => 'Kapal Api'])
            ->assertCreated()->json('data');
        $this->assertSame('kapal-api', $created['slug']);

        $this->getJson('/api/v1/brands/'.$created['id'])->assertOk();

        $updated = $this->putJson('/api/v1/brands/'.$created['id'], ['description' => 'Kopi bubuk'])
            ->assertOk()->json('data');
        $this->assertSame('Kopi bubuk', $updated['description']);
        // Slug tidak berubah bila nama tidak dikirim
        $this->assertSame('kapal-api', $updated['slug']);

        $this->actingAs($this->kasir, 'api');
        $this->postJson('/api/v1/brands', ['name' => 'Coba'])->assertForbidden();
        $this->putJson('/api/v1/brands/'.$created['id'], ['name' => 'Coba'])->assertForbidden();
    }

    public function test_satuan_bisa_simpan_dan_update(): void
    {
        $this->actingAs($this->owner, 'api');

        $created = $this->postJson('/api/v1/units', ['name' => 'Pieces', 'code' => 'PCS'])
            ->assertCreated()->json('data');
        $this->assertSame('PCS', $created['code']);

        // Kode duplikat ditolak
        $this->postJson('/api/v1/units', ['name' => 'Pcs Lagi', 'code' => 'PCS'])
            ->assertStatus(422);

        $updated = $this->putJson('/api/v1/units/'.$created['id'], ['name' => 'Pcs'])
            ->assertOk()->json('data');
        $this->assertSame('Pcs', $updated['name']);

        $this->actingAs($this->kasir, 'api');
        $this->postJson('/api/v1/units', ['name' => 'Coba', 'code' => 'CB'])->assertForbidden();
        $this->putJson('/api/v1/units/'.$created['id'], ['name' => 'Coba'])->assertForbidden();
    }

    public function test_customer_bisa_update(): void
    {
        $customer = Customer::create([
            'name' => 'Budi', 'phone' => '0811', 'active' => true,
        ]);

        $this->actingAs($this->owner, 'api');
        $updated = $this->putJson('/api/v1/customers/'.$customer->id, [
            'phone' => '0822', 'address' => 'Jl. Merdeka 1',
        ])->assertOk()->json('data');
        $this->assertSame('0822', $updated['phone']);

        // Kasir: customer tanpa order di outletnya → 403
        $this->actingAs($this->kasir, 'api');
        $this->putJson('/api/v1/customers/'.$customer->id, ['phone' => '0833'])
            ->assertForbidden();

        // Kasir: customer yang punya order di outletnya → boleh
        Order::factory()->create(['customer_id' => $customer->id, 'outlet_id' => $this->outlet->id]);
        $this->putJson('/api/v1/customers/'.$customer->id, ['phone' => '0844'])
            ->assertOk()->assertJsonPath('data.phone', '0844');
    }

    public function test_supplier_bisa_update(): void
    {
        $this->actingAs($this->owner, 'api');

        $supplier = Supplier::create(['name' => 'PT Maju', 'active' => true]);

        $updated = $this->putJson('/api/v1/suppliers/'.$supplier->id, [
            'contact_person' => 'Andi', 'phone' => '0800',
        ])->assertOk()->json('data');
        $this->assertSame('Andi', $updated['contact_person']);

        $this->actingAs($this->kasir, 'api');
        $this->putJson('/api/v1/suppliers/'.$supplier->id, ['phone' => '0811'])
            ->assertForbidden();
    }

    public function test_bahan_baku_bisa_update(): void
    {
        $this->actingAs($this->owner, 'api');

        $unit = Unit::create(['name' => 'Kilogram', 'code' => 'KG']);
        $created = $this->postJson('/api/v1/raw-materials', [
            'name' => 'Gula Pasir', 'code' => 'RM-GULA',
            'outlet_id' => $this->outlet->id, 'unit_id' => $unit->id,
            'current_stock' => 50, 'min_stock' => 5,
        ])->assertCreated()->json('data');

        $updated = $this->putJson('/api/v1/raw-materials/'.$created['id'], [
            'current_stock' => 75,
        ])->assertOk()->json('data');
        $this->assertEquals(75, (float) $updated['current_stock']);

        // Kode duplikat milik record lain ditolak
        $this->postJson('/api/v1/raw-materials', [
            'name' => 'Gula Batu', 'code' => 'RM-GULA',
            'outlet_id' => $this->outlet->id, 'unit_id' => $unit->id,
        ])->assertStatus(422);

        // Tanpa code/unit kini 422 (bukan 500)
        $this->postJson('/api/v1/raw-materials', [
            'name' => 'Tanpa Kode', 'outlet_id' => $this->outlet->id,
        ])->assertStatus(422);

        $this->actingAs($this->kasir, 'api');
        $this->putJson('/api/v1/raw-materials/'.$created['id'], ['current_stock' => 1])
            ->assertForbidden();
    }

    public function test_endpoint_baru_tetap_butuh_auth(): void
    {
        foreach ([
            ['post', '/api/v1/categories'],
            ['post', '/api/v1/brands'],
            ['post', '/api/v1/units'],
        ] as [$method, $uri]) {
            $this->json($method, $uri, [])->assertStatus(401);
        }
    }
}
