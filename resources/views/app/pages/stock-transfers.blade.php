@extends('app.layout')
@section('title','Stock Transfers')

@section('content')

                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Transfers</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Stock Transfers</h1>
                        <p class="page-subtitle">Move stock between outlets and track each transfer.</p>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Pending</div><div class="kpi-value">{{ number_format($pending) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-hourglass-split"></i></span></div>
                            <div class="kpi-foot"><span>awaiting approval</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">In transit</div><div class="kpi-value">{{ number_format($inTransit) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-truck"></i></span></div>
                            <div class="kpi-foot"><span>on the way</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Completed</div><div class="kpi-value">{{ number_format($completed) }}</div></div><span class="icon-box tone-success"><i class="bi bi-check2-circle"></i></span></div>
                            <div class="kpi-foot"><span>received transfers</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header"><h2 class="card-title h6 mb-0">Latest transfers</h2></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Transfer No</th><th>Date</th><th>From</th><th>To</th><th class="table-num">Items</th><th>By</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($transfers as $t)
                                <tr>
                                    <td><span class="font-mono text-xs">{{ $t->transfer_number }}</span></td>
                                    <td class="text-muted small">{{ $t->created_at?->format('d M Y H:i') }}</td>
                                    <td>{{ $t->fromOutlet->name ?? '-' }}</td>
                                    <td>{{ $t->toOutlet->name ?? '-' }}</td>
                                    <td class="table-num">{{ number_format($t->stockTransferItems->count()) }}</td>
                                    <td>{{ $t->user->name ?? '-' }}</td>
                                    <td><span class="status-badge {{ $t->status === 'completed' ? 'status-success' : ($t->status === 'pending' ? 'status-warning' : 'status-info') }}">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transfer stok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
