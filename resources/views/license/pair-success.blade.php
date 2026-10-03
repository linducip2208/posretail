<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="4; url=/">
<title>Aktivasi Berhasil</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial }
  @keyframes check-pop { 0% { transform: scale(0); opacity: 0 } 50% { transform: scale(1.2) } 100% { transform: scale(1); opacity: 1 } }
  .check-pop { animation: check-pop 0.6s cubic-bezier(.34,1.56,.64,1) }
  @keyframes fade-up { from { opacity: 0; transform: translateY(10px) } to { opacity: 1; transform: translateY(0) } }
  .fade-up { animation: fade-up 0.5s ease-out 0.3s both }
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#2fb344]/8 via-white to-[#0ca678]/10 flex items-center justify-center px-4 py-10">

<div class="w-full max-w-xl">
  <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

    <div class="bg-gradient-to-br from-[#2fb344] to-teal-600 px-7 py-10 text-center text-white">
      <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur rounded-full mb-4 check-pop">
        <x-ti name="check" class="w-10 h-10 text-white" />
      </div>
      <h1 class="text-2xl font-bold">Aktivasi Berhasil</h1>
      <p class="text-[#2fb344]/15 mt-1 text-sm">Aplikasi siap digunakan.</p>
    </div>

    <div class="p-7 space-y-5 fade-up">

      <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Produk</div>
        <div class="flex items-baseline gap-3">
          <span><x-ti name="package" class="w-6 h-6" /></span>
          <div>
            <div class="text-lg font-bold text-slate-900">{{ $data['product']['name'] ?? 'Aplikasi' }}</div>
            <div class="text-sm text-slate-500">Versi v{{ $data['product']['version'] ?? '1.0.0' }}</div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
            <x-ti name="world" class="w-3.5 h-3.5" />
            Domain Terkunci
          </div>
          <div class="font-mono text-sm text-slate-800 break-all">{{ $data['domain'] ?? '-' }}</div>
        </div>

        @if(!empty($data['license']['support_until']))
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
            <x-ti name="calendar" class="w-3.5 h-3.5" />
            Support Aktif Sampai
          </div>
          <div class="font-medium text-sm text-slate-800">{{ \Illuminate\Support\Carbon::parse($data['license']['support_until'])->format('d M Y') }}</div>
        </div>
        @endif
      </div>

      <div class="bg-[#2fb344]/8 border border-emerald-200 rounded-lg p-4 flex items-start gap-3">
        <x-ti name="shield-check" class="w-5 h-5 text-[#2fb344] mt-0.5 flex-shrink-0" />
        <div class="text-sm text-[#1f7a2e] leading-relaxed">
          <strong>Konfirmasi:</strong> nama produk di atas harus cocok dengan yang kamu beli. Kalau salah, klik "Revoke" di marketplace dan re-pair dengan key yang benar.
        </div>
      </div>

      <a href="/" class="block w-full text-center py-3.5 px-6 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg transition">
        Masuk ke Aplikasi →
      </a>

      <p class="text-center text-xs text-slate-500">
        Auto-redirect dalam 4 detik...
      </p>
    </div>
  </div>
</div>

</body>
</html>
