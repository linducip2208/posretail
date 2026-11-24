@extends('app.layout')
@section('title','Payment Providers')

@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-overview">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Providers</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Payment Providers</h1>
                        <p class="page-subtitle">{{ number_format($totalCount) }} provider pembayaran terdaftar.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/admin/providers/create"><i class="bi bi-plus-lg"></i>Tambah</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total provider</div><div class="kpi-value">{{ number_format($totalCount) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-hdd-network"></i></span></div>
                            <div class="kpi-foot"><span>terdaftar</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-emerald">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Aktif</div><div class="kpi-value">{{ number_format($activeCount) }}</div></div><span class="icon-box tone-success"><i class="bi bi-check-circle"></i></span></div>
                            <div class="kpi-foot"><span>siap dipakai</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Default</div><div class="kpi-value h5 mb-0">{{ $defaultProvider ? $defaultProvider->name : 'Belum ada' }}</div></div><span class="icon-box tone-warning"><i class="bi bi-star"></i></span></div>
                            <div class="kpi-foot"><span>{{ $defaultProvider ? $defaultProvider->type : 'atur di halaman kelola' }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header"><h2 class="card-title h6 mb-0">Daftar provider</h2></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Provider</th><th>Tipe</th><th>Status</th><th>Default</th><th>Metode pembayaran</th></tr></thead>
                            <tbody>
                                @forelse($providers as $p)
                                <tr>
                                    <td><span class="cell-title">{{ $p->name }}</span><span class="cell-sub">{{ $p->base_url ?? '-' }}</span></td>
                                    <td>{{ $p->type }}</td>
                                    <td>
                                        @if($p->is_active)
                                        <span class="status-badge status-success">Aktif</span>
                                        @else
                                        <span class="status-badge status-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->is_default)
                                        <span class="status-badge status-warning">Default</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @forelse($p->paymentMethods as $m)
                                        <span class="status-badge status-info me-1 mb-1 d-inline-block">{{ $m->name }}</span>
                                        @empty
                                        <span class="text-muted">Belum ada metode</span>
                                        @endforelse
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada provider pembayaran. Tambahkan melalui halaman kelola provider.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
