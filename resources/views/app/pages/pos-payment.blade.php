@extends('app.layout')
@section('title','POS Payment')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/pos">POS</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Payment</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Payment methods</h1>
                        <p class="page-subtitle">Metode pembayaran aktif dari database — pembayaran dilakukan di kasir.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Bayar di Kasir</a>
                    </div>
                </div>

                <div class="row g-app">
                    <div class="col-lg-7">
                        <div class="card table-card mb-4">
                            <div class="table-toolbar"><h6 class="card-title mb-0">Metode pembayaran</h6></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead><tr><th>Metode</th><th>Kode</th><th>Tipe</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @forelse($methods as $m)
                                        <tr>
                                            <td><span class="pay-chip"><i class="bi bi-credit-card-2-front"></i>{{ $m->name }}</span></td>
                                            <td><span class="table-id">{{ $m->code ?? '—' }}</span></td>
                                            <td class="text-muted">{{ $m->type ?? '—' }}</td>
                                            <td><span class="status-badge {{ $m->active ? 'status-success' : 'status-danger' }}">{{ $m->active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada metode pembayaran.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Open carts menunggu bayar</h6>
                                @forelse($openCarts as $c)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div><span class="table-id">{{ $c->label ?? 'Cart #'.$c->id }}</span><small class="d-block text-muted">{{ optional($c->customer)->name ?? 'Walk-in' }} · {{ optional($c->outlet)->name ?? '—' }}</small></div>
                                    <strong>{{ tillora_rp($c->line_total) }}</strong>
                                </div>
                                @empty
                                <p class="text-muted mb-0">Tidak ada cart menunggu pembayaran.</p>
                                @endforelse
                                <a class="btn btn-primary w-100 mt-3" href="/pos"><i class="bi bi-upc-scan"></i>Buka Kasir</a>
                            </div>
                        </div>
                    </div>
                </div>
@endsection
