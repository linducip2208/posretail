/**
 * POS Retail - Offline Mode
 * Cache products & queue transactions in localStorage.
 * Auto-sync when back online.
 */

const PosOffline = {
    keys: {
        products: 'pos_offline_products',
        customers: 'pos_offline_customers',
        queue: 'pos_offline_queue',
        lastSync: 'pos_offline_last_sync',
    },

    isOnline: function () {
        return navigator.onLine;
    },

    cacheProducts: async function (apiUrl) {
        try {
            const res = await fetch(apiUrl + '?per_page=500');
            const data = await res.json();
            const products = data.data || data;
            localStorage.setItem(this.keys.products, JSON.stringify(products));
            localStorage.setItem(this.keys.lastSync, Date.now());
            return products;
        } catch (e) {
            return this.getCachedProducts();
        }
    },

    getCachedProducts: function () {
        const raw = localStorage.getItem(this.keys.products);
        return raw ? JSON.parse(raw) : [];
    },

    searchProducts: function (query) {
        const products = this.getCachedProducts();
        if (!query) return products.slice(0, 50);
        const q = query.toLowerCase();
        return products.filter(p =>
            (p.name && p.name.toLowerCase().includes(q)) ||
            (p.sku && p.sku.toLowerCase().includes(q)) ||
            (p.barcode && p.barcode.includes(q))
        ).slice(0, 50);
    },

    queueTransaction: function (orderData) {
        const queue = this.getQueue();
        queue.push({
            ...orderData,
            _queuedAt: Date.now(),
            _id: 'offline_' + Date.now(),
        });
        localStorage.setItem(this.keys.queue, JSON.stringify(queue));
        return queue.length;
    },

    getQueue: function () {
        const raw = localStorage.getItem(this.keys.queue);
        return raw ? JSON.parse(raw) : [];
    },

    getQueueCount: function () {
        return this.getQueue().length;
    },

    syncQueue: async function (checkoutUrl) {
        if (!this.isOnline()) return { synced: 0, failed: 0 };

        const queue = this.getQueue();
        if (queue.length === 0) return { synced: 0, failed: 0 };

        let synced = 0;
        let failed = 0;
        const remaining = [];

        for (const order of queue) {
            try {
                const { _queuedAt, _id, ...cleanOrder } = order;
                const res = await fetch(checkoutUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(cleanOrder),
                });
                if (res.ok) {
                    synced++;
                } else {
                    failed++;
                    remaining.push(order);
                }
            } catch (e) {
                failed++;
                remaining.push(order);
            }
        }

        localStorage.setItem(this.keys.queue, JSON.stringify(remaining));
        return { synced, failed, remaining: remaining.length };
    },

    clearCache: function () {
        Object.values(this.keys).forEach(k => localStorage.removeItem(k));
    },
};

window.PosOffline = PosOffline;
