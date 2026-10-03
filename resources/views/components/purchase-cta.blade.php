{{-- Floating WhatsApp + delayed purchase popup — dynamic from admin settings --}}
@php
    $brandName = \App\Models\SystemSetting::getAppName();
    $waNumber = \App\Models\SystemSetting::getValue('whatsapp_number', '6281296052010');
    $posPrice = \App\Models\SystemSetting::getValue('pos_price', 'Rp 4.999.000');
    $posFeaturesRaw = \App\Models\SystemSetting::getValue('pos_features', "Full source code — Laravel + Filament + TailwindCSS\n30+ admin resources, 3 dashboard report pages\nPOS Kasir, Inventori, Pembelian, Loyalitas lengkap\nPayment gateway dinamis berbasis format API\nCustomer portal, API v1, PSEO directory built-in\nMulti-outlet + Blog + IndexNow SEO\n52 tabel DB, approval workflow\nLifetime update + 6 bulan support");
    $posFeatures = array_filter(array_map('trim', explode("\n", $posFeaturesRaw)));
    $waMessage = urlencode("Halo, saya tertarik beli source code {$brandName}");
    $waLink = "https://wa.me/{$waNumber}?text={$waMessage}";
@endphp

<div x-data="{
    showPopup: false,
    init() {
        const dismissed = sessionStorage.getItem('posretail_popup_dismissed');
        if (! dismissed) {
            setTimeout(() => this.showPopup = true, 25000);
        }
    },
    dismiss() {
        this.showPopup = false;
        sessionStorage.setItem('posretail_popup_dismissed', '1');
    }
}">

    {{-- Floating WhatsApp button --}}
    <a href="{{ $waLink }}" target="_blank" rel="noopener"
       class="fixed bottom-6 right-6 z-40 group flex items-center gap-2 px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full font-bold shadow-2xl shadow-emerald-500/40 hover:scale-105 transition">
        <x-ti name="brand-whatsapp" class="w-6 h-6" />
        <span class="hidden md:inline">WhatsApp</span>
        <span class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 rounded-full ring-2 ring-white animate-pulse"></span>
    </a>

    {{-- Backdrop --}}
    <div x-show="showPopup" x-cloak x-transition.opacity
         @click="dismiss"
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm"></div>

    {{-- Modal --}}
    <div x-show="showPopup" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">

        <div class="relative pointer-events-auto w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">

            <button @click="dismiss" class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition">&times;</button>

            {{-- Hero --}}
            <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-slate-900 text-white p-8 overflow-hidden">
                <div class="absolute top-0 right-0 text-[10rem] opacity-10 leading-none">💻</div>
                <div class="relative">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-semibold mb-4">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                        Limited Promo &middot; 2026
                    </div>
                    <h2 class="text-3xl font-extrabold leading-tight mb-2">Butuh {{ $brandName }}?</h2>
                    <p class="text-blue-200 text-sm">Beli source code lengkap. 1&times; bayar, lifetime + 6 bulan support.</p>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6 space-y-4">
                <ul class="space-y-2.5 text-sm text-slate-700">
                    @foreach($posFeatures as $feature)
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span><span>{{ $feature }}</span></li>
                    @endforeach
                </ul>

                <div class="bg-slate-100 rounded-2xl p-4">
                    <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Hubungi langsung</div>
                    <div class="font-mono font-bold text-slate-900">+62 {{ substr($waNumber, 2) }}</div>
                    <div class="text-xs text-slate-500 mt-1">Respon cepat &middot; Demo lengkap &middot; Pricing fleksibel</div>
                </div>

                <a href="{{ $waLink }}" target="_blank" rel="noopener"
                   class="block w-full py-4 bg-gradient-to-br from-emerald-500 to-emerald-700 text-white text-center rounded-2xl font-bold shadow-xl shadow-emerald-500/30 hover:shadow-2xl active:scale-[0.98] transition">
                    Chat WhatsApp Sekarang — {{ $posPrice }}
                </a>

                <a href="/docs" class="block w-full py-3 text-center text-sm text-slate-600 hover:text-blue-600 font-semibold transition">
                    Baca Dokumentasi Dulu &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
