@extends('app.layout')
@section('title','Stock Levels')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Stock levels</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Stock Levels</h1>
                        <p class="page-subtitle">{{ number_format($totalUnits) }} units on hand. Sorted by lowest stock first.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/stock-adjustments"><i class="bi bi-sliders"></i>Adjustments</a>
                        <a class="btn btn-primary" href="/app/low-stock-alerts"><i class="bi bi-exclamation-triangle"></i>Low stock</a>
                    </div>
                </div>

                <div class="card table-card">
                    <form method="GET" action="/app/stock-levels" class="table-toolbar">
                        <div class="input-icon">
                            <i class="bi bi-search"></i>
                            <input type="search" name="q" class="form-control" placeholder="Search product…" value="{{ $q }}" aria-label="Search product…">
                        </div>
                        <select class="form-select" name="outlet" aria-label="All outlets" onchange="this.form.submit()">
                            <option value="">All outlets</option>
                            @foreach($outlets as $o)
                            <option value="{{ $o->id }}" @selected((string)$outlet === (string)$o->id)>{{ $o->name }}</option>
                            @endforeach
                        </select>
                        <div class="toolbar-end"><button type="submit" class="btn btn-light"><i class="bi bi-search"></i>Cari</button></div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Product</th><th>SKU</th><th class="table-num">On hand</th><th class="table-num">Min</th><th class="table-num">Max</th><th style="min-width:160px">Level</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($products as $p)
                                @php
                                    $st=(int)$p->current_stock; $mn=(int)($p->min_stock ?? 0); $mx=(int)($p->max_stock ?? 0);
                                    $span = max(1, $mx - 0); $pct = $mx>0 ? min(100, round($st/$mx*100)) : ($st>0?100:0);
                                    $cls=$st<=0?'is-out':($st<=$mn?'is-low':'is-ok');
                                    $badge=$st<=0?'status-danger':($st<=$mn?'status-warning':'status-success');
                                    $lbl=$st<=0?'Out':($st<=$mn?'Low':'OK');
                                @endphp
                                <tr>
                                    <td><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a><span class="cell-sub">{{ $p->category->name ?? '-' }}</span></td>
                                    <td><span class="font-mono text-xs">{{ $p->sku }}</span></td>
                                    <td class="table-num fw-semibold">{{ number_format($st) }}</td>
                                    <td class="table-num text-muted">{{ number_format($mn) }}</td>
                                    <td class="table-num text-muted">{{ $mx ? number_format($mx) : '-' }}</td>
                                    <td><div class="stock-meter {{ $cls }}"><div class="progress"><div class="progress-bar" style="width: {{ $pct }}%;"></div></div></div></td>
                                    <td><span class="status-badge {{ $badge }}">{{ $lbl }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada produk yang cocok dengan filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
