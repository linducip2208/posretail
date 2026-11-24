@extends('app.layout')
@section('title','New Sales Order')

@section('content')


                
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">New sales order</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">New sales order</h1>
                        <p class="page-subtitle">Create a manual order for phone, B2B or special-order customers.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/sales-list">Cancel</a>
                        <button type="submit" form="salesOrderForm" class="btn btn-primary"><i class="bi bi-check2"></i>Create order</button>
                    </div>
                </div>


                
                <form id="salesOrderForm" data-validate data-success="Sales order SO-20418 created (demo)." data-success-title="Order created" data-redirect="sales-list.html">
                    <div class="row g-app">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header">
                                    <div>
                                        <h2 class="card-title">Order details</h2>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="soCustomer">Customer</label>
                                            <select class="form-select" id="soCustomer" required><option value="">Select customer…</option><option>Olivia Bennett</option><option>Marcus Hale</option><option>Sofia Alvarez</option><option>Ethan Clarke</option><option>Amara Nwosu</option><option>Liam Foster</option><option>Hannah Kim</option><option>Noah Patel</option><option>Chloe Martin</option><option>Gabriel Santos</option><option>Isabella Rossi</option><option>Jacob Turner</option></select>
                                            <div class="invalid-feedback">Choose the customer for this order.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="soStore">Fulfilling store</label>
                                            <select class="form-select" id="soStore" required><option selected>Downtown Flagship</option><option>Westfield Mall</option><option>Riverside Market</option><option>Airport Kiosk</option></select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="soDate">Order date</label>
                                            <input type="date" class="form-control" id="soDate" value="2026-11-17" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="soChannel">Channel</label>
                                            <select class="form-select" id="soChannel"><option>Phone order</option><option>B2B account</option><option>Special order</option><option>Click &amp; collect</option></select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="soRef">Customer reference</label>
                                            <input type="text" class="form-control" id="soRef" placeholder="Optional PO number">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="card table-card" data-line-builder="price" data-tax-rate="8.5" data-seed="P1005:1,P1006:2,P1007:2">
                                    <div class="card-header">
                                        <div>
                                            <h2 class="card-title">Order lines</h2>
                                            <p class="card-subtitle" data-line-count>0 lines</p>
                                        </div>
                                    </div>
                                    <div class="table-toolbar">
                                        <select class="form-select flex-grow-1" style="max-width: 420px;" data-line-product aria-label="Product"></select>
                                        <input type="number" class="form-control" style="width: 100px;" min="1" value="1" data-line-qty aria-label="Quantity">
                                        <button type="button" class="btn btn-primary" data-line-add><i class="bi bi-plus-lg"></i>Add line</button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="table-num">Unit price</th>
                                                    <th>Qty</th>
                                                    <th class="table-num">Line total</th>
                                                    <th class="col-actions"><span class="visually-hidden">Remove</span></th>
                                                </tr>
                                            </thead>
                                            <tbody data-line-body></tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-4"><label class="form-label" for="soShip">Shipping</label><div class="input-group"><span class="input-group-text">$</span><input type="number" class="form-control" id="soShip" min="0" step="0.01" value="0" data-line-shipping></div></div>
                                            <div class="col-md-4"><label class="form-label" for="soDisc">Order discount</label><div class="input-group"><span class="input-group-text">$</span><input type="number" class="form-control" id="soDisc" min="0" step="0.01" value="0" data-line-discount></div></div>
                                            <div class="col-md ms-auto" style="max-width: 320px;">
                                                <div class="summary-row"><span>Subtotal</span><span data-line-subtotal>$0.00</span></div>
                                                <div class="summary-row"><span>Tax (8.5%)</span><span data-line-tax>$0.00</span></div>
                                                <div class="summary-total"><span>Total</span><strong data-line-total>$0.00</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-header">
                                    <div>
                                        <h2 class="card-title">Payment &amp; delivery</h2>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <label class="form-label" for="soTerms">Payment terms</label>
                                    <select class="form-select mb-3" id="soTerms"><option>Pay on collection</option><option>Paid in advance</option><option>Net 15</option><option>Net 30</option></select>
                                    <label class="form-label" for="soDelivery">Delivery method</label>
                                    <select class="form-select mb-3" id="soDelivery"><option>Store pickup</option><option>Local courier</option><option>Standard shipping</option></select>
                                    <label class="form-label" for="soNotes">Notes</label>
                                    <textarea class="form-control mb-3" id="soNotes" rows="4" placeholder="Gift wrap, delivery window, special instructions…"></textarea>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="soReserve" checked>
                                        <label class="form-check-label" for="soReserve">Reserve stock immediately</label>
                                    </div>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="soEmail">
                                        <label class="form-check-label" for="soEmail">Email confirmation to customer</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            
@endsection
