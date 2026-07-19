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
        <div class="ml-auto flex gap-2">
            <a href="{{ route('export.laba-rugi', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'csv']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('export.laba-rugi', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Pendapatan</div>
            <div class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">HPP</div>
            <div class="text-2xl font-extrabold text-rose-600">Rp {{ number_format($this->totalCogs, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Laba Kotor</div>
            <div class="text-2xl font-extrabold {{ $this->grossProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format(abs($this->grossProfit), 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Beban</div>
            <div class="text-2xl font-extrabold text-amber-600">Rp {{ number_format($this->totalExpenses, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Laba Bersih</div>
            <div class="text-2xl font-extrabold {{ $this->netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format(abs($this->netProfit), 0, ',', '.') }}</div>
            @if($this->totalRevenue > 0)
            <div class="text-xs mt-1 {{ $this->netProfit >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">Margin: {{ $this->netProfitMargin }}%</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Komposisi Pendapatan</h3>
            <canvas id="revenuePieChart" height="80"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Rincian Beban</h3>
            <canvas id="expenseBarChart" height="80"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Ringkasan Laba Rugi</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Keterangan</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 font-semibold text-gray-800">Pendapatan</td>
                        <td class="py-3 text-right font-bold text-emerald-600">Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 pl-6 text-gray-600">Harga Pokok Penjualan (HPP)</td>
                        <td class="py-3 text-right text-rose-600">(Rp {{ number_format($this->totalCogs, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b-2 border-gray-200">
                        <td class="py-3 font-semibold text-gray-800">Laba Kotor</td>
                        <td class="py-3 text-right font-bold {{ $this->grossProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format($this->grossProfit, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 font-semibold text-gray-800">Beban Usaha</td>
                        <td class="py-3 text-right text-rose-600">(Rp {{ number_format($this->totalExpenses, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b-2 border-gray-200">
                        <td class="py-3 font-semibold text-base text-gray-900">Laba / Rugi Bersih</td>
                        <td class="py-3 text-right font-extrabold text-base {{ $this->netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format(abs($this->netProfit), 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Rincian Akun</h3>
            <span class="text-xs text-gray-400">{{ $this->accountDetails->count() }} akun</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Kode</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Nama Akun</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Tipe</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Debit</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Kredit</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @php $categories = ['Pendapatan', 'HPP', 'Beban']; @endphp
                    @foreach($categories as $cat)
                        @php $catItems = $this->accountDetails->where('category', $cat); @endphp
                        @if($catItems->isNotEmpty())
                        <tr class="bg-gray-50">
                            <td colspan="7" class="py-2 px-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $cat }}</td>
                        </tr>
                        @foreach($catItems as $i => $item)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                            <td class="py-3 text-gray-400">{{ $loop->parent->index + 1 }}.{{ $loop->index + 1 }}</td>
                            <td class="py-3 font-mono text-xs text-gray-500">{{ $item->code }}</td>
                            <td class="py-3 font-medium">{{ $item->name }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $item->type === 'revenue' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $item->type === 'cogs' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $item->type === 'expense' ? 'bg-rose-100 text-rose-700' : '' }}">
                                    {{ $cat }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-mono text-xs">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td>
                            <td class="py-3 text-right font-mono text-xs">Rp {{ number_format($item->total_credit, 0, ',', '.') }}</td>
                            <td class="py-3 text-right font-semibold {{ $item->balance >= 0 ? 'text-gray-900' : 'text-rose-600' }}">Rp {{ number_format(abs($item->balance), 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        @endif
                    @endforeach
                    @if($this->accountDetails->isEmpty())
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-400">Belum ada data jurnal pada periode ini</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function() {
    const revenueData = {!! json_encode($this->revenueChartData) !!};
    const expenseData = {!! json_encode($this->expenseChartData) !!};

    const pieEl = document.getElementById('revenuePieChart');
    if (pieEl) {
        if (pieEl._chart) pieEl._chart.destroy();
        const colors = [
            'rgba(16, 185, 129, 0.8)',
            'rgba(79, 70, 229, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(249, 115, 22, 0.8)',
        ];
        pieEl._chart = new Chart(pieEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: revenueData.labels,
                datasets: [{
                    data: revenueData.data,
                    backgroundColor: colors.slice(0, revenueData.labels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 16 }
                    }
                }
            }
        });
    }

    const barEl = document.getElementById('expenseBarChart');
    if (barEl) {
        if (barEl._chart) barEl._chart.destroy();
        barEl._chart = new Chart(barEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: expenseData.labels,
                datasets: [{
                    label: 'Beban',
                    data: expenseData.data,
                    backgroundColor: 'rgba(244, 63, 94, 0.7)',
                    borderColor: 'rgba(244, 63, 94, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'M' } }
                }
            }
        });
    }
})();
</script>
