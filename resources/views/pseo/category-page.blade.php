@extends('pseo._layout')
@section('content')
<h1 class="text-2xl font-extrabold text-gray-900 mb-1">{{ $category->name }}</h1>
<p class="text-gray-500 mb-6">{{ $category->description }}</p>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
    @foreach($products as $p)
    <a href="/produk/{{ $p->slug }}" class="bg-white rounded-xl border p-4 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="text-sm font-semibold text-gray-800 line-clamp-2">{{ $p->name }}</div>
        <div class="text-blue-600 font-bold mt-2 font-mono">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-400 mt-1">Stok: {{ $p->current_stock }}</div>
    </a>
    @endforeach
</div>
{{ $products->links() }}
@endsection
