/* POS Retail PWA service worker (v4).
 * Strategi:
 * - Navigasi: network-first, fallback cache, fallback halaman offline.
 * - Aset build (/build/): stale-while-revalidate.
 * - API GET: network-first + cache bacaan offline; non-GET tidak disentuh.
 * - Precaching dibuat toleran gagal (satu aset 404 tidak boleh menggagalkan install).
 */
const CACHE_NAME = 'pos-retail-v4';

const PRECACHE_URLS = [
    '/manifest.json',
    '/pwa/icon.svg',
    '/pwa/icon-192.png',
    '/pwa/icon-512.png',
    '/favicon.svg',
];

const OFFLINE_HTML = `<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Offline — POS Retail</title>
<style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f8fafc;color:#334155;text-align:center;padding:24px}</style>
</head><body><div><h1>Kamu sedang offline</h1><p>Periksa koneksi internet lalu coba lagi.</p></div></body></html>`;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            await Promise.allSettled(
                PRECACHE_URLS.map((url) =>
                    fetch(url, { cache: 'reload' }).then((res) => {
                        if (res && res.ok) return cache.put(url, res);
                        return Promise.resolve();
                    }).catch(() => Promise.resolve())
                )
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

function isSameOrigin(url) {
    try {
        return new URL(url).origin === self.location.origin;
    } catch (e) {
        return false;
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;
    if (!isSameOrigin(request.url)) return;

    const url = new URL(request.url);

    // 1. Navigasi halaman: network-first + fallback offline.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response && response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(async () => {
                    const cached = await caches.match(request);
                    if (cached) return cached;
                    const fallback = await caches.match('/');
                    if (fallback) return fallback;
                    return new Response(OFFLINE_HTML, {
                        status: 503,
                        headers: { 'Content-Type': 'text/html; charset=UTF-8' },
                    });
                })
        );
        return;
    }

    // 2. Aset build berversi: stale-while-revalidate.
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request)
                    .then((response) => {
                        if (response && response.ok) {
                            const clone = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                        }
                        return response;
                    })
                    .catch(() => cached);
                return cached || network;
            })
        );
        return;
    }

    // 3. API: network-first, cache hanya GET sukses untuk bacaan offline.
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response && response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() => caches.match(request))
        );
        return;
    }

    // 4. Aset same-origin lain (gambar, font lokal): stale-while-revalidate.
    event.respondWith(
        caches.match(request).then((cached) => {
            const network = fetch(request)
                .then((response) => {
                    if (response && (response.ok || response.type === 'opaque')) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() => cached);
            return cached || network;
        })
    );
});
