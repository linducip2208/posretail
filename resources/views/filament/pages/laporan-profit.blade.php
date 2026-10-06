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
        <div class="ml-auto flex gap-2 flex-wrap">
            <a href="{{ route('export.profit', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'csv']) }}"
               class="px-4 py-2 text-sm font-semibold text-white bg-[#2fb344] rounded-lg">CSV Profit</a>
            <a href="{{ route('export.profit.xlsx', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId]) }}"
               class="px-4 py-2 text-sm font-semibold text-white bg-[#206bc4] rounded-lg">Excel Profit</a>
            <a href="{{ route('export.stock-slow', ['outlet_id' => $this->outletId, 'format' => 'xlsx']) }}"
               class="px-4 py-2 text-sm font-semibold text-white bg-orange-600 rounded-lg">Excel Stok Lambat</a>
            <a href="{{ route('export.comprehensive.xlsx', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId]) }}"
               class="px-4 py-2 text-sm font-semibold text-white bg-purple-700 rounded-lg">Komprehensif (6 sheet)</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-5">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Omzet (periode)</div>
            <div class="text-2xl font-extrabold">Rp {{ number_format($this->totalOmzet, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Laba Kotor</div>
            <div class="text-2xl font-extrabold text-green-600">Rp {{ number_format($this->totalLaba, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border p-5 mb-6">
        <h3 class="font-semibold mb-4">Profit / HPP per Produk</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left border-b">
                    <th class="pb-2">SKU</th><th class="pb-2">Produk</th><th class="pb-2 text-right">Qty</th>
                    <th class="pb-2 text-right">Omzet</th><th class="pb-2 text-right">HPP</th>
                    <th class="pb-2 text-right">Laba</th><th class="pb-2 text-right">Margin</th>
                </tr></thead>
                <tbody>
                    @forelse($this->profitRows as $r)
                    <tr class="border-b border-gray-50">
                        <td class="py-2 font-mono text-xs">{{ $r[0] }}</td>
                        <td class="py-2">{{ $r[1] }} <span class="text-xs text-gray-400">({{ $r[2] }})</span></td>
                        <td class="py-2 text-right">{{ number_format($r[3]) }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($r[4], 0, ',', '.') }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($r[5], 0, ',', '.') }}</td>
                        <td class="py-2 text-right font-semibold {{ $r[6] >= 0 ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($r[6], 0, ',', '.') }}</td>
                        <td class="py-2 text-right">{{ $r[7] }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-400">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold mb-4">Stok Lambat / Mati (tidak laku &gt;= 30 hari)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left border-b">
                    <th class="pb-2">SKU</th><th class="pb-2">Produk</th><th class="pb-2 text-right">Stok</th>
                    <th class="pb-2 text-right">Nilai</th><th class="pb-2 text-right">Laku 30hr</th>
                    <th class="pb-2">Terakhir</th><th class="pb-2">Status</th>
                </tr></thead>
                <tbody>
                    @forelse($this->slowRows as $r)
                    <tr class="border-b border-gray-50">
                        <td class="py-2 font-mono text-xs">{{ $r[0] }}</td>
                        <td class="py-2">{{ $r[1] }}</td>
                        <td class="py-2 text-right">{{ $r[2] }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($r[3], 0, ',', '.') }}</td>
                        <td class="py-2 text-right">{{ $r[4] }}</td>
                        <td class="py-2 text-xs">{{ $r[5] }} ({{ $r[6] }} hr)</td>
                        <td class="py-2"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ str_contains($r[7], 'Mati') ? 'bg-red-100 text-red-700' : (str_contains($r[7], 'Lambat') ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-700') }}">{{ $r[7] }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-400">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
