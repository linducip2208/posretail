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
