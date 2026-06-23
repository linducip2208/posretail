@extends('pseo._layout')
@section('content')
<h1 class="text-2xl font-extrabold text-gray-900 mb-2">{{ $product->name }}</h1>
<p class="text-gray-500 mb-4">{{ $product->category->name ?? '' }} &bull; {{ $product->brand->name ?? '' }}</p>

<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl border p-6 mb-6">
            <div class="text-3xl font-extrabold text-blue-600 font-mono mb-4">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
            @if($product->wholesale_price)<div class="text-sm text-gray-500">Grosir: Rp {{ number_format($product->wholesale_price, 0, ',', '.') }}</div>@endif
            @if($product->member_price)<div class="text-sm text-gray-500">Member: Rp {{ number_format($product->member_price, 0, ',', '.') }}</div>@endif
            <div class="mt-4 text-sm text-gray-600">{{ $product->description }}</div>
            <div class="mt-4 flex gap-4 text-sm text-gray-500">
                <span>SKU: <code class="font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $product->sku }}</code></span>
                <span>Stok: <strong class="{{ $product->current_stock > 10 ? 'text-green-600' : 'text-red-600' }}">{{ $product->current_stock }}</strong></span>
            </div>
        </div>

        @if($related->isNotEmpty())
        <h2 class="text-lg font-bold text-gray-800 mb-3">Produk Terkait</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($related as $r)
            <a href="/produk/{{ $r->slug }}" class="bg-white rounded-xl border p-3 hover:border-blue-300 transition-colors">
                <div class="text-sm font-semibold">{{ $r->name }}</div>
                <div class="text-blue-600 font-bold text-sm font-mono mt-1">Rp {{ number_format($r->selling_price, 0, ',', '.') }}</div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <div>
        <div class="bg-white rounded-2xl border p-4">
            <h3 class="font-semibold text-sm mb-2">Detail Produk</h3>
            <table class="w-full text-sm">
                <tr class="border-b"><td class="py-2 text-gray-500">Kategori</td><td class="py-2 font-medium">{{ $product->category->name ?? '-' }}</td></tr>
                <tr class="border-b"><td class="py-2 text-gray-500">Brand</td><td class="py-2 font-medium">{{ $product->brand->name ?? '-' }}</td></tr>
                <tr class="border-b"><td class="py-2 text-gray-500">Unit</td><td class="py-2 font-medium">{{ $product->unit->name ?? '-' }}</td></tr>
                <tr><td class="py-2 text-gray-500">Barcode</td><td class="py-2 font-mono text-xs">{{ $product->barcode }}</td></tr>
            </table>
        </div>
    </div>
</div>
@endsection
