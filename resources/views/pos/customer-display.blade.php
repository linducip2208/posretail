<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Display — {{ $appName }}</title>
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; overflow: hidden; }
        .item-row { animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        .pulse { animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-8">
    <div class="w-full max-w-4xl">
        <div class="text-center mb-8">
            <div class="text-5xl font-extrabold mb-2">{{ $appName }}</div>
            <div class="text-xl text-slate-400" id="outletName"></div>
        </div>

        <div class="bg-slate-800 rounded-2xl p-8 mb-6">
            <div class="text-center mb-6">
                <div class="text-lg text-slate-400 mb-1">TOTAL BELANJA</div>
                <div class="text-7xl font-extrabold text-emerald-400" id="totalDisplay">Rp 0</div>
            </div>

            <div class="grid grid-cols-2 gap-6 text-center">
                <div class="bg-slate-700/50 rounded-xl p-5">
                    <div class="text-sm text-slate-400 mb-1">ITEM</div>
                    <div class="text-3xl font-bold" id="itemCount">0</div>
                </div>
                <div class="bg-slate-700/50 rounded-xl p-5">
                    <div class="text-sm text-slate-400 mb-1">ANTRIAN</div>
                    <div class="text-3xl font-bold text-amber-400" id="queueNumber">-</div>
                </div>
            </div>
        </div>

        <div class="bg-slate-800 rounded-2xl p-6" style="max-height: 40vh; overflow-y: auto;">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700">
                        <th class="pb-3 w-12">#</th>
                        <th class="pb-3">Item</th>
                        <th class="pb-3 text-right w-20">Qty</th>
                        <th class="pb-3 text-right w-40">Harga</th>
                        <th class="pb-3 text-right w-40">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="itemList">
                    <tr><td colspan="5" class="py-12 text-center text-slate-500">Menunggu transaksi...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const API = '/api/pos';
        let lastOrderId = null;
        let outletId = null;

        async function poll() {
            try {
                const res = await fetch(`${API}/display?outlet_id=${outletId || ''}`);
                if (!res.ok) return;
                const data = await res.json();

                if (data.order_id && data.order_id !== lastOrderId) {
                    lastOrderId = data.order_id;
                    updateDisplay(data);
                }
            } catch (e) { /* ignore */ }
        }

        function updateDisplay(data) {
            document.getElementById('totalDisplay').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total || 0);
            document.getElementById('itemCount').textContent = data.items?.length || 0;
            document.getElementById('queueNumber').textContent = '#' + (data.queue_number || '-');
            document.getElementById('outletName').textContent = data.outlet_name || '';

            const tbody = document.getElementById('itemList');
            if (data.items && data.items.length > 0) {
                tbody.innerHTML = data.items.map((item, i) => `
                    <tr class="item-row border-b border-slate-700/50">
                        <td class="py-3 text-slate-500">${i + 1}</td>
                        <td class="py-3 font-medium">${item.name}${item.variant ? ' <span class="text-slate-500">(' + item.variant + ')</span>' : ''}</td>
                        <td class="py-3 text-right text-slate-400">${item.qty}</td>
                        <td class="py-3 text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.price || 0)}</td>
                        <td class="py-3 text-right font-semibold">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal || 0)}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-slate-500">Menunggu transaksi...</td></tr>';
            }
        }

        setInterval(poll, 1500);
        poll();
    </script>
</body>
</html>
