<div class="rp-page rp-accent-green">
    <x-report-head title="Laporan Keuangan" subtitle="Pendapatan vs pengeluaran, arus kas, dan piutang usaha" />

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
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Grup</label>
            <select wire:model.live="groupBy" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="daily">Harian</option>
                <option value="weekly">Mingguan</option>
                <option value="monthly">Bulanan</option>
            </select>
        </div>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('export.financial', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'csv']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#2fb344] hover:bg-[#268f36] rounded-lg transition-colors shadow-sm">
                <x-ti name="download" class="w-4 h-4" />
                CSV
            </a>
            <a href="{{ route('export.financial', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#d63939] hover:bg-[#b22b2b] rounded-lg transition-colors shadow-sm">
                <x-ti name="file-description" class="w-4 h-4" />
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Pendapatan</div>
            <div class="text-2xl font-extrabold text-[#2fb344]">Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Pengeluaran</div>
            <div class="text-2xl font-extrabold text-[#d63939]">Rp {{ number_format($this->totalExpense, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Laba / Rugi</div>
            <div class="text-2xl font-extrabold {{ $this->totalProfit >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]' }}">Rp {{ number_format(abs($this->totalProfit), 0, ',', '.') }}</div>
            <div class="text-xs mt-1 {{ $this->totalProfit >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]' }}">{{ $this->totalProfit >= 0 ? 'Laba' : 'Rugi' }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Piutang Belum Lunas</div>
            <div class="text-2xl font-extrabold text-[#c87f00]">Rp {{ number_format($this->unpaidSales, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Uang Masuk</div>
            <div class="text-xl font-extrabold text-[#2fb344]">Rp {{ number_format($this->cashFlow['money_in'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Uang Keluar</div>
            <div class="text-xl font-extrabold text-[#d63939]">Rp {{ number_format($this->cashFlow['money_out'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Arus Kas Bersih</div>
            <div class="text-xl font-extrabold {{ $this->cashFlow['net'] >= 0 ? 'text-[#2fb344]' : 'text-[#d63939]' }}">Rp {{ number_format(abs($this->cashFlow['net']), 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Pendapatan vs Pengeluaran</h3>
            <div class="rp-chart rp-chart--tall"><canvas id="revExpChart"></canvas></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Pendapatan per Metode Bayar</h3>
            <div class="rp-chart rp-chart--doughnut"><canvas id="paymentPieChart"></canvas></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Rincian Metode Bayar</h3>
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
                    @php $revenueTotal = $this->revenueByPayment->sum('total'); @endphp
                    @forelse($this->revenueByPayment as $i => $item)
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-medium">{{ $item->method }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td class="py-3 text-right">{{ $revenueTotal > 0 ? round(($item->total / $revenueTotal) * 100) : 0 }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data pembayaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mt-6">
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
                    @forelse($this->paymentsDetail as $i => $payment)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-mono text-xs">{{ $payment->order_number }}</td>
                        <td class="py-3">{{ $payment->customer_name ?: '-' }}</td>
                        <td class="py-3 text-xs text-gray-500">{{ $payment->outlet_name ?: '-' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $payment->method === 'Cash' || $payment->method === 'Tunai' ? 'bg-[#2fb344]/15 text-[#268f36]' : '' }}
                                {{ $payment->method === 'QRIS' ? 'bg-[#206bc4]/10 text-[#1a569d]' : '' }}
                                {{ $payment->method === 'Transfer' || $payment->method === 'Debit' ? 'bg-[#f59f00]/15 text-[#c87f00]' : '' }}">
                                {{ $payment->method }}
                            </span>
                        </td>
                        <td class="py-3 text-right font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td class="py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-400">Belum ada data transaksi pembayaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Daftar Transaksi Order</h3>
            <span class="text-xs text-gray-400">{{ $this->ordersDetail->count() }} order</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">No. Order</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Tanggal</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Outlet</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Pelanggan</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Subtotal</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Pajak</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Total</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Metode</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->ordersDetail as $order)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                        <td class="py-3 font-mono text-xs font-semibold">{{ $order->order_number }}</td>
                        <td class="py-3 text-xs text-gray-500">{{ $order->created_at->format('d/m/y H:i') }}</td>
                        <td class="py-3 text-xs">{{ $order->outlet?->name ?: '-' }}</td>
                        <td class="py-3">{{ $order->customer?->name ?: '-' }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                        <td class="py-3 text-right font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3">
                            @php $pm = $order->payments->first()?->paymentMethod?->name; @endphp
                            @if($pm)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $pm === 'Cash' || $pm === 'Tunai' ? 'bg-[#2fb344]/15 text-[#268f36]' : '' }}
                                {{ $pm === 'QRIS' ? 'bg-[#206bc4]/10 text-[#1a569d]' : '' }}
                                {{ $pm === 'Transfer' || $pm === 'Debit' ? 'bg-[#f59f00]/15 text-[#c87f00]' : '' }}">
                                {{ $pm }}
                            </span>
                            @else
                            <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $order->payment_status === 'paid' ? 'bg-[#2fb344]/15 text-[#268f36]' : '' }}
                                {{ $order->payment_status === 'partial' ? 'bg-[#f59f00]/15 text-[#c87f00]' : '' }}
                                {{ $order->payment_status === 'unpaid' ? 'bg-[#d63939]/10 text-[#b22b2b]' : '' }}">
                                {{ $order->payment_status === 'paid' ? 'Lunas' : ($order->payment_status === 'partial' ? 'Sebagian' : 'Belum') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-gray-400">Belum ada data transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('filament.pages.partials.report-charts')
<script>
(function() {
    PosChart.bar('revExpChart', {
        labels: {!! json_encode($this->chartLabels) !!},
        datasets: [
            {
                label: 'Pendapatan',
                data: {!! json_encode($this->chartRevenue) !!},
                color: '#10b981',
            },
            {
                label: 'Pengeluaran',
                data: {!! json_encode($this->chartExpense) !!},
                color: '#f43f5e',
            }
        ],
    });

    const methods = {!! json_encode($this->revenueByPayment->pluck('method')) !!};
    const totals = {!! json_encode($this->revenueByPayment->pluck('total')->map(fn($v) => (float)$v)) !!};

    PosChart.doughnut('paymentPieChart', {
        labels: methods,
        data: totals,
        colors: ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#a855f7', '#ec4899', '#14b8a6', '#f97316'],
        centerTitle: 'Total',
        centerValue: PosChart.compact((totals || []).reduce((a, b) => a + b, 0)),
    });
})();
</script>

