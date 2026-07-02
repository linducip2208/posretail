<div>
    <div class="flex flex-wrap gap-3 items-end mb-6">
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
            <a href="{{ route('export.stock', ['outlet_id' => $this->outletId, 'format' => 'csv']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('export.stock', ['outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Nilai Stok</div>
            <div class="text-2xl font-extrabold text-gray-900">Rp {{ number_format($this->totalStockValue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Produk</div>
            <div class="text-2xl font-extrabold text-gray-900">{{ number_format($this->totalProducts, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Stok Menipis</div>
            <div class="text-2xl font-extrabold {{ $this->lowStockCount > 0 ? 'text-rose-600' : 'text-gray-900' }}">{{ number_format($this->lowStockCount, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Kategori</div>
            <div class="text-2xl font-extrabold text-gray-900">{{ count($this->categoryLabels) }}</div>
        </div>
    </div>

    @if($this->lowStockCount > 0)
    <div class="bg-white rounded-xl shadow-sm border border-rose-200 p-5 mb-6">
        <h3 class="font-semibold text-rose-700 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            Peringatan — Produk Stok Menipis
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-rose-100">
                        <th class="pb-3 font-semibold text-rose-500 uppercase text-xs tracking-wider">Produk</th>
                        <th class="pb-3 font-semibold text-rose-500 uppercase text-xs tracking-wider text-right">SKU</th>
                        <th class="pb-3 font-semibold text-rose-500 uppercase text-xs tracking-wider text-right">Stok</th>
                        <th class="pb-3 font-semibold text-rose-500 uppercase text-xs tracking-wider text-right">Min Stok</th>
                        <th class="pb-3 font-semibold text-rose-500 uppercase text-xs tracking-wider text-right">Harga Beli</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->lowStockProducts as $product)
                    <tr class="border-b border-rose-50">
                        <td class="py-3 font-medium text-rose-800">{{ $product->name }}</td>
                        <td class="py-3 text-right font-mono text-xs text-rose-600">{{ $product->sku }}</td>
                        <td class="py-3 text-right font-bold text-rose-600">{{ number_format($product->current_stock) }}</td>
                        <td class="py-3 text-right text-rose-500">{{ number_format($product->min_stock) }}</td>
                        <td class="py-3 text-right text-rose-500">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Distribusi Nilai Stok per Kategori</h3>
            <canvas id="categoryDoughnutChart" height="80"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Top 10 Produk Stok Terbanyak</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">#</th>
                            <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Produk</th>
                            <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Stok</th>
                            <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->topStockedProducts as $i => $product)
                        <tr class="border-b border-gray-50">
                            <td class="py-3 text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-3 font-medium">{{ $product->name }}</td>
                            <td class="py-3 text-right">{{ number_format($product->current_stock) }}</td>
                            <td class="py-3 text-right font-medium">Rp {{ number_format($product->current_stock * $product->cost_price, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-400">Belum ada produk tersedia</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Riwayat Pergerakan Stok</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Tanggal</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Produk</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Outlet</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-center">Tipe</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider text-right">Qty</th>
                        <th class="pb-3 font-semibold text-gray-500 uppercase text-xs tracking-wider">Referensi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->stockMovements as $movement)
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-500">{{ $movement->created_at->format('d M Y H:i') }}</td>
                        <td class="py-3 font-medium">{{ $movement->product?->name ?? '-' }}</td>
                        <td class="py-3 text-gray-500">{{ $movement->outlet?->name ?? '-' }}</td>
                        <td class="py-3 text-center">
                            @php
                                $typeColors = ['in' => 'emerald', 'out' => 'rose', 'adjustment' => 'amber'];
                                $typeLabels = ['in' => 'Masuk', 'out' => 'Keluar', 'adjustment' => 'Adjust'];
                                $color = $typeColors[$movement->type] ?? 'gray';
                                $label = $typeLabels[$movement->type] ?? $movement->type;
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-700">{{ $label }}</span>
                        </td>
                        <td class="py-3 text-right font-medium {{ $movement->type === 'out' ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $movement->type === 'out' ? '-' : '+' }}{{ number_format($movement->quantity) }}
                        </td>
                        <td class="py-3 text-gray-500">{{ $movement->reference_type ? ucfirst($movement->reference_type) . ' #' . $movement->reference_id : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400">Belum ada pergerakan stok</td>
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
    const doughnutEl = document.getElementById('categoryDoughnutChart');
    if (!doughnutEl) return;
    if (doughnutEl._chart) doughnutEl._chart.destroy();

    const categoryLabels = {!! json_encode($this->categoryLabels) !!};
    const categoryData = {!! json_encode($this->categoryData) !!};

    const colors = [
        'rgba(79, 70, 229, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(168, 85, 247, 0.8)',
        'rgba(236, 72, 153, 0.8)',
        'rgba(20, 184, 166, 0.8)',
        'rgba(249, 115, 22, 0.8)',
        'rgba(99, 102, 241, 0.8)',
        'rgba(34, 197, 94, 0.8)',
    ];

    doughnutEl._chart = new Chart(doughnutEl.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
                backgroundColor: colors.slice(0, categoryLabels.length),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 16, font: { size: 11 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' Rp ' + ctx.raw.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
})();
</script>
