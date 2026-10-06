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
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#2fb344] hover:bg-[#268f36] rounded-lg transition-colors shadow-sm">
                <x-ti name="download" class="w-4 h-4" />
                CSV
            </a>
            <a href="{{ route('export.stock', ['outlet_id' => $this->outletId, 'format' => 'pdf']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#d63939] hover:bg-[#b22b2b] rounded-lg transition-colors shadow-sm">
                <x-ti name="file-description" class="w-4 h-4" />
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
            <div class="text-2xl font-extrabold {{ $this->lowStockCount > 0 ? 'text-[#d63939]' : 'text-gray-900' }}">{{ number_format($this->lowStockCount, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Kategori</div>
            <div class="text-2xl font-extrabold text-gray-900">{{ count($this->categoryLabels) }}</div>
        </div>
    </div>

    @if($this->lowStockCount > 0)
    <div class="bg-white rounded-xl shadow-sm border border-rose-200 p-5 mb-6">
        <h3 class="font-semibold text-[#b22b2b] mb-4 flex items-center gap-2">
            <x-ti name="alert-triangle" class="w-5 h-5" />
            Peringatan — Produk Stok Menipis
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-[#d63939]/10">
                        <th class="pb-3 font-semibold text-[#d63939] uppercase text-xs tracking-wider">Produk</th>
                        <th class="pb-3 font-semibold text-[#d63939] uppercase text-xs tracking-wider text-right">SKU</th>
                        <th class="pb-3 font-semibold text-[#d63939] uppercase text-xs tracking-wider text-right">Stok</th>
                        <th class="pb-3 font-semibold text-[#d63939] uppercase text-xs tracking-wider text-right">Min Stok</th>
                        <th class="pb-3 font-semibold text-[#d63939] uppercase text-xs tracking-wider text-right">Harga Beli</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->lowStockProducts as $product)
                    <tr class="border-b border-[#d63939]/8">
                        <td class="py-3 font-medium text-[#8f1d1d]">{{ $product->name }}</td>
                        <td class="py-3 text-right font-mono text-xs text-[#d63939]">{{ $product->sku }}</td>
                        <td class="py-3 text-right font-bold text-[#d63939]">{{ number_format($product->current_stock) }}</td>
                        <td class="py-3 text-right text-[#d63939]">{{ number_format($product->min_stock) }}</td>
                        <td class="py-3 text-right text-[#d63939]">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
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
                        <td class="py-3 text-right font-medium {{ $movement->type === 'out' ? 'text-[#d63939]' : 'text-[#2fb344]' }}">
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

<script src="{{ asset('vendor/chart.js/chart.umd.min.js') }}"></script>
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

