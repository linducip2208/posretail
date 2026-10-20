<div class="rp-page rp-accent-teal">
    <x-report-head title="Laporan Laba Rugi" subtitle="Pendapatan, beban, dan laba bersih per periode" />

    <div class="rp-toolbar">
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
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#2fb344] hover:bg-[#268f36] rounded-lg transition-colors shadow-sm">
                <x-ti name="download" class="w-4 h-4" />
                CSV
            </a>
            <a href="{{ route('export.laba-rugi', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#d63939] hover:bg-[#b22b2b] rounded-lg transition-colors shadow-sm">
                <x-ti name="file-description" class="w-4 h-4" />
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Pendapatan</div>
            <div class="text-2xl font-extrabold text-[#2fb344]">Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">HPP</div>
            <div class="text-2xl font-extrabold text-[#d63939]">Rp {{ number_format($this->totalCogs, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Laba Kotor</div>
            <div class="text-2xl font-extrabold {{ $this->grossProfit >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]' }}">Rp {{ number_format(abs($this->grossProfit), 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Beban</div>
            <div class="text-2xl font-extrabold text-[#c87f00]">Rp {{ number_format($this->totalExpenses, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Laba Bersih</div>
            <div class="text-2xl font-extrabold {{ $this->netProfit >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]' }}">Rp {{ number_format(abs($this->netProfit), 0, ',', '.') }}</div>
            @if($this->totalRevenue > 0)
            <div class="text-xs mt-1 {{ $this->netProfit >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]' }}">Margin: {{ $this->netProfitMargin }}%</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Komposisi Pendapatan</h3>
            <div class="rp-chart rp-chart--doughnut"><canvas id="revenuePieChart"></canvas></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Rincian Beban</h3>
            <div class="rp-chart rp-chart--tall"><canvas id="expenseBarChart"></canvas></div>
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
                        <td class="py-3 text-right font-bold text-[#2fb344]">Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 pl-6 text-gray-600">Harga Pokok Penjualan (HPP)</td>
                        <td class="py-3 text-right text-[#d63939]">(Rp {{ number_format($this->totalCogs, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b-2 border-gray-200">
                        <td class="py-3 font-semibold text-gray-800">Laba Kotor</td>
                        <td class="py-3 text-right font-bold {{ $this->grossProfit >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]' }}">Rp {{ number_format($this->grossProfit, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 font-semibold text-gray-800">Beban Usaha</td>
                        <td class="py-3 text-right text-[#d63939]">(Rp {{ number_format($this->totalExpenses, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b-2 border-gray-200">
                        <td class="py-3 font-semibold text-base text-gray-900">Laba / Rugi Bersih</td>
                        <td class="py-3 text-right font-extrabold text-base {{ $this->netProfit >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]' }}">Rp {{ number_format(abs($this->netProfit), 0, ',', '.') }}</td>
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
                                    {{ $item->type === 'revenue' ? 'bg-[#2fb344]/15 text-[#268f36]' : '' }}
                                    {{ $item->type === 'cogs' ? 'bg-[#fd7e14]/15 text-[#d15a08]' : '' }}
                                    {{ $item->type === 'expense' ? 'bg-[#d63939]/10 text-[#b22b2b]' : '' }}">
                                    {{ $cat }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-mono text-xs">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td>
                            <td class="py-3 text-right font-mono text-xs">Rp {{ number_format($item->total_credit, 0, ',', '.') }}</td>
                            <td class="py-3 text-right font-semibold {{ $item->balance >= 0 ? 'text-gray-900' : 'text-[#d63939]' }}">Rp {{ number_format(abs($item->balance), 0, ',', '.') }}</td>
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

@include('filament.pages.partials.report-charts')
<script>
(function() {
    const revenueData = {!! json_encode($this->revenueChartData) !!};
    const expenseData = {!! json_encode($this->expenseChartData) !!};

    PosChart.doughnut('revenuePieChart', {
        labels: revenueData.labels,
        data: revenueData.data,
        colors: ['#10b981', '#6366f1', '#f59e0b', '#3b82f6', '#a855f7', '#ec4899', '#14b8a6', '#f97316'],
        centerTitle: 'Pendapatan',
        centerValue: PosChart.compact((revenueData.data || []).reduce((a, b) => a + b, 0)),
    });

    PosChart.bar('expenseBarChart', {
        labels: expenseData.labels,
        datasets: [{
            label: 'Beban',
            data: expenseData.data,
            color: '#f43f5e',
        }],
        horizontal: true,
    });
})();
</script>

