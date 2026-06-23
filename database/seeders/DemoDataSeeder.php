<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;

class DemoDataSeeder extends Seeder
{
    private array $outletIds = [1, 2, 3];
    private array $categoryIds = [1, 2, 3, 4, 5, 6, 7, 8];
    private array $brandIds = [1, 2, 3, 4, 5];
    private array $unitIds = [1, 2, 3, 4, 5];
    private array $groupIds = [1, 2, 3];
    private array $paymentMethodIds = [1, 2, 3, 4, 5, 6];
    private array $rewardIds = [1, 2, 3];
    private array $supplierIds = [1, 2, 3, 4, 5];
    private array $userIdIds = [1, 2, 3, 4, 5];
    private array $productIds = [];
    private array $variantIds = [];
    private array $customerIds = [];
    private array $products = [];

    public function run(): void
    {
        $faker = FakerFactory::create('id_ID');
        $now = now();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear in reverse FK order
        $tables = [
            'cash_drawer_transactions', 'shifts', 'held_carts',
            'payable_payments', 'supplier_payables',
            'return_items', 'returns',
            'kitchen_tickets', 'installments',
            'recipe_items', 'raw_materials', 'discount_templates',
            'tables', 'table_areas',
            'attendances',
            'stock_opname_items', 'stock_opnames',
            'stock_transfer_items', 'stock_transfers',
            'loyalty_points',
            'purchase_order_items', 'purchase_orders',
            'payments', 'order_items', 'orders',
            'stock_movements',
            'product_variants', 'products',
            'customers',
            'membership_tiers',
            'payment_methods', 'providers',
            'suppliers', 'loyalty_rewards',
            'customer_groups', 'units', 'brands',
            'system_settings', 'user_outlet',
            'audit_logs',
            'categories',
            'outlets', 'users',
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ============================================================
        // PHASE 0: USERS (5 demo users)
        // ============================================================
        $this->seedUsers($now);

        // ============================================================
        // PHASE 1: MASTER DATA
        // ============================================================
        $this->seedOutlets($now);
        $this->seedCategories($now);
        $this->seedBrands($now);
        $this->seedUnits($now);
        $this->seedCustomerGroups($now);
        $this->seedProviders($now);
        $this->seedPaymentMethods($now);
        $this->seedLoyaltyRewards($now);
        $this->seedSuppliers($now);
        $this->seedSystemSettings($now);
        $this->seedUserOutlet($now);

        // ============================================================
        // PHASE 2: PRODUCTS (50)
        // ============================================================
        $this->seedProducts($faker, $now);

        // ============================================================
        // PHASE 3: PRODUCT VARIANTS (10)
        // ============================================================
        $this->seedProductVariants($now);

        // ============================================================
        // PHASE 4: CUSTOMERS (200)
        // ============================================================
        $this->seedCustomers($faker, $now);

        // ============================================================
        // PHASE 5: ORDERS + ORDER ITEMS + PAYMENTS + STOCK MOVEMENTS (500)
        // ============================================================
        $this->seedOrders($faker, $now);

        // ============================================================
        // PHASE 6: PURCHASE ORDERS (20)
        // ============================================================
        $this->seedPurchaseOrders($now);

        // ============================================================
        // PHASE 7: STOCK OPNAMES (5)
        // ============================================================
        $this->seedStockOpnames($now);

        // ============================================================
        // PHASE 8: LOYALTY POINTS
        // ============================================================
        $this->seedLoyaltyPoints($now);

        // ============================================================
        // PHASE 9: MEMBERSHIP TIERS
        // ============================================================
        $this->seedMembershipTiers($now);

        // ============================================================
        // PHASE 10: STOCK TRANSFERS (5)
        // ============================================================
        $this->seedStockTransfers($now);

        // ============================================================
        // PHASE 11: RETURNS (5)
        // ============================================================
        $this->seedReturns($now);

        // ============================================================
        // PHASE 12: INSTALLMENTS (for some orders)
        // ============================================================
        $this->seedInstallments($now);

        // ============================================================
        // PHASE 13: SUPPLIER PAYABLES (5)
        // ============================================================
        $this->seedSupplierPayables($now);

        // ============================================================
        // PHASE 14: SHIFTS & CASH DRAWER
        // ============================================================
        $this->seedShifts($now);

        // ============================================================
        // PHASE 15: HELD CARTS (3)
        // ============================================================
        $this->seedHeldCarts($now);

        // ============================================================
        // PHASE 16: NEW FEATURES — Tables, Raw Materials, Discount Templates, Attendance
        // ============================================================
        $this->seedTableAreas($now);
        $this->seedTables($now);
        $this->seedRawMaterials($now);
        $this->seedRecipeItems($now);
        $this->seedDiscountTemplates($now);
        $this->seedAttendances($now);

        $this->command?->info('Demo data seeded: 500 orders, 200 customers, 50 products, 20 POs, and more!');
    }

    // ================================================================
    // USERS
    // ================================================================
    private function seedUsers(Carbon $now): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Budi Hartono',
                'email' => 'owner@pos-retail.test',
                'email_verified_at' => $now,
                'password' => bcrypt('password'),
                'role' => 'owner',
                'remember_token' => Str::random(10),
                'created_at' => $now->copy()->subMonths(6),
                'updated_at' => $now,
            ],
            [
                'name' => 'Sari Dewi',
                'email' => 'manager@pos-retail.test',
                'email_verified_at' => $now,
                'password' => bcrypt('password'),
                'role' => 'manager',
                'remember_token' => Str::random(10),
                'created_at' => $now->copy()->subMonths(5),
                'updated_at' => $now,
            ],
            [
                'name' => 'Agus Prasetyo',
                'email' => 'admin@pos-retail.test',
                'email_verified_at' => $now,
                'password' => bcrypt('password'),
                'role' => 'admin',
                'remember_token' => Str::random(10),
                'created_at' => $now->copy()->subMonths(4),
                'updated_at' => $now,
            ],
            [
                'name' => 'Rina Safitri',
                'email' => 'kasir@pos-retail.test',
                'email_verified_at' => $now,
                'password' => bcrypt('password'),
                'role' => 'kasir',
                'remember_token' => Str::random(10),
                'created_at' => $now->copy()->subMonths(3),
                'updated_at' => $now,
            ],
            [
                'name' => 'Doni Kusuma',
                'email' => 'gudang@pos-retail.test',
                'email_verified_at' => $now,
                'password' => bcrypt('password'),
                'role' => 'gudang',
                'remember_token' => Str::random(10),
                'created_at' => $now->copy()->subMonths(2),
                'updated_at' => $now,
            ],
        ]);
    }

    // ================================================================
    // OUTLETS
    // ================================================================
    private function seedOutlets(Carbon $now): void
    {
        DB::table('outlets')->insert([
            [
                'name' => 'Toko Pusat',
                'code' => 'TP001',
                'address' => 'Jl. Raya Malioboro No. 10, Yogyakarta',
                'phone' => '0274-555001',
                'active' => true,
                'created_at' => $now->copy()->subMonths(12),
                'updated_at' => $now,
            ],
            [
                'name' => 'Cabang Timur',
                'code' => 'CT002',
                'address' => 'Jl. Solo Raya KM 5, Klaten',
                'phone' => '0272-555002',
                'active' => true,
                'created_at' => $now->copy()->subMonths(8),
                'updated_at' => $now,
            ],
            [
                'name' => 'Cabang Barat',
                'code' => 'CB003',
                'address' => 'Jl. Magelang KM 8, Sleman',
                'phone' => '0274-555003',
                'active' => true,
                'created_at' => $now->copy()->subMonths(6),
                'updated_at' => $now,
            ],
        ]);
    }

    // ================================================================
    // CATEGORIES
    // ================================================================
    private function seedCategories(Carbon $now): void
    {
        DB::table('categories')->insert([
            ['name' => 'Makanan Ringan', 'slug' => 'makanan-ringan', 'description' => 'Snack, keripik, biskuit, dan camilan', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Minuman', 'slug' => 'minuman', 'description' => 'Air mineral, teh, kopi, sirup, dan minuman kemasan', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bumbu Dapur', 'slug' => 'bumbu-dapur', 'description' => 'Kecap, sambal, penyedap rasa, dan bumbu masak', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sabun & Kebersihan', 'slug' => 'sabun-kebersihan', 'description' => 'Sabun mandi, deterjen, pembersih lantai, dan perawatan rumah', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Alat Tulis', 'slug' => 'alat-tulis', 'description' => 'Buku, pulpen, pensil, dan perlengkapan kantor', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Elektronik Kecil', 'slug' => 'elektronik-kecil', 'description' => 'Baterai, lampu, kabel charger, dan aksesoris', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rokok', 'slug' => 'rokok', 'description' => 'Rokok filter, kretek, dan tembakau', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sembako', 'slug' => 'sembako', 'description' => 'Beras, gula, minyak goreng, telur, tepung', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    // ================================================================
    // BRANDS
    // ================================================================
    private function seedBrands(Carbon $now): void
    {
        DB::table('brands')->insert([
            ['name' => 'Indofood', 'slug' => 'indofood', 'description' => 'PT Indofood Sukses Makmur Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Wings', 'slug' => 'wings', 'description' => 'Wings Group Indonesia', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Unilever', 'slug' => 'unilever', 'description' => 'PT Unilever Indonesia Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sido Muncul', 'slug' => 'sido-muncul', 'description' => 'PT Industri Jamu dan Farmasi Sido Muncul Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gudang Garam', 'slug' => 'gudang-garam', 'description' => 'PT Gudang Garam Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    // ================================================================
    // UNITS
    // ================================================================
    private function seedUnits(Carbon $now): void
    {
        DB::table('units')->insert([
            ['name' => 'PCS', 'code' => 'PCS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'KG', 'code' => 'KG', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'BOX', 'code' => 'BOX', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'LTR', 'code' => 'LTR', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'RENCENG', 'code' => 'RENCENG', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    // ================================================================
    // CUSTOMER GROUPS
    // ================================================================
    private function seedCustomerGroups(Carbon $now): void
    {
        DB::table('customer_groups')->insert([
            [
                'name' => 'Regular',
                'discount_percent' => 0,
                'min_spent' => 0,
                'description' => 'Pelanggan baru / tidak terdaftar',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Silver',
                'discount_percent' => 3,
                'min_spent' => 500000,
                'description' => 'Total belanja > 500rb dapat diskon 3%',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gold',
                'discount_percent' => 5,
                'min_spent' => 2000000,
                'description' => 'Total belanja > 2jt dapat diskon 5%',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    // ================================================================
    // PROVIDERS
    // ================================================================
    private function seedProviders(Carbon $now): void
    {
        DB::table('providers')->insert([
            [
                'name' => 'Midtrans Demo',
                'type' => 'payment',
                'api_format' => 'rest-redirect',
                'base_url' => 'https://api.sandbox.midtrans.com/v2',
                'api_key_encrypted' => null,
                'api_secret_encrypted' => null,
                'merchant_id' => 'G123456789',
                'client_id' => null,
                'extra_headers' => null,
                'extra_config' => json_encode(['snap_js_url' => 'https://app.sandbox.midtrans.com/snap/snap.js']),
                'is_active' => true,
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'WhatsApp Gateway Demo',
                'type' => 'notification',
                'api_format' => 'rest-api',
                'base_url' => 'https://api.whatsapp.com',
                'api_key_encrypted' => null,
                'api_secret_encrypted' => null,
                'merchant_id' => null,
                'client_id' => null,
                'extra_headers' => null,
                'extra_config' => null,
                'is_active' => false,
                'is_default' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    // ================================================================
    // PAYMENT METHODS
    // ================================================================
    private function seedPaymentMethods(Carbon $now): void
    {
        DB::table('payment_methods')->insert([
            [
                'name' => 'Tunai',
                'code' => 'CASH',
                'provider_id' => null,
                'type' => 'offline',
                'active' => true,
                'is_gateway' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'QRIS',
                'code' => 'QRIS',
                'provider_id' => 1,
                'type' => 'online',
                'active' => true,
                'is_gateway' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Transfer Bank',
                'code' => 'BANK_TRANSFER',
                'provider_id' => 1,
                'type' => 'online',
                'active' => true,
                'is_gateway' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'E-Wallet',
                'code' => 'EWALLET',
                'provider_id' => 1,
                'type' => 'online',
                'active' => true,
                'is_gateway' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kartu Debit',
                'code' => 'DEBIT',
                'provider_id' => 1,
                'type' => 'online',
                'active' => true,
                'is_gateway' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kredit',
                'code' => 'CREDIT',
                'provider_id' => null,
                'type' => 'offline',
                'active' => true,
                'is_gateway' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    // ================================================================
    // LOYALTY REWARDS
    // ================================================================
    private function seedLoyaltyRewards(Carbon $now): void
    {
        DB::table('loyalty_rewards')->insert([
            [
                'name' => 'Diskon 5.000',
                'points_required' => 50,
                'discount_type' => 'fixed',
                'discount_value' => 5000,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Diskon 10%',
                'points_required' => 100,
                'discount_type' => 'percent',
                'discount_value' => 10,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gratis 1 Produk Pilihan',
                'points_required' => 200,
                'discount_type' => 'fixed',
                'discount_value' => 25000,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    // ================================================================
    // SUPPLIERS
    // ================================================================
    private function seedSuppliers(Carbon $now): void
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'PT Indomarco Prismatama',
                'contact_person' => 'Hendra Wijaya',
                'phone' => '021-5550101',
                'email' => 'hendra@indomarco.co.id',
                'address' => 'Jl. Raya Bogor KM 28, Jakarta Timur',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'CV Sumber Makmur',
                'contact_person' => 'Susi Rahmawati',
                'phone' => '0274-5550202',
                'email' => 'susi@sumbermakmur.co.id',
                'address' => 'Jl. Kusumanegara No. 45, Yogyakarta',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'UD Lancar Jaya',
                'contact_person' => 'Joko Santoso',
                'phone' => '0271-5550303',
                'email' => 'joko@lancarjaya.co.id',
                'address' => 'Jl. Slamet Riyadi No. 120, Solo',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'PT Mitra Distribusi Nusantara',
                'contact_person' => 'Rudi Hartanto',
                'phone' => '031-5550404',
                'email' => 'rudi@mitranusantara.co.id',
                'address' => 'Jl. Raya Darmo No. 88, Surabaya',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'CV Berkah Utama',
                'contact_person' => 'Ani Lestari',
                'phone' => '024-5550505',
                'email' => 'ani@berkahutama.co.id',
                'address' => 'Jl. Pandanaran No. 67, Semarang',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    // ================================================================
    // SYSTEM SETTINGS
    // ================================================================
    private function seedSystemSettings(Carbon $now): void
    {
        $settings = [];
        $keys = ['store_name', 'store_address', 'store_phone', 'tax_percent', 'loyalty_points_rate', 'low_stock_threshold', 'currency', 'timezone'];
        $values = [
            'Toko Retail POS',
            'Jl. Raya Malioboro No. 10, Yogyakarta 55271',
            '0274-555001',
            '11',
            '10000',
            '10',
            'IDR',
            'Asia/Jakarta',
        ];
        foreach ($keys as $i => $key) {
            $settings[] = [
                'key' => $key,
                'value' => $values[$i],
                'outlet_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            foreach ([1, 2, 3] as $outletId) {
                if ($i <= 2) {
                    $outletValues = [
                        1 => ['Toko Pusat', 'Jl. Malioboro No. 10, Yogyakarta', '0274-555001'],
                        2 => ['Cabang Timur', 'Jl. Solo Raya KM 5, Klaten', '0272-555002'],
                        3 => ['Cabang Barat', 'Jl. Magelang KM 8, Sleman', '0274-555003'],
                    ];
                    $settings[] = [
                        'key' => $key,
                        'value' => $outletValues[$outletId][$i],
                        'outlet_id' => $outletId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }
        DB::table('system_settings')->insert($settings);
    }

    // ================================================================
    // USER_OUTLET
    // ================================================================
    private function seedUserOutlet(Carbon $now): void
    {
        DB::table('user_outlet')->insert([
            ['user_id' => 1, 'outlet_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 1, 'outlet_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 1, 'outlet_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 2, 'outlet_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 3, 'outlet_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 3, 'outlet_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 4, 'outlet_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 4, 'outlet_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 4, 'outlet_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 5, 'outlet_id' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    // ================================================================
    // PRODUCTS (50 realistic Indonesian retail products)
    // ================================================================
    private function seedProducts($faker, Carbon $now): void
    {
        $productDefinitions = [
            // Makanan Ringan (cat 1) - Indofood
            ['name' => 'Chitato Sapi Panggang 60gr', 'cat' => 1, 'brand' => 1, 'unit' => 1, 'cost' => 8000, 'sell' => 12000, 'whole' => 11000, 'mem' => 10500, 'stock' => 120, 'min' => 10, 'max' => 200],
            ['name' => 'Qtela Singkong Balado 55gr', 'cat' => 1, 'brand' => 1, 'unit' => 1, 'cost' => 3500, 'sell' => 6000, 'whole' => 5500, 'mem' => 5000, 'stock' => 80, 'min' => 5, 'max' => 150],
            ['name' => 'Lays Rumput Laut 68gr', 'cat' => 1, 'brand' => 1, 'unit' => 1, 'cost' => 9000, 'sell' => 13500, 'whole' => 12500, 'mem' => 12000, 'stock' => 60, 'min' => 5, 'max' => 100],
            ['name' => 'Taro Net Rumput Laut 35gr', 'cat' => 1, 'brand' => 1, 'unit' => 1, 'cost' => 3000, 'sell' => 5000, 'whole' => 4500, 'mem' => 4000, 'stock' => 150, 'min' => 10, 'max' => 200],
            ['name' => 'Cheetos Jagung Bakar 45gr', 'cat' => 1, 'brand' => 1, 'unit' => 1, 'cost' => 5500, 'sell' => 8500, 'whole' => 8000, 'mem' => 7500, 'stock' => 90, 'min' => 10, 'max' => 150],
            ['name' => 'Beng Beng Coklat 30gr', 'cat' => 1, 'brand' => 2, 'unit' => 1, 'cost' => 2000, 'sell' => 3000, 'whole' => 2800, 'mem' => 2500, 'stock' => 200, 'min' => 20, 'max' => 300],
            ['name' => 'Oreo Original 133gr', 'cat' => 1, 'brand' => 2, 'unit' => 1, 'cost' => 7000, 'sell' => 10500, 'whole' => 10000, 'mem' => 9500, 'stock' => 75, 'min' => 5, 'max' => 120],
            ['name' => 'Kacang Garuda 150gr', 'cat' => 1, 'brand' => 2, 'unit' => 1, 'cost' => 8500, 'sell' => 12500, 'whole' => 11500, 'mem' => 11000, 'stock' => 45, 'min' => 5, 'max' => 80],

            // Minuman (cat 2)
            ['name' => 'Teh Botol Sosro 450ml', 'cat' => 2, 'brand' => 4, 'unit' => 1, 'cost' => 3500, 'sell' => 5500, 'whole' => 5000, 'mem' => 4500, 'stock' => 180, 'min' => 20, 'max' => 300],
            ['name' => 'Aqua 600ml', 'cat' => 2, 'brand' => 2, 'unit' => 1, 'cost' => 2000, 'sell' => 3500, 'whole' => 3000, 'mem' => 3000, 'stock' => 300, 'min' => 30, 'max' => 500],
            ['name' => 'Kopi Kapal Api 380gr', 'cat' => 2, 'brand' => 2, 'unit' => 1, 'cost' => 18000, 'sell' => 25000, 'whole' => 23500, 'mem' => 22000, 'stock' => 40, 'min' => 5, 'max' => 60],
            ['name' => 'Sirup Marjan Cocopandan 460ml', 'cat' => 2, 'brand' => 2, 'unit' => 1, 'cost' => 14000, 'sell' => 20000, 'whole' => 19000, 'mem' => 18000, 'stock' => 35, 'min' => 5, 'max' => 50],
            ['name' => 'Mizone Lemon 500ml', 'cat' => 2, 'brand' => 2, 'unit' => 1, 'cost' => 4000, 'sell' => 6500, 'whole' => 6000, 'mem' => 5500, 'stock' => 110, 'min' => 10, 'max' => 200],
            ['name' => 'Pocari Sweat 500ml', 'cat' => 2, 'brand' => 2, 'unit' => 1, 'cost' => 6000, 'sell' => 9000, 'whole' => 8500, 'mem' => 8000, 'stock' => 85, 'min' => 10, 'max' => 150],
            ['name' => 'Frisian Flag Kaleng 370gr', 'cat' => 2, 'brand' => 3, 'unit' => 1, 'cost' => 12000, 'sell' => 17000, 'whole' => 16000, 'mem' => 15000, 'stock' => 30, 'min' => 5, 'max' => 50],
            ['name' => 'Bear Brand 189ml', 'cat' => 2, 'brand' => 3, 'unit' => 1, 'cost' => 8000, 'sell' => 11000, 'whole' => 10500, 'mem' => 10000, 'stock' => 55, 'min' => 5, 'max' => 80],

            // Bumbu Dapur (cat 3)
            ['name' => 'Kecap Bango 300ml', 'cat' => 3, 'brand' => 3, 'unit' => 1, 'cost' => 12000, 'sell' => 17000, 'whole' => 16000, 'mem' => 15000, 'stock' => 50, 'min' => 5, 'max' => 80],
            ['name' => 'Sambal ABC 335ml', 'cat' => 3, 'brand' => 1, 'unit' => 1, 'cost' => 7500, 'sell' => 11000, 'whole' => 10000, 'mem' => 9500, 'stock' => 65, 'min' => 5, 'max' => 100],
            ['name' => 'Royco Ayam 9gr', 'cat' => 3, 'brand' => 3, 'unit' => 1, 'cost' => 1500, 'sell' => 2500, 'whole' => 2300, 'mem' => 2200, 'stock' => 250, 'min' => 20, 'max' => 400],
            ['name' => 'Masako Ayam 8gr', 'cat' => 3, 'brand' => 1, 'unit' => 1, 'cost' => 1000, 'sell' => 2000, 'whole' => 1800, 'mem' => 1800, 'stock' => 300, 'min' => 30, 'max' => 500],
            ['name' => 'Sedaap Mie Goreng 85gr', 'cat' => 3, 'brand' => 2, 'unit' => 1, 'cost' => 2200, 'sell' => 3500, 'whole' => 3200, 'mem' => 3000, 'stock' => 180, 'min' => 20, 'max' => 300],
            ['name' => 'Indomie Goreng 85gr', 'cat' => 3, 'brand' => 1, 'unit' => 1, 'cost' => 2200, 'sell' => 3500, 'whole' => 3200, 'mem' => 3000, 'stock' => 200, 'min' => 20, 'max' => 350],

            // Sabun & Kebersihan (cat 4)
            ['name' => 'Lifebuoy Sabun Batang 70gr', 'cat' => 4, 'brand' => 3, 'unit' => 1, 'cost' => 2500, 'sell' => 4000, 'whole' => 3800, 'mem' => 3500, 'stock' => 160, 'min' => 15, 'max' => 250],
            ['name' => 'Rinso Deterjen 770gr', 'cat' => 4, 'brand' => 3, 'unit' => 1, 'cost' => 12000, 'sell' => 17000, 'whole' => 16000, 'mem' => 15000, 'stock' => 40, 'min' => 5, 'max' => 60],
            ['name' => 'Sunlight CIF 450ml', 'cat' => 4, 'brand' => 3, 'unit' => 1, 'cost' => 8500, 'sell' => 12500, 'whole' => 11500, 'mem' => 11000, 'stock' => 55, 'min' => 5, 'max' => 80],
            ['name' => 'So Klin Lantai 800ml', 'cat' => 4, 'brand' => 2, 'unit' => 1, 'cost' => 10000, 'sell' => 14500, 'whole' => 13500, 'mem' => 13000, 'stock' => 45, 'min' => 5, 'max' => 70],
            ['name' => 'Pepsodent 75gr', 'cat' => 4, 'brand' => 3, 'unit' => 1, 'cost' => 5500, 'sell' => 8500, 'whole' => 8000, 'mem' => 7500, 'stock' => 95, 'min' => 10, 'max' => 150],
            ['name' => 'Clear Shampoo 170ml', 'cat' => 4, 'brand' => 3, 'unit' => 1, 'cost' => 11500, 'sell' => 16000, 'whole' => 15000, 'mem' => 14000, 'stock' => 35, 'min' => 5, 'max' => 55],
            ['name' => 'Ekonomi Sabun Colet 585gr', 'cat' => 4, 'brand' => 2, 'unit' => 1, 'cost' => 7000, 'sell' => 10500, 'whole' => 10000, 'mem' => 9500, 'stock' => 30, 'min' => 5, 'max' => 50],

            // Alat Tulis (cat 5)
            ['name' => 'Buku Tulis Sidu 58 Lembar', 'cat' => 5, 'brand' => 2, 'unit' => 1, 'cost' => 3000, 'sell' => 4500, 'whole' => 4200, 'mem' => 4000, 'stock' => 200, 'min' => 20, 'max' => 350],
            ['name' => 'Pulpen Faster C600', 'cat' => 5, 'brand' => 2, 'unit' => 3, 'cost' => 24000, 'sell' => 35000, 'whole' => 33000, 'mem' => 31000, 'stock' => 15, 'min' => 2, 'max' => 25],
            ['name' => 'Pensil 2B Steadler', 'cat' => 5, 'brand' => 2, 'unit' => 1, 'cost' => 2000, 'sell' => 3500, 'whole' => 3200, 'mem' => 3000, 'stock' => 120, 'min' => 10, 'max' => 200],
            ['name' => 'Spidol Snowman 12 Warna', 'cat' => 5, 'brand' => 2, 'unit' => 1, 'cost' => 28000, 'sell' => 40000, 'whole' => 38000, 'mem' => 36000, 'stock' => 10, 'min' => 2, 'max' => 20],
            ['name' => 'Lakban Bening 45mm', 'cat' => 5, 'brand' => 2, 'unit' => 1, 'cost' => 6000, 'sell' => 9000, 'whole' => 8500, 'mem' => 8000, 'stock' => 40, 'min' => 5, 'max' => 60],

            // Elektronik Kecil (cat 6)
            ['name' => 'Baterai ABC AA (2pcs)', 'cat' => 6, 'brand' => 2, 'unit' => 1, 'cost' => 5000, 'sell' => 8000, 'whole' => 7500, 'mem' => 7000, 'stock' => 100, 'min' => 10, 'max' => 150],
            ['name' => 'Lampu LED Philips 12W', 'cat' => 6, 'brand' => 2, 'unit' => 1, 'cost' => 22000, 'sell' => 32000, 'whole' => 30000, 'mem' => 28000, 'stock' => 25, 'min' => 3, 'max' => 40],
            ['name' => 'Kabel USB Type-C 1M', 'cat' => 6, 'brand' => 2, 'unit' => 1, 'cost' => 12000, 'sell' => 18000, 'whole' => 17000, 'mem' => 16000, 'stock' => 35, 'min' => 5, 'max' => 50],
            ['name' => 'Stop Kontak Broco 4 Lubang', 'cat' => 6, 'brand' => 2, 'unit' => 1, 'cost' => 18000, 'sell' => 26000, 'whole' => 24500, 'mem' => 23000, 'stock' => 20, 'min' => 2, 'max' => 30],

            // Rokok (cat 7)
            ['name' => 'Gudang Garam Merah 12', 'cat' => 7, 'brand' => 5, 'unit' => 5, 'cost' => 20500, 'sell' => 25000, 'whole' => 24000, 'mem' => 23500, 'stock' => 80, 'min' => 5, 'max' => 120],
            ['name' => 'Gudang Garam Filter 12', 'cat' => 7, 'brand' => 5, 'unit' => 5, 'cost' => 22000, 'sell' => 27000, 'whole' => 26000, 'mem' => 25500, 'stock' => 70, 'min' => 5, 'max' => 100],
            ['name' => 'Sampoerna A Mild 16', 'cat' => 7, 'brand' => 5, 'unit' => 5, 'cost' => 27500, 'sell' => 33000, 'whole' => 31500, 'mem' => 30500, 'stock' => 55, 'min' => 5, 'max' => 80],
            ['name' => 'Djarum Super 12', 'cat' => 7, 'brand' => 5, 'unit' => 5, 'cost' => 19500, 'sell' => 23500, 'whole' => 22500, 'mem' => 22000, 'stock' => 65, 'min' => 5, 'max' => 100],

            // Sembako (cat 8)
            ['name' => 'Beras Pandan Wangi 5KG', 'cat' => 8, 'brand' => 1, 'unit' => 2, 'cost' => 65000, 'sell' => 78000, 'whole' => 75000, 'mem' => 73000, 'stock' => 30, 'min' => 3, 'max' => 50],
            ['name' => 'Gula Pasir Gulaku 1KG', 'cat' => 8, 'brand' => 1, 'unit' => 2, 'cost' => 14000, 'sell' => 18000, 'whole' => 17000, 'mem' => 16500, 'stock' => 50, 'min' => 5, 'max' => 80],
            ['name' => 'Minyak Goreng Bimoli 2L', 'cat' => 8, 'brand' => 1, 'unit' => 1, 'cost' => 32000, 'sell' => 38000, 'whole' => 36500, 'mem' => 36000, 'stock' => 35, 'min' => 3, 'max' => 50],
            ['name' => 'Tepung Terigu Segitiga Biru 1KG', 'cat' => 8, 'brand' => 1, 'unit' => 2, 'cost' => 10000, 'sell' => 13500, 'whole' => 13000, 'mem' => 12500, 'stock' => 40, 'min' => 5, 'max' => 60],
            ['name' => 'Telur Ayam 1KG', 'cat' => 8, 'brand' => 2, 'unit' => 2, 'cost' => 25000, 'sell' => 30000, 'whole' => 29000, 'mem' => 28000, 'stock' => 20, 'min' => 2, 'max' => 30],
            ['name' => 'Susu Kental Manis Bendera 385gr', 'cat' => 8, 'brand' => 3, 'unit' => 1, 'cost' => 10500, 'sell' => 14500, 'whole' => 13800, 'mem' => 13000, 'stock' => 60, 'min' => 5, 'max' => 100],
            ['name' => 'Kopi Bubuk ABC 350gr', 'cat' => 8, 'brand' => 2, 'unit' => 1, 'cost' => 17000, 'sell' => 23500, 'whole' => 22000, 'mem' => 21000, 'stock' => 25, 'min' => 3, 'max' => 40],
            ['name' => 'Minyak Kita 1L', 'cat' => 8, 'brand' => 1, 'unit' => 1, 'cost' => 14500, 'sell' => 17500, 'whole' => 16800, 'mem' => 16000, 'stock' => 55, 'min' => 5, 'max' => 80],
        ];

        $products = [];
        foreach ($productDefinitions as $i => $p) {
            $slug = Str::slug($p['name']);
            $sku = 'SKU' . str_pad($i + 1, 5, '0', STR_PAD_LEFT);
            $barcode = '899' . str_pad(random_int(10000000, 99999999), 10, '0', STR_PAD_LEFT);

            $products[] = [
                'name' => $p['name'],
                'slug' => $slug,
                'description' => $p['name'] . ' - kualitas terjamin, harga hemat',
                'category_id' => $p['cat'],
                'brand_id' => $p['brand'],
                'unit_id' => $p['unit'],
                'outlet_id' => null,
                'sku' => $sku,
                'barcode' => $barcode,
                'cost_price' => $p['cost'],
                'selling_price' => $p['sell'],
                'wholesale_price' => $p['whole'],
                'member_price' => $p['mem'],
                'min_stock' => $p['min'],
                'max_stock' => $p['max'],
                'current_stock' => $p['stock'],
                'image' => 'https://picsum.photos/seed/' . urlencode($slug) . '/200/200',
                'has_variants' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $this->products[$i + 1] = $p;
        }

        DB::table('products')->insert($products);
        $this->productIds = range(1, count($products));
    }

    // ================================================================
    // PRODUCT VARIANTS (10)
    // ================================================================
    private function seedProductVariants(Carbon $now): void
    {
        // Mark products with variants
        DB::table('products')->whereIn('id', [3, 11, 16, 21, 27])->update(['has_variants' => true]);

        $variants = [
            // Lays Rumput Laut - variant rasa
            ['product_id' => 3, 'name' => 'Lays Ayam Lada Hitam 68gr', 'sku' => 'SKU00003B', 'barcode' => '8993000001123', 'cost' => 9500, 'sell' => 14000, 'stock' => 50],
            ['product_id' => 3, 'name' => 'Lays Sapi Panggang 68gr', 'sku' => 'SKU00003C', 'barcode' => '8993000001124', 'cost' => 9000, 'sell' => 13500, 'stock' => 45],

            // Kopi Kapal Api - variant ukuran
            ['product_id' => 11, 'name' => 'Kopi Kapal Api 165gr', 'sku' => 'SKU00011B', 'barcode' => '8991100001125', 'cost' => 10000, 'sell' => 14000, 'stock' => 60],
            ['product_id' => 11, 'name' => 'Kopi Kapal Api 760gr', 'sku' => 'SKU00011C', 'barcode' => '8991100001126', 'cost' => 32000, 'sell' => 43000, 'stock' => 15],

            // Bear Brand - variant ukuran
            ['product_id' => 16, 'name' => 'Bear Brand 400gr', 'sku' => 'SKU00016B', 'barcode' => '8991600001127', 'cost' => 18000, 'sell' => 24000, 'stock' => 25],

            // Indomie Goreng - variant rasa
            ['product_id' => 21, 'name' => 'Indomie Goreng Rendang 85gr', 'sku' => 'SKU00021B', 'barcode' => '8992100001128', 'cost' => 2200, 'sell' => 3500, 'stock' => 170],
            ['product_id' => 21, 'name' => 'Indomie Goreng Aceh 85gr', 'sku' => 'SKU00021C', 'barcode' => '8992100001129', 'cost' => 2200, 'sell' => 3500, 'stock' => 155],

            // Pulpen Faster - variant warna
            ['product_id' => 27, 'name' => 'Pulpen Faster C600 Hitam', 'sku' => 'SKU00027B', 'barcode' => '8992700001130', 'cost' => 8000, 'sell' => 12000, 'stock' => 120],
            ['product_id' => 27, 'name' => 'Pulpen Faster C600 Biru', 'sku' => 'SKU00027C', 'barcode' => '8992700001131', 'cost' => 8000, 'sell' => 12000, 'stock' => 110],
            ['product_id' => 27, 'name' => 'Pulpen Faster C600 Merah', 'sku' => 'SKU00027D', 'barcode' => '8992700001132', 'cost' => 8000, 'sell' => 12000, 'stock' => 85],
        ];

        $rows = [];
        foreach ($variants as $v) {
            $rows[] = [
                'product_id' => $v['product_id'],
                'name' => $v['name'],
                'sku' => $v['sku'],
                'barcode' => $v['barcode'],
                'cost_price' => $v['cost'],
                'selling_price' => $v['sell'],
                'current_stock' => $v['stock'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('product_variants')->insert($rows);
        $this->variantIds = range(1, count($variants));
    }

    // ================================================================
    // CUSTOMERS (200)
    // ================================================================
    private function seedCustomers($faker, Carbon $now): void
    {
        $customers = [];
        $usedPhones = [];
        $usedEmails = [];

        for ($i = 0; $i < 200; $i++) {
            $phone = '08' . str_pad(random_int(100000000, 999999999), 10, '0', STR_PAD_LEFT);
            while (in_array($phone, $usedPhones)) {
                $phone = '08' . str_pad(random_int(100000000, 999999999), 10, '0', STR_PAD_LEFT);
            }
            $usedPhones[] = $phone;

            $email = strtolower(Str::slug($faker->name) . random_int(10, 99) . '@gmail.com');
            while (in_array($email, $usedEmails)) {
                $email = strtolower(Str::slug($faker->name) . random_int(10, 99) . '@gmail.com');
            }
            $usedEmails[] = $email;

            $groupId = random_int(1, 3);
            if ($groupId === 1 && random_int(1, 100) <= 30) {
                $groupId = 1;
            }

            $totalSpent = $groupId === 3 ? random_int(2000000, 15000000) : ($groupId === 2 ? random_int(500000, 5000000) : random_int(0, 2000000));

            $customers[] = [
                'name' => $faker->name,
                'email' => $email,
                'phone' => $phone,
                'address' => $faker->address,
                'customer_group_id' => $groupId,
                'total_points' => 0,
                'total_spent' => $totalSpent,
                'active' => true,
                'created_at' => $now->copy()->subDays(random_int(0, 180)),
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($customers, 50) as $chunk) {
            DB::table('customers')->insert($chunk);
        }
        $this->customerIds = range(1, 200);
    }

    // ================================================================
    // ORDERS + ORDER ITEMS + PAYMENTS + STOCK MOVEMENTS (500)
    // ================================================================
    private function seedOrders($faker, Carbon $now): void
    {
        $orders = [];
        $orderItems = [];
        $payments = [];
        $stockMovements = [];

        $orderId = 1;

        for ($i = 0; $i < 500; $i++) {
            $orderDate = $now->copy()->subDays(random_int(0, 30))
                ->setTime(random_int(7, 22), random_int(0, 59), random_int(0, 59));

            $outletId = $this->outletIds[array_rand($this->outletIds)];
            $hasCustomer = random_int(1, 100) <= 70;
            $customerId = $hasCustomer ? $this->customerIds[array_rand($this->customerIds)] : null;
            $userId = $this->userIdIds[array_rand($this->userIdIds)];

            $paymentStatuses = ['paid', 'paid', 'paid', 'paid', 'pending', 'partial'];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            // Generate 1-5 order items
            $itemCount = random_int(1, 5);
            $productPool = $this->pickRandomProducts($itemCount);

            $itemsSubtotal = 0;
            $orderItemsBatch = [];

            foreach ($productPool as $pid) {
                $prod = $this->products[$pid];
                $isVariant = random_int(1, 100) <= 8 && $this->hasVariants($pid);
                $variantId = null;
                $unitPrice = $prod['sell'];

                if ($isVariant) {
                    $variantOpts = $this->getVariantIdsForProduct($pid);
                    if ($variantOpts) {
                        $variantId = $variantOpts[array_rand($variantOpts)];
                        $variantInfo = $this->getVariantInfo($variantId);
                        if ($variantInfo) {
                            $unitPrice = $variantInfo['sell'];
                        }
                    }
                }

                $qty = random_int(1, 4);
                $discPercent = random_int(1, 100) <= 20 ? random_int(5, 15) : 0;

                $lineSubtotal = $qty * $unitPrice;
                $discAmount = round($lineSubtotal * $discPercent / 100);
                $lineTotal = $lineSubtotal - $discAmount;

                $orderItemsBatch[] = [
                    'order_id' => $orderId,
                    'product_id' => $pid,
                    'product_variant_id' => $variantId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discPercent,
                    'discount_amount' => $discAmount,
                    'subtotal' => $lineTotal,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ];

                $itemsSubtotal += $lineTotal;
            }

            $discountAmount = 0;
            if ($customerId) {
                $cust = DB::table('customers')->find($customerId);
                if ($cust) {
                    $grp = DB::table('customer_groups')->find($cust->customer_group_id);
                    if ($grp && $grp->discount_percent > 0) {
                        $discountAmount = round($itemsSubtotal * $grp->discount_percent / 100);
                    }
                }
            }

            $taxAmount = round($itemsSubtotal * 0.11);
            $totalAmount = $itemsSubtotal - $discountAmount + $taxAmount;

            $orderNumber = 'INV-' . date('Ymd', $orderDate->timestamp) . '-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);

            $orders[] = [
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'outlet_id' => $outletId,
                'user_id' => $userId,
                'subtotal' => $itemsSubtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_status' => $paymentStatus,
                'order_status' => 'completed',
                'notes' => random_int(1, 100) <= 10 ? $faker->sentence : null,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ];

            // Payment
            $payAmount = $paymentStatus === 'paid' ? $totalAmount : round($totalAmount * 0.5);
            $payMethodId = $this->paymentMethodIds[array_rand($this->paymentMethodIds)];
            $payments[] = [
                'order_id' => $orderId,
                'payment_method_id' => $payMethodId,
                'amount' => $payAmount,
                'reference_number' => $payMethodId > 1 ? 'REF' . strtoupper(Str::random(12)) : null,
                'status' => $paymentStatus === 'paid' ? 'completed' : ($paymentStatus === 'partial' ? 'partial' : 'pending'),
                'paid_at' => $paymentStatus === 'paid' ? $orderDate : null,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ];

            // Stock movements (out) for each order item
            foreach ($orderItemsBatch as $item) {
                $stockMovements[] = [
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'outlet_id' => $outletId,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference_type' => 'order',
                    'reference_id' => $orderId,
                    'notes' => 'Penjualan #' . $orderNumber,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ];
            }

            // Merge order items
            foreach ($orderItemsBatch as $oi) {
                $orderItems[] = $oi;
            }

            $orderId++;

            // Flush chunks every 50 orders
            if ($i > 0 && $i % 50 === 0) {
                DB::table('orders')->insert($orders);
                DB::table('order_items')->insert($orderItems);
                DB::table('payments')->insert($payments);
                DB::table('stock_movements')->insert($stockMovements);
                $orders = [];
                $orderItems = [];
                $payments = [];
                $stockMovements = [];
            }
        }

        // Flush remaining
        if (!empty($orders)) {
            DB::table('orders')->insert($orders);
        }
        if (!empty($orderItems)) {
            DB::table('order_items')->insert($orderItems);
        }
        if (!empty($payments)) {
            DB::table('payments')->insert($payments);
        }
        if (!empty($stockMovements)) {
            DB::table('stock_movements')->insert($stockMovements);
        }
    }

    // ================================================================
    // PURCHASE ORDERS (20)
    // ================================================================
    private function seedPurchaseOrders(Carbon $now): void
    {
        $statuses = ['draft', 'ordered', 'received', 'received', 'received', 'received', 'received'];
        $purchaseOrders = [];
        $poItems = [];
        $stockMovements = [];
        $poId = 1;

        for ($i = 0; $i < 20; $i++) {
            $poDate = $now->copy()->subDays(random_int(5, 45));
            $status = $statuses[array_rand($statuses)];
            $supplierId = $this->supplierIds[array_rand($this->supplierIds)];
            $outletId = $this->outletIds[array_rand($this->outletIds)];
            $userId = random_int(1, 3);

            $poNumber = 'PO-' . date('Ymd', $poDate->timestamp) . '-' . str_pad($poId, 4, '0', STR_PAD_LEFT);

            $itemCount = random_int(2, 6);
            $productIds = $this->pickRandomProducts($itemCount);
            $totalAmount = 0;

            foreach ($productIds as $pid) {
                $prod = $this->products[$pid];
                $qty = random_int(5, 30);
                $unitPrice = $prod['cost'];
                $subtotal = $qty * $unitPrice;
                $totalAmount += $subtotal;

                $poItems[] = [
                    'purchase_order_id' => $poId,
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'created_at' => $poDate,
                    'updated_at' => $poDate,
                ];

                if ($status === 'received') {
                    $stockMovements[] = [
                        'product_id' => $pid,
                        'product_variant_id' => null,
                        'outlet_id' => $outletId,
                        'type' => 'in',
                        'quantity' => $qty,
                        'reference_type' => 'purchase',
                        'reference_id' => $poId,
                        'notes' => 'Penerimaan ' . $poNumber,
                        'created_at' => $poDate,
                        'updated_at' => $poDate,
                    ];
                }
            }

            $purchaseOrders[] = [
                'po_number' => $poNumber,
                'supplier_id' => $supplierId,
                'outlet_id' => $outletId,
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => $status,
                'notes' => $status === 'draft' ? 'Menunggu konfirmasi supplier' : null,
                'created_at' => $poDate,
                'updated_at' => $poDate,
            ];

            $poId++;

            if ($i > 0 && $i % 10 === 0) {
                DB::table('purchase_orders')->insert($purchaseOrders);
                DB::table('purchase_order_items')->insert($poItems);
                DB::table('stock_movements')->insert($stockMovements);
                $purchaseOrders = [];
                $poItems = [];
                $stockMovements = [];
            }
        }

        if (!empty($purchaseOrders)) {
            DB::table('purchase_orders')->insert($purchaseOrders);
        }
        if (!empty($poItems)) {
            DB::table('purchase_order_items')->insert($poItems);
        }
        if (!empty($stockMovements)) {
            DB::table('stock_movements')->insert($stockMovements);
        }
    }

    // ================================================================
    // STOCK OPNAMES (5)
    // ================================================================
    private function seedStockOpnames(Carbon $now): void
    {
        $opnames = [];
        $opnameItems = [];
        $stockMovements = [];
        $opnameId = 1;

        for ($i = 0; $i < 5; $i++) {
            $opnameDate = $now->copy()->subDays(random_int(1, 28));
            $outletId = $this->outletIds[array_rand($this->outletIds)];
            $userId = random_int(1, 3);

            $opnameNumber = 'OPN-' . date('Ymd', $opnameDate->timestamp) . '-' . str_pad($opnameId, 3, '0', STR_PAD_LEFT);

            $opnames[] = [
                'opname_number' => $opnameNumber,
                'outlet_id' => $outletId,
                'user_id' => $userId,
                'status' => 'completed',
                'notes' => 'Stock opname rutin bulanan',
                'created_at' => $opnameDate,
                'updated_at' => $opnameDate,
            ];

            $productPool = $this->pickRandomProducts(random_int(10, 25));
            foreach ($productPool as $pid) {
                $prod = $this->products[$pid];
                $systemStock = random_int(5, 150);
                $difference = random_int(-5, 5);
                $actualStock = $systemStock + $difference;

                $opnameItems[] = [
                    'stock_opname_id' => $opnameId,
                    'product_id' => $pid,
                    'product_variant_id' => null,
                    'system_stock' => $systemStock,
                    'actual_stock' => $actualStock,
                    'difference' => $difference,
                    'notes' => $difference !== 0 ? 'Selisih stok perlu penyesuaian' : null,
                    'created_at' => $opnameDate,
                    'updated_at' => $opnameDate,
                ];

                if ($difference !== 0) {
                    $stockMovements[] = [
                        'product_id' => $pid,
                        'product_variant_id' => null,
                        'outlet_id' => $outletId,
                        'type' => 'adjustment',
                        'quantity' => abs($difference),
                        'reference_type' => 'opname',
                        'reference_id' => $opnameId,
                        'notes' => $difference > 0 ? 'Penyesuaian stok (+) dari opname' : 'Penyesuaian stok (-) dari opname',
                        'created_at' => $opnameDate,
                        'updated_at' => $opnameDate,
                    ];
                }
            }

            $opnameId++;
        }

        DB::table('stock_opnames')->insert($opnames);
        DB::table('stock_opname_items')->insert($opnameItems);
        if (!empty($stockMovements)) {
            DB::table('stock_movements')->insert($stockMovements);
        }
    }

    // ================================================================
    // LOYALTY POINTS
    // ================================================================
    private function seedLoyaltyPoints(Carbon $now): void
    {
        $points = [];
        $customers = DB::table('customers')->whereIn('customer_group_id', [2, 3])->pluck('id')->toArray();

        if (empty($customers)) {
            $customers = range(1, 50);
        }

        $chosenCustomers = array_slice($customers, 0, min(count($customers), 80));

        foreach ($chosenCustomers as $customerId) {
            // Get orders for this customer
            $orderIds = DB::table('orders')
                ->where('customer_id', $customerId)
                ->pluck('id')
                ->toArray();

            if (empty($orderIds)) {
                continue;
            }

            $dipilih = array_slice($orderIds, 0, min(count($orderIds), 8));
            $runningBalance = 0;

            foreach ($dipilih as $orderId) {
                $orderDt = DB::table('orders')->where('id', $orderId)->value('created_at');
                $orderDate = Carbon::parse($orderDt);

                $earned = random_int(5, 30);
                $redeemed = random_int(1, 100) <= 15 ? random_int(0, 10) : 0;
                $runningBalance += $earned - $redeemed;

                $points[] = [
                    'customer_id' => $customerId,
                    'order_id' => $orderId,
                    'points_earned' => $earned,
                    'points_redeemed' => $redeemed,
                    'balance' => $runningBalance,
                    'description' => $redeemed > 0 ? 'Point dari transaksi + penukaran reward' : 'Point dari transaksi',
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ];
            }
        }

        foreach (array_chunk($points, 50) as $chunk) {
            DB::table('loyalty_points')->insert($chunk);
        }
    }

    // ================================================================
    // NEW FEATURES — Tables, Raw Materials, Discount Templates, Attendance
    // ================================================================

    private function seedTableAreas(Carbon $now): void
    {
        $areas = [
            ['outlet_id' => 1, 'name' => 'Indoor', 'description' => 'Area dalam ruangan ber-AC', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'name' => 'Outdoor', 'description' => 'Area luar ruangan', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'name' => 'VIP', 'description' => 'Ruang VIP khusus', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 2, 'name' => 'Lantai 1', 'description' => 'Area lantai dasar', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('table_areas')->insert($areas);
    }

    private function seedTables(Carbon $now): void
    {
        $tables = [
            ['outlet_id' => 1, 'table_area_id' => 1, 'name' => 'Meja 1', 'code' => 'T01', 'capacity' => 4, 'status' => 'available', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'table_area_id' => 1, 'name' => 'Meja 2', 'code' => 'T02', 'capacity' => 4, 'status' => 'available', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'table_area_id' => 1, 'name' => 'Meja 3', 'code' => 'T03', 'capacity' => 2, 'status' => 'available', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'table_area_id' => 1, 'name' => 'Meja 4', 'code' => 'T04', 'capacity' => 6, 'status' => 'occupied', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'table_area_id' => 2, 'name' => 'Meja Outdoor 1', 'code' => 'T05', 'capacity' => 4, 'status' => 'available', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'table_area_id' => 3, 'name' => 'VIP 1', 'code' => 'T06', 'capacity' => 8, 'status' => 'reserved', 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 2, 'table_area_id' => 4, 'name' => 'Meja A1', 'code' => 'T07', 'capacity' => 4, 'status' => 'available', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 2, 'table_area_id' => 4, 'name' => 'Meja A2', 'code' => 'T08', 'capacity' => 4, 'status' => 'available', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('tables')->insert($tables);
    }

    private function seedRawMaterials(Carbon $now): void
    {
        $materials = [
            ['outlet_id' => 1, 'unit_id' => 4, 'name' => 'Tepung Terigu', 'code' => 'RM001', 'cost_per_unit' => 12000, 'current_stock' => 50, 'min_stock' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'unit_id' => 4, 'name' => 'Gula Pasir', 'code' => 'RM002', 'cost_per_unit' => 15000, 'current_stock' => 30, 'min_stock' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'unit_id' => 4, 'name' => 'Minyak Goreng', 'code' => 'RM003', 'cost_per_unit' => 20000, 'current_stock' => 20, 'min_stock' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'unit_id' => 4, 'name' => 'Telur', 'code' => 'RM004', 'cost_per_unit' => 28000, 'current_stock' => 15, 'min_stock' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['outlet_id' => 1, 'unit_id' => 5, 'name' => 'Susu UHT', 'code' => 'RM005', 'cost_per_unit' => 18000, 'current_stock' => 25, 'min_stock' => 5, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('raw_materials')->insert($materials);
    }

    private function seedRecipeItems(Carbon $now): void
    {
        DB::table('recipe_items')->insert([
            ['product_id' => 8, 'raw_material_id' => 1, 'quantity' => 0.5, 'created_at' => $now, 'updated_at' => $now],
            ['product_id' => 8, 'raw_material_id' => 2, 'quantity' => 0.1, 'created_at' => $now, 'updated_at' => $now],
            ['product_id' => 8, 'raw_material_id' => 4, 'quantity' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['product_id' => 12, 'raw_material_id' => 1, 'quantity' => 0.3, 'created_at' => $now, 'updated_at' => $now],
            ['product_id' => 12, 'raw_material_id' => 5, 'quantity' => 0.25, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedDiscountTemplates(Carbon $now): void
    {
        DB::table('discount_templates')->insert([
            ['name' => 'Diskon 10% Member', 'type' => 'percent', 'value' => 10, 'min_purchase' => 50000, 'buy_quantity' => null, 'get_quantity' => null, 'start_date' => '2026-06-01', 'end_date' => '2026-12-31', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Potongan 5rb', 'type' => 'fixed', 'value' => 5000, 'min_purchase' => 25000, 'buy_quantity' => null, 'get_quantity' => null, 'start_date' => '2026-06-01', 'end_date' => '2026-12-31', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Beli 3 Gratis 1', 'type' => 'buy_x_get_y', 'value' => 100, 'min_purchase' => 0, 'buy_quantity' => 3, 'get_quantity' => 1, 'start_date' => '2026-06-01', 'end_date' => '2026-07-31', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diskon Weekend 15%', 'type' => 'percent', 'value' => 15, 'min_purchase' => 100000, 'buy_quantity' => null, 'get_quantity' => null, 'start_date' => '2026-06-01', 'end_date' => '2026-08-31', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedAttendances(Carbon $now): void
    {
        $records = [];
        $users = [1, 2, 3, 4, 5];
        for ($d = 5; $d >= 0; $d--) {
            $date = $now->copy()->subDays($d);
            foreach ($users as $uid) {
                $clockIn = sprintf('%02d:%02d:%02d', rand(7, 9), rand(0, 59), rand(0, 59));
                $clockOut = sprintf('%02d:%02d:%02d', rand(16, 18), rand(0, 59), rand(0, 59));
                $status = $d > 1 ? 'present' : (rand(1, 10) > 8 ? 'late' : 'present');
                $records[] = [
                    'user_id' => $uid,
                    'outlet_id' => rand(1, 2),
                    'date' => $date->toDateString(),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('attendances')->insert($records);
    }

    // ================================================================
    // MEMBERSHIP TIERS (4)
    // ================================================================
    private function seedMembershipTiers(Carbon $now): void
    {
        DB::table('membership_tiers')->insert([
            ['name' => 'Bronze', 'min_spent' => 0, 'min_orders' => 0, 'discount_percent' => 0, 'point_multiplier' => 1.0, 'sort_order' => 1, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Silver', 'min_spent' => 1000000, 'min_orders' => 5, 'discount_percent' => 2, 'point_multiplier' => 1.5, 'sort_order' => 2, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gold', 'min_spent' => 5000000, 'min_orders' => 15, 'discount_percent' => 5, 'point_multiplier' => 2.0, 'sort_order' => 3, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Platinum', 'min_spent' => 15000000, 'min_orders' => 30, 'discount_percent' => 8, 'point_multiplier' => 3.0, 'sort_order' => 4, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    // ================================================================
    // STOCK TRANSFERS (5)
    // ================================================================
    private function seedStockTransfers(Carbon $now): void
    {
        $transfers = [];
        $transferItems = [];
        $transferId = 1;

        $transferConfigs = [
            ['from' => 1, 'to' => 2, 'status' => 'received', 'days' => 15],
            ['from' => 2, 'to' => 3, 'status' => 'sent', 'days' => 10],
            ['from' => 1, 'to' => 3, 'status' => 'received', 'days' => 7],
            ['from' => 3, 'to' => 1, 'status' => 'received', 'days' => 5],
            ['from' => 1, 'to' => 2, 'status' => 'draft', 'days' => 1],
        ];

        foreach ($transferConfigs as $cfg) {
            $transferDate = $now->copy()->subDays($cfg['days']);
            $transferNumber = 'STF-' . date('Ymd', $transferDate->timestamp) . '-' . str_pad($transferId, 4, '0', STR_PAD_LEFT);

            $transfers[] = [
                'transfer_number' => $transferNumber,
                'from_outlet_id' => $cfg['from'],
                'to_outlet_id' => $cfg['to'],
                'user_id' => random_int(2, 3),
                'status' => $cfg['status'],
                'notes' => 'Transfer stok antar outlet ' . ($cfg['status'] === 'draft' ? '- draft' : ''),
                'created_at' => $transferDate,
                'updated_at' => $transferDate,
            ];

            $itemCount = random_int(3, 7);
            $productsToTransfer = $this->pickRandomProducts($itemCount);
            foreach ($productsToTransfer as $pid) {
                $prod = $this->products[$pid];
                $qty = random_int(10, 50);
                $transferItems[] = [
                    'stock_transfer_id' => $transferId,
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'created_at' => $transferDate,
                    'updated_at' => $transferDate,
                ];
            }

            $transferId++;
        }

        DB::table('stock_transfers')->insert($transfers);
        DB::table('stock_transfer_items')->insert($transferItems);
    }

    // ================================================================
    // RETURNS (5)
    // ================================================================
    private function seedReturns(Carbon $now): void
    {
        $returns = [];
        $returnItems = [];
        $returnId = 1;

        $completedOrderIds = DB::table('orders')
            ->where('order_status', 'completed')
            ->inRandomOrder()
            ->limit(5)
            ->pluck('id')
            ->toArray();

        $returnConfigs = [
            ['type' => 'customer_return', 'reason' => 'Produk rusak / kemasan bocor', 'status' => 'completed'],
            ['type' => 'customer_return', 'reason' => 'Salah beli varian rasa', 'status' => 'completed'],
            ['type' => 'customer_return', 'reason' => 'Produk kadaluarsa', 'status' => 'completed'],
            ['type' => 'supplier_return', 'reason' => 'Kemasan tidak sesuai PO', 'status' => 'approved'],
            ['type' => 'customer_return', 'reason' => 'Produk tidak sesuai deskripsi', 'status' => 'pending'],
        ];

        foreach ($returnConfigs as $i => $cfg) {
            $orderId = $completedOrderIds[$i] ?? $completedOrderIds[0];
            $order = DB::table('orders')->where('id', $orderId)->first();
            $returnDate = $now->copy()->subDays(random_int(3, 20));
            $returnNumber = 'RTN-' . date('Ymd', $returnDate->timestamp) . '-' . str_pad($returnId, 4, '0', STR_PAD_LEFT);

            $returns[] = [
                'return_number' => $returnNumber,
                'order_id' => $orderId,
                'outlet_id' => $order->outlet_id,
                'user_id' => random_int(2, 4),
                'type' => $cfg['type'],
                'total_amount' => round($order->total_amount * (random_int(10, 30) / 100)),
                'reason' => $cfg['reason'],
                'status' => $cfg['status'],
                'notes' => null,
                'created_at' => $returnDate,
                'updated_at' => $returnDate,
            ];

            $itemsInOrder = DB::table('order_items')->where('order_id', $orderId)->take(3)->get();
            foreach ($itemsInOrder as $item) {
                $returnQty = min($item->quantity, random_int(1, 2));
                $returnItems[] = [
                    'return_id' => $returnId,
                    'product_id' => $item->product_id,
                    'quantity' => $returnQty,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $returnQty * $item->unit_price,
                    'created_at' => $returnDate,
                    'updated_at' => $returnDate,
                ];
            }

            $returnId++;
        }

        DB::table('returns')->insert($returns);
        DB::table('return_items')->insert($returnItems);
    }

    // ================================================================
    // INSTALLMENTS (for 4 orders)
    // ================================================================
    private function seedInstallments(Carbon $now): void
    {
        $installments = [];

        $ordersWithInstallments = DB::table('orders')
            ->where('payment_status', 'partial')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        foreach ($ordersWithInstallments as $order) {
            $totalInstallments = random_int(3, 6);
            $amountPerInstallment = round($order->total_amount / $totalInstallments);
            $remaining = $order->total_amount;
            $dueStart = Carbon::parse($order->created_at);

            for ($i = 1; $i <= $totalInstallments; $i++) {
                $dueDate = $dueStart->copy()->addMonths($i);
                $isPaid = $i <= ($totalInstallments - 1);
                $amount = $i === $totalInstallments ? $remaining : $amountPerInstallment;
                $remaining -= $amount;

                $installments[] = [
                    'order_id' => $order->id,
                    'installment_number' => $i,
                    'amount' => $amount,
                    'due_date' => $dueDate->toDateString(),
                    'paid_date' => $isPaid ? $dueDate->copy()->subDays(random_int(0, 5))->toDateString() : null,
                    'status' => $isPaid ? 'paid' : 'pending',
                    'created_at' => $order->created_at,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('installments')->insert($installments);
    }

    // ================================================================
    // SUPPLIER PAYABLES (5)
    // ================================================================
    private function seedSupplierPayables(Carbon $now): void
    {
        $payables = [];
        $payments = [];

        $receivedPOs = DB::table('purchase_orders')
            ->where('status', 'received')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $payableId = 1;
        foreach ($receivedPOs as $po) {
            $poDate = Carbon::parse($po->created_at);
            $dueDate = $poDate->copy()->addDays(random_int(14, 45));
            $paidAmount = round($po->total_amount * (random_int(50, 100) / 100));
            $status = $paidAmount >= $po->total_amount ? 'paid' : 'partial';

            $invoiceNumber = 'INV-' . $po->po_number;

            $payables[] = [
                'supplier_id' => $po->supplier_id,
                'purchase_order_id' => $po->id,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $po->total_amount,
                'paid_amount' => $paidAmount,
                'due_date' => $dueDate->toDateString(),
                'status' => $status,
                'notes' => 'Tagihan untuk ' . $po->po_number,
                'created_at' => $poDate,
                'updated_at' => $now,
            ];

            $payments[] = [
                'supplier_payable_id' => $payableId,
                'amount' => $paidAmount,
                'payment_method' => 'Transfer Bank',
                'reference_number' => 'BAY-' . strtoupper(Str::random(10)),
                'payment_date' => $dueDate->copy()->subDays(random_int(0, 7))->toDateString(),
                'notes' => 'Pembayaran ' . $invoiceNumber,
                'created_at' => $poDate,
                'updated_at' => $now,
            ];

            $payableId++;
        }

        DB::table('supplier_payables')->insert($payables);
        DB::table('payable_payments')->insert($payments);
    }

    // ================================================================
    // SHIFTS & CASH DRAWER TRANSACTIONS
    // ================================================================
    private function seedShifts(Carbon $now): void
    {
        $shifts = [];
        $cashTransactions = [];
        $shiftId = 1;

        for ($d = 5; $d >= 0; $d--) {
            $date = $now->copy()->subDays($d);

            foreach ([1, 2] as $outletId) {
                $userId = $outletId === 1 ? 4 : 3;
                $startHour = random_int(6, 8);
                $endHour = random_int(14, 16);

                $startedAt = $date->copy()->setTime($startHour, random_int(0, 59), 0);
                $endedAt = $date->copy()->setTime($endHour, random_int(0, 59), 0);
                $startingCash = 500000;
                $expectedCash = random_int(1000000, 5000000);
                $endingCash = $expectedCash + random_int(-50000, 50000);
                $difference = $endingCash - $expectedCash;

                $shifts[] = [
                    'outlet_id' => $outletId,
                    'user_id' => $userId,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'starting_cash' => $startingCash,
                    'ending_cash' => $endingCash,
                    'expected_cash' => $expectedCash,
                    'difference' => $difference,
                    'status' => 'closed',
                    'notes' => $difference !== 0 ? 'Selisih kas: ' . number_format($difference) : null,
                    'created_at' => $startedAt,
                    'updated_at' => $endedAt,
                ];

                $saleAmount = random_int(500000, 3000000);
                $cashTransactions[] = [
                    'shift_id' => $shiftId,
                    'order_id' => null,
                    'type' => 'sale',
                    'amount' => $saleAmount,
                    'payment_method' => 'Tunai',
                    'notes' => 'Total penjualan tunai shift',
                    'created_at' => $endedAt,
                    'updated_at' => $endedAt,
                ];

                $cashTransactions[] = [
                    'shift_id' => $shiftId,
                    'order_id' => null,
                    'type' => 'cash_out',
                    'amount' => random_int(50000, 200000),
                    'payment_method' => 'Tunai',
                    'notes' => 'Pengeluaran operasional',
                    'created_at' => $endedAt,
                    'updated_at' => $endedAt,
                ];

                $shiftId++;
            }
        }

        DB::table('shifts')->insert($shifts);
        DB::table('cash_drawer_transactions')->insert($cashTransactions);
    }

    // ================================================================
    // HELD CARTS (3)
    // ================================================================
    private function seedHeldCarts(Carbon $now): void
    {
        $heldCarts = [];
        $recentDate = $now->copy()->subHours(random_int(0, 4));

        for ($i = 0; $i < 3; $i++) {
            $productPool = $this->pickRandomProducts(random_int(2, 5));
            $items = [];
            foreach ($productPool as $pid) {
                $prod = $this->products[$pid];
                $items[] = [
                    'id' => $pid,
                    'name' => $prod['name'],
                    'price' => $prod['sell'],
                    'qty' => random_int(1, 3),
                ];
            }

            $heldCarts[] = [
                'outlet_id' => $this->outletIds[array_rand($this->outletIds)],
                'user_id' => random_int(3, 5),
                'customer_id' => random_int(1, 100) <= 50 ? $this->customerIds[array_rand($this->customerIds)] : null,
                'label' => $i === 0 ? 'Pelanggan menunggu' : ($i === 1 ? 'Telepon dulu' : null),
                'items' => json_encode($items),
                'created_at' => $recentDate,
                'updated_at' => $recentDate,
            ];
        }

        DB::table('held_carts')->insert($heldCarts);
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================
    private function pickRandomProducts(int $count): array
    {
        $pool = $this->productIds;
        shuffle($pool);
        return array_slice($pool, 0, $count);
    }

    private function hasVariants(int $productId): bool
    {
        return in_array($productId, [3, 11, 16, 21, 27]);
    }

    private function getVariantIdsForProduct(int $productId): array
    {
        $map = [
            3 => [1, 2],
            11 => [3, 4],
            16 => [5],
            21 => [6, 7],
            27 => [8, 9, 10],
        ];
        return $map[$productId] ?? [];
    }

    private function getVariantInfo(int $variantId): ?array
    {
        $map = [
            1 => ['sell' => 14000],
            2 => ['sell' => 13500],
            3 => ['sell' => 14000],
            4 => ['sell' => 43000],
            5 => ['sell' => 24000],
            6 => ['sell' => 3500],
            7 => ['sell' => 3500],
            8 => ['sell' => 12000],
            9 => ['sell' => 12000],
            10 => ['sell' => 12000],
        ];
        return $map[$variantId] ?? null;
    }
}
