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
            <a href="{{ route('export.cashflow', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'xlsx']) }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#206bc4] rounded-lg">Excel Arus Kas</a>
            <a href="{{ route('export.payables', ['outlet_id' => $this->outletId, 'format' => 'xlsx']) }}" class="px-4 py-2 text-sm font-semibold text-white bg-orange-600 rounded-lg">Excel Hutang</a>
            <a href="{{ route('export.tax', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'xlsx']) }}" class="px-4 py-2 text-sm font-semibold text-white bg-teal-700 rounded-lg">Excel Pajak</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-5"><div class="text-xs uppercase text-gray-500 font-semibold">Kas Masuk</div><div class="text-2xl font-extrabold text-green-600">Rp {{ number_format($this->totalMasuk, 0, ',', '.') }}</div></div>
        <div class="bg-white rounded-xl border p-5"><div class="text-xs uppercase text-gray-500 font-semibold">Kas Keluar</div><div class="text-2xl font-extrabold text-red-600">Rp {{ number_format($this->totalKeluar, 0, ',', '.') }}</div></div>
        <div class="bg-white rounded-xl border p-5"><div class="text-xs uppercase text-gray-500 font-semibold">Sisa Hutang Supplier</div><div class="text-2xl font-extrabold">Rp {{ number_format($this->totalHutang, 0, ',', '.') }}</div></div>
    </div>

    <div class="bg-white rounded-xl border p-5 mb-6">
        <h3 class="font-semibold mb-4">Arus Kas Harian</h3>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="text-left border-b"><th class="pb-2">Tanggal</th><th class="pb-2 text-right">Masuk</th><th class="pb-2 text-right">Keluar</th><th class="pb-2 text-right">Bersih</th><th class="pb-2 text-right">Trx</th></tr></thead>
            <tbody>@forelse($this->cashflowRows as $r)<tr class="border-b border-gray-50"><td class="py-2">{{ $r[0] }}</td><td class="py-2 text-right">Rp {{ number_format($r[1], 0, ',', '.') }}</td><td class="py-2 text-right">Rp {{ number_format($r[2], 0, ',', '.') }}</td><td class="py-2 text-right font-semibold {{ $r[3] >= 0 ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($r[3], 0, ',', '.') }}</td><td class="py-2 text-right">{{ $r[4] }}</td></tr>@empty<tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada data</td></tr>@endforelse</tbody>
        </table></div>
    </div>

    <div class="bg-white rounded-xl border p-5 mb-6">
        <h3 class="font-semibold mb-4">Hutang Supplier</h3>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="text-left border-b"><th class="pb-2">Supplier</th><th class="pb-2">Invoice</th><th class="pb-2 text-right">Sisa</th><th class="pb-2">Jatuh Tempo</th><th class="pb-2">Status</th></tr></thead>
            <tbody>@forelse($this->payableRows as $r)<tr class="border-b border-gray-50"><td class="py-2">{{ $r[0] }}</td><td class="py-2 font-mono text-xs">{{ $r[1] }}</td><td class="py-2 text-right">Rp {{ number_format($r[4], 0, ',', '.') }}</td><td class="py-2 text-xs">{{ $r[5] }} @if($r[6] > 0)<span class="text-red-600">({{ $r[6] }} hr)</span>@endif</td><td class="py-2">{{ $r[7] }}</td></tr>@empty<tr><td colspan="5" class="py-6 text-center text-gray-400">Tidak ada hutang</td></tr>@endforelse</tbody>
        </table></div>
    </div>

    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold mb-4">Faktur Pajak Keluaran</h3>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="text-left border-b"><th class="pb-2">No. Faktur</th><th class="pb-2">Tanggal</th><th class="pb-2">Pelanggan</th><th class="pb-2 text-right">DPP</th><th class="pb-2 text-right">PPN</th><th class="pb-2 text-right">Total</th></tr></thead>
            <tbody>@forelse($this->taxRows as $r)<tr class="border-b border-gray-50"><td class="py-2 font-mono text-xs">{{ $r[0] }}</td><td class="py-2">{{ $r[1] }}</td><td class="py-2">{{ $r[2] }}</td><td class="py-2 text-right">{{ number_format($r[3], 0, ',', '.') }}</td><td class="py-2 text-right">{{ number_format($r[4], 0, ',', '.') }}</td><td class="py-2 text-right font-semibold">{{ number_format($r[5], 0, ',', '.') }}</td></tr>@empty<tr><td colspan="6" class="py-6 text-center text-gray-400">Belum ada faktur</td></tr>@endforelse</tbody>
        </table></div>
    </div>
</div>
