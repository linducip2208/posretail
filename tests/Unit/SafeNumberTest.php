<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Support\SafeNumber;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SafeNumberTest extends TestCase
{
    public function test_check_stock_rejects_scientific_notation_overflow(): void
    {
        // Kasus persis dari produksi: current_stock = 1.0E+22.
        $this->assertNotNull(SafeNumber::checkStock(1.0E+22, 'Stok saat ini'));
        $this->assertNotNull(SafeNumber::checkStock('1.0E+22', 'Stok saat ini'));
        $this->assertNotNull(SafeNumber::checkStock(99999999999999, 'Stok maksimum'));
    }

    public function test_check_stock_accepts_normal_values(): void
    {
        $this->assertNull(SafeNumber::checkStock(0, 'Stok saat ini'));
        $this->assertNull(SafeNumber::checkStock(150, 'Stok saat ini'));
        $this->assertNull(SafeNumber::checkStock('150', 'Stok saat ini'));
        $this->assertNull(SafeNumber::checkStock(null, 'Stok saat ini'));
        $this->assertNull(SafeNumber::checkStock('', 'Stok saat ini'));
        $this->assertNull(SafeNumber::checkStock(SafeNumber::STOCK_MAX, 'Stok saat ini'));
    }

    public function test_check_stock_rejects_negative_and_fraction(): void
    {
        $this->assertNotNull(SafeNumber::checkStock(-5, 'Stok saat ini'));
        $this->assertNotNull(SafeNumber::checkStock(1.5, 'Stok saat ini'));
        $this->assertNotNull(SafeNumber::checkStock('abc', 'Stok saat ini'));
    }

    public function test_check_money_accepts_decimals_but_rejects_overflow(): void
    {
        $this->assertNull(SafeNumber::checkMoney(11000, 'Harga jual'));
        $this->assertNull(SafeNumber::checkMoney('11000.50', 'Harga jual'));
        $this->assertNotNull(SafeNumber::checkMoney(-1, 'Harga jual'));
        $this->assertNotNull(SafeNumber::checkMoney(1.0E+22, 'Harga jual'));
    }

    public function test_product_save_with_huge_stock_throws_validation_exception(): void
    {
        // Guard berjalan sebelum query apa pun: tidak butuh DB.
        $this->expectException(ValidationException::class);

        $product = new Product([
            'name' => 'Tes Overflow',
            'current_stock' => 1.0E+22,
            'min_stock' => 0,
            'max_stock' => 0,
        ]);
        $product->save();
    }

    public function test_guard_product_attributes_reports_all_bad_fields(): void
    {
        try {
            SafeNumber::guardProductAttributes([
                'current_stock' => -1,
                'cost_price' => -50,
                'selling_price' => 10000,
            ]);
            $this->fail('Seharusnya melempar ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('current_stock', $e->errors());
            $this->assertArrayHasKey('cost_price', $e->errors());
            $this->assertArrayNotHasKey('selling_price', $e->errors());
        }
    }

    public function test_guard_variant_attributes(): void
    {
        $this->expectException(ValidationException::class);

        SafeNumber::guardVariantAttributes(['current_stock' => 1.0E+22]);
    }
}
