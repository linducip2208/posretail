<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat &amp; Ketentuan — {{ config('app.name') }}</title>
    <meta name="description" content="Syarat dan ketentuan penggunaan {{ config('app.name') }}: lisensi source code, pembayaran, dukungan, dan batasan tanggung jawab.">
    <link rel="canonical" href="{{ config('app.url') }}/terms">
    <meta property="og:title" content="Syarat &amp; Ketentuan — {{ config('app.name') }}">
    <meta property="og:description" content="Syarat dan ketentuan penggunaan {{ config('app.name') }}: lisensi source code, pembayaran, dukungan, dan batasan tanggung jawab.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url') }}/terms">
    <meta property="og:image" content="{{ config('app.url') }}/brand/logo-posretail.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Syarat &amp; Ketentuan — {{ config('app.name') }}">
    <meta name="twitter:description" content="Syarat dan ketentuan penggunaan {{ config('app.name') }}: lisensi source code, pembayaran, dukungan, dan batasan tanggung jawab.">
    <meta name="twitter:image" content="{{ config('app.url') }}/brand/logo-posretail.png">
    @vite('resources/css/portal.css')
</head>
<body class="font-sans bg-stone-50 text-slate-800 antialiased">
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-xl text-slate-900"><span><x-ti name="building-store" class="w-6 h-6" /></span> {{ config('app.name') }}</a>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="text-sm text-slate-600 hover:text-blue-600 transition">Beranda</a>
                <a href="{{ route('blog.index') }}" class="text-sm text-slate-600 hover:text-blue-600 transition">Blog</a>
                <a href="/docs" class="text-sm text-slate-600 hover:text-blue-600 transition">Dokumentasi</a>
                <a href="{{ route('login') }}" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Masuk</a>
            </div>
        </div>
    </nav>

    <section class="bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 text-white py-16">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h1 class="text-4xl font-extrabold mb-4">Syarat &amp; Ketentuan</h1>
            <p class="text-blue-200 text-lg">Terakhir diperbarui: {{ date('d F Y') }}. Berlaku untuk penggunaan situs dan pembelian source code.</p>
        </div>
    </section>

    <div class="max-w-3xl mx-auto px-4 py-16">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-10 space-y-8 leading-relaxed text-slate-600">
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">1. Definisi</h2>
                <p>"Aplikasi" berarti source code dan sistem POS Retail beserta dokumentasinya. "Pembeli" berarti pihak yang membeli lisensi source code. "Kami" berarti penyedia/penjual resmi POS Retail.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">2. Lisensi source code</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Pembelian memberikan lisensi <strong>sekali bayar (lifetime)</strong> untuk digunakan dan dimodifikasi sesuai kebutuhan bisnis Pembeli, termasuk instalasi di server sendiri (self-hosted).</li>
                    <li>Lisensi bersifat non-eksklusif dan tidak dapat dialihkan tanpa persetujuan tertulis dari Kami.</li>
                    <li>Dilarang menjual kembali, mendistribusikan ulang, mempublikasikan, atau membagikan source code (baik gratis maupun berbayar) kepada pihak ketiga dalam bentuk apa pun.</li>
                    <li>Atribusi/hak cipta yang melekat pada kode tidak boleh dihapus.</li>
                </ul>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">3. Harga &amp; pembayaran</h2>
                <p>Harga tercantum pada halaman harga resmi dan dapat berubah sewaktu-waktu tanpa mengurangi hak Pembeli yang sudah membayar. Pembayaran dilakukan melalui kanal yang disepakati; lisensi/file diserahkan setelah pembayaran terkonfirmasi.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">4. Pengembalian dana</h2>
                <p>Karena sifat produk berupa barang digital yang dapat disalin, <strong>pembayaran yang sudah dilakukan tidak dapat dikembalikan</strong> kecuali disepakati lain secara tertulis. Pastikan Anda telah melihat demo dan memahami fitur sebelum membeli.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">5. Dukungan &amp; pembaruan</h2>
                <p>Pembeli berhak atas panduan instalasi melalui dokumentasi di halaman <a href="/docs" class="text-blue-600 hover:underline font-medium">/docs</a>. Bantuan instalasi dan kustomisasi lanjutan dapat dikenakan biaya jasa terpisah sesuai kesepakatan. Pembaruan (update) diberikan sesuai kebijakan rilis Kami.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">6. Penggunaan yang dilarang</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Melanggar hukum, meretas, atau mengganggu keamanan sistem pihak mana pun.</li>
                    <li>Menggunakan Aplikasi untuk aktivitas penipuan, pencucian uang, atau konten ilegal.</li>
                    <li>Mengklaim Aplikasi sebagai karya sendiri atau menghapus lisensi/hak cipta.</li>
                </ul>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">7. Tanpa jaminan &amp; batas tanggung jawab</h2>
                <p>Aplikasi disediakan <strong>"sebagaimana adanya"</strong>. Sejauh diizinkan hukum, Kami tidak bertanggung jawab atas kerugian tidak langsung, kehilangan keuntungan, atau kehilangan data akibat penggunaan/modifikasi Aplikasi oleh Pembeli. Tanggung jawab total Kami terbatas pada nilai yang Pembeli bayarkan.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">8. Privasi</h2>
                <p>Pengumpulan dan penggunaan data pribadi tunduk pada <a href="{{ route('privacy') }}" class="text-blue-600 hover:underline font-medium">Kebijakan Privasi</a> kami.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">9. Perubahan &amp; hukum yang berlaku</h2>
                <p>Kami dapat memperbarui halaman ini; tanggal pembaruan tercantum di atas. Syarat ini diatur oleh hukum Republik Indonesia dan sengketa diselesaikan secara musyawarah terlebih dahulu. Pertanyaan: hubungi kami via halaman <a href="{{ route('contact') }}" class="text-blue-600 hover:underline font-medium">Kontak</a>.</p>
            </section>
        </div>
    </div>

    <footer class="bg-slate-900 text-slate-400 py-10 text-sm">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="mb-3">
                <a href="{{ route('privacy') }}" class="hover:text-white transition">Kebijakan Privasi</a>
                <span class="mx-2">&middot;</span>
                <a href="{{ route('terms') }}" class="text-white font-medium">Syarat &amp; Ketentuan</a>
                <span class="mx-2">&middot;</span>
                <a href="{{ route('contact') }}" class="hover:text-white transition">Kontak</a>
                <span class="mx-2">&middot;</span>
                <a href="{{ route('faq') }}" class="hover:text-white transition">FAQ</a>
            </p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} &middot; Powered by Laravel</p>
        </div>
    </footer>
</body>
</html>
