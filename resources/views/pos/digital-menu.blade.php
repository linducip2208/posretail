<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Digital — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .card { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="min-h-screen pb-24">
    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 text-white p-6 shadow-lg">
        <h1 class="text-2xl font-extrabold">{{ $outlet->name }}</h1>
        <p class="text-indigo-200 text-sm mt-1">Meja: {{ $table->name ?? '#' . request('table') }}</p>
    </div>

    <div class="max-w-2xl mx-auto px-4 mt-4" id="menuList">
        <div class="text-center text-gray-400 py-8">Memuat menu...</div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg p-4" id="cartFooter" style="display:none">
        <div class="max-w-2xl mx-auto flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-500" id="cartCount">0 item</span>
                <div class="text-xl font-bold text-indigo-600" id="cartTotal">Rp 0</div>
            </div>
            <button onclick="submitOrder()" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-indigo-700 active:scale-95 transition">
                Pesan Sekarang
            </button>
        </div>
    </div>

    <script>
        const API = '/api/v1';
        const tableId = {{ $table->id ?? 0 }};
        const outletId = {{ $outlet->id }};
        let cart = [];

        async function loadMenu() {
            try {
                const res = await fetch(`${API}/products?outlet_id=${outletId}&per_page=100`); // use pos API
                const text = await res.text();
                const data = JSON.parse(text);
                const products = data.data || [];
                renderMenu(products);
            } catch(e) {
                document.getElementById('menuList').innerHTML = '<div class="text-center text-red-500 py-8">Gagal memuat menu</div>';
            }
        }

        function renderMenu(products) {
            const html = products.filter(p => p.active && p.current_stock > 0).map(p => `
                <div class="card bg-white rounded-xl shadow-sm border p-4 mb-3 flex justify-between items-center cursor-pointer hover:shadow-md transition" onclick="addToCart(${p.id}, '${p.name}', ${p.selling_price})">
                    <div>
                        <div class="font-semibold text-gray-800">${p.name}</div>
                        <div class="text-sm text-gray-500">${p.category?.name || ''}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-indigo-600">Rp ${new Intl.NumberFormat('id-ID').format(p.selling_price)}</div>
                        <button class="mt-1 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">+ Tambah</button>
                    </div>
                </div>
            `).join('');
            document.getElementById('menuList').innerHTML = html || '<div class="text-center text-gray-400 py-8">Menu kosong</div>';
        }

        function addToCart(id, name, price) {
            const existing = cart.find(i => i.id === id);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({ id, name, price, qty: 1 });
            }
            updateCartUI();
        }

        function updateCartUI() {
            const footer = document.getElementById('cartFooter');
            const count = cart.reduce((s, i) => s + i.qty, 0);
            const total = cart.reduce((s, i) => s + (i.price * i.qty), 0);

            document.getElementById('cartCount').textContent = count + ' item';
            document.getElementById('cartTotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            footer.style.display = count > 0 ? 'block' : 'none';
        }

        async function submitOrder() {
            if (cart.length === 0) return;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            try {
                const items = cart.map(i => ({ product_id: i.id, quantity: i.qty, unit_price: i.price }));
                const res = await fetch(`${API}/orders`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({
                        items, table_id: tableId, outlet_id: outletId,
                        payments: [{ payment_method_id: 1, amount: cart.reduce((s,i) => s + i.price*i.qty, 0) }],
                        order_type: 'dine_in'
                    })
                });
                if (res.ok) {
                    alert('Pesanan berhasil dikirim!');
                    cart = [];
                    updateCartUI();
                } else {
                    alert('Gagal mengirim pesanan. Silakan panggil pelayan.');
                }
            } catch(e) {
                alert('Gagal terhubung. Silakan panggil pelayan.');
            }
        }

        loadMenu();
    </script>
</body>
</html>
