const { chromium } = require('playwright');
const PAGES = [
  '/', '/pos', '/admin', '/admin/orders', '/admin/orders/create',
  '/admin/products', '/admin/products/create', '/admin/customers',
  '/admin/purchase-orders', '/admin/stock-opnames',
  '/admin/laporan-penjualan', '/admin/laporan-keuangan', '/admin/laporan-stok',
  '/admin/laporan-laba-rugi', '/admin/laporan-neraca', '/admin/laporan-kas',
  '/admin/laporan-pembatalan', '/admin/laporan-komisi', '/admin/laporan-tutup-hari',
  '/admin/laporan-profit', '/admin/laporan-piutang', '/admin/laporan-pelanggan-promo',
  '/admin/users', '/admin/outlets', '/admin/pengaturan-sistem',
];
async function main() {
  const browser = await chromium.launch({ headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await ctx.newPage();
  const bad = [];
  page.on('response', r => { if (r.status() >= 500) bad.push(r.status() + ' ' + r.url()); });
  await page.goto('http://127.0.0.1:8765/admin/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForSelector('input[type="email"]', { timeout: 15000 });
  await page.fill('input[type="email"]', 'owner@pos-retail.test');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);
  for (const u of PAGES) {
    try {
      const res = await page.goto('http://127.0.0.1:8765' + u, { waitUntil: 'networkidle', timeout: 60000 });
      await page.waitForTimeout(2500);
      const s = res ? res.status() : 0;
      const errBox = await page.evaluate(() => {
        const t = document.body.innerText || '';
        return t.includes('Server Error') || t.includes('Exception') || t.includes('SQLSTATE');
      });
      console.log((s === 200 && !errBox ? 'OK  ' : 'FAIL') + ' ' + s + ' ' + u);
    } catch (e) { console.log('FAIL ERR ' + u + ' :: ' + e.message.slice(0, 100)); }
  }
  console.log('SERVER-ERRORS:', JSON.stringify(bad));
  await browser.close();
}
main().catch(e => { console.error('FAIL:', e.message); process.exit(1); });
