@extends('app.layout')
@section('title','Unit Conversions')

@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Unit conversions</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Unit Conversions</h1>
                        <p class="page-subtitle">{{ number_format($totalCount) }} aturan konversi satuan antar unit.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/admin/unit-conversions/create"><i class="bi bi-plus-lg"></i>Tambah</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-12 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total konversi</div><div class="kpi-value">{{ number_format($totalCount) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-arrow-left-right"></i></span></div>
                            <div class="kpi-foot"><span>semua produk &amp; satuan</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header"><h2 class="card-title h6 mb-0">Daftar konversi</h2></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Dari</th><th>Ke</th><th class="table-num">Faktor</th><th>Produk</th></tr></thead>
                            <tbody>
                                @forelse($conversions as $c)
                                <tr>
                                    <td class="fw-semibold">{{ $c->fromUnit->name ?? '-' }}</td>
                                    <td class="fw-semibold">{{ $c->toUnit->name ?? '-' }}</td>
                                    <td class="table-num">1 {{ $c->fromUnit->code ?? '' }} = {{ $c->conversion_rate }} {{ $c->toUnit->code ?? '' }}</td>
                                    <td>
                                        @if($c->product)
                                        <a class="cell-title" href="/app/product-details?id={{ $c->product->id }}">{{ $c->product->name }}</a><span class="cell-sub">{{ $c->product->sku }}</span>
                                        @else
                                        <span class="text-muted">Semua produk</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada konversi satuan. Tambahkan melalui halaman kelola konversi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
