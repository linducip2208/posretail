<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Guard permanen: setiap model Eloquent harus punya tabel yang bisa
 * dibuat oleh migrasi. Mencegah terulangnya kasus supplier_ratings
 * (model ada, tabel tidak ada saat runtime).
 */
class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_model_has_its_table(): void
    {
        $missing = [];
        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');
            if (! class_exists($class)) {
                continue;
            }
            $model = new $class;
            if (! $model instanceof \Illuminate\Database\Eloquent\Model) {
                continue;
            }
            if (! Schema::hasTable($model->getTable())) {
                $missing[] = $class.' -> '.$model->getTable();
            }
        }

        $this->assertSame([], $missing, 'Model tanpa tabel: '.implode(', ', $missing));
    }

    public function test_supplier_ratings_schema_matches_model(): void
    {
        foreach (['id', 'supplier_id', 'purchase_order_id', 'on_time', 'quality',
            'price_competitiveness', 'communication', 'avg_score', 'notes',
            'created_at', 'updated_at'] as $col) {
            $this->assertTrue(
                Schema::hasColumn('supplier_ratings', $col),
                "supplier_ratings missing column: {$col}"
            );
        }
    }

    public function test_orders_has_client_uuid_unique(): void
    {
        $this->assertTrue(Schema::hasColumn('orders', 'client_uuid'));
        $indexes = collect(Schema::getIndexes('orders'))->pluck('name');
        $this->assertTrue(
            $indexes->contains('orders_client_uuid_unique'),
            'orders.client_uuid unique index hilang'
        );
    }
}
