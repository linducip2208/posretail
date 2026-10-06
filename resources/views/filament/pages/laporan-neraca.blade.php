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
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#2fb344] hover:bg-[#268f36] rounded-lg transition-colors shadow-sm">
                <x-ti name="download" class="w-4 h-4" />
                CSV
            </a>
            <a href="{{ route('export.neraca', ['as_of_date' => $this->asOfDate, 'outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#d63939] hover:bg-[#b22b2b] rounded-lg transition-colors shadow-sm">
                <x-ti name="file-description" class="w-4 h-4" />
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Aset</div>
            <div class="text-2xl font-extrabold text-[#206bc4]">Rp {{ number_format($this->totalAset, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Liabilitas</div>
            <div class="text-2xl font-extrabold text-[#d63939]">Rp {{ number_format($this->totalLiabilitas, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Ekuitas</div>
            <div class="text-2xl font-extrabold text-[#ae3ec9]">Rp {{ number_format($this->totalEkuitas, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Status Neraca</div>
            @if($this->isBalanced)
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-[#2fb344]"></span>
                <span class="text-2xl font-extrabold text-[#2fb344]">Seimbang</span>
            </div>
            <div class="text-xs mt-1 text-[#2fb344]">Aset = Liabilitas + Ekuitas</div>
            @else
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-[#d63939]"></span>
                <span class="text-2xl font-extrabold text-[#d63939]">Tidak Seimbang</span>
            </div>
            @php $diff = round($this->totalAset - ($this->totalLiabilitas + $this->totalEkuitas), 2); @endphp
            <div class="text-xs mt-1 text-[#d63939]">Selisih: Rp {{ number_format(abs($diff), 0, ',', '.') }}</div>
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
                <div class="flex items-center justify-between p-4 bg-[#206bc4]/5 rounded-lg">
                    <span class="font-semibold text-[#1a569d]">Aset</span>
                    <span class="text-lg font-extrabold text-[#1a569d]">Rp {{ number_format($this->totalAset, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-center text-2xl font-black text-gray-400">=</div>
                <div class="flex items-center justify-between p-4 bg-[#d63939]/8 rounded-lg">
                    <span class="font-semibold text-[#8f1d1d]">Liabilitas</span>
                    <span class="text-lg font-extrabold text-[#b22b2b]">Rp {{ number_format($this->totalLiabilitas, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-center text-2xl font-black text-gray-400">+</div>
                <div class="flex items-center justify-between p-4 bg-[#ae3ec9]/10 rounded-lg">
                    <span class="font-semibold text-[#862e9c]">Ekuitas</span>
                    <span class="text-lg font-extrabold text-[#862e9c]">Rp {{ number_format($this->totalEkuitas, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">
            <span class="inline-flex items-center gap-2">
                <x-ti name="circle-check" class="w-5 h-5 text-[#206bc4]" />
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
                        <td class="py-3 text-right font-semibold {{ $account->balance >= 0 ? 'text-gray-900' : 'text-[#d63939]' }}">Rp {{ number_format(abs($account->balance), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data akun aset</td>
                    </tr>
                    @endforelse
                    <tr class="border-t-2 border-gray-200 bg-[#206bc4]">
                        <td colspan="3" class="py-3 font-bold text-[#1a569d]">Total Aset</td>
                        <td class="py-3 text-right font-extrabold text-[#1a569d]">Rp {{ number_format($this->totalAset, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">
            <span class="inline-flex items-center gap-2">
                <x-ti name="currency-dollar" class="w-5 h-5 text-[#d63939]" />
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
                        <td class="py-3 text-right font-semibold {{ $account->balance >= 0 ? 'text-gray-900' : 'text-[#d63939]' }}">Rp {{ number_format(abs($account->balance), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data akun liabilitas</td>
                    </tr>
                    @endforelse
                    <tr class="border-t-2 border-gray-200 bg-[#d63939]/8/50">
                        <td colspan="3" class="py-3 font-bold text-[#8f1d1d]">Total Liabilitas</td>
                        <td class="py-3 text-right font-extrabold text-[#b22b2b]">Rp {{ number_format($this->totalLiabilitas, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">
            <span class="inline-flex items-center gap-2">
                <x-ti name="trending-up" class="w-5 h-5 text-[#ae3ec9]" />
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
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 {{ isset($account->is_summary) && $account->is_summary ? 'bg-[#ae3ec9]/10/50' : '' }}">
                        <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-3 font-mono text-xs text-gray-500">{{ $account->code ?? '' }}</td>
                        <td class="py-3 font-medium {{ isset($account->is_summary) && $account->is_summary ? 'text-[#862e9c]' : '' }}">{{ $account->name }}</td>
                        <td class="py-3 text-right font-semibold {{ $account->balance >= 0 ? 'text-gray-900' : 'text-[#d63939]' }}">Rp {{ number_format(abs($account->balance), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data akun ekuitas</td>
                    </tr>
                    @endforelse
                    <tr class="border-t-2 border-gray-200 bg-[#ae3ec9]/10/50">
                        <td colspan="3" class="py-3 font-bold text-[#862e9c]">Total Ekuitas</td>
                        <td class="py-3 text-right font-extrabold text-[#862e9c]">Rp {{ number_format($this->totalEkuitas, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/chart.js/chart.umd.min.js') }}"></script>
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

