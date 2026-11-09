<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="5">
<title>Progress Generate Foto Produk (ComfyUI)</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen p-6">
<div class="max-w-4xl mx-auto">
  <h1 class="text-2xl font-bold mb-1">Generate Foto Produk — ComfyUI</h1>
  <p class="text-sm text-slate-500 mb-4">Auto-refresh tiap 5 detik · Model DreamShaperXL Turbo 768px · Packshot background putih</p>
  <div class="bg-white rounded-xl shadow p-5 mb-5">
    <div class="flex justify-between text-sm mb-2">
      <span class="font-semibold">{{ $done }} / {{ $total }} gambar</span>
      <span class="text-slate-500">{{ $pct }}% · gagal: {{ $fail }} · sisa: {{ $remaining }}</span>
    </div>
    <div class="w-full bg-slate-200 rounded-full h-5 overflow-hidden">
      <div class="bg-blue-600 h-5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
    </div>
    <p class="text-xs text-slate-500 mt-2">Terakhir: <b>{{ $lastFile }}</b> ({{ $lastTime }}) · Estimasi sisa: {{ $eta }}</p>
    <p class="text-xs text-slate-400 mt-1">File: storage/app/public/products/*.jpg · Log: jalankan <code>python scripts/generate_product_images.py</code></p>
  </div>
  <div class="bg-white rounded-xl shadow p-5">
    <h2 class="font-semibold mb-3">20 gambar terbaru</h2>
    <div class="grid grid-cols-4 md:grid-cols-5 gap-3">
      @foreach($latest as $img)
        <div class="border rounded-lg overflow-hidden bg-slate-50">
          <img src="{{ $img['url'] }}" alt="{{ $img['name'] }}" class="w-full aspect-square object-cover" loading="lazy">
          <p class="text-[10px] p-1 truncate" title="{{ $img['name'] }}">{{ $img['name'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</div>
</body>
</html>
