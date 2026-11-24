@extends('app.layout')
@section('title','Users & Roles')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/employees-list">People</a></li>
                <li class="breadcrumb-item active" aria-current="page">Users & Roles</li>
            </ol>
        </nav>
        <h1 class="page-title">Users & Roles</h1>
        <p class="page-subtitle">{{ $totalUsers }} active users across {{ $roles->count() }} roles.</p>
    </div>
</div>

<div class="row g-app mb-4">
    @forelse($roles as $role)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="h6 mb-0">{{ $role->name }}</h2>
                    <span class="status-badge status-primary no-dot">{{ $role->users_count }} users</span>
                </div>
                <p class="text-muted small">{{ $role->description ?? $role->slug }}</p>
                <ul class="legend-list">
                    @forelse($role->permissions->take(6) as $p)
                        <li><span>{{ $p->name }}</span></li>
                    @empty
                        <li><span class="text-muted">No permissions assigned.</span></li>
                    @endforelse
                    @if($role->permissions->count() > 6)
                        <li><span class="text-muted">+{{ $role->permissions->count() - 6 }} more…</span></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">Belum ada role.</div></div></div>
    @endforelse
</div>

<div class="card table-card">
    <div class="table-toolbar"><h2 class="h6 mb-0">All permissions by group</h2></div>
    <div class="card-body">
        @forelse($grouped as $group => $perms)
            <h3 class="h6 mt-3">{{ $groupLabels[$group] ?? ucfirst($group) }} <span class="text-muted">({{ $perms->count() }})</span></h3>
            <div class="d-flex flex-wrap gap-2">
                @foreach($perms as $p)
                    <span class="status-badge status-neutral no-dot">{{ $p->name }}</span>
                @endforeach
            </div>
        @empty
            <p class="text-muted">Belum ada permission.</p>
        @endforelse
    </div>
</div>
@endsection
