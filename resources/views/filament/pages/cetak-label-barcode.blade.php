<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Cetak Label Barcode</h2>
        <p class="text-sm text-gray-500 mt-1">Cari produk berdasarkan nama, barcode, atau SKU. Pilih produk yang ingin dicetak labelnya.</p>
    </div>

    {{-- Search --}}
    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama produk, barcode, atau SKU..."
            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow"
            autofocus
        >
        @if($search)
            <button
                wire:click="$set('search', '')"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Search Results --}}
        <div class="lg:col-span-2">
            @if($search && strlen($search) >= 2)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <span class="text-sm font-semibold text-gray-700">Hasil Pencarian: {{ $this->products->count() }} produk</span>
                    </div>

                    @forelse($this->products as $product)
                        <div class="border-b border-gray-50 last:border-b-0">
                            {{-- Product row --}}
                            @php
                                $productKey = 'product:' . $product->id;
                                $isSelected = in_array($productKey, $selectedProducts);
                            @endphp
                            <div
                                wire:click="toggleProduct('{{ $productKey }}')"
                                class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-blue-50/50 transition-colors {{ $isSelected ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
                            >
                                <div class="shrink-0">
                                    @if($isSelected)
                                        <div class="w-5 h-5 rounded bg-blue-500 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @else
                                        <div class="w-5 h-5 rounded border-2 border-gray-300"></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm text-gray-900 truncate">{{ $product->name }}</div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                        @if($product->barcode)
                                            <span class="font-mono">{{ $product->barcode }}</span>
                                        @else
                                            <span class="text-rose-500">Belum ada barcode</span>
                                        @endif
                                        @if($product->sku)
                                            <span>| SKU: {{ $product->sku }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-gray-700 shrink-0">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </div>
                            </div>

                            {{-- Variants --}}
                            @if($product->has_variants && $product->variants->isNotEmpty())
                                @foreach($product->variants as $variant)
                                    @php
                                        $variantKey = 'variant:' . $variant->id;
                                        $isVariantSelected = in_array($variantKey, $selectedProducts);
                                    @endphp
                                    <div
                                        wire:click="toggleProduct('{{ $variantKey }}')"
                                        class="flex items-center gap-3 pl-10 pr-4 py-2 cursor-pointer hover:bg-blue-50/50 transition-colors {{ $isVariantSelected ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
                                    >
                                        <div class="shrink-0">
                                            @if($isVariantSelected)
                                                <div class="w-5 h-5 rounded bg-blue-500 flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                            @else
                                                <div class="w-5 h-5 rounded border-2 border-gray-300"></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm text-gray-700 truncate">{{ $variant->name }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                @if($variant->barcode)
                                                    <span class="font-mono">{{ $variant->barcode }}</span>
                                                @else
                                                    <span class="text-rose-500">Belum ada barcode</span>
                                                @endif
                                                @if($variant->sku)
                                                    <span>| SKU: {{ $variant->sku }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-700 shrink-0">
                                            Rp {{ number_format($variant->selling_price, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <p class="text-sm">Produk tidak ditemukan</p>
                        </div>
                    @endforelse
                </div>
            @elseif(!$search)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <p class="text-sm">Ketik nama produk, barcode, atau SKU untuk mencari</p>
                </div>
            @endif
        </div>

        {{-- Right: Selected Products --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 sticky top-4">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">
                        Produk Dipilih ({{ count($selectedProducts) }})
                    </span>
                    @if(count($selectedProducts) > 0)
                        <button
                            wire:click="$set('selectedProducts', []); $set('quantities', [])"
                            class="text-xs text-rose-600 hover:text-rose-800 font-medium"
                        >
                            Hapus Semua
                        </button>
                    @endif
                </div>

                <div class="divide-y divide-gray-50 max-h-[400px] overflow-y-auto">
                    @forelse($selectedProducts as $key)
                        @php
                            $parts = explode(':', $key);
                            $type = $parts[0] ?? '';
                            $id = $parts[1] ?? '';
                            $name = '';
                            $barcode = '';
                            $price = 0;

                            if ($type === 'product') {
                                $p = \App\Models\Product::find($id);
                                if ($p) {
                                    $name = $p->name;
                                    $barcode = $p->barcode;
                                    $price = $p->selling_price;
                                }
                            } elseif ($type === 'variant') {
                                $v = \App\Models\ProductVariant::with('product')->find($id);
                                if ($v) {
                                    $name = $v->product->name.' — '.$v->name;
                                    $barcode = $v->barcode;
                                    $price = $v->selling_price;
                                }
                            }

                            $qty = $quantities[$key] ?? 1;
                        @endphp
                        <div class="px-4 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $name }}</div>
                                    <div class="text-xs font-mono text-gray-500 mt-0.5">{{ $barcode ?: 'Tanpa barcode' }}</div>
                                    <div class="text-xs text-gray-500">Rp {{ number_format($price, 0, ',', '.') }}</div>
                                </div>
                                <button
                                    wire:click="removeProduct('{{ $key }}')"
                                    class="shrink-0 text-gray-300 hover:text-rose-500 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <label class="text-xs text-gray-500">Jumlah label:</label>
                                <input
                                    type="number"
                                    wire:model.live="quantities.{{ $key }}"
                                    min="1"
                                    max="100"
                                    class="w-16 border border-gray-300 rounded px-2 py-1 text-xs text-center focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                >
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm">Pilih produk dari hasil pencarian</p>
                        </div>
                    @endforelse
                </div>

                @if(count($selectedProducts) > 0)
                    <div class="px-4 py-3 border-t border-gray-100">
                        <button
                            onclick="printBarcodeLabels()"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Cetak Label ({{ count($selectedProducts) }} produk)
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(count($selectedProducts) > 0)
    @script
    <script>
        window.printBarcodeLabels = function() {
            const items = @json($this->selectedItems);
            const width = document.getElementById('labelWidth')?.value || '50';
            const height = document.getElementById('labelHeight')?.value || '30';

            if (!items.length) {
                alert('Tidak ada produk dengan barcode yang dipilih.');
                return;
            }

            const printWindow = window.open('', '_blank', 'width=900,height=700');
            if (!printWindow) {
                alert('Pop-up diblokir. Izinkan pop-up untuk mencetak label.');
                return;
            }

            let html = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Barcode</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4; margin: 8mm; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 10px;
        }
        .labels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(${width}mm, 1fr));
            gap: 3mm;
        }
        .label {
            border: 1px dashed #ccc;
            border-radius: 4px;
            padding: 2mm;
            text-align: center;
            height: ${height}mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            page-break-inside: avoid;
        }
        .label img {
            max-width: 100%;
            height: auto;
            margin-bottom: 1mm;
        }
        .label .name {
            font-weight: 700;
            font-size: 8px;
            line-height: 1.2;
            color: #111;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }
        .label .price {
            font-weight: 800;
            font-size: 9px;
            color: #1d4ed8;
            margin-top: 0.5mm;
        }
        .label .sku {
            font-size: 6px;
            color: #888;
            margin-top: 0.5mm;
        }
        @@media print {
            .label { border: 1px solid #ddd; }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(function(){ window.close(); }, 500);">
    <div class="labels-grid">`;

            items.forEach(item => {
                html += `
        <div class="label">
            <img src="/barcode/${encodeURIComponent(item.barcode)}?width=1.5&height=30" alt="${item.barcode}" style="width:100%">
            <div class="price">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</div>
            <div class="name">${item.name}</div>
            <div class="sku">${item.barcode}</div>
        </div>`;
            });

            html += `
    </div>
</body>
</html>`;

            printWindow.document.write(html);
            printWindow.document.close();
        };
    </script>
    @endscript
@endif
