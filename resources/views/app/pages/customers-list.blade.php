@extends('app.layout')
@section('title','Customers')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/customers-list">People</a></li>
                <li class="breadcrumb-item active" aria-current="page">Customers</li>
            </ol>
        </nav>
        <h1 class="page-title">Customers</h1>
        <p class="page-subtitle">Your customer directory with spend, visit frequency and loyalty status.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/customer-groups"><i class="bi bi-diagram-3"></i>Segments</a>
        <a class="btn btn-primary" href="/app/customer-create"><i class="bi bi-person-plus"></i>Add customer</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Total customers</div>
                    <div class="kpi-value">{{ number_format($totalCustomers) }}</div>
                </div>
                <span class="icon-box tone-primary"><i class="bi bi-people"></i></span>
            </div>
            <div class="kpi-foot"><span>{{ number_format($customers->count()) }} shown</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-lavender">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Loyalty members</div>
                    <div class="kpi-value">{{ number_format($loyaltyMembers) }}</div>
                </div>
                <span class="icon-box tone-violet"><i class="bi bi-gem"></i></span>
            </div>
            <div class="kpi-foot"><span>with points &gt; 0</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Repeat purchase rate</div>
                    <div class="kpi-value">{{ $repeatRate }}%</div>
                </div>
                <span class="icon-box tone-success"><i class="bi bi-arrow-repeat"></i></span>
            </div>
            <div class="kpi-foot"><span>customers with 2+ orders</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Avg order value</div>
                    <div class="kpi-value">{{ tillora_rp($avgLtv) }}</div>
                </div>
                <span class="icon-box tone-cyan"><i class="bi bi-person-heart"></i></span>
            </div>
            <div class="kpi-foot"><span>completed orders</span></div>
        </div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <form method="GET" action="/app/customers-list" class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search name, email, phone…" aria-label="Search customers">
        </form>
        <div class="toolbar-end">
            <a class="btn btn-light" href="/app/customers-list"><i class="bi bi-arrow-counterclockwise"></i>Reset</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="customersTable">
            <thead>
                <tr>
                    <th class="col-main">Customer</th>
                    <th>Contact</th>
                    <th>Group</th>
                    <th>Tier</th>
                    <th class="table-num">Orders</th>
                    <th class="table-num">Spent</th>
                    <th class="table-num">Points</th>
                    <th class="col-actions"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr>
                    <td><div class="cell-user"><span class="avatar">{{ strtoupper(substr($c->name,0,1)) }}</span><div><a class="cell-title" href="/app/customer-details?id={{ $c->id }}">{{ $c->name }}</a><span class="cell-sub">#{{ $c->id }}</span></div></div></td>
                    <td>{{ $c->email ?? '—' }}<small class="d-block text-muted">{{ $c->phone ?? '—' }}</small></td>
                    <td>{{ $c->customerGroup->name ?? '—' }}</td>
                    <td>{{ $c->membershipTier->name ?? '—' }}</td>
                    <td class="table-num">{{ number_format($c->orders_count ?? 0) }}</td>
                    <td class="table-num fw-semibold">{{ tillora_rp($c->spent_total ?? 0) }}</td>
                    <td class="table-num">{{ number_format($c->total_points ?? 0) }}</td>
                    <td class="col-actions"><a class="btn btn-ghost btn-icon btn-sm" href="/app/customer-details?id={{ $c->id }}" aria-label="View details"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div class="d-flex align-items-center gap-2">
            <span>{{ $customers->count() }} pelanggan ditampilkan</span>
        </div>
    </div>
</div>
@endsection
