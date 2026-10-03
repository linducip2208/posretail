@extends('portal.layout')

@section('title', 'Pesanan Saya')

@push('styles')
<style>
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-processed { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .payment-paid { background: #d1fae5; color: #065f46; }
    .payment-partial { background: #fef3c7; color: #92400e; }
    .payment-unpaid { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-6 transition">
        <x-ti name="chevron-left" class="w-4 h-4" />
        Kembali ke Dashboard
    </a>

    @if (session('error'))
        <div class="mb-6 p-4 bg-[#d63939]/8 border border-red-200 rounded-xl text-sm text-[#b22b2b]">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-xl font-bold text-gray-900 mb-4">Cari Pesanan</h1>
        <form action="{{ route('portal.lookup') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input
                type="text"
                name="order_number"
                value="{{ request('order_number') }}"
                placeholder="Nomor pesanan (opsional)"
                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-[#206bc4] focus:border-[#206bc4] outline-none transition"
            >
            <button
                type="submit"
                class="px-6 py-2.5 bg-[#206bc4] text-white text-sm font-semibold rounded-xl hover:bg-[#1a569d] active:scale-[0.98] transition shadow-sm"
            >
                Cari
            </button>
        </form>
    </div>

    @if ($orders->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gray-100 rounded-full mb-4">
                <x-ti name="receipt" class="w-7 h-7 text-gray-400" />
            </div>
            <p class="text-sm text-gray-500">Tidak ada pesanan ditemukan.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                <a href="{{ route('portal.order', $order->id) }}" class="block bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:border-[#8fb6e4] hover:shadow transition group">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                            @switch($order->order_status)
                                @case('pending') status-pending @break
                                @case('processed') status-processed @break
                                @case('completed') status-completed @break
                                @case('cancelled') status-cancelled @break
                                @default bg-gray-100 text-gray-600
                            @endswitch
                        ">
                            @switch($order->order_status)
                                @case('pending') Pending @break
                                @case('processed') Diproses @break
                                @case('completed') Selesai @break
                                @case('cancelled') Dibatalkan @break
                                @default {{ $order->order_status }}
                            @endswitch
                        </span>
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xs text-gray-400">
                                {{ $order->orderItems->count() }} item
                                @if ($order->outlet)
                                    &middot; {{ $order->outlet->name }}
                                @endif
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                <span class="
                                    @switch($order->payment_status)
                                        @case('paid') payment-paid @break
                                        @case('partial') payment-partial @break
                                        @case('unpaid') payment-unpaid @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch
                                    inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                ">
                                    @switch($order->payment_status)
                                        @case('paid') Lunas @break
                                        @case('partial') DP @break
                                        @case('unpaid') Belum Bayar @break
                                        @default {{ $order->payment_status }}
                                    @endswitch
                                </span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            <x-ti name="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-[#206bc4] ml-auto mt-0.5 transition" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">
            Menampilkan maksimal 20 pesanan terbaru.
        </p>
    @endif

</div>
@endsection
