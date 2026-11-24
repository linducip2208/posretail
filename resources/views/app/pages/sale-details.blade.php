@extends('app.layout')
@section('title','Sale Details')


@section('content')
@php
  $badge = match($order->order_status){ 'completed'=>'status-success', 'cancelled'=>'status-danger', 'pending'=>'status-info', default=>'status-warning' };
  $payBadge = match($order->payment_status){ 'paid'=>'status-success', 'partial'=>'status-warning', 'unpaid'=>'status-danger', 'refunded'=>'status-danger', default=>'status-info' };
@endphp
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">{{ $order->order_number }}</h1>
                        <p class="page-subtitle">Dibuat {{ $order->created_at->format('d M Y H:i') }} · {{ optional($order->outlet)->name ?? '—' }}</p>
                    </div>
                    <div class="page-actions">
                        <span class="status-badge {{ $badge }}">{{ ucfirst($order->order_status) }}</span>
                        <a class="btn btn-light" href="/app/pos-receipt?id={{ $order->order_number }}"><i class="bi bi-receipt"></i>Receipt</a>
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Buka Kasir</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total</div><div class="kpi-value">{{ tillora_rp($order->total_amount) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-currency-dollar"></i></span></div>
                            <div class="kpi-foot"><span class="status-badge {{ $payBadge }}">{{ ucfirst($order->payment_status ?? '-') }}</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Subtotal</div><div class="kpi-value">{{ tillora_rp($order->subtotal) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-basket"></i></span></div>
                            <div class="kpi-foot"><span>{{ $order->orderItems->sum('quantity') }} item</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Diskon</div><div class="kpi-value">{{ tillora_rp($order->discount_amount) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-percent"></i></span></div>
                            <div class="kpi-foot"><span>pajak {{ tillora_rp($order->tax_amount) }}</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Sisa / Deposit</div><div class="kpi-value">{{ tillora_rp($order->remaining_amount) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-wallet2"></i></span></div>
                            <div class="kpi-foot"><span>deposit {{ tillora_rp($order->deposit_amount) }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-app">
                    <div class="col-lg-8">
                        <div class="card table-card mb-4">
                            <div class="table-toolbar"><h6 class="card-title mb-0">Items</h6></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead><tr><th>Produk</th><th class="table-num">Qty</th><th class="table-num">Harga</th><th class="table-num">Subtotal</th></tr></thead>
                                    <tbody>
                                        @forelse($order->orderItems as $it)
                                        <tr>
                                            <td><div class="cell-user"><span class="avatar tone-neutral"><i class="bi bi-box-seam"></i></span><div><span class="cell-title">{{ optional($it->product)->name ?? 'Produk #'.$it->product_id }}</span><span class="cell-sub">{{ optional($it->product)->sku ?? '' }}</span></div></div></td>
                                            <td class="table-num">{{ $it->quantity }}</td>
                                            <td class="table-num">{{ tillora_rp($it->unit_price) }}</td>
                                            <td class="table-num fw-semibold">{{ tillora_rp($it->subtotal) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada item pada order ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Payments</h6>
                                @forelse($order->payments as $p)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div><span class="pay-chip"><i class="bi bi-credit-card-2-front"></i>{{ optional($p->paymentMethod)->name ?? '—' }}</span><small class="text-muted ms-2">{{ $p->reference_number ?? '' }}</small></div>
                                    <div class="text-end"><strong>{{ tillora_rp($p->amount) }}</strong><br><small class="text-muted">{{ ucfirst($p->status) }} · {{ optional($p->paid_at)->format('d M Y H:i') ?? $p->created_at->format('d M Y H:i') }}</small></div>
                                </div>
                                @empty
                                <p class="text-muted mb-0">Belum ada pembayaran tercatat.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Ringkasan</h6>
                                <ul class="legend-list">
                                    <li><span>Customer</span><strong>{{ optional($order->customer)->name ?? 'Walk-in customer' }}</strong></li>
                                    <li><span>Outlet</span><strong>{{ optional($order->outlet)->name ?? '—' }}</strong></li>
                                    <li><span>Kasir</span><strong>{{ optional($order->user)->name ?? '—' }}</strong></li>
                                    <li><span>Tipe order</span><strong>{{ $order->order_type ?? '—' }}</strong></li>
                                    <li><span>No. antrian</span><strong>{{ $order->queue_number ?? '—' }}</strong></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Status timeline</h6>
                                <div class="alert-strip">
                                    <div class="alert-strip-item"><i class="bi bi-plus-circle"></i><div><strong>Dibuat</strong><small class="d-block text-muted">{{ $order->created_at->format('d M Y H:i') }}</small></div></div>
                                    <div class="alert-strip-item"><i class="bi bi-credit-card"></i><div><strong>Pembayaran: {{ ucfirst($order->payment_status ?? '-') }}</strong><small class="d-block text-muted">{{ $order->payments->count() }} pembayaran tercatat</small></div></div>
                                    <div class="alert-strip-item"><i class="bi bi-check-circle"></i><div><strong>Order: {{ ucfirst($order->order_status) }}</strong><small class="d-block text-muted">diperbarui {{ $order->updated_at->format('d M Y H:i') }}</small></div></div>
                                </div>
                                @if($order->notes)<p class="text-muted mt-3 mb-0"><small>Catatan: {{ $order->notes }}</small></p>@endif
                            </div>
                        </div>
                    </div>
                </div>
@endsection
