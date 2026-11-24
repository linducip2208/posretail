@extends('app.layout')
@section('title','Pricing')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Catalog</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Pricing</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Pricing</h1>
                        <p class="page-subtitle">Cost, selling and member prices with margin and change history.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/products-list"><i class="bi bi-arrow-left"></i>Products</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Priced products</div><div class="kpi-value">{{ number_format($products->count()) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-tag"></i></span></div>
                            <div class="kpi-foot"><span>first 60 by name</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Average margin</div><div class="kpi-value">{{ number_format($avgMargin,1) }}%</div></div><span class="icon-box tone-success"><i class="bi bi-graph-up"></i></span></div>
                            <div class="kpi-foot"><span>selling vs cost</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Price changes</div><div class="kpi-value">{{ number_format($changes->count()) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-clock-history"></i></span></div>
                            <div class="kpi-foot"><span>latest 20 logged</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-app">
                    <div class="col-xl-7">
                        <div class="card table-card">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Price list</h2></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Product</th><th class="table-num">Cost</th><th class="table-num">Selling</th><th class="table-num">Member</th><th class="table-num">Margin</th></tr></thead>
                                    <tbody>
                                        @forelse($products as $p)
                                        @php $m = (float)$p->selling_price > 0 ? ((float)$p->selling_price-(float)$p->cost_price)/(float)$p->selling_price*100 : 0; @endphp
                                        <tr>
                                            <td><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a><span class="cell-sub">{{ $p->sku }} · {{ $p->category->name ?? '-' }}</span></td>
                                            <td class="table-num text-muted">{{ tillora_rp($p->cost_price) }}</td>
                                            <td class="table-num fw-semibold">{{ tillora_rp($p->selling_price) }}</td>
                                            <td class="table-num">{{ $p->member_price ? tillora_rp($p->member_price) : '-' }}</td>
                                            <td class="table-num"><span class="status-badge {{ $m >= 20 ? 'status-success' : ($m >= 0 ? 'status-warning' : 'status-danger') }}">{{ number_format($m,1) }}%</span></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada produk.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Recent price changes</h2></div>
                            <div class="card-body">
                                @forelse($changes as $ch)
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <div class="min-w-0"><div class="fw-semibold text-truncate">{{ $ch->product->name ?? '-' }}</div><small class="text-muted">{{ $ch->created_at?->format('d M Y H:i') }} · {{ $ch->user->name ?? 'system' }} · {{ $ch->changed_fields }}</small></div>
                                    <div class="text-end small"><div class="text-muted">{{ tillora_rp($ch->old_selling_price) }}</div><div class="fw-semibold">→ {{ tillora_rp($ch->new_selling_price) }}</div></div>
                                </div>
                                @empty
                                <p class="text-muted small mb-0">Belum ada riwayat perubahan harga.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
@endsection
