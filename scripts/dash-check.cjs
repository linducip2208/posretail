const { chromium } = require('playwright');
async function main() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 2400 } });
  const page = await context.newPage();
  await page.goto('http://127.0.0.1:8765/admin/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForSelector('input[type="email"]', { timeout: 15000 });
  await page.fill('input[type="email"]', 'owner@pos-retail.test');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);
  await page.goto('http://127.0.0.1:8765/admin', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(8000);
  await page.screenshot({ path: 'public/dash-check.png', fullPage: true });
  const data = await page.evaluate(() => ({
    stats: Array.from(document.querySelectorAll('.fi-wi-stats-overview-stat')).map(el => el.innerText.replace(/\n+/g, ' | ').slice(0, 120)),
    widgets: Array.from(document.querySelectorAll('.fi-wi')).map(el => {
      const h = el.querySelector('h2, h3');
      return (h ? h.innerText.trim() : '(tanpa judul)') + ' :: ' + el.innerText.replace(/\n+/g, ' ').slice(0, 100);
    }),
  }));
  console.log('STATS(' + data.stats.length + '):', JSON.stringify(data.stats, null, 1).slice(0, 2000));
  console.log('WIDGETS(' + data.widgets.length + '):', JSON.stringify(data.widgets, null, 1).slice(0, 3000));
  await browser.close();
}
main().catch(e => { console.error('FAIL:', e.message); process.exit(1); });
