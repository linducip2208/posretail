<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi — {{ config('app.name') }}</title>
    <meta name="description" content="Kebijakan privasi {{ config('app.name') }}: data yang kami kumpulkan, cara penggunaan, keamanan, dan hak Anda sesuai UU Pelindungan Data Pribadi.">
    <link rel="canonical" href="{{ config('app.url') }}/privacy">
    <meta property="og:title" content="Kebijakan Privasi — {{ config('app.name') }}">
    <meta property="og:description" content="{{ config('app.name') }} tunduk pada UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url') }}/privacy">
    <meta property="og:image" content="{{ config('app.url') }}/brand/logo-posretail.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Kebijakan Privasi — {{ config('app.name') }}">
    <meta name="twitter:description" content="{{ config('app.name') }} tunduk pada UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi.">
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
            <h1 class="text-4xl font-extrabold mb-4">Kebijakan Privasi</h1>
            <p class="text-blue-200 text-lg">Terakhir diperbarui: {{ date('d F Y') }}. Kami tunduk pada UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi.</p>
        </div>
    </section>

    <div class="max-w-3xl mx-auto px-4 py-16">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-10 space-y-8 leading-relaxed text-slate-600">
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">1. Data yang kami kumpulkan</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong>Data akun:</strong> nama, email, nomor telepon, dan peran/outlet Anda saat menggunakan aplikasi.</li>
                    <li><strong>Data transaksi bisnis Anda:</strong> produk, penjualan, pembelian, stok, pelanggan, dan pemasok yang Anda kelola di dalam aplikasi. Untuk instalasi self-hosted, data ini tersimpan 100% di server Anda sendiri dan tidak kami akses.</li>
                    <li><strong>Data kontak penjualan:</strong> nama, email/nomor WhatsApp yang Anda berikan saat bertanya tentang pembelian source code atau meminta demo.</li>
                    <li><strong>Data teknis:</strong> log akses dasar (alamat IP, jenis browser, halaman dikunjungi) untuk keamanan dan analitik situs.</li>
                </ul>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">2. Cara kami menggunakan data</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Menyediakan, mengoperasikan, dan memelihara layanan/aplikasi.</li>
                    <li>Merespons pertanyaan penjualan, demo, dan dukungan teknis.</li>
                    <li>Meningkatkan keamanan, mencegah penyalahgunaan, dan memenuhi kewajiban hukum.</li>
                    <li>Kami <strong>tidak menjual</strong> data pribadi Anda kepada pihak ketiga.</li>
                </ul>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">3. Penyimpanan &amp; keamanan</h2>
                <p>Data situs dilindungi dengan enkripsi koneksi (HTTPS), kontrol akses berbasis peran, dan audit trail. Backup database dapat dijadwalkan otomatis. Untuk instalasi self-hosted di server Anda, tanggung jawab keamanan infrastruktur ada pada Anda; kami menyediakan panduan pengerasan (hardening) pada dokumentasi.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">4. Berbagi data</h2>
                <p>Kami hanya membagikan data kepada pihak ketiga sejauh diperlukan untuk menjalankan layanan (misalnya penyedia hosting, payment gateway yang Anda konfigurasi sendiri) atau ketika diwajibkan oleh peraturan perundang-undangan yang berlaku.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">5. Cookie</h2>
                <p>Situs ini menggunakan cookie esensial untuk login dan preferensi sesi. Kami tidak menggunakan cookie pelacakan iklan pihak ketiga. Anda dapat menonaktifkan cookie melalui pengaturan browser, namun sebagian fungsi login mungkin terganggu.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">6. Hak Anda</h2>
                <p>Sesuai UU Pelindungan Data Pribadi, Anda berhak meminta akses, koreksi, pembatasan, penghapusan, dan penarikan persetujuan atas data pribadi Anda. Ajukan permintaan melalui halaman <a href="{{ route('contact') }}" class="text-blue-600 hover:underline font-medium">Kontak</a> dan kami akan menindaklanjuti maksimal 14 hari kerja.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">7. Retensi data</h2>
                <p>Data kontak penjualan disimpan selama diperlukan untuk tujuan pengumpulan atau sesuai kewajiban hukum, lalu dihapus atau dianonimkan. Data di instalasi self-hosted Anda sepenuhnya berada di bawah kendali Anda.</p>
            </section>
            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">8. Perubahan kebijakan</h2>
                <p>Kami dapat memperbarui halaman ini sewaktu-waktu. Tanggal pembaruan selalu tercantum di bagian atas. Penggunaan berkelanjutan atas layanan setelah perubahan berarti Anda menyetujuinya.</p>
            </section>
        </div>
    </div>

    <footer class="bg-slate-900 text-slate-400 py-10 text-sm">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="mb-3">
                <a href="{{ route('privacy') }}" class="text-white font-medium">Kebijakan Privasi</a>
                <span class="mx-2">&middot;</span>
                <a href="{{ route('terms') }}" class="hover:text-white transition">Syarat &amp; Ketentuan</a>
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
