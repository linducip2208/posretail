# Arsitektur Sistem POS Retail

Sistem Point-of-Sale Retail lengkap: **Laravel 13 + Filament 5.6 + Blade + Tailwind (Admin)** + **Flutter (Android Kasir)** + **MySQL** + **Sanctum API**.

---

## Daftar Isi

1. [System Overview](#1-system-overview)
2. [Backend Architecture (Laravel 13)](#2-backend-architecture-laravel-13)
3. [Frontend Admin (Filament 5.6)](#3-frontend-admin-filament-56)
4. [Mobile App (Flutter)](#4-mobile-app-flutter)
5. [API Design (Sanctum)](#5-api-design-sanctum)
6. [Database (MySQL)](#6-database-mysql)
7. [Security](#7-security)
8. [Deployment](#8-deployment)

---

## 1. System Overview

### Diagram Arsitektur High-Level

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           POS RETAIL SYSTEM                                  │
│                       Arsitektur High-Level (v1.0)                           │
└─────────────────────────────────────────────────────────────────────────────┘

                              ┌──────────────────┐
                              │   INTERNET / LAN  │
                              │   (HTTPS / HTTP)  │
                              └────────┬─────────┘
                                       │
                 ┌─────────────────────┼─────────────────────┐
                 │                     │                     │
                 ▼                     ▼                     ▼
┌────────────────────┐   ┌────────────────────┐   ┌────────────────────┐
│                    │   │                    │   │                    │
│   FLUTTER APP      │   │  FILAMENT ADMIN    │   │  CUSTOMER PORTAL  │
│   (Android Kasir)  │   │  (Browser Desktop) │   │  (Browser Web)    │
│                    │   │                    │   │                    │
│ • Provider/Riverpod│   │ • Blade + Tailwind │   │ • Blade + Tailwind│
│ • SQLite offline   │   │ • Livewire 3       │   │ • Bootstrap/Tailwind│
│ • Thermal print    │   │ • Chart.js         │   │ • Public access    │
│ • Barcode scanner  │   │ • Custom theme     │   │                    │
│                    │   │                    │   │                    │
└─────────┬──────────┘   └─────────┬──────────┘   └─────────┬──────────┘
          │                        │                         │
          │ Sanctum Token          │ Sanctum SPA Cookie      │ Sanctum SPA Cookie
          │ (API Bearer)           │ (Admin Session)         │ (Portal Session)
          │                        │                         │
          └────────────────────────┼─────────────────────────┘
                                   │
                                   ▼
              ┌────────────────────────────────────────┐
              │                                        │
              │         LARAVEL 13 BACKEND              │
              │         (REST API + Web)                │
              │                                        │
              │  ┌──────────────────────────────────┐  │
              │  │        MIDDLEWARE LAYER          │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Sanctum  │ │ Role Middleware│  │  │
              │  │  │ Auth     │ │ (admin,kasir, │  │  │
              │  │  │          │ │  owner,staff)  │  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Throttle │ │ Audit Logger  │  │  │
              │  │  │ (60/min) │ │ (all changes) │  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  └──────────────────────────────────┘  │
              │                                        │
              │  ┌──────────────────────────────────┐  │
              │  │       CONTROLLER LAYER           │  │
              │  │  ┌─────────┐ ┌────────────────┐  │  │
              │  │  │ API v1  │ │ Web Controllers│  │  │
              │  │  │ Controllers│ │ (Filament/    │  │  │
              │  │  │          │ │  Portal/Views) │  │  │
              │  │  └─────────┘ └────────────────┘  │  │
              │  └──────────────────────────────────┘  │
              │                                        │
              │  ┌──────────────────────────────────┐  │
              │  │       SERVICE LAYER              │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Order    │ │ StockService  │  │  │
              │  │  │ Service  │ │ (in/out/opname)│  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Payment  │ │ PricingService│  │  │
              │  │  │ Service  │ │ (discount/tax)│  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Receipt  │ │ ReportService │  │  │
              │  │  │ Service  │ │ (P&L, sales)  │  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Sync     │ │ LoyaltyService│  │  │
              │  │  │ Service  │ │ (poin/member) │  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  └──────────────────────────────────┘  │
              │                                        │
              │  ┌──────────────────────────────────┐  │
              │  │       EVENT / LISTENER LAYER     │  │
              │  │  ┌────────────┐ ┌─────────────┐  │  │
              │  │  │ StockMove  │ │ OrderCreated │  │  │
              │  │  │ Event      │ │ Event        │  │  │
              │  │  └────────────┘ └─────────────┘  │  │
              │  │  ┌────────────┐ ┌─────────────┐  │  │
              │  │  │ Payment    │ │ LowStock     │  │  │
              │  │  │ Received   │ │ Alert Event  │  │  │
              │  │  └────────────┘ └─────────────┘  │  │
              │  └──────────────────────────────────┘  │
              │                                        │
              │  ┌──────────────────────────────────┐  │
              │  │        QUEUE / JOB LAYER         │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Send     │ │ GeneratePDF   │  │  │
              │  │  │ Notif    │ │ Job           │  │  │
              │  │  │ Job      │ │ (receipt/inv) │  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ SyncData │ │ ExportExcel   │  │  │
              │  │  │ Job      │ │ Job (laporan) │  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  └──────────────────────────────────┘  │
              │                                        │
              │  ┌──────────────────────────────────┐  │
              │  │          SCHEDULER               │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Cek Over │ │ Backup DB     │  │  │
              │  │  │ Due (1h) │ │ (daily 02:00) │  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  │  ┌──────────┐ ┌───────────────┐  │  │
              │  │  │ Kirim    │ │ Stok Habis    │  │  │
              │  │  │ Reminder │ │ Alert (1h)    │  │  │
              │  │  │ (daily)  │ │               │  │  │
              │  │  └──────────┘ └───────────────┘  │  │
              │  └──────────────────────────────────┘  │
              │                                        │
              └───────────────────┬────────────────────┘
                                  │
                                  │ PDO / Eloquent
                                  │ (Connection Pool)
                                  │
                                  ▼
              ┌────────────────────────────────────────┐
              │                                        │
              │              MYSQL 8.x                 │
              │         (Primary + Read Replica)       │
              │                                        │
              │  ┌──────────────────────────────────┐  │
              │  │  Data Operasional                │  │
              │  │  • products, categories, brands  │  │
              │  │  • customers, members            │  │
              │  │  • stores, outlets               │  │
              │  │  • suppliers                     │  │
              │  └──────────────────────────────────┘  │
              │  ┌──────────────────────────────────┐  │
              │  │  Data Transaksional              │  │
              │  │  • orders, order_items           │  │
              │  │  • payments, payment_proofs      │  │
              │  │  • stock_movements               │  │
              │  │  • invoices, receipts            │  │
              │  └──────────────────────────────────┘  │
              │  ┌──────────────────────────────────┐  │
              │  │  Data Sistem                     │  │
              │  │  • users, roles, permissions     │  │
              │  │  • audit_logs, login_logs        │  │
              │  │  • settings, providers           │  │
              │  │  • notifications, jobs           │  │
              │  └──────────────────────────────────┘  │
              │  ┌──────────────────────────────────┐  │
              │  │  Data Offline Sync               │  │
              │  │  • sync_logs                     │  │
              │  │  • local_transactions (temp)     │  │
              │  └──────────────────────────────────┘  │
              │                                        │
              └────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────┐
│                          INFRASTRUCTURE STACK                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐     │
│  │ Nginx    │  │ PHP-FPM  │  │ MySQL    │  │ Redis    │  │ Laravel  │     │
│  │ (Web)    │  │ 8.3/8.4  │  │ 8.x      │  │ (Cache/  │  │ Forge/   │     │
│  │          │  │          │  │          │  │  Queue)  │  │ VPS      │     │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘     │
│       │             │             │             │             │            │
│  ┌────┴─────────────┴─────────────┴─────────────┴─────────────┴────┐      │
│  │                      Supervisor (Process Manager)                 │      │
│  │  • horizon / queue:work (8 workers)                               │      │
│  │  • scheduler: cron (every minute)                                 │      │
│  │  • mysql-backup: daily cron                                       │      │
│  └──────────────────────────────────────────────────────────────────┘      │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Ringkasan Alur Data

| Client | Auth | Protocol | Data Flow |
|--------|------|----------|-----------|
| Flutter App (Kasir) | Sanctum Token (Bearer) | HTTPS REST JSON | Mobile → API → MySQL |
| Filament Admin | Sanctum SPA Cookie | HTTPS + Livewire | Browser → Laravel → MySQL |
| Customer Portal | Sanctum SPA Cookie | HTTPS | Browser → Laravel → MySQL |

### Port Default

| Service | Port | Deskripsi |
|---------|------|-----------|
| Nginx HTTP | 80 | Redirect ke HTTPS |
| Nginx HTTPS | 443 | SSL/TLS Termination |
| PHP-FPM | 9000 | FastCGI Process Manager |
| MySQL | 3306 | Database Primary |
| Redis | 6379 | Cache & Queue Driver |
| Laravel Horizon | — | Queue Monitor (via web) |

---

## 2. Backend Architecture (Laravel 13)

### 2.1 Struktur Folder

```
laravel/
├── app/
│   ├── Console/
│   │   └── Commands/                 # Artisan commands
│   │       ├── BackupDatabase.php    # mysqldump daily
│   │       ├── EscalateOverdueOrders.php
│   │       ├── SendReminders.php
│   │       ├── CheckLowStock.php
│   │       └── SendNotifications.php
│   │
│   ├── Enums/                        # PHP 8.4 backed enums
│   │   ├── OrderStatus.php
│   │   ├── PaymentStatus.php
│   │   ├── StockMovementType.php
│   │   └── UserRole.php
│   │
│   ├── Events/                       # Event classes
│   │   ├── OrderCreated.php
│   │   ├── PaymentReceived.php
│   │   ├── StockMovementCreated.php
│   │   └── LowStockAlert.php
│   │
│   ├── Filament/                     # Admin panel (Filament 5.6)
│   │   ├── Pages/
│   │   │   ├── Dashboard.php
│   │   │   ├── LaporanPenjualan.php
│   │   │   ├── LaporanKeuangan.php
│   │   │   └── LaporanStok.php
│   │   ├── Resources/
│   │   │   ├── ProductResource.php
│   │   │   ├── OrderResource.php
│   │   │   ├── CustomerResource.php
│   │   │   └── ... (20+ resources)
│   │   ├── Widgets/
│   │   │   ├── DashboardWidgetFilter.php  # Trait per-role
│   │   │   ├── StatsOverview.php
│   │   │   ├── SalesChart.php
│   │   │   ├── CashierTodayWidget.php
│   │   │   ├── LowStockAlertWidget.php
│   │   │   └── PendingOrdersWidget.php
│   │   └── Exports/
│   │       ├── OrderExporter.php
│   │       └── ProductExporter.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── V1/
│   │   │   │       ├── AuthController.php
│   │   │   │       ├── ProductController.php
│   │   │   │       ├── OrderController.php
│   │   │   │       ├── PaymentController.php
│   │   │   │       └── SyncController.php
│   │   │   ├── Portal/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   └── OrderController.php
│   │   │   ├── DocsController.php
│   │   │   ├── SitemapController.php
│   │   │   └── ProgrammaticSeoController.php
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php
│   │   │   ├── AuditLogMiddleware.php
│   │   │   └── ForceJsonResponse.php
│   │   └── Requests/
│   │       ├── Api/
│   │       │   └── V1/
│   │       │       ├── CreateOrderRequest.php
│   │       │       └── SyncDataRequest.php
│   │       └── Web/
│   │           └── PortalLoginRequest.php
│   │
│   ├── Jobs/
│   │   ├── SendNotificationJob.php
│   │   ├── GenerateReceiptPdfJob.php
│   │   ├── SyncOfflineDataJob.php
│   │   └── ExportReportJob.php
│   │
│   ├── Listeners/
│   │   ├── UpdateStockOnOrderCreated.php
│   │   ├── SendLowStockNotification.php
│   │   ├── LogStockMovement.php
│   │   └── UpdateCustomerLoyalty.php
│   │
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Brand.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Payment.php
│   │   ├── Customer.php
│   │   ├── Store.php
│   │   ├── StockMovement.php
│   │   ├── User.php
│   │   ├── AuditLog.php
│   │   └── Setting.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AdminPanelProvider.php      # Filament config
│   │   ├── EventServiceProvider.php
│   │   └── RouteServiceProvider.php
│   │
│   └── Services/
│       ├── OrderService.php
│       ├── StockService.php
│       ├── PaymentService.php
│       ├── PricingService.php
│       ├── ReceiptService.php
│       ├── SyncService.php
│       ├── ReportService.php
│       ├── LoyaltyService.php
│       ├── NotificationService.php
│       ├── AuditService.php
│       └── LicenseClient.php           # License v3 (jika WHM)
│
├── bootstrap/
│   ├── app.php                         # Application bootstrap
│   └── providers.php                   # Provider registration
│
├── config/
│   ├── app.php                         # APP_NAME, APP_URL, timezone
│   ├── auth.php                        # Guards (web, api, customer)
│   ├── database.php                    # MySQL connection pool
│   ├── filament.php                    # Admin panel config
│   ├── sanctum.php                     # API auth config
│   ├── queue.php                       # Redis queue config
│   └── ...
│
├── database/
│   ├── migrations/                     # Schema migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_05_30_000001_create_products_table.php
│   │   ├── 2026_05_30_000002_create_orders_table.php
│   │   └── ...
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── DemoDataSeeder.php
│   │   ├── ProductSeeder.php
│   │   └── UserSeeder.php
│   └── factories/
│       ├── ProductFactory.php
│       └── OrderFactory.php
│
├── resources/
│   ├── css/
│   │   ├── app.css                     # Public CSS
│   │   └── filament/
│   │       └── admin/
│   │           └── theme.css           # Custom Filament theme
│   ├── views/
│   │   ├── marketing.blade.php         # Landing page
│   │   ├── vendor/
│   │   │   └── filament-panels/
│   │   │       └── components/
│   │   │           └── layout/
│   │   │               └── simple.blade.php  # Clean login
│   │   ├── pseo/
│   │   │   ├── _layout.blade.php       # SEO layout
│   │   │   └── docs-index.blade.php    # /docs page
│   │   ├── portal/
│   │   │   ├── layout.blade.php
│   │   │   ├── dashboard.blade.php
│   │   │   └── orders/
│   │   │       ├── index.blade.php
│   │   │       └── show.blade.php
│   │   └── pdf/
│   │       ├── receipt.blade.php
│   │       └── invoice.blade.php
│   └── js/
│       └── app.js
│
├── routes/
│   ├── web.php                         # Web + Filament + Portal
│   ├── api.php                         # API v1 (Sanctum token)
│   ├── console.php                     # Scheduler
│   └── pseo.php                        # Programmatic SEO routes
│
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── marketing/
│   │   │   │   └── screens/            # Screenshots untuk landing
│   │   │   └── receipts/               # PDF receipts
│   │   ├── backups/                    # MySQL dump
│   │   └── exports/                    # Excel/CSV exports
│   ├── logs/
│   │   └── laravel.log
│   └── framework/
│       └── cache/
│
├── tests/
│   ├── Feature/
│   │   ├── OrderApiTest.php
│   │   ├── OrderFlowTest.php
│   │   ├── SyncTest.php
│   │   └── PortalTest.php
│   └── Unit/
│       ├── PricingServiceTest.php
│       ├── StockServiceTest.php
│       └── LoyaltyServiceTest.php
│
├── deploy/
│   ├── nginx.conf                      # Nginx config template
│   └── supervisor.conf                 # Supervisor config template
│
├── scripts/
│   ├── screenshot.cjs                  # Desktop screenshots (Playwright)
│   └── screenshot-mobile.cjs           # Mobile screenshots (iPhone)
│
├── public/
│   ├── index.php                       # Entry point
│   ├── robots.txt                      # Crawl rules
│   └── build/                          # Vite compiled assets
│
├── .env.example                        # Environment template
├── DEPLOYMENT.md                       # Deployment guide
├── composer.json                       # PHP dependencies
├── package.json                        # Node dependencies
├── vite.config.js                      # Vite bundler config
└── artisan                             # CLI entrypoint
```

### 2.2 Service Layer Pattern

Setiap modul bisnis memiliki satu `Service` class yang menangani logika inti. Controller **tidak pernah** berisi business logic langsung — hanya menerima input, memanggil service, dan return response.

```
Request → Controller → FormRequest (validate)
                         ↓
                     Service Layer
                         ↓
                  Model (Eloquent)
                         ↓
                        DB
```

**Contoh struktur service:**

```php
// app/Services/OrderService.php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected StockService $stockService,
        protected PricingService $pricingService,
    ) {}

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Validasi stok
            // 2. Hitung harga (diskon, pajak) via PricingService
            $data = $this->pricingService->calculate($data);

            // 3. Buat order
            $order = Order::create([...]);
            foreach ($data['items'] as $item) {
                OrderItem::create([...]);

                // 4. Kurangi stok via StockService
                $this->stockService->decrease($item['product_id'], $item['qty']);
            }

            // 5. Fire event
            event(new OrderCreated($order));

            return $order->fresh(['items.product', 'customer']);
        });
    }
}
```

### 2.3 Repository Pattern

Tidak digunakan secara penuh. Eloquent Model berfungsi sebagai Active Record + Repository. Query kompleks di-encapsulate dalam **query scope** pada Model atau method static pada Service.

Untuk query reporting kompleks, gunakan **Query Builder** langsung di Service layer:

```php
// Di ReportService.php
public function salesByCategory(DateRange $range): Collection
{
    return DB::table('order_items')
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->whereBetween('orders.created_at', [$range->from, $range->to])
        ->where('orders.status', OrderStatus::COMPLETED)
        ->select('products.category_id', DB::raw('SUM(order_items.qty) as total_qty'))
        ->groupBy('products.category_id')
        ->get();
}
```

### 2.4 Event / Listener untuk Stock Movement

Setiap perubahan stok di-fire sebagai event untuk audit trail dan notifikasi.

```
┌────────────────────┐
│  StockMovement     │
│  Created (DB)      │
└────────┬───────────┘
         │
         ▼
┌────────────────────┐      ┌──────────────────────────┐
│ StockMovementCreated│─────▶│ UpdateStockSummary       │
│ Event              │      │ Listener                  │
└────────┬───────────┘      │ (cache invalidation)      │
         │                  └──────────────────────────┘
         │                  ┌──────────────────────────┐
         ├─────────────────▶│ LogStockMovement          │
         │                  │ Listener (audit_logs)     │
         │                  └──────────────────────────┘
         │                  ┌──────────────────────────┐
         ├─────────────────▶│ CheckLowStockThreshold    │
         │                  │ Listener                  │
         │                  └──────────┬───────────────┘
         │                             │ (jika stok < min)
         │                             ▼
         │                  ┌──────────────────────────┐
         └─────────────────▶│ LowStockAlert Event       │
                            │ → SendLowStockNotification│
                            │   Listener (Job dispatch) │
                            └──────────────────────────┘
```

**Register di `EventServiceProvider.php`:**

```php
protected $listen = [
    StockMovementCreated::class => [
        UpdateStockSummary::class,
        LogStockMovement::class,
        CheckLowStockThreshold::class,
    ],
    OrderCreated::class => [
        UpdateStockOnOrder::class,
        SendOrderNotification::class,
    ],
    LowStockAlert::class => [
        SendLowStockNotification::class,
    ],
];
```

### 2.5 Queue / Jobs untuk Notifikasi

Semua notifikasi (WhatsApp, Email, Push) dikirim via Queue agar tidak blocking request.

```
┌──────────┐      ┌──────────────┐      ┌──────────────┐
│ Event    │─────▶│ Job           │─────▶│ Notification │
│ Fired    │      │ Dispatched   │      │ Service      │
└──────────┘      │ to Redis     │      │ (Adapter)    │
                  └──────┬───────┘      └──────┬───────┘
                         │                     │
                         ▼                     ▼
                  ┌──────────────┐      ┌──────────────┐
                  │ Queue Worker │      │ Provider      │
                  │ (Horizon)    │      │ (WhatsApp API,│
                  │              │      │  Mailgun, FCM)│
                  └──────────────┘      └──────────────┘
```

**Job contoh:**

```php
// app/Jobs/SendNotificationJob.php
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Notifiable $user,
        protected string $channel, // 'whatsapp', 'email', 'push'
        protected string $template,
        protected array $data,
    ) {}

    public function handle(NotificationService $service): void
    {
        $service->send(
            user: $this->user,
            channel: $this->channel,
            template: $this->template,
            data: $this->data,
        );
    }
}
```

**NotificationService menggunakan adapter pattern (format-based):**

```php
// app/Services/NotificationService.php
class NotificationService
{
    public function send(...): void
    {
        $provider = Provider::where('type', 'notification')
            ->where('is_active', true)
            ->first();

        $adapter = match ($provider->api_format) {
            'fonnte'    => new FonnteAdapter($provider),
            'whatsapp_api' => new WhatsAppCloudAdapter($provider),
            'smtp'      => new SmtpAdapter($provider),
            'fcm'       => new FcmAdapter($provider),
            default     => throw new \Exception('Unknown format'),
        };

        $adapter->send($user, $template, $data);
    }
}
```

### 2.6 Scheduler untuk Automation

Terdaftar di `routes/console.php`, di-trigger oleh cron `* * * * *`.

```
┌─────────────────────────────────────────────────────────────────┐
│                        SCHEDULER TABLE                           │
├──────────────┬──────────────────────┬────────────────────────────┤
│ Command      │ Schedule             │ Deskripsi                  │
├──────────────┼──────────────────────┼────────────────────────────┤
│ escalate     │ everyFifteenMinutes()│ Cek order overdue →        │
│ overdue      │                      │ escalate ke LP + blacklist │
├──────────────┼──────────────────────┼────────────────────────────┤
│ send         │ everyMinute()        │ Kirim pending notification │
│ notifications│                      │ dari queue via adapter     │
├──────────────┼──────────────────────┼────────────────────────────┤
│ send         │ dailyAt('08:00')     │ Reminder: H-1 jatuh tempo  │
│ reminders    │                      │ + invoice due              │
├──────────────┼──────────────────────┼────────────────────────────┤
│ check        │ hourly()             │ Cek stok di bawah minimum  │
│ low-stock    │                      │ → dispatch notification    │
├──────────────┼──────────────────────┼────────────────────────────┤
│ backup:db    │ dailyAt('02:00')     │ mysqldump → storage/       │
│              │                      │ backups/                   │
├──────────────┼──────────────────────┼────────────────────────────┤
│ clean:       │ dailyAt('03:00')     │ Hapus sync_logs >30 hari   │
│ old-sync-logs│                      │                            │
├──────────────┼──────────────────────┼────────────────────────────┤
│ prune:       │ dailyAt('04:00')     │ Hapus failed_jobs >7 hari  │
│ failed-jobs  │                      │                            │
├──────────────┼──────────────────────┼────────────────────────────┤
│ sanctum:     │ dailyAt('04:30')     │ Hapus expired API tokens    │
│ prune-expired│                      │                            │
├──────────────┼──────────────────────┼────────────────────────────┤
│ horizon:     │ dailyAt('05:00')     │ Restart Horizon gracefully  │
│ terminate    │                      │                            │
└──────────────┴──────────────────────┴────────────────────────────┘
```

---

## 3. Frontend Admin (Filament 5.6)

### 3.1 Navigation Group Organization (Alur Bisnis)

Navigation group diorganisir mengikuti alur bisnis, bukan berdasarkan tipe data.

```
┌────────────────────────────────────────────────────────────┐
│                    NAVIGATION GROUPS                        │
│                  (Urutan Alur Bisnis)                       │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  📦 MASTER DATA                                            │
│  ├── Produk           ├── Brand            ├── Supplier    │
│  ├── Kategori         ├── Unit/Satuan      ├── Pelanggan   │
│  ├── Toko/Outlet      ├── Gudang           ├── Member      │
│  └── Metode Bayar     └── Bank/Tunai                     │
│                                                            │
│  🛒 PENJUALAN                                              │
│  ├── Pesanan Baru     ├── Daftar Pesanan    ├── Pembayaran │
│  ├── Retur            └── Diskon/Voucher                   │
│                                                            │
│  📊 KEUANGAN                                               │
│  ├── Chart of Account ├── Jurnal           ├── Pengeluaran │
│  └── Kas/Bank                                                │
│                                                            │
│  📦 STOK / GUDANG                                           │
│  ├── Stok Masuk       ├── Stok Keluar       ├── Stok Opname│
│  └── Transfer Stok                                           │
│                                                            │
│  📈 LAPORAN                                                 │
│  ├── Laporan Penjualan├── Laporan Stok     ├── Laporan     │
│  ├── Laba Rugi        └── Laporan Kas      │   Keuangan    │
│                                                            │
│  📣 MARKETING / PSEO                                        │
│  ├── Blog             ├── Promo            ├── Newsletter  │
│  └── Testimoni                                              │
│                                                            │
│  🔗 INTEGRASI                                              │
│  ├── Provider AI      ├── Payment Gateway  ├── Notifikasi  │
│  └── Webhook                                                 │
│                                                            │
│  🔒 SISTEM                                                  │
│  ├── Pengguna         ├── Peran            ├── Audit Log   │
│  ├── Pengaturan       └── Backup                          │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

**Register di `AdminPanelProvider.php`:**

```php
->navigationGroups([
    NavigationGroup::make('Master Data')->collapsed(),
    NavigationGroup::make('Penjualan'),
    NavigationGroup::make('Keuangan'),
    NavigationGroup::make('Stok / Gudang'),
    NavigationGroup::make('Laporan'),
    NavigationGroup::make('Marketing'),
    NavigationGroup::make('Integrasi'),
    NavigationGroup::make('Sistem'),
])
```

### 3.2 Custom Theme Setup

**File:** `resources/css/filament/admin/theme.css`

```
┌──────────────────────────────────────────────────┐
│              CUSTOM THEME STACK                   │
├──────────────────────────────────────────────────┤
│                                                  │
│  @import '/vendor/filament/filament/resources/   │
│           css/theme.css';  (base Filament)       │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │  Primary Color: Indigo → Violet Gradient   │  │
│  │  --primary-500: #6366f1 (indigo)           │  │
│  │  --primary-600: #4f46e5                    │  │
│  │  --primary-700: #4338ca                    │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │  Typography                                │  │
│  │  Font: Inter (UI) + JetBrains Mono (code)  │  │
│  │  Via Bunny CDN via Vite                    │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │  Component Styling (override)              │  │
│  │  • Sidebar: glass effect, section labels   │  │
│  │    uppercase letter-spacing 0.06em         │  │
│  │  • Topbar: backdrop-filter blur(12px)      │  │
│  │  • Cards: border-radius 14px, soft shadow  │  │
│  │  • Tables: uppercase header, hover tint    │  │
│  │  • Forms: input border 1.5px, focus ring   │  │
│  │  • Badges: rounded 6px, weight 600        │  │
│  │  • Buttons: linear-gradient + box-shadow   │  │
│  │  • Scrollbars: subtle 10px, rounded thumb  │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │  Responsive Breakpoints                    │  │
│  │  • Tablet (max-width: 1023px)              │  │
│  │    - Section radius 10px, padding 14px     │  │
│  │    - Table cell font-size 12.5px           │  │
│  │    - Stats value font-size 22px            │  │
│  │  • Mobile (max-width: 640px)               │  │
│  │    - Sidebar → drawer overlay 280px        │  │
│  │    - Touch targets min 38px (WCAG 2.5.5)   │  │
│  │    - Form fields full-width                │  │
│  │    - Stats stack vertikal                  │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │  Accessibility                             │  │
│  │  • Print: hide sidebar + topbar            │  │
│  │  • Reduced motion: animation 0.01ms        │  │
│  │  • Dark mode: full override                │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │  Login Page (.fi-login-clean)              │  │
│  │  • Centered, max-width 420px               │  │
│  │  • Background #f8fafc                       │  │
│  │  • Icon-only SVG logo (no brand text)      │  │
│  │  • No right panel, no testimonials         │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
└──────────────────────────────────────────────────┘
```

**Register di Vite (`vite.config.js`):**

```js
laravel({
    input: [
        'resources/css/app.css',
        'resources/css/filament/admin/theme.css',
        'resources/js/app.js',
    ],
    fonts: [
        bunny('Inter', { weights: [400, 500, 600, 700, 800] }),
        bunny('JetBrains Mono', { weights: [400, 500, 700] }),
    ],
})
```

**AdminPanelProvider config:**

```php
->viteTheme('resources/css/filament/admin/theme.css')
->brandName('Admin')
->brandLogo(fn () => new HtmlString('<svg>...</svg>')) // icon only
->darkMode(true)
->sidebarCollapsibleOnDesktop()
->sidebarWidth('15.5rem')
->collapsedSidebarWidth('4rem')
->topbar(true)
```

### 3.3 Widget System Per Role

Setiap role melihat widget berbeda di dashboard menggunakan trait `DashboardWidgetFilter`.

```
┌─────────────────────────────────────────────────────────────────┐
│                     DASHBOARD WIDGET PER ROLE                     │
├────────────┬──────────────────┬──────────────────────────────────┤
│ Widget     │ Owner  Mgr  Staff│ Deskripsi                        │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Stats      │  ✅    ✅    ✅   │ Total pendapatan, pesanan,      │
│ Overview   │                  │ produk, pelanggan (hari ini)     │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Sales      │  ✅    ✅    ❌   │ Chart penjualan harian/mingguan │
│ Chart      │                  │ (bar chart)                     │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Payment    │  ✅    ✅    ❌   │ Doughnut: metode bayar          │
│ Method     │                  │ (cash, QRIS, transfer)          │
│ Chart      │                  │                                  │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Top        │  ✅    ✅    ❌   │ Top 10 produk terlaris (bar)    │
│ Products   │                  │                                  │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Cashier    │  ❌    ❌    ✅   │ Transaksi staff hari ini        │
│ Today      │                  │ (table)                          │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Low Stock  │  ✅    ✅    ✅   │ Produk di bawah stok minimum    │
│ Alert      │                  │ (table dengan badge warning)     │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Pending    │  ✅    ✅    ✅   │ Pesanan belum dibayar           │
│ Orders     │                  │ (table dengan action button)     │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Recent     │  ✅    ✅    ✅   │ 10 transaksi terakhir           │
│ Orders     │                  │ (table compact)                  │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Pending    │  ✅    ✅    ❌   │ Pesanan >threshold perlu        │
│ Approval   │                  │ approval (widget)                │
├────────────┼──────────────────┼──────────────────────────────────┤
│ Expiring   │  ✅    ✅    ✅   │ Produk mendekati expired        │
│ Products   │                  │ (table)                          │
└────────────┴──────────────────┴──────────────────────────────────┘
```

**Trait widget filter:**

```php
// app/Filament/Widgets/DashboardWidgetFilter.php
trait DashboardWidgetFilter
{
    public static function canView(): bool
    {
        return static::isVisibleToRole(auth()->user()?->role);
    }

    protected static function isVisibleToRole(?string $role): bool
    {
        return true; // override di widget spesifik
    }
}

// Contoh widget role-specific:
class CashierTodayWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static function isVisibleToRole(?string $role): bool
    {
        return $role === 'kasir';
    }
}
```

**Auto-refresh via polling:** Semua widget dashboard menggunakan polling interval untuk data real-time.

```php
protected int|string|array $pollingInterval = '30s';
```

---

## 4. Mobile App (Flutter)

### 4.1 Arsitektur Flutter Kasir

```
┌─────────────────────────────────────────────────────────────┐
│                  FLUTTER APP ARCHITECTURE                     │
│                  (Android Cashier App)                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                    UI LAYER                            │  │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐  │  │
│  │  │ Login   │  │ Product │  │ Cart    │  │ Payment │  │  │
│  │  │ Screen  │  │ Browser │  │ Screen  │  │ Screen  │  │  │
│  │  └─────────┘  └─────────┘  └─────────┘  └─────────┘  │  │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐  │  │
│  │  │ Order   │  │ Sync    │  │ Settings│  │ Receipt │  │  │
│  │  │ History │  │ Status  │  │ Screen  │  │ Preview │  │  │
│  │  └─────────┘  └─────────┘  └─────────┘  └─────────┘  │  │
│  └───────────────────────────────────────────────────────┘  │
│                          │                                   │
│                          ▼                                   │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                STATE MANAGEMENT                        │  │
│  │              (Provider / Riverpod)                      │  │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  │  │
│  │  │ Auth     │ │ Cart     │ │ Product  │ │ Order    │  │  │
│  │  │ Provider │ │ Provider │ │ Provider │ │ Provider │  │  │
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘  │  │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐               │  │
│  │  │ Sync     │ │ Printer  │ │ Settings │               │  │
│  │  │ Provider │ │ Provider │ │ Provider │               │  │
│  │  └──────────┘ └──────────┘ └──────────┘               │  │
│  └───────────────────────────────────────────────────────┘  │
│                          │                                   │
│                          ▼                                   │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                SERVICE / REPOSITORY                    │  │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  │  │
│  │  │ ApiClient│ │ Product  │ │ Order    │ │ Sync     │  │  │
│  │  │ (Dio)    │ │ Repo     │ │ Repo     │ │ Engine   │  │  │
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘  │  │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐               │  │
│  │  │ LocalDB  │ │ Printer  │ │ Scanner  │               │  │
│  │  │ (SQLite) │ │ Service  │ │ Service  │               │  │
│  │  └──────────┘ └──────────┘ └──────────┘               │  │
│  └───────────────────────────────────────────────────────┘  │
│                          │                                   │
│                          ▼                                   │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                    DATA LAYER                          │  │
│  │  ┌──────────────────┐    ┌──────────────────────────┐ │  │
│  │  │   SQLite (Local)  │    │   REST API (Remote)      │ │  │
│  │  │   • products      │    │   POST /api/v1/auth/     │ │  │
│  │  │   • categories    │    │   login                 │ │  │
│  │  │   • orders (local)│    │   GET  /api/v1/products  │ │  │
│  │  │   • cart_cache    │    │   POST /api/v1/orders    │ │  │
│  │  │   • sync_queue    │    │   POST /api/v1/sync      │ │  │
│  │  └──────────────────┘    └──────────────────────────┘ │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 4.2 State Management — Provider

Disarankan menggunakan **Provider** (ringan, cukup untuk POS) atau **Riverpod** jika butuh compile-time safety.

```dart
// CartProvider.dart
class CartProvider extends ChangeNotifier {
    final List<CartItem> _items = [];

    List<CartItem> get items => List.unmodifiable(_items);
    int get itemCount => _items.fold(0, (sum, item) => sum + item.qty);
    double get subtotal => _items.fold(0, (sum, item) => sum + item.subtotal);

    void addItem(Product product, int qty) { ... }
    void removeItem(int index) { ... }
    void updateQty(int index, int qty) { ... }
    void clear() { ... }
}

// ProductProvider.dart
class ProductProvider extends ChangeNotifier {
    List<Product> _products = [];

    Future<void> fetchProducts() async {
        // Cek offline dulu
        _products = await LocalDB.getAllProducts();
        notifyListeners();

        // Fetch dari API untuk update
        try {
            final remote = await ApiClient.getProducts();
            await LocalDB.syncProducts(remote);
            _products = remote;
            notifyListeners();
        } catch (e) {
            // Tetap pakai data lokal
        }
    }
}
```

### 4.3 Offline-First Architecture

Aplikasi kasir harus tetap berfungsi saat internet mati.

```
┌───────────────────────────────────────────────────────────┐
│                OFFLINE-FIRST DATA FLOW                     │
├───────────────────────────────────────────────────────────┤
│                                                           │
│   [User Action] ──▶ [Local SQLite] ──▶ [UI Updated]       │
│        │                                                  │
│        ├── Online? ──▶ [API Request] ──▶ [Server]         │
│        │       │                           │              │
│        │       └── Success ──▶ [Update SQLite with        │
│        │                        server response]          │
│        │                                                  │
│        └── Offline? ──▶ [Queued in sync_queue]            │
│                 │                                         │
│                 └── [Sync Engine] ──▶ When online:        │
│                         • POST /api/v1/sync (batch)       │
│                         • Conflict resolution:            │
│                           server-wins for inventory       │
│                           client-wins for local orders    │
│                         • Update SQLite with server data  │
│                                                           │
└───────────────────────────────────────────────────────────┘
```

**Schema local SQLite:**

```sql
-- Produk cache (sync dari server)
CREATE TABLE products (
    id INTEGER PRIMARY KEY,
    server_id INTEGER UNIQUE,
    name TEXT, barcode TEXT, price REAL,
    stock INTEGER,
    category_id INTEGER,
    image_url TEXT,
    updated_at TEXT
);

-- Pesanan lokal (dibuat saat offline, sync ke server)
CREATE TABLE local_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT UNIQUE,          -- UUID client-side
    customer_name TEXT,
    subtotal REAL, tax REAL, total REAL,
    payment_method TEXT,       -- cash / qris / transfer
    items_json TEXT,           -- JSON array order items
    status TEXT DEFAULT 'pending', -- pending / synced / failed
    created_at TEXT,
    synced_at TEXT NULL
);

-- Queue sync (antrian data untuk dikirim ke server)
CREATE TABLE sync_queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    table_name TEXT,
    record_id TEXT,
    action TEXT,               -- create / update / delete
    payload TEXT,              -- JSON
    status TEXT DEFAULT 'pending',
    created_at TEXT,
    attempted_at TEXT NULL,
    error TEXT NULL
);
```

**Sync Engine flow:**

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│ Timer    │────▶│ Check    │────▶│ Pull     │────▶│ Push     │
│ (30 detik)│    │ Online?   │     │ from API │     │ queue    │
└──────────┘     └──────────┘     └──────────┘     └──────────┘
                                     │                  │
                                     ▼                  ▼
                               ┌──────────┐     ┌──────────┐
                               │ Update   │     │ Resolve  │
                               │ SQLite   │     │ Conflict │
                               └──────────┘     └──────────┘
```

### 4.4 Bluetooth Thermal Printer

```
┌───────────────────────────────────────────────────────────┐
│            PRINTER INTEGRATION FLOW                        │
├───────────────────────────────────────────────────────────┤
│                                                           │
│  ┌──────────┐    ┌──────────────────┐    ┌─────────────┐  │
│  │ Order    │───▶│ Receipt Builder  │───▶│ Bluetooth   │  │
│  │ Completed│    │ • Header (toko)  │    │ Printer     │  │
│  └──────────┘    │ • Items table    │    │ (ESC/POS)   │  │
│                  │ • Total + tax    │    └─────────────┘  │
│                  │ • Barcode        │                     │
│                  │ • Footer         │                     │
│                  └──────────────────┘                     │
│                                                           │
│  Package: esc_pos_bluetooth / esc_pos_utils               │
│                                                           │
│  Setting printer di app:                                  │
│  • Bluetooth device pairing (user pilih dari list)        │
│  • Paper size: 58mm / 80mm                                │
│  • DPI: 203                                               │
│  • Toko name, alamat, phone (from API settings)          │
│                                                           │
└───────────────────────────────────────────────────────────┘
```

```dart
// PrinterService.dart
class PrinterService {
    BluetoothDevice? _device;

    Future<void> connect(String address) async {
        _device = await BluetoothConnection.connect(address);
    }

    Future<void> printReceipt(Order order) async {
        final profile = await CapabilityProfile.load();
        final generator = Generator(PaperSize.mm58, profile);

        List<int> bytes = [];
        bytes += generator.text('TOKO RETAIL',
            styles: PosStyles(bold: true, align: PosAlign.center));
        bytes += generator.text('Jl. Contoh No. 123');
        bytes += generator.hr();
        bytes += generator.row([
            PosColumn(text: 'Item', width: 6),
            PosColumn(text: 'Qty', width: 2),
            PosColumn(text: 'Total', width: 4),
        ]);
        for (final item in order.items) {
            bytes += generator.row([
                PosColumn(text: item.name, width: 6),
                PosColumn(text: '${item.qty}', width: 2),
                PosColumn(text: '${item.total}', width: 4),
            ]);
        }
        bytes += generator.hr();
        bytes += generator.row([
            PosColumn(text: 'TOTAL', width: 8),
            PosColumn(text: '${order.total}', width: 4,
                styles: PosStyles(bold: true)),
        ]);
        bytes += generator.cut();

        await _device?.write(bytes);
    }
}
```

### 4.5 Barcode Scanner

Dua mode scan:

| Mode | Library | Use Case |
|------|---------|----------|
| Kamera | `mobile_scanner` | Scan barcode fisik produk |
| Bluetooth Scanner | Raw HID / Serial | Scanner handheld eksternal (lebih cepat) |

```dart
// Camera-based scanner (mobile_scanner)
class BarcodeScannerWidget extends StatelessWidget {
    @override
    Widget build(BuildContext context) {
        return MobileScanner(
            onDetect: (capture) {
                final barcode = capture.barcodes.first;
                if (barcode.rawValue != null) {
                    context.read<ProductProvider>().searchByBarcode(
                        barcode.rawValue!
                    );
                }
            },
        );
    }
}

// Product lookup di Provider
class ProductProvider extends ChangeNotifier {
    Product? _scannedProduct;

    Future<void> searchByBarcode(String barcode) async {
        // Cek lokal dulu
        _scannedProduct = await LocalDB.getProductByBarcode(barcode);

        // Jika tidak ditemukan, coba API
        if (_scannedProduct == null) {
            _scannedProduct = await ApiClient.getProductByBarcode(barcode);
        }

        if (_scannedProduct != null) {
            notifyListeners(); // trigger cart add
        }
    }
}
```

### 4.6 Flutter Project Structure

```
flutter_app/
├── lib/
│   ├── main.dart
│   ├── app.dart                          # MaterialApp + router
│   │
│   ├── config/
│   │   ├── api_config.dart               # Base URL, headers
│   │   ├── app_theme.dart                # Light/dark theme
│   │   └── constants.dart                # App-wide constants
│   │
│   ├── models/
│   │   ├── product.dart                  # Freezed/JSON serializable
│   │   ├── order.dart
│   │   ├── cart_item.dart
│   │   ├── customer.dart
│   │   └── sync_record.dart
│   │
│   ├── providers/
│   │   ├── auth_provider.dart
│   │   ├── cart_provider.dart
│   │   ├── product_provider.dart
│   │   ├── order_provider.dart
│   │   ├── sync_provider.dart
│   │   ├── printer_provider.dart
│   │   └── settings_provider.dart
│   │
│   ├── services/
│   │   ├── api_client.dart               # Dio instance + interceptors
│   │   ├── local_db.dart                 # SQLite helper
│   │   ├── sync_engine.dart              # Offline sync logic
│   │   ├── printer_service.dart          # Bluetooth thermal print
│   │   └── auth_service.dart             # Token storage (flutter_secure_storage)
│   │
│   ├── screens/
│   │   ├── login_screen.dart
│   │   ├── product_list_screen.dart
│   │   ├── cart_screen.dart
│   │   ├── payment_screen.dart
│   │   ├── receipt_preview_screen.dart
│   │   ├── order_history_screen.dart
│   │   ├── sync_status_screen.dart
│   │   └── settings_screen.dart
│   │
│   └── widgets/
│       ├── product_card.dart
│       ├── cart_item_tile.dart
│       ├── barcode_scanner_sheet.dart
│       ├── payment_method_selector.dart
│       └── sync_status_indicator.dart
│
├── pubspec.yaml
├── android/
│   └── app/build.gradle                  # Min SDK 24, target SDK 34
└── test/
    ├── providers/cart_provider_test.dart
    └── services/sync_engine_test.dart
```

---

## 5. API Design (Sanctum)

### 5.1 Auth Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                     SANCTUM AUTH FLOW                             │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ADMIN (Filament) - SPA Cookie                                   │
│  ────────────────────────────────                                │
│  [Browser]                                                       │
│      │ POST /admin/login                                          │
│      │   email + password                                         │
│      ▼                                                           │
│  [Server]                                                        │
│      │ Validate credentials                                      │
│      │ Set XSRF-TOKEN cookie                                     │
│      │ Set session cookie                                        │
│      ▼                                                           │
│  [Browser] → Subsequent requests include cookie automatically    │
│                                                                  │
│  ═══════════════════════════════════                             │
│                                                                  │
│  FLUTTER APP - API Token (Bearer)                                │
│  ────────────────────────────────                                │
│  [Flutter App]                                                   │
│      │ POST /api/v1/auth/login                                    │
│      │   email + password + device_name                           │
│      ▼                                                           │
│  [Server]                                                        │
│      │ Validate credentials                                      │
│      │ Create Sanctum token via createToken()                    │
│      │ Return { token, user, store }                             │
│      ▼                                                           │
│  [Flutter App]                                                   │
│      │ Simpan token di flutter_secure_storage                    │
│      │ Set header: Authorization: Bearer {token}                 │
│      │ Semua request API berikutnya pakai token                  │
│      ▼                                                           │
│  [Flutter App] → POST /api/v1/auth/logout                        │
│      │ Delete token di server                                    │
│      │ Hapus token dari secure storage                           │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Login response:**

```json
{
    "status": "success",
    "message": "Login berhasil",
    "data": {
        "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
        "user": {
            "id": 1,
            "name": "Kasir 01",
            "email": "kasir@toko.com",
            "role": "kasir"
        },
        "store": {
            "id": 1,
            "name": "Toko Retail Pusat",
            "address": "Jl. Contoh No. 123",
            "phone": "081234567890"
        }
    }
}
```

### 5.2 API Versioning

```
GET /api/v1/products          → App\Http\Controllers\Api\V1\ProductController
GET /api/v2/products          → App\Http\Controllers\Api\V2\ProductController
                                     (future: breaking changes)
```

**Route file (`routes/api.php`):**

```php
Route::prefix('v1')->group(function () {
    // Public
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::get('/products/barcode/{barcode}', [ProductController::class, 'byBarcode']);

        // Orders
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);

        // Payments
        Route::post('/payments', [PaymentController::class, 'store']);

        // Offline Sync
        Route::post('/sync', [SyncController::class, 'push']);
        Route::get('/sync/last-updated', [SyncController::class, 'lastUpdated']);
    });
});
```

### 5.3 Rate Limiting

Default: **60 request per menit** untuk API endpoint.

```php
// Di config/sanctum.php atau via middleware
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by(
        $request->user()?->id ?: $request->ip()
    );
});
```

Rate limit per endpoint disesuaikan:

| Endpoint | Rate Limit | Reason |
|----------|-----------|--------|
| `/api/v1/auth/login` | 5/min | Brute force protection |
| `/api/v1/orders` | 60/min | Transaksi normal |
| `/api/v1/sync` | 30/min | Batch sync (payload besar) |
| `/api/v1/products` | 120/min | Browsing produk (cached) |

### 5.4 Endpoint Structure

```
┌──────────────────────────────────────────────────────────────────┐
│                        API v1 ENDPOINTS                           │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  AUTH                                                             │
│  ──────────────────────────────────────────────────────────────  │
│  POST   /api/v1/auth/login           Login kasir                │
│  POST   /api/v1/auth/logout          Logout (revoke token)      │
│  GET    /api/v1/auth/me              Get current user profile   │
│  PUT    /api/v1/auth/password        Change password            │
│                                                                  │
│  PRODUCTS                                                         │
│  ──────────────────────────────────────────────────────────────  │
│  GET    /api/v1/products             List produk (paginate)     │
│  GET    /api/v1/products/{id}        Detail produk              │
│  GET    /api/v1/products/barcode/{bc} Cari by barcode           │
│  GET    /api/v1/categories           List kategori              │
│                                                                  │
│  ORDERS                                                           │
│  ──────────────────────────────────────────────────────────────  │
│  POST   /api/v1/orders               Buat pesanan baru          │
│  GET    /api/v1/orders               Riwayat pesanan            │
│  GET    /api/v1/orders/{id}          Detail pesanan             │
│  GET    /api/v1/orders/today         Pesanan hari ini           │
│                                                                  │
│  PAYMENTS                                                         │
│  ──────────────────────────────────────────────────────────────  │
│  POST   /api/v1/payments             Proses pembayaran          │
│  GET    /api/v1/payments/{id}        Detail pembayaran          │
│  GET    /api/v1/payment-methods      Metode bayar tersedia      │
│                                                                  │
│  CUSTOMERS                                                        │
│  ──────────────────────────────────────────────────────────────  │
│  GET    /api/v1/customers            List pelanggan             │
│  GET    /api/v1/customers/search     Cari pelanggan (phone)     │
│  POST   /api/v1/customers            Tambah pelanggan baru      │
│                                                                  │
│  STORE / SETTINGS                                                 │
│  ──────────────────────────────────────────────────────────────  │
│  GET    /api/v1/store               Info toko (nama, alamat)   │
│  GET    /api/v1/settings            Pengaturan umum             │
│                                                                  │
│  SYNC                                                             │
│  ──────────────────────────────────────────────────────────────  │
│  POST   /api/v1/sync                Push data offline ke server │
│  GET    /api/v1/sync/last-updated   Timestamp terakhir update   │
│  GET    /api/v1/sync/products       Pull semua produk (full)    │
│  GET    /api/v1/sync/changes        Pull perubahan sejak tgl    │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 5.5 Response Standard

Semua response API mengikuti format standar:

```json
// Success
{
    "status": "success",
    "message": "Pesanan berhasil dibuat",
    "data": { ... }
}

// Error (validation)
{
    "status": "error",
    "message": "Validasi gagal",
    "errors": {
        "items": ["Minimal 1 item diperlukan"],
        "payment_method": ["Metode bayar wajib dipilih"]
    }
}

// Error (server)
{
    "status": "error",
    "message": "Terjadi kesalahan server. Silakan coba lagi.",
    "code": 500
}
```

---

## 6. Database (MySQL)

### 6.1 Connection Pool

```
┌────────────────────────────────────────────────────────────┐
│                  CONNECTION POOLING                         │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  config/database.php:                                      │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ 'mysql' => [                                         │  │
│  │     'driver'       => 'mysql',                       │  │
│  │     'host'         => env('DB_HOST', '127.0.0.1'),   │  │
│  │     'port'         => env('DB_PORT', '3306'),        │  │
│  │     'database'     => env('DB_DATABASE', 'pos'),     │  │
│  │     'username'     => env('DB_USERNAME', 'pos'),     │  │
│  │     'password'     => env('DB_PASSWORD', ''),        │  │
│  │     'charset'      => 'utf8mb4',                     │  │
│  │     'collation'    => 'utf8mb4_unicode_ci',          │  │
│  │     'prefix'       => '',                            │  │
│  │     'options'      => [                              │  │
│  │         PDO::ATTR_PERSISTENT => true,  // << pooling │  │
│  │     ],                                               │  │
│  │ ]                                                    │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                            │
│  PHP-FPM pool → PDO persistent connections                  │
│  Tanpa persistent: connect/disconnect tiap request          │
│  Dengan persistent: reuse existing connection               │
│                                                            │
│  MySQL config (my.cnf):                                    │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ max_connections         = 100                         │  │
│  │ max_user_connections    = 50                          │  │
│  │ wait_timeout            = 600                         │  │
│  │ interactive_timeout     = 600                         │  │
│  │ thread_cache_size       = 8                           │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

### 6.2 Indexing Strategy

```
┌──────────────────────────────────────────────────────────────────┐
│                      INDEXING STRATEGY                            │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  TABEL PRODUCTS                                                   │
│  ────────────────                                                │
│  PRIMARY    id                                                   │
│  UNIQUE     barcode                                              │
│  INDEX      category_id (FK)                                     │
│  INDEX      brand_id (FK)                                        │
│  INDEX      (name)          -- FULLTEXT untuk search             │
│  INDEX      (price)         -- filter/sort                      │
│  INDEX      (is_active, stock) -- filter produk aktif + stok    │
│                                                                  │
│  TABEL ORDERS                                                     │
│  ──────────────                                                  │
│  PRIMARY    id                                                   │
│  INDEX      store_id (FK)                                        │
│  INDEX      customer_id (FK)                                     │
│  INDEX      user_id (FK)                                          │
│  INDEX      (created_at)   -- date range queries                │
│  INDEX      (status, created_at) -- filter status + sort by date│
│  INDEX      (store_id, created_at) -- per-store reporting       │
│                                                                  │
│  TABEL ORDER_ITEMS                                                │
│  ──────────────────                                              │
│  PRIMARY    id                                                   │
│  INDEX      order_id (FK)                                        │
│  INDEX      product_id (FK)                                      │
│  INDEX      (order_id, product_id) -- composite                │
│                                                                  │
│  TABEL STOCK_MOVEMENTS                                           │
│  ──────────────────────                                          │
│  PRIMARY    id                                                   │
│  INDEX      product_id (FK)                                      │
│  INDEX      (created_at)                                         │
│  INDEX      (type, created_at) -- filter type + date            │
│  INDEX      (store_id, product_id) -- per-store per-product     │
│                                                                  │
│  TABEL PAYMENTS                                                   │
│  ────────────────                                                │
│  PRIMARY    id                                                   │
│  INDEX      order_id (FK)                                        │
│  INDEX      (created_at)                                         │
│  INDEX      (payment_method, created_at)                        │
│                                                                  │
│  TABEL AUDIT_LOGS                                                 │
│  ────────────────                                                │
│  PRIMARY    id                                                   │
│  INDEX      user_id                                              │
│  INDEX      (auditable_type, auditable_id) -- polymorphic      │
│  INDEX      (created_at)                                         │
│  INDEX      (event)          -- created/updated/deleted         │
│                                                                  │
│  TABEL NOTIFICATIONS                                              │
│  ───────────────────                                             │
│  PRIMARY    id                                                   │
│  INDEX      notifiable_type, notifiable_id -- polymorphic      │
│  INDEX      (read_at)    -- where read_at IS NULL               │
│  INDEX      (created_at)                                         │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Panduan indexing:**

1. **Foreign Key selalu di-index** — Laravel migration `->constrained()` tidak otomatis bikin index, harus explicit `->index()` pada column FK
2. **Date columns di-index** — selalu pakai composite dengan status field: `INDEX (status, created_at)`
3. **FULLTEXT index** untuk search produk/nama via `$table->fullText('name')` di MySQL
4. **Composite index** untuk query yang sering difilter + di-sort bersamaan
5. **Avoid over-indexing** — terlalu banyak index memperlambat INSERT/UPDATE

### 6.3 Backup Strategy

```
┌──────────────────────────────────────────────────────────────────┐
│                     BACKUP STRATEGY                               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  LEVEL 1: Automated Daily (Scheduler)                             │
│  ──────────────────────────────────────                          │
│  Command: php artisan backup:db                                  │
│  Schedule: dailyAt('02:00')                                      │
│  Output: storage/app/backups/pos-backup-2026-05-31-020000.sql.gz│
│  Retention: 30 hari lokal                                        │
│                                                                  │
│  LEVEL 2: Weekly Offsite (Laravel Filesystem)                     │
│  ─────────────────────────────────────────                       │
│  Copy backup ke S3 / DO Spaces / GCS via:                        │
│  Storage::disk('s3')->put(                                       │
│      "backups/{$filename}",                                       │
│      file_get_contents($localPath)                                │
│  );                                                               │
│  Schedule: weekly()->sundays()->at('03:00')                      │
│                                                                  │
│  LEVEL 3: Binary Log (Point-in-Time Recovery)                     │
│  ────────────────────────────────────────────                    │
│  MySQL config:                                                    │
│  log_bin = /var/lib/mysql/mysql-bin                              │
│  binlog_expire_logs_seconds = 604800  (7 hari)                  │
│                                                                  │
│  RESTORE PROCEDURE:                                               │
│  ─────────────────────────────⁠──                                  │
│  1. Restore full backup:                                          │
│     gunzip < backup.sql.gz | mysql -u user -p pos                │
│  2. Point-in-time recovery (jika perlu):                          │
│     mysqlbinlog mysql-bin.0000xx | mysql -u user -p pos          │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Backup command:**

```php
// app/Console/Commands/BackupDatabase.php
class BackupDatabase extends Command
{
    protected $signature = 'backup:db {--disk=local}';

    public function handle(): void
    {
        $filename = sprintf(
            'pos-backup-%s.sql.gz',
            now()->format('Y-m-d-His')
        );

        $path = storage_path("app/backups/{$filename}");

        // mysqldump
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s | gzip > %s',
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($path),
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Backup gagal.');
            return;
        }

        // Upload ke cloud (opsional)
        if ($this->option('disk') === 's3') {
            Storage::disk('s3')->put(
                "backups/{$filename}",
                fopen($path, 'r')
            );
        }

        // Cleanup: hapus backup >30 hari
        $this->cleanOldBackups(30);

        $this->info("Backup selesai: {$filename}");
    }
}
```

---

## 7. Security

### 7.1 Sanctum SPA Auth (Admin + Portal)

```
┌──────────────────────────────────────────────────────────────────┐
│                SANCTUM SPA AUTH (COOKIE-BASED)                    │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Digunakan untuk:                                                │
│  • Filament Admin (Laravel SPA)                                  │
│  • Customer Portal (Laravel SPA)                                 │
│                                                                  │
│  Middleware: auth                                                 │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 'stateful,web'  -- Sanctum stateful middleware             │  │
│  │ + session-based auth                                      │  │
│  │ + CSRF protection (XSRF-TOKEN cookie)                     │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  Sanity checks:                                                  │
│  • SESSION_DRIVER=database (simpan session di DB, bukan file)   │
│  • SESSION_LIFETIME=120 (2 jam auto logout)                     │
│  • SESSION_SECURE_COOKIE=true (production only, HTTPS)          │
│  • SESSION_SAME_SITE=lax                                        │
│  • SANCTUM_STATEFUL_DOMAINS=domainanda.com                       │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 7.2 Sanctum Token Auth (Flutter API)

```
┌──────────────────────────────────────────────────────────────────┐
│               SANCTUM TOKEN AUTH (BEARER TOKEN)                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Middleware: auth:sanctum                                         │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Header: Authorization: Bearer 1|aBcDeFgHiJkLmNoPqRsTu...   │  │
│  │ Tidak butuh CSRF (stateless)                               │  │
│  │ Token bisa multiple per user (multi-device)                │  │
│  │ Token abilities: ['product:read', 'order:create', ...]    │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  Token management:                                               │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ $user->createToken('kasir-tablet-01', ['order:create']);   │  │
│  │ // Simpan plain text token (hanya muncul 1x saat create)   │  │
│  │ $token->plainTextToken;  // 1|aBcDeF...                    │  │
│  │ $user->tokens()->delete();  // revoke all                   │  │
│  │ $user->currentAccessToken()->delete();  // revoke current   │  │
│  │                                                            │  │
│  │ Scheduler: php artisan sanctum:prune-expired (daily)      │  │
│  │ Hapus token expired > expiry duration                       │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  Flutter secure storage (flutter_secure_storage):                 │
│  • Android: EncryptedSharedPreferences                           │
│  • Key: auth_token                                               │
│  • Never stored in plain SharedPreferences                       │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 7.3 Role-Based Middleware

```
┌──────────────────────────────────────────────────────────────────┐
│                    ROLE MATRIX                                    │
├──────────────┬───────────────────────────────────────────────────┤
│ Role         │ Akses                                             │
├──────────────┼───────────────────────────────────────────────────┤
│ owner        │ Semua akses penuh (full CRUD, reports, settings)  │
├──────────────┼───────────────────────────────────────────────────┤
│ manager      │ CRUD produk + orders + reports (no user mgmt)    │
├──────────────┼───────────────────────────────────────────────────┤
│ admin        │ CRUD produk + orders + laporan harian           │
├──────────────┼───────────────────────────────────────────────────┤
│ kasir        │ Hanya create orders + view produk                │
├──────────────┼───────────────────────────────────────────────────┤
│ staff_gudang │ Stock management (in/out/opname/transfer)        │
├──────────────┼───────────────────────────────────────────────────┤
│ finance      │ Keuangan: COA, journal, expense, P&L            │
└──────────────┴───────────────────────────────────────────────────┘
```

**Middleware:**

```php
// app/Http/Middleware/RoleMiddleware.php
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Role tidak diizinkan.',
                ], 403);
            }
            abort(403, 'Akses ditolak.');
        }
        return $next($request);
    }
}
```

**Route usage:**

```php
// API routes
Route::middleware(['auth:sanctum', 'role:owner,manager,admin,kasir'])
    ->group(function () { ... });

// Filament — via canAccess() di resource
class OrderResource extends Resource
{
    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin', 'kasir']);
    }
}
```

### 7.4 Audit Logging

```
┌──────────────────────────────────────────────────────────────────┐
│                     AUDIT LOGGING                                 │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Logged via:                                                     │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 1. Laravel Model Events (created, updated, deleted)        │  │
│  │    → AuditLogService::log($event, $model)                  │  │
│  │                                                            │  │
│  │ 2. Custom middleware untuk track login/logout              │  │
│  │    → AuditLog::log('login', $user)                         │  │
│  │                                                            │  │
│  │ 3. Sensitive fields: old_value + new_value disimpan        │  │
│  │    sebagai JSON untuk rollback                             │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  Schema audit_logs:                                              │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ id          BIGINT PRIMARY AUTO_INCREMENT                  │  │
│  │ user_id     BIGINT FK → users (nullable for system)       │  │
│  │ event       VARCHAR(50)  -- created, updated, deleted,    │  │
│  │                            login, logout, export           │  │
│  │ auditable_type VARCHAR(255) -- Model class                │  │
│  │ auditable_id   BIGINT                                      │  │
│  │ old_values  JSON (nullable)                                 │  │
│  │ new_values  JSON (nullable)                                 │  │
│  │ ip_address  VARCHAR(45)                                     │  │
│  │ user_agent  TEXT                                            │  │
│  │ metadata    JSON (nullable)  -- extra context              │  │
│  │ created_at  TIMESTAMP                                      │  │
│  │                                                            │  │
│  │ INDEX (user_id), INDEX (auditable_type, auditable_id)     │  │
│  │ INDEX (event), INDEX (created_at)                         │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  Kebijakan retensi:                                              │
│  • Audit logs → 12 bulan (auto prune via scheduler)             │
│  • Login logs → 6 bulan                                         │
│  • Failed login → 3 bulan                                       │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 7.5 Security Checklist

```
┌──────────────────────────────────────────────────────────────────┐
│                   SECURITY CHECKLIST                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  [✅] Sanctum token dengan abilities (product:read, order:* )    │
│  [✅] CORS dibatasi ke domain yang diketahui                     │
│  [✅] Rate limiting: 60 req/min global, 5/min login             │
│  [✅] Password: min 8 char, harus ada angka + huruf             │
│  [✅] API keys di-encrypt di DB (AES-256-GCM)                   │
│  [✅] Session hijacking: session driver = database              │
│  [✅] HTTPS enforced (HSTS header)                               │
│  [✅] XSS: semua output di-escape, CSP header                   │
│  [✅] SQL Injection: Eloquent + prepared statements             │
│  [✅] File upload: validasi mime type + size + antivirus scan    │
│  [✅] Input sanitization di FormRequest                          │
│  [✅] Debug mode OFF di production (APP_DEBUG=false)            │
│  [✅] Log sensitif tidak include password/API key               │
│  [✅] Scheduler: prune expired tokens + session + audit logs    │
│  [✅] Filament admin: SPA cookie + CSRF                          │
│  [✅] Flutter: token di flutter_secure_storage (encrypted)      │
│  [✅] Backup encrypted at rest (AES-256)                         │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 8. Deployment

### 8.1 Nginx Configuration

```
┌──────────────────────────────────────────────────────────────────┐
│                     NGINX CONFIG                                  │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  deploy/nginx.conf:                                              │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                                                            │  │
│  │ server {                                                   │  │
│  │     listen 80;                                             │  │
│  │     server_name domainanda.com;                            │  │
│  │     return 301 https://$host$request_uri;                  │  │
│  │ }                                                          │  │
│  │                                                            │  │
│  │ server {                                                   │  │
│  │     listen 443 ssl http2;                                  │  │
│  │     server_name domainanda.com;                            │  │
│  │     root /var/www/pos-retail/public;                       │  │
│  │                                                            │  │
│  │     # SSL                                                   │  │
│  │     ssl_certificate     /etc/ssl/certs/domainanda.pem;     │  │
│  │     ssl_certificate_key /etc/ssl/private/domainanda.key;   │  │
│  │                                                            │  │
│  │     # Security headers                                      │  │
│  │     add_header X-Frame-Options "SAMEORIGIN";               │  │
│  │     add_header X-Content-Type-Options "nosniff";           │  │
│  │     add_header X-XSS-Protection "1; mode=block";           │  │
│  │     add_header Strict-Transport-Security                    │  │
│  │         "max-age=31536000; includeSubDomains";             │  │
│  │                                                            │  │
│  │     # Laravel routing                                       │  │
│  │     location / {                                           │  │
│  │         try_files $uri $uri/ /index.php?$query_string;    │  │
│  │     }                                                      │  │
│  │                                                            │  │
│  │     # PHP-FPM                                              │  │
│  │     location ~ \.php$ {                                    │  │
│  │         fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;   │  │
│  │         include fastcgi_params;                            │  │
│  │         fastcgi_param SCRIPT_FILENAME                     │  │
│  │             $document_root$fastcgi_script_name;           │  │
│  │     }                                                      │  │
│  │                                                            │  │
│  │     # Rate limiting (API)                                   │  │
│  │     location /api/ {                                       │  │
│  │         limit_req zone=api burst=30 nodelay;              │  │
│  │         try_files $uri $uri/ /index.php?$query_string;    │  │
│  │     }                                                      │  │
│  │                                                            │  │
│  │     # Static assets (cache 1 year)                         │  │
│  │     location ~* \.(css|js|svg|woff2|png|jpg|ico)$ {       │  │
│  │         expires 1y;                                       │  │
│  │         add_header Cache-Control "public, immutable";    │  │
│  │     }                                                      │  │
│  │                                                            │  │
│  │     # Deny hidden files                                     │  │
│  │     location ~ /\. { deny all; }                           │  │
│  │ }                                                          │  │
│  │                                                            │  │
│  │ # Rate limit zone                                          │  │
│  │ limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;│  │
│  │                                                            │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 8.2 Supervisor for Queue Workers

```
┌──────────────────────────────────────────────────────────────────┐
│                  SUPERVISOR CONFIG                                │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  deploy/supervisor.conf:                                         │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                                                            │  │
│  │ ; Horizon (queue worker manager)                           │  │
│  │ [program:pos-horizon]                                     │  │
│  │ process_name=%(program_name)s                             │  │
│  │ command=php /var/www/pos-retail/artisan horizon           │  │
│  │ autostart=true                                            │  │
│  │ autorestart=true                                          │  │
│  │ user=www-data                                             │  │
│  │ numprocs=1                                                │  │
│  │ redirect_stderr=true                                      │  │
│  │ stdout_logfile=/var/www/pos-retail/storage/logs/          │  │
│  │     horizon.log                                           │  │
│  │                                                            │  │
│  │ ; Laravel Scheduler (cron replacement)                     │  │
│  │ [program:pos-scheduler]                                   │  │
│  │ process_name=%(program_name)s                             │  │
│  │ command=php /var/www/pos-retail/artisan                   │  │
│  │     schedule:work                                          │  │
│  │ autostart=true                                            │  │
│  │ autorestart=true                                          │  │
│  │ user=www-data                                             │  │
│  │ numprocs=1                                                │  │
│  │ redirect_stderr=true                                      │  │
│  │ stdout_logfile=/var/www/pos-retail/storage/logs/          │  │
│  │     scheduler.log                                         │  │
│  │                                                            │  │
│  │ ; Queue worker backup (jika tidak pakai Horizon)           │  │
│  │ [program:pos-queue]                                       │  │
│  │ process_name=%(program_name)s_%(process_num)02d           │  │
│  │ command=php /var/www/pos-retail/artisan                   │  │
│  │     queue:work redis --tries=3 --backoff=5                 │  │
│  │ autostart=true                                            │  │
│  │ autorestart=true                                          │  │
│  │ user=www-data                                             │  │
│  │ numpros=8                                                 │  │
│  │ redirect_stderr=true                                      │  │
│  │ stdout_logfile=/var/www/pos-retail/storage/logs/          │  │
│  │     queue.log                                             │  │
│  │                                                            │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 8.3 MySQL Backup Cron

```
┌──────────────────────────────────────────────────────────────────┐
│                     BACKUP CRON                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  /etc/cron.d/pos-retail:                                         │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                                                            │  │
│  │ # Laravel scheduler (every minute)                          │  │
│  │ * * * * * www-data php /var/www/pos-retail/artisan          │  │
│  │     schedule:run >> /dev/null 2>&1                          │  │
│  │                                                            │  │
│  │ # MySQL backup langsung via mysqldump (opsi alternatif)    │  │
│  │ 0 2 * * * www-data mysqldump -u pos -p"password" pos |     │  │
│  │     gzip > /var/www/pos-retail/storage/app/backups/        │  │
│  │     pos-$(date +\%Y\%m\%d).sql.gz                          │  │
│  │                                                            │  │
│  │ # Delete backups older than 30 days                        │  │
│  │ 0 3 * * * www-data find /var/www/pos-retail/storage/      │  │
│  │     app/backups/ -name "*.sql.gz" -mtime +30 -delete       │  │
│  │                                                            │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 8.4 Deployment Options

```
┌──────────────────────────────────────────────────────────────────┐
│                  DEPLOYMENT OPTIONS                               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  OPTION 1: Laravel Forge (Disarankan)                             │
│  ─────────────────────────────────────                           │
│  • Automated provisioning: Nginx, PHP, MySQL, Redis, Supervisor │
│  • Push-to-deploy via Git (GitHub/GitLab)                        │
│  • SSL via Let's Encrypt (auto-renew)                            │
│  • Horizon monitoring dashboard                                  │
│  • $12/bulan (Hobby) + server cost                              │
│                                                                  │
│  OPTION 2: Manual VPS                                            │
│  ─────────────────────                                           │
│  Provider: DigitalOcean, Vultr, IDCloudhost, Niagahoster         │
│  Minimum spec: 2 vCPU, 4GB RAM, 60GB SSD                        │
│                                                                  │
│  Setup steps:                                                    │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 1. apt update && apt install nginx mysql-server            │  │
│  │    php8.3-fpm redis-server supervisor git composer        │  │
│  │                                                            │  │
│  │ 2. Clone repo: git clone <url> /var/www/pos-retail        │  │
│  │                                                            │  │
│  │ 3. composer install --no-dev --optimize-autoloader        │  │
│  │                                                            │  │
│  │ 4. cp .env.example .env && php artisan key:generate       │  │
│  │                                                            │  │
│  │ 5. php artisan migrate --force                             │  │
│  │    php artisan db:seed --class=DemoDataSeeder              │  │
│  │                                                            │  │
│  │ 6. npm ci && npm run build                                 │  │
│  │                                                            │  │
│  │ 7. php artisan storage:link                                │  │
│  │                                                            │  │
│  │ 8. Copy deploy/nginx.conf → /etc/nginx/sites-available/   │  │
│  │    ln -s .../sites-enabled/                                │  │
│  │    nginx -t && systemctl reload nginx                      │  │
│  │                                                            │  │
│  │ 9. Copy deploy/supervisor.conf →                           │  │
│  │    /etc/supervisor/conf.d/pos-retail.conf                  │  │
│  │    supervisorctl reread && supervisorctl update            │  │
│  │                                                            │  │
│  │ 10. chown -R www-data:www-data storage bootstrap/cache    │  │
│  │     chmod -R 775 storage bootstrap/cache                   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  OPTION 3: Laravel Cloud (Kurang cocok untuk POS)                │
│  ─────────────────────────────────────────────                   │
│  • Serverless — tidak cocok untuk background jobs/workers       │
│  • Tidak direkomendasikan untuk POS retail yang butuh queue    │
│    worker persistent                                            │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 8.5 Horizontal Scaling (Future)

```
┌──────────────────────────────────────────────────────────────────┐
│               HORIZONTAL SCALING (GROWTH PLAN)                    │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Fase 1: Single Server (Awal)                                     │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ [Nginx + PHP-FPM + MySQL + Redis + Supervisor]             │  │
│  │ 1 VPS, 2-4 vCPU, 4-8GB RAM                                 │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  Fase 2: Split Database (Menengah — 50+ toko)                     │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐       │
│  │ App Server   │    │ App Server   │    │ MySQL        │       │
│  │ (Nginx+PHP)  │    │ (Nginx+PHP)  │    │ (Primary +   │       │
│  │              │    │              │    │  Read Rep.)  │       │
│  └──────────────┘    └──────────────┘    └──────────────┘       │
│        │                    │                    │               │
│        └────────────────────┴────────────────────┘               │
│                             │                                    │
│                    ┌────────┴────────┐                            │
│                    │   Redis + LB    │                            │
│                    └─────────────────┘                            │
│                                                                  │
│  Fase 3: Multi-Region (Enterprise — 200+ toko)                    │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ • CDN (Cloudflare) untuk static assets                     │  │
│  │ • Load Balancer (Nginx / HAProxy)                          │  │
│  │ • MySQL Cluster / Master-Slave + ProxySQL                   │  │
│  │ • Redis Sentinel untuk failover                             │  │
│  │ • File storage: S3/DO Spaces (shared antar server)         │  │
│  │ • Queue: dedicated Redis instance                          │  │
│  │ • Monitoring: Laravel Telescope + Horizon + Grafana       │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 9. Ringkasan Teknologi

| Layer | Teknologi | Versi |
|-------|-----------|-------|
| Framework Backend | Laravel | 13.x |
| Admin Panel | Filament | 5.6 |
| Frontend Admin | Blade + Livewire + Tailwind CSS | — |
| Mobile App | Flutter | 3.x (Dart) |
| Database | MySQL | 8.x |
| Cache & Queue | Redis | 7.x |
| Web Server | Nginx | 1.26 |
| PHP | PHP-FPM | 8.3+ |
| API Auth | Laravel Sanctum | 4.x |
| PDF Generator | Barryvdh/DomPDF | 3.x |
| Excel Export | Filament Exporter / Laravel Excel | — |
| State Management (Flutter) | Provider / Riverpod | — |
| HTTP Client (Flutter) | Dio | 5.x |
| Local DB (Flutter) | sqflite | 2.x |
| Barcode Scanner (Flutter) | mobile_scanner | 6.x |
| Thermal Printer (Flutter) | esc_pos_bluetooth | 0.4 |
| Asset Bundling | Vite | 6.x |
| Queue Monitor | Laravel Horizon | 5.x |
| Debug Toolbar | Laravel Telescope | 5.x |
| Testing | PHPUnit + Laravel TestCase | 11.x |

---

## 10. Environment Variables

```env
# .env.example (key variables)
APP_NAME="POS Retail"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainanda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_retail
DB_USERNAME=pos_user
DB_PASSWORD=secure_password_here

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

SANCTUM_STATEFUL_DOMAINS=domainanda.com

# Notification Provider (format-based, user-input via admin UI)
NOTIFICATION_DEFAULT_CHANNEL=whatsapp
```

---

## 9. Multi-Outlet Architecture

### OutletScope — Automatic Query Filtering

Setiap model yang pakai `HasOutletScope` trait akan otomatis difilter berdasarkan outlet user yang login:

```
User login → getAccessibleOutletIds() 
  → jika punya '*' permission → lihat SEMUA outlet 
  → jika tidak → hanya outlet yang di-assign via user_outlet pivot
  → OutletScope filter: WHERE outlet_id IN (...) [OR outlet_id IS NULL jika nullable]
```

**Model yang di-scope:** Product (nullable), Category (nullable), DiscountTemplate (nullable), Order, PurchaseOrder, StockMovement, StockOpname, Shift, Attendance, HeldCart, KitchenTicket, Retur, RawMaterial, TableArea, TableResto.

**StockTransfer** menggunakan `HasMultiOutletScope` untuk filter `from_outlet_id` / `to_outlet_id` via `MultiOutletScope`.

### SystemSetting Per-Outlet

Settings bisa global (`outlet_id = NULL`) atau per-outlet. Method `SystemSetting::getValue('key', default, $outletId)`:
1. Cek setting khusus outlet tersebut
2. Fallback ke setting global
3. Fallback ke `$default`

### User Outlet Assignment

Tabel pivot `user_outlet` — satu user bisa diassign ke banyak outlet. Owner dengan permission `*` otomatis lihat semua outlet tanpa filter.

---

## 10. Latest Features (Updated 9 Juli 2026)

### Barcode Auto-Generate

Setiap product & product variant yang dibuat via admin panel akan otomatis mendapat:
- **SKU**: sequential (`SKU000001`, ...)
- **Barcode**: EAN-13 valid (prefix `899` + 9 digit random + checksum)

Implementasi di `Product::booted()` dan `ProductVariant::booted()`.

### POS Web — Kiosk Layout

Layout single-viewport pakai CSS Grid:
- 80% produk (scroll vertical internal)
- 20% keranjang (cart items scrollable, summary tetap)
- Scan barcode via **USB scanner** (keyboard capture) atau **kamera HP** (BarcodeDetector API)
- Produk 48 per halaman, pagination

### Dynamic Order Types

Tipe order (Walk-in, Member, Online, dll) dikonfigurasi via `system_settings` key `order_types` sebagai JSON array. Controller & API validation baca dari settings — tidak hardcode.

### Report PDF Export

Laporan bisa di-download sebagai PDF via `/export/laporan/*/pdf` menggunakan `ReportPdfService` + DomPDF.

### Payment Proof Upload (Customer Portal)

Customer bisa upload bukti pembayaran via `/portal/order/{id}/upload-proof`. Tersimpan di tabel `payment_proofs` dengan status pending → diverifikasi admin.

### Invoice PDF Download (Customer Portal)

Customer bisa download invoice PDF via `/portal/order/{id}/invoice`.

### API Rate Limiting

- Login: `throttle:10,1` (10 request/menit)
- API authenticated: `throttle:120,1` (120 request/menit)
- Webhooks: `throttle:30,1` (30 request/menit)

### Demo Data: 1000 Products

`DemoDataSeeder` menghasilkan:
- 25 kategori supermarket-style
- 25 brand
- 1000 produk (200 hardcode + 800 programmatic)
- 500 orders, 200 customers

### Exporters

- `CustomerExporter` — export data pelanggan
- `StockExporter` — export data stok

### Cash Drawer Transaction Resource

Resource read-only `Riwayat Kas` di navigation group Penjualan — menampilkan semua transaksi cash drawer dengan filter tipe.

### Navigation Reorganization (14 groups)

Struktur menu dirombak dari 11 group ke 14 group fokus retail:

| Group | Isi |
|-------|-----|
| 💰 Penjualan | Daftar Penjualan, Hold/Suspend, Retur, Riwayat Transaksi |
| 🛒 Pembelian | Purchase Order, Supplier Invoice |
| 📦 Inventory | Produk, Kategori, Brand, Unit, Stock Opname, Mutasi, Transfer, Raw Material, Barcode |
| 👥 Customer | Customer, Group, Membership Tier, Poin, Hadiah/Reward |
| 🚚 Supplier | Supplier |
| 🏪 Outlet | Outlet |
| 💳 Keuangan | Metode Bayar, Cicilan, Shift Kasir |
| 🎁 Promo | Discount Template |
| 📈 Laporan | Penjualan, Keuangan, Stok |
| 👨‍💼 Pegawai | User/Pegawai, Absensi |
| 🔔 Notifikasi | System Notifications |
| 🔗 Integrasi | Payment Gateway Provider |
| ⚙️ Pengaturan | Role & Permission, Audit Log |
| 📰 Website | Blog Post, Blog Category |

Resource restoran (KitchenTicket, TableResto, TableArea) disembunyikan dari navigasi.

### Multi-Outlet Fixes

- **DiscountTemplate** — migration fix: tambah kolom `outlet_id` yang sebelumnya hilang (model sudah pakai `HasOutletScope`)
- **StockTransfer** — sekarang pakai `HasMultiOutletScope` trait + `MultiOutletScope` global scope untuk filter `from_outlet_id` / `to_outlet_id`
- **HasMultiOutletScope** — trait yang sebelumnya dead code, sekarang aktif dengan global scope yang proper

### Payment Proof System

- Migration `payment_proofs` — order_id, file_path, amount, status
- Model `PaymentProof` dengan relasi `belongsTo(Order)`
- Customer portal: upload bukti bayar + download invoice PDF

### Tests — Workflow Coverage (8 test, all pass)

- Purchase Order workflow (draft → ordered → received)
- Stock Transfer antar outlet (draft → sent → received)
- Invalid order payload validation
- API rate limiting verification
- Customer portal auth gate
- Payment proof upload auth requirement
- Report PDF export auth requirement

### Customer & Stock Exporters

- `CustomerExporter` terpasang di ListCustomers
- `StockExporter` terpasang di ListProducts (export stok)

---

Dokumen ini adalah living document — update saat arsitektur berubah.

**Terakhir diperbarui:** 9 Juli 2026
