<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — POS Retail</title>
    @vite('resources/css/portal.css')
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/" class="inline-block text-2xl font-bold text-[#206bc4]">POS Retail</a>
            <p class="text-sm text-gray-500 mt-2">Buat akun pelanggan baru</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-[#d63939]/8 border border-red-200 rounded-xl text-sm text-[#b22b2b]">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('portal.register') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    placeholder="Nama Anda"
                    required
                    autofocus
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-[#206bc4] focus:border-[#206bc4] outline-none transition"
                >
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-[#206bc4] focus:border-[#206bc4] outline-none transition"
                >
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Telepon</label>
                <input
                    type="text"
                    name="phone"
                    id="phone"
                    value="{{ old('phone') }}"
                    placeholder="081234567890"
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-[#206bc4] focus:border-[#206bc4] outline-none transition"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Minimal 6 karakter"
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-[#206bc4] focus:border-[#206bc4] outline-none transition"
                >
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Kata Sandi</label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="Ulangi kata sandi"
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-[#206bc4] focus:border-[#206bc4] outline-none transition"
                >
            </div>

            <button
                type="submit"
                class="w-full py-2.5 px-4 bg-[#206bc4] text-white text-sm font-semibold rounded-xl hover:bg-[#1a569d] active:scale-[0.98] transition shadow-sm"
            >
                Daftar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('portal.login') }}" class="text-[#206bc4] font-medium hover:text-[#1a569d]">Masuk di sini</a>
        </p>
    </div>

</body>
</html>
