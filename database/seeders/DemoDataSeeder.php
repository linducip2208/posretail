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
    private array $categoryIds = [];
    private array $brandIds = [];
    private array $unitIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    private array $groupIds = [1, 2, 3];
    private array $paymentMethodIds = [1, 2, 3, 4, 5, 6];
    private array $rewardIds = [1, 2, 3];
    private array $supplierIds = [1, 2, 3, 4, 5];
    private array $userIdIds = [1, 2, 3, 4, 5];
    private array $productIds = [];
    private array $variantIds = [];
    private array $customerIds = [];
    private array $products = [];
    private array $variantProductIds = [];
    private array $variantIdsByProduct = [];
    private array $variantSellById = [];

    public function run(): void
    {
        $faker = FakerFactory::create('id_ID');
        $now = now();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

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
            'blog_posts', 'blog_categories',
            'categories',
            'outlets', 'users',
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->seedUsers($now);
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

        $this->seedProducts($now);
        $this->seedProductVariants($now);
        $this->seedCustomers($faker, $now);
        $this->seedOrders($faker, $now);
        $this->seedPurchaseOrders($now);
        $this->seedStockOpnames($now);
        $this->seedLoyaltyPoints($now);
        $this->seedMembershipTiers($now);
        $this->seedStockTransfers($now);
        $this->seedReturns($now);
        $this->seedInstallments($now);
        $this->seedSupplierPayables($now);
        $this->seedShifts($now);
        $this->seedHeldCarts($now);
        $this->seedTableAreas($now);
        $this->seedTables($now);
        $this->seedRawMaterials($now);
        $this->seedRecipeItems($now);
        $this->seedDiscountTemplates($now);
        $this->seedAttendances($now);
        $this->seedBlogCategories($now);
        $this->seedBlogPosts($now);

        $this->command?->info('Demo data seeded: 1000 produk, 500 orders, 200 customers, and more!');
    }

    // ================================================================
    // USERS
    // ================================================================
    private function seedUsers(Carbon $now): void
    {
        DB::table('users')->insert([
            ['name' => 'Budi Hartono', 'email' => 'owner@pos-retail.test', 'email_verified_at' => $now, 'password' => bcrypt('password'), 'role' => 'owner', 'remember_token' => Str::random(10), 'created_at' => $now->copy()->subMonths(6), 'updated_at' => $now],
            ['name' => 'Sari Dewi', 'email' => 'manager@pos-retail.test', 'email_verified_at' => $now, 'password' => bcrypt('password'), 'role' => 'manager', 'remember_token' => Str::random(10), 'created_at' => $now->copy()->subMonths(5), 'updated_at' => $now],
            ['name' => 'Agus Prasetyo', 'email' => 'admin@pos-retail.test', 'email_verified_at' => $now, 'password' => bcrypt('password'), 'role' => 'admin', 'remember_token' => Str::random(10), 'created_at' => $now->copy()->subMonths(4), 'updated_at' => $now],
            ['name' => 'Rina Safitri', 'email' => 'kasir@pos-retail.test', 'email_verified_at' => $now, 'password' => bcrypt('password'), 'role' => 'kasir', 'remember_token' => Str::random(10), 'created_at' => $now->copy()->subMonths(3), 'updated_at' => $now],
            ['name' => 'Doni Kusuma', 'email' => 'gudang@pos-retail.test', 'email_verified_at' => $now, 'password' => bcrypt('password'), 'role' => 'gudang', 'remember_token' => Str::random(10), 'created_at' => $now->copy()->subMonths(2), 'updated_at' => $now],
        ]);
        $this->assignRolesToUsers();
    }

    private function assignRolesToUsers(): void
    {
        $roleMap = \App\Models\Role::pluck('id', 'slug');
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $roleSlug = $user->role;
            if ($roleSlug && isset($roleMap[$roleSlug])) {
                $user->roles()->sync([$roleMap[$roleSlug]]);
            }
        }
    }

    // ================================================================
    // OUTLETS
    // ================================================================
    private function seedOutlets(Carbon $now): void
    {
        DB::table('outlets')->insert([
            ['name' => 'Toko Pusat', 'code' => 'TP001', 'address' => 'Jl. Raya Malioboro No. 10, Yogyakarta', 'phone' => '0274-555001', 'active' => true, 'created_at' => $now->copy()->subMonths(12), 'updated_at' => $now],
            ['name' => 'Cabang Timur', 'code' => 'CT002', 'address' => 'Jl. Solo Raya KM 5, Klaten', 'phone' => '0272-555002', 'active' => true, 'created_at' => $now->copy()->subMonths(8), 'updated_at' => $now],
            ['name' => 'Cabang Barat', 'code' => 'CB003', 'address' => 'Jl. Magelang KM 8, Sleman', 'phone' => '0274-555003', 'active' => true, 'created_at' => $now->copy()->subMonths(6), 'updated_at' => $now],
        ]);
    }

    // ================================================================
    // CATEGORIES (25 supermarket-style categories)
    // ================================================================
    private function seedCategories(Carbon $now): void
    {
        DB::table('categories')->insert([
            ['name' => 'Makanan Ringan', 'slug' => 'makanan-ringan', 'description' => 'Snack, keripik, biskuit, kacang, dan camilan', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Minuman Ringan', 'slug' => 'minuman-ringan', 'description' => 'Air mineral, teh, soda, jus, minuman energi, isotonik', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mie & Bumbu Masak', 'slug' => 'mie-bumbu-masak', 'description' => 'Mie instan, bumbu instan, tepung bumbu, santan kemasan', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sabun & Pembersih', 'slug' => 'sabun-pembersih', 'description' => 'Sabun mandi, deterjen, pembersih lantai, cuci piring, pewangi', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Alat Tulis & Kantor', 'slug' => 'alat-tulis-kantor', 'description' => 'Buku, pulpen, pensil, spidol, kertas, perlengkapan kantor', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Elektronik & Aksesoris', 'slug' => 'elektronik-aksesoris', 'description' => 'Baterai, lampu, kabel, charger, earphone, aksesoris HP', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rokok & Tembakau', 'slug' => 'rokok-tembakau', 'description' => 'Rokok filter, kretek, mild, dan tembakau', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sembako', 'slug' => 'sembako', 'description' => 'Beras, gula, minyak goreng, telur, tepung, garam', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Susu & Olahan Susu', 'slug' => 'susu-olahan', 'description' => 'Susu cair, susu bubuk, yogurt, keju, mentega', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Roti, Kue & Sereal', 'slug' => 'roti-kue-sereal', 'description' => 'Roti tawar, roti manis, biskuit, sereal sarapan, wafer', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Makanan Beku', 'slug' => 'makanan-beku', 'description' => 'Nugget, sosis, bakso, es krim, dimsum, kentang goreng beku', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Makanan Kaleng & Siap Saji', 'slug' => 'makanan-kaleng', 'description' => 'Sarden, kornet, abon, buah kaleng, sayur kaleng', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Saus, Sambal & Bumbu Cair', 'slug' => 'saus-sambal-bumbu', 'description' => 'Kecap, sambal botol, saus tomat, saus tiram, cuka', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Minyak Goreng & Mentega', 'slug' => 'minyak-mentega', 'description' => 'Minyak goreng, minyak zaitun, margarin, mentega', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Beras & Biji-bijian', 'slug' => 'beras-bijian', 'description' => 'Beras putih, beras merah, ketan, kacang-kacangan, jagung', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kopi, Teh & Coklat', 'slug' => 'kopi-teh-coklat', 'description' => 'Kopi bubuk, kopi sachet, teh celup, coklat bubuk, minuman serbuk', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Permen & Coklat', 'slug' => 'permen-coklat', 'description' => 'Permen keras, permen lunak, coklat batang, coklat butir', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Perlengkapan Bayi', 'slug' => 'perlengkapan-bayi', 'description' => 'Popok bayi, susu formula, bubur bayi, minyak telon, tissue basah', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Perawatan Tubuh', 'slug' => 'perawatan-tubuh', 'description' => 'Body wash, lotion, deodorant, sabun muka, sunscreen', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Perawatan Rambut', 'slug' => 'perawatan-rambut', 'description' => 'Shampoo, conditioner, hair serum, hair color, pomade', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Obat & Kesehatan', 'slug' => 'obat-kesehatan', 'description' => 'Obat bebas, vitamin, suplemen, minyak angin, plester', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Makanan Hewan', 'slug' => 'makanan-hewan', 'description' => 'Makanan kucing, makanan anjing, pasir kucing, aksesoris hewan', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mainan Anak', 'slug' => 'mainan-anak', 'description' => 'Mainan edukasi, boneka, action figure, puzzle, bola', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Peralatan Rumah Tangga', 'slug' => 'peralatan-rumah-tangga', 'description' => 'Peralatan dapur, alat mandi, perkakas ringan, tempat penyimpanan', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Produk Lainnya', 'slug' => 'produk-lainnya', 'description' => 'Produk musiman, bundle promo, dan produk lainnya', 'parent_id' => null, 'outlet_id' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->categoryIds = range(1, 25);
    }

    // ================================================================
    // BRANDS (25 brands - supermarket style)
    // ================================================================
    private function seedBrands(Carbon $now): void
    {
        DB::table('brands')->insert([
            ['name' => 'Indofood', 'slug' => 'indofood', 'description' => 'PT Indofood Sukses Makmur Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Wings Group', 'slug' => 'wings-group', 'description' => 'Wings Group Indonesia', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Unilever', 'slug' => 'unilever', 'description' => 'PT Unilever Indonesia Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mayora', 'slug' => 'mayora', 'description' => 'PT Mayora Indah Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nestle', 'slug' => 'nestle', 'description' => 'PT Nestle Indonesia', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Danone', 'slug' => 'danone', 'description' => 'PT Danone Indonesia (Aqua, SGM, Mizone)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sosro', 'slug' => 'sosro', 'description' => 'PT Sinar Sosro (Teh Botol, Fruit Tea)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Garudafood', 'slug' => 'garudafood', 'description' => 'PT Garudafood Putra Putri Jaya Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kalbe', 'slug' => 'kalbe', 'description' => 'PT Kalbe Farma Tbk (Hydro Coco, Extra Joss)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ABC', 'slug' => 'abc', 'description' => 'PT Heinz ABC Indonesia (Kecap, Sambal, Sirup)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'P&G', 'slug' => 'pg', 'description' => 'Procter & Gamble (Pampers, Gillette, Head & Shoulders)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kapal Api', 'slug' => 'kapal-api', 'description' => 'PT Kapal Api Global (Kopi, Susu)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ultrajaya', 'slug' => 'ultrajaya', 'description' => 'PT Ultrajaya Milk Industry Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Frisian Flag', 'slug' => 'frisian-flag', 'description' => 'PT Frisian Flag Indonesia', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Siantar Top', 'slug' => 'siantar-top', 'description' => 'PT Siantar Top Tbk (Taro, Mie Gemez)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Djarum', 'slug' => 'djarum', 'description' => 'PT Djarum (Djarum Super, LA Lights)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gudang Garam', 'slug' => 'gudang-garam', 'description' => 'PT Gudang Garam Tbk', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sampoerna', 'slug' => 'sampoerna', 'description' => 'PT HM Sampoerna Tbk (A Mild, Dji Sam Soe)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Orang Tua Group', 'slug' => 'orang-tua', 'description' => 'OT Group (Tanggo, Fullo, Vitacharm)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kino', 'slug' => 'kino', 'description' => 'PT Kino Indonesia Tbk (Ellips, Eskulin)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mandom', 'slug' => 'mandom', 'description' => 'PT Mandom Indonesia Tbk (Gatsby, Pucelle, Pixy)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ajinomoto', 'slug' => 'ajinomoto', 'description' => 'PT Ajinomoto Indonesia (Masako, Sajiku)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bimoli', 'slug' => 'bimoli', 'description' => 'PT Salim Ivomas Pratama Tbk (Bimoli, Happy Soya)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Philips', 'slug' => 'philips', 'description' => 'PT Philips Indonesia (Lampu, Elektronik)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Maspion', 'slug' => 'maspion', 'description' => 'PT Maspion (Peralatan Rumah Tangga)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->brandIds = range(1, 25);
    }

    // ================================================================
    // UNITS (9)
    // ================================================================
    private function seedUnits(Carbon $now): void
    {
        DB::table('units')->insert([
            ['name' => 'PCS', 'code' => 'PCS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'KG', 'code' => 'KG', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'BOX', 'code' => 'BOX', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'LTR', 'code' => 'LTR', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'RENCENG', 'code' => 'RENCENG', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'SACHET', 'code' => 'SACHET', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'BOTOL', 'code' => 'BOTOL', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'KALENG', 'code' => 'KALENG', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PACK', 'code' => 'PACK', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    // ================================================================
    // CUSTOMER GROUPS
    // ================================================================
    private function seedCustomerGroups(Carbon $now): void
    {
        DB::table('customer_groups')->insert([
            ['name' => 'Regular', 'discount_percent' => 0, 'min_spent' => 0, 'description' => 'Pelanggan baru / tidak terdaftar', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Silver', 'discount_percent' => 3, 'min_spent' => 500000, 'description' => 'Total belanja > 500rb dapat diskon 3%', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gold', 'discount_percent' => 5, 'min_spent' => 2000000, 'description' => 'Total belanja > 2jt dapat diskon 5%', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedProviders(Carbon $now): void
    {
        DB::table('providers')->insert([
            ['name' => 'Midtrans Demo', 'type' => 'payment', 'api_format' => 'rest-redirect', 'base_url' => 'https://api.sandbox.midtrans.com/v2', 'api_key_encrypted' => null, 'api_secret_encrypted' => null, 'merchant_id' => 'G123456789', 'client_id' => null, 'extra_headers' => null, 'extra_config' => json_encode(['snap_js_url' => 'https://app.sandbox.midtrans.com/snap/snap.js']), 'is_active' => true, 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'WhatsApp Gateway Demo', 'type' => 'notification', 'api_format' => 'rest-api', 'base_url' => 'https://api.whatsapp.com', 'api_key_encrypted' => null, 'api_secret_encrypted' => null, 'merchant_id' => null, 'client_id' => null, 'extra_headers' => null, 'extra_config' => null, 'is_active' => false, 'is_default' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedPaymentMethods(Carbon $now): void
    {
        DB::table('payment_methods')->insert([
            ['name' => 'Tunai', 'code' => 'CASH', 'provider_id' => null, 'type' => 'offline', 'active' => true, 'is_gateway' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'QRIS', 'code' => 'QRIS', 'provider_id' => 1, 'type' => 'online', 'active' => true, 'is_gateway' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Transfer Bank', 'code' => 'BANK_TRANSFER', 'provider_id' => 1, 'type' => 'online', 'active' => true, 'is_gateway' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'E-Wallet', 'code' => 'EWALLET', 'provider_id' => 1, 'type' => 'online', 'active' => true, 'is_gateway' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kartu Debit', 'code' => 'DEBIT', 'provider_id' => 1, 'type' => 'online', 'active' => true, 'is_gateway' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kredit', 'code' => 'CREDIT', 'provider_id' => null, 'type' => 'offline', 'active' => true, 'is_gateway' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedLoyaltyRewards(Carbon $now): void
    {
        DB::table('loyalty_rewards')->insert([
            ['name' => 'Diskon 5.000', 'points_required' => 50, 'discount_type' => 'fixed', 'discount_value' => 5000, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diskon 10%', 'points_required' => 100, 'discount_type' => 'percent', 'discount_value' => 10, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gratis 1 Produk Pilihan', 'points_required' => 200, 'discount_type' => 'fixed', 'discount_value' => 25000, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedSuppliers(Carbon $now): void
    {
        DB::table('suppliers')->insert([
            ['name' => 'PT Indomarco Prismatama', 'contact_person' => 'Hendra Wijaya', 'phone' => '021-5550101', 'email' => 'hendra@indomarco.co.id', 'address' => 'Jl. Raya Bogor KM 28, Jakarta Timur', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CV Sumber Makmur', 'contact_person' => 'Susi Rahmawati', 'phone' => '0274-5550202', 'email' => 'susi@sumbermakmur.co.id', 'address' => 'Jl. Kusumanegara No. 45, Yogyakarta', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'UD Lancar Jaya', 'contact_person' => 'Joko Santoso', 'phone' => '0271-5550303', 'email' => 'joko@lancarjaya.co.id', 'address' => 'Jl. Slamet Riyadi No. 120, Solo', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PT Mitra Distribusi Nusantara', 'contact_person' => 'Rudi Hartanto', 'phone' => '031-5550404', 'email' => 'rudi@mitranusantara.co.id', 'address' => 'Jl. Raya Darmo No. 88, Surabaya', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CV Berkah Utama', 'contact_person' => 'Ani Lestari', 'phone' => '024-5550505', 'email' => 'ani@berkahutama.co.id', 'address' => 'Jl. Pandanaran No. 67, Semarang', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedSystemSettings(Carbon $now): void
    {
        $settings = [];
        $keys = ['app_name', 'store_address', 'store_phone', 'tax_percent', 'loyalty_points_rate', 'low_stock_threshold', 'currency', 'timezone', 'receipt_footer', 'approval_threshold', 'hero_headline', 'hero_subheadline', 'whatsapp_number', 'pos_price', 'pos_features'];
        $values = [
            'POS Retail', 'Jl. Raya Malioboro No. 10, Yogyakarta 55271', '0274-555001', '11', '10000', '10', 'IDR', 'Asia/Jakarta',
            'Terima kasih telah berbelanja!', '5000000',
            'Solusi Kasir Modern untuk Toko Retail Anda',
            'Kelola produk, transaksi penjualan, inventori, pelanggan, dan laporan — semua dalam satu dashboard. Dukung multi-outlet, scan barcode, dan program loyalitas.',
            '6281296052010', 'Rp 4.999.000',
            "Full source code — Laravel + Filament + TailwindCSS\n30+ admin resources, 3 dashboard report pages\nPOS Kasir, Inventori, Pembelian, Loyalitas lengkap\nPayment gateway dinamis (Midtrans, Xendit, dll)\nCustomer portal, API v1, PSEO directory built-in\nMulti-outlet + Blog + IndexNow SEO\n52 tabel DB, approval workflow\nLifetime update + 6 bulan support",
        ];
        foreach ($keys as $i => $key) {
            $settings[] = ['key' => $key, 'value' => $values[$i], 'outlet_id' => null, 'created_at' => $now, 'updated_at' => $now];
            foreach ([1, 2, 3] as $outletId) {
                if ($i <= 2) {
                    $outletValues = [
                        1 => ['POS Retail - Toko Pusat', 'Jl. Malioboro No. 10, Yogyakarta', '0274-555001'],
                        2 => ['POS Retail - Cabang Timur', 'Jl. Solo Raya KM 5, Klaten', '0272-555002'],
                        3 => ['POS Retail - Cabang Barat', 'Jl. Magelang KM 8, Sleman', '0274-555003'],
                    ];
                    $settings[] = ['key' => $key, 'value' => $outletValues[$outletId][$i], 'outlet_id' => $outletId, 'created_at' => $now, 'updated_at' => $now];
                }
            }
        }
        DB::table('system_settings')->insert($settings);

        DB::table('system_settings')->insert([
            ['key' => 'order_types', 'value' => json_encode([
                ['value' => 'walk_in', 'label' => 'Walk-in / Umum'],
                ['value' => 'member', 'label' => 'Member'],
                ['value' => 'online', 'label' => 'Online / Delivery'],
            ]), 'outlet_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedUserOutlet(Carbon $now): void
    {
        DB::table('user_outlet')->insert([
            ['user_id' => 1, 'outlet_id' => 1], ['user_id' => 1, 'outlet_id' => 2], ['user_id' => 1, 'outlet_id' => 3],
            ['user_id' => 2, 'outlet_id' => 1], ['user_id' => 3, 'outlet_id' => 1], ['user_id' => 3, 'outlet_id' => 2],
            ['user_id' => 4, 'outlet_id' => 1], ['user_id' => 4, 'outlet_id' => 2], ['user_id' => 4, 'outlet_id' => 3],
            ['user_id' => 5, 'outlet_id' => 1],
        ]);
    }

    // ================================================================
    // PRODUCTS — 1000 realistic Indonesian supermarket products
    // ================================================================
    private function seedProducts(Carbon $now): void
    {
        $faker = FakerFactory::create('id_ID');

        // First 200 curated products (categories 1-4), then generate the rest up to 1000.
        $defs = $this->getHardcodedProducts();
        $defs = array_merge($defs, $this->generateProducts($faker, count($defs), 1000));

        $products = [];
        $usedSlugs = [];
        foreach ($defs as $i => $p) {
            $slug = Str::slug($p['name']);
            if (isset($usedSlugs[$slug])) {
                $slug .= '-' . ($i + 1);
            }
            $usedSlugs[$slug] = true;

            $sku = 'SKU' . str_pad($i + 1, 5, '0', STR_PAD_LEFT);
            $barcode = '899' . str_pad((string) ($i + 1), 10, '0', STR_PAD_LEFT);
            $products[] = [
                'name' => $p['name'], 'slug' => $slug, 'description' => $p['name'] . ' - kualitas terjamin',
                'category_id' => $p['cat'], 'brand_id' => $p['brand'], 'unit_id' => $p['unit'],
                'outlet_id' => null, 'sku' => $sku, 'barcode' => $barcode,
                'cost_price' => $p['cost'], 'selling_price' => $p['sell'],
                'wholesale_price' => $p['whole'], 'member_price' => $p['mem'],
                'min_stock' => $p['min'], 'max_stock' => $p['max'], 'current_stock' => $p['stock'],
                'image' => 'https://picsum.photos/seed/' . urlencode($slug) . '/200/200',
                'has_variants' => false, 'active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ];
            $this->products[$i + 1] = $p;
        }
        foreach (array_chunk($products, 100) as $chunk) {
            DB::table('products')->insert($chunk);
        }
        $this->productIds = range(1, count($products));
    }

    /**
     * Generate realistic Indonesian retail products to fill up to $target total.
     * Distributed across categories 4-25 with brand/unit/price patterns.
     */
    private function generateProducts($faker, int $existingCount, int $target): array
    {
        $needed = $target - $existingCount;
        if ($needed <= 0) {
            return [];
        }

        // Per-category name templates: [prefixes], [variants/sizes], [unit], [brand pool], [cost range]
        $catalog = [
            4  => [ // Sabun & Pembersih
                'items' => ['Sabun Cuci Piring', 'Deterjen Bubuk', 'Pembersih Lantai', 'Pemutih Pakaian', 'Pelembut Pakaian', 'Pembersih Kaca', 'Sabun Cair Antiseptik', 'Karbol Wangi', 'Spons Cuci', 'Pengharum Ruangan'],
                'sizes' => ['refill 400ml', 'refill 800ml', 'botol 200ml', 'sachet 45gr', 'pouch 1kg', '250ml', '500ml', '1 liter'],
                'units' => [7, 1, 6, 9], 'brands' => [2, 3, 10], 'cost' => [3000, 22000],
            ],
            5  => [ // Alat Tulis & Kantor
                'items' => ['Buku Tulis', 'Pulpen', 'Pensil 2B', 'Spidol Whiteboard', 'Penghapus', 'Penggaris', 'Kertas HVS A4', 'Map Plastik', 'Lakban Bening', 'Lem Kertas', 'Stabilo', 'Tipe-X', 'Amplop', 'Binder Clip'],
                'sizes' => ['isi 38 lembar', 'isi 58 lembar', '1 pcs', 'pak isi 12', '70gsm', 'lebar 45mm', 'ukuran 30cm', 'kecil', 'besar'],
                'units' => [1, 9, 3], 'brands' => [24, 25, 19], 'cost' => [1500, 45000],
            ],
            6  => [ // Elektronik & Aksesoris
                'items' => ['Baterai AA', 'Baterai AAA', 'Baterai 9V', 'Lampu LED', 'Kabel Data USB', 'Charger HP', 'Earphone', 'Powerbank', 'Kabel Roll', 'Fitting Lampu', 'Steker Listrik', 'Senter LED', 'Kabel HDMI'],
                'sizes' => ['isi 2', 'isi 4', '5 watt', '12 watt', '23 watt', 'Type-C 1M', 'Micro 1M', '10000mAh', '1.5 meter', '3 meter'],
                'units' => [1, 9], 'brands' => [24, 25], 'cost' => [4000, 120000],
            ],
            7  => [ // Rokok & Tembakau
                'items' => ['Rokok Filter', 'Rokok Kretek', 'Rokok Mild', 'Rokok Menthol', 'Tembakau Lintingan'],
                'sizes' => ['isi 12', 'isi 16', 'isi 20', 'kemasan 50gr'],
                'units' => [5, 1], 'brands' => [16, 17, 18], 'cost' => [16000, 35000],
            ],
            8  => [ // Sembako
                'items' => ['Beras Premium', 'Beras Medium', 'Gula Pasir', 'Minyak Goreng', 'Tepung Terigu', 'Telur Ayam', 'Garam Dapur', 'Kecap Manis', 'Margarin', 'Susu Kental Manis'],
                'sizes' => ['1kg', '2kg', '5kg', 'pouch 2L', 'botol 1L', 'refill 900ml', 'kemasan 500gr'],
                'units' => [2, 1, 4, 7], 'brands' => [1, 23, 14], 'cost' => [8000, 78000],
            ],
            9  => [ // Susu & Olahan Susu
                'items' => ['Susu UHT Full Cream', 'Susu UHT Coklat', 'Susu Bubuk', 'Yogurt', 'Keju Cheddar', 'Mentega', 'Susu Kedelai', 'Krimer Kental Manis'],
                'sizes' => ['200ml', '250ml', '1 liter', 'kotak 115gr', 'sachet 27gr', 'cup 80gr', 'refill 400gr'],
                'units' => [7, 1, 6, 3], 'brands' => [13, 14, 5], 'cost' => [4000, 65000],
            ],
            10 => [ // Roti, Kue & Sereal
                'items' => ['Roti Tawar', 'Roti Manis Coklat', 'Roti Sobek', 'Sereal Jagung', 'Wafer Coklat', 'Biskuit Gandum', 'Kue Kering Nastar', 'Cracker Asin'],
                'sizes' => ['isi 10 lembar', 'kemasan 300gr', 'box 165gr', 'pouch 120gr', 'toples 250gr', 'roll 130gr'],
                'units' => [1, 9, 3], 'brands' => [4, 8, 5], 'cost' => [5000, 45000],
            ],
            11 => [ // Makanan Beku
                'items' => ['Nugget Ayam', 'Sosis Sapi', 'Bakso Sapi', 'Es Krim', 'Dimsum', 'Kentang Goreng', 'Otak-Otak', 'Chicken Katsu'],
                'sizes' => ['pouch 250gr', 'pouch 500gr', 'kemasan 400gr', 'cup 100ml', 'box 200gr', 'isi 10'],
                'units' => [9, 1, 3], 'brands' => [1, 8, 2], 'cost' => [8000, 55000],
            ],
            12 => [ // Makanan Kaleng & Siap Saji
                'items' => ['Sarden Kaleng', 'Kornet Sapi', 'Abon Sapi', 'Buah Kaleng', 'Jamur Kaleng', 'Tuna Kaleng', 'Sosis Kaleng', 'Sup Krim Instan'],
                'sizes' => ['155gr', '198gr', '340gr', 'kaleng 425gr', 'pouch 100gr'],
                'units' => [8, 1, 6], 'brands' => [10, 1, 5], 'cost' => [7000, 42000],
            ],
            13 => [ // Saus, Sambal & Bumbu Cair
                'items' => ['Kecap Manis', 'Kecap Asin', 'Saus Sambal', 'Saus Tomat', 'Saus Tiram', 'Cuka Masak', 'Sambal Botol', 'Saus Teriyaki'],
                'sizes' => ['botol 135ml', 'botol 275ml', 'botol 340ml', 'refill 520ml', 'sachet 20ml'],
                'units' => [7, 6, 1], 'brands' => [10, 3, 1], 'cost' => [3000, 22000],
            ],
            14 => [ // Minyak Goreng & Mentega
                'items' => ['Minyak Goreng Sawit', 'Minyak Zaitun', 'Margarin', 'Mentega', 'Minyak Kelapa'],
                'sizes' => ['pouch 1L', 'pouch 2L', 'botol 1L', 'kemasan 200gr', 'kaleng 250ml'],
                'units' => [7, 1, 4], 'brands' => [23, 1, 14], 'cost' => [12000, 80000],
            ],
            15 => [ // Beras & Biji-bijian
                'items' => ['Beras Merah', 'Beras Ketan', 'Kacang Hijau', 'Kacang Tanah', 'Kedelai', 'Jagung Pipil', 'Kacang Merah'],
                'sizes' => ['pouch 500gr', 'pouch 1kg', 'kemasan 2kg', 'karung 5kg'],
                'units' => [2, 1, 9], 'brands' => [1, 23, 2], 'cost' => [10000, 90000],
            ],
            16 => [ // Kopi, Teh & Coklat
                'items' => ['Kopi Bubuk', 'Kopi Sachet 3in1', 'Teh Celup', 'Coklat Bubuk', 'Kopi Instan', 'Teh Tubruk', 'Minuman Serbuk Jahe'],
                'sizes' => ['sachet 20gr', 'renceng isi 10', 'box isi 25', 'kemasan 165gr', 'kemasan 380gr', 'pouch 100gr'],
                'units' => [6, 5, 3, 1], 'brands' => [12, 5, 4], 'cost' => [1500, 30000],
            ],
            17 => [ // Permen & Coklat
                'items' => ['Permen Mint', 'Permen Susu', 'Coklat Batang', 'Coklat Butir', 'Permen Karet', 'Permen Jelly', 'Coklat Wafer'],
                'sizes' => ['bungkus 125gr', 'roll isi 12', 'batang 40gr', 'toples 150gr', 'sachet 25gr'],
                'units' => [1, 9, 3], 'brands' => [4, 8, 19], 'cost' => [1000, 18000],
            ],
            18 => [ // Perlengkapan Bayi
                'items' => ['Popok Bayi', 'Susu Formula', 'Bubur Bayi', 'Minyak Telon', 'Tissue Basah Bayi', 'Sabun Bayi', 'Bedak Bayi', 'Sampo Bayi'],
                'sizes' => ['isi 20 S', 'isi 24 M', 'isi 30 L', 'box 400gr', 'kemasan 120gr', 'botol 100ml', 'refill 50 sheet'],
                'units' => [9, 3, 7, 1], 'brands' => [11, 6, 20], 'cost' => [8000, 130000],
            ],
            19 => [ // Perawatan Tubuh
                'items' => ['Body Wash', 'Body Lotion', 'Deodorant Roll On', 'Sabun Muka', 'Sunscreen', 'Hand Sanitizer', 'Body Scrub', 'Lip Balm'],
                'sizes' => ['botol 100ml', 'botol 250ml', 'refill 400ml', 'tube 50ml', 'roll 40ml', 'sachet 8ml'],
                'units' => [7, 1, 6], 'brands' => [3, 20, 21], 'cost' => [5000, 55000],
            ],
            20 => [ // Perawatan Rambut
                'items' => ['Shampoo Anti Ketombe', 'Conditioner', 'Hair Serum', 'Hair Tonic', 'Pomade', 'Vitamin Rambut', 'Hair Mask', 'Minyak Rambut'],
                'sizes' => ['botol 170ml', 'botol 340ml', 'sachet 10ml', 'tube 100ml', 'kaleng 80gr', 'refill 400ml'],
                'units' => [7, 6, 1], 'brands' => [3, 20, 21], 'cost' => [4000, 60000],
            ],
            21 => [ // Obat & Kesehatan
                'items' => ['Obat Sakit Kepala', 'Vitamin C', 'Suplemen Daya Tahan', 'Minyak Angin', 'Plester Luka', 'Obat Batuk', 'Balsem Otot', 'Masker Medis', 'Obat Maag', 'Tetes Mata'],
                'sizes' => ['strip isi 4', 'strip isi 10', 'botol 60ml', 'tube 20gr', 'box isi 20', 'roll 10ml', 'kemasan 30ml'],
                'units' => [1, 5, 8, 7], 'brands' => [9, 5, 19], 'cost' => [2000, 45000],
            ],
            22 => [ // Makanan Hewan
                'items' => ['Makanan Kucing', 'Makanan Anjing', 'Pasir Kucing', 'Snack Kucing', 'Snack Anjing', 'Susu Anak Kucing', 'Vitamin Hewan'],
                'sizes' => ['pouch 85gr', 'kemasan 500gr', 'karung 1kg', 'kaleng 400gr', 'box 20L'],
                'units' => [9, 1, 8, 2], 'brands' => [5, 24, 25], 'cost' => [6000, 95000],
            ],
            23 => [ // Mainan Anak
                'items' => ['Mainan Edukasi Balok', 'Boneka Beruang', 'Action Figure', 'Puzzle Kayu', 'Bola Plastik', 'Mobil-Mobilan', 'Lego Set', 'Robot Mainan', 'Masak-Masakan'],
                'sizes' => ['ukuran kecil', 'ukuran sedang', 'ukuran besar', 'isi 24 pcs', 'edisi deluxe'],
                'units' => [1, 3, 9], 'brands' => [25, 24, 19], 'cost' => [15000, 150000],
            ],
            24 => [ // Peralatan Rumah Tangga
                'items' => ['Sapu Lantai', 'Pel Lantai', 'Ember Plastik', 'Gelas Kaca', 'Piring Melamin', 'Sendok Set', 'Wajan Anti Lengket', 'Panci', 'Toples Plastik', 'Rak Piring', 'Sikat Kloset', 'Tempat Sampah'],
                'sizes' => ['ukuran kecil', 'ukuran sedang', 'ukuran besar', 'isi 6', 'diameter 24cm', 'kapasitas 5L'],
                'units' => [1, 9, 3], 'brands' => [25, 24, 19], 'cost' => [10000, 120000],
            ],
            25 => [ // Produk Lainnya
                'items' => ['Paket Hemat Sembako', 'Bundle Snack', 'Voucher Belanja', 'Kantong Plastik', 'Tas Belanja', 'Korek Api', 'Lilin', 'Tusuk Gigi', 'Payung Lipat', 'Jas Hujan'],
                'sizes' => ['1 paket', 'isi 100', 'ukuran sedang', 'kemasan 50gr', 'edisi promo'],
                'units' => [9, 1, 3], 'brands' => [25, 24, 19], 'cost' => [2000, 75000],
            ],
        ];

        $catKeys = array_keys($catalog);
        $catCount = count($catKeys);
        $result = [];
        $used = [];

        for ($n = 0; $n < $needed; $n++) {
            $cat = $catKeys[$n % $catCount];
            $cfg = $catalog[$cat];

            $attempt = 0;
            do {
                $item = $cfg['items'][array_rand($cfg['items'])];
                $size = $cfg['sizes'][array_rand($cfg['sizes'])];
                $name = trim($item . ' ' . $size);
                $attempt++;
                if ($attempt > 6) {
                    $name .= ' ' . strtoupper($faker->bothify('##?'));
                    break;
                }
            } while (isset($used[$name]));
            $used[$name] = true;

            $cost = random_int((int) $cfg['cost'][0], (int) $cfg['cost'][1]);
            $cost = (int) (round($cost / 500) * 500);
            if ($cost < 500) {
                $cost = 500;
            }
            $margin = random_int(25, 55) / 100;
            $sell = (int) (round(($cost * (1 + $margin)) / 500) * 500);
            $whole = (int) (round(($sell * 0.94) / 500) * 500);
            $mem = (int) (round(($sell * 0.90) / 500) * 500);
            $min = random_int(3, 20);
            $max = random_int(60, 400);
            $stock = random_int($min, $max);

            $result[] = [
                'name'  => $name,
                'cat'   => $cat,
                'brand' => $cfg['brands'][array_rand($cfg['brands'])],
                'unit'  => $cfg['units'][array_rand($cfg['units'])],
                'cost'  => $cost,
                'sell'  => $sell,
                'whole' => $whole,
                'mem'   => $mem,
                'stock' => $stock,
                'min'   => $min,
                'max'   => $max,
            ];
        }

        return $result;
    }

    private function getHardcodedProducts(): array
    {
        /* Format: ['name', cat, brand, unit, cost, sell, whole, mem, stock, min, max] */
        return [
            // ==================== CAT 1: MAKANAN RINGAN (50) ====================
            ['name'=>'Chitato Sapi Panggang 60gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>8000,'sell'=>12000,'whole'=>11000,'mem'=>10500,'stock'=>120,'min'=>10,'max'=>200],
            ['name'=>'Chitato Ayam Bawang 60gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>8000,'sell'=>12000,'whole'=>11000,'mem'=>10500,'stock'=>110,'min'=>10,'max'=>200],
            ['name'=>'Chitato Sapi Panggang 150gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>15500,'sell'=>22000,'whole'=>21000,'mem'=>19500,'stock'=>45,'min'=>5,'max'=>80],
            ['name'=>'Qtela Singkong Balado 55gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>3500,'sell'=>6000,'whole'=>5500,'mem'=>5000,'stock'=>80,'min'=>5,'max'=>150],
            ['name'=>'Qtela Tempe 50gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>3500,'sell'=>6000,'whole'=>5500,'mem'=>5000,'stock'=>75,'min'=>5,'max'=>150],
            ['name'=>'Lays Rumput Laut 68gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>9000,'sell'=>13500,'whole'=>12500,'mem'=>12000,'stock'=>60,'min'=>5,'max'=>100],
            ['name'=>'Lays Sapi Panggang 68gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>9000,'sell'=>13500,'whole'=>12500,'mem'=>12000,'stock'=>55,'min'=>5,'max'=>100],
            ['name'=>'Taro Net Rumput Laut 35gr','cat'=>1,'brand'=>15,'unit'=>1,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4000,'stock'=>150,'min'=>10,'max'=>200],
            ['name'=>'Taro Net Ayam Bawang 35gr','cat'=>1,'brand'=>15,'unit'=>1,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4000,'stock'=>140,'min'=>10,'max'=>200],
            ['name'=>'Cheetos Jagung Bakar 45gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>5500,'sell'=>8500,'whole'=>8000,'mem'=>7500,'stock'=>90,'min'=>10,'max'=>150],
            ['name'=>'Cheetos Keju 45gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>5500,'sell'=>8500,'whole'=>8000,'mem'=>7500,'stock'=>85,'min'=>10,'max'=>150],
            ['name'=>'Doritos BBQ 65gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>9500,'sell'=>14000,'whole'=>13000,'mem'=>12500,'stock'=>40,'min'=>5,'max'=>70],
            ['name'=>'Doritos Nacho Cheese 65gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>9500,'sell'=>14000,'whole'=>13000,'mem'=>12500,'stock'=>38,'min'=>5,'max'=>70],
            ['name'=>'Beng Beng Coklat 30gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>2000,'sell'=>3000,'whole'=>2800,'mem'=>2500,'stock'=>200,'min'=>20,'max'=>300],
            ['name'=>'Beng Beng Vanilla 30gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>2000,'sell'=>3000,'whole'=>2800,'mem'=>2500,'stock'=>180,'min'=>20,'max'=>300],
            ['name'=>'Oreo Original 133gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>7000,'sell'=>10500,'whole'=>10000,'mem'=>9500,'stock'=>75,'min'=>5,'max'=>120],
            ['name'=>'Oreo Vanilla 133gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>7000,'sell'=>10500,'whole'=>10000,'mem'=>9500,'stock'=>70,'min'=>5,'max'=>120],
            ['name'=>'Oreo Strawberry 133gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>7000,'sell'=>10500,'whole'=>10000,'mem'=>9500,'stock'=>65,'min'=>5,'max'=>120],
            ['name'=>'Biskuat Coklat 104gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6000,'stock'=>100,'min'=>10,'max'=>150],
            ['name'=>'Roma Kelapa 81gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>5500,'sell'=>8000,'whole'=>7500,'mem'=>7000,'stock'=>110,'min'=>10,'max'=>180],
            ['name'=>'Roma Malkist Coklat 108gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>95,'min'=>10,'max'=>150],
            ['name'=>'Roma Sari Gandum 120gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>5500,'sell'=>8000,'whole'=>7500,'mem'=>7000,'stock'=>100,'min'=>10,'max'=>160],
            ['name'=>'Khong Guan Assorted 600gr','cat'=>1,'brand'=>4,'unit'=>3,'cost'=>38000,'sell'=>52000,'whole'=>49000,'mem'=>47000,'stock'=>20,'min'=>3,'max'=>35],
            ['name'=>'Kacang Garuda 150gr','cat'=>1,'brand'=>8,'unit'=>1,'cost'=>8500,'sell'=>12500,'whole'=>11500,'mem'=>11000,'stock'=>45,'min'=>5,'max'=>80],
            ['name'=>'Kacang Garuda 75gr','cat'=>1,'brand'=>8,'unit'=>1,'cost'=>4500,'sell'=>6500,'whole'=>6000,'mem'=>5500,'stock'=>70,'min'=>5,'max'=>120],
            ['name'=>'Kacang Sukro 150gr','cat'=>1,'brand'=>8,'unit'=>1,'cost'=>7000,'sell'=>10000,'whole'=>9500,'mem'=>9000,'stock'=>55,'min'=>5,'max'=>90],
            ['name'=>'Kacang Dua Kelinci 150gr','cat'=>1,'brand'=>2,'unit'=>1,'cost'=>8000,'sell'=>12000,'whole'=>11000,'mem'=>10500,'stock'=>40,'min'=>5,'max'=>70],
            ['name'=>'Rosta Kacang Panggang 150gr','cat'=>1,'brand'=>8,'unit'=>1,'cost'=>7500,'sell'=>11000,'whole'=>10000,'mem'=>9500,'stock'=>50,'min'=>5,'max'=>85],
            ['name'=>'Pilus Garuda 50gr','cat'=>1,'brand'=>8,'unit'=>1,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4000,'stock'=>130,'min'=>10,'max'=>200],
            ['name'=>'Basreng Pedas 100gr','cat'=>1,'brand'=>8,'unit'=>1,'cost'=>5500,'sell'=>8000,'whole'=>7500,'mem'=>7000,'stock'=>65,'min'=>5,'max'=>100],
            ['name'=>'Keripik Singkong Balado 100gr','cat'=>1,'brand'=>15,'unit'=>1,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6000,'stock'=>85,'min'=>5,'max'=>140],
            ['name'=>'Keripik Pisang Coklat 80gr','cat'=>1,'brand'=>15,'unit'=>1,'cost'=>5000,'sell'=>7500,'whole'=>7000,'mem'=>6500,'stock'=>60,'min'=>5,'max'=>100],
            ['name'=>'Makaroni Panggang 70gr','cat'=>1,'brand'=>15,'unit'=>1,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>90,'min'=>10,'max'=>140],
            ['name'=>'Hello Panda Coklat 35gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>120,'min'=>10,'max'=>200],
            ['name'=>'Hello Panda Strawberry 35gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>110,'min'=>10,'max'=>200],
            ['name'=>'Astor Wafer 128gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>6500,'sell'=>9500,'whole'=>9000,'mem'=>8500,'stock'=>55,'min'=>5,'max'=>90],
            ['name'=>'Kusuka Singkong 100gr','cat'=>1,'brand'=>2,'unit'=>1,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6000,'stock'=>75,'min'=>5,'max'=>120],
            ['name'=>'Kusuka Kentang Goreng 60gr','cat'=>1,'brand'=>2,'unit'=>1,'cost'=>4000,'sell'=>6500,'whole'=>6000,'mem'=>5500,'stock'=>80,'min'=>5,'max'=>130],
            ['name'=>'Twisko Jagung Bakar 35gr','cat'=>1,'brand'=>2,'unit'=>1,'cost'=>2500,'sell'=>4000,'whole'=>3800,'mem'=>3500,'stock'=>160,'min'=>15,'max'=>250],
            ['name'=>'Pocky Coklat 47gr','cat'=>1,'brand'=>2,'unit'=>1,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>70,'min'=>5,'max'=>110],
            ['name'=>'Pocky Strawberry 47gr','cat'=>1,'brand'=>2,'unit'=>1,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>65,'min'=>5,'max'=>110],
            ['name'=>'Good Time Chocochips 80gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>6500,'sell'=>9500,'whole'=>9000,'mem'=>8500,'stock'=>60,'min'=>5,'max'=>100],
            ['name'=>'Slai Olai Keju 100gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>5000,'sell'=>8000,'whole'=>7500,'mem'=>7000,'stock'=>55,'min'=>5,'max'=>90],
            ['name'=>'Slai Olai Nanas 100gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>5000,'sell'=>8000,'whole'=>7500,'mem'=>7000,'stock'=>50,'min'=>5,'max'=>90],
            ['name'=>'Better Wafer Keju 86gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6000,'stock'=>80,'min'=>5,'max'=>130],
            ['name'=>'Choki Choki Coklat 9gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>1000,'sell'=>2000,'whole'=>1800,'mem'=>1700,'stock'=>300,'min'=>30,'max'=>500],
            ['name'=>'Potabee BBQ 60gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>7500,'sell'=>11000,'whole'=>10000,'mem'=>9500,'stock'=>70,'min'=>5,'max'=>120],
            ['name'=>'Potabee Rumput Laut 60gr','cat'=>1,'brand'=>1,'unit'=>1,'cost'=>7500,'sell'=>11000,'whole'=>10000,'mem'=>9500,'stock'=>65,'min'=>5,'max'=>120],
            ['name'=>'Nextar Brownies 84gr','cat'=>1,'brand'=>4,'unit'=>1,'cost'=>5000,'sell'=>8000,'whole'=>7500,'mem'=>7000,'stock'=>60,'min'=>5,'max'=>100],
            ['name'=>'Sukro Panggang 50gr','cat'=>1,'brand'=>8,'unit'=>1,'cost'=>2500,'sell'=>4000,'whole'=>3800,'mem'=>3500,'stock'=>140,'min'=>10,'max'=>220],

            // ==================== CAT 2: MINUMAN RINGAN (50) ====================
            ['name'=>'Aqua 330ml','cat'=>2,'brand'=>6,'unit'=>1,'cost'=>2000,'sell'=>3000,'whole'=>2800,'mem'=>2700,'stock'=>500,'min'=>50,'max'=>800],
            ['name'=>'Aqua 600ml','cat'=>2,'brand'=>6,'unit'=>1,'cost'=>2000,'sell'=>3500,'whole'=>3000,'mem'=>3000,'stock'=>300,'min'=>30,'max'=>500],
            ['name'=>'Aqua 1500ml','cat'=>2,'brand'=>6,'unit'=>1,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>200,'min'=>20,'max'=>350],
            ['name'=>'Le Minerale 600ml','cat'=>2,'brand'=>6,'unit'=>1,'cost'=>2000,'sell'=>3000,'whole'=>2800,'mem'=>2700,'stock'=>280,'min'=>30,'max'=>450],
            ['name'=>'Le Minerale 1500ml','cat'=>2,'brand'=>6,'unit'=>1,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4300,'stock'=>180,'min'=>20,'max'=>300],
            ['name'=>'Nestle Pure Life 600ml','cat'=>2,'brand'=>5,'unit'=>1,'cost'=>2000,'sell'=>3000,'whole'=>2800,'mem'=>2700,'stock'=>250,'min'=>25,'max'=>400],
            ['name'=>'Teh Botol Sosro 450ml','cat'=>2,'brand'=>7,'unit'=>1,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4500,'stock'=>180,'min'=>20,'max'=>300],
            ['name'=>'Teh Kotak Original 200ml','cat'=>2,'brand'=>13,'unit'=>1,'cost'=>3000,'sell'=>4500,'whole'=>4200,'mem'=>4000,'stock'=>200,'min'=>20,'max'=>350],
            ['name'=>'Teh Pucuk Harum 500ml','cat'=>2,'brand'=>4,'unit'=>1,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4300,'stock'=>170,'min'=>15,'max'=>280],
            ['name'=>'Frestea Jasmine 500ml','cat'=>2,'brand'=>1,'unit'=>1,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>120,'min'=>10,'max'=>200],
            ['name'=>'NU Green Tea 500ml','cat'=>2,'brand'=>2,'unit'=>1,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>110,'min'=>10,'max'=>180],
            ['name'=>'Coca Cola 390ml','cat'=>2,'brand'=>19,'unit'=>8,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6200,'stock'=>150,'min'=>15,'max'=>250],
            ['name'=>'Coca Cola 1.5L','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>9000,'sell'=>13500,'whole'=>12500,'mem'=>12000,'stock'=>60,'min'=>5,'max'=>100],
            ['name'=>'Sprite 390ml','cat'=>2,'brand'=>19,'unit'=>8,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6200,'stock'=>140,'min'=>15,'max'=>240],
            ['name'=>'Fanta Strawberry 390ml','cat'=>2,'brand'=>19,'unit'=>8,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6200,'stock'=>130,'min'=>15,'max'=>230],
            ['name'=>'Big Cola 500ml','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>160,'min'=>15,'max'=>260],
            ['name'=>'Pulpy Orange 300ml','cat'=>2,'brand'=>4,'unit'=>7,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6200,'stock'=>90,'min'=>10,'max'=>150],
            ['name'=>'Buavita Jambu 250ml','cat'=>2,'brand'=>13,'unit'=>7,'cost'=>5000,'sell'=>8000,'whole'=>7500,'mem'=>7200,'stock'=>70,'min'=>5,'max'=>120],
            ['name'=>'Buavita Mangga 250ml','cat'=>2,'brand'=>13,'unit'=>7,'cost'=>5000,'sell'=>8000,'whole'=>7500,'mem'=>7200,'stock'=>65,'min'=>5,'max'=>120],
            ['name'=>'Minute Maid Pulpy 250ml','cat'=>2,'brand'=>5,'unit'=>7,'cost'=>5000,'sell'=>7500,'whole'=>7000,'mem'=>6800,'stock'=>75,'min'=>5,'max'=>130],
            ['name'=>'ABC Juice Jeruk 250ml','cat'=>2,'brand'=>10,'unit'=>7,'cost'=>4000,'sell'=>6500,'whole'=>6000,'mem'=>5800,'stock'=>80,'min'=>5,'max'=>140],
            ['name'=>'Pocari Sweat 350ml','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>5500,'sell'=>8500,'whole'=>8000,'mem'=>7800,'stock'=>100,'min'=>10,'max'=>170],
            ['name'=>'Pocari Sweat 500ml','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>7000,'sell'=>10500,'whole'=>10000,'mem'=>9500,'stock'=>85,'min'=>10,'max'=>150],
            ['name'=>'Pocari Sweat 900ml','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>10000,'sell'=>15000,'whole'=>14000,'mem'=>13500,'stock'=>40,'min'=>5,'max'=>70],
            ['name'=>'Mizone Lychee 500ml','cat'=>2,'brand'=>6,'unit'=>7,'cost'=>4000,'sell'=>6500,'whole'=>6000,'mem'=>5500,'stock'=>110,'min'=>10,'max'=>180],
            ['name'=>'Mizone Lemon 500ml','cat'=>2,'brand'=>6,'unit'=>7,'cost'=>4000,'sell'=>6500,'whole'=>6000,'mem'=>5500,'stock'=>110,'min'=>10,'max'=>180],
            ['name'=>'Hydro Coco 500ml','cat'=>2,'brand'=>9,'unit'=>7,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>75,'min'=>5,'max'=>130],
            ['name'=>'Kratingdaeng 150ml','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>5000,'sell'=>8000,'whole'=>7500,'mem'=>7200,'stock'=>120,'min'=>10,'max'=>200],
            ['name'=>'Kuku Bima 150ml','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>130,'min'=>10,'max'=>210],
            ['name'=>'Extra Joss Susu 25g','cat'=>2,'brand'=>9,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>200,'min'=>20,'max'=>350],
            ['name'=>'Hemaviton Jreng 22g','cat'=>2,'brand'=>9,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>190,'min'=>20,'max'=>340],
            ['name'=>'You C1000 Orange 140ml','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>5000,'sell'=>7500,'whole'=>7000,'mem'=>6800,'stock'=>95,'min'=>10,'max'=>160],
            ['name'=>'You C1000 Lemon 140ml','cat'=>2,'brand'=>19,'unit'=>7,'cost'=>5000,'sell'=>7500,'whole'=>7000,'mem'=>6800,'stock'=>90,'min'=>10,'max'=>160],
            ['name'=>'Ale-Ale Anggur 180ml','cat'=>2,'brand'=>4,'unit'=>1,'cost'=>1000,'sell'=>2000,'whole'=>1800,'mem'=>1700,'stock'=>250,'min'=>25,'max'=>400],
            ['name'=>'Ale-Ale Melon 180ml','cat'=>2,'brand'=>4,'unit'=>1,'cost'=>1000,'sell'=>2000,'whole'=>1800,'mem'=>1700,'stock'=>240,'min'=>25,'max'=>400],
            ['name'=>'Okky Jelly Drink 150ml','cat'=>2,'brand'=>19,'unit'=>1,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>220,'min'=>20,'max'=>380],
            ['name'=>'Fruit Tea Apel 350ml','cat'=>2,'brand'=>7,'unit'=>7,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>130,'min'=>10,'max'=>210],
            ['name'=>'Fruit Tea Stroberi 350ml','cat'=>2,'brand'=>7,'unit'=>7,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>125,'min'=>10,'max'=>210],
            ['name'=>'Mountea 250ml','cat'=>2,'brand'=>4,'unit'=>7,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4200,'stock'=>140,'min'=>15,'max'=>240],
            ['name'=>'Larutan Cap Kaki 3 200ml','cat'=>2,'brand'=>4,'unit'=>7,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>115,'min'=>10,'max'=>190],
            ['name'=>'Adem Sari 7gr','cat'=>2,'brand'=>9,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>180,'min'=>20,'max'=>300],
            ['name'=>'Bear Brand 140ml','cat'=>2,'brand'=>5,'unit'=>8,'cost'=>7000,'sell'=>10500,'whole'=>10000,'mem'=>9500,'stock'=>80,'min'=>10,'max'=>140],
            ['name'=>'Milo 3in1 35gr','cat'=>2,'brand'=>5,'unit'=>6,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>150,'min'=>15,'max'=>250],
            ['name'=>'Nutrisari Jeruk 14gr','cat'=>2,'brand'=>5,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>200,'min'=>20,'max'=>350],
            ['name'=>'Nutrisari Mangga 14gr','cat'=>2,'brand'=>5,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>190,'min'=>20,'max'=>350],
            ['name'=>'Marimas Jeruk 8gr','cat'=>2,'brand'=>2,'unit'=>6,'cost'=>500,'sell'=>1000,'whole'=>900,'mem'=>800,'stock'=>500,'min'=>50,'max'=>800],
            ['name'=>'Jas Jus Mangga 6gr','cat'=>2,'brand'=>2,'unit'=>6,'cost'=>500,'sell'=>1000,'whole'=>900,'mem'=>800,'stock'=>480,'min'=>50,'max'=>800],
            ['name'=>'Pop Ice Coklat 25gr','cat'=>2,'brand'=>2,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>170,'min'=>15,'max'=>280],
            ['name'=>'Pop Ice Vanila 25gr','cat'=>2,'brand'=>2,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>165,'min'=>15,'max'=>280],
            ['name'=>'Good Day Cappuccino 25gr','cat'=>2,'brand'=>4,'unit'=>6,'cost'=>2000,'sell'=>3000,'whole'=>2800,'mem'=>2700,'stock'=>210,'min'=>20,'max'=>350],

            // ==================== CAT 3: MIE & BUMBU MASAK (50) ====================
            ['name'=>'Indomie Goreng 85gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>2200,'sell'=>3500,'whole'=>3200,'mem'=>3000,'stock'=>200,'min'=>20,'max'=>350],
            ['name'=>'Indomie Goreng Rendang 85gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>2200,'sell'=>3500,'whole'=>3200,'mem'=>3000,'stock'=>180,'min'=>20,'max'=>320],
            ['name'=>'Indomie Kuah Ayam Bawang 70gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>2000,'sell'=>3200,'whole'=>3000,'mem'=>2800,'stock'=>220,'min'=>20,'max'=>380],
            ['name'=>'Indomie Kuah Kari Ayam 72gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>2000,'sell'=>3200,'whole'=>3000,'mem'=>2800,'stock'=>210,'min'=>20,'max'=>360],
            ['name'=>'Indomie Kuah Soto 70gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>2000,'sell'=>3200,'whole'=>3000,'mem'=>2800,'stock'=>200,'min'=>20,'max'=>350],
            ['name'=>'Indomie Kuah Baso 69gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>2000,'sell'=>3200,'whole'=>3000,'mem'=>2800,'stock'=>190,'min'=>20,'max'=>340],
            ['name'=>'Indomie Empal Gentong 75gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>2200,'sell'=>3500,'whole'=>3200,'mem'=>3000,'stock'=>140,'min'=>15,'max'=>240],
            ['name'=>'Mie Sedap Goreng 90gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>2200,'sell'=>3500,'whole'=>3200,'mem'=>3000,'stock'=>195,'min'=>20,'max'=>340],
            ['name'=>'Mie Sedap Kuah Ayam 75gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>2000,'sell'=>3200,'whole'=>3000,'mem'=>2800,'stock'=>200,'min'=>20,'max'=>350],
            ['name'=>'Mie Sedap Kuah Soto 75gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>2000,'sell'=>3200,'whole'=>3000,'mem'=>2800,'stock'=>195,'min'=>20,'max'=>340],
            ['name'=>'Mie Sedap Kuah Baso 75gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>2000,'sell'=>3200,'whole'=>3000,'mem'=>2800,'stock'=>190,'min'=>20,'max'=>330],
            ['name'=>'Sarimi Isi 2 Goreng 115gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>3000,'sell'=>4500,'whole'=>4200,'mem'=>4000,'stock'=>130,'min'=>10,'max'=>220],
            ['name'=>'Supermi Goreng 80gr','cat'=>3,'brand'=>1,'unit'=>1,'cost'=>1800,'sell'=>3000,'whole'=>2800,'mem'=>2500,'stock'=>170,'min'=>15,'max'=>280],
            ['name'=>'Pop Mie Goreng 80gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>100,'min'=>10,'max'=>180],
            ['name'=>'Pop Mie Baso 75gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>95,'min'=>10,'max'=>170],
            ['name'=>'Royco Masako Ayam 9gr','cat'=>3,'brand'=>3,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>250,'min'=>20,'max'=>400],
            ['name'=>'Royco Masako Sapi 9gr','cat'=>3,'brand'=>3,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>240,'min'=>20,'max'=>400],
            ['name'=>'Masako Ayam 8gr','cat'=>3,'brand'=>22,'unit'=>6,'cost'=>1000,'sell'=>2000,'whole'=>1800,'mem'=>1800,'stock'=>300,'min'=>30,'max'=>500],
            ['name'=>'Masako Sapi 8gr','cat'=>3,'brand'=>22,'unit'=>6,'cost'=>1000,'sell'=>2000,'whole'=>1800,'mem'=>1800,'stock'=>290,'min'=>30,'max'=>500],
            ['name'=>'Maggi Kaldu Blok Ayam 6x10gr','cat'=>3,'brand'=>5,'unit'=>3,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>70,'min'=>5,'max'=>120],
            ['name'=>'Maggi Kaldu Blok Sapi 6x10gr','cat'=>3,'brand'=>5,'unit'=>3,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>65,'min'=>5,'max'=>120],
            ['name'=>'Sajiku Tepung Bumbu 80gr','cat'=>3,'brand'=>22,'unit'=>6,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4200,'stock'=>130,'min'=>10,'max'=>220],
            ['name'=>'Sajiku Tepung Bumbu Pedas 80gr','cat'=>3,'brand'=>22,'unit'=>6,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4200,'stock'=>125,'min'=>10,'max'=>220],
            ['name'=>'Sasa Tepung Bumbu 80gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4200,'stock'=>140,'min'=>10,'max'=>230],
            ['name'=>'Bamboe Nasi Goreng 45gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>110,'min'=>10,'max'=>190],
            ['name'=>'Bamboe Rendang 45gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>105,'min'=>10,'max'=>190],
            ['name'=>'Bamboe Soto Ayam 45gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>100,'min'=>10,'max'=>180],
            ['name'=>'Racik Nasi Goreng 45gr','cat'=>3,'brand'=>1,'unit'=>6,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>95,'min'=>10,'max'=>165],
            ['name'=>'Racik Rendang 45gr','cat'=>3,'brand'=>1,'unit'=>6,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>90,'min'=>10,'max'=>160],
            ['name'=>'Racik Ayam Goreng 45gr','cat'=>3,'brand'=>1,'unit'=>6,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>85,'min'=>10,'max'=>155],
            ['name'=>'Kara Santan 200ml','cat'=>3,'brand'=>2,'unit'=>7,'cost'=>8000,'sell'=>12000,'whole'=>11000,'mem'=>10500,'stock'=>55,'min'=>5,'max'=>90],
            ['name'=>'Sun Kara Santan 200ml','cat'=>3,'brand'=>2,'unit'=>7,'cost'=>7500,'sell'=>11000,'whole'=>10000,'mem'=>9500,'stock'=>60,'min'=>5,'max'=>100],
            ['name'=>'Sasa Santan Bubuk 20gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4200,'stock'=>95,'min'=>10,'max'=>160],
            ['name'=>'Tepung Terigu Segitiga Biru 1KG','cat'=>3,'brand'=>1,'unit'=>2,'cost'=>10000,'sell'=>13500,'whole'=>13000,'mem'=>12500,'stock'=>40,'min'=>5,'max'=>60],
            ['name'=>'Tepung Beras Rose Brand 500gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>7000,'sell'=>10000,'whole'=>9500,'mem'=>9000,'stock'=>35,'min'=>5,'max'=>55],
            ['name'=>'Tepung Tapioka Rose Brand 500gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>6500,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>38,'min'=>5,'max'=>60],
            ['name'=>'Tepung Ketan Rose Brand 500gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>8000,'sell'=>11000,'whole'=>10000,'mem'=>9500,'stock'=>30,'min'=>3,'max'=>50],
            ['name'=>'Tepung Maizena Maizenaku 150gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>4500,'sell'=>7000,'whole'=>6500,'mem'=>6000,'stock'=>55,'min'=>5,'max'=>90],
            ['name'=>'Garam Refina 500gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>3000,'sell'=>4500,'whole'=>4200,'mem'=>4000,'stock'=>70,'min'=>5,'max'=>120],
            ['name'=>'Garam Dolpin 500gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>2500,'sell'=>4000,'whole'=>3800,'mem'=>3500,'stock'=>75,'min'=>5,'max'=>130],
            ['name'=>'Micin Sasa 50gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>1000,'sell'=>2000,'whole'=>1800,'mem'=>1800,'stock'=>200,'min'=>20,'max'=>350],
            ['name'=>'Micin Ajinomoto 50gr','cat'=>3,'brand'=>22,'unit'=>6,'cost'=>1500,'sell'=>2500,'whole'=>2300,'mem'=>2200,'stock'=>180,'min'=>20,'max'=>320],
            ['name'=>'Gula Pasir Gulaku 1KG','cat'=>3,'brand'=>2,'unit'=>2,'cost'=>14000,'sell'=>18000,'whole'=>17000,'mem'=>16500,'stock'=>50,'min'=>5,'max'=>80],
            ['name'=>'Gula Merah Semut 250gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>45,'min'=>5,'max'=>75],
            ['name'=>'Tepung Panir Putih 200gr','cat'=>3,'brand'=>2,'unit'=>1,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>50,'min'=>5,'max'=>85],
            ['name'=>'Kobe Tepung Kentucky 75gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4200,'stock'=>120,'min'=>10,'max'=>200],
            ['name'=>'Mama Suka Tepung Bumbu Serbaguna 80gr','cat'=>3,'brand'=>15,'unit'=>6,'cost'=>2500,'sell'=>4000,'whole'=>3800,'mem'=>3500,'stock'=>110,'min'=>10,'max'=>190],
            ['name'=>'Indofood Bumbu Nasi Goreng Instan 20gr','cat'=>3,'brand'=>1,'unit'=>6,'cost'=>2000,'sell'=>3000,'whole'=>2800,'mem'=>2700,'stock'=>160,'min'=>15,'max'=>280],
            ['name'=>'Ladaku Merica Bubuk 6gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>1000,'sell'=>2000,'whole'=>1800,'mem'=>1800,'stock'=>220,'min'=>20,'max'=>380],
            ['name'=>'Ketumbar Bubuk Desaku 10gr','cat'=>3,'brand'=>2,'unit'=>6,'cost'=>1000,'sell'=>2000,'whole'=>1800,'mem'=>1800,'stock'=>180,'min'=>20,'max'=>320],

            // ==================== CAT 4: SABUN & PEMBERSIH (50) ====================
            ['name'=>'Lifebuoy Sabun Batang Merah 70gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>2500,'sell'=>4000,'whole'=>3800,'mem'=>3500,'stock'=>160,'min'=>15,'max'=>250],
            ['name'=>'Lifebuoy Sabun Batang Kuning 70gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>2500,'sell'=>4000,'whole'=>3800,'mem'=>3500,'stock'=>150,'min'=>15,'max'=>250],
            ['name'=>'Lifebuoy Sabun Batang Hijau 70gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>2500,'sell'=>4000,'whole'=>3800,'mem'=>3500,'stock'=>145,'min'=>15,'max'=>250],
            ['name'=>'Lux Sabun Batang Pink 80gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>3500,'sell'=>5000,'whole'=>4800,'mem'=>4500,'stock'=>120,'min'=>10,'max'=>200],
            ['name'=>'Lux Sabun Batang Putih 80gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>3500,'sell'=>5000,'whole'=>4800,'mem'=>4500,'stock'=>115,'min'=>10,'max'=>200],
            ['name'=>'Dettol Sabun Batang Original 65gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>90,'min'=>10,'max'=>160],
            ['name'=>'Dettol Sabun Batang Cool 65gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>85,'min'=>10,'max'=>160],
            ['name'=>'Nuvo Sabun Batang Family 80gr','cat'=>4,'brand'=>2,'unit'=>1,'cost'=>2200,'sell'=>3500,'whole'=>3200,'mem'=>3000,'stock'=>140,'min'=>15,'max'=>240],
            ['name'=>'Giv Sabun Batang White 80gr','cat'=>4,'brand'=>2,'unit'=>1,'cost'=>2000,'sell'=>3200,'whole'=>3000,'mem'=>2800,'stock'=>150,'min'=>15,'max'=>260],
            ['name'=>'Rinso Deterjen 770gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>12000,'sell'=>17000,'whole'=>16000,'mem'=>15000,'stock'=>40,'min'=>5,'max'=>60],
            ['name'=>'Rinso Deterjen 1.7KG','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>20000,'sell'=>28000,'whole'=>26500,'mem'=>25000,'stock'=>25,'min'=>3,'max'=>40],
            ['name'=>'So Klin Deterjen 770gr','cat'=>4,'brand'=>2,'unit'=>1,'cost'=>10000,'sell'=>14500,'whole'=>13500,'mem'=>13000,'stock'=>45,'min'=>5,'max'=>70],
            ['name'=>'Daia Deterjen 700gr','cat'=>4,'brand'=>2,'unit'=>1,'cost'=>8500,'sell'=>12500,'whole'=>11500,'mem'=>11000,'stock'=>50,'min'=>5,'max'=>80],
            ['name'=>'Attack Deterjen 800gr','cat'=>4,'brand'=>2,'unit'=>1,'cost'=>15000,'sell'=>21000,'whole'=>20000,'mem'=>19000,'stock'=>30,'min'=>3,'max'=>50],
            ['name'=>'Sunlight CIF 450ml','cat'=>4,'brand'=>3,'unit'=>7,'cost'=>8500,'sell'=>12500,'whole'=>11500,'mem'=>11000,'stock'=>55,'min'=>5,'max'=>80],
            ['name'=>'Sunlight CIF 750ml','cat'=>4,'brand'=>3,'unit'=>7,'cost'=>12000,'sell'=>17500,'whole'=>16500,'mem'=>15500,'stock'=>35,'min'=>5,'max'=>55],
            ['name'=>'Mama Lemon 400ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>6500,'sell'=>9500,'whole'=>9000,'mem'=>8500,'stock'=>60,'min'=>5,'max'=>100],
            ['name'=>'Mama Lemon 750ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>10000,'sell'=>15000,'whole'=>14000,'mem'=>13000,'stock'=>40,'min'=>5,'max'=>65],
            ['name'=>'So Klin Lantai 800ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>10000,'sell'=>14500,'whole'=>13500,'mem'=>13000,'stock'=>45,'min'=>5,'max'=>70],
            ['name'=>'So Klin Lantai 1600ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>16000,'sell'=>23000,'whole'=>21500,'mem'=>20500,'stock'=>25,'min'=>3,'max'=>40],
            ['name'=>'Super Pell 800ml','cat'=>4,'brand'=>3,'unit'=>7,'cost'=>10500,'sell'=>15000,'whole'=>14000,'mem'=>13500,'stock'=>40,'min'=>5,'max'=>65],
            ['name'=>'Wipol 450ml','cat'=>4,'brand'=>3,'unit'=>7,'cost'=>8000,'sell'=>11500,'whole'=>10800,'mem'=>10500,'stock'=>50,'min'=>5,'max'=>80],
            ['name'=>'SOS Pembersih Lantai 900ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>9000,'sell'=>13000,'whole'=>12000,'mem'=>11500,'stock'=>48,'min'=>5,'max'=>75],
            ['name'=>'SOS Pembersih Kamar Mandi 450ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>7000,'sell'=>10000,'whole'=>9500,'mem'=>9000,'stock'=>55,'min'=>5,'max'=>85],
            ['name'=>'Bayfresh Spray 300ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>8500,'sell'=>12500,'whole'=>11500,'mem'=>11000,'stock'=>50,'min'=>5,'max'=>80],
            ['name'=>'Stella Pewangi 225ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>4000,'sell'=>6500,'whole'=>6000,'mem'=>5500,'stock'=>85,'min'=>10,'max'=>140],
            ['name'=>'Stella Pewangi 750ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>10000,'sell'=>15000,'whole'=>14000,'mem'=>13000,'stock'=>40,'min'=>5,'max'=>65],
            ['name'=>'Glade Spray Lavender 300ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>14000,'sell'=>20000,'whole'=>19000,'mem'=>18000,'stock'=>30,'min'=>3,'max'=>50],
            ['name'=>'Pepsodent 75gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>5500,'sell'=>8500,'whole'=>8000,'mem'=>7500,'stock'=>95,'min'=>10,'max'=>150],
            ['name'=>'Pepsodent 120gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>8000,'sell'=>12000,'whole'=>11000,'mem'=>10500,'stock'=>65,'min'=>5,'max'=>110],
            ['name'=>'Pepsodent 190gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>12000,'sell'=>17500,'whole'=>16500,'mem'=>15500,'stock'=>40,'min'=>5,'max'=>70],
            ['name'=>'Close Up 75gr','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>85,'min'=>10,'max'=>140],
            ['name'=>'Ciptadent 75gr','cat'=>4,'brand'=>19,'unit'=>1,'cost'=>4000,'sell'=>6000,'whole'=>5500,'mem'=>5200,'stock'=>100,'min'=>10,'max'=>170],
            ['name'=>'Sikat Gigi Formula 1pc','cat'=>4,'brand'=>19,'unit'=>1,'cost'=>3500,'sell'=>5500,'whole'=>5000,'mem'=>4800,'stock'=>90,'min'=>10,'max'=>150],
            ['name'=>'Sikat Gigi Pepsodent 1pc','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>5000,'sell'=>7500,'whole'=>7000,'mem'=>6800,'stock'=>75,'min'=>5,'max'=>130],
            ['name'=>'Ekonomi Sabun Colet 585gr','cat'=>4,'brand'=>2,'unit'=>1,'cost'=>7000,'sell'=>10500,'whole'=>10000,'mem'=>9500,'stock'=>30,'min'=>5,'max'=>50],
            ['name'=>'Molto Pewangi 770ml','cat'=>4,'brand'=>3,'unit'=>7,'cost'=>9500,'sell'=>13500,'whole'=>12500,'mem'=>12000,'stock'=>42,'min'=>5,'max'=>68],
            ['name'=>'Wipol Cairan Pembersih 780ml','cat'=>4,'brand'=>3,'unit'=>7,'cost'=>11000,'sell'=>16000,'whole'=>15000,'mem'=>14200,'stock'=>38,'min'=>5,'max'=>62],
            ['name'=>'Bayfresh Gel Pengharum 10gr','cat'=>4,'brand'=>2,'unit'=>1,'cost'=>3000,'sell'=>5000,'whole'=>4500,'mem'=>4200,'stock'=>110,'min'=>10,'max'=>180],
            ['name'=>'Harpic Pembersih Kloset 450ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>9500,'sell'=>14000,'whole'=>13000,'mem'=>12500,'stock'=>35,'min'=>5,'max'=>58],
            ['name'=>'HIT Spray 200ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>12000,'sell'=>17500,'whole'=>16500,'mem'=>15500,'stock'=>40,'min'=>5,'max'=>65],
            ['name'=>'Baygon Spray 400ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>13500,'sell'=>19500,'whole'=>18500,'mem'=>17500,'stock'=>35,'min'=>5,'max'=>55],
            ['name'=>'Vixal Pembersih Keramik 250ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>7500,'sell'=>11000,'whole'=>10000,'mem'=>9500,'stock'=>45,'min'=>5,'max'=>75],
            ['name'=>'Mama Lime Pencuci Piring 400ml','cat'=>4,'brand'=>2,'unit'=>7,'cost'=>6500,'sell'=>9500,'whole'=>9000,'mem'=>8500,'stock'=>58,'min'=>5,'max'=>95],
            ['name'=>'Tisu Paseo 250 Sheet','cat'=>4,'brand'=>2,'unit'=>3,'cost'=>8000,'sell'=>12000,'whole'=>11000,'mem'=>10500,'stock'=>55,'min'=>5,'max'=>90],
            ['name'=>'Tisu Nice 250 Sheet','cat'=>4,'brand'=>2,'unit'=>3,'cost'=>6000,'sell'=>9000,'whole'=>8500,'mem'=>8000,'stock'=>65,'min'=>5,'max'=>105],
            ['name'=>'Tisu basah Mitu Baby 50 Sheet','cat'=>4,'brand'=>2,'unit'=>1,'cost'=>7000,'sell'=>10500,'whole'=>10000,'mem'=>9500,'stock'=>50,'min'=>5,'max'=>80],
            ['name'=>'Tisu basah Cussons Baby 50 Sheet','cat'=>4,'brand'=>3,'unit'=>1,'cost'=>8000,'sell'=>12000,'whole'=>11000,'mem'=>10500,'stock'=>45,'min'=>5,'max'=>75],
            ['name'=>'Sikat Kloset 1pc','cat'=>4,'brand'=>25,'unit'=>1,'cost'=>10000,'sell'=>15000,'whole'=>14000,'mem'=>13000,'stock'=>25,'min'=>3,'max'=>40],
            ['name'=>'Sapu Ijuk 1pc','cat'=>4,'brand'=>25,'unit'=>1,'cost'=>12000,'sell'=>18000,'whole'=>17000,'mem'=>16000,'stock'=>20,'min'=>3,'max'=>35],
        ];
    }

    // ================================================================
    // PRODUCT VARIANTS (dynamic - on selected products in range 201-1000)
    // ================================================================
    private function seedProductVariants(Carbon $now): void
    {
        // Choose products from the generated range to carry variants (flavors/sizes).
        $maxId = count($this->productIds);
        $candidates = range(201, max(201, $maxId));
        shuffle($candidates);
        $chosen = array_slice($candidates, 0, 40);
        sort($chosen);

        $this->variantProductIds = $chosen;
        if (!empty($chosen)) {
            DB::table('products')->whereIn('id', $chosen)->update(['has_variants' => true]);
        }

        $suffixes = ['Varian Kecil', 'Varian Besar', 'Varian Jumbo', 'Rasa Original', 'Rasa Pedas', 'Rasa Manis', 'Edisi Refill', 'Kemasan Ekonomis'];
        $factors = [0.85, 1.0, 1.15, 1.25];

        $rows = [];
        $variantId = 1;
        foreach ($chosen as $pid) {
            $prod = $this->products[$pid];
            $count = random_int(1, 3);
            $pool = $suffixes;
            shuffle($pool);
            $ids = [];

            for ($k = 0; $k < $count; $k++) {
                $suffix = $pool[$k];
                $factor = $factors[random_int(0, count($factors) - 1)];
                $sell = (int) (round(($prod['sell'] * $factor) / 500) * 500);
                $cost = (int) (round(($prod['cost'] * $factor) / 500) * 500);
                if ($sell < 500) { $sell = 500; }
                if ($cost < 500) { $cost = 500; }
                $stock = random_int($prod['min'], $prod['max']);
                $letter = chr(66 + $k); // B, C, D

                $rows[] = [
                    'product_id' => $pid,
                    'name' => $prod['name'] . ' - ' . $suffix,
                    'sku' => 'SKU' . str_pad((string) $pid, 5, '0', STR_PAD_LEFT) . $letter,
                    'barcode' => '890' . str_pad((string) $variantId, 10, '0', STR_PAD_LEFT),
                    'cost_price' => $cost,
                    'selling_price' => $sell,
                    'current_stock' => $stock,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $ids[] = $variantId;
                $this->variantSellById[$variantId] = $sell;
                $variantId++;
            }

            $this->variantIdsByProduct[$pid] = $ids;
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('product_variants')->insert($chunk);
        }
        $this->variantIds = range(1, count($rows));
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
            $phone = '08' . str_pad((string) random_int(100000000, 999999999), 10, '0', STR_PAD_LEFT);
            while (in_array($phone, $usedPhones)) {
                $phone = '08' . str_pad((string) random_int(100000000, 999999999), 10, '0', STR_PAD_LEFT);
            }
            $usedPhones[] = $phone;

            $email = strtolower(Str::slug($faker->name) . random_int(10, 99) . '@gmail.com');
            while (in_array($email, $usedEmails)) {
                $email = strtolower(Str::slug($faker->name) . random_int(10, 99) . '@gmail.com');
            }
            $usedEmails[] = $email;

            $groupId = random_int(1, 3);
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

            $orderNumber = 'INV-' . date('Ymd', $orderDate->timestamp) . '-' . str_pad((string) $orderId, 5, '0', STR_PAD_LEFT);

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

            $poNumber = 'PO-' . date('Ymd', $poDate->timestamp) . '-' . str_pad((string) $poId, 4, '0', STR_PAD_LEFT);

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

            $opnameNumber = 'OPN-' . date('Ymd', $opnameDate->timestamp) . '-' . str_pad((string) $opnameId, 3, '0', STR_PAD_LEFT);

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
            $transferNumber = 'STF-' . date('Ymd', $transferDate->timestamp) . '-' . str_pad((string) $transferId, 4, '0', STR_PAD_LEFT);

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

        if (empty($completedOrderIds)) {
            return;
        }

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
            $returnNumber = 'RTN-' . date('Ymd', $returnDate->timestamp) . '-' . str_pad((string) $returnId, 4, '0', STR_PAD_LEFT);

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
        if (!empty($returnItems)) {
            DB::table('return_items')->insert($returnItems);
        }
    }

    // ================================================================
    // INSTALLMENTS (for up to 4 orders)
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

        if (!empty($installments)) {
            DB::table('installments')->insert($installments);
        }
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

        if (!empty($payables)) {
            DB::table('supplier_payables')->insert($payables);
            DB::table('payable_payments')->insert($payments);
        }
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
    // TABLE AREAS
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

    // ================================================================
    // TABLES
    // ================================================================
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

    // ================================================================
    // RAW MATERIALS (5)
    // ================================================================
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

    // ================================================================
    // RECIPE ITEMS
    // ================================================================
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

    // ================================================================
    // DISCOUNT TEMPLATES (4)
    // ================================================================
    private function seedDiscountTemplates(Carbon $now): void
    {
        DB::table('discount_templates')->insert([
            ['name' => 'Diskon 10% Member', 'type' => 'percent', 'value' => 10, 'min_purchase' => 50000, 'buy_quantity' => null, 'get_quantity' => null, 'start_date' => '2026-06-01', 'end_date' => '2026-12-31', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Potongan 5rb', 'type' => 'fixed', 'value' => 5000, 'min_purchase' => 25000, 'buy_quantity' => null, 'get_quantity' => null, 'start_date' => '2026-06-01', 'end_date' => '2026-12-31', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Beli 3 Gratis 1', 'type' => 'buy_x_get_y', 'value' => 100, 'min_purchase' => 0, 'buy_quantity' => 3, 'get_quantity' => 1, 'start_date' => '2026-06-01', 'end_date' => '2026-07-31', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diskon Weekend 15%', 'type' => 'percent', 'value' => 15, 'min_purchase' => 100000, 'buy_quantity' => null, 'get_quantity' => null, 'start_date' => '2026-06-01', 'end_date' => '2026-08-31', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    // ================================================================
    // ATTENDANCES (6 days x 5 users)
    // ================================================================
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
        return in_array($productId, $this->variantProductIds, true);
    }

    private function getVariantIdsForProduct(int $productId): array
    {
        return $this->variantIdsByProduct[$productId] ?? [];
    }

    private function getVariantInfo(int $variantId): ?array
    {
        if (isset($this->variantSellById[$variantId])) {
            return ['sell' => $this->variantSellById[$variantId]];
        }
        return null;
    }

    // ================================================================
    // BLOG CATEGORIES
    // ================================================================
    private function seedBlogCategories(Carbon $now): void
    {
        $categories = [
            ['name' => 'Tips Bisnis Retail', 'slug' => 'tips-bisnis-retail', 'description' => 'Tips dan trik menjalankan bisnis retail yang sukses.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manajemen Toko', 'slug' => 'manajemen-toko', 'description' => 'Panduan mengelola operasional toko sehari-hari.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Strategi Penjualan', 'slug' => 'strategi-penjualan', 'description' => 'Strategi meningkatkan omzet dan loyalitas pelanggan.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Teknologi Retail', 'slug' => 'teknologi-retail', 'description' => 'Peran teknologi dalam modernisasi bisnis retail.', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('blog_categories')->insert($categories);
    }

    // ================================================================
    // BLOG POSTS (10 demo articles)
    // ================================================================
    private function seedBlogPosts(Carbon $now): void
    {
        $posts = [
            [
                'category_id' => 1, 'author_id' => 2, 'title' => '5 Strategi Mengelola Stok Barang Agar Tidak Kehabisan',
                'slug' => '5-strategi-mengelola-stok-barang',
                'excerpt' => 'Stok habis saat pelanggan datang bisa bikin kehilangan penjualan. Simak 5 strategi jitu mengelola inventori toko retail Anda.',
                'content' => '<p>Salah satu masalah terbesar dalam bisnis retail adalah manajemen stok. Stok yang terlalu sedikit membuat Anda kehilangan penjualan, sementara stok berlebih mengikat modal kerja.</p><h3>1. Terapkan Sistem Minimum-Maximum Stock</h3><p>Tentukan batas minimum (reorder point) dan maksimum untuk setiap produk. Ketika stok menyentuh batas minimum, segera lakukan pembelian ulang. Aplikasi POS Retail memiliki fitur low stock alert yang otomatis memberi notifikasi saat stok di bawah threshold.</p><h3>2. Analisis Fast-Moving vs Slow-Moving</h3><p>Kategorikan produk berdasarkan kecepatan perputaran. Produk fast-moving perlu stok lebih banyak dan frekuensi restock lebih sering. Produk slow-moving bisa dipesan dalam jumlah kecil atau bahkan di-discontinue.</p><h3>3. Lakukan Stock Opname Rutin</h3><p>Perbedaan antara stok sistem dan stok fisik adalah masalah umum. Lakukan stock opname berkala - harian untuk produk bernilai tinggi, mingguan untuk produk fast-moving, dan bulanan untuk sisanya.</p><h3>4. Gunakan Data Historis untuk Forecasting</h3><p>Jangan hanya mengandalkan feeling. Gunakan data penjualan historis untuk memprediksi kebutuhan stok, terutama menjelang musim ramai seperti Lebaran atau Tahun Baru.</p><h3>5. Integrasi Supplier</h3><p>Bangun hubungan baik dengan supplier dan pastikan lead time pengiriman jelas. Dengan POS Retail, Anda bisa langsung membuat Purchase Order dari sistem begitu stok menipis.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(30), 'meta_title' => '5 Strategi Mengelola Stok Barang - POS Retail', 'meta_description' => 'Pelajari 5 strategi jitu mengelola stok barang retail agar tidak kehabisan dan tidak overstock.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 1, 'author_id' => 2, 'title' => 'Cara Menentukan Harga Jual yang Menguntungkan',
                'slug' => 'cara-menentukan-harga-jual-menguntungkan',
                'excerpt' => 'Harga jual yang tepat adalah kunci profit. Pelajari rumus sederhana menentukan harga jual retail yang kompetitif namun tetap menguntungkan.',
                'content' => '<p>Menentukan harga jual bukan sekadar menaikkan harga beli sekian persen. Ada kalkulasi yang perlu diperhatikan agar bisnis tetap profit tanpa membuat pelanggan kabur.</p><h3>Rumus Dasar Markup</h3><p>Harga Jual = Harga Pokok + (Harga Pokok x Persentase Markup). Tapi markup tidak boleh sembarangan. Pertimbangkan: biaya operasional (sewa, listrik, gaji), biaya pemasaran, target margin, dan harga kompetitor.</p><h3>Multi-Tier Pricing</h3><p>POS Retail mendukung 3 level harga: eceran (harga normal), grosir (pembelian dalam jumlah besar), dan member (khusus pelanggan loyal). Ini memberi fleksibilitas untuk melayani berbagai segmen pelanggan.</p><h3>Monitor & Sesuaikan</h3><p>Harga bukan sesuatu yang statis. Pantau laporan penjualan, cek margin per produk, dan sesuaikan harga secara berkala berdasarkan data - bukan asumsi.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(25), 'meta_title' => 'Cara Menentukan Harga Jual yang Menguntungkan - POS Retail', 'meta_description' => 'Panduan lengkap menentukan harga jual retail dengan rumus markup, multi-tier pricing, dan strategi monitoring.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 2, 'author_id' => 3, 'title' => 'Checklist Buka Tutup Toko yang Wajib Dilakukan Setiap Hari',
                'slug' => 'checklist-buka-tutup-toko',
                'excerpt' => 'Disiplin operasional dimulai dari SOP buka dan tutup toko. Berikut checklist lengkap yang bisa Anda terapkan di bisnis retail.',
                'content' => '<h3>Checklist Buka Toko</h3><ol><li>Datang 30 menit sebelum jam buka</li><li>Nyalakan semua perangkat: komputer, printer struk, barcode scanner</li><li>Login ke aplikasi POS Retail dan buka shift</li><li>Hitung saldo kas awal (cash drawer opening balance)</li><li>Cek stok display - isi ulang yang kosong</li><li>Pastikan koneksi internet dan payment gateway aktif</li><li>Bersihkan area kasir dan display produk</li></ol><h3>Checklist Tutup Toko</h3><ol><li>Hitung fisik uang di cash drawer</li><li>Cocokkan dengan total transaksi di sistem</li><li>Catat selisih (jika ada)</li><li>Tutup shift di aplikasi POS Retail</li><li>Matikan semua perangkat</li><li>Kunci semua akses dan aktifkan alarm</li></ol><p>Dengan fitur Shift Management di POS Retail, semua aktivitas buka-tutup tercatat rapi dan bisa diaudit kapan saja.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(20), 'meta_title' => 'Checklist Buka Tutup Toko - POS Retail', 'meta_description' => 'Checklist lengkap SOP buka dan tutup toko retail untuk menjaga disiplin operasional.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 2, 'author_id' => 3, 'title' => 'Cara Rekrut dan Latih Kasir yang Handal',
                'slug' => 'cara-rekrut-dan-latih-kasir-handal',
                'excerpt' => 'Kasir adalah ujung tombak bisnis retail. Pelajari cara merekrut dan melatih kasir yang cepat, teliti, dan ramah pelanggan.',
                'content' => '<p>Kasir yang handal bukan hanya cepat dalam bertransaksi, tapi juga bisa menjadi aset dalam meningkatkan penjualan melalui upselling.</p><h3>Kriteria Rekrutmen Kasir</h3><p>Cari kandidat dengan: kejujuran tinggi, ketelitian, kemampuan berhitung cepat, komunikasi ramah, dan familiar dengan teknologi.</p><h3>Training 3 Hari</h3><p>Hari 1: Pengenalan produk dan layout toko. Hari 2: Operasional POS (scan, input manual, payment, refund). Hari 3: Handling customer + upselling technique + simulasi transaksi sibuk.</p><h3>Evaluasi Berkala</h3><p>Gunakan data dari POS Retail: rata-rata waktu transaksi, jumlah transaksi per shift, akurasi cash drawer. Kasir dengan performa terbaik bisa diberi insentif.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(18), 'meta_title' => 'Cara Rekrut dan Latih Kasir - POS Retail', 'meta_description' => 'Panduan merekrut dan melatih kasir retail yang handal, cepat, teliti, dan ramah.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 3, 'author_id' => 2, 'title' => 'Program Loyalitas Pelanggan: Investasi Kecil, Dampak Besar',
                'slug' => 'program-loyalitas-pelanggan-investasi-kecil',
                'excerpt' => 'Mempertahankan pelanggan lama 5x lebih murah daripada mencari pelanggan baru. Bangun program loyalitas yang efektif untuk bisnis retail Anda.',
                'content' => '<p>Fakta bisnis: meningkatkan retensi pelanggan sebesar 5% bisa meningkatkan profit hingga 25-95%. Itulah kenapa program loyalitas adalah investasi, bukan biaya.</p><h3>Model Point-Based Loyalty</h3><p>Pelanggan mendapat poin setiap bertransaksi. Contoh: setiap Rp 10.000 pembelanjaan = 1 poin. 100 poin bisa ditukar diskon Rp 10.000. POS Retail menghitung loyalty points otomatis per transaksi - tanpa input manual.</p><h3>Membership Tier</h3><p>Buat jenjang keanggotaan: Silver (0-500rb/bulan), Gold (500rb-2jt/bulan), Platinum (>2jt/bulan). Setiap tier dapat benefit berbeda: diskon tambahan, gratis ongkir, akses early sale, dll. POS Retail support 3 tier membership out of the box.</p><h3>Promo Tematik</h3><p>Gunakan Discount Template untuk membuat promo tematik: diskon 20% produk tertentu, buy 1 get 1, diskon jam tertentu (happy hour), dll. Variasikan promo agar pelanggan tidak bosan.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(15), 'meta_title' => 'Program Loyalitas Pelanggan - POS Retail', 'meta_description' => 'Bangun program loyalitas dengan point system, membership tier, dan discount template di POS Retail.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 3, 'author_id' => 2, 'title' => 'Upselling dan Cross-selling: Tingkatkan Omzet Tanpa Tambah Pelanggan',
                'slug' => 'upselling-cross-selling-tingkatkan-omzet',
                'excerpt' => 'Teknik upselling dan cross-selling bisa meningkatkan nilai transaksi rata-rata 20-40%. Pelajari cara menerapkannya di toko retail Anda.',
                'content' => '<p>Upselling = menawarkan versi lebih mahal dari produk yang sama. Cross-selling = menawarkan produk komplementer. Keduanya adalah cara termurah meningkatkan omzet.</p><h3>Contoh Up-Selling</h3><p>Pelanggan beli beras 5kg -> tawarkan beras premium 5kg yang lebih pulen. Pelanggan beli deterjen 500ml -> tawarkan yang 1L (lebih hemat per ml).</p><h3>Contoh Cross-Selling</h3><p>Pelanggan beli Indomie -> tawarkan telur + sawi. Pelanggan beli kopi sachet -> tawarkan gula + creamer. Pelanggan beli sabun mandi -> tawarkan sikat gigi + pasta gigi.</p><h3>Pakai Data</h3><p>POS Retail mencatat history transaksi. Gunakan data untuk tahu produk apa yang sering dibeli bersamaan (market basket analysis). Latih kasir untuk merekomendasikan based on data - bukan asal tawarkan.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(12), 'meta_title' => 'Upselling & Cross-selling Retail - POS Retail', 'meta_description' => 'Teknik upselling dan cross-selling untuk meningkatkan omzet toko retail tanpa tambah pelanggan baru.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 1, 'author_id' => 3, 'title' => 'Laporan Keuangan untuk Pemilik Toko: Yang Perlu Anda Pantau',
                'slug' => 'laporan-keuangan-untuk-pemilik-toko',
                'excerpt' => 'Tidak perlu jadi akuntan untuk membaca laporan keuangan. Ini 3 laporan penting yang wajib dipantau pemilik toko retail setiap bulan.',
                'content' => '<h3>1. Laporan Penjualan Harian</h3><p>Pantau: total omzet, jumlah transaksi, rata-rata nilai transaksi, dan top 10 produk. Jika ada anomali (misal omzet turun drastis di hari biasa), segera investigasi.</p><h3>2. Laporan Laba Rugi (P&L)</h3><p>Pendapatan - Harga Pokok Penjualan = Laba Kotor. Laba Kotor - Biaya Operasional = Laba Bersih. POS Retail generate P&L otomatis dengan filter tanggal dan outlet.</p><h3>3. Laporan Stok & Inventory Value</h3><p>Nilai total stok yang Anda pegang = modal yang tertahan. Pantau inventory turnover ratio: semakin cepat stok berputar, semakin efisien penggunaan modal. POS Retail menyediakan laporan stok lengkap dengan pergerakan per produk.</p><h3>Tips: Review Rutin</h3><p>Jadwalkan review laporan setiap minggu (Senin pagi) bersama manager toko. 30 menit cukup untuk baca 3 laporan di atas dan ambil keputusan cepat.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(10), 'meta_title' => 'Laporan Keuangan Pemilik Toko - POS Retail', 'meta_description' => 'Tiga laporan keuangan penting yang wajib dipantau pemilik toko retail: penjualan, P&L, dan stok.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 4, 'author_id' => 2, 'title' => 'POS Berbasis Cloud vs Lokal: Mana yang Cocok untuk Bisnis Anda?',
                'slug' => 'pos-cloud-vs-lokal',
                'excerpt' => 'Bingung pilih POS berbasis cloud atau on-premise? Bandingkan kelebihan dan kekurangan keduanya untuk bisnis retail Anda.',
                'content' => '<h3>POS Cloud (Online)</h3><p><strong>Kelebihan:</strong> Akses dari mana saja, data real-time multi-cabang, update otomatis, tidak perlu server sendiri, biaya awal rendah (subscription).<br><strong>Kekurangan:</strong> Ketergantungan internet, biaya bulanan, data di server pihak ketiga.</p><h3>POS On-Premise / Self-Hosted</h3><p><strong>Kelebihan:</strong> Data di server sendiri (privasi), tidak ada biaya langganan setelah beli, tetap bisa transaksi tanpa internet (offline mode).<br><strong>Kekurangan:</strong> Perlu maintain server sendiri, update manual, butuh tenaga IT.</p><h3>POS Retail: The Best of Both Worlds</h3><p>POS Retail adalah aplikasi self-hosted yang bisa Anda install di server sendiri (VPS, dedicated, atau bahkan laptop untuk single store). Anda punya kontrol penuh atas data. Support multi-outlet dengan sinkronisasi real-time antar cabang. Tidak ada biaya bulanan - beli source code sekali, pakai selamanya.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(8), 'meta_title' => 'POS Cloud vs Lokal - POS Retail', 'meta_description' => 'Perbandingan POS cloud vs on-premise untuk bisnis retail Indonesia.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 4, 'author_id' => 3, 'title' => 'Integrasi Payment Gateway: Kenapa Toko Modern Wajib Punya',
                'slug' => 'integrasi-payment-gateway',
                'excerpt' => 'QRIS, GoPay, OVO, kartu kredit - pelanggan sekarang ingin bayar dengan cara mereka. Pelajari cara integrasi payment gateway di toko retail.',
                'content' => '<p>2026: lebih dari 70% transaksi ritel di Indonesia melibatkan pembayaran non-tunai. Toko yang hanya terima cash akan kehilangan pelanggan.</p><h3>Kenapa Harus Multi-Payment?</h3><ol><li>Pelanggan tidak bawa uang tunai cukup</li><li>Transaksi lebih cepat (tap/scan vs hitung uang)</li><li>Mengurangi risiko uang palsu</li><li>Otomatis tercatat - tidak ada selisih kas</li><li>Data pembayaran untuk analisis customer behavior</li></ol><h3>POS Retail: Dynamic Provider System</h3><p>POS Retail tidak mengunci Anda ke satu payment gateway. Sistem provider dinamis memungkinkan Anda menambahkan Midtrans, Xendit, Duitku, atau gateway lain via admin panel. Tambah API key, pilih environment (sandbox/production), dan langsung bisa dipakai. Bahkan bisa multiple provider sekaligus - customer tinggal pilih metode bayar favoritnya.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(5), 'meta_title' => 'Integrasi Payment Gateway Retail - POS Retail', 'meta_description' => 'Kenapa toko modern wajib punya payment gateway: QRIS, e-wallet, kartu kredit. Integrasi mudah di POS Retail.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'category_id' => 2, 'author_id' => 2, 'title' => 'Anti-Fraud di Toko Retail: Cegah Kecurangan Kasir dan Karyawan',
                'slug' => 'anti-fraud-toko-retail',
                'excerpt' => 'Kecurangan internal bisa menggerogoti profit tanpa disadari. Terapkan 7 langkah anti-fraud untuk melindungi bisnis retail Anda.',
                'content' => '<h3>Jenis Kecurangan di Retail</h3><ol><li>Kasir tidak memasukkan transaksi (no-sale fraud)</li><li>Diskon tidak sah / markdown fiktif</li><li>Markup harga dan ambil selisihnya</li><li>Fake return/refund</li><li>Kolusi kasir + customer (bagi-bagi diskon)</li><li>Pencurian stok gudang</li></ol><h3>7 Langkah Anti-Fraud</h3><ol><li><strong>Setiap user login sendiri</strong> - jangan sharing akun kasir</li><li><strong>Approval threshold</strong> - transaksi di atas Rp 5jt wajib approval manager</li><li><strong>Cash drawer reconciliation</strong> - cocokkan fisik vs sistem setiap shift tutup</li><li><strong>Audit log otomatis</strong> - POS Retail mencatat setiap create/update/delete data lengkap dengan user ID dan timestamp</li><li><strong>Role-based access</strong> - kasir tidak bisa akses laporan keuangan atau edit produk</li><li><strong>Stock opname random</strong> - jangan terjadwal, biar gudang tidak bisa "siap-siap"</li><li><strong>Review CCTV + data transaksi</strong> - cross-check transaksi mencurigakan dengan rekaman</li></ol><p>POS Retail menyediakan audit trail lengkap dan role-based access control. Kecurangan jadi lebih sulit dilakukan dan lebih mudah dideteksi.</p>',
                'is_published' => true, 'published_at' => $now->copy()->subDays(3), 'meta_title' => 'Anti-Fraud Toko Retail - POS Retail', 'meta_description' => '7 langkah mencegah kecurangan kasir dan karyawan di toko retail dengan audit trail dan role-based access.',
                'created_at' => $now, 'updated_at' => $now,
            ],
        ];
        DB::table('blog_posts')->insert($posts);
    }
}
