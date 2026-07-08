<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>POS — Point of Sale</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,700" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        #cartPanel { overflow: hidden !important; display: flex !important; flex-direction: column !important; }
        #cartItems { overflow-y: auto !important; flex: 1 1 0% !important; min-height: 0; }
        #cartSummary { flex-shrink: 0 !important; }
        @media (max-width: 767px) {
            #cartPanel { width: 100% !important; max-width: 24rem !important; }
        }
        .cart-item-enter { animation: slideIn 0.2s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-overlay { animation: fadeIn 0.2s ease; }
        .barcode-scanner { position: relative; }
        #barcodeInput { position: absolute; left: -9999px; opacity: 0; }
        .product-card { cursor: pointer; transition: all 0.15s; }
        .product-card:active { transform: scale(0.97); }
    </style>
</head>
<body class="font-sans bg-gray-50" style="display:flex;flex-direction:column;height:100vh;overflow:hidden">
    {{-- Hidden barcode input for USB scanner --}}
    <input type="text" id="barcodeInput" autocomplete="off">

    {{-- TOP BAR --}}
    <header class="bg-blue-600 text-white px-3 sm:px-4 py-2 flex items-center flex-wrap gap-2 sm:gap-4 shadow-lg z-10" style="flex-shrink:0">
        <div class="font-extrabold text-lg tracking-tight">POS</div>
        <div class="flex items-center gap-2 text-sm">
            <select id="customerId" class="bg-indigo-600 text-white rounded px-2 py-1 text-sm border border-indigo-500">
                @foreach($orderTypes as $type)
                <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                @endforeach
            </select>
            <select id="outletId" class="bg-indigo-600 text-white rounded px-2 py-1 text-sm border border-indigo-500">
                @forelse($outlets as $o)
                <option value="{{ $o->id }}">{{ $o->name }}</option>
                @empty
                <option value="">-- Tidak ada outlet --</option>
                @endforelse
            </select>
            <span id="queueDisplay" class="bg-green-500 text-white px-2 py-0.5 rounded font-bold text-xs hidden">#001</span>
        </div>
        <div class="flex-1"></div>
        @auth
        <span class="text-xs text-indigo-200">{{ auth()->user()->name }}</span>
        @else
        <a href="/admin/login" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded text-sm font-bold">LOGIN DULU</a>
        @endauth
        <button onclick="toggleScanner()" class="bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded text-sm flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Zm0 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Zm9.75-9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Zm0 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z"/></svg>
            Scan
        </button>
        <button onclick="connectPrinter()" class="bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded text-sm flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
            Print
        </button>
        <a href="/admin" class="bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded text-sm">Admin</a>
    </header>

    {{-- MAIN LAYOUT --}}
    <div id="mainLayout" style="display:flex;flex:1;min-height:0;overflow:hidden">
        <div id="productPanel" style="display:flex;flex-direction:column;flex:1;min-width:0;overflow:hidden">
            {{-- Search --}}
            <div class="p-3 bg-white border-b" style="flex-shrink:0">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#9ca3af" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input type="text" id="searchInput" placeholder="Cari produk atau scan barcode..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm">
                    </div>
                </div>
            </div>

            {{-- Product Grid --}}
            <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 content-start" style="flex:1;overflow-y:auto;overflow-x:hidden;min-height:0;padding:0.75rem">
                <div class="col-span-full text-center text-gray-400 py-20">Memuat produk...</div>
            </div>

            {{-- Pagination --}}
            <div id="pagination" class="p-2 bg-white border-t flex justify-center gap-1" style="flex-shrink:0"></div>
        </div>

        {{-- CART DRAWER BACKDROP (mobile) --}}
        <div id="cartBackdrop" onclick="closeCart()" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden"></div>

        {{-- CART PANEL --}}
        <div id="cartPanel" class="fixed md:static inset-y-0 right-0 z-40 w-full max-w-sm md:w-auto bg-white border-l shadow-2xl md:shadow-lg translate-x-full md:translate-x-0 transition-transform duration-300" style="display:flex;flex-direction:column;overflow:hidden;flex-shrink:0;width:20%">
            <div class="p-4 border-b bg-gray-50" style="flex-shrink:0">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-lg">Keranjang</h2>
                    <div class="flex items-center gap-2">
                        <span id="cartCount" class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs font-bold">0</span>
                        <button onclick="closeCart()" class="md:hidden text-gray-400 hover:text-red-600 text-2xl leading-none">&times;</button>
                    </div>
                </div>
                <div id="cartCustomer" class="mt-2 text-xs text-gray-500">
                    <select id="customerId" class="w-full border border-gray-200 rounded px-2 py-1 text-xs" onchange="updateCustomer()">
                        <option value="">Walk-in Customer</option>
                    </select>
                </div>
            </div>

            {{-- Cart Items --}}
            <div id="cartItems" style="flex:1;overflow-y:auto;padding:0.5rem;min-height:0">
                <div class="text-center text-gray-400 py-10 text-sm">Keranjang kosong</div>
            </div>

            {{-- Cart Summary --}}
            <div id="cartSummary" class="border-t bg-gray-50 p-4 hidden" style="flex-shrink:0">
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span id="subtotal" class="font-mono font-semibold">Rp 0</span></div>
                    <div class="flex justify-between"><span>Diskon</span><span id="discount" class="font-mono text-red-600">Rp 0</span></div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-2">
                            <input type="checkbox" id="useTax" checked onchange="updateSummary()" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Pajak (<span id="taxRateLabel">{{ $taxPercent }}</span>%)</span>
                        </span>
                        <span id="tax" class="font-mono">Rp 0</span>
                    </div>
                    <div class="flex justify-between font-bold text-base border-t pt-2 mt-2"><span>Total</span><span id="total" class="font-mono text-indigo-700">Rp 0</span></div>
                </div>
                <div class="flex gap-2 mt-3">
                    <button onclick="showPayment()" id="payBtn" class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition-colors">Bayar</button>
                    <button onclick="holdCart()" class="bg-yellow-500 text-white px-4 py-3 rounded-lg font-bold hover:bg-yellow-600 transition-colors text-sm" title="Tahan Transaksi">Hold</button>
                </div>
                <button onclick="showHeldCarts()" id="heldBadge" class="w-full mt-1.5 text-xs text-blue-600 hover:text-blue-800 py-1 hidden">0 transaksi ditahan</button>
                <button onclick="clearCart()" class="w-full mt-1.5 text-xs text-gray-500 hover:text-red-600 py-1">Kosongkan Keranjang</button>
            </div>
        </div>
    </div>

    {{-- MOBILE CART FAB --}}
    <button id="cartFab" onclick="openCart()" class="md:hidden fixed bottom-4 right-4 z-30 bg-blue-600 text-white rounded-full shadow-xl px-5 py-3 flex items-center gap-2 font-bold hover:bg-blue-700 active:scale-95 transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
        <span id="cartFabCount" class="bg-white text-blue-700 rounded-full w-6 h-6 flex items-center justify-center text-xs">0</span>
        <span id="cartFabTotal" class="font-mono text-sm">Rp 0</span>
    </button>
    <div id="scannerOverlay" class="fixed inset-0 bg-black/70 z-50 flex flex-col items-center justify-center hidden">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">Scan Barcode</h3>
                <button onclick="stopScanner()" class="text-gray-500 hover:text-red-600 text-2xl">&times;</button>
            </div>
            <div id="scannerView" class="bg-black rounded-xl overflow-hidden" style="height: 250px;">
                <video id="scannerVideo" class="w-full h-full object-cover"></video>
            </div>
            <p class="text-xs text-gray-500 mt-3 text-center">Arahkan kamera ke barcode produk</p>
            <p class="text-xs text-gray-400 mt-1 text-center">atau gunakan USB barcode scanner — langsung scan tanpa klik apa pun</p>
        </div>
    </div>

    {{-- PAYMENT MODAL --}}
    <div id="paymentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden modal-overlay" onclick="hidePayment()">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4" onclick="event.stopPropagation()">
            <h3 class="font-bold text-xl mb-4">Pembayaran</h3>
            <div id="paymentTotal" class="text-3xl font-extrabold text-indigo-700 mb-4 font-mono">Rp 0</div>

            <label class="block text-sm font-semibold text-gray-700 mb-1">Metode Bayar</label>
            <select id="paymentMethod" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 text-sm">
                @foreach($paymentMethods as $pm)
                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                @endforeach
            </select>

            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Dibayar</label>
            <input type="number" id="paidAmount" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-2 text-lg font-mono" placeholder="Rp 0" oninput="calculateChange()" onkeydown="if(event.key==='Enter')processPayment()" inputmode="numeric">
            <div id="changeDisplay" class="text-sm font-semibold text-green-600 mb-4 hidden">Kembalian: <span id="changeAmount" class="font-mono">Rp 0</span></div>

            <div class="flex gap-2">
                <button onclick="hidePayment()" class="flex-1 border border-gray-300 py-2.5 rounded-lg font-semibold hover:bg-gray-50">Batal</button>
                <button onclick="processPayment()" class="flex-1 bg-indigo-600 text-white py-2.5 rounded-lg font-bold hover:bg-indigo-700">Proses</button>
            </div>
        </div>
    </div>

    {{-- HELD CARTS MODAL --}}
    <div id="heldModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden modal-overlay" onclick="hideHeldCarts()">
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4 max-h-[80vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-xl">Transaksi Ditahan</h3>
                <button onclick="hideHeldCarts()" class="text-gray-400 hover:text-red-600 text-2xl">&times;</button>
            </div>
            <div id="heldList" class="space-y-2">
                <div class="text-center text-gray-400 py-6">Tidak ada transaksi ditahan</div>
            </div>
        </div>
    </div>

    {{-- RECEIPT PRINT IFRAME — menghindari popup blocker --}}
    <iframe id="printFrame" name="printFrame" style="display:none" title="Print Receipt"></iframe>

    <script>
        const API = '/api/pos';
        let cart = [];
        let heldCarts = [];
        let currentPage = 1;
        let scanning = false;
        let stream = null;
        let printerDevice = null;

        const RECEIPT = {
            appName: @json($appName),
            appLogo: @json($appLogo),
            footer: @json($receiptFooter),
            receiptFooter: @json($receiptFooter),
            storeAddress: @json($storeAddress),
            storePhone: @json($storePhone),
            showLogo: @json($receiptShowLogo),
            showName: @json($receiptShowName),
            showAddress: @json($receiptShowAddress),
            showPhone: @json($receiptShowPhone),
            showFooter: @json($receiptShowFooter),
        };

        if (typeof PosPrinter !== 'undefined') {
            PosPrinter.setConfig(RECEIPT);
        }

        // === USB BARCODE SCANNER ===
        const barcodeInput = document.getElementById('barcodeInput');
        let barcodeBuffer = '';
        let barcodeTimer = null;

        document.addEventListener('keydown', function(e) {
            // Don't capture barcode when payment modal is open
            if (!document.getElementById('paymentModal').classList.contains('hidden')) return;
            // Don't capture when typing in search or other inputs
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
            if (e.key === 'Enter' && barcodeBuffer.length > 3) {
                e.preventDefault();
                scanBarcode(barcodeBuffer);
                barcodeBuffer = '';
                return;
            }
            if (e.key.length === 1) {
                barcodeBuffer += e.key;
                if (barcodeTimer) clearTimeout(barcodeTimer);
                barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 50);
            }
        });
        // Only auto-focus barcode input when clicking on body (not modals/inputs)
        document.addEventListener('click', function(e) {
            if (document.getElementById('paymentModal').classList.contains('hidden') &&
                e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' &&
                e.target.tagName !== 'SELECT' && e.target.tagName !== 'BUTTON') {
                barcodeInput.focus();
            }
        });

        // === LOAD PRODUCTS ===
        async function loadProducts(page = 1) {
            const search = document.getElementById('searchInput').value;
            const catId = document.getElementById('categoryFilter')?.value || '';
            const grid = document.getElementById('productGrid');

            grid.innerHTML = '<div class="col-span-full text-center text-gray-400 py-20">Memuat...</div>';

            let url = `${API}/products?page=${page}&per_page=48`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (catId) url += `&category_id=${catId}`;

            const res = await fetch(url);
            const data = await res.json();

            renderProducts(data.data);
            renderPagination(data);
            currentPage = page;
        }

        function renderProducts(products) {
            const grid = document.getElementById('productGrid');
            if (!products || products.length === 0) {
                grid.innerHTML = '<div class="col-span-full text-center text-gray-400 py-20">Produk tidak ditemukan</div>';
                return;
            }

            grid.innerHTML = products.map(p => {
                const out = Number(p.current_stock) <= 0;
                const stockClass = out ? 'text-red-600' : (p.current_stock > 10 ? 'text-green-600' : 'text-orange-500');
                const stockLabel = out ? 'Stok 0' : p.current_stock;
                const cardClass = out
                    ? 'bg-white rounded-xl border border-gray-200 overflow-hidden relative opacity-60 grayscale'
                    : 'product-card bg-white rounded-xl border border-gray-200 overflow-hidden hover:border-indigo-300 hover:shadow-md relative';
                const clickAttr = out
                    ? 'style="cursor:not-allowed" onclick="alert(\'Stok habis — tidak bisa ditambahkan\')"'
                    : `onclick="addToCart(${p.id}, '${escapeHtml(p.name)}', ${p.selling_price})"`;
                const badge = out ? '<div class="absolute top-1 right-1 bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded z-10">HABIS</div>' : '';
                return `
                <div class="${cardClass}" ${clickAttr}>
                    ${badge}
                    <div class="h-24 bg-gray-100 flex items-center justify-center overflow-hidden">
                        <img src="${p.image || '/marketing/screens/default-product.png'}" alt="${escapeHtml(p.name)}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><rect fill=%22%23e2e8f0%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%2394a3b8%22 font-size=%2212%22>No Image</text></svg>'">
                    </div>
                    <div class="p-2">
                        <div class="text-xs font-semibold text-gray-800 line-clamp-2 leading-tight">${escapeHtml(p.name)}</div>
                        <div class="text-indigo-700 font-bold text-xs font-mono mt-1">${formatRupiah(p.selling_price)}</div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-[10px] text-gray-400 truncate max-w-[60px]">${p.sku || '-'}</span>
                            <span class="text-[10px] ${stockClass} font-semibold">${stockLabel}</span>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        function renderPagination(data) {
            const container = document.getElementById('pagination');
            if (!data.last_page || data.last_page <= 1) {
                container.innerHTML = '';
                return;
            }
            let html = '';
            for (let i = 1; i <= data.last_page; i++) {
                html += `<button onclick="loadProducts(${i})" class="px-3 py-1 rounded text-sm ${i === currentPage ? 'bg-indigo-600 text-white' : 'bg-gray-100 hover:bg-gray-200'}">${i}</button>`;
            }
            container.innerHTML = html;
        }

        // === SCAN BARCODE ===
        async function scanBarcode(code) {
            try {
                const res = await fetch(`${API}/barcode/${encodeURIComponent(code)}`);
                if (!res.ok) {
                    alert('Produk dengan barcode ' + code + ' tidak ditemukan');
                    return;
                }
                const product = await res.json();
                addToCart(product.id, product.name, product.selling_price);
                document.getElementById('searchInput').value = '';
            } catch (e) {
                alert('Gagal mencari barcode');
            }
        }

        // === CAMERA SCANNER ===
        async function toggleScanner() {
            if (scanning) { stopScanner(); return; }
            const overlay = document.getElementById('scannerOverlay');
            overlay.classList.remove('hidden');
            scanning = true;

            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                const video = document.getElementById('scannerVideo');
                video.srcObject = stream;
                video.play();
                scanLoop();
            } catch (e) {
                alert('Tidak bisa mengakses kamera. Gunakan USB barcode scanner.');
                stopScanner();
            }
        }

        function stopScanner() {
            scanning = false;
            document.getElementById('scannerOverlay').classList.add('hidden');
            if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        }

        // Simple camera-based barcode detection (uses BarcodeDetector API if available)
        async function scanLoop() {
            if (!scanning || !stream) return;
            if ('BarcodeDetector' in window) {
                try {
                    const detector = new BarcodeDetector({ formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a'] });
                    const video = document.getElementById('scannerVideo');
                    const barcodes = await detector.detect(video);
                    if (barcodes.length > 0) {
                        scanBarcode(barcodes[0].rawValue);
                        stopScanner();
                        return;
                    }
                } catch (e) {}
            }
            setTimeout(() => requestAnimationFrame(scanLoop), 500);
        }

        // === CART ===
        function addToCart(id, name, price) {
            const existing = cart.find(i => i.id === id);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({ id, name, price, qty: 1, discount: 0 });
            }
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function updateQty(index, delta) {
            cart[index].qty += delta;
            if (cart[index].qty <= 0) cart.splice(index, 1);
            renderCart();
        }

        function clearCart() {
            if (cart.length === 0) return;
            if (!confirm('Kosongkan keranjang?')) return;
            cart = [];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            const summary = document.getElementById('cartSummary');
            const count = document.getElementById('cartCount');

            count.textContent = cart.length;
            const fabCount = document.getElementById('cartFabCount');
            if (fabCount) fabCount.textContent = cart.reduce((s, i) => s + i.qty, 0);

            if (cart.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-400 py-10 text-sm">Keranjang kosong</div>';
                summary.classList.add('hidden');
                return;
            }

            summary.classList.remove('hidden');

            container.innerHTML = cart.map((item, i) => `
                <div class="cart-item-enter bg-gray-50 rounded-lg p-2 mb-2 border border-gray-100">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-gray-800 truncate">${escapeHtml(item.name)}</div>
                            <div class="text-xs text-gray-500 font-mono">${formatRupiah(item.price)}</div>
                        </div>
                        <button onclick="removeFromCart(${i})" class="text-red-400 hover:text-red-600 ml-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-1">
                            <button onclick="updateQty(${i}, -1)" class="w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm font-bold">-</button>
                            <span class="w-8 text-center font-mono text-sm">${item.qty}</span>
                            <button onclick="updateQty(${i}, 1)" class="w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm font-bold">+</button>
                        </div>
                        <span class="font-mono font-bold text-sm text-indigo-700">${formatRupiah(item.price * item.qty)}</span>
                    </div>
                </div>
            `).join('');

            updateSummary();
        }

        function updateSummary() {
            const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
            const discount = 0;
            const useTax = document.getElementById('useTax').checked;
            const taxRate = parseFloat(document.getElementById('taxRateLabel').textContent);
            const tax = useTax ? (subtotal - discount) * taxRate / 100 : 0;
            const total = subtotal - discount + tax;

            document.getElementById('subtotal').textContent = formatRupiah(subtotal);
            document.getElementById('discount').textContent = formatRupiah(discount);
            document.getElementById('tax').textContent = formatRupiah(tax);
            document.getElementById('total').textContent = formatRupiah(total);
            document.getElementById('payBtn').textContent = 'Bayar ' + formatRupiah(total);
            const fabTotal = document.getElementById('cartFabTotal');
            if (fabTotal) fabTotal.textContent = formatRupiah(total);
        }

        // === MOBILE CART DRAWER ===
        function openCart() {
            document.getElementById('cartPanel').classList.remove('translate-x-full');
            document.getElementById('cartBackdrop').classList.remove('hidden');
        }
        function closeCart() {
            document.getElementById('cartPanel').classList.add('translate-x-full');
            document.getElementById('cartBackdrop').classList.add('hidden');
        }

        function updateCustomer() {
            // Customer assignment handled server-side
        }

        function getTotal() {
            const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
            const useTax = document.getElementById('useTax').checked;
            const taxRate = parseFloat(document.getElementById('taxRateLabel').textContent);
            return useTax ? subtotal * (1 + taxRate / 100) : subtotal;
        }

        // === PAYMENT ===
        function showPayment() {
            if (cart.length === 0) return;
            const total = getTotal();
            document.getElementById('paymentTotal').textContent = formatRupiah(total);
            document.getElementById('paymentModal').classList.remove('hidden');
            const paidInput = document.getElementById('paidAmount');
            paidInput.value = Math.ceil(total / 1000) * 1000;
            setTimeout(() => { paidInput.focus(); paidInput.select(); }, 100);
            calculateChange();
        }

        function hidePayment() {
            document.getElementById('paymentModal').classList.add('hidden');
            document.getElementById('changeDisplay').classList.add('hidden');
        }

        function calculateChange() {
            const total = getTotal();
            const paid = parseInt(document.getElementById('paidAmount').value) || 0;
            const change = paid - total;

            const display = document.getElementById('changeDisplay');
            if (paid > 0) {
                display.classList.remove('hidden');
                document.getElementById('changeAmount').textContent = formatRupiah(change);
                display.className = `text-sm font-semibold mb-4 ${change >= 0 ? 'text-green-600' : 'text-red-600'}`;
            } else {
                display.classList.add('hidden');
            }
        }

        async function processPayment() {
            const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
            const total = getTotal();
            const paid = parseInt(document.getElementById('paidAmount').value) || 0;
            if (paid < total) { alert('Jumlah dibayar kurang!'); return; }

            const outletId = document.getElementById('outletId').value;
            if (!outletId) { alert('Anda belum memiliki akses outlet. Hubungi admin.'); return; }

            const payload = {
                outlet_id: outletId,
                order_type: document.getElementById('customerId').value,
                customer_id: document.getElementById('customerId').value || null,
                table_id: null,
                items: cart.map(i => ({ id: i.id, qty: i.qty, price: i.price })),
                payment_method_id: document.getElementById('paymentMethod').value,
                paid_amount: paid,
                use_tax: document.getElementById('useTax').checked,
            };

            try {
                const res = await fetch('/pos/checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify(payload),
                });

                if (res.status === 401 || res.status === 419) {
                    alert('Sesi habis. Silakan login dulu.');
                    window.location.href = '/admin/login';
                    return;
                }

                const text = await res.text();
                if (!res.ok && (text.startsWith('<!DOCTYPE') || text.startsWith('<html'))) {
                    alert('Terjadi kesalahan server. Silakan coba lagi.');
                    return;
                }

                const data = JSON.parse(text);

                if (data.success) {
                    hidePayment();
                    const orderNumber = data.order_number;
                    const queueNumber = data.queue_number;
                    if (queueNumber) {
                        document.getElementById('queueDisplay').textContent = '#' + queueNumber;
                        document.getElementById('queueDisplay').classList.remove('hidden');
                    }
                    const cartSnapshot = [...cart];
                    cart = [];
                    renderCart();

                    printToIframe(cartSnapshot, orderNumber, paid, data.change || (paid - data.total));
                } else {
                    alert('Gagal: ' + (data.message || 'Unknown error'));
                }
            } catch (e) {
                alert('Gagal memproses pembayaran: ' + e.message);
            }
        }

        // === PRINT via IFRAME (tidak kena popup blocker) ===
        function printToIframe(cartItems, orderNumber, paid, change) {
            const iframe = document.getElementById('printFrame');
            if (!iframe) return;
            const subtotal = cartItems.reduce((s, i) => s + (i.price * i.qty), 0);
            const useTax = document.getElementById('useTax').checked;
            const taxRate = parseFloat(document.getElementById('taxRateLabel').textContent);
            const total = useTax ? subtotal * (1 + taxRate / 100) : subtotal;
            const now = new Date();
            const dateStr = now.toLocaleDateString('id-ID') + ' ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            let itemsHtml = cartItems.map(i =>
                `<tr><td>${escapeHtml(i.name).substring(0,16)}</td><td class="r">${i.qty}</td><td class="r">${formatRupiah(i.price)}</td><td class="r">${formatRupiah(i.price * i.qty)}</td></tr>`
            ).join('');

            let headerHtml = '';
            if (RECEIPT.showLogo && RECEIPT.appLogo) {
                headerHtml += `<div class="c" style="margin-bottom:2mm"><img src="${RECEIPT.appLogo}" style="max-width:60mm; max-height:20mm; display:block; margin:0 auto;" onerror="this.style.display='none'"></div>`;
            }
            if (RECEIPT.showName) {
                headerHtml += `<div class="c b">${escapeHtml(RECEIPT.appName)}</div>`;
            }
            if (RECEIPT.showAddress && RECEIPT.storeAddress) {
                headerHtml += `<div class="c" style="font-size:10px">${escapeHtml(RECEIPT.storeAddress)}</div>`;
            }
            if (RECEIPT.showPhone && RECEIPT.storePhone) {
                headerHtml += `<div class="c" style="font-size:10px">Telp: ${escapeHtml(RECEIPT.storePhone)}</div>`;
            }
            headerHtml += `<div class="c" style="font-size:10px">${document.getElementById('outletId').options[document.getElementById('outletId').selectedIndex]?.text || ''}</div>`;

            const receiptHtml = `
                <!DOCTYPE html>
                <html><head><meta charset="UTF-8"><style>
                    @page { margin: 0; size: 80mm auto; }
                    body { font-family: 'Courier New', monospace; font-size: 12px; width: 72mm; margin: 4mm auto; -webkit-print-color-adjust: exact; }
                    .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; }
                    hr { border: none; border-top: 1px dashed #000; }
                    table { width: 100%; } td { padding: 1px 0; }
                </style></head><body>
                    ${headerHtml}
                    <hr>
                    <div>No: ${orderNumber}<span style="float:right">${dateStr}</span></div>
                    <hr>
                    <table>
                        <tr class="b" style="font-size:10px"><td>Item</td><td class="r">Qty</td><td class="r">Harga</td><td class="r">Sub</td></tr>
                        ${itemsHtml}
                    </table>
                    <hr>
                    <div class="b" style="font-size:14px">TOTAL<span style="float:right">${formatRupiah(total)}</span></div>
                    <hr>
                    <div>Dibayar<span style="float:right">${formatRupiah(paid)}</span></div>
                    <div>Kembali<span style="float:right">${formatRupiah(change)}</span></div>
                    <hr>
                    ${RECEIPT.showFooter ? `<div class="c" style="font-size:10px">${escapeHtml(RECEIPT.footer)}</div>` : ''}
                </body></html>
            `;

            const doc = iframe.contentDocument || iframe.contentWindow.document;

            iframe.onload = function() {
                setTimeout(function() {
                    iframe.contentWindow.print();
                }, 300);
            };

            doc.open();
            doc.write(receiptHtml);
            doc.close();
        }

        async function connectPrinter() {
            if (!('bluetooth' in navigator)) { alert('Browser tidak mendukung Bluetooth'); return; }
            try {
                const device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                });
                printerDevice = device;
                alert('Printer terhubung: ' + device.name);
            } catch (e) {
                alert('Gagal menghubungkan printer Bluetooth');
            }
        }

        // === HELPERS ===
        function formatRupiah(n) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
        }
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // === HOLD & RECALL ===
        function holdCart() {
            if (cart.length === 0) return;
            const label = prompt('Label transaksi (opsional):', 'Transaksi #' + (heldCarts.length + 1));
            heldCarts.push({
                id: Date.now(),
                label: label || ('Transaksi #' + (heldCarts.length + 1)),
                items: JSON.parse(JSON.stringify(cart)),
                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
            });
            cart = [];
            renderCart();
            updateHeldBadge();
        }

        function recallHeld(id) {
            const held = heldCarts.find(h => h.id === id);
            if (!held) return;
            cart = JSON.parse(JSON.stringify(held.items));
            heldCarts = heldCarts.filter(h => h.id !== id);
            renderCart();
            updateHeldBadge();
            hideHeldCarts();
        }

        function removeHeld(id) {
            if (!confirm('Hapus transaksi ditahan?')) return;
            heldCarts = heldCarts.filter(h => h.id !== id);
            updateHeldBadge();
            showHeldCarts();
        }

        function showHeldCarts() {
            const modal = document.getElementById('heldModal');
            const list = document.getElementById('heldList');

            if (heldCarts.length === 0) {
                list.innerHTML = '<div class="text-center text-gray-400 py-6">Tidak ada transaksi ditahan</div>';
            } else {
                list.innerHTML = heldCarts.map(h => {
                    const total = h.items.reduce((s, i) => s + (i.price * i.qty), 0) * 1.11;
                    return `<div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-gray-800">${escapeHtml(h.label)}</span>
                            <span class="text-xs text-gray-400">${h.time}</span>
                        </div>
                        <div class="text-sm text-gray-500 mb-2">${h.items.length} item &bull; ${h.items.map(i=>i.qty).reduce((a,b)=>a+b,0)} pcs</div>
                        <div class="font-mono font-bold text-blue-600 mb-3">${formatRupiah(total)}</div>
                        <div class="flex gap-2">
                            <button onclick="recallHeld(${h.id})" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-blue-700">Lanjutkan</button>
                            <button onclick="removeHeld(${h.id})" class="border border-red-300 text-red-600 px-4 py-1.5 rounded-lg text-sm hover:bg-red-50">Hapus</button>
                        </div>
                    </div>`;
                }).join('');
            }
            modal.classList.remove('hidden');
        }

        function hideHeldCarts() {
            document.getElementById('heldModal').classList.add('hidden');
        }

        function updateHeldBadge() {
            const badge = document.getElementById('heldBadge');
            if (heldCarts.length > 0) {
                badge.textContent = heldCarts.length + ' transaksi ditahan — klik untuk lihat';
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // === INIT ===
        document.getElementById('searchInput').addEventListener('input', function() {
            loadProducts(1);
        });
        loadProducts();

        // Keep session alive — ping every 4 minutes
        setInterval(async function() {
            try {
                await fetch('/api/pos/products?per_page=1&search=__ping__', {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
                });
            } catch(e) {}
        }, 4 * 60 * 1000);
    </script>
    <script src="{{ asset('js/pos-printer.js') }}"></script>
</body>
</html>
