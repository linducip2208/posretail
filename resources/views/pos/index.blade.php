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
    <header class="bg-white text-slate-700 px-3 sm:px-4 py-2 flex items-center flex-wrap gap-2 sm:gap-3 border-b border-slate-200 shadow-sm z-10" style="flex-shrink:0">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-[#206bc4] text-white flex items-center justify-center font-extrabold">P</span>
            <div class="font-extrabold text-lg tracking-tight text-slate-800">POS</div>
        </div>
        <div class="flex items-center gap-2 text-sm">
            <select id="orderType" class="bg-slate-100 text-slate-700 rounded-lg px-2 py-1.5 text-sm border border-slate-200 outline-none focus:border-[#206bc4]">
                @foreach($orderTypes as $type)
                <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                @endforeach
            </select>
            <select id="outletId" class="bg-slate-100 text-slate-700 rounded-lg px-2 py-1.5 text-sm border border-slate-200 outline-none focus:border-[#206bc4]">
                @forelse($outlets as $o)
                <option value="{{ $o->id }}">{{ $o->name }}</option>
                @empty
                <option value="">-- Tidak ada outlet --</option>
                @endforelse
            </select>
            <span id="queueDisplay" class="bg-[#2fb344] text-white px-2 py-0.5 rounded-md font-bold text-xs hidden">#001</span>
        </div>
        <div class="flex-1"></div>
        <span id="syncQueueBadge" class="bg-[#f59f00] text-white px-2 py-0.5 rounded-full text-xs font-bold mr-1 hidden" title="Transaksi offline pending sync">0</span>
        <button onclick="window.open('/pos/display','_blank','width=1024,height=768')" aria-label="Buka customer display" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-sm font-medium flex items-center gap-1.5 mr-1 border border-slate-200 min-h-[40px]" title="Customer Display">
            <x-ti name="device-desktop" class="w-4 h-4" />
            Display
        </button>
        @auth
        <span class="text-xs text-slate-500 flex items-center gap-1.5"><x-ti name="user" class="w-4 h-4" />{{ auth()->user()->name }}</span>
        @else
        <a href="/admin/login" class="bg-[#d63939] hover:bg-[#c22f2f] text-white px-3 py-1.5 rounded-lg text-sm font-bold">LOGIN DULU</a>
        @endauth
        <button onclick="toggleScanner()" aria-label="Scan barcode kamera" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-sm font-medium flex items-center gap-1.5 border border-slate-200 min-h-[40px]">
            <x-ti name="barcode" class="w-4 h-4" />
            Scan
        </button>
        <button onclick="connectPrinter()" aria-label="Hubungkan printer" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-sm font-medium flex items-center gap-1.5 border border-slate-200 min-h-[40px]">
            <x-ti name="printer" class="w-4 h-4" />
            Print
        </button>
        <a href="/admin" class="bg-[#206bc4]/10 hover:bg-[#206bc4]/20 text-[#206bc4] px-3 py-1.5 rounded-lg text-sm font-semibold">Admin</a>
    </header>

    {{-- MAIN LAYOUT --}}
    <div id="mainLayout" style="display:flex;flex:1;min-height:0;overflow:hidden">
        <div id="productPanel" style="display:flex;flex-direction:column;flex:1;min-width:0;overflow:hidden">
            {{-- Search + Category --}}
            <div class="p-3 bg-white border-b border-slate-200" style="flex-shrink:0">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <x-ti name="search" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input type="text" id="searchInput" placeholder="Cari produk atau scan barcode... (F1)" aria-label="Cari produk" class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#206bc4]/30 focus:border-[#206bc4] outline-none text-sm bg-white">
                    </div>
                    <select id="categoryFilter" aria-label="Filter kategori" onchange="loadProducts(1)" class="border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-white text-slate-700 outline-none focus:border-[#206bc4] max-w-[10rem]">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hidden lg:flex items-center gap-3 mt-2 text-[11px] text-slate-400" aria-hidden="true">
                    <span><kbd class="px-1 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono">F1</kbd> Cari</span>
                    <span><kbd class="px-1 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono">F2</kbd> Bayar</span>
                    <span><kbd class="px-1 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono">F3</kbd> Scan</span>
                    <span><kbd class="px-1 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono">F4</kbd> Bersihkan</span>
                    <span><kbd class="px-1 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono">F5</kbd> Hold</span>
                    <span><kbd class="px-1 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono">Esc</kbd> Tutup</span>
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
                        <span id="cartCount" class="bg-[#206bc4]/10 text-[#206bc4] px-2 py-0.5 rounded-full text-xs font-bold">0</span>
                        <button onclick="closeCart()" class="md:hidden text-gray-400 hover:text-[#d63939] text-2xl leading-none">&times;</button>
                    </div>
                </div>
                <div id="cartCustomer" class="mt-2 text-xs text-gray-500">
                    <select id="customerSelect" class="w-full border border-gray-200 rounded px-2 py-1 text-xs" onchange="updateCustomer()">
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
                <div class="flex gap-2 mb-2">
                    <input type="text" id="voucherInput" placeholder="Kode voucher" class="flex-1 border border-gray-200 rounded px-2 py-1.5 text-sm uppercase" onkeydown="if(event.key==='Enter'){event.preventDefault();applyVoucher();}">
                    <button onclick="applyVoucher()" class="bg-gray-800 text-white px-3 py-1.5 rounded text-sm font-semibold hover:bg-gray-700">Pakai</button>
                </div>
                <div id="voucherStatus" class="hidden text-xs mb-2"></div>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span id="subtotal" class="font-mono font-semibold">Rp 0</span></div>
                    <div class="flex justify-between"><span>Diskon</span><span id="discount" class="font-mono text-[#d63939]">Rp 0</span></div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-2">
                            <input type="checkbox" id="useTax" checked onchange="updateSummary()" class="w-4 h-4 rounded border-gray-300 text-[#206bc4] focus:ring-[#206bc4]">
                            <span>Pajak (<span id="taxRateLabel">{{ $taxPercent }}</span>%)</span>
                        </span>
                        <span id="tax" class="font-mono">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center font-bold text-lg border-t-2 border-[#206bc4] bg-[#206bc4]/5 -mx-4 px-4 pt-2 pb-1 mt-2"><span>TOTAL</span><span id="total" class="font-mono text-[#1a569d] text-xl">Rp 0</span></div>
                </div>
                <div class="flex gap-2 mt-3">
                    <button onclick="showPayment()" id="payBtn" class="flex-1 bg-[#206bc4] text-white py-3 rounded-lg font-bold hover:bg-[#1a569d] active:scale-[0.98] transition-all shadow-sm">Bayar</button>
                    <button onclick="holdCart()" class="bg-[#f59f00] text-white px-4 py-3 rounded-lg font-bold hover:bg-[#e89400] active:scale-[0.98] transition-all text-sm shadow-sm" title="Tahan Transaksi">Hold</button>
                </div>
                <button onclick="showHeldCarts()" id="heldBadge" class="w-full mt-1.5 text-xs text-[#206bc4] hover:text-[#1a569d] py-1 hidden">0 transaksi ditahan</button>
                <button onclick="clearCart()" class="w-full mt-1.5 text-xs text-gray-500 hover:text-[#d63939] py-1">Kosongkan Keranjang</button>
            </div>
        </div>
    </div>

    {{-- MOBILE CART FAB --}}
    <button id="cartFab" onclick="openCart()" class="md:hidden fixed bottom-4 right-4 z-30 bg-[#206bc4] text-white rounded-full shadow-xl px-5 py-3 flex items-center gap-2 font-bold hover:bg-[#1a569d] active:scale-95 transition">
        <x-ti name="shopping-cart" class="w-6 h-6" />
        <span id="cartFabCount" class="bg-white text-[#1a569d] rounded-full w-6 h-6 flex items-center justify-center text-xs">0</span>
        <span id="cartFabTotal" class="font-mono text-sm">Rp 0</span>
    </button>
    <div id="scannerOverlay" class="fixed inset-0 bg-black/70 z-50 flex flex-col items-center justify-center hidden">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">Scan Barcode</h3>
                <button onclick="stopScanner()" class="text-gray-500 hover:text-[#d63939] text-2xl">&times;</button>
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
            <div id="paymentTotal" class="text-3xl font-extrabold text-[#1a569d] mb-4 font-mono">Rp 0</div>

            <label class="block text-sm font-semibold text-gray-700 mb-1">Metode Bayar</label>
            <select id="paymentMethod" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 text-sm">
                @foreach($paymentMethods as $pm)
                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                @endforeach
            </select>

            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Dibayar</label>
            <input type="number" id="paidAmount" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-2 text-lg font-mono" placeholder="Rp 0" oninput="calculateChange()" onkeydown="if(event.key==='Enter')processPayment()" inputmode="numeric">
            <div id="changeDisplay" class="text-sm font-semibold text-[#2fb344] mb-4 hidden">Kembalian: <span id="changeAmount" class="font-mono">Rp 0</span></div>

            <div class="flex gap-2">
                <button onclick="hidePayment()" class="flex-1 border border-gray-300 py-2.5 rounded-lg font-semibold hover:bg-gray-50">Batal</button>
                <button onclick="processPayment()" class="flex-1 bg-[#206bc4] text-white py-2.5 rounded-lg font-bold hover:bg-[#1a569d]">Proses</button>
            </div>
        </div>
    </div>

    {{-- HELD CARTS MODAL --}}
    <div id="heldModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden modal-overlay" onclick="hideHeldCarts()">
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4 max-h-[80vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-xl">Transaksi Ditahan</h3>
                <button onclick="hideHeldCarts()" class="text-gray-400 hover:text-[#d63939] text-2xl">&times;</button>
            </div>
            <div id="heldList" class="space-y-2">
                <div class="text-center text-gray-400 py-6">Tidak ada transaksi ditahan</div>
            </div>
        </div>
    </div>

    {{-- SERIAL / IMEI MODAL --}}
    <div id="serialModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden modal-overlay" onclick="cancelSerials()">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-lg">Input IMEI / Serial</h3>
                <button onclick="cancelSerials()" class="text-gray-500 hover:text-[#d63939] text-2xl">&times;</button>
            </div>
            <p class="text-sm text-gray-600 mb-3">Produk: <span id="serialProductName" class="font-semibold text-gray-900"></span></p>
            <textarea id="serialInput" rows="5" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono" placeholder="Satu IMEI per baris.&#10;Contoh:&#10;356789012345678&#10;356789012345679"></textarea>
            <p class="text-xs text-gray-400 mt-1 mb-4">Scan barcode IMEI langsung atau paste dari Excel.</p>
            <div class="flex gap-2">
                <button onclick="cancelSerials()" class="flex-1 border border-gray-300 py-2.5 rounded-lg font-semibold hover:bg-gray-50">Batal</button>
                <button id="serialSkip" onclick="skipSerials()" class="border border-gray-300 py-2.5 px-3 rounded-lg font-semibold hover:bg-gray-50 text-gray-600" style="display:none">Lewati</button>
                <button onclick="confirmSerials()" class="flex-1 bg-[#206bc4] text-white py-2.5 rounded-lg font-bold hover:bg-[#1a569d]">Simpan</button>
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
        let voucherDiscount = 0;
        let voucherCode = '';

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

            grid.innerHTML = Array.from({length: 12}).map(() =>
                '<div class="bg-white rounded-lg border border-slate-200 overflow-hidden animate-pulse"><div class="h-24 bg-slate-200"></div><div class="p-2 space-y-2"><div class="h-3 w-4/5 bg-slate-200 rounded"></div><div class="h-3 w-2/5 bg-slate-200 rounded"></div></div></div>'
            ).join('');

            let url = `${API}/products?page=${page}&per_page=48`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (catId) url += `&category_id=${catId}`;

            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();

                renderProducts(data.data);
                renderPagination(data);
                currentPage = page;
            } catch (e) {
                grid.innerHTML = '<div class="col-span-full text-center py-16 px-4"><div class="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M2 9a15 15 0 0 1 20 0"/><path d="M5.5 12.5a10 10 0 0 1 13 0"/><path d="M9 16a5 5 0 0 1 6 0"/><path d="M12 19.5h.01"/></svg></div><div class="font-bold text-slate-700 mb-1">Gagal memuat produk</div><div class="text-sm text-slate-500 mb-4">Periksa koneksi lalu coba lagi.</div><button onclick="loadProducts(' + page + ')" class="bg-[#206bc4] hover:bg-[#1a569d] text-white font-semibold text-sm px-5 py-2.5 rounded-lg min-h-[42px]">Coba Lagi</button></div>';
            }
        }

        function renderProducts(products) {
            const grid = document.getElementById('productGrid');
            if (!products || products.length === 0) {
                grid.innerHTML = '<div class="col-span-full text-center py-16 px-4"><div class="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><circle cx="10" cy="10" r="7"/><path d="M21 21l-6 -6"/></svg></div><div class="font-bold text-slate-700 mb-1">Produk tidak ditemukan</div><div class="text-sm text-slate-500">Coba kata kunci atau kategori lain.</div></div>';
                return;
            }

            grid.innerHTML = products.map(p => {
                const out = Number(p.current_stock) <= 0;
                const stockClass = out ? 'text-[#d63939]' : (p.current_stock > 10 ? 'text-[#2fb344]' : 'text-[#fd7e14]');
                const stockLabel = out ? 'Stok 0' : p.current_stock;
                const cardClass = out
                    ? 'bg-white rounded-lg border border-slate-200 overflow-hidden relative opacity-60 grayscale'
                    : 'product-card bg-white rounded-lg border border-slate-200 overflow-hidden hover:border-[#206bc4] hover:shadow-md relative';
                const clickAttr = out
                    ? 'style="cursor:not-allowed" onclick="alert(\'Stok habis — tidak bisa ditambahkan\')"'
                    : `onclick="addToCart(${p.id}, '${escapeHtml(p.name)}', ${p.selling_price}, '${p.serial_tracking || 'none'}')"`;
                const badge = out ? '<div class="absolute top-1 right-1 bg-[#d63939] text-white text-[9px] font-bold px-1.5 py-0.5 rounded z-10">HABIS</div>'
                    : ((p.variants && p.variants.length > 0) ? '<div class="absolute top-1 right-1 bg-[#206bc4] text-white text-[9px] font-bold px-1.5 py-0.5 rounded z-10">' + p.variants.length + ' VARIAN</div>' : '');
                return `
                <div class="${cardClass}" ${clickAttr}>
                    ${badge}
                    <div class="h-24 bg-gray-100 flex items-center justify-center overflow-hidden">
                        <img src="${p.image || '/marketing/screens/default-product.png'}" alt="${escapeHtml(p.name)}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><rect fill=%22%23e2e8f0%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%2394a3b8%22 font-size=%2212%22>No Image</text></svg>'">
                    </div>
                    <div class="p-2">
                        <div class="text-xs font-semibold text-gray-800 line-clamp-2 leading-tight">${escapeHtml(p.name)}</div>
                        <div class="text-[#1a569d] font-bold text-xs font-mono mt-1">${formatRupiah(p.selling_price)}</div>
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
                html += `<button onclick="loadProducts(${i})" class="px-3 py-1 rounded-md text-sm font-medium ${i === currentPage ? 'bg-[#206bc4] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}">${i}</button>`;
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
                addToCart(product.id, product.name, product.selling_price, product.serial_tracking || 'none');
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
        function addToCart(id, name, price, serialTracking) {
            const tracking = serialTracking || 'none';
            if (tracking === 'required') {
                promptSerials(name, false).then(serials => {
                    if (!serials) return;
                    if (!serials.length) { alert('IMEI wajib diisi untuk produk ini.'); return; }
                    addSerializedToCart(id, name, price, tracking, serials);
                });
                return;
            }
            if (tracking === 'optional') {
                promptSerials(name, true).then(serials => {
                    if (serials === null) return;
                    addSerializedToCart(id, name, price, tracking, serials);
                });
                return;
            }
            const existing = cart.find(i => i.id === id);
            if (existing) existing.qty++;
            else cart.push({ id, name, price, qty: 1, discount: 0, serials: [], serialTracking: 'none' });
            renderCart();
        }

        function addSerializedToCart(id, name, price, tracking, serials) {
            const existing = cart.find(i => i.id === id);
            if (existing) {
                if (serials.length) {
                    existing.qty += serials.length;
                    existing.serials = existing.serials || [];
                    existing.serials.push(...serials);
                }
            } else {
                cart.push({ id, name, price, qty: serials.length || 1, discount: 0, serials: serials, serialTracking: tracking });
            }
            renderCart();
        }

        function addImeiToItem(index) {
            const item = cart[index];
            promptSerials(item.name, false).then(serials => {
                if (!serials || !serials.length) return;
                if (serials.length !== item.qty) { alert('Jumlah IMEI harus sama dengan qty (' + item.qty + ').'); return; }
                item.serials = serials;
                renderCart();
            });
        }

        function promptSerials(name, allowSkip) {
            return new Promise((resolve) => {
                document.getElementById('serialProductName').textContent = name;
                document.getElementById('serialInput').value = '';
                document.getElementById('serialSkip').style.display = allowSkip ? 'inline-block' : 'none';
                document.getElementById('serialModal').classList.remove('hidden');
                window._serialResolve = resolve;
            });
        }

        function confirmSerials() {
            const raw = document.getElementById('serialInput').value;
            const serials = raw.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
            if (!serials.length) { alert('Masukkan minimal 1 IMEI'); return; }
            document.getElementById('serialModal').classList.add('hidden');
            window._serialResolve(serials);
        }

        function cancelSerials() {
            document.getElementById('serialModal').classList.add('hidden');
            window._serialResolve(null);
        }

        function skipSerials() {
            document.getElementById('serialModal').classList.add('hidden');
            window._serialResolve([]);
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function updateQty(index, delta) {
            const item = cart[index];
            const hasSerials = item.serialTracking === 'required' || (item.serials && item.serials.length > 0);
            if (hasSerials) {
                if (delta < 0) {
                    item.serials.pop();
                    item.qty = item.serials.length;
                    if (item.qty <= 0) cart.splice(index, 1);
                    renderCart();
                } else {
                    promptSerials(item.name, item.serialTracking !== 'required').then(serials => {
                        if (!serials || !serials.length) return;
                        item.serials.push(...serials);
                        item.qty = item.serials.length;
                        renderCart();
                    });
                }
                return;
            }
            cart[index].qty += delta;
            if (cart[index].qty <= 0) cart.splice(index, 1);
            renderCart();
        }

        function clearCart() {
            if (cart.length === 0) return;
            if (!confirm('Kosongkan keranjang?')) return;
            cart = [];
            clearVoucher();
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
                        <button onclick="removeFromCart(${i})" class="text-[#e57373] hover:text-[#d63939] ml-1">
                            <x-ti name="x" class="w-4 h-4" />
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-1">
                            <button onclick="updateQty(${i}, -1)" class="w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm font-bold">-</button>
                            <span class="w-8 text-center font-mono text-sm">${item.qty}</span>
                            <button onclick="updateQty(${i}, 1)" class="w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm font-bold">+</button>
                        </div>
                        <span class="font-mono font-bold text-sm text-[#1a569d]">${formatRupiah(item.price * item.qty)}</span>
                    </div>
                    ${(item.serials && item.serials.length)
                        ? `<div class="mt-1 text-[10px] text-gray-500 font-mono truncate">IMEI: ${item.serials.map(s => escapeHtml(s)).join(', ')}</div>`
                        : (item.serialTracking === 'optional'
                            ? `<div class="mt-1"><button onclick="addImeiToItem(${i})" class="text-[10px] text-[#206bc4] hover:text-[#1a569d] font-semibold">+ Tambah IMEI</button></div>`
                            : '')}
                </div>
            `).join('');

            updateSummary();
        }

        function updateSummary() {
            const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
            const discount = voucherDiscount;
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
            return useTax ? (subtotal - voucherDiscount) * (1 + taxRate / 100) : (subtotal - voucherDiscount);
        }

        async function applyVoucher() {
            const code = document.getElementById('voucherInput').value.trim().toUpperCase();
            const statusEl = document.getElementById('voucherStatus');
            const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);

            if (!code) { alert('Masukkan kode voucher terlebih dahulu.'); return; }
            if (subtotal <= 0) { alert('Keranjang masih kosong.'); return; }

            try {
                const res = await fetch('/api/pos/validate-voucher', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ code: code, subtotal: subtotal }),
                });
                const data = await res.json();
                if (!res.ok) {
                    voucherDiscount = 0;
                    voucherCode = '';
                    statusEl.className = 'text-xs mb-2 text-[#d63939] font-semibold';
                    statusEl.textContent = (data.message || 'Voucher tidak valid.');
                    statusEl.classList.remove('hidden');
                    updateSummary();
                    return;
                }
                voucherDiscount = parseFloat(data.discount) || 0;
                voucherCode = code;
                statusEl.className = 'text-xs mb-2 text-[#2fb344] font-semibold';
                statusEl.textContent = 'Voucher ' + code + ' — diskon ' + formatRupiah(voucherDiscount);
                statusEl.classList.remove('hidden');
                updateSummary();
            } catch (e) {
                alert('Gagal memvalidasi voucher.');
            }
        }

        function clearVoucher() {
            voucherDiscount = 0;
            voucherCode = '';
            document.getElementById('voucherInput').value = '';
            document.getElementById('voucherStatus').classList.add('hidden');
            updateSummary();
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
                display.className = `text-sm font-semibold mb-4 ${change >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]'}`;
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
                order_type: document.getElementById('orderType').value,
                customer_id: parseInt(document.getElementById('customerSelect').value) || null,
                items: cart.map(i => ({ id: i.id, qty: i.qty, price: i.price, serial_numbers: i.serials || [] })),
                payment_method_id: document.getElementById('paymentMethod').value,
                paid_amount: paid,
                use_tax: document.getElementById('useTax').checked,
                voucher_code: voucherCode || null,
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
                    clearVoucher();
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
                        <div class="font-mono font-bold text-[#206bc4] mb-3">${formatRupiah(total)}</div>
                        <div class="flex gap-2">
                            <button onclick="recallHeld(${h.id})" class="bg-[#206bc4] text-white px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-[#1a569d]">Lanjutkan</button>
                            <button onclick="removeHeld(${h.id})" class="border border-[#e8a3a3] text-[#d63939] px-4 py-1.5 rounded-lg text-sm hover:bg-[#d63939]/8">Hapus</button>
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

        // Offline mode
        (function() {
            const indicator = document.createElement('div');
            indicator.id = 'offlineIndicator';
            indicator.style.cssText = 'display:none;position:fixed;top:0;left:0;right:0;background:#ef4444;color:white;text-align:center;padding:4px;font-size:12px;font-weight:600;z-index:9999';
            indicator.textContent = 'OFFLINE — Transaksi akan disimpan & otomatis sync saat online';
            document.body.prepend(indicator);

            function updateStatus() {
                const offline = !navigator.onLine;
                indicator.style.display = offline ? 'block' : 'none';
                const syncBadge = document.getElementById('syncQueueBadge');
                if (syncBadge) {
                    const count = PosOffline.getQueueCount();
                    syncBadge.textContent = count;
                    syncBadge.style.display = count > 0 ? 'inline-block' : 'none';
                }
            }

            window.addEventListener('online', async () => {
                updateStatus();
                const result = await PosOffline.syncQueue('/pos/checkout');
                if (result.synced > 0) {
                    alert('Sync: ' + result.synced + ' transaksi offline berhasil dikirim.' + (result.failed > 0 ? ' ' + result.failed + ' gagal.' : ''));
                }
                updateStatus();
            });

            window.addEventListener('offline', updateStatus);
            updateStatus();

            // Pre-cache products on load
            PosOffline.cacheProducts('/api/pos/products');
        })();

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                if (e.key === 'Escape') e.target.blur();
                return;
            }
            switch(e.key) {
                case 'F1': document.getElementById('searchInput')?.focus(); e.preventDefault(); break;
                case 'F2':
                    document.getElementById('paymentModal')?.classList.remove('hidden');
                    document.getElementById('paidAmount')?.focus();
                    e.preventDefault();
                    break;
                case 'F3':
                    if (typeof toggleScanner === 'function') toggleScanner();
                    e.preventDefault();
                    break;
                case 'F4':
                    if (typeof clearCart === 'function') {
                        if (cart.length > 0 && confirm('Hapus semua item dari keranjang?')) clearCart();
                    }
                    e.preventDefault();
                    break;
                case 'F5':
                    if (typeof heldCart === 'function' && cart.length > 0) heldCart();
                    e.preventDefault();
                    break;
                case 'F8':
                    if (typeof connectPrinter === 'function') connectPrinter();
                    e.preventDefault();
                    break;
                case 'Escape':
                    document.getElementById('paymentModal')?.classList.add('hidden');
                    document.getElementById('scannerOverlay')?.classList.add('hidden');
                    e.preventDefault();
                    break;
            }
        });

        // Show shortcuts hint
        console.log('POS Shortcuts: F1=Search F2=Bayar F3=Scan F4=Clear F5=Hold F8=Print Esc=Tutup');
    </script>
    <script src="{{ asset('js/pos-offline.js') }}"></script>
    <script src="{{ asset('js/pos-printer.js') }}"></script>
</body>
</html>
