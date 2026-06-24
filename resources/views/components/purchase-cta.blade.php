{{-- Floating WhatsApp + delayed purchase popup — dynamic from admin settings --}}
@php
    $brandName = \App\Models\SystemSetting::getAppName();
    $waNumber = \App\Models\SystemSetting::getValue('whatsapp_number', '6281296052010');
    $posPrice = \App\Models\SystemSetting::getValue('pos_price', 'Rp 4.999.000');
    $posFeaturesRaw = \App\Models\SystemSetting::getValue('pos_features', "Full source code — Laravel + Filament + TailwindCSS\n30+ admin resources, 3 dashboard report pages\nPOS Kasir, Inventori, Pembelian, Loyalitas lengkap\nPayment gateway dinamis (Midtrans, Xendit, dll)\nCustomer portal, API v1, PSEO directory built-in\nMulti-outlet + Blog + IndexNow SEO\n52 tabel DB, approval workflow\nLifetime update + 6 bulan support");
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
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
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
