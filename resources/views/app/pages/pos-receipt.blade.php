@extends('app.layout')
@section('title','POS Receipt')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Receipt</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Receipt</h1>
                        <p class="page-subtitle">Struk penjualan asli dari database.</p>
                    </div>
                    <div class="page-actions">
                        <button type="button" class="btn btn-light" onclick="window.print()"><i class="bi bi-printer"></i>Print</button>
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Buka Kasir</a>
                    </div>
                </div>

                @if($order)
                <div class="row g-app justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <h5 class="mb-0">{{ optional($order->outlet)->name ?? config('app.name','POS Retail') }}</h5>
                                    <small class="text-muted">{{ $order->order_number }} · {{ $order->created_at->format('d M Y H:i') }}</small><br>
                                    <small class="text-muted">Kasir: {{ optional($order->user)->name ?? '—' }} · Customer: {{ optional($order->customer)->name ?? 'Walk-in' }}</small>
                                </div>
                                <hr>
                                @foreach($order->orderItems as $it)
                                <div class="d-flex justify-content-between py-1">
                                    <div><span>{{ optional($it->product)->name ?? 'Produk #'.$it->product_id }}</span><small class="d-block text-muted">{{ $it->quantity }} × {{ tillora_rp($it->unit_price) }}</small></div>
                                    <strong>{{ tillora_rp($it->subtotal) }}</strong>
                                </div>
                                @endforeach
                                <hr>
                                <ul class="legend-list">
                                    <li><span>Subtotal</span><strong>{{ tillora_rp($order->subtotal) }}</strong></li>
                                    <li><span>Diskon</span><strong>{{ tillora_rp($order->discount_amount) }}</strong></li>
                                    <li><span>Pajak</span><strong>{{ tillora_rp($order->tax_amount) }}</strong></li>
                                    <li><span>Total</span><strong>{{ tillora_rp($order->total_amount) }}</strong></li>
                                </ul>
                                <hr>
                                @foreach($order->payments as $p)
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">{{ optional($p->paymentMethod)->name ?? 'Pembayaran' }}</span>
                                    <span>{{ tillora_rp($p->amount) }}</span>
                                </div>
                                @endforeach
                                <p class="text-center text-muted mt-3 mb-0"><small>Terima kasih atas kunjungan Anda</small></p>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="card"><div class="card-body text-center text-muted py-4">Belum ada order completed untuk ditampilkan sebagai struk.</div></div>
                @endif
@endsection
