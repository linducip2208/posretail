const { chromium } = require('playwright');
async function main() {
  const browser = await chromium.launch({ headless: true });
  const page = await (await browser.newContext({ viewport: { width: 1366, height: 768 } })).newPage();
  await page.goto('http://127.0.0.1:8765/admin/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForSelector('input[type="email"]', { timeout: 15000 });
  await page.fill('input[type="email"]', 'owner@pos-retail.test');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);
  await page.goto('http://127.0.0.1:8765/admin', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  for (let i = 0; i < 8; i++) { await page.mouse.wheel(0, 1000); await page.waitForTimeout(900); }
  await page.waitForFunction(() => (document.body.innerText || '').includes('Ringkasan Pembayaran'), { timeout: 60000 });
  await page.waitForTimeout(2000);
  await page.evaluate(() => {
    const els = [...document.querySelectorAll('h2, h3')];
    const t = els.find(e => (e.innerText || '').includes('Ringkasan Pembayaran'));
    if (t) t.scrollIntoView({ block: 'center' });
  });
  await page.waitForTimeout(4000);
  await page.screenshot({ path: 'public/m-pay3.png', fullPage: false });
  console.log('saved');
  await browser.close();
}
main().catch(e => { console.error('FAIL:', e.message); process.exit(1); });
