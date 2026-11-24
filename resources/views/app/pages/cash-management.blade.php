@extends('app.layout')
@section('title','Cash Management')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Cash management</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Cash management</h1>
                        <p class="page-subtitle">Arus kas laci dari database — cash in / out per shift.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-arrow-down-up"></i>Catat di Kasir</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Cash in hari ini</div><div class="kpi-value">{{ tillora_rp($cashInToday) }}</div></div><span class="icon-box tone-success"><i class="bi bi-box-arrow-in-down"></i></span></div>
                            <div class="kpi-foot"><span>kas masuk hari ini</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Cash out hari ini</div><div class="kpi-value">{{ tillora_rp($cashOutToday) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-box-arrow-up"></i></span></div>
                            <div class="kpi-foot"><span>kas keluar hari ini</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Selisih bersih</div><div class="kpi-value">{{ tillora_rp($cashInToday - $cashOutToday) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-calculator"></i></span></div>
                            <div class="kpi-foot"><span>{{ $openShifts }} shift sedang open</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card" data-table data-page-size="10">
                    <div class="table-toolbar"><h6 class="card-title mb-0">Cash drawer transactions</h6></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Tanggal</th><th>Tipe</th><th>Shift / Kasir</th><th>Order</th><th>Catatan</th><th class="table-num">Jumlah</th></tr></thead>
                            <tbody>
                                @forelse($transactions as $t)
                                <tr>
                                    <td><span class="fw-medium text-heading">{{ $t->created_at->format('d M Y') }}</span><small class="d-block text-muted">{{ $t->created_at->format('H:i') }}</small></td>
                                    <td><span class="status-badge {{ $t->type==='cash_in' ? 'status-success' : 'status-danger' }}">{{ $t->type==='cash_in' ? 'Cash in' : 'Cash out' }}</span></td>
                                    <td>{{ optional(optional($t->shift)->user)->name ?? '—' }}<span class="cell-sub d-block">Shift #{{ $t->shift_id ?? '—' }}</span></td>
                                    <td>@if($t->order)<a href="/app/sale-details?id={{ $t->order->order_number }}">{{ $t->order->order_number }}</a>@else — @endif</td>
                                    <td class="text-muted">{{ \Illuminate\Support\Str::limit($t->notes ?? '—', 45) }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($t->amount) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi kas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
