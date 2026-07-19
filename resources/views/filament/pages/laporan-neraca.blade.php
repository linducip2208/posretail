<div>
    <div class="flex flex-wrap gap-3 items-end mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Per Tanggal</label>
            <input type="date" wire:model.live="asOfDate" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
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
            <a href="{{ route('export.neraca', ['as_of_date' => $this->asOfDate, 'outlet_id' => $this->outletId, 'format' => 'csv']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('export.neraca', ['as_of_date' => $this->asOfDate, 'outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Aset</div>
            <div class="text-2xl font-extrabold text-blue-600">Rp {{ number_format($this->totalAset, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Liabilitas</div>
            <div class="text-2xl font-extrabold text-rose-600">Rp {{ number_format($this->totalLiabilitas, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Ekuitas</div>
            <div class="text-2xl font-extrabold text-purple-600">Rp {{ number_format($this->totalEkuitas, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Status Neraca</div>
            @if($this->isBalanced)
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-2xl font-extrabold text-emerald-600">Seimbang</span>
            </div>
            <div class="text-xs mt-1 text-emerald-500">Aset = Liabilitas + Ekuitas</div>
            @else
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                <span class="text-2xl font-extrabold text-rose-600">Tidak Seimbang</span>
            </div>
            @php $diff = round($this->totalAset - ($this->totalLiabilitas + $this->totalEkuitas), 2); @endphp
            <div class="text-xs mt-1 text-rose-500">Selisih: Rp {{ number_format(abs($diff), 0, ',', '.') }}</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Komposisi Neraca</h3>
            <canvas id="balanceChart" height="80"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Persamaan Dasar Akuntansi</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                    <span class="font-semibold text-blue-800">Aset</span>
                    <span class="text-lg font-extrabold text-blue-700">Rp {{ number_format($this->totalAset, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-center text-2xl font-black text-gray-400">=</div>
                <div class="flex items-center justify-between p-4 bg-rose-50 rounded-lg">
                    <span class="font-semibold text-rose-800">Liabilitas</span>
                    <span class="text-lg font-extrabold text-rose-700">Rp {{ number_format($this->totalLiabilitas, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-center text-2xl font-black text-gray-400">+</div>
                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                    <span class="font-semibold text-purple-800">Ekuitas</span>
                    <span class="text-lg font-extrabold text-purple-700">Rp {{ number_format($this->totalEkuitas, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">
            <span class="inline-flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Aset
            </span>
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Kode</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Nama Akun</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->asetAccounts as $i => $account)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-mono text-xs text-gray-500">{{ $account->code }}</td>
                        <td class="py-3 font-medium">{{ $account->name }}</td>
                        <td class="py-3 text-right font-semibold {{ $account->balance >= 0 ? 'text-gray-900' : 'text-rose-600' }}">Rp {{ number_format(abs($account->balance), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data akun aset</td>
                    </tr>
                    @endforelse
                    <tr class="border-t-2 border-gray-200 bg-blue-50/50">
                        <td colspan="3" class="py-3 font-bold text-blue-800">Total Aset</td>
                        <td class="py-3 text-right font-extrabold text-blue-700">Rp {{ number_format($this->totalAset, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">
            <span class="inline-flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Liabilitas
            </span>
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Kode</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Nama Akun</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->liabilitasAccounts as $i => $account)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-mono text-xs text-gray-500">{{ $account->code }}</td>
                        <td class="py-3 font-medium">{{ $account->name }}</td>
                        <td class="py-3 text-right font-semibold {{ $account->balance >= 0 ? 'text-gray-900' : 'text-rose-600' }}">Rp {{ number_format(abs($account->balance), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data akun liabilitas</td>
                    </tr>
                    @endforelse
                    <tr class="border-t-2 border-gray-200 bg-rose-50/50">
                        <td colspan="3" class="py-3 font-bold text-rose-800">Total Liabilitas</td>
                        <td class="py-3 text-right font-extrabold text-rose-700">Rp {{ number_format($this->totalLiabilitas, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">
            <span class="inline-flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Ekuitas
            </span>
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Kode</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Nama Akun</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->ekuitasAccounts as $i => $account)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 {{ isset($account->is_summary) && $account->is_summary ? 'bg-purple-50/50' : '' }}">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-mono text-xs text-gray-500">{{ $account->code ?? '' }}</td>
                        <td class="py-3 font-medium {{ isset($account->is_summary) && $account->is_summary ? 'text-purple-700' : '' }}">{{ $account->name }}</td>
                        <td class="py-3 text-right font-semibold {{ $account->balance >= 0 ? 'text-gray-900' : 'text-rose-600' }}">Rp {{ number_format(abs($account->balance), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data akun ekuitas</td>
                    </tr>
                    @endforelse
                    <tr class="border-t-2 border-gray-200 bg-purple-50/50">
                        <td colspan="3" class="py-3 font-bold text-purple-800">Total Ekuitas</td>
                        <td class="py-3 text-right font-extrabold text-purple-700">Rp {{ number_format($this->totalEkuitas, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function() {
    const chartEl = document.getElementById('balanceChart');
    if (!chartEl) return;
    if (chartEl._chart) chartEl._chart.destroy();

    chartEl._chart = new Chart(chartEl.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Aset', 'Liabilitas', 'Ekuitas'],
            datasets: [{
                data: [
                    {{ $this->totalAset }},
                    {{ $this->totalLiabilitas }},
                    {{ $this->totalEkuitas }}
                ],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(244, 63, 94, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                ],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 20 }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const v = ctx.parsed;
                            return 'Rp ' + v.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
})();
</script>
