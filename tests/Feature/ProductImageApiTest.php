<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $kasir;

    protected Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kopi Susu',
            'cost_price' => 10000,
            'selling_price' => 15000,
            'outlet_id' => $this->outlet->id,
        ], $overrides);
    }

    public function test_store_produk_dengan_gambar_multipart(): void
    {
        $this->actingAs($this->owner, 'api');

        $file = UploadedFile::fake()->image('kopi.jpg', 400, 400);

        $res = $this->post('/api/v1/products', $this->validPayload(['image' => $file]))
            ->assertCreated()
            ->json('data');

        $this->assertNotEmpty($res['image']);
        $this->assertNotEmpty($res['image_url']);
        Storage::disk('public')->assertExists($res['image']);
        $this->assertStringStartsWith('products/', $res['image']);
    }

    public function test_store_produk_dengan_image_base64(): void
    {
        $this->actingAs($this->owner, 'api');

        // PNG 1x1 valid
        $base64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $res = $this->postJson('/api/v1/products', $this->validPayload(['image_base64' => $base64]))
            ->assertCreated()
            ->json('data');

        $this->assertNotEmpty($res['image']);
        $this->assertNotEmpty($res['image_url']);
        Storage::disk('public')->assertExists($res['image']);
    }

    public function test_update_gambar_mengganti_dan_menghapus_file_lama(): void
    {
        $this->actingAs($this->owner, 'api');

        $old = $this->post('/api/v1/products', $this->validPayload([
            'name' => 'Teh Manis',
            'image' => UploadedFile::fake()->image('lama.jpg', 400, 400),
        ]))->assertCreated()->json('data');

        Storage::disk('public')->assertExists($old['image']);

        $new = $this->put('/api/v1/products/'.$old['id'], [
            'image' => UploadedFile::fake()->image('baru.png', 400, 400),
        ])->assertOk()->json('data');

        $this->assertNotEquals($old['image'], $new['image']);
        Storage::disk('public')->assertExists($new['image']);
        Storage::disk('public')->assertMissing($old['image']);
        $this->assertNotEmpty($new['image_url']);
    }

    public function test_update_tanpa_gambar_tidak_menghapus_gambar_lama(): void
    {
        $this->actingAs($this->owner, 'api');

        $created = $this->post('/api/v1/products', $this->validPayload([
            'name' => 'Susu Jahe',
            'image' => UploadedFile::fake()->image('tetap.jpg', 400, 400),
        ]))->assertCreated()->json('data');

        $updated = $this->putJson('/api/v1/products/'.$created['id'], [
            'selling_price' => 18000,
        ])->assertOk()->json('data');

        $this->assertEquals($created['image'], $updated['image']);
        $this->assertEquals(18000, (int) $updated['selling_price']);
    }

    public function test_remove_image_menghapus_file(): void
    {
        $this->actingAs($this->owner, 'api');

        $created = $this->post('/api/v1/products', $this->validPayload([
            'name' => 'Kopi Hitam',
            'image' => UploadedFile::fake()->image('hapus.jpg', 400, 400),
        ]))->assertCreated()->json('data');

        $updated = $this->putJson('/api/v1/products/'.$created['id'], [
            'remove_image' => true,
        ])->assertOk()->json('data');

        $this->assertNull($updated['image']);
        $this->assertNull($updated['image_url']);
        Storage::disk('public')->assertMissing($created['image']);
    }

    public function test_kasir_tanpa_izin_inventori_ditolak(): void
    {
        $this->actingAs($this->kasir, 'api');

        $this->postJson('/api/v1/products', $this->validPayload())
            ->assertForbidden();

        $product = Product::create([
            'name' => 'Gula', 'slug' => 'gula-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'cost_price' => 5000, 'selling_price' => 8000,
            'current_stock' => 10, 'active' => true,
        ]);

        $this->putJson('/api/v1/products/'.$product->id, ['selling_price' => 9000])
            ->assertForbidden();
    }

    public function test_image_url_accessor_menangani_nilai_lama(): void
    {
        // URL penuh dari data lama → dikembalikan apa adanya
        $p = new Product(['image' => 'https://cdn.example.com/foto.jpg']);
        $this->assertSame('https://cdn.example.com/foto.jpg', $p->image_url);

        // Kosong → null
        $p2 = new Product(['image' => null]);
        $this->assertNull($p2->image_url);

        // Path di disk public → URL public
        Storage::disk('public')->put('products/lama.jpg', 'dummy');
        $p3 = new Product(['image' => 'products/lama.jpg']);
        $this->assertNotNull($p3->image_url);
        $this->assertStringContainsString('products/lama.jpg', $p3->image_url);
    }

    public function test_index_memuat_image_url(): void
    {
        $this->actingAs($this->owner, 'api');

        $created = $this->post('/api/v1/products', $this->validPayload([
            'image' => UploadedFile::fake()->image('katalog.jpg', 400, 400),
        ]))->assertCreated()->json('data');

        $this->getJson('/api/v1/products?search=Kopi%20Susu')
            ->assertOk()
            ->assertJsonFragment(['id' => $created['id'], 'image_url' => $created['image_url']]);
    }

    public function test_update_via_post_multipart_dan_barcode_tetap_aman(): void
    {
        $this->actingAs($this->owner, 'api');

        $created = $this->postJson('/api/v1/products', $this->validPayload([
            'name' => 'Kopi Tubruk',
            'barcode' => '8991000000007',
        ]))->assertCreated()->json('data');

        // Rute POST /products/barcode tidak dibayangi rute POST /products/{product}
        $this->postJson('/api/v1/products/barcode', ['barcode' => '8991000000007'])
            ->assertOk()
            ->assertJsonPath('data.id', $created['id']);

        // Update gambar via POST multipart (pola umum aplikasi mobile)
        $updated = $this->post('/api/v1/products/'.$created['id'], [
            'image' => UploadedFile::fake()->image('mobile.jpg', 400, 400),
        ])->assertOk()->json('data');

        $this->assertNotEmpty($updated['image']);
        $this->assertNotEmpty($updated['image_url']);
        Storage::disk('public')->assertExists($updated['image']);
    }
}
