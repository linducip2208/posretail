<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aktivasi Aplikasi</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @keyframes pulse-ring { 0%{box-shadow:0 0 0 0 rgba(99,102,241,.4)} 70%{box-shadow:0 0 0 12px rgba(99,102,241,0)} 100%{box-shadow:0 0 0 0 rgba(99,102,241,0)} }
  .pulse-ring { animation: pulse-ring 2.5s cubic-bezier(.66,0,0,1) infinite }
  body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial }
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center px-4 py-10">

<div class="w-full max-w-xl">
  <div class="text-center mb-6">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#206bc4]/10 rounded-2xl border border-[#5b9bd9]/30 mb-4 pulse-ring">
      <x-ti name="lock" class="w-8 h-8 text-[#8fb6e4]" />
    </div>
    <h1 class="text-3xl font-bold text-white">Aktivasi Aplikasi</h1>
    <p class="text-slate-400 mt-2">Aplikasi ini perlu di-aktivasi sebelum bisa digunakan.</p>
  </div>

  <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
    <form method="POST" action="/__pair" class="p-7 space-y-5">
      @csrf

      @if($error)
        <div class="bg-[#d63939]/8 border border-red-200 rounded-lg p-4 flex items-start gap-3">
          <x-ti name="alert-triangle" class="w-5 h-5 text-[#d63939] mt-0.5 flex-shrink-0" />
          <div class="text-sm text-[#b22b2b] leading-relaxed">{{ $error }}</div>
        </div>
      @endif

      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Domain Terdeteksi</label>
        <div class="flex items-center gap-2 p-3.5 bg-slate-50 border border-slate-200 rounded-lg">
          <x-ti name="world" class="w-5 h-5 text-slate-400" />
          <span class="font-mono text-slate-800">{{ $domain }}</span>
          <span class="ml-auto text-xs text-slate-400">auto</span>
        </div>
        <p class="text-xs text-slate-500 mt-1.5">Domain di-deteksi otomatis dari browser kamu — tidak bisa diubah manual.</p>
      </div>

      <div>
        <label for="activation_key" class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Activation Key</label>
        <input
          type="text"
          name="activation_key"
          id="activation_key"
          value="{{ $old_key }}"
          placeholder="XXXXX-XXXXX-XXXXX-XXXXX"
          autocomplete="off"
          autofocus
          class="block w-full px-4 py-3.5 bg-white border border-slate-300 rounded-lg font-mono uppercase tracking-wider text-center text-slate-800 placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-[#206bc4] focus:border-[#206bc4]"
          oninput="this.value = this.value.toUpperCase()"
        >
        <p class="text-xs text-slate-500 mt-1.5">Format: 4 grup × 5 karakter, dipisahkan tanda hubung.</p>
      </div>

      <button
        type="submit"
        id="submitBtn"
        class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 bg-[#206bc4] hover:bg-[#1a569d] text-white font-semibold rounded-lg transition shadow-lg shadow-[#206bc4]/30 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span id="submitText">Aktivasi</span>
        <x-ti name="arrow-right" class="w-5 h-5" />
      </button>
    </form>

    <div class="border-t border-slate-100 bg-slate-50 px-7 py-5">
      <p class="text-xs text-slate-500 text-center leading-relaxed">
        Belum punya activation key?
        <a href="{{ $marketplace_url }}/user/licenses" target="_blank" class="text-[#206bc4] hover:text-[#1a569d] font-medium">
          Buka marketplace ↗
        </a>
        — login → /user/licenses → copy key dari kartu lisensimu.
      </p>
    </div>
  </div>

  <p class="text-center text-xs text-slate-500 mt-6">
    Setelah aktivasi, file <code class="text-slate-300">.license.lock</code> akan dibuat otomatis. Domain ter-bind permanen sampai di-revoke dari marketplace.
  </p>
</div>

<script>
  document.querySelector('form').addEventListener('submit', function() {
    const btn  = document.getElementById('submitBtn');
    const text = document.getElementById('submitText');
    btn.disabled = true;
    text.textContent = 'Memvalidasi key...';
  });
</script>

</body>
</html>
