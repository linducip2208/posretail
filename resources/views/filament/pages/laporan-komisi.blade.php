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
            <label class="block text-sm font-medium text-gray-700 mb-1">Pegawai</label>
            <select wire:model.live="userId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Pegawai</option>
                @foreach($this->users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Komisi</div>
            <div class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($this->totalKomisi, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Transaksi</div>
            <div class="text-2xl font-extrabold text-gray-900">{{ number_format($this->totalTransaksi, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Rata-rata Komisi</div>
            <div class="text-2xl font-extrabold text-indigo-600">Rp {{ number_format($this->rataKomisi, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Grafik Komisi</h3>
        <canvas id="commissionChart" height="80"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Komisi per Pegawai</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Pegawai</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Total Order</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Total Penjualan</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Persen Komisi</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Total Komisi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->komisiPerUser as $i => $row)
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-medium">{{ $row->user_name }}</td>
                        <td class="py-3 text-right">{{ number_format($row->total_orders) }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($row->total_sales, 0, ',', '.') }}</td>
                        <td class="py-3 text-right">{{ $row->commission_percent }}%</td>
                        <td class="py-3 text-right font-semibold text-emerald-600">Rp {{ number_format($row->total_commission, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-10 text-center text-gray-400">Belum ada data komisi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Detail Komisi</h3>
            <span class="text-xs text-gray-400">{{ $this->komisiDetail->count() }} transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">No. Order</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Pegawai</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Pelanggan</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Outlet</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Total</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Komisi %</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Komisi</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->komisiDetail as $i => $item)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-mono text-xs">{{ $item->order_number }}</td>
                        <td class="py-3 font-medium">{{ $item->user_name }}</td>
                        <td class="py-3">{{ $item->customer_name ?: '-' }}</td>
                        <td class="py-3 text-xs text-gray-500">{{ $item->outlet_name ?: '-' }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 text-right">{{ $item->commission_percent }}%</td>
                        <td class="py-3 text-right font-semibold text-emerald-600">Rp {{ number_format($item->commission_amount, 0, ',', '.') }}</td>
                        <td class="py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="py-10 text-center text-gray-400">Belum ada data komisi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function() {
    const el = document.getElementById('commissionChart');
    if (!el) return;
    if (el._chart) el._chart.destroy();
    const ctx = el.getContext('2d');
    el._chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($this->chartLabels) !!},
            datasets: [{
                label: 'Komisi',
                data: {!! json_encode($this->chartData) !!},
                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                borderColor: 'rgba(16, 185, 129, 1)',
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
