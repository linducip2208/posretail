

<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/app') }}"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/app') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Retail dashboard</li>
        </ol></nav>
        <h1 class="page-title">Retail dashboard</h1>
        <p class="page-subtitle">Live trading hari ini — {{ $today->translatedFormat('l, d F Y') }} · {{ $todayCount }} transaksi.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="{{ url('/export/laporan/penjualan') }}"><i class="bi bi-download"></i>Export</a>
        <a class="btn btn-primary" href="{{ url('/pos') }}"><i class="bi bi-upc-scan"></i>Buka kasir</a>
    </div>
</div>

<div class="card hero-card welcome-card mb-4">
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-5">
                <span class="welcome-chip"><span class="live-dot"></span>Register open · shift berjalan</span>
                <h2 class="welcome-title">{{ $greeting }}, {{ explode(' ', trim($user?->name ?? 'Kasir'))[0] }}</h2>
                <p class="mb-3">Penjualan hari ini <strong class="text-white">{{ $pctRev >= 0 ? '+' : '' }}{{ number_format($pctRev,1) }}%</strong> vs kemarin ({{ tillora_rp($yRevenue) }}). Jam ramai sekitar <strong class="text-white">{{ $peakLabel }}</strong>.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-primary" href="{{ url('/pos') }}"><i class="bi bi-upc-scan"></i>Transaksi baru</a>
                    <a class="btn btn-light" href="{{ url('/app/low-stock-alerts') }}"><i class="bi bi-exclamation-triangle"></i>Cek stok</a>
                    <a class="btn btn-light" href="{{ url('/app/shift-management') }}"><i class="bi bi-calendar-week"></i>Shift saya</a>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="welcome-stats">
                    <div class="welcome-stat"><span>Target harian</span><strong>{{ tillora_rp($todayRevenue) }} <small class="fw-normal opacity-75">/ {{ tillora_rp($dailyTarget) }}</small></strong><div class="progress"><div class="progress-bar" style="width: {{ round($dailyPct) }}%;"></div></div></div>
                    <div class="welcome-stat"><span>Pelanggan dilayani</span><strong>{{ $customersToday }}</strong><div class="progress"><div class="progress-bar" style="width: {{ min(100, $customersToday) }}%;"></div></div></div>
                    <div class="welcome-stat"><span>Item per struk</span><strong>{{ number_format($itemsPerBasket,1) }}</strong><div class="progress"><div class="progress-bar" style="width: {{ min(100, $itemsPerBasket*25) }}%;"></div></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Pendapatan hari ini</div><div class="kpi-value">{{ tillora_rp($todayRevenue) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-currency-dollar"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $pctRev>=0?'is-up':'is-down' }}"><i class="bi bi-arrow-{{ $pctRev>=0?'up':'down' }}-right"></i>{{ number_format(abs($pctRev),1) }}%</span><span>vs kemarin</span></div>
            <div class="kpi-spark"><canvas id="sparkRev"></canvas></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Transaksi</div><div class="kpi-value">{{ $todayCount }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-receipt"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $pctCount>=0?'is-up':'is-down' }}"><i class="bi bi-arrow-{{ $pctCount>=0?'up':'down' }}-right"></i>{{ number_format(abs($pctCount),1) }}%</span><span>vs kemarin</span></div>
            <div class="kpi-spark"><canvas id="sparkCount"></canvas></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Rata-rata struk</div><div class="kpi-value">{{ tillora_rp($avgBasket) }}</div></div><span class="icon-box tone-success"><i class="bi bi-basket"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta is-up"><i class="bi bi-arrow-up-right"></i>{{ number_format($itemsPerBasket,1) }}</span><span>item per struk</span></div>
            <div class="kpi-spark"><canvas id="sparkAvg"></canvas></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Gross margin</div><div class="kpi-value">{{ number_format($margin,1) }}%</div></div><span class="icon-box tone-warning"><i class="bi bi-percent"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta is-up"><i class="bi bi-arrow-up-right"></i>live</span><span>pendapatan − HPP</span></div>
            <div class="kpi-spark"><canvas id="sparkMargin"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Tren pendapatan</h2><p class="card-subtitle">30 hari terakhir vs 30 hari sebelumnya · data asli</p></div></div>
            <div class="card-body"><div class="chart-box chart-lg"><canvas id="dashRevenue"></canvas></div>
            <div class="chart-legend"><span><i class="legend-dot" style="background: var(--app-primary,#0d6efd);"></i>Periode ini</span><span><i class="legend-dot" style="background: #94A3B8;"></i>Periode lalu</span></div></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Penjualan per toko</h2><p class="card-subtitle">Porsi pendapatan hari ini</p></div></div>
            <div class="card-body">
                <div class="chart-box chart-donut"><canvas id="salesByOutlet"></canvas></div>
                <ul class="legend-list mt-3">
                    @forelse($byOutlet as $i => $r)
                    <li><span><i class="legend-dot" style="background: {{ ['#0d6efd','#06B6D4','#10B981','#F59E0B','#8b5cf6'][$i % 5] }};"></i>{{ $r->outlet?->name ?? 'Outlet #'.$r->outlet_id }}</span><strong>{{ tillora_rp($r->total) }}</strong></li>
                    @empty
                    <li class="text-muted">Belum ada penjualan hari ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-xxl-8">
        <div class="card table-card h-100">
            <div class="card-header"><div><h2 class="card-title">Transaksi terbaru</h2><p class="card-subtitle">Penjualan terakhir semua kasir</p></div>
            <div class="d-flex gap-2"><a class="btn btn-primary btn-sm" href="{{ url('/app/sales-list') }}">Lihat semua<i class="bi bi-arrow-right"></i></a></div></div>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Struk</th><th>Pelanggan</th><th class="table-num">Item</th><th class="table-num">Total</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($recent as $o)
                    <tr>
                        <td><div class="cell-receipt"><span class="receipt-icon"><i class="bi bi-receipt"></i></span><div><span class="table-id">{{ $o->order_number }}</span><span class="cell-sub">{{ $o->created_at->format('d M · H:i') }}</span></div></div></td>
                        <td><div class="cell-user"><span class="avatar tone-{{ $o->customer ? 'primary' : 'neutral' }}">{{ strtoupper(substr($o->customer?->name ?? 'WL',0,2)) }}</span><div><span class="cell-title">{{ $o->customer?->name ?? 'Walk-in' }}</span><span class="cell-sub">{{ $o->outlet?->name ?? '-' }}</span></div></div></td>
                        <td class="table-num text-muted">{{ $o->order_items_count }}</td>
                        <td class="table-num fw-semibold">{{ tillora_rp($o->total_amount) }}</td>
                        <td><span class="status-badge {{ $o->order_status==='completed'?'status-success':($o->order_status==='cancelled'?'status-danger':'status-info') }}">{{ $o->order_status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-xxl-4">
        <div class="card h-100 grad-neutral">
            <div class="card-header"><div><h2 class="card-title">Peringatan stok</h2><p class="card-subtitle">Diurutkan paling kritis</p></div>
            <div><a class="btn btn-light btn-sm" href="{{ url('/app/products-list') }}">Semua<i class="bi bi-arrow-right"></i></a></div></div>
            <div class="card-body">
                <div class="stock-summary">
                    <div class="sum-out"><span>Habis</span><strong>{{ $outOfStock }}</strong></div>
                    <div class="sum-critical"><span>Kritis</span><strong>{{ $criticalCount }}</strong></div>
                    <div class="sum-low"><span>Rendah</span><strong>{{ $lowStock->count() }}</strong></div>
                </div>
                <ul class="stock-alerts">
                    @forelse($lowStock as $p)
                    <li class="stock-alert {{ $p->current_stock<=0?'sev-out':($p->current_stock <= ($p->min_stock/2) ? 'sev-critical':'sev-low') }}">
                        <span class="sa-thumb">@php $img = $p->image; $imgUrl = $img ? (str_starts_with($img,'http') ? $img : (str_starts_with($img,'storage/') ? asset($img) : asset('storage/'.$img))) : asset('app-assets/images/products/mug.svg'); @endphp<img src="{{ $imgUrl }}" onerror="this.onerror=null;this.src='{{ asset('app-assets/images/products/mug.svg') }}'" alt=""></span>
                        <div class="min-w-0">
                            <div class="sa-top"><span class="sa-name" title="{{ $p->name }}">{{ $p->name }}</span></div>
                            <div class="sa-meta">{{ $p->sku }} · {{ $p->category?->name ?? 'Tanpa kategori' }}</div>
                            <div class="sa-level"><span class="sev-pill">{{ $p->current_stock<=0?'Habis':($p->current_stock <= ($p->min_stock/2)?'Kritis':'Rendah') }}</span><span><strong>{{ $p->current_stock }}</strong> / {{ $p->min_stock }} min</span></div>
                        </div>
                        <a class="btn btn-reorder btn-sm" href="{{ url('/admin/purchase-orders/create') }}"><i class="bi bi-cart-plus"></i>Reorder</a>
                    </li>
                    @empty
                    <li class="text-muted">Stok aman.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 grad-indigo">
            <div class="card-header"><div><h2 class="card-title">Produk terlaris minggu ini</h2><p class="card-subtitle">Berdasarkan unit terjual</p></div>
            <div><a class="btn btn-light btn-sm" href="{{ url('/app/products-list') }}">Detail</a></div></div>
            <div class="card-body">
                @forelse($topProducts as $i => $t)
                <div class="rank-bar">
                    <span class="d-flex align-items-center gap-2 min-w-0"><span class="rank-no">{{ $i+1 }}</span><span class="text-truncate fw-semibold text-heading">{{ $t->product?->name ?? 'Produk #'.$t->product_id }}</span></span>
                    <span class="text-muted">{{ $t->qty }} terjual</span>
                    <div class="progress"><div class="progress-bar" style="width: {{ round($t->qty/$maxQty*100) }}%;"></div></div>
                </div>
                @empty
                <p class="text-muted">Belum ada penjualan minggu ini.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Penjualan per jam</h2><p class="card-subtitle">Hari ini, semua kasir (08–21)</p></div></div>
            <div class="card-body">
                <div class="chart-box chart-sm"><canvas id="hourlySales"></canvas></div>
                <div class="alert-strip is-info mt-3 py-2"><i class="bi bi-lightning-charge text-warning-app"></i><span class="small flex-grow-1">Puncak jam <strong class="text-heading">{{ $peakLabel }}</strong> — pertimbangkan kasir kedua.</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100 grad-lavender">
            <div class="card-header"><div><h2 class="card-title">Tim shift</h2><p class="card-subtitle">Yang sedang bertugas</p></div>
            <div><a class="btn btn-light btn-sm" href="{{ url('/app/shift-management') }}">Rota</a></div></div>
            <div class="card-body"><ul class="list-app">
                @foreach($shifts as $s)
                @php $u = is_object($s) && isset($s->user) && $s->user instanceof \App\Models\User ? $s->user : $s; @endphp
                <li><span class="avatar"><img src="{{ asset('app-assets/images/avatars/avatar-11.svg') }}" alt=""></span>
                    <div class="list-main"><span class="list-title">{{ $u->name ?? '-' }}</span><span class="list-sub">{{ ucfirst($u->role ?? 'staff') }}</span></div>
                    <div class="list-end"><span class="status-badge status-success">Aktif</span></div></li>
                @endforeach
            </ul></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    @foreach($storePerf as $sp)
    <div class="col-md-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <h3 class="card-title fs-6">{{ $sp->outlet->name }}</h3>
            <p class="card-subtitle">{{ $sp->outlet->code ?? '' }}</p>
            <div class="d-flex justify-content-between"><strong>{{ round($sp->pct) }}%</strong><span class="text-muted">{{ tillora_rp($sp->mtd) }}</span></div>
            <div class="progress mt-2"><div class="progress-bar" style="width: {{ min(100, round($sp->pct)) }}%;"></div></div>
            <small class="text-muted">Bulan ini · target {{ tillora_rp($sp->target) }}</small>
        </div></div>
    </div>
    @endforeach
</div>