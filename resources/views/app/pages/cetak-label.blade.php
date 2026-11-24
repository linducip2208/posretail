@extends('app.layout')
@section('title','Cetak Label Produk')

@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Catalog</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Cetak label</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Cetak Label Produk</h1>
                        <p class="page-subtitle">Cari produk lalu cetak label barcode satu per satu.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/products-list"><i class="bi bi-box"></i>Products</a>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="/app/cetak-label" class="table-toolbar">
                            <div class="input-icon">
                                <i class="bi bi-search"></i>
                                <input type="search" name="q" class="form-control" placeholder="Cari nama, SKU, atau barcode…" value="{{ $q }}" aria-label="Cari nama, SKU, atau barcode…">
                            </div>
                            <div class="toolbar-end"><button type="submit" class="btn btn-light"><i class="bi bi-search"></i>Cari</button></div>
                        </form>
                    </div>
                </div>

                <div class="row g-app" id="labelGrid">
                    @forelse($products as $p)
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card label-card h-100">
                            <div class="card-body text-center">
                                <div class="fw-semibold">{{ $p->name }}</div>
                                <div class="text-muted small font-mono">{{ $p->sku }}</div>
                                <img src="/barcode/{{ $p->barcode }}" alt="Barcode {{ $p->sku }}" class="img-fluid my-2" loading="lazy">
                                <div class="font-mono small">{{ $p->barcode }}</div>
                                <div class="fw-bold mt-1">{{ tillora_rp($p->selling_price) }}</div>
                                <button type="button" class="btn btn-primary btn-sm w-100 mt-2 btn-print-label" data-name="{{ $p->name }}" data-sku="{{ $p->sku }}" data-barcode="{{ $p->barcode }}" data-price="{{ tillora_rp($p->selling_price) }}"><i class="bi bi-printer"></i>Print</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="card"><div class="card-body text-center text-muted py-4">Tidak ada produk yang cocok dengan pencarian.</div></div>
                    </div>
                    @endforelse
                </div>

                <div id="printArea" class="d-none">
                    <div class="print-label">
                        <div class="print-label-name" id="printName"></div>
                        <img id="printBarcode" src="" alt="Barcode">
                        <div class="print-label-code" id="printCode"></div>
                        <div class="print-label-price" id="printPrice"></div>
                    </div>
                </div>

                <style>
                    .print-label { width: 58mm; padding: 4mm; text-align: center; font-family: Arial, sans-serif; }
                    .print-label-name { font-size: 12pt; font-weight: bold; margin-bottom: 2mm; }
                    .print-label img { max-width: 100%; height: auto; }
                    .print-label-code { font-size: 9pt; margin-top: 1mm; }
                    .print-label-price { font-size: 13pt; font-weight: bold; margin-top: 1mm; }
                    @media print {
                        body * { visibility: hidden; }
                        #printArea, #printArea * { visibility: visible; }
                        #printArea { display: block; position: absolute; left: 0; top: 0; width: 100%; }
                    }
                </style>

                <script>
                (function(){
                    function printLabel(btn) {
                        document.getElementById('printName').textContent = btn.getAttribute('data-name');
                        document.getElementById('printCode').textContent = btn.getAttribute('data-sku') + ' / ' + btn.getAttribute('data-barcode');
                        document.getElementById('printPrice').textContent = btn.getAttribute('data-price');
                        document.getElementById('printBarcode').src = '/barcode/' + btn.getAttribute('data-barcode');
                        document.getElementById('printBarcode').alt = 'Barcode ' + btn.getAttribute('data-sku');
                        window.print();
                    }
                    document.querySelectorAll('.btn-print-label').forEach(function(btn){
                        btn.addEventListener('click', function(){ printLabel(btn); });
                    });
                })();
                </script>
@endsection
