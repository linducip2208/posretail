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
            <a href="{{ route('export.customers', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'xlsx']) }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#206bc4] rounded-lg">Excel Pelanggan</a>
            <a href="{{ route('export.promo', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId, 'format' => 'xlsx']) }}" class="px-4 py-2 text-sm font-semibold text-white bg-orange-600 rounded-lg">Excel Diskon</a>
        </div>
    </div>

    <div class="bg-white rounded-xl border p-5 mb-6">
        <h3 class="font-semibold mb-4">Efektivitas Diskon (apakah diskon menaikkan omzet?)</h3>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="text-left border-b"><th class="pb-2">Bucket</th><th class="pb-2 text-right">Trx</th><th class="pb-2 text-right">Omzet Kotor</th><th class="pb-2 text-right">Diskon</th><th class="pb-2 text-right">Bersih</th><th class="pb-2 text-right">Rata2/Trx</th></tr></thead>
            <tbody>@forelse($this->promoRows as $r)<tr class="border-b border-gray-50"><td class="py-2 font-medium">{{ $r[0] }}</td><td class="py-2 text-right">{{ number_format($r[1]) }}</td><td class="py-2 text-right">{{ number_format($r[2], 0, ',', '.') }}</td><td class="py-2 text-right text-orange-600">{{ number_format($r[3], 0, ',', '.') }} ({{ $r[5] }}%)</td><td class="py-2 text-right font-semibold">{{ number_format($r[4], 0, ',', '.') }}</td><td class="py-2 text-right">{{ number_format($r[6], 0, ',', '.') }}</td></tr>@empty<tr><td colspan="6" class="py-6 text-center text-gray-400">Belum ada data</td></tr>@endforelse</tbody>
        </table></div>
        <p class="text-xs text-gray-500 mt-3">Cara baca: kalau "Diskon Besar" rata2/trx-nya malah lebih kecil dari "Tanpa Diskon", berarti promonya bakar margin tanpa menaikkan basket size.</p>
    </div>

    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold mb-4">Segmentasi Pelanggan (RFM)</h3>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="text-left border-b"><th class="pb-2">Pelanggan</th><th class="pb-2">Terakhir</th><th class="pb-2 text-right">Freq</th><th class="pb-2 text-right">Monetary</th><th class="pb-2">Segmen</th></tr></thead>
            <tbody>@forelse($this->rfmRows as $r)<tr class="border-b border-gray-50"><td class="py-2">{{ $r[0] }} <span class="text-xs text-gray-400">{{ $r[1] }}</span></td><td class="py-2 text-xs">{{ $r[2] }} ({{ $r[3] }} hr lalu)</td><td class="py-2 text-right">{{ $r[4] }}</td><td class="py-2 text-right">Rp {{ number_format($r[5], 0, ',', '.') }}</td><td class="py-2"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $r[6] === 'Champions' ? 'bg-green-100 text-green-700' : ($r[6] === 'Loyal' ? 'bg-blue-100 text-blue-700' : ($r[6] === 'Aktif' ? 'bg-gray-100 text-gray-700' : 'bg-red-100 text-red-700')) }}">{{ $r[6] }}</span></td></tr>@empty<tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada data pelanggan</td></tr>@endforelse</tbody>
        </table></div>
    </div>
</div>
