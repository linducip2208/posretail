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
        <div class="ml-auto">
            <a href="{{ route('export.cancelled', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'outlet_id' => $this->outletId]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Order Dibatalkan</div>
            <div class="text-2xl font-extrabold text-gray-900">{{ number_format($this->totalCount, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Nilai Dibatalkan</div>
            <div class="text-2xl font-extrabold text-rose-600">Rp {{ number_format($this->totalCancelled, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Catatan</div>
            <div class="text-sm text-gray-600 mt-2">Order yang dibatalkan tetap tersimpan di database dan tidak dihitung di laporan keuangan.</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800">Daftar Order Dibatalkan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No. Order</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Outlet</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kasir</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pelanggan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status Bayar</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($this->cancelledOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ $order->order_number }}</td>
                            <td class="px-4 py-3">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $order->outlet?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $order->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $order->customer?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $order->payment_status === 'refunded' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $order->payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $order->notes ?? $order->order_notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-400">Tidak ada order yang dibatalkan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
