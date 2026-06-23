const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://localhost:8765';
const ADMIN_EMAIL = 'admin@pos-retail.test';
const ADMIN_PASSWORD = 'password';
const SCREENSHOT_DIR = path.join(__dirname, '..', 'public', 'marketing', 'screens');

const PAGES = [
  // Dashboard
  { name: 'dashboard', url: '/admin', label: 'Dashboard' },

  // Master Data (12)
  { name: 'outlets', url: '/admin/outlets', label: 'Outlet' },
  { name: 'categories', url: '/admin/categories', label: 'Kategori' },
  { name: 'brands', url: '/admin/brands', label: 'Merek' },
  { name: 'units', url: '/admin/units', label: 'Satuan' },
  { name: 'products', url: '/admin/products', label: 'Produk' },
  { name: 'customers', url: '/admin/customers', label: 'Pelanggan' },
  { name: 'customer-groups', url: '/admin/customer-groups', label: 'Grup Pelanggan' },
  { name: 'suppliers', url: '/admin/suppliers', label: 'Supplier' },
  { name: 'table-restos', url: '/admin/table-restos', label: 'Meja Resto' },
  { name: 'table-areas', url: '/admin/table-areas', label: 'Area Meja' },
  { name: 'payment-methods', url: '/admin/payment-methods', label: 'Metode Pembayaran' },
  { name: 'raw-materials', url: '/admin/raw-materials', label: 'Bahan Baku' },

  // Transaksi (4)
  { name: 'orders', url: '/admin/orders', label: 'Pesanan' },
  { name: 'returs', url: '/admin/returs', label: 'Retur' },
  { name: 'kitchen-tickets', url: '/admin/kitchen-tickets', label: 'Tiket Dapur' },
  { name: 'shifts', url: '/admin/shifts', label: 'Shift Kasir' },

  // Pembelian (3)
  { name: 'purchase-orders', url: '/admin/purchase-orders', label: 'Purchase Order' },
  { name: 'installments', url: '/admin/installments', label: 'Cicilan' },
  { name: 'supplier-payables', url: '/admin/supplier-payables', label: 'Hutang Supplier' },

  // Inventori (3)
  { name: 'stock-opnames', url: '/admin/stock-opnames', label: 'Stock Opname' },
  { name: 'stock-movements', url: '/admin/stock-movements', label: 'Pergerakan Stok' },
  { name: 'stock-transfers', url: '/admin/stock-transfers', label: 'Transfer Stok' },

  // Loyalitas (4)
  { name: 'loyalty-rewards', url: '/admin/loyalty-rewards', label: 'Reward Loyalitas' },
  { name: 'discount-templates', url: '/admin/discount-templates', label: 'Template Diskon' },
  { name: 'loyalty-points', url: '/admin/loyalty-points', label: 'Poin Pelanggan' },
  { name: 'membership-tiers', url: '/admin/membership-tiers', label: 'Tier Member' },

  // Laporan (3)
  { name: 'laporan-penjualan', url: '/admin/laporan-penjualan', label: 'Laporan Penjualan' },
  { name: 'laporan-keuangan', url: '/admin/laporan-keuangan', label: 'Laporan Keuangan' },
  { name: 'laporan-stok', url: '/admin/laporan-stok', label: 'Laporan Stok' },

  // Sistem (5)
  { name: 'pengaturan-sistem', url: '/admin/pengaturan-sistem', label: 'Pengaturan Sistem' },
  { name: 'providers', url: '/admin/providers', label: 'Payment Gateway' },
  { name: 'attendances', url: '/admin/attendances', label: 'Kehadiran' },
  { name: 'audit-logs', url: '/admin/audit-logs', label: 'Log Audit' },
  { name: 'notifications', url: '/admin/notifications', label: 'Notifikasi' },

  // POS Kasir (external)
  { name: 'pos-web', url: '/pos', label: 'POS Kasir' },

  // Public pages
  { name: 'portal-login', url: '/portal/login', label: 'Portal Customer' },
];

const BROWSER_CHROME_CSS = `
  body { margin: 0; padding: 16px; background: #f8fafc; }
  .browser-mock {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    background: #fff;
  }
  .browser-topbar {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
  }
  .browser-dot { width: 10px; height: 10px; border-radius: 50%; }
  .browser-dot.red { background: #ef4444; }
  .browser-dot.yellow { background: #f59e0b; }
  .browser-dot.green { background: #22c55e; }
  .browser-url {
    margin-left: 12px;
    padding: 4px 10px;
    background: #e2e8f0;
    border-radius: 5px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: #64748b;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
`;

function getUrlLabel(label, name) {
  if (name === 'dashboard') return 'posretail.test/admin';
  if (name === 'pos-web') return 'posretail.test/pos';
  return `posretail.test/admin/${name}`;
}

async function screenshot() {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

  console.log('Launching browser...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    deviceScaleFactor: 2,
  });

  const page = await context.newPage();

  console.log('Logging in to admin...');
  await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'networkidle' });

  await page.waitForSelector('input[type="email"]', { timeout: 15000 });
  await page.locator('input[type="email"]').first().click();
  await page.type('input[type="email"]', ADMIN_EMAIL, { delay: 50 });
  await page.locator('input[type="password"]').first().click();
  await page.type('input[type="password"]', ADMIN_PASSWORD, { delay: 50 });
  await page.waitForTimeout(500);

  await page.locator('button[type="submit"]').first().click();
  await page.waitForURL('**/admin**', { timeout: 15000 });
  await page.waitForTimeout(2000);

  console.log(`Logged in. Capturing ${PAGES.length} screenshots...\n`);

  let idx = 0;
  for (const { name, url, label } of PAGES) {
    idx++;
    const fullUrl = `${BASE_URL}${url}`;
    console.log(`  [${idx}/${PAGES.length}] ${label} → ${fullUrl}`);

    try {
      await page.goto(fullUrl, { waitUntil: 'networkidle', timeout: 20000 });
      await page.waitForTimeout(3000);

      const content = await page.content();
      const urlLabel = getUrlLabel(label, name);

      const htmlWithChrome = `<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>${BROWSER_CHROME_CSS}</style>
</head>
<body>
  <div class="browser-mock">
    <div class="browser-topbar">
      <div class="browser-dot red"></div>
      <div class="browser-dot yellow"></div>
      <div class="browser-dot green"></div>
      <div class="browser-url">${urlLabel}</div>
    </div>
    ${content.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')}
  </div>
</body>
</html>`;

      await page.setContent(htmlWithChrome, { waitUntil: 'networkidle' });
      await page.waitForTimeout(500);

      const filePath = path.join(SCREENSHOT_DIR, `${name}.png`);
      await page.screenshot({ path: filePath, fullPage: true });
      console.log(`    Saved: ${name}.png`);
    } catch (err) {
      console.error(`    ERROR: ${err.message}`);
    }
  }

  await browser.close();
  console.log(`\nDone. ${PAGES.length} screenshots captured to ${SCREENSHOT_DIR}`);
}

screenshot().catch((err) => {
  console.error('Script failed:', err);
  process.exit(1);
});
