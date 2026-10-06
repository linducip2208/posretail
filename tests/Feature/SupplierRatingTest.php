<?php

namespace Tests\Feature;

use App\Filament\Resources\SupplierRatings\SupplierRatingResource;
use App\Models\Outlet;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierRatingTest extends TestCase
{
    use RefreshDatabase;

    protected function ctx(): array
    {
        $a = Outlet::create(['name' => 'A', 'code' => 'A1', 'active' => true]);
        $b = Outlet::create(['name' => 'B', 'code' => 'B1', 'active' => true]);
        $kasir = User::factory()->create(['role' => 'kasir']);
        $kasir->outlets()->attach($a->id);
        $supplier = Supplier::create(['name' => 'S1']);
        $poA = PurchaseOrder::create([
            'po_number' => 'PO-A-1', 'supplier_id' => $supplier->id,
            'outlet_id' => $a->id, 'user_id' => $kasir->id,
            'total_amount' => 100000, 'status' => 'received',
        ]);
        $poB = PurchaseOrder::create([
            'po_number' => 'PO-B-1', 'supplier_id' => $supplier->id,
            'outlet_id' => $b->id, 'user_id' => $kasir->id,
            'total_amount' => 50000, 'status' => 'received',
        ]);

        return compact('a', 'b', 'kasir', 'supplier', 'poA', 'poB');
    }

    public function test_avg_score_computed_on_save(): void
    {
        ['supplier' => $s, 'poA' => $po] = $this->ctx();
        $r = SupplierRating::create([
            'supplier_id' => $s->id, 'purchase_order_id' => $po->id,
            'on_time' => 5, 'quality' => 4, 'price_competitiveness' => 3,
            'communication' => 4, 'notes' => 'ok',
        ]);

        $this->assertEquals(4.0, (float) $r->fresh()->avg_score);
    }

    public function test_listing_scoped_to_accessible_outlets(): void
    {
        ['supplier' => $s, 'poA' => $poA, 'poB' => $poB, 'kasir' => $k] = $this->ctx();
        SupplierRating::create(['supplier_id' => $s->id, 'purchase_order_id' => $poA->id]);
        SupplierRating::create(['supplier_id' => $s->id, 'purchase_order_id' => $poB->id]);

        $this->actingAs($k);
        $visible = SupplierRatingResource::getEloquentQuery()->pluck('purchase_order_id');

        $this->assertTrue($visible->contains($poA->id));
        $this->assertFalse($visible->contains($poB->id));
    }

    public function test_user_without_access_sees_nothing(): void
    {
        ['supplier' => $s, 'poA' => $poA, 'poB' => $poB] = $this->ctx();
        // User tanpa permission '*' dan tanpa outlet: fail-closed.
        // (Owner sungguhan mendapat '*' via RolePermissionSeeder di production.)
        $nobody = User::factory()->create(['role' => 'kasir']);
        SupplierRating::create(['supplier_id' => $s->id, 'purchase_order_id' => $poA->id]);
        SupplierRating::create(['supplier_id' => $s->id, 'purchase_order_id' => $poB->id]);

        $this->actingAs($nobody);
        $this->assertCount(0, SupplierRatingResource::getEloquentQuery()->get());
    }
}
