@extends('app.layout')
@section('title','POS Cart')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/pos">POS</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Cart</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Held carts</h1>
                        <p class="page-subtitle">Keranjang yang ditahan dari database — lanjutkan transaksinya di kasir.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Lanjutkan di Kasir</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Held carts</div><div class="kpi-value">{{ $carts->count() }}</div></div><span class="icon-box tone-warning"><i class="bi bi-pause-circle"></i></span></div>
                            <div class="kpi-foot"><span>keranjang tertahan</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total nilai</div><div class="kpi-value">{{ tillora_rp($grandTotal) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-cart3"></i></span></div>
                            <div class="kpi-foot"><span>gabungan semua held carts</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-app">
                    @forelse($carts as $c)
                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div><span class="table-id">{{ $c->label ?? 'Cart #'.$c->id }}</span><span class="cell-sub d-block">{{ optional($c->customer)->name ?? 'Walk-in' }} · {{ optional($c->outlet)->name ?? '—' }}</span></div>
                                    <strong>{{ tillora_rp($c->line_total) }}</strong>
                                </div>
                                <small class="text-muted d-block mb-2">{{ optional($c->user)->name ?? '—' }} · {{ $c->created_at->format('d M Y H:i') }} · {{ $c->line_count }} item</small>
                                <ul class="legend-list">
                                    @foreach(array_slice(is_array($c->items) ? $c->items : [], 0, 5) as $it)
                                    <li><span>{{ $it['name'] ?? 'Item' }} × {{ $it['qty'] ?? 0 }}</span><strong>{{ tillora_rp(($it['price'] ?? 0) * ($it['qty'] ?? 0)) }}</strong></li>
                                    @endforeach
                                </ul>
                                <a class="btn btn-primary btn-sm w-100 mt-3" href="/pos"><i class="bi bi-play-circle"></i>Lanjutkan di Kasir</a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">Tidak ada held cart saat ini.</div></div></div>
                    @endforelse
                </div>
@endsection
