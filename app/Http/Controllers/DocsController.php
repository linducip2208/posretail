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
        // Jangan pernah menampilkan kredensial demo di production kecuali
        // instance ini memang instance demo publik (POS_DEMO_MODE=true).
        if (! \App\Support\DemoMode::enabled()) {
            return [];
        }

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
                    'Tambah <strong>Outlet</strong> — toko/cabang tempat Anda beroperasi. Isi nama, alamat, telepon, dan status aktif.',
                    'Atur <strong>Pengaturan Sistem</strong> — pajak default, mata uang, format nota, tipe order dinamis, dan threshold approval.',
                    'Tambah <strong>Pegawai (User)</strong> — buat akun kasir, admin, gudang, dan manager dengan role yang sesuai.',
                    'User bisa <strong>multi-outlet</strong> — owner otomatis memegang semua outlet, user lain di-assign ke outlet tertentu.',
                ],
            ],
            [
                'phase' => 2,
                'title' => 'Input Data Master',
                'icon' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-3.75',
                'steps' => [
                    'Tambah <strong>Kategori</strong> produk — misal: Makanan, Minuman, Elektronik. Bisa nested (sub-kategori).',
                    'Tambah <strong>Merek</strong> — merek produk seperti Indomie, Samsung, SariWangi.',
                    'Tambah <strong>Satuan</strong> — Pcs, Box, Kardus, Kg, Liter, Sachet, Botol, Pack — plus <strong>Konversi Satuan</strong> (misal 1 Kardus = 24 Pcs).',
                    'Tambah <strong>Produk</strong> — nama, kategori, merek, satuan, harga beli, harga jual, harga grosir, harga member, stok minimum & maksimum.',
                    '<strong>Barcode & SKU otomatis</strong> — setiap produk baru langsung dapat barcode EAN-13 dan SKU sequential, siap discan.',
                    'Tambah <strong>Pelanggan</strong> — nama, telepon, alamat — lalu masukkan ke <strong>Grup / Member Tier</strong> (Regular, Member, Reseller, Grosir).',
                    'Tambah <strong>Pemasok (Supplier)</strong> — lengkap dengan kontak dan alamat untuk kebutuhan pembelian.',
                    'Tambah <strong>Metode Pembayaran</strong> — Tunai, Debit, QRIS, Transfer Bank.',
                    'Buat <strong>Template Diskon</strong> — percent, fixed, atau Buy X Get Y dengan minimal pembelian dan periode berlaku.',
                ],
            ],
            [
                'phase' => 3,
                'title' => 'Kasir POS (/pos)',
                'icon' => 'M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M12 7.5h.008v.008H12V7.5Zm-3 0h.008v.008H9V7.5Zm6 0h.008v.008H15V7.5Z',
                'steps' => [
                    'Buka <strong>/pos</strong> — antarmuka kasir cepat via browser. Pilih <strong>outlet aktif</strong> terlebih dahulu (multi-outlet).',
                    'Pilih <strong>Tipe Order</strong> yang dinamis dari Pengaturan Sistem — Walk-in, Member, Takeaway, Delivery, Online.',
                    'Tambah item — <strong>scan barcode</strong> via USB scanner atau kamera HP, atau klik dari grid produk.',
                    'Gunakan <strong>Tahan Keranjang (Hold)</strong> bila pelanggan belum selesai — beri label dan lanjutkan kapan saja.',
                    'Tukarkan <strong>voucher / gift card</strong> pelanggan langsung di keranjang.',
                    'Terima <strong>DP (uang muka)</strong> atau jadikan transaksi <strong>Cicilan</strong> — Mingguan, 2-Mingguan, atau Bulanan dengan jadwal bayar otomatis.',
                    'Selesaikan pembayaran — multi-metode bayar, kembalian otomatis, cetak via <strong>printer thermal</strong>, tampilkan total di <strong>customer display</strong>.',
                ],
            ],
            [
                'phase' => 4,
                'title' => 'Daftar Penjualan & Approval',
                'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
                'steps' => [
                    'Buka menu <strong>Daftar Penjualan</strong> untuk melihat semua transaksi dari semua outlet.',
                    'Filter berdasarkan outlet, pelanggan, status, dan tanggal; kelola status Pending → Diproses → Selesai → Dibatalkan.',
                    'Order di atas <strong>threshold approval</strong> otomatis berstatus <strong>pending_approval</strong>.',
                    'Setujui atau tolak di widget <strong>Menunggu Approval</strong> pada dashboard — setiap keputusan tercatat di audit log.',
                    'Catat <strong>Pembayaran</strong> per order — Lunas, DP, atau Belum Bayar — lengkap dengan metode bayar dan kembalian.',
                    'Cetak struk/nota dari halaman detail order.',
                ],
            ],
            [
                'phase' => 5,
                'title' => 'Order Pembelian & Hutang Pemasok',
                'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
                'steps' => [
                    'Buat <strong>Permintaan Pembelian</strong> bila gudang/outlet butuh restock.',
                    'Buat <strong>Order Pembelian</strong> — pilih pemasok dan outlet tujuan, tambah item beserta harga beli.',
                    'Jalankan alur <strong>Draft → Dipesan → Diterima</strong> — saat Diterima, stok otomatis bertambah.',
                    'Proses <strong>Retur Supplier</strong> untuk barang rusak/salah — stok otomatis berkurang.',
                    'Kelola <strong>Konsinyasi</strong> dan <strong>Kontrak Supplier</strong> untuk kerja sama jangka panjang.',
                    'Pantau <strong>Hutang Supplier (AP)</strong> dan lunasi lewat <strong>pembayaran termin</strong>.',
                    'Nilai pemasok lewat <strong>Rating Supplier</strong> — kualitas barang, ketepatan kirim, dan harga.',
                ],
            ],
            [
                'phase' => 6,
                'title' => 'Inventori & Stok',
                'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
                'steps' => [
                    'Lakukan <strong>Stock Opname</strong> berkala — bandingkan stok fisik vs sistem; selisih surplus/defisit tercatat otomatis.',
                    'Gunakan <strong>Stock Take Mobile</strong> untuk opname lewat HP langsung di rak gudang.',
                    'Pindahkan stok antar cabang via <strong>Transfer Stok</strong> (Draft → Dikirim → Diterima).',
                    'Pantau <strong>Mutasi Stok</strong> — setiap pergerakan in/out tercatat lengkap dengan referensi order/PO/transfer.',
                    'Aktifkan pelacakan <strong>batch & tanggal kedaluarsa (FEFO)</strong> — stok yang paling cepat kedaluarsa keluar lebih dulu.',
                    'Lacak unit bernilai tinggi dengan <strong>Nomor Seri / IMEI</strong> per unit.',
                    'Atur <strong>Bin Location</strong> agar setiap produk punya alamat rak yang jelas.',
                    'Catat barang rusak/hilang via <strong>Write-off</strong> dengan alasan dan approval.',
                ],
            ],
            [
                'phase' => 7,
                'title' => 'Pelanggan & Loyalitas',
                'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
                'steps' => [
                    'Setiap transaksi dengan pelanggan terdaftar otomatis menghasilkan <strong>Poin Loyalitas</strong>.',
                    'Buat katalog <strong>Reward</strong> — tentukan harga poin, stok reward, dan status aktifnya.',
                    'Naikkan pelanggan antar <strong>Grup / Member Tier</strong> berdasarkan total belanja.',
                    'Catat <strong>Deposit Pelanggan</strong> — saldo prabayar yang bisa dipakai belanja kapan saja.',
                    'Aktifkan <strong>Referral</strong> — beri poin bonus ke pelanggan yang mengajak pelanggan baru.',
                ],
            ],
            [
                'phase' => 8,
                'title' => 'Operasional Harian',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'steps' => [
                    'Buka <strong>Shift Kasir</strong> di awal tugas — catat modal awal <strong>cash drawer</strong>, tutup shift di akhir hari dengan rekonsiliasi kas.',
                    'Catat <strong>Pengeluaran</strong> operasional — sewa, listrik, gaji, dan biaya lain.',
                    'Kelola <strong>Absensi</strong> pegawai — clock in/out, hadir, terlambat, izin, sakit — plus <strong>Jadwal Shift</strong> mingguan.',
                    'Proses <strong>Pengiriman / Delivery</strong> untuk order antar — assign kurir dan lacak status kirim.',
                    'Tarik <strong>Marketplace Orders</strong> — pesanan dari kanal online masuk otomatis via webhook/API.',
                    'Pantau <strong>Target Penjualan</strong> per outlet/pegawai dan lihat progresnya di dashboard.',
                ],
            ],
            [
                'phase' => 9,
                'title' => 'Laporan & Keuangan',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                'steps' => [
                    'Buka grup menu <strong>Laporan</strong> — filter semua laporan per outlet dan rentang tanggal.',
                    'Lihat <strong>Laporan Penjualan</strong> — chart combo pendapatan + transaksi, doughnut komposisi metode bayar, dan top produk.',
                    'Baca <strong>Laporan Keuangan, Laba Rugi, Neraca, Kas, Komisi, Tutup Hari, Profit, Piutang, dan Pembatalan</strong> untuk kondisi bisnis menyeluruh.',
                    'Analisis <strong>Laporan Stok</strong> — stok menipis, overstock, dan pergerakan barang.',
                    'Evaluasi <strong>Laporan Pelanggan & Promo</strong> — pelanggan teratas dan efektivitas diskon.',
                    'Manfaatkan <strong>Ramalan Permintaan (demand forecast)</strong> dan <strong>forecast arus kas</strong> untuk rencana belanja dan keuangan.',
                    'Export laporan ke <strong>CSV, PDF, atau Excel</strong> untuk dibagikan ke akuntan/manajemen.',
                ],
            ],
            [
                'phase' => 10,
                'title' => 'Sistem, Keamanan & Integrasi',
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'steps' => [
                    'Atur <strong>Role & Permission per outlet</strong> — Owner, Manager, Admin, Kasir, Gudang — masing-masing dengan hak akses berbeda.',
                    'Pantau <strong>Audit Log</strong> — siapa mengubah apa dan kapan di seluruh sistem.',
                    'Kelola <strong>Notifikasi</strong> — stok menipis, order pending approval, dan pengingat penting lainnya.',
                    'Konfigurasi <strong>Provider / payment gateway dinamis</strong> — aktifkan QRIS dan kanal digital lain tanpa ubah kode.',
                    'Hubungkan sistem lain via <strong>API v1 + webhook</strong> — dokumentasi tersedia di halaman <strong>/docs/api</strong>.',
                    'Jalankan <strong>backup database</strong> berkala melalui scheduler agar data selalu aman.',
                ],
            ],
        ];
    }

    protected function features(): array
    {
        return [
            [
                'group' => 'Kasir POS (Point of Sale)',
                'icon' => 'M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M12 7.5h.008v.008H12V7.5Zm-3 0h.008v.008H9V7.5Zm6 0h.008v.008H15V7.5Z',
                'items' => [
                    [
                        'title' => 'Kasir POS /pos Multi-Outlet',
                        'desc' => 'Antarmuka kasir cepat via browser di /pos: pilih outlet aktif, tipe order dinamis, scan barcode, dan cetak struk dalam hitungan detik.',
                        'bullets' => [
                            'Multi-outlet — pilih outlet aktif sebelum jualan',
                            'Tipe order dinamis via Pengaturan Sistem (Walk-in, Member, Takeaway, Delivery, Online)',
                            'Scan barcode USB scanner atau kamera HP — langsung masuk keranjang',
                            'Kalkulasi otomatis: subtotal → diskon → pajak → total',
                            'Multi-metode pembayaran dalam satu transaksi + kembalian otomatis',
                            'Cetak struk via printer thermal, total tampil di customer display',
                        ],
                        'screenshot' => 'pos-web',
                    ],
                    [
                        'title' => 'Tahan Keranjang, Voucher & Gift Card',
                        'desc' => 'Tahan transaksi yang belum selesai, tukarkan voucher pelanggan, dan jual-gunakan gift card langsung dari kasir.',
                        'bullets' => [
                            'Tahan keranjang (hold) dengan label, lanjutkan kapan saja',
                            'Voucher diskon pelanggan terverifikasi otomatis',
                            'Gift card: jual, isi saldo, dan tukarkan',
                            'Auto-expire keranjang yang terlalu lama ditahan',
                        ],
                        'screenshot' => 'pos-web',
                    ],
                    [
                        'title' => 'DP & Cicilan di Kasir',
                        'desc' => 'Terima uang muka (DP) dan ubah transaksi menjadi cicilan dengan jadwal bayar otomatis.',
                        'bullets' => [
                            'Uang muka (DP) — pelanggan bayar sebagian di awal',
                            'Periode cicilan: Mingguan, 2-Mingguan, Bulanan',
                            'Jadwal bayar otomatis + pantau sisa pembayaran',
                            'Status order: Lunas, DP, Belum Bayar',
                        ],
                        'screenshot' => 'orders',
                    ],
                ],
            ],
            [
                'group' => 'Daftar Penjualan & Approval',
                'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
                'items' => [
                    [
                        'title' => 'Daftar Penjualan',
                        'desc' => 'Lihat dan kelola semua transaksi penjualan dari semua outlet. Filter, search, dan update status pesanan.',
                        'bullets' => [
                            'Nomor order unik auto-generate',
                            'Filter: outlet, pelanggan, status, tanggal',
                            'Detail item, diskon, pajak per order',
                            'Riwayat pembayaran per order',
                            'Batalkan pesanan dengan alasan',
                            'Export data ke Excel',
                        ],
                        'screenshot' => 'orders',
                    ],
                    [
                        'title' => 'Menunggu Approval',
                        'desc' => 'Order di atas threshold approval otomatis berstatus pending_approval. Setujui atau tolak dari widget dashboard.',
                        'bullets' => [
                            'Threshold approval diatur di Pengaturan Sistem',
                            'Status pending_approval terkunci sampai diputus',
                            'Approve/tolak di widget Menunggu Approval',
                            'Setiap keputusan tercatat di audit log',
                        ],
                        'screenshot' => 'dashboard',
                    ],
                    [
                        'title' => 'Retur Penjualan',
                        'desc' => 'Proses pengembalian barang dengan alasan dan status approval. Stok otomatis kembali.',
                        'bullets' => [
                            'Customer return & supplier return',
                            'Alasan retur wajib diisi',
                            'Approval workflow',
                            'Stok auto-kembali',
                        ],
                        'screenshot' => 'orders',
                    ],
                ],
            ],
            [
                'group' => 'Produk & Data Master',
                'icon' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-3.75',
                'items' => [
                    [
                        'title' => 'Produk, Kategori, Merek & Satuan',
                        'desc' => 'Kelola katalog lengkap: kategori nested, merek, satuan + konversi, harga berlapis, dan barcode otomatis.',
                        'bullets' => [
                            'Kategori nested, Merek, dan Satuan (Pcs, Box, Kg, Liter, dll)',
                            'Konversi Satuan — misal 1 Kardus = 24 Pcs',
                            'Harga beli, jual, grosir, dan member per produk',
                            'Stok minimum & maksimum + peringatan stok menipis',
                            'Barcode EAN-13 & SKU auto-generate, siap discan',
                            'Varian produk (warna, ukuran, rasa) dengan stok per varian',
                        ],
                        'screenshot' => 'dashboard',
                    ],
                    [
                        'title' => 'Outlet, Pegawai, Template Diskon & Metode Pembayaran',
                        'desc' => 'Data pendukung operasional: cabang, tim, promo diskon reusable, dan kanal bayar termasuk QRIS.',
                        'bullets' => [
                            'Outlet multi-cabang dengan alamat dan status aktif',
                            'Pegawai terhubung ke user login dan jadwal shift',
                            'Template Diskon: percent, fixed, Buy X Get Y + minimal belanja & periode',
                            'Metode Pembayaran: Tunai, Debit, QRIS, Transfer Bank',
                        ],
                        'screenshot' => 'dashboard',
                    ],
                ],
            ],
            [
                'group' => 'Order Pembelian & Pemasok',
                'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
                'items' => [
                    [
                        'title' => 'Order Pembelian',
                        'desc' => 'Buat dan lacak pesanan pembelian ke pemasok. Status Draft → Dipesan → Diterima, stok otomatis update saat diterima.',
                        'bullets' => [
                            'Workflow: Draft → Dipesan → Diterima',
                            'Auto-update stok saat PO diterima',
                            'Harga beli per item tercatat',
                            'Riwayat PO per pemasok',
                            'Filter PO berdasarkan status dan pemasok',
                        ],
                        'screenshot' => 'purchase-orders',
                    ],
                    [
                        'title' => 'Pemasok, Retur, Konsinyasi & Hutang',
                        'desc' => 'Kelola hubungan pemasok end-to-end: retur barang, titip jual, kontrak, permintaan pembelian, sampai hutang termin.',
                        'bullets' => [
                            'Data Pemasok/Supplier + Rating Supplier (kualitas, ketepatan, harga)',
                            'Retur supplier — stok otomatis berkurang',
                            'Konsinyasi dan Kontrak Supplier jangka panjang',
                            'Permintaan Pembelian dari gudang/outlet',
                            'Hutang Supplier (AP) + pembayaran termin bertahap',
                        ],
                        'screenshot' => 'purchase-orders',
                    ],
                ],
            ],
            [
                'group' => 'Inventori & Stok',
                'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
                'items' => [
                    [
                        'title' => 'Stock Opname',
                        'desc' => 'Penghitungan fisik stok berkala per outlet, termasuk stock take mobile lewat HP. Selisih tercatat dan stok terkoreksi.',
                        'bullets' => [
                            'Buat sesi opname per outlet',
                            'Stock take mobile — opname lewat HP di rak gudang',
                            'Input stok fisik vs stok sistem',
                            'Hitung selisih otomatis (surplus/defisit)',
                            'Approval sebelum koreksi diterapkan',
                            'Laporan akurasi stok per periode',
                        ],
                        'screenshot' => 'stock-opnames',
                    ],
                    [
                        'title' => 'Mutasi Stok',
                        'desc' => 'Setiap pergerakan stok tercatat — penjualan, pembelian, transfer, opname, adjustment, write-off. Audit trail lengkap.',
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
                        'desc' => 'Pindahkan stok antar outlet. Stok outlet asal berkurang, outlet tujuan bertambah. Lacak status transfer.',
                        'bullets' => [
                            'Transfer antar outlet dalam satu sistem',
                            'Status: Draft → Dikirim → Diterima',
                            'Auto-update stok di kedua outlet',
                            'Catatan dan referensi pengiriman',
                            'Riwayat transfer lengkap',
                        ],
                        'screenshot' => 'stock-transfers',
                    ],
                    [
                        'title' => 'FEFO, Batch, IMEI & Bin Location',
                        'desc' => 'Kontrol stok tingkat lanjut: kedaluarsa dengan metode FEFO, nomor seri per unit, alamat rak, dan ramalan kebutuhan.',
                        'bullets' => [
                            'Batch + tanggal kedaluarsa dengan arus FEFO',
                            'Nomor Seri / IMEI per unit untuk barang bernilai tinggi',
                            'Bin Location — alamat rak setiap produk',
                            'Write-off barang rusak/hilang dengan approval',
                            'Ramalan Permintaan (demand forecast) per produk',
                        ],
                        'screenshot' => 'stock-movements',
                    ],
                ],
            ],
            [
                'group' => 'Pelanggan & Loyalitas',
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
                        ],
                        'screenshot' => 'loyalty-rewards',
                    ],
                    [
                        'title' => 'Grup, Member Tier, Deposit & Referral',
                        'desc' => 'Segmentasikan pelanggan, kelola saldo prabayar, dan tumbuhkan basis pelanggan lewat referral.',
                        'bullets' => [
                            'Grup / Member Tier: Regular, Member, Reseller, Grosir',
                            'Auto-upgrade tier berdasarkan total belanja',
                            'Deposit Pelanggan — saldo prabayar untuk belanja',
                            'Referral — bonus poin pengajak pelanggan baru',
                        ],
                        'screenshot' => 'loyalty-rewards',
                    ],
                ],
            ],
            [
                'group' => 'Laporan & Keuangan',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                'items' => [
                    [
                        'title' => 'Laporan Penjualan',
                        'desc' => 'Dashboard penjualan lengkap dengan chart combo, komposisi bayar, dan tabel detail. Filter per outlet dan rentang tanggal.',
                        'bullets' => [
                            'Chart combo pendapatan + jumlah transaksi',
                            'Doughnut komposisi metode pembayaran',
                            'Top produk by quantity dan revenue',
                            'Rata-rata nilai transaksi + perbandingan antar periode',
                            'Export CSV, PDF & Excel',
                        ],
                        'screenshot' => 'laporan-penjualan',
                    ],
                    [
                        'title' => 'Laporan Stok',
                        'desc' => 'Status stok terkini, produk di bawah minimum, overstock, akurasi opname, dan pergerakan stok.',
                        'bullets' => [
                            'Stok menipis alert',
                            'Overstock warning',
                            'Akurasi stock opname',
                            'Mutasi stok per periode',
                            'Nilai inventori (cost × qty)',
                            'Export CSV, PDF & Excel',
                        ],
                        'screenshot' => 'laporan-stok',
                    ],
                    [
                        'title' => 'Laporan Keuangan & Lainnya',
                        'desc' => 'Satu grup Laporan + Keuangan untuk kondisi bisnis menyeluruh — dari laba rugi sampai promo.',
                        'bullets' => [
                            'Laporan Keuangan, Laba Rugi, Neraca, dan Kas',
                            'Laporan Komisi, Tutup Hari, Profit, dan Piutang',
                            'Laporan Pembatalan + tren & per outlet',
                            'Laporan Pelanggan & Promo — pelanggan teratas, efektivitas diskon',
                            'Forecast arus kas untuk rencana keuangan',
                            'Export CSV, PDF & Excel',
                        ],
                        'screenshot' => 'laporan-penjualan',
                    ],
                ],
            ],
            [
                'group' => 'Operasional, Sistem & Integrasi',
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'items' => [
                    [
                        'title' => 'Operasional Harian',
                        'desc' => 'Shift kasir, kehadiran tim, biaya operasional, pengiriman, kanal online, dan target penjualan dalam satu alur kerja.',
                        'bullets' => [
                            'Shift Kasir + cash drawer: modal awal & rekonsiliasi tutup shift',
                            'Absensi Pegawai: clock in/out, hadir, terlambat, izin, sakit',
                            'Jadwal Shift mingguan per outlet',
                            'Pengeluaran operasional tercatat per kategori',
                            'Pengiriman / Delivery dengan kurir dan status kirim',
                            'Marketplace Orders masuk otomatis via webhook/API',
                            'Target Penjualan per outlet/pegawai + progres dashboard',
                        ],
                        'screenshot' => 'dashboard',
                    ],
                    [
                        'title' => 'Peran, Audit & Integrasi',
                        'desc' => 'Keamanan berlapis per outlet, jejak audit penuh, gateway bayar dinamis, dan API terbuka untuk integrasi.',
                        'bullets' => [
                            'Role & permission per outlet: Owner, Manager, Admin, Kasir, Gudang',
                            'User multi-outlet — owner memegang semua outlet',
                            'Audit Log: siapa mengubah apa dan kapan',
                            'Pengaturan Sistem global + Notifikasi stok & approval',
                            'Provider / payment gateway dinamis (QRIS dsb) tanpa ubah kode',
                            'API v1 + webhook, dokumentasi di /docs/api',
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
