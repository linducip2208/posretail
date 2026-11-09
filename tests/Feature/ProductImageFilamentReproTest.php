<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Outlet;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductImageFilamentReproTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->outlet = Outlet::create(['name' => 'Toko Pusat', 'code' => 'TP001', 'active' => true]);
        $this->owner->outlets()->attach($this->outlet->id);
        $star = Permission::create(['name' => 'Super Admin', 'slug' => '*', 'group' => 'special']);
        $ownerRole = Role::create(['name' => 'Owner', 'slug' => 'owner', 'description' => 'test']);
        $ownerRole->permissions()->attach($star->id);
        $this->owner->roles()->attach($ownerRole->id);
    }

    public function test_admin_edit_bisa_ganti_gambar(): void
    {
        $product = Product::create([
            'name' => 'Kopi Test', 'slug' => 'kopi-test', 'sku' => 'SKU001',
            'cost_price' => 10000, 'selling_price' => 15000,
            'current_stock' => 10, 'active' => true, 'outlet_id' => $this->outlet->id,
            'image' => 'products/kopi-test.jpg',
        ]);

        $file = UploadedFile::fake()->image('baru.jpg', 400, 400);

        Livewire::actingAs($this->owner)
            ->test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.image', $file)
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertNotEquals('products/kopi-test.jpg', $product->image);
        $this->assertNotNull($product->image);
    }

    public function test_admin_create_bisa_tambah_gambar(): void
    {
        $file = UploadedFile::fake()->image('baru.jpg', 400, 400);

        Livewire::actingAs($this->owner)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Teh Test',
                'sku' => 'SKU002',
                'cost_price' => 5000,
                'selling_price' => 8000,
                'current_stock' => 5,
                'active' => true,
                'image' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', ['slug' => 'teh-test']);
        $p = Product::where('slug', 'teh-test')->first();
        $this->assertNotNull($p->image);
    }
}
