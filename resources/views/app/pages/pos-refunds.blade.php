@extends('app.layout')
@section('title','POS Refunds')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/pos">POS</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Refunds</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Refunds</h1>
                        <p class="page-subtitle">Riwayat retur dari database — proses refund baru di kasir.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-arrow-return-left"></i>Proses di Kasir</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total refunded</div><div class="kpi-value">{{ tillora_rp($completedTotal) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-arrow-return-left"></i></span></div>
                            <div class="kpi-foot"><span>retur completed</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Pending</div><div class="kpi-value">{{ $pendingCount }}</div></div><span class="icon-box tone-warning"><i class="bi bi-hourglass-split"></i></span></div>
                            <div class="kpi-foot"><span>menunggu diproses</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card" data-table data-page-size="10">
                    <div class="table-toolbar"><h6 class="card-title mb-0">30 retur terbaru</h6></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Order</th><th>Customer</th><th class="table-num">Total</th><th>Status</th><th class="col-actions"></th></tr></thead>
                            <tbody>
                                @forelse($returns as $rt)
                                <tr>
                                    <td><span class="table-id">{{ $rt->return_number }}</span></td>
                                    <td>{{ $rt->created_at->format('d M Y') }}</td>
                                    <td>@if($rt->order)<a href="/app/sale-details?id={{ $rt->order->order_number }}">{{ $rt->order->order_number }}</a>@else — @endif</td>
                                    <td>{{ optional(optional($rt->order)->customer)->name ?? '—' }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($rt->total_amount) }}</td>
                                    <td><span class="status-badge {{ $rt->status==='completed' ? 'status-success' : ($rt->status==='pending' ? 'status-warning' : 'status-danger') }}">{{ ucfirst($rt->status) }}</span></td>
                                    <td class="col-actions"><a class="btn btn-light btn-sm" href="/pos"><i class="bi bi-arrow-return-left"></i>Proses</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data retur.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
