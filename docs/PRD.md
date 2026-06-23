# Product Requirements Document (PRD)
## POS Retail — Sistem Point of Sale untuk Toko Ritel

---

### Versi Dokumen

| Versi | Tanggal    | Penulis | Deskripsi Perubahan           |
|-------|------------|---------|-------------------------------|
| 1.0   | 2026-05-31 | Tim Dev | Draft awal PRD pos-retail     |

---

## Daftar Isi

1. [Overview Produk](#1-overview-produk)
2. [Target User & Persona](#2-target-user--persona)
3. [Fitur Inti](#3-fitur-inti)
   - [3.1 Kasir / Point of Sale (Flutter Android)](#31-kasir--point-of-sale-flutter-android)
   - [3.2 Manajemen Produk & Inventori](#32-manajemen-produk--inventori)
   - [3.3 Customer & Loyalty](#33-customer--loyalty)
   - [3.4 Supplier & Pembelian](#34-supplier--pembelian)
   - [3.5 Multi-Outlet](#35-multi-outlet)
   - [3.6 Laporan](#36-laporan)
   - [3.7 Role-Based Access Control](#37-role-based-access-control)
4. [Tech Stack](#4-tech-stack)
5. [Non-Functional Requirements](#5-non-functional-requirements)
6. [Arsitektur Sistem](#6-arsitektur-sistem)
7. [Database Design Highlights](#7-database-design-highlights)
8. [UI/UX Guidelines](#8-uiux-guidelines)
9. [Timeline & Milestone](#9-timeline--milestone)
10. [Asumsi & Batasan](#10-asumsi--batasan)
11. [Definisi Sukses](#11-definisi-sukses)

---

## 1. Overview Produk

**POS Retail** adalah sistem manajemen toko ritel terintegrasi yang mencakup aplikasi kasir (Android) dan panel administrasi (web). Sistem dirancang untuk menggantikan pencatatan manual / Excel dengan single source of truth yang real-time, akurat, dan bisa diakses dari mana saja.

### Jenis Toko yang Didukung
- Fashion & butik (pakaian, aksesori, sepatu)
- Elektronik & gadget (HP, laptop, aksesori)
- F&B / kuliner (restoran, kafe, bakery)
- Minimarket & sembako
- Toko obat & apotek
- Toko bangunan & material
- Dan toko ritel umum lainnya

### Value Proposition
| Sebelum | Sesudah |
|---------|---------|
| Catatan penjualan di buku / Excel, rawan hilang | Semua transaksi tercatat rapi di database, real-time |
| Stok tidak akurat, sering kehabisan tanpa tahu | Low stock alert otomatis, stok opname periodik |
| Tidak tahu produk mana yang paling laku | Best seller report per periode |
| Kasir bisa curang (markdown harga manual) | Role-based access, semua aksi tercatat di audit log |
| Customer tidak tercatat, tidak ada loyalitas | CRM + poin + membership tier otomatis |
| Multi cabang = data terpisah, ribet konsolidasi | Dashboard terpusat, stok transfer antar cabang |

---

## 2. Target User & Persona

### 2.1 Pemilik Toko (Owner)
- **Kebutuhan:** Melihat dashboard penjualan semua outlet, laba rugi, best seller, arus kas
- **Skill teknis:** Menengah (bisa pakai smartphone & laptop)
- **Frekuensi akses:** Harian (pagi lihat laporan kemarin, sore cek omset hari ini)
- **Device:** Laptop / tablet, kadang HP

### 2.2 Manager Toko
- **Kebutuhan:** Kelola stok, atur harga, kelola supplier, approval purchase order, rekap closing kasir
- **Skill teknis:** Menengah–tinggi
- **Frekuensi akses:** Sepanjang jam kerja
- **Device:** Laptop (admin panel)

### 2.3 Kasir
- **Kebutuhan:** Scan barcode, input transaksi, terima pembayaran, cetak struk, hold transaksi
- **Skill teknis:** Dasar–menengah
- **Frekuensi akses:** Sepanjang shift
- **Device:** Android tablet / HP (aplikasi Flutter)

### 2.4 Admin Gudang
- **Kebutuhan:** Terima barang dari supplier, input stok masuk, stok opname, kirim stok ke cabang
- **Skill teknis:** Dasar–menengah
- **Frekuensi akses:** Harian
- **Device:** Laptop (admin panel) / Android (scan barcode terima barang)

---

## 3. Fitur Inti

### 3.1 Kasir / Point of Sale (Flutter Android)

Aplikasi Flutter yang berjalan di Android (tablet 10" atau HP 6"+) sebagai antarmuka utama transaksi di depan.

#### 3.1.1 Barcode Scanner
- Dukungan kamera belakang sebagai scanner (via library `mobile_scanner`)
- Dukungan Bluetooth barcode scanner eksternal sebagai input keyboard
- Scan barcode → auto add ke cart (qty default 1, bisa diubah)
- Scan barcode yang sama → increment qty
- Barcode tidak ditemukan → opsi "tambah produk baru" (jika user punya izin)

#### 3.1.2 Keranjang Belanja (Cart)
- Daftar item dengan: foto thumbnail, nama produk, harga satuan, qty, subtotal
- Ubah qty: tombol +/- atau input manual
- Hapus item: swipe left atau icon trash
- Diskon per item: nominal (Rp) atau persentase (%)
- Catatan per item (opsional, misal "size XL warna merah")
- Ringkasan bawah: total item, subtotal, diskon total, pajak (jika ada), grand total

#### 3.1.3 Multi Payment
- **Tunai:** Input nominal bayar → hitung kembalian otomatis
- **QRIS:** Generate QR statis / dinamis → konfirmasi setelah customer bayar
- **Transfer Bank:** Pilih bank tujuan (BCA, Mandiri, BRI, BNI, dll.)
- **E-Wallet:** GoPay, OVO, DANA, ShopeePay, LinkAja
- **Kartu Debit/Kredit:** Input via EDC (manual input nomor approval)

#### 3.1.4 Split Payment
- Customer bisa bayar dengan kombinasi 2+ metode (contoh: tunai Rp 50.000 + QRIS Rp 30.000)
- UI modal split: tampilkan sisa yang harus dibayar → pilih metode → input nominal → ulangi sampai lunas
- Setiap split tercatat sebagai payment line item terpisah

#### 3.1.5 Cetak Struk & Kirim Digital
- **Cetak Bluetooth:** Koneksi ke thermal printer (ESC/POS) via Bluetooth, cetak struk 58mm / 80mm
- **Kirim WA:** Generate struk text/image → share via WhatsApp intent
- **Kirim Email:** Kirim struk PDF ke email customer (ambil dari database customer)
- Struk berisi: nama toko, alamat, tanggal, kasir, daftar item, total, pembayaran, kembalian

#### 3.1.6 Hold & Recall
- Kasir bisa **hold** transaksi yang belum selesai (customer masih memilih, nunggu teman, dll.)
- Hold list tampil di drawer → tap untuk melanjutkan (recall)
- Hold transaksi bertahan sampai akhir shift / dihapus manual
- Jumlah hold item tampil badge di tombol "Hold"

#### 3.1.7 Offline Mode → Auto Sync
- Saat koneksi internet putus, aplikasi Flutter tetap bisa transaksi (data produk & harga di-cache di lokal SQLite)
- Transaksi offline disimpan di local queue
- Begitu koneksi pulih → auto sync transaksi ke server (background, non-blocking)
- Conflict resolution: server timestamp sebagai acuan (last write wins)
- Indikator status koneksi di top bar (online hijau / offline merah)

#### 3.1.8 Fitur Tambahan Kasir
- **Pencarian produk:** Search bar dengan debounce, hasil real-time
- **Shortcut kategori:** Grid kategori di atas cart untuk filter cepat
- **Customer selection:** Pilih customer dari database sebelum checkout → auto apply diskon member
- **Shift management:** Kasir login di awal shift → semua transaksi tercatat per shift → closing di akhir shift (hitung cash drawer)

---

### 3.2 Manajemen Produk & Inventori

#### 3.2.1 CRUD Produk + Varian
- **Produk:** Nama, SKU (auto-generate), kategori, brand, supplier default, harga beli, gambar (multiple)
- **Varian:** Ukuran (S/M/L/XL), warna (merah/biru/hitam), dll. — kombinatorial
- Setiap varian punya: SKU sendiri, harga jual sendiri, stok sendiri, barcode sendiri
- Harga bisa di-set per varian atau inherit dari produk induk
- Gambar bisa di-set per varian (misal warna beda = foto beda)

#### 3.2.2 Multi Satuan
- Produk bisa dijual dalam beberapa satuan: pcs, box, lusin, karton, kg, gram, meter, liter
- Konversi antar satuan (1 box = 12 pcs, 1 lusin = 12 pcs, 1 kg = 1000 gram)
- Harga per satuan bisa berbeda (beli eceran Rp 5.000/pcs, beli box Rp 50.000/box → hemat)
- Di kasir: tampil opsi satuan saat scan / pilih produk

#### 3.2.3 Stok Masuk & Keluar
- **Stok Masuk:** Dari purchase order (PO), retur customer, stok transfer dari cabang, adjustment manual
- **Stok Keluar:** Dari penjualan (auto decrement), retur supplier, stok transfer ke cabang, rusak/hilang, adjustment manual
- Setiap mutasi stok tercatat di `stock_movements` dengan: produk, qty, tipe, referensi, user, timestamp
- Stok real-time: setiap transaksi langsung update `stock` field di tabel produk

#### 3.2.4 Stok Opname
- Periodik (misal bulanan) atau ad-hoc
- Admin pilih produk → input stok fisik → sistem bandingkan dengan stok sistem → selisih
- Selisih dicatat sebagai adjustment stok (masuk/keluar)
- History opname lengkap: siapa, kapan, selisih per produk

#### 3.2.5 Low Stock Alert
- Setiap produk punya **minimum stock threshold** (default 5, bisa diubah per produk)
- Saat stok ≤ threshold → muncul di dashboard widget "Low Stock Alert"
- Notifikasi ke Owner/Manager (opsional: via WA / Telegram)
- Bisa langsung klik → buat purchase order ke supplier

#### 3.2.6 Expiry Tracking
- Khusus F&B, obat, dan produk dengan masa kadaluarsa
- Setiap batch stok masuk punya tanggal expired
- Dashboard alert: "Expiring in 7 days" / "Expired"
- FEFO (First Expired First Out) suggestion saat kasir: produk yang hampir expired dijual dulu

#### 3.2.7 Harga Multi-Tier
- **Harga Eceran:** Harga normal untuk customer walk-in
- **Harga Grosir:** Harga untuk pembelian di atas qty tertentu (misal beli 6+ = diskon 10%)
- **Harga Member:** Per tier (Silver 5%, Gold 10%, Platinum 15%)
- Harga ditampilkan jelas di kasir: coret harga normal, tampil harga yang berlaku

---

### 3.3 Customer & Loyalty

#### 3.3.1 Database Customer
- Data: Nama, no HP (unique), email, alamat, tanggal lahir, jenis kelamin
- Riwayat pembelian lengkap (produk, nominal, frekuensi, tanggal)
- Total belanja seumur hidup (lifetime value)
- Segmentasi: new, active, dormant, churned (berdasarkan transaksi terakhir)

#### 3.3.2 Poin Reward
- Konfigurasi: Rp X pembelanjaan = 1 poin (misal Rp 1.000 = 1 poin) — bisa diubah di settings
- Poin bisa ditukar dengan diskon / produk gratis (konversi di settings)
- Poin expired setelah X bulan tidak ada transaksi (default 12 bulan)
- Notifikasi otomatis: "Poin Anda akan expired bulan depan!"

#### 3.3.3 Membership Tier
- **Silver:** Default untuk semua customer baru. Syarat: daftar. Benefit: diskon 2%, poin 1×.
- **Gold:** Syarat: total belanja > Rp 5.000.000 dalam 6 bulan. Benefit: diskon 5%, poin 1.5×, gratis ongkir area tertentu.
- **Platinum:** Syarat: total belanja > Rp 25.000.000 dalam 12 bulan. Benefit: diskon 10%, poin 2×, gratis ongkir, priority support, akses pre-order.
- Upgrade/downgrade otomatis berdasarkan kalkulasi bulanan
- Badge member muncul di struk dan aplikasi kasir

#### 3.3.4 Diskon Otomatis per Member
- Saat kasir pilih customer → sistem auto hitung diskon member yang berlaku
- Diskon bisa di-override manager (dengan alasan, tercatat di audit log)
- Promo / voucher bisa stacking dengan diskon member (konfigurasi: "bisa digabung" atau "tidak bisa")

---

### 3.4 Supplier & Pembelian

#### 3.4.1 Purchase Order (PO)
- Form PO: pilih supplier, pilih produk (bisa multiple), qty, harga beli, tanggal kirim estimasi
- Status PO: draft → dikirim → dikonfirmasi supplier → sebagian diterima → selesai
- Approval PO: jika total di atas threshold (setting), perlu approval Manager/Owner
- PO bisa dibuat otomatis dari low stock alert (sistem suggest qty berdasarkan rata-rata penjualan)

#### 3.4.2 Penerimaan Barang
- Scan / pilih PO → input qty yang diterima per item (bisa sebagian)
- Update stok otomatis setelah penerimaan
- Jika qty diterima < qty PO → PO status "sebagian diterima", bisa diterima lagi nanti
- Catat nomor batch & tanggal expired (jika produk dengan expiry)
- Generate barcode label untuk produk baru (opsional, langsung print)

#### 3.4.3 Retur Supplier
- Retur barang rusak / expired / tidak sesuai
- Kurangi stok otomatis
- Catat nominal retur sebagai pengurang hutang ke supplier
- Status: menunggu pickup → selesai

#### 3.4.4 Hutang AP (Accounts Payable)
- Setiap PO yang diterima → otomatis catat sebagai hutang ke supplier
- Jatuh tempo berdasarkan term pembayaran supplier (contoh: net 30 hari)
- Alert hutang jatuh tempo
- Pembayaran hutang dicatat → update sisa hutang
- Laporan aging AP: 0-30 hari, 31-60 hari, 61-90 hari, >90 hari

---

### 3.5 Multi-Outlet

#### 3.5.1 Dashboard Terpusat
- Owner bisa lihat gabungan semua outlet dalam satu dashboard
- Filter per outlet di setiap widget & laporan
- Perbandingan antar outlet (grafik batang: omset per outlet bulan ini)

#### 3.5.2 Stok Transfer Antar Cabang
- Outlet A kirim stok ke Outlet B
- Form transfer: pilih outlet tujuan, pilih produk & qty
- Status: request → approved → dikirim → diterima
- Saat diterima: stok Outlet A berkurang, Outlet B bertambah (auto)
- Biaya transfer bisa dicatat (opsional, untuk costing)

#### 3.5.3 Independent Pricing per Outlet
- Setiap outlet bisa punya harga jual berbeda (biaya operasional beda, target market beda)
- Default: inherit harga dari pusat, bisa override

---

### 3.6 Laporan

#### 3.6.1 Sales Summary
- Filter: tanggal (dari–sampai), outlet, kasir, customer, kategori
- Ringkasan: total transaksi, total item terjual, rata-rata per transaksi, omset, laba kotor
- Grafik: bar chart omset harian, line chart tren mingguan
- Tabel detail per transaksi (bisa drill-down lihat item)
- Export: PDF (format laporan), Excel (format mentah)

#### 3.6.2 Profit & Loss (Laba Rugi)
- Pendapatan: total penjualan (setelah diskon & retur)
- HPP (Harga Pokok Penjualan): total harga beli produk yang terjual
- Laba Kotor = Pendapatan – HPP
- Biaya operasional: gaji, sewa, listrik, dll. (input manual atau dari modul finance)
- Laba Bersih = Laba Kotor – Biaya Operasional
- Grafik: bar chart perbandingan pendapatan vs HPP vs laba

#### 3.6.3 Best Seller
- Top 10/25/50 produk berdasarkan: qty terjual / omset / laba
- Filter periode, outlet, kategori
- Grafik: horizontal bar chart
- Slow mover: produk dengan penjualan paling rendah (candidate diskon / hapus)

#### 3.6.4 Closing Cash (Tutup Kasir)
- Per shift: total transaksi tunai, non-tunai, selisih (input fisik vs sistem)
- Rekap per metode bayar
- Riwayat closing per kasir per shift
- Print laporan closing untuk diserahkan ke manager

#### 3.6.5 Export
- **PDF:** Format profesional dengan header toko, periode, logo. Gunakan Barryvdh/DomPDF.
- **Excel:** Export via Filament Exporter (`pxlrbt/filament-excel`), support CSV & XLSX
- Semua laporan bisa di-export dengan filter yang sedang aktif

---

### 3.7 Role-Based Access Control

| Izin                          | Owner | Manager | Kasir | Admin Gudang |
|-------------------------------|:-----:|:-------:|:-----:|:------------:|
| Dashboard semua outlet        |   ✅   |    ✅    |   ❌   |      ❌       |
| Dashboard outlet sendiri      |   ✅   |    ✅    |   ✅   |      ✅       |
| Transaksi penjualan           |   ✅   |    ✅    |   ✅   |      ❌       |
| Hold & recall                 |   ✅   |    ✅    |   ✅   |      ❌       |
| Diskon manual / override      |   ✅   |    ✅    |   ❌   |      ❌       |
| Void / batalkan transaksi     |   ✅   |    ✅    |   ❌   |      ❌       |
| Kelola produk                 |   ✅   |    ✅    |   ❌   |      ✅       |
| Stok opname                   |   ✅   |    ✅    |   ❌   |      ✅       |
| Kelola customer               |   ✅   |    ✅    |   ✅   |      ❌       |
| Purchase order                |   ✅   |    ✅    |   ❌   |      ✅       |
| Penerimaan barang             |   ✅   |    ✅    |   ❌   |      ✅       |
| Approval PO                   |   ✅   |    ✅    |   ❌   |      ❌       |
| Transfer stok antar cabang    |   ✅   |    ✅    |   ❌   |      ✅       |
| Lihat laporan                 |   ✅   |    ✅    |   ❌   |      ❌       |
| Export laporan                |   ✅   |    ✅    |   ❌   |      ❌       |
| Kelola user & role            |   ✅   |    ❌    |   ❌   |      ❌       |
| Konfigurasi sistem            |   ✅   |    ❌    |   ❌   |      ❌       |
| Lihat audit log               |   ✅   |    ❌    |   ❌   |      ❌       |

**Catatan:**
- Roles built-in tidak bisa dihapus, hanya bisa dinonaktifkan
- Owner bisa membuat custom role tambahan (via admin panel)
- Setiap permission bisa di-assign per role
- Semua aksi yang mengubah data tercatat di audit log

---

## 4. Tech Stack

### Backend (Laravel 13)
| Komponen        | Teknologi                               | Keterangan                         |
|-----------------|-----------------------------------------|------------------------------------|
| Framework       | Laravel 13                              | PHP 8.4+                           |
| Admin Panel     | Filament 5.6                            | Full-stack admin panel             |
| Templating      | Blade + Tailwind CSS 4                  | Custom theme responsive            |
| Reactive        | Livewire 4                              | Komponen dinamis di admin panel    |
| Database        | MySQL 8.4                               | Primary database                   |
| Cache           | Redis                                   | Queue, cache, session              |
| Queue           | Laravel Queue (Redis driver)            | Proses async (sync, notif, export) |
| API             | Laravel Sanctum                         | Token-based auth untuk Flutter     |
| Real-time       | Laravel Reverb                          | WebSocket untuk notifikasi         |
| PDF             | Barryvdh/DomPDF                         | Export laporan PDF                 |
| Excel           | pxlrbt/filament-excel                   | Export laporan Excel               |

### Frontend Admin (Filament + Blade)
| Komponen        | Teknologi               | Keterangan                             |
|-----------------|-------------------------|----------------------------------------|
| Panel           | Filament 5.6            | Admin panel dengan 9 navigation group  |
| Styling         | Tailwind CSS 4          | Custom theme premium, responsive       |
| Font            | Inter + JetBrains Mono  | Via Bunny CDN                          |
| Charts          | Chart.js                | Grafik di dashboard & laporan          |
| Icons           | Heroicons (via Blade)   | Icon set standar Filament              |

### Frontend Kasir (Flutter Android)
| Komponen        | Teknologi                    | Keterangan                         |
|-----------------|------------------------------|------------------------------------|
| Framework       | Flutter 3.29+                | Cross-platform Android             |
| State           | Riverpod                     | State management                   |
| HTTP Client     | Dio                          | API calls ke Laravel Sanctum       |
| Local DB        | Drift (SQLite)               | Offline cache produk & transaksi   |
| Scanner         | mobile_scanner               | Barcode via kamera                 |
| Bluetooth Print | esc_pos_printer / blue_print | Thermal printer 58mm/80mm          |
| Sync            | Custom sync engine           | Offline queue → auto sync          |

### DevOps & Tools
| Komponen        | Teknologi               | Keterangan                         |
|-----------------|-------------------------|------------------------------------|
| Version Control | Git + GitHub            | Source code management             |
| CI/CD           | GitHub Actions          | Automated testing & deployment     |
| Server          | Nginx + PHP-FPM         | Production environment             |
| Supervisor      | Supervisor              | Queue worker, scheduler, Reverb    |
| Monitoring      | Laravel Telescope       | Debugging & monitoring dev/staging |

---

## 5. Non-Functional Requirements

### 5.1 Offline Mode
- Aplikasi Flutter harus bisa beroperasi tanpa internet minimal 8 jam (satu shift kerja)
- Data produk (10.000 SKU) harus bisa di-cache di lokal SQLite
- Transaksi offline harus auto sync dalam waktu < 30 detik setelah koneksi pulih
- Conflict resolution: last-write-wins berdasarkan timestamp transaksi

### 5.2 Performance
- Waktu respons API: < 200ms untuk endpoint baca, < 500ms untuk endpoint tulis
- Loading halaman admin: < 2 detik (first paint)
- Scan barcode ke tampil di cart: < 500ms
- Cetak struk Bluetooth: < 3 detik setelah tekan "Bayar"
- Database query harus pakai index optimal untuk semua kolom yang sering di-query

### 5.3 Security
- Semua API endpoint di-protect dengan Sanctum token
- Password di-hash dengan Bcrypt (Laravel default)
- API key & secret di-encrypt at rest (Laravel encryption)
- Rate limiting: max 60 request/menit per token untuk endpoint umum
- SQL injection prevention: semua query pakai Eloquent / Query Builder (parameterized)
- XSS prevention: Blade auto-escape, Flutter input sanitization
- Audit log lengkap: siapa, apa, kapan, IP address, user agent

### 5.4 Auto-Backup
- Database backup otomatis setiap hari (scheduler: `php artisan backup:run`)
- Simpan backup di storage lokal + upload ke cloud storage (opsional, misal S3)
- Retensi: 7 backup harian + 4 backup mingguan + 3 backup bulanan
- Alert jika backup gagal (notifikasi ke Owner)

### 5.5 Audit Log
- Semua aksi CRUD di-record: user, aksi, model, ID, data sebelum, data sesudah, timestamp
- View transaksi → tidak di-audit (hanya baca)
- Audit log tidak bisa dihapus (bahkan oleh Owner)
- Retensi: 2 tahun, lalu diarsipkan ke file (opsional)

### 5.6 Responsive & Aksesibilitas (WCAG)
- Admin panel Filament wajib responsive: desktop (1440px), tablet (768px), mobile (414px)
- Touch targets minimum 38×38px di mobile (WCAG 2.5.5)
- Kontras warna minimum 4.5:1 untuk teks normal (WCAG 1.4.3)
- Keyboard navigasi: semua form bisa diakses tanpa mouse
- Screen reader: label ARIA di form input, alt text di gambar
- Reduced motion: `@media (prefers-reduced-motion: reduce)` di CSS

### 5.7 Skalabilitas
- Dukungan minimal 50 outlet per instalasi
- Dukungan minimal 100.000 SKU per database
- Dukungan minimal 5.000 transaksi per hari per outlet
- Arsitektur siap horizontal scaling (load balancer + multiple app server)

### 5.8 Ketersediaan (Availability)
- Target uptime: 99.5% (kecuali maintenance terencana)
- Maintenance window: Minggu 02:00–04:00 WIB
- Graceful degradation: jika server mati, aplikasi Flutter tetap bisa transaksi offline

---

## 6. Arsitektur Sistem

```
┌────────────────────────────────────────────────────────────┐
│                      CLIENT LAYER                          │
│  ┌──────────────────┐  ┌──────────────────────────────┐    │
│  │  Admin Panel (Web)│  │  Kasir App (Flutter Android) │    │
│  │  Laravel Blade    │  │  Riverpod + Dio + Drift      │    │
│  │  Filament + Livewire│ │  SQLite (offline cache)      │    │
│  └───────┬──────────┘  └──────────────┬───────────────┘    │
└──────────┼────────────────────────────┼────────────────────┘
           │        HTTPS (TLS 1.3)     │
           ▼                            ▼
┌────────────────────────────────────────────────────────────┐
│                      API GATEWAY                           │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              Nginx Reverse Proxy                      │  │
│  │          (SSL termination, rate limiting)             │  │
│  └───────────────────────┬──────────────────────────────┘  │
└──────────────────────────┼─────────────────────────────────┘
                           │
                           ▼
┌────────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER                         │
│  ┌──────────┐  ┌───────────┐  ┌──────────────────────────┐ │
│  │  Sanctum  │  │  Filament  │  │  Queue Worker (Redis)    │ │
│  │  API Auth │  │  Admin UI  │  │  - Sync data offline     │ │
│  │           │  │            │  │  - Send notifications    │ │
│  │           │  │            │  │  - Generate reports      │ │
│  │           │  │            │  │  - Backup database       │ │
│  └─────┬─────┘  └─────┬──────┘  └───────────┬──────────────┘ │
└────────┼──────────────┼─────────────────────┼───────────────┘
         │              │                     │
         ▼              ▼                     ▼
┌────────────────────────────────────────────────────────────┐
│                      DATA LAYER                             │
│  ┌──────────┐  ┌───────────┐  ┌──────────────────────────┐ │
│  │  MySQL 8 │  │  Redis    │  │  Storage (local / S3)     │ │
│  │  Primary │  │  Cache +  │  │  - Backup files           │ │
│  │  DB      │  │  Queue    │  │  - Product images         │ │
│  │          │  │           │  │  - Exported reports       │ │
│  └──────────┘  └───────────┘  └──────────────────────────┘ │
└────────────────────────────────────────────────────────────┘
```

### Komunikasi Flutter ↔ Laravel
- REST API via HTTPS dengan JSON payload
- Autentikasi: token Sanctum (disimpan aman di Flutter Secure Storage)
- Sync: POST `/api/v1/sync/batch` — kirim semua transaksi offline dalam satu request array, dapatkan response dengan status & ID server

---

## 7. Database Design Highlights

### 7.1 Tabel Utama (Core Entities)
```
outlets             — data cabang/toko
users               — user admin panel (Owner, Manager, Admin Gudang)
cashiers            — user aplikasi kasir (mobile)
customers           — data customer/member
categories          — kategori produk
brands              — merek produk
products            — produk (induk)
product_variants    — varian produk (size, warna, dll.)
product_units       — multi satuan & konversi
suppliers           — data supplier
```

### 7.2 Transaksi (Transaction Entities)
```
carts               — keranjang belanja (aktif + hold)
cart_items          — item dalam keranjang
orders              — transaksi penjualan (completed)
order_items         — item dalam transaksi
payments            — pembayaran (bisa multiple per order → split payment)
purchase_orders     — purchase order ke supplier
po_items            — item dalam PO
stock_movements     — mutasi stok (in/out/transfer/adjustment)
stock_transfers     — transfer stok antar outlet
```

### 7.3 Loyalty & Finance
```
memberships         — data membership customer (tier, poin)
point_transactions  — riwayat tambah/pakai poin
supplier_payables   — hutang AP ke supplier
payable_payments    — pembayaran hutang ke supplier
cash_drawers        — shift kasir & closing cash
```

### 7.4 Sistem
```
audit_logs          — audit trail semua aksi
notifications       — notifikasi (low stock, expired, dll.)
system_settings     — konfigurasi aplikasi (key-value)
sync_queue          — antrian sync offline Flutter
backups             — metadata backup database
```

---

## 8. UI/UX Guidelines

### 8.1 Admin Panel (Filament + Blade)

#### Navigation Groups (9 groups, mengikuti alur bisnis):
1. **Master Data** — Outlet, Kategori, Brand, Produk, Customer, Supplier
2. **Penjualan** — Orders, Payments, Carts (Hold)
3. **Pembelian** — Purchase Order, Penerimaan Barang, Retur Supplier
4. **Inventori** — Stok Opname, Stok Transfer, Stok Mutasi
5. **Finance** — Hutang AP, Pembayaran AP, Cash Drawer, COA
6. **Loyalty** — Membership, Poin, Promo & Voucher
7. **Laporan** — Sales, Profit & Loss, Best Seller, Closing Cash, Aging AP
8. **Sistem** — User, Role, Audit Log, System Settings, Backup
9. **Integrasi** — Provider (Payment, SMS, WA, Email), Webhook

#### Dashboard Widgets (per role):
| Widget              | Visible To              | Tipe      |
|---------------------|-------------------------|-----------|
| Stats Overview      | Semua                   | 4 cards   |
| Revenue Chart       | Owner, Manager          | Bar chart |
| Best Seller Donut   | Owner, Manager          | Donut     |
| Low Stock Alert     | Owner, Manager, Gudang  | Table     |
| Pending Approvals   | Owner, Manager          | Table     |
| Today Transactions  | Kasir                   | Table     |
| Expiring Products   | Manager, Gudang         | Table     |
| Cash Drawer Status  | Kasir, Manager          | Card      |

#### Theme:
- Custom Filament theme CSS (primary gradient indigo→violet)
- Responsive breakpoints: 640px, 768px, 1024px
- Sidebar collapsible, glass topbar, soft card shadows
- Dark mode support

### 8.2 Aplikasi Kasir (Flutter)

#### Halaman Utama:
1. **Dashboard Kasir** — Stat hari ini, shift info, quick shortcuts
2. **Transaksi** — Scan / cari produk → cart → checkout (halaman utama)
3. **Hold List** — Daftar transaksi yang di-hold
4. **Riwayat** — Transaksi hari ini (per kasir)
5. **Profil** — Info kasir, logout, sync status

#### Tampilan Cart:
- Top: search bar + scan button
- Tab kategori horizontal (bisa scroll)
- Grid produk (2 kolom di HP, 3-4 kolom di tablet)
- Cart panel: slide-up dari bawah (HP) atau side panel kanan (tablet)
- FAB "Hold" & "Checkout" di cart footer

#### Checkout Flow:
```
Cart Review → Select Customer (opsional) → 
Select Payment Method → Input Amount → 
(Repeat for Split Payment) → 
Print Struk / Kirim WA / Selesai
```

---

## 9. Timeline & Milestone

### Fase 0: Foundation (Minggu 1)
- Setup project Laravel 13 + Filament 5.6
- Setup database schema (migrations)
- Setup custom theme (Tailwind, responsive)
- Setup Sanctum API + Flutter project scaffold
- CI/CD pipeline (GitHub Actions)
- **Deliverable:** Admin panel login + Dashboard skeleton + Flutter login ke API

### Fase 1: Master Data (Minggu 2–3)
- Outlet, Kategori, Brand, Produk + Varian + Multi Satuan
- Customer database
- Supplier database
- System settings
- **Deliverable:** CRUD lengkap semua master data di admin panel

### Fase 2: Inventori (Minggu 4)
- Stok masuk/keluar/mutasi
- Stok opname
- Low stock alert
- Expiry tracking (batch)
- **Deliverable:** Manajemen stok lengkap + alert system

### Fase 3: Pembelian (Minggu 5)
- Purchase Order (CRUD + approval)
- Penerimaan barang
- Retur supplier
- Hutang AP + pembayaran
- **Deliverable:** Siklus pembelian supplier lengkap

### Fase 4: Kasir Flutter v1 (Minggu 6–8)
- Login + shift management
- Barcode scanner + pencarian produk
- Cart (add, edit qty, remove, diskon)
- Checkout: single payment (tunai, QRIS, transfer)
- Print struk Bluetooth
- Hold & recall
- Offline mode + auto sync
- **Deliverable:** Aplikasi kasir MVP siap uji coba

### Fase 5: Transaksi & Pembayaran (Minggu 9)
- Order management (admin panel view)
- Multi payment + split payment (Flutter v2)
- Kirim struk WA & email
- Void / batalkan transaksi
- Cash drawer + closing kasir
- **Deliverable:** Transaksi end-to-end selesai

### Fase 6: Loyalty (Minggu 10)
- Poin reward (tambah, tukar)
- Membership tier (silver/gold/platinum)
- Auto upgrade/downgrade tier
- Diskon otomatis per member
- **Deliverable:** Sistem loyalitas customer lengkap

### Fase 7: Multi-Outlet & Laporan (Minggu 11–12)
- Dashboard multi-outlet (pusat)
- Stok transfer antar cabang
- Sales summary + Profit & Loss
- Best seller + Slow mover
- Export PDF & Excel
- **Deliverable:** Multi-outlet + reporting selesai

### Fase 8: Polish & Testing (Minggu 13–14)
- Screenshot capture (Playwright) untuk marketing
- Halaman marketing landing (`/`)
- Halaman dokumentasi (`/docs`)
- Programmatic SEO routes
- Sitemap.xml + robots.txt
- Test coverage (feature + unit, target 40+ tests)
- Bug fixing & performance optimization
- **Deliverable:** MVP siap soft launch

### Fase 9: Soft Launch & Iterate (Minggu 15–16)
- Deployment production
- Onboarding user (Owner + Manager training)
- Feedback loop → bug fix → fitur minor
- **Deliverable:** Sistem live di 1 outlet percontohan

---

## 10. Asumsi & Batasan

### Asumsi
1. Setiap toko minimal punya koneksi internet (walaupun tidak selalu stabil) — offline mode menangani ketidakstabilan
2. Kasir menggunakan Android (minimal Android 8.0 / API 26)
3. Printer struk menggunakan ESC/POS-compatible thermal printer via Bluetooth
4. Barcode yang digunakan adalah barcode standar (EAN-13, UPC-A, CODE-128)
5. QRIS diasumsikan menggunakan QR statis (merchant display) — tidak ada integrasi callback pembayaran di fase MVP

### Batasan (Out of Scope MVP)
1. **Tidak ada Fitur Akuntansi Ganda (Double Entry)** — Hanya pencatatan hutang piutang sederhana. COA dan jurnal penuh di luar scope MVP.
2. **Tidak ada Integrasi e-Commerce** — Tidak ada sinkronisasi stok dengan Shopee/Tokopedia/dll.
3. **Tidak ada HR & Payroll** — Tidak ada modul penggajian, absensi, dll.
4. **Tidak ada Kitchen Display / F&B Production** — Fitur produksi (bill of materials) di luar scope.
5. **Tidak ada Integrasi Payment Gateway real-time** — MVP pakai pencatatan manual pembayaran non-tunai (QRIS, transfer, e-wallet). Callback otomatis bisa ditambahkan di fase selanjutnya.
6. **Tidak ada Multi-Tenant (SaaS)** — MVP adalah instalasi per toko/outlet. SaaS multi-tenant bisa dipertimbangkan di versi selanjutnya.

---

## 11. Definisi Sukses

### Kriteria Rilis MVP
- [ ] Aplikasi kasir Flutter bisa transaksi (scan → cart → bayar tunai → print struk)
- [ ] Admin panel bisa kelola produk, stok, customer, supplier
- [ ] Laporan sales summary bisa diakses Owner/Manager
- [ ] Offline mode berfungsi: transaksi tanpa internet, auto sync saat online kembali
- [ ] Minimal 1 toko riil menggunakan sistem selama 2 minggu tanpa masalah kritis
- [ ] Test coverage > 70% untuk business logic
- [ ] Semua halaman responsive di mobile & desktop

### Metrik Sukses (Post-Launch, 3 bulan)
- Zero data loss incident
- Waktu transaksi rata-rata < 30 detik (dari scan ke print struk)
- Akurasi stok 99%+ (dibanding stok fisik)
- NPS (Net Promoter Score) dari user > 50
- Crash rate aplikasi Flutter < 0.5%

---

## Lampiran

### A. Glosarium
| Istilah        | Definisi                                                     |
|----------------|--------------------------------------------------------------|
| SKU            | Stock Keeping Unit — kode unik produk                        |
| PO             | Purchase Order — pesanan ke supplier                         |
| AP             | Accounts Payable — hutang usaha                              |
| QRIS           | Quick Response Code Indonesian Standard — standar QR bayar  |
| ESC/POS        | Protokol printer termal standar Epson                        |
| FEFO           | First Expired First Out — stok kadaluarsa dulu keluar dulu   |
| HPP            | Harga Pokok Penjualan — modal barang yang terjual            |
| COA            | Chart of Accounts — bagan akun keuangan                      |
| Sanctum        | Package Laravel untuk API token authentication               |
| Riverpod       | State management library untuk Flutter                       |
| Drift          | Library SQLite untuk Flutter (type-safe)                     |

### B. Referensi
- [Laravel 13 Documentation](https://laravel.com/docs/13.x)
- [Filament 5.x Documentation](https://filamentphp.com/docs/5.x)
- [Flutter Documentation](https://flutter.dev/docs)
- [WCAG 2.2 Guidelines](https://www.w3.org/TR/WCAG22/)
- Architecture decision: Global CLAUDE.md preferences (No Hardcoded Providers, Programmatic SEO, Admin Panel Premium Theme, Clean Login)

---

**Dokumen ini adalah living document.** Akan diperbarui seiring feedback user, perubahan requirement, dan iterasi development.
