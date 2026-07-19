<x-filament-panels::page>

    <div class="stock-take-mobile" x-data="stockTakeApp()" x-init="init">

        {{-- Header with outlet selector --}}
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-1">
                <select wire:model.change="outletId" class="w-full rounded-xl border-stone-300 text-sm py-3 px-4 bg-white shadow-sm">
                    @foreach($this->outlets as $outlet)
                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Search bar --}}
        <div class="relative mb-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama, SKU, atau scan barcode..."
                class="w-full pl-10 pr-4 py-4 rounded-xl border-stone-300 text-base bg-white shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                autofocus
            >
        </div>

        {{-- Product list --}}
        <div class="space-y-3">
            @forelse($this->products as $product)
                <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-4"
                     x-data="{
                        productId: {{ $product->id }},
                        systemQty: {{ $product->current_stock }},
                        actualQty: null,
                        difference: 0,
                        hasValue: false,

                        updateDiff() {
                            if (this.actualQty !== null && this.actualQty !== '') {
                                const val = parseInt(this.actualQty);
                                this.difference = val - this.systemQty;
                                this.hasValue = true;
                                $wire.set('selectedProducts.' + this.productId + '.actual_qty', val);
                                $wire.set('selectedProducts.' + this.productId + '.system_qty', this.systemQty);
                                $wire.set('selectedProducts.' + this.productId + '.difference', this.difference);
                            } else {
                                this.hasValue = false;
                                $wire.set('selectedProducts.' + this.productId, null);
                            }
                        }
                     }">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-stone-900 text-sm truncate">{{ $product->name }}</h3>
                            <div class="flex items-center gap-2 mt-1 text-xs text-stone-500">
                                <span class="bg-stone-100 px-2 py-0.5 rounded-full">{{ $product->sku }}</span>
                                @if($product->barcode)
                                    <span class="bg-stone-100 px-2 py-0.5 rounded-full">{{ $product->barcode }}</span>
                                @endif
                                @if($product->category)
                                    <span class="text-primary-600">{{ $product->category->name }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- System stock badge --}}
                        <div class="text-right ml-3 shrink-0">
                            <div class="text-xs text-stone-400">Sistem</div>
                            <div class="text-lg font-bold text-stone-600">{{ $product->current_stock }}</div>
                        </div>
                    </div>

                    {{-- Actual qty input --}}
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Qty Aktual</label>
                            <input
                                type="number"
                                x-model="actualQty"
                                x-on:input="updateDiff()"
                                class="w-full rounded-lg border-stone-300 text-lg py-3 px-4 bg-stone-50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Masukkan jumlah..."
                                inputmode="numeric"
                                min="0"
                            >
                        </div>

                        {{-- Difference indicator --}}
                        <div class="text-right shrink-0" x-show="hasValue" x-cloak>
                            <div class="text-xs font-medium mb-1" :class="difference === 0 ? 'text-stone-400' : 'text-stone-500'">Selisih</div>
                            <div class="text-lg font-bold rounded-lg px-3 py-1"
                                 :class="{
                                    'bg-green-50 text-green-700': difference > 0,
                                    'bg-red-50 text-red-700': difference < 0,
                                    'bg-stone-50 text-stone-500': difference === 0
                                 }">
                                <span x-show="difference > 0">+</span>
                                <span x-text="difference"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="text-4xl mb-3">📋</div>
                    <p class="text-stone-500">Tidak ada produk ditemukan</p>
                    <p class="text-stone-400 text-sm">Coba kata kunci lain atau pilih outlet berbeda</p>
                </div>
            @endforelse
        </div>

        {{-- Complete button --}}
        @if(count($this->selectedProducts) > 0)
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-stone-200 shadow-lg" style="z-index:50">
            <div class="flex items-center justify-between max-w-lg mx-auto">
                <div>
                    <span class="text-sm text-stone-500">{{ count($this->selectedProducts) }} produk dihitung</span>
                </div>
                <button
                    wire:click="completeStockTake"
                    wire:loading.attr="disabled"
                    class="bg-primary-600 text-white font-semibold px-8 py-3 rounded-xl shadow-md hover:bg-primary-700 transition disabled:opacity-50 text-base"
                >
                    <span wire:loading.remove>Selesai Stock Take</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </div>
        @endif

    </div>

    @push('scripts')
    <script>
        function stockTakeApp() {
            return {
                init() {
                    document.addEventListener('keydown', (e) => {
                        if (e.ctrlKey && e.key === 'k') {
                            e.preventDefault();
                            document.querySelector('input[wire\\:model\\.live]')?.focus();
                        }
                    });
                }
            }
        }
    </script>
    @endpush

    <style>
        .stock-take-mobile {
            max-width: 600px;
            margin: 0 auto;
            padding-bottom: 100px;
        }

        .stock-take-mobile input[type="number"] {
            -moz-appearance: textfield;
        }

        .stock-take-mobile input[type="number"]::-webkit-outer-spin-button,
        .stock-take-mobile input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        [x-cloak] { display: none !important; }

        @media (min-width: 640px) {
            .stock-take-mobile {
                padding-bottom: 120px;
            }
        }
    </style>

</x-filament-panels::page>
