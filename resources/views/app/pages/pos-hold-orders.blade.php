@extends('app.layout')
@section('title','POS Hold Orders')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/pos">POS</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Hold orders</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Hold orders</h1>
                        <p class="page-subtitle">Semua order yang ditahan dari database — resume di kasir.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-play-circle"></i>Resume di Kasir</a>
                    </div>
                </div>

                <div class="card table-card" data-table data-page-size="10">
                    <div class="table-toolbar"><h6 class="card-title mb-0">{{ $carts->count() }} held orders</h6></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Label</th><th>Dibuat</th><th>Customer</th><th>Outlet</th><th>Kasir</th><th class="table-num">Items</th><th class="table-num">Total</th><th class="col-actions"></th></tr></thead>
                            <tbody>
                                @forelse($carts as $c)
                                <tr>
                                    <td><span class="table-id">{{ $c->label ?? 'Cart #'.$c->id }}</span></td>
                                    <td>{{ $c->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ optional($c->customer)->name ?? 'Walk-in' }}</td>
                                    <td>{{ optional($c->outlet)->name ?? '—' }}</td>
                                    <td>{{ optional($c->user)->name ?? '—' }}</td>
                                    <td class="table-num">{{ $c->line_count }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($c->line_total) }}</td>
                                    <td class="col-actions"><a class="btn btn-primary btn-sm" href="/pos"><i class="bi bi-play-circle"></i>Resume</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada hold order saat ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
