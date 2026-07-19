<div>
    <x-filament::section>
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" wire:model.live="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
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
        </div>
    </x-filament::section>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Penjualan</div>
            <div class="text-2xl font-extrabold text-gray-900">Rp {{ number_format($this->summary['total_sales'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Transaksi</div>
            <div class="text-2xl font-extrabold text-gray-900">{{ number_format($this->summary['total_orders'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Cash</div>
            <div class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($this->summary['cash'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Non-Cash</div>
            <div class="text-2xl font-extrabold text-indigo-600">Rp {{ number_format($this->summary['non_cash'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Biaya Hari Ini</div>
            <div class="text-2xl font-extrabold text-rose-600">Rp {{ number_format($this->summary['expenses'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Pendapatan per Metode Bayar</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Metode</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Transaksi</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->paymentsByMethod as $item)
                    <tr class="border-b border-gray-50">
                        <td class="py-3 font-medium">{{ $item->method }}</td>
                        <td class="py-3 text-right">{{ $item->count }}</td>
                        <td class="py-3 text-right font-medium">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Shift Aktif</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Kasir</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Modal Awal</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Kas Akhir</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Selisih</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->summary['shifts'] as $shift)
                    <tr class="border-b border-gray-50">
                        <td class="py-3">{{ $shift->user?->name ?: '-' }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($shift->ending_cash ?? 0, 0, ',', '.') }}</td>
                        <td class="py-3 text-right {{ ($shift->difference ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            Rp {{ number_format($shift->difference ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $shift->status === 'closed' ? 'bg-gray-100 text-gray-600' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $shift->status === 'closed' ? 'Tutup' : 'Buka' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada shift aktif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-emerald-100 mb-1">Net Cash (Cash - Biaya)</div>
                <div class="text-3xl font-extrabold">Rp {{ number_format($this->summary['net_cash'], 0, ',', '.') }}</div>
            </div>
            <div class="text-right text-emerald-100 text-sm">
                @if($this->summary['net_cash'] >= 0)
                    Surplus Hari Ini
                @else
                    Defisit Hari Ini
                @endif
            </div>
        </div>
    </div>
</div>
