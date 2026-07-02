<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Outlet::factory()->create(['name' => 'Main Outlet', 'active' => true]);
        Product::factory()->count(5)->create(['active' => true]);
    }

    public function test_sales_export_csv_downloads_when_authenticated(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($user)->get('/export/laporan/penjualan?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_financial_export_csv_downloads_when_authenticated(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($user)->get('/export/laporan/keuangan?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_stock_export_csv_downloads_when_authenticated(): void
    {
        $user = User::factory()->create(['role' => 'gudang']);

        $response = $this->actingAs($user)->get('/export/laporan/stok?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_unauth_export_redirects_to_login(): void
    {
        $response = $this->get('/export/laporan/penjualan?format=csv');

        $this->assertTrue($response->status() >= 300 && $response->status() < 400);
    }
}
