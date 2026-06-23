const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const SCREEN_DIR = path.join(__dirname, '..', 'public', 'marketing', 'screens');
const BASE = `file:///${__dirname.replace(/\\/g, '/')}/../public/marketing/mockups/`;

const pages = [
  { file: 'dashboard.html', name: 'dashboard' },
  { file: 'products.html', name: 'products' },
  { file: 'orders.html', name: 'orders' },
  { file: 'customers.html', name: 'customers' },
  { file: 'purchase-orders.html', name: 'purchase-orders' },
  { file: 'stock-opnames.html', name: 'stock-opnames' },
  { file: 'loyalty-rewards.html', name: 'loyalty-rewards' },
  { file: 'laporan-penjualan.html', name: 'laporan-penjualan' },
  { file: 'discount-templates.html', name: 'discount-templates' },
  { file: 'attendances.html', name: 'attendances' },
  { file: 'table-restos.html', name: 'table-restos' },
  { file: 'raw-materials.html', name: 'raw-materials' },
];

const MOCKUP_HTML = (title, icon, rows, group = 'Master Data') => `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,700" rel="stylesheet">
  <style>
    body { font-family: Inter, sans-serif; background: #f8fafc; }
    .sidebar { background: linear-gradient(180deg, #1e1b4b 0%, #111827 100%); }
    .card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; }
    .badge { border-radius: 6px; font-weight: 600; font-size: 11px; padding: 2px 8px; }
    .btn-primary { background: linear-gradient(135deg, #4f46e5, #4338ca); color: white; border-radius: 8px; font-weight: 600; }
  </style>
</head>
<body class="flex h-screen">
  <div class="sidebar w-64 text-white p-4 flex flex-col gap-1">
    <div class="text-sm font-bold px-3 py-2">POS Retail</div>
    <div class="text-[10px] uppercase tracking-widest text-gray-500 px-3 pt-4 pb-1">${group}</div>
    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/10 text-sm font-medium">
      <span>${icon}</span> ${title}
    </div>
    <div class="text-[10px] uppercase tracking-widest text-gray-500 px-3 pt-4 pb-1">Lainnya</div>
  </div>
  <div class="flex-1 flex flex-col">
    <div class="bg-white/80 backdrop-blur border-b px-6 py-3 flex items-center justify-between">
      <h1 class="text-lg font-bold text-gray-900">${title}</h1>
      <button class="btn-primary px-4 py-2 text-sm">+ Tambah</button>
    </div>
    <div class="flex-1 p-6">
      <div class="card overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-500 font-bold">
              ${Object.keys(rows[0] || {}).map(k => `<th class="px-4 py-3">${k}</th>`).join('')}
            </tr>
          </thead>
          <tbody>
            ${rows.map((row, i) => `<tr class="${i%2===0?'bg-white':'bg-gray-50/50'} border-t border-gray-100 text-sm">
              ${Object.values(row).map((v, j) => `<td class="px-4 py-2.5 ${j===0?'font-semibold text-gray-800':''}">${v}</td>`).join('')}
            </tr>`).join('')}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>`;

const MOCKUPS = {
  'dashboard.html': `<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,700" rel="stylesheet">
<style>body{font-family:Inter,sans-serif;background:#f8fafc}.stat-card{background:linear-gradient(135deg,#eef2ff,#e0e7ff);border-radius:14px;border:1px solid #c7d2fe}</style></head>
<body class="p-6">
  <div class="flex items-center justify-between mb-6"><h1 class="text-2xl font-extrabold text-gray-900">Dashboard</h1><span class="text-sm text-gray-500">Hari ini: ${new Date().toLocaleDateString('id-ID')}</span></div>
  <div class="grid grid-cols-4 gap-4 mb-6">
    <div class="stat-card p-5"><div class="text-xs text-indigo-600 font-semibold uppercase">Transaksi Hari Ini</div><div class="text-3xl font-extrabold text-gray-900 mt-1">124</div><div class="text-xs text-green-600 mt-1">+12% dari kemarin</div></div>
    <div class="stat-card p-5"><div class="text-xs text-indigo-600 font-semibold uppercase">Pendapatan</div><div class="text-3xl font-extrabold text-gray-900 mt-1">Rp 8.4M</div><div class="text-xs text-green-600 mt-1">+8% dari kemarin</div></div>
    <div class="stat-card p-5"><div class="text-xs text-indigo-600 font-semibold uppercase">Produk Terjual</div><div class="text-3xl font-extrabold text-gray-900 mt-1">342</div><div class="text-xs text-green-600 mt-1">+15% dari kemarin</div></div>
    <div class="stat-card p-5"><div class="text-xs text-indigo-600 font-semibold uppercase">Stok Menipis</div><div class="text-3xl font-extrabold text-red-600 mt-1">7</div><div class="text-xs text-red-600 mt-1">Perlu restock</div></div>
  </div>
  <div class="bg-white rounded-2xl border p-6 mb-4"><div class="text-sm font-bold text-gray-700 mb-4">Pendapatan 30 Hari Terakhir</div><div class="h-48 bg-gradient-to-t from-indigo-100 to-indigo-50 rounded-xl flex items-end p-4 gap-2">${Array.from({length:30},(_,i)=>`<div class="flex-1 bg-indigo-500 rounded-t" style="height:${20+Math.random()*80}%"></div>`).join('')}</div></div>
</body></html>`,

  'products.html': MOCKUP_HTML('Produk', '📦', [
    { Nama: 'Indomie Goreng', SKU: 'SKU00021', Kategori: 'Bumbu Dapur', Harga: 'Rp 3.500', Stok: '200', Status: '<span class="badge bg-green-100 text-green-700">Aktif</span>' },
    { Nama: 'Aqua 600ml', SKU: 'SKU00010', Kategori: 'Minuman', Harga: 'Rp 3.500', Stok: '300', Status: '<span class="badge bg-green-100 text-green-700">Aktif</span>' },
    { Nama: 'Beras Pandan Wangi 5KG', SKU: 'SKU00045', Kategori: 'Sembako', Harga: 'Rp 78.000', Stok: '30', Status: '<span class="badge bg-green-100 text-green-700">Aktif</span>' },
    { Nama: 'Lifebuoy Sabun 70gr', SKU: 'SKU00018', Kategori: 'Kebersihan', Harga: 'Rp 4.000', Stok: '160', Status: '<span class="badge bg-green-100 text-green-700">Aktif</span>' },
    { Nama: 'Kopi Kapal Api 380gr', SKU: 'SKU00011', Kategori: 'Minuman', Harga: 'Rp 25.000', Stok: '12', Status: '<span class="badge bg-orange-100 text-orange-700">Stok Menipis</span>' },
  ], 'Master Data'),

  'orders.html': MOCKUP_HTML('Pesanan', '🛒', [
    { No: 'ORD-20260531-A3F2B', Tipe: '<span class="badge bg-blue-100 text-blue-700">Dine In</span>', Customer: 'Budi Santoso', Total: 'Rp 156.500', Status: '<span class="badge bg-green-100 text-green-700">Selesai</span>' },
    { No: 'ORD-20260531-B7E1A', Tipe: '<span class="badge bg-purple-100 text-purple-700">Takeaway</span>', Customer: 'Walk-in', Total: 'Rp 42.000', Status: '<span class="badge bg-green-100 text-green-700">Selesai</span>' },
    { No: 'ORD-20260531-C9D4F', Tipe: '<span class="badge bg-orange-100 text-orange-700">Delivery</span>', Customer: 'Sari Anggraini', Total: 'Rp 285.000', Status: '<span class="badge bg-yellow-100 text-yellow-700">Diproses</span>' },
    { No: 'ORD-20260530-D2E8K', Tipe: '<span class="badge bg-blue-100 text-blue-700">Dine In</span>', Customer: 'Rizky Hermawan', Total: 'Rp 89.500', Status: '<span class="badge bg-green-100 text-green-700">Selesai</span>' },
    { No: 'ORD-20260530-E5F1M', Tipe: '<span class="badge bg-purple-100 text-purple-700">Takeaway</span>', Customer: 'Walk-in', Total: 'Rp 234.000', Status: '<span class="badge bg-red-100 text-red-700">Dibatalkan</span>' },
  ], 'Transaksi'),

  'customers.html': MOCKUP_HTML('Pelanggan', '👥', [
    { Nama: 'Budi Santoso', Telepon: '0812-3456-7890', Grup: '<span class="badge bg-indigo-100 text-indigo-700">Member</span>', Poin: '1.250', 'Total Belanja': 'Rp 8.4M' },
    { Nama: 'Sari Anggraini', Telepon: '0813-9876-5432', Grup: '<span class="badge bg-purple-100 text-purple-700">Reseller</span>', Poin: '3.400', 'Total Belanja': 'Rp 24.1M' },
    { Nama: 'Hendra Wijaya', Telepon: '0856-1111-2222', Grup: '<span class="badge bg-gray-100 text-gray-700">Regular</span>', Poin: '45', 'Total Belanja': 'Rp 350K' },
  ], 'Master Data'),

  'purchase-orders.html': MOCKUP_HTML('Purchase Order', '📋', [
    { No: 'PO-20260531-001', Supplier: 'PT Indofood', Total: 'Rp 5.2M', Status: '<span class="badge bg-green-100 text-green-700">Diterima</span>' },
    { No: 'PO-20260530-002', Supplier: 'PT Sumber Makmur', Total: 'Rp 3.1M', Status: '<span class="badge bg-yellow-100 text-yellow-700">Dipesan</span>' },
    { No: 'PO-20260529-003', Supplier: 'PT Wings Food', Total: 'Rp 2.8M', Status: '<span class="badge bg-gray-100 text-gray-700">Draft</span>' },
  ], 'Pembelian'),

  'stock-opnames.html': MOCKUP_HTML('Stock Opname', '📊', [
    { No: 'SO-20260531-001', Outlet: 'Toko Pusat', Status: '<span class="badge bg-yellow-100 text-yellow-700">Draft</span>', 'Jumlah Item': '50' },
    { No: 'SO-20260525-002', Outlet: 'Cabang Timur', Status: '<span class="badge bg-green-100 text-green-700">Selesai</span>', 'Jumlah Item': '48' },
  ], 'Inventori'),

  'loyalty-rewards.html': MOCKUP_HTML('Loyalty Reward', '🎁', [
    { Hadiah: 'Diskon 10% Next Purchase', 'Jenis Diskon': '<span class="badge bg-blue-100 text-blue-700">Percent</span>', Poin: '500', Status: '<span class="badge bg-green-100 text-green-700">Aktif</span>' },
    { Hadiah: 'Gratis 1 Produk', 'Jenis Diskon': '<span class="badge bg-orange-100 text-orange-700">Fixed</span>', Poin: '1.000', Status: '<span class="badge bg-green-100 text-green-700">Aktif</span>' },
  ], 'Loyalitas'),

  'laporan-penjualan.html': `<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet"><style>body{font-family:Inter,sans-serif;background:#f8fafc}</style></head><body class="p-6">
  <h1 class="text-2xl font-extrabold text-gray-900 mb-6">Laporan Penjualan</h1>
  <div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border p-4"><div class="text-xs text-gray-500 uppercase font-semibold">Total Revenue</div><div class="text-2xl font-extrabold mt-1">Rp 42.850.000</div></div>
    <div class="bg-white rounded-xl border p-4"><div class="text-xs text-gray-500 uppercase font-semibold">Total Order</div><div class="text-2xl font-extrabold mt-1">1,247</div></div>
    <div class="bg-white rounded-xl border p-4"><div class="text-xs text-gray-500 uppercase font-semibold">Avg Order</div><div class="text-2xl font-extrabold mt-1">Rp 34.300</div></div>
    <div class="bg-white rounded-xl border p-4"><div class="text-xs text-gray-500 uppercase font-semibold">Top Produk</div><div class="text-2xl font-extrabold mt-1">Indomie Goreng</div></div>
  </div>
  <div class="bg-white rounded-2xl border p-6"><div class="text-sm font-bold text-gray-700 mb-4">Revenue Chart</div><div class="h-64 bg-gradient-to-t from-indigo-100 to-indigo-50 rounded-xl flex items-end p-4 gap-2">${Array.from({length:12},(_,i)=>`<div class="flex-1 bg-indigo-500 rounded-t" style="height:${30+Math.random()*70}%"></div>`).join('')}</div></div>
</body></html>`,

  'discount-templates.html': MOCKUP_HTML('Template Diskon', '🏷️', [
    { Nama: 'Diskon 10% Member', Tipe: '<span class="badge bg-blue-100 text-blue-700">Percent</span>', 'Min Belanja': 'Rp 50.000', Berlaku: '1 Jun - 31 Des 2026' },
    { Nama: 'Potongan 5rb', Tipe: '<span class="badge bg-orange-100 text-orange-700">Fixed</span>', 'Min Belanja': 'Rp 25.000', Berlaku: '1 Jun - 31 Des 2026' },
    { Nama: 'Beli 3 Gratis 1', Tipe: '<span class="badge bg-purple-100 text-purple-700">Buy X Get Y</span>', 'Min Belanja': '-', Berlaku: '1 Jun - 31 Jul 2026' },
  ], 'Loyalitas'),

  'attendances.html': MOCKUP_HTML('Absensi', '🕐', [
    { Tanggal: '31 Mei 2026', Nama: 'Owner POS', Masuk: '07:30', Keluar: '17:00', Status: '<span class="badge bg-green-100 text-green-700">Hadir</span>' },
    { Tanggal: '31 Mei 2026', Nama: 'Kasir 1', Masuk: '08:15', Keluar: '17:30', Status: '<span class="badge bg-yellow-100 text-yellow-700">Terlambat</span>' },
    { Tanggal: '30 Mei 2026', Nama: 'Admin Gudang', Masuk: '07:45', Keluar: '16:45', Status: '<span class="badge bg-green-100 text-green-700">Hadir</span>' },
  ], 'Sistem'),

  'table-restos.html': MOCKUP_HTML('Meja', '🪑', [
    { Nama: 'Meja 1', Kode: 'T01', Area: 'Indoor', Kapasitas: '4', Status: '<span class="badge bg-green-100 text-green-700">Tersedia</span>' },
    { Nama: 'Meja 4', Kode: 'T04', Area: 'Indoor', Kapasitas: '6', Status: '<span class="badge bg-red-100 text-red-700">Terpakai</span>' },
    { Nama: 'VIP 1', Kode: 'T06', Area: 'VIP', Kapasitas: '8', Status: '<span class="badge bg-yellow-100 text-yellow-700">Reserved</span>' },
  ], 'Master Data'),

  'raw-materials.html': MOCKUP_HTML('Bahan Baku', '🧂', [
    { Nama: 'Tepung Terigu', Kode: 'RM001', Unit: 'Kg', 'Harga/Unit': 'Rp 12.000', Stok: '50' },
    { Nama: 'Gula Pasir', Kode: 'RM002', Unit: 'Kg', 'Harga/Unit': 'Rp 15.000', Stok: '30' },
    { Nama: 'Minyak Goreng', Kode: 'RM003', Unit: 'Liter', 'Harga/Unit': 'Rp 20.000', Stok: '20' },
  ], 'Inventori'),
};

async function main() {
  const dir = path.join(__dirname, '..', 'public', 'marketing', 'mockups');
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });

  // Write mockup HTML files
  for (const [file, html] of Object.entries(MOCKUPS)) {
    fs.writeFileSync(path.join(dir, file), html);
  }

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

  for (const p of pages) {
    const filePath = path.join(dir, p.file);
    if (!fs.existsSync(filePath)) { console.log(`  SKIP ${p.name} (no mockup)`); continue; }

    await page.goto('file:///' + filePath.replace(/\\/g, '/'), { waitUntil: 'networkidle', timeout: 10000 });
    await page.waitForTimeout(500);
    await page.screenshot({ path: path.join(SCREEN_DIR, `${p.name}.png`) });
    console.log(`  ${p.name}.png`);
  }

  await browser.close();
  console.log('All mockup screenshots generated!');
}

main().catch(console.error);
