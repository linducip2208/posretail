<div>
    <div class="flex flex-wrap gap-3 items-end mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
            <input type="date" wire:model.live="startDate" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai</label>
            <input type="date" wire:model.live="endDate" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Outlet</label>
            <select wire:model.live="outletId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Outlet</option>
                @foreach($this->outlets as $outlet)
                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Grup</label>
            <select wire:model.live="groupBy" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="daily">Harian</option>
                <option value="weekly">Mingguan</option>
                <option value="monthly">Bulanan</option>
            </select>
        </div>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('export.sales', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'csv']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('export.sales', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Penjualan</div>
            <div class="text-2xl font-extrabold text-gray-900">Rp {{ number_format($this->totalSales, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Transaksi</div>
            <div class="text-2xl font-extrabold text-gray-900">{{ number_format($this->totalOrders, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Rata-rata / Transaksi</div>
            <div class="text-2xl font-extrabold text-gray-900">Rp {{ number_format($this->avgOrder, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Diskon</div>
            <div class="text-2xl font-extrabold text-amber-600">Rp {{ number_format($this->totalDiscount, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Grafik Penjualan</h3>
        <canvas id="salesChart" height="80"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Pendapatan per Metode Bayar</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Metode Bayar</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Total</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Kontribusi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $revTotal = $this->revenueByPayment->sum('total'); @endphp
                    @forelse($this->revenueByPayment as $i => $item)
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-medium">{{ $item->method }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td class="py-3 text-right">{{ $revTotal > 0 ? round(($item->total / $revTotal) * 100) : 0 }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Detail Transaksi Pembayaran</h3>
            <span class="text-xs text-gray-400">{{ $this->paymentsDetail->count() }} transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">No. Order</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Pelanggan</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Outlet</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Metode</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Jumlah</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->paymentsDetail as $i => $p)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-mono text-xs">{{ $p->order_number }}</td>
                        <td class="py-3">{{ $p->customer_name ?: '-' }}</td>
                        <td class="py-3 text-xs text-gray-500">{{ $p->outlet_name ?: '-' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $p->method === 'Cash' || $p->method === 'Tunai' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $p->method === 'QRIS' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                {{ $p->method === 'Transfer' || $p->method === 'Debit' ? 'bg-amber-100 text-amber-700' : '' }}">
                                {{ $p->method }}
                            </span>
                        </td>
                        <td class="py-3 text-right font-medium">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                        <td class="py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-10 text-center text-gray-400">Belum ada data transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Produk Terlaris</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Produk</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Terjual</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->topProducts as $i => $product)
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-medium">{{ $product->name }}</td>
                        <td class="py-3 text-right">{{ number_format($product->total_qty) }}</td>
                        <td class="py-3 text-right font-medium">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data penjualan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function() {
    const el = document.getElementById('salesChart');
    if (!el) return;
    if (el._chart) el._chart.destroy();
    const ctx = el.getContext('2d');
    el._chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($this->chartLabels) !!},
            datasets: [{
                label: 'Pendapatan',
                data: {!! json_encode($this->chartData) !!},
                backgroundColor: 'rgba(79, 70, 229, 0.7)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'M' } }
            }
        }
    });
})();
</script>
