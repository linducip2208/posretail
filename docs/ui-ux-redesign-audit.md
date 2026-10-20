# Audit UI/UX Redesign — POS Retail

Tanggal: 2026-10-20. Branch: `main` (bersih, sinkron `origin/main`). Baseline tes: **131 passed / 326 assertions** (sqlite `:memory:`, `RefreshDatabase`).

## 1. Stack terkonfirmasi (dari file proyek, bukan asumsi)

| Komponen | Versi | Sumber |
|---|---|---|
| PHP | 8.3.30 | `php -v` |
| Laravel Framework | 13.29.0 | `php artisan --version` |
| Filament | 5.6.5 | `composer.json` |
| Tailwind CSS | 4.0.0 (via `@tailwindcss/vite`) | `package.json`, `vite.config.js` |
| Alpine.js | 3.17.4 | `package.json` |
| Chart.js | UMD lokal `public/vendor/chart.js/` + helper `PosChart` | `report-charts.blade.php` |
| Build | Vite (`app.css`, `portal.css`, `filament/admin/theme.css`) | `vite.config.js` |
| Font build | Bunny (Instrument Sans, Inter, JetBrains Mono — diunduh saat build, lokal) | `vite.config.js` |
| DB lokal | MySQL `pos_retail` (97 tabel) | `SHOW TABLES` |
| API mobile | REST `/api/v1` + `/api/pos/*`, kontrak dipakai Flutter | `routes/api.php` |

## 2. Arsitektur yang ada

- **Shell admin**: `AdminPanelProvider` (panel `admin`, auth custom `App\Filament\Auth\Login`) + tema `resources/css/filament/admin/theme.css` + override Blade `resources/views/vendor/filament-panels/`.
- **Otorisasi**: trait `AuthorizesByNavigation` (peta class→grup permission) + `HasRoles` + policies (`OrderPolicy` dsb). Jangan disentuh strukturnya.
- **Multi-outlet**: relasi `user_outlet`, `User::getAccessibleOutletIds()/accessibleOutlets()`, scope `OutletScope`/`MultiOutletScope`. Kolom string `role` lawas di-sync ke pivot via `User::booted()`; `HasRoles::hasPermission()` fallback `role === 'owner'`.
- **Dashboard**: halaman default Filament + ~20 widget (`app/Filament/Widgets/`): header, 12 stat, tabel (transaksi hari ini, approval, RFM, target, stok, supplier, dsb), chart (arus kas). Filter: `DashboardWidgetFilter` (outlet).
- **Laporan**: 12 halaman `Laporan*.php` + Blade `filament/pages/` + `PosChart` (bar/line/mixed/doughnut, sadar dark-mode) + export CSV/PDF/Excel (`ReportExportController`).
- **POS kasir**: `PosController` + `CheckoutService` + Blade `/pos` (offline queue, barcode USB/kamera, thermal print, display pelanggan).
- **PSEO/SEO/GEO**: `ProgrammaticSeoController` + `PseoService` (kota, fitur, industri), `SitemapController` (+ `/llms.txt`), `DocsController` (`/docs`), robots dinamis, IndexNow terjadwal.
- **Ikon**: set Tabler sendiri (`x-ti` + `TablerIcons::svg`). Jangan tambah library ikon.

## 3. File kunci per area

1. Shell/nav: `AdminPanelProvider.php`, `theme.css`, `views/vendor/filament-panels/components/layout/*`, `sidebar.blade.php`, `topbar.blade.php`.
2. Dashboard: `StatsOverview.php`, `KpiDashboardWidget.php`, `DashboardHeaderWidget.php` + `dashboard-header.blade.php`, `KasirTodayWidget.php`, `PaymentSummaryWidget.php`, `TopProductsWidget.php`, `ManagerChartWidget.php`, `DemandForecastWidget.php`, `SupplierScoreSummaryWidget.php`, `CashFlowForecastWidget.php`, dst.
3. Produk/inventori: `Filament/Resources/{Products,Categories,Brands,Units,StockOpnames,StockTransfers,StockMovements}/`.
4. Penjualan: `Orders/*`, `PosController.php`, `CheckoutService.php`, `ApprovalService.php`.
5. Pembelian: `PurchaseOrders/*`, `Suppliers/*`, `SupplierPayables/*`, `PurchaseOrder.php` (event terima → jurnal).
6. Pelanggan/loyalitas: `Customers/*`, `LoyaltyRewards/*`, `ReferralService.php`.
7. Laporan: `Filament/Pages/Laporan*.php` + `views/filament/pages/laporan-*.blade.php` + `partials/report-charts.blade.php`.
8. User/permission: `Users/*`, `Roles/*`, `HasRoles.php`, `User.php`.
9. Outlet/setting: `Outlets/*`, `Settings/PengaturanSistem.php`, `SystemSetting.php`.
10. Auth/form/tabel: `Filament/Auth/Login.php`, `vendor/.../layout/simple.blade.php` (login split), `Tables/*`, `Schemas/*`.

## 4. Defek UI/UX saat ini

1. Palet lama (`#1769F5`) diganti brief baru (`#0878F9` + navy + oranye) — token belum sinkron.
2. Sidebar hasil iterasi kemarin (chip warna-warni + gradien) melanggar brief enterprise (satu aksen, tanpa gradien).
3. Dashboard: ~20 widget menumpuk vertikal panjang; tabel transaksi harian penuh; belum ada chart penjualan ringkas; tidak ada filter tanggal/outlet global.
4. Beberapa tabel resource masih label Inggris/kolom berlebihan (Order/Produk/Pelanggan sudah dipoles; sisanya menyusul).
5. Badge/status belum konsisten soft di semua modul (laporan sudah, resource tables sebagian).
6. Login: panel kiri sudah branded tapi memakai palet lama.

## 5. Risiko fungsional & keamanan

- Jangan ubah: skema DB, event model (stok/jurnal/komisi), kontrak API, policy, middleware/CSRF/validasi.
- Jangan jalankan `migrate:fresh`/`db:wipe` (DB lokal berisi data demo; tes memakai sqlite terpisah — aman).
- Jangan expose kredensial demo di `/llms.txt` (sudah hanya email+role).
- Widget dashboard memakai query agregat berat — batasi (limit/pagination), jangan tambah query per-baris di Blade.

## 6. Urutan implementasi

1. Token + shell (sidebar/topbar) + login. 2. Dashboard (header ringkas, KPI 4 primer, chart penjualan, transaksi ringkas + link, panel stok/FEFO, breakdown bayar). 3. Tabel resource utama. 4. Form/empty/loading/notifikasi. 5. Responsif + a11y + performa. 6. Build + tes + dokumen implementasi.

## 7. Tes yang ada

`tests/Feature/*` (131 tes): checkout, idempotency, approval tidak langsung, workflow PO/transfer, retur, keamanan API, hardening, konektivitas fitur (`FeatureConnectivityTest`), SEO/sitemap. Jalankan penuh tiap fase besar.
