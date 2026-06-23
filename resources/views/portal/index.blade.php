@extends('portal.layout')

@section('title', 'Dashboard')

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

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-xl font-bold text-gray-900">Selamat datang, {{ $customer->name }}!</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $customer->email }}</p>
        @if ($customer->phone)
            <p class="text-sm text-gray-400">{{ $customer->phone }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Total Pesanan</p>
            <p class="text-2xl font-bold text-gray-900">{{ $customer->orders_count }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Total Belanja</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($customer->orders_sum_total_amount ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Poin Loyalitas</p>
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($customer->total_points, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Pesanan Terbaru</h2>
    </div>

    @if ($recentOrders->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gray-100 rounded-full mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <p class="text-sm text-gray-500">Belum ada pesanan.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($recentOrders as $order)
                <a href="{{ route('portal.order', $order->id) }}" class="block bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:border-indigo-300 hover:shadow transition group">
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
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 ml-auto mt-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

</div>
@endsection
