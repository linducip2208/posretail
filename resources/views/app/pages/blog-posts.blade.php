@extends('app.layout')
@section('title','Blog Posts')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/blog-posts">Tools</a></li>
                <li class="breadcrumb-item active" aria-current="page">Blog Posts</li>
            </ol>
        </nav>
        <h1 class="page-title">Artikel blog</h1>
        <p class="page-subtitle">Konten publikasi toko dari database — kelola lewat Filament.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/blog-posts/create"><i class="bi bi-plus-lg"></i>Tulis baru</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Total artikel</div>
                    <div class="kpi-value">{{ number_format($totalPosts) }}</div>
                </div>
                <span class="icon-box tone-primary"><i class="bi bi-file-text"></i></span>
            </div>
            <div class="kpi-foot"><span>{{ number_format($catCount) }} kategori</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Tayang</div>
                    <div class="kpi-value">{{ number_format($publishedCount) }}</div>
                </div>
                <span class="icon-box tone-cyan"><i class="bi bi-globe"></i></span>
            </div>
            <div class="kpi-foot"><span>artikel dipublikasikan</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Draf</div>
                    <div class="kpi-value">{{ number_format($draftCount) }}</div>
                </div>
                <span class="icon-box tone-warning"><i class="bi bi-pencil"></i></span>
            </div>
            <div class="kpi-foot"><span>belum dipublikasikan</span></div>
        </div>
    </div>
    <div class="col-12 col-xl-3">
        <div class="card mb-0">
            <div class="card-body">
                <h6 class="card-title mb-3">Artikel per kategori</h6>
                <div style="height:120px;"><canvas id="blogCat"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">Kategori</h6>
        <div class="legend-list">
            @forelse($categories as $cat)
            <div class="legend-row">
                <span class="legend-dot"></span>
                <span class="legend-label">{{ $cat->name }}</span>
                <span class="legend-value">{{ number_format($cat->posts_count) }} artikel</span>
            </div>
            @empty
            <p class="text-muted mb-0">Belum ada kategori blog.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="9">
    <div class="table-toolbar">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Cari judul, kategori…" data-table-search aria-label="Cari artikel">
        </div>
        <div class="toolbar-end">
            <a class="btn btn-primary" href="/admin/blog-posts/create"><i class="bi bi-plus-lg"></i>Tulis baru</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-app">
            @forelse($posts as $p)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            @if($p->is_published)
                            <span class="status-badge status-success">Tayang</span>
                            @else
                            <span class="status-badge status-warning">Draf</span>
                            @endif
                            <span class="cell-sub">{{ optional($p->category)->name ?: 'Tanpa kategori' }}</span>
                        </div>
                        <h6 class="cell-title mb-1">{{ $p->title }}</h6>
                        <p class="cell-sub mb-2">{{ $p->excerpt ?: '—' }}</p>
                        <small class="d-block text-muted">Oleh {{ optional($p->author)->name ?: '—' }} · {{ $p->published_at ? $p->published_at->format('d M Y') : ($p->created_at ? $p->created_at->format('d M Y') : '—') }}</small>
                        <div class="mt-3 d-flex gap-2">
                            <a class="btn btn-ghost btn-sm" href="/admin/blog-posts/{{ $p->id }}/edit"><i class="bi bi-pencil"></i>Edit</a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12"><p class="text-center text-muted py-4 mb-0">Belum ada artikel blog. Tulis artikel pertama lewat Filament.</p></div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  if (!window.Chart) return;
  Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
  new Chart(document.getElementById('blogCat'), {type:'doughnut',
    data:{labels:@json($catLabels),datasets:[{data:@json($catCounts),backgroundColor:['#4F46E5','#06B6D4','#22C55E','#F59E0B','#EF4444','#94A3B8']}]},
    options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
