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
            <a href="{{ route('export.financial', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'csv']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('export.financial', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Pendapatan</div>
            <div class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Pengeluaran</div>
            <div class="text-2xl font-extrabold text-rose-600">Rp {{ number_format($this->totalExpense, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Laba / Rugi</div>
            <div class="text-2xl font-extrabold {{ $this->totalProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format(abs($this->totalProfit), 0, ',', '.') }}</div>
            <div class="text-xs mt-1 {{ $this->totalProfit >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">{{ $this->totalProfit >= 0 ? 'Laba' : 'Rugi' }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Piutang Belum Lunas</div>
            <div class="text-2xl font-extrabold text-amber-600">Rp {{ number_format($this->unpaidSales, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Uang Masuk</div>
            <div class="text-xl font-extrabold text-emerald-600">Rp {{ number_format($this->cashFlow['money_in'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Uang Keluar</div>
            <div class="text-xl font-extrabold text-rose-600">Rp {{ number_format($this->cashFlow['money_out'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Arus Kas Bersih</div>
            <div class="text-xl font-extrabold {{ $this->cashFlow['net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format(abs($this->cashFlow['net']), 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Pendapatan vs Pengeluaran</h3>
            <canvas id="revExpChart" height="80"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Pendapatan per Metode Bayar</h3>
            <canvas id="paymentPieChart" height="80"></canvas>
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
                                {{ $payment->method === 'Cash' || $payment->method === 'Tunai' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $payment->method === 'QRIS' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                {{ $payment->method === 'Transfer' || $payment->method === 'Debit' ? 'bg-amber-100 text-amber-700' : '' }}">
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
                                {{ $pm === 'Cash' || $pm === 'Tunai' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $pm === 'QRIS' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                {{ $pm === 'Transfer' || $pm === 'Debit' ? 'bg-amber-100 text-amber-700' : '' }}">
                                {{ $pm }}
                            </span>
                            @else
                            <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $order->payment_status === 'partial' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $order->payment_status === 'unpaid' ? 'bg-red-100 text-red-700' : '' }}">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function() {
    const revEl = document.getElementById('revExpChart');
    if (revEl) {
        if (revEl._chart) revEl._chart.destroy();
        revEl._chart = new Chart(revEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($this->chartLabels) !!},
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: {!! json_encode($this->chartRevenue) !!},
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'Pengeluaran',
                        data: {!! json_encode($this->chartExpense) !!},
                        backgroundColor: 'rgba(244, 63, 94, 0.7)',
                        borderColor: 'rgba(244, 63, 94, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20 }
                    }
                },
                scales: {
                    y: { ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'M' } }
                }
            }
        });
    }

    const pieEl = document.getElementById('paymentPieChart');
    if (pieEl) {
        if (pieEl._chart) pieEl._chart.destroy();
        const methods = {!! json_encode($this->revenueByPayment->pluck('method')) !!};
        const totals = {!! json_encode($this->revenueByPayment->pluck('total')->map(fn($v) => (float)$v)) !!};
        const colors = [
            'rgba(79, 70, 229, 0.8)',
            'rgba(16, 185, 129, 0.8)',
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
                labels: methods,
                datasets: [{
                    data: totals,
                    backgroundColor: colors.slice(0, methods.length),
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
})();
</script>
