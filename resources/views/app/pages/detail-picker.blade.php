@extends('app.layout')
@section('title', $picker['title'] ?? 'Pilih Data')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $picker['title'] ?? 'Pilih Data' }}</li>
        </ol></nav>
        <h1 class="page-title">{{ $picker['title'] ?? 'Pilih Data' }}</h1>
        <p class="page-subtitle">{{ $picker['subtitle'] ?? 'Pilih salah satu untuk melihat detailnya.' }}</p>
    </div>
</div>

<div class="row g-app justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ $picker['searchAction'] ?? '/app' }}" class="d-flex gap-2 flex-wrap">
                    <div class="input-icon flex-grow-1">
                        <i class="bi bi-search"></i>
                        <input type="search" name="q" class="form-control" placeholder="{{ $picker['placeholder'] ?? 'Cari…' }}" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i>Cari</button>
                </form>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header"><div><h2 class="card-title">Terbaru</h2><p class="card-subtitle">Klik untuk membuka detail</p></div></div>
            <div class="card-body">
                <ul class="list-app">
                    @forelse($picker['items'] ?? [] as $it)
                    <li>
                        <span class="avatar tone-primary"><i class="bi bi-arrow-up-right"></i></span>
                        <div class="list-main"><a class="list-title" href="{{ $it['url'] }}">{{ $it['title'] }}</a><span class="list-sub">{{ $it['sub'] }}</span></div>
                        <div class="list-end"><a class="btn btn-ghost btn-icon btn-sm" href="{{ $it['url'] }}" aria-label="Buka"><i class="bi bi-chevron-right"></i></a></div>
                    </li>
                    @empty
                    <li class="text-muted">Belum ada data.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
