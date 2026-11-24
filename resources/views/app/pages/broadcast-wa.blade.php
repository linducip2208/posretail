@extends('app.layout')
@section('title','Broadcast WhatsApp')

@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/customers-list">Customers</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Broadcast WA</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Broadcast WhatsApp</h1>
                        <p class="page-subtitle">Kirim pesan ke customer aktif yang memiliki nomor telepon.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/customers-list"><i class="bi bi-people"></i>Customers</a>
                    </div>
                </div>

                @if($errors->any())
                <div class="alert-strip danger mb-3"><i class="bi bi-exclamation-triangle"></i><div><strong>Pesan gagal dikirim.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
                @endif
                @if(session('success'))
                <div class="alert-strip success mb-3"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="alert-strip danger mb-3"><i class="bi bi-exclamation-triangle"></i>{{ session('error') }}</div>
                @endif

                <div class="row g-app">
                    <div class="col-xl-4">
                        <div class="card kpi-card grad-emerald mb-4">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Estimasi penerima</div><div class="kpi-value">{{ number_format($totalRecipients) }}</div></div><span class="icon-box tone-success"><i class="bi bi-whatsapp"></i></span></div>
                            <div class="kpi-foot"><span>customer aktif ber-telepon</span></div>
                        </div>
                        <div class="card">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Penerima per grup</h2></div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    @forelse($groupOptions as $g)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $g['name'] }}</span>
                                        <span class="badge bg-primary rounded-pill">{{ number_format($g['count']) }}</span>
                                    </li>
                                    @empty
                                    <li class="list-group-item text-muted">Belum ada grup customer.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="{{ route('tillora.broadcast.send') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="bwGroup">Grup customer</label>
                                        <select name="customer_group_id" id="bwGroup" class="form-select @error('customer_group_id') is-invalid @enderror" onchange="window.location='/app/broadcast-wa?customer_group_id='+this.value">
                                            <option value="">Semua customer ({{ number_format($totalRecipients) }} penerima)</option>
                                            @foreach($groupOptions as $g)
                                            <option value="{{ $g['id'] }}" @selected((string)old('customer_group_id', $selectedGroupId) === (string)$g['id'])>{{ $g['name'] }} ({{ number_format($g['count']) }} penerima)</option>
                                            @endforeach
                                        </select>
                                        @error('customer_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-text">Memilih grup akan memuat ulang estimasi penerima di bawah.</div>
                                    </div>
                                    @if($selectedGroupId !== '')
                                    <div class="alert alert-info">Estimasi penerima untuk grup terpilih: <strong>{{ number_format($selectedRecipients) }} customer</strong>.</div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label" for="bwMessage">Pesan *</label>
                                        <textarea name="message" id="bwMessage" rows="5" maxlength="1000" required class="form-control @error('message') is-invalid @enderror" placeholder="Tulis pesan broadcast…">{{ old('message') }}</textarea>
                                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-text">Maksimal 1000 karakter.</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-send"></i>Kirim broadcast</button>
                                </form>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="bi bi-clock-history"></i>
                                    Pengiriman berjalan sinkron satu per satu — untuk grup besar prosesnya butuh waktu, mohon jangan menutup halaman selama pengiriman berlangsung.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
@endsection
