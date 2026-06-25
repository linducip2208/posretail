# POS Retail

**Sistem Point of Sale untuk Toko Ritel** — aplikasi kasir + admin panel terintegrasi untuk mengelola toko ritel modern.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php)
![Filament](https://img.shields.io/badge/Filament-3.x-FF6900)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-06B6D4?logo=tailwindcss)
![Flutter](https://img.shields.io/badge/Flutter-Android-02569B?logo=flutter)
![License](https://img.shields.io/badge/License-Proprietary-red)

---

## Overview

POS Retail adalah sistem manajemen toko ritel lengkap yang mencakup:

- **Admin Panel (Web)** — dashboard, manajemen produk, stok, customer, supplier, laporan
- **Kasir App (Android / Flutter)** — scan barcode, transaksi, cetak struk, hold order
- **Multi-Outlet** — kelola banyak cabang dari satu dashboard
- **CRM & Loyalty** — poin, membership tier, reward otomatis
- **Supplier & Purchasing** — purchase order, penerimaan barang, hutang supplier

> **Kasir App (Flutter):** source code aplikasi kasir Android berada di project terpisah `D:\projekflutter\pos_kasir`, terhubung ke backend ini lewat **API v1** (`/api/v1`). Lihat `README.md` di project tersebut untuk setup.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12, PHP 8.3+ |
| Admin Panel | Filament 3, Livewire |
| Frontend | TailwindCSS 4, Vite 8 |
| Database | MySQL 8 / MariaDB 10.11+ |
| Cache & Queue | Redis (opsional) |
| Kasir App | Flutter (Android) |
| Fonts | Inter, Instrument Sans, JetBrains Mono |

---

## Quick Start

```bash
# 1. Clone repository
git clone <repo-url> pos-retail
cd pos-retail

# 2. Install dependencies
composer install
npm install
npm run build

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Edit .env — set database credentials
# DB_CONNECTION=mysql
# DB_DATABASE=pos_retail
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Migrate & seed
php artisan migrate --force
php artisan db:seed --force

# 6. Create storage symlink
php artisan storage:link

# 7. Start dev server
php artisan serve --host=127.0.0.1 --port=8765
```

Akses admin panel di **http://127.0.0.1:8765/admin**

---

## Features

### Kasir / Point of Sale
- Barcode scanner (via kamera / Bluetooth barcode gun)
- Keranjang belanja dengan hold & recall transaksi
- Multiple payment methods (tunai, QRIS, kartu, split payment)
- Diskon per item & per transaksi (percentage / nominal)
- Cetak struk thermal (ESC/POS) via Bluetooth / Wi-Fi
- Refund & return transaksi

### Manajemen Produk & Inventori
- Master produk dengan variant (warna, ukuran, SKU)
- Barcode generation & print
- Kategori & brand management
- Unit of measure (pcs, box, kg, liter, dll.)
- Stok multi-outlet dengan transfer antar gudang/cabang
- Stock opname periodik dengan approval
- Low stock alert otomatis
- Stock movement log (in, out, transfer, adjustment)

### Customer & Loyalty
- Database customer dengan riwayat transaksi
- Customer grouping (member, reseller, wholesale)
- Poin otomatis per transaksi
- Membership tier (Silver, Gold, Platinum)
- Loyalty reward redemption
- Customer communication log

### Supplier & Purchasing
- Supplier database dengan kontak & terms
- Purchase order workflow
- Penerimaan barang (goods receipt)
- Hutang supplier tracking
- Supplier performance report

### Multi-Outlet
- Kelola banyak outlet / cabang dalam satu sistem
- Dashboard per outlet & konsolidasi
- Transfer stok antar outlet
- Perbandingan performa outlet

### Laporan
- Laporan penjualan harian / mingguan / bulanan
- Best seller & slow moving products
- Profit margin per produk / kategori
- Laporan kasir (shift summary)
- Laporan stok & stock opname
- Export PDF & Excel

### Role-Based Access Control
- Owner — full access + dashboard konsolidasi
- Manager — kelola stok, harga, supplier, laporan
- Admin — input data master, kelola customer
- Kasir — POS transaksi saja (terbatas)
- Setiap aksi tercatat di audit log

---

## Documentation

| Dokumen | Path |
|----------|------|
| Product Requirements | [docs/PRD.md](docs/PRD.md) |
| Entity Relationship Diagram | [docs/ERD.md](docs/ERD.md) |
| Architecture Guide | [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) |
| Deployment Guide | [DEPLOYMENT.md](DEPLOYMENT.md) |
| API Documentation | `/docs` (public page) |

---

## Demo Accounts

| Role | Email | Password | Scope |
|------|-------|----------|-------|
| Owner | owner@pos-retail.test | password | Full access, semua outlet |
| Manager | manager@pos-retail.test | password | Stok, harga, supplier, laporan |
| Admin | admin@pos-retail.test | password | Master data, customer |
| Kasir | kasir@pos-retail.test | password | POS transaksi (outlet 1) |
| Gudang | gudang@pos-retail.test | password | Inventori, stok, transfer |

> **Login:** [http://127.0.0.1:8765/admin/login](http://127.0.0.1:8765/admin/login)

---

## Deployment

Lihat panduan deployment lengkap di [DEPLOYMENT.md](DEPLOYMENT.md) untuk:
- Server requirements & setup
- Nginx configuration
- Supervisor for queue workers & scheduler
- MySQL backup cron
- SSL via Certbot
- Post-deploy checklist

---

## Scheduler / Automation

| Command | Schedule | Deskripsi |
|---------|----------|-----------|
| Low stock alert | Setiap jam | Kirim notifikasi produk stok menipis |
| Backup database | Daily 2 AM | mysqldump terkompresi + retensi 30 hari |
| Loyalty point expiry | Daily | Hapus poin kadaluarsa |
| Stok opname reminder | Weekly | Notifikasi untuk jadwal stok opname |

---

## License

Proprietary. Source code dilindungi License v3 (whitelabel.co.id).

Dilarang mendistribusikan ulang, memodifikasi, atau menggunakan source code ini tanpa izin tertulis.
