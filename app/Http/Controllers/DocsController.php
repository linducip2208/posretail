<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DocsController extends Controller
{
    public function index(): View
    {
        $demoAccounts = $this->demoAccounts();
        $tutorial = $this->tutorial();
        $features = $this->features();
        $seoMeta = $this->seoMeta();

        return view('pseo.docs-index', compact(
            'demoAccounts',
            'tutorial',
            'features',
            'seoMeta'
        ));
    }

    protected function demoAccounts(): array
    {
        return [
            [
                'role' => 'Owner',
                'email' => 'owner@pos-retail.test',
                'password' => 'password',
                'scope' => 'Akses penuh — semua outlet, semua laporan, pengaturan sistem, approval transaksi',
            ],
            [
                'role' => 'Manager',
                'email' => 'manager@pos-retail.test',
                'password' => 'password',
                'scope' => 'Kelola data master, laporan, approval transaksi, manajemen tim',
            ],
            [
                'role' => 'Admin',
                'email' => 'admin@pos-retail.test',
                'password' => 'password',
                'scope' => 'Kelola data master, transaksi, inventori, laporan harian',
            ],
            [
                'role' => 'Kasir',
                'email' => 'kasir@pos-retail.test',
                'password' => 'password',
                'scope' => 'Transaksi penjualan, scan barcode, cetak struk, lihat produk & stok',
            ],
            [
                'role' => 'Gudang',
                'email' => 'gudang@pos-retail.test',
                'password' => 'password',
                'scope' => 'Kelola stok, stock opname, purchase order, transfer stok, mutasi',
            ],
        ];
    }

    protected function tutorial(): array
    {
        return [
            [
                'phase' => 1,
                'title' => 'Setup Awal',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'steps' => [
                    'Buka halaman <strong>/admin</strong> dan login dengan akun demo di atas.',
                    'Tambah <strong>Outlet</strong> — ini adalah toko/cabang tempat Anda beroperasi. Isi nama, alamat, telepon, dan status aktif.',
                    'Atur <strong>System Settings</strong> — tentukan pajak default, mata uang, format nota, dan threshold approval.',
                    'Tambah <strong>User</strong> — buat akun untuk kasir, admin gudang, dan manager dengan role yang sesuai.',
                    'Assign user ke outlet melalui <strong>User Outlet</strong> — tentukan user mana yang bertugas di outlet mana.',
                ],
            ],
            [
                'phase' => 2,
                'title' => 'Input Master Data',
                'icon' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-3.75',
                'steps' => [
                    'Tambah <strong>Kategori</strong> produk — misal: Makanan, Minuman, Elektronik, ATK. Bisa nested (sub-kategori).',
                    'Tambah <strong>Brand</strong> — merek produk seperti Indomie, Samsung, SariWangi.',
                    'Tambah <strong>Unit/Satuan</strong> — Pcs, Box, Kardus, Kg, Liter, Sachet, Botol, Kaleng, Pack.',
                    'Tambah <strong>Produk</strong> — lengkapi nama, kategori, brand, harga beli, harga jual, harga grosir, harga member, stok minimum & maksimum.',
                    '<strong>Barcode & SKU otomatis</strong> — setiap produk baru langsung dapat barcode EAN-13 (prefix 899) dan SKU sequential. Tidak perlu input manual, siap discan.',
                    'Tambah <strong>Varian Produk</strong> jika produk memiliki pilihan (warna, ukuran, rasa) — stok per varian, barcode otomatis juga.',
                    'Tambah <strong>Customer Group</strong> — Regular, Member, Reseller, Grosir. Tentukan diskon per grup.',
                    'Tambah <strong>Customer/Pelanggan</strong> — nama, telepon, email, alamat, grup pelanggan.',
                    'Tambah <strong>Supplier</strong> — pemasok barang, lengkap dengan kontak dan alamat.',
                    'Tambah <strong>Metode Pembayaran</strong> — Tunai, Debit, QRIS, Transfer Bank.',
                ],
            ],
            [
                'phase' => 3,
                'title' => 'Transaksi Penjualan',
                'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
                'steps' => [
                    'Buka menu <strong>Orders</strong> dan klik <strong>Buat Pesanan Baru</strong>.',
                    'Pilih outlet tujuan transaksi dan customer (opsional — bisa walk-in).',
                    'Tambah item produk ke keranjang — scan barcode atau pilih dari daftar. Tentukan quantity dan diskon per item.',
                    'Sistem otomatis menghitung subtotal, diskon total, pajak, dan grand total.',
                    'Pilih status pesanan: <strong>Pending → Diproses → Selesai → Dibatalkan</strong>.',
                    'Lakukan <strong>Pembayaran</strong> — pilih metode bayar, masukkan jumlah dibayar, sistem hitung kembalian.',
                    'Pesanan selesai — stok produk otomatis berkurang, poin loyalitas pelanggan bertambah.',
                    'Cetak struk/nota dari halaman detail order.',
                ],
            ],
            [
                'phase' => 4,
                'title' => 'Pembelian & Restock',
                'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
                'steps' => [
                    'Buka menu <strong>Purchase Orders</strong> dan buat PO baru.',
                    'Pilih supplier dan outlet tujuan restock.',
                    'Tambah item yang akan dibeli — cari produk, tentukan quantity dan harga beli.',
                    'Simpan PO dengan status <strong>Draft → Dipesan → Diterima</strong>.',
                    'Saat barang diterima, ubah status PO jadi <strong>Diterima</strong> — stok produk otomatis bertambah.',
                    'Pantau <strong>Stok Minimum</strong> di halaman produk — sistem memberi peringatan saat stok di bawah minimum.',
                ],
            ],
            [
                'phase' => 5,
                'title' => 'Manajemen Inventori',
                'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
                'steps' => [
                    'Lakukan <strong>Stock Opname</strong> secara berkala — hitung fisik vs stok sistem, catat selisih.',
                    'Input hasil opname per produk — sistem mencatat selisih dan menghasilkan laporan akurasi stok.',
                    'Gunakan <strong>Stock Transfer</strong> untuk memindahkan stok antar outlet.',
                    'Pantau <strong>Stock Movement</strong> — setiap perubahan stok tercatat (penjualan, pembelian, transfer, opname, adjustment).',
                    'Filter mutasi stok berdasarkan produk, outlet, tipe pergerakan, dan rentang tanggal.',
                ],
            ],
            [
                'phase' => 6,
                'title' => 'Program Loyalitas',
                'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
                'steps' => [
                    'Tambah <strong>Loyalty Rewards</strong> — hadiah yang bisa ditukar pelanggan dengan poin.',
                    'Tentukan poin yang dibutuhkan per reward, stok reward, dan status aktif.',
                    'Setiap transaksi otomatis menghasilkan <strong>Loyalty Points</strong> untuk pelanggan (jika pelanggan dipilih saat order).',
                    'Lihat riwayat poin pelanggan — earned dari transaksi, redeemed untuk reward.',
                    'Pelanggan dengan total belanja tinggi naik grup (Regular → Member → Reseller) otomatis berdasarkan konfigurasi.',
                ],
            ],
            [
                'phase' => 7,
                'title' => 'Laporan & Analisis',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                'steps' => [
                    'Buka menu <strong>Laporan</strong> — tersedia laporan penjualan, pembelian, inventori, dan loyalitas.',
                    'Filter laporan berdasarkan outlet dan rentang tanggal.',
                    'Lihat <strong>Chart Penjualan</strong> — revenue harian/mingguan/bulanan.',
                    'Lihat <strong>Top Produk</strong> — produk paling laris berdasarkan quantity dan revenue.',
                    'Lihat <strong>Laporan Stok</strong> — produk dengan stok menipis, overstock, dan pergerakan stok.',
                    'Export laporan ke <strong>PDF</strong> atau <strong>Excel</strong> untuk analisis lebih lanjut.',
                ],
            ],
            [
                'phase' => 8,
                'title' => 'Sistem & Keamanan',
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'steps' => [
                    'Kelola <strong>User</strong> — tambah, edit, nonaktifkan akun pengguna.',
                    'Atur <strong>Role</strong> user — Owner, Admin, Kasir, Gudang, Manager.',
                    'Konfigurasi <strong>Pengaturan Sistem</strong> — pajak, mata uang, logo usaha, approval threshold.',
                    'Upload <strong>Logo Usaha</strong> — tampil di admin panel dan struk.',
                    'Pantau <strong>Absensi Pegawai</strong> — clock in/out, status hadir/terlambat/izin/sakit.',
                    'Jalankan <strong>Backup Database</strong> secara berkala melalui scheduler.',
                ],
            ],
            [
                'phase' => 9,
                'title' => 'Pengaturan Meja & Tipe Order',
                'icon' => 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5',
                'steps' => [
                    'Tambah <strong>Area Meja</strong> — Indoor, Outdoor, VIP, Lantai 1, dll.',
                    'Tambah <strong>Meja</strong> — nama, kode, kapasitas, area, status (tersedia/terpakai/reserved).',
                    'Saat transaksi Dine In, pilih meja yang digunakan pelanggan.',
                    'Status meja otomatis berubah saat dipakai dan selesai.',
                    'Gunakan <strong>Tipe Order</strong> — Dine In, Takeaway, Delivery — sesuai kebutuhan.',
                    '<strong>Nomor Antrian</strong> otomatis untuk Takeaway dan Dine In.',
                ],
            ],
            [
                'phase' => 10,
                'title' => 'Bahan Baku & Recipe',
                'icon' => 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
                'steps' => [
                    'Tambah <strong>Bahan Baku</strong> di menu Raw Materials — tepung, gula, minyak, dll.',
                    'Catat harga per unit, stok saat ini, dan stok minimum.',
                    'Buat <strong>Recipe Items</strong> — hubungkan produk dengan bahan bakunya.',
                    'Tentukan quantity bahan baku yang dibutuhkan per produk.',
                    'Pantau stok bahan baku — alert saat di bawah minimum.',
                ],
            ],
            [
                'phase' => 11,
                'title' => 'Diskon, Cicilan & Uang Muka',
                'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'steps' => [
                    'Buat <strong>Template Diskon</strong> — percent, fixed, atau Buy X Get Y.',
                    'Tentukan minimal pembelian, tanggal berlaku, dan status aktif.',
                    'Untuk cicilan: aktifkan <strong>Installment</strong> di form order.',
                    'Pilih periode cicilan: Mingguan, 2-Mingguan, atau Bulanan.',
                    'Tentukan jumlah cicilan — sistem otomatis generate jadwal bayar.',
                    'Gunakan <strong>Uang Muka (DP)</strong> — pelanggan bayar sebagian di awal.',
                    'Pantau <strong>Sisa Pembayaran</strong> di detail order.',
                ],
            ],
            [
                'phase' => 12,
                'title' => 'Integrasi & POS Web',
                'icon' => 'M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M12 7.5h.008v.008H12V7.5Zm-3 0h.008v.008H9V7.5Zm6 0h.008v.008H15V7.5Z',
                'steps' => [
                    'Konfigurasi <strong>Payment Gateway</strong> di menu Providers — Midtrans, Xendit, dll.',
                    'Aktifkan <strong>QRIS</strong> untuk pembayaran digital.',
                    'Gunakan <strong>POS Web</strong> di <code>/pos</code> — antarmuka kasir cepat via browser dengan layout 80/20 (produk/kranjang).',
                    'Produk grid bisa di-<strong>scroll vertikal</strong> — lihat semua 1000+ produk tanpa pindah halaman.',
                    'Scan barcode dengan <strong>USB Scanner</strong> (auto-detect) atau <strong>Kamera HP</strong> — produk langsung masuk keranjang.',
                    'Barcode produk <strong>auto-generate</strong> saat create di admin panel — format EAN-13 valid, langsung bisa discan.',
                    'Atur <strong>Tipe Order</strong> (Walk-in, Member, Online) via System Settings — fully dynamic, tidak hardcode.',
                    'Cetak struk via <strong>Bluetooth Thermal Printer</strong> atau <strong>Browser Print</strong>.',
                    'Integrasikan dengan <strong>Flutter Mobile App</strong> untuk Android kasir.',
                ],
            ],
        ];
    }

    protected function features(): array
    {
        return [
            [
                'group' => '💰 Penjualan',
                'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
                'items' => [
                    [
                        'title' => 'Daftar Penjualan',
                        'desc' => 'Lihat dan kelola semua transaksi penjualan dari semua outlet. Filter, search, dan update status pesanan.',
                        'bullets' => ['Nomor order unik auto-generate', 'Filter: outlet, customer, status, tanggal', 'Detail item, diskon, pajak per order', 'Riwayat pembayaran per order', 'Export data ke Excel'],
                        'screenshot' => 'orders',
                    ],
                    [
                        'title' => 'Point of Sale',
                        'desc' => 'Antarmuka kasir kiosk single-viewport: 80% produk yang bisa discroll vertikal, 20% keranjang dengan subtotal tetap. Scan barcode USB/kamera.',
                        'bullets' => ['Layout kiosk 80/20 — produk scroll vertikal', 'Scan barcode USB atau kamera', 'Barcode & SKU auto-generate', '48 produk per halaman', 'Kalkulasi otomatis: subtotal → pajak → total', 'Multi-metode pembayaran', 'Cetak struk thermal'],
                        'screenshot' => 'order-create',
                    ],
                    [
                        'title' => 'Hold / Suspend',
                        'desc' => 'Tahan transaksi yang belum selesai. Pelanggan bisa lanjut belanja nanti — keranjang tersimpan.',
                        'bullets' => ['Simpan keranjang sementara', 'Label untuk identifikasi', 'Lanjutkan kapan saja', 'Auto-expire setelah 24 jam'],
                        'screenshot' => 'orders',
                    ],
                    [
                        'title' => 'Retur Penjualan',
                        'desc' => 'Proses pengembalian barang dengan alasan dan status approval. Stok otomatis kembali.',
                        'bullets' => ['Customer return & supplier return', 'Alasan retur wajib', 'Approval workflow', 'Stok auto-kembali'],
                        'screenshot' => 'orders',
                    ],
                ],
            ],
            [
                'group' => 'Transaksi',
                'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
                'items' => [
                    [
                        'title' => 'Point of Sale',
                        'desc' => 'Antarmuka kasir kiosk single-viewport: 80% produk yang bisa discroll vertikal, 20% keranjang dengan subtotal yang tetap di tempat. Transaksi selesai dalam hitungan detik.',
                        'bullets' => [
                            'Layout kiosk 80/20 — produk full scroll vertikal',
                            'Scan barcode USB atau kamera — langsung ke keranjang',
                            'Barcode & SKU auto-generate di setiap produk baru',
                            '1000+ produk diload per halaman — scroll tanpa henti',
                            'Kalkulasi otomatis: subtotal → diskon → pajak → total',
                            'Multi-metode pembayaran dalam satu transaksi',
                            'Hitung kembalian otomatis',
                            'Cetak struk termal via Bluetooth / browser',
                            'Tipe order dynamic via System Settings',
                        ],
                        'screenshot' => 'order-create',
                    ],
                    [
                        'title' => 'Manajemen Pesanan',
                        'desc' => 'Lacak semua pesanan dari semua outlet. Filter, search, dan kelola status pesanan dengan mudah.',
                        'bullets' => [
                            'Nomor order unik auto-generate',
                            'Filter: outlet, customer, status, tanggal',
                            'Detail item, diskon, pajak per order',
                            'Riwayat pembayaran per order',
                            'Batalkan pesanan dengan alasan',
                            'Export data order ke Excel',
                        ],
                        'screenshot' => 'orders',
                    ],
                    [
                        'title' => 'Pembayaran',
                        'desc' => 'Setiap transaksi tercatat lengkap dengan metode bayar, jumlah, dan kembalian. Support split payment.',
                        'bullets' => [
                            'Multi-payment dalam satu order',
                            'Auto-hitung kembalian',
                            'Status: Lunas, DP, Belum Bayar',
                            'Riwayat pembayaran per customer',
                            'Rekap kas per outlet per hari',
                        ],
                        'screenshot' => 'orders',
                    ],
                ],
            ],
            [
                'group' => '🛒 Pembelian',
                'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
                'items' => [
                    [
                        'title' => 'Purchase Order (PO)',
                        'desc' => 'Buat dan lacak pesanan pembelian ke supplier. Status PO: Draft → Dipesan → Diterima. Stok otomatis update saat PO diterima.',
                        'bullets' => [
                            'Workflow: Draft → Dipesan → Dikirim → Diterima',
                            'Auto-update stok saat PO diterima',
                            'Harga beli per item tercatat',
                            'Riwayat PO per supplier',
                            'Total biaya pembelian otomatis',
                            'Filter PO berdasarkan status dan supplier',
                        ],
                        'screenshot' => 'purchase-orders',
                    ],
                ],
            ],
            [
                'group' => '📦 Inventory',
                'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
                'items' => [
                    [
                        'title' => 'Stock Opname',
                        'desc' => 'Lakukan penghitungan fisik stok secara berkala dan bandingkan dengan catatan sistem. Selisih tercatat dan stok terkoreksi.',
                        'bullets' => [
                            'Buat sesi opname per outlet',
                            'Input stok fisik vs stok sistem',
                            'Hitung selisih otomatis (surplus/deficit)',
                            'Approval sebelum koreksi diterapkan',
                            'Laporan akurasi stok per periode',
                        ],
                        'screenshot' => 'stock-opnames',
                    ],
                    [
                        'title' => 'Mutasi Stok',
                        'desc' => 'Setiap pergerakan stok tercatat — penjualan, pembelian, transfer, opname, adjustment manual. Audit trail lengkap.',
                        'bullets' => [
                            'Tipe mutasi: in/out dari berbagai sumber',
                            'Filter: produk, outlet, tipe, tanggal',
                            'Quantity before → after setiap mutasi',
                            'Reference ke order/PO/transfer sumber',
                            'Audit trail untuk investigasi selisih',
                        ],
                        'screenshot' => 'stock-movements',
                    ],
                    [
                        'title' => 'Transfer Stok',
                        'desc' => 'Pindahkan stok antar outlet dengan mudah. Stok outlet asal berkurang, outlet tujuan bertambah. Lacak status transfer.',
                        'bullets' => [
                            'Transfer antar outlet dalam satu sistem',
                            'Status: Draft → Dikirim → Diterima',
                            'Auto-update stok di kedua outlet',
                            'Catatan dan referensi pengiriman',
                            'Riwayat transfer lengkap',
                        ],
                        'screenshot' => 'stock-transfers',
                    ],
                ],
            ],
            [
                'group' => '👥 Customer & Loyalitas',
                'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
                'items' => [
                    [
                        'title' => 'Poin & Reward',
                        'desc' => 'Program loyalitas otomatis — setiap transaksi menghasilkan poin, poin bisa ditukar reward. Tingkatkan retensi pelanggan.',
                        'bullets' => [
                            'Poin otomatis per transaksi',
                            'Reward catalog: hadiah dengan harga poin',
                            'Stok reward terbatas dengan sistem klaim',
                            'Riwayat poin earned & redeemed',
                            'Auto-upgrade grup pelanggan',
                        ],
                        'screenshot' => 'loyalty-rewards',
                    ],
                ],
            ],
            [
                'group' => 'Laporan',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                'items' => [
                    [
                        'title' => 'Laporan Penjualan',
                        'desc' => 'Dashboard penjualan lengkap dengan chart, summary cards, dan tabel detail. Filter per outlet dan rentang tanggal.',
                        'bullets' => [
                            'Chart penjualan harian/mingguan/bulanan',
                            'Top produk by quantity dan revenue',
                            'Rata-rata nilai transaksi',
                            'Perbandingan antar periode',
                            'Export PDF & Excel',
                        ],
                        'screenshot' => 'laporan-penjualan',
                    ],
                    [
                        'title' => 'Laporan Pembelian',
                        'desc' => 'Pantau total pembelian ke supplier, biaya restock, dan tren pembelian per periode.',
                        'bullets' => [
                            'Total pembelian per periode',
                            'Top supplier by volume',
                            'Tren harga beli',
                            'PO outstanding (belum diterima)',
                        ],
                        'screenshot' => 'laporan-penjualan',
                    ],
                    [
                        'title' => 'Laporan Inventori',
                        'desc' => 'Status stok terkini, produk di bawah minimum, overstock, akurasi opname, dan pergerakan stok.',
                        'bullets' => [
                            'Stok menipis alert',
                            'Overstock warning',
                            'Akurasi stock opname',
                            'Mutasi stok per periode',
                            'Nilai inventori (cost × qty)',
                        ],
                        'screenshot' => 'laporan-stok',
                    ],
                ],
            ],
            [
                'group' => 'Sistem',
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'items' => [
                    [
                        'title' => 'Manajemen User & Role',
                        'desc' => 'Kelola akun pengguna dengan role-based access. Owner, Admin, Kasir, Gudang, Manager — masing-masing dengan hak akses berbeda.',
                        'bullets' => [
                            'Multi-role: Owner, Admin, Kasir, Gudang, Manager',
                            'Assign user ke outlet',
                            'Dashboard berbeda per role',
                            'Nonaktifkan user tanpa hapus data',
                            'Audit log per user',
                        ],
                        'screenshot' => 'dashboard',
                    ],
                    [
                        'title' => 'Pengaturan Sistem',
                        'desc' => 'Konfigurasi global: pajak, mata uang, order types, approval threshold, format nota. Sesuaikan sistem dengan kebutuhan bisnis Anda.',
                        'bullets' => [
                            'Pajak default (PPN)',
                            'Mata uang',
                            'Tipe order (Walk-in, Member, Online dll) — dynamic via JSON',
                            'Approval threshold untuk transaksi besar',
                            'Format nota dan struk',
                            'Notifikasi stok menipis',
                        ],
                        'screenshot' => 'dashboard',
                    ],
                ],
            ],
        ];
    }

    protected function seoMeta(): array
    {
        return [
            'title' => 'Dokumentasi POS Retail — Panduan Lengkap Sistem Kasir',
            'description' => 'Dokumentasi lengkap POS Retail: tutorial langkah demi langkah, fitur-fitur, akun demo, dan panduan penggunaan sistem kasir modern untuk toko retail Indonesia.',
        ];
    }
}
