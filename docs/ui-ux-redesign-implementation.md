# Implementasi UI/UX Redesign — POS Retail Enterprise

Tanggal: 2026-10-20. Branch: `main`.

## 1. Token & shell (`theme.css`, `AdminPanelProvider.php`)

- Token enterprise terpusat `--pos-*`: primer `#0878F9`, hover `#0666D6`, navy `#0B2854`/`#173B70`, aksen oranye `#FF9800` (hanya ikon POS Kasir + aksen headline login), bg `#F3F7FC`, teks `#172B4D`/`#64748B`, border `#E2EAF4`, sukses `#16A34A`, warning `#F59E0B`, danger `#DC2626`, info `#0284C7`.
- Sidebar putih 240px, menu aktif latar biru-muda + teks biru + indikator kiri, ikon outline 18px, label 13px, judul grup uppercase. Chip warna-warni + gradien dari iterasi lama dicabut.
- Topbar putih 56px, border bawah tipis. Tombol primer radius 8px + focus ring. Badge pill soft. Tabel 13px/header tinted/baris ±42px. Kartu radius 10px + shadow tinted ringan. Stat KPI: label 12px, angka 22px tabular.
- Panel provider: warna Filament = token, `sidebarWidth('15rem')`, darkMode tetap nonaktif (aplikasi terang sesuai brief).

## 2. Dashboard

- `StatsOverview`: dipangkas 7 → 4 KPI primer (Pendapatan, Transaksi, Rata-rata, Pending) + hapus 4 query agregat yang tak terpakai (lebih hemat).
- `RecentOrders`: limit 10 → 7 + tombol `Lihat Semua` ke Daftar Penjualan.
- `ManagerChartWidget` + `CashFlowForecastWidget`: warna seri → `#0878F9`.
- `dashboard-header.blade.php`: judul ganda dihapus (tinggal `Dasbor` bawaan halaman), tombol diselaraskan ke token.
- Widget lain (approval, stok/FEFO, target, supplier, RFM, dsb) tidak diubah strukturnya — hanya mewarisi token.

## 3. Login admin (`simple.blade.php`)

- Panel kiri: logo + headline navy dengan aksen biru/oranye + deskripsi + 4 checklist fitur + kartu poster. Panel kanan putih, form + proteksi (CSRF/session/throttle) tidak berubah. Mobile: form prioritas (CSS responsif bawaan).

## 4. Verifikasi yang benar-benar dijalankan

| Cek | Hasil |
|---|---|
| `npm run build` | sukses |
| `php artisan test` (penuh) | **131 passed / 326 assertions** |
| `vendor/bin/pint` (5 file diubah) | fixed, diff hanya gaya |
| Screenshot 1366×768 dashboard | OK: sidebar/token/KPI/tabel/badge |
| Screenshot 1366×768 login | OK: panel brand + form |
| Screenshot 390×844 + 768×1024 | OK, `overflowX=0`, tanpa 500 |
| `route:list` panel | tidak berubah |

## 5. Batasan & sisa kerja jujur

- Filter tanggal/outlet global dashboard belum ada (widget memakai cakupan outlet otomatis + hari berjalan). Menambah filter global butuh state Livewire bersama antar widget — ditunda, bukan dihapus.
- Label Indonesia tabel resource di luar Order/Produk/Pelanggan belum disapu (21 sidebar resource sudah).
- Mode gelap nonaktif; CSS `.dark` dipertahankan minimal, tidak diuji menyeluruh.
- Screenshot produksi (`posretail.whitelabel.co.id`) belum diambil — verifikasi akhir di staging/produksi masih diperlukan setelah deploy + `config:cache`.
- Tidak ada perubahan skema DB, API, policy, atau business logic (diff hanya CSS/Blade/properti label + query yang dihapus karena tak terpakai).

## 6. Deploy aman

`down → pull → composer install --no-dev → migrate --force → (tidak perlu seed) → optimize:clear → config/route/view:cache → up`. Tanpa `migrate:fresh`, tanpa truncate.
