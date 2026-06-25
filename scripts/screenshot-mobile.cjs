const { chromium, devices } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://127.0.0.1:8765';
const ADMIN_EMAIL = 'admin@pos-retail.test';
const ADMIN_PASSWORD = 'password';
const SCREENSHOT_DIR = path.join(__dirname, '..', 'public', 'marketing', 'screens-mobile');

// Key flows to verify responsive behaviour: dashboard, list, form, report, POS, public.
const PAGES = [
  { name: 'dashboard', url: '/admin', label: 'Dashboard' },
  { name: 'products', url: '/admin/products', label: 'Produk (List)' },
  { name: 'product-create', url: '/admin/products/create', label: 'Produk (Form)' },
  { name: 'orders', url: '/admin/orders', label: 'Pesanan (List)' },
  { name: 'laporan-penjualan', url: '/admin/laporan-penjualan', label: 'Laporan Penjualan' },
  { name: 'customers', url: '/admin/customers', label: 'Pelanggan (List)' },
  { name: 'pos-web', url: '/pos', label: 'POS Kasir' },
  { name: 'home', url: '/', label: 'Landing (Marketing)', public: true },
  { name: 'portal-login', url: '/portal/login', label: 'Portal Customer', public: true },
];

async function screenshot() {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

  console.log('Launching browser (iPhone 11 Pro Max — 414x896)...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 414, height: 896 },
    deviceScaleFactor: 2,
    isMobile: true,
    hasTouch: true,
    userAgent: devices['iPhone 11 Pro Max']?.userAgent,
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

  console.log(`Logged in. Capturing ${PAGES.length} mobile screenshots...\n`);

  let idx = 0;
  for (const { name, url, label } of PAGES) {
    idx++;
    const fullUrl = `${BASE_URL}${url}`;
    console.log(`  [${idx}/${PAGES.length}] ${label} -> ${fullUrl}`);

    try {
      await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
      await page.waitForTimeout(3500);

      const filePath = path.join(SCREENSHOT_DIR, `${name}.png`);
      await page.screenshot({ path: filePath, fullPage: true });
      console.log(`    Saved: ${name}.png`);
    } catch (err) {
      console.error(`    ERROR: ${err.message}`);
    }
  }

  await browser.close();
  console.log(`\nDone. Mobile screenshots captured to ${SCREENSHOT_DIR}`);
}

screenshot().catch((err) => {
  console.error('Script failed:', err);
  process.exit(1);
});
