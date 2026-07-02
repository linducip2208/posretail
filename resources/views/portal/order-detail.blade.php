@extends('portal.layout')

@section('title', $order->order_number)

@push('styles')
<style>
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-processed { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .payment-paid { background: #d1fae5; color: #065f46; }
    .payment-partial { background: #fef3c7; color: #92400e; }
    .payment-unpaid { background: #fee2e2; color: #991b1b; }

    @media print {
        body { background: white; }
        .no-print { display: none !important; }
        header { border-bottom: 1px solid #e5e7eb !important; background: white !important; }
        main { max-width: 100% !important; padding: 0 !important; }
        footer { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6 no-print">
        <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Kembali ke Dashboard
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
            </svg>
            Cetak
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6 card">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
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
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                    @switch($order->payment_status)
                        @case('paid') payment-paid @break
                        @case('partial') payment-partial @break
                        @case('unpaid') payment-unpaid @break
                        @default bg-gray-100 text-gray-600
                    @endswitch
                ">
                    @switch($order->payment_status)
                        @case('paid') Lunas @break
                        @case('partial') DP @break
                        @case('unpaid') Belum Bayar @break
                        @default {{ $order->payment_status }}
                    @endswitch
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
            @if ($order->outlet)
                <div>
                    <p class="text-gray-400 mb-0.5">Outlet</p>
                    <p class="font-medium text-gray-700">{{ $order->outlet->name }}</p>
                </div>
            @endif
            <div>
                <p class="text-gray-400 mb-0.5">Tipe</p>
                <p class="font-medium text-gray-700">{{ $order->order_type ?? '-' }}</p>
            </div>
            @if ($order->queue_number)
                <div>
                    <p class="text-gray-400 mb-0.5">No. Antrian</p>
                    <p class="font-medium text-gray-700">{{ $order->queue_number }}</p>
                </div>
            @endif
            @if ($order->order_notes)
                <div class="col-span-2 sm:col-span-4">
                    <p class="text-gray-400 mb-0.5">Catatan</p>
                    <p class="font-medium text-gray-700">{{ $order->order_notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6 card">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Item Pesanan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Produk</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Qty</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Harga</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td class="px-6 py-3">
                                <p class="font-medium text-gray-900">{{ $item->product?->name ?? 'Produk #'.$item->product_id }}</p>
                                @if ($item->productVariant)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->productVariant->name }}</p>
                                @endif
                                @if ($item->discount_percent > 0 || $item->discount_amount > 0)
                                    <p class="text-xs text-red-500 mt-0.5">
                                        @if ($item->discount_percent > 0)
                                            Diskon {{ number_format($item->discount_percent, 1) }}%
                                        @endif
                                        @if ($item->discount_amount > 0)
                                            -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}
                                        @endif
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-right font-medium text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 card">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Ringkasan</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium text-gray-700">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($order->discount_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Diskon</span>
                        <span class="font-medium text-red-500">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if ($order->tax_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pajak</span>
                        <span class="font-medium text-gray-700">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-gray-100 pt-2 mt-2">
                    <span class="font-semibold text-gray-900">Total</span>
                    <span class="font-bold text-gray-900 text-base">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                @if ($order->deposit_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Uang Muka</span>
                        <span class="font-medium text-gray-700">Rp {{ number_format($order->deposit_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sisa</span>
                        <span class="font-medium text-orange-600">Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 card">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Pembayaran</h2>
            @if ($order->payments->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($order->payments as $payment)
                        <div class="flex items-center justify-between text-sm">
                            <div>
                                <p class="font-medium text-gray-700">{{ $payment->paymentMethod?->name ?? 'Pembayaran #'.$payment->id }}</p>
                                @if ($payment->reference_number)
                                    <p class="text-xs text-gray-400">{{ $payment->reference_number }}</p>
                                @endif
                                @if ($payment->paid_at)
                                    <p class="text-xs text-gray-400">{{ $payment->paid_at->format('d M Y, H:i') }}</p>
                                @endif
                            </div>
                            <span class="font-semibold text-gray-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">Belum ada pembayaran.</p>
            @endif
        </div>
    </div>

    @if ($order->is_installment)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6 card">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Cicilan</h2>
            <p class="text-sm text-gray-500">
                Periode: {{ $order->installment_period }} &middot; {{ $order->installment_count }}x cicilan
            </p>
        </div>
    @endif


</div>
@endsection
