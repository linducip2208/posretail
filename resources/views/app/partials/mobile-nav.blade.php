    <div class="offcanvas offcanvas-start mobile-nav" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
        <div class="offcanvas-header">
            <a class="topbar-brand" href="/app" id="mobileNavLabel">
                <span class="brand-mark"><img src="/app-assets/images/logo-mark.svg" alt="" width="32" height="32"></span>
                <span class="brand-text"><span class="brand-name">POS Retail</span><span class="brand-sub">Retail OS</span></span>
            </a>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="p-3 border-bottom">
                <a class="mobile-user" href="/app/profile">
                    <span class="profile-avatar"><img src="/app-assets/images/avatars/avatar-11.svg" alt=""><span class="presence"></span></span>
                    <span class="flex-grow-1"><strong>{{ auth()->user()?->name ?? 'Kasir' }}</strong><small>{{ ucfirst(auth()->user()?->role ?? 'staff') }}</small></span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <div class="dropdown store-selector mt-3 d-md-none">
                    <button class="btn btn-light w-100 justify-content-between" id="mobileStore" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-label="Current store">
                        <span><i class="bi bi-shop me-2"></i><span data-store-label>{{ \App\Models\Outlet::where('active', true)->value('name') ?? 'Semua Outlet' }}</span></span><i class="bi bi-chevron-expand"></i>
                    </button>
                    <ul class="dropdown-menu w-100">
                        @foreach(\App\Models\Outlet::where('active', true)->limit(6)->get() as $mo)
                        <li><a class="dropdown-item" href="/app">{{ $mo->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="accordion accordion-flush" id="mobileNavAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-dashboard" aria-expanded="true" aria-controls="mnav-dashboard">
                            <span class="dd-icon"><i class="bi bi-grid-1x2"></i></span>Dashboard
                        </button>
                    </h2>
                    <div id="mnav-dashboard" class="accordion-collapse collapse show" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <a class="mobile-nav-link active" href="/app"><i class="bi bi-speedometer2"></i>Retail dashboard</a>
                            <a class="mobile-nav-link" href="/app/sales-overview"><i class="bi bi-graph-up-arrow"></i>Sales overview</a>
                            <a class="mobile-nav-link" href="/app/store-performance"><i class="bi bi-shop"></i>Store performance</a>
                            <a class="mobile-nav-link" href="/app/business-overview"><i class="bi bi-briefcase"></i>Business overview</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-pos" aria-expanded="false" aria-controls="mnav-pos">
                            <span class="dd-icon"><i class="bi bi-upc-scan"></i></span>POS
                        </button>
                    </h2>
                    <div id="mnav-pos" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <div class="mega-title">Checkout</div>
                            <a class="mobile-nav-link" href="/app/pos"><i class="bi bi-upc-scan"></i>Checkout terminal</a>
                            <a class="mobile-nav-link" href="/app/pos-product-search"><i class="bi bi-search"></i>Product lookup</a>
                            <a class="mobile-nav-link" href="/app/pos-cart"><i class="bi bi-cart3"></i>Cart & order</a>
                            <a class="mobile-nav-link" href="/app/pos-payment"><i class="bi bi-credit-card-2-front"></i>Payment</a>
                            <div class="mega-title">After sale</div>
                            <a class="mobile-nav-link" href="/app/pos-receipt"><i class="bi bi-receipt-cutoff"></i>Receipt</a>
                            <a class="mobile-nav-link" href="/app/pos-refunds"><i class="bi bi-arrow-return-left"></i>Refunds & returns</a>
                            <a class="mobile-nav-link" href="/app/pos-hold-orders"><i class="bi bi-pause-circle"></i>Held orders</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-sales" aria-expanded="false" aria-controls="mnav-sales">
                            <span class="dd-icon"><i class="bi bi-receipt"></i></span>Sales
                        </button>
                    </h2>
                    <div id="mnav-sales" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <div class="mega-title">Transactions</div>
                            <a class="mobile-nav-link" href="/app/sales-list"><i class="bi bi-list-ul"></i>Transactions</a>
                            <a class="mobile-nav-link" href="/app/sale-details"><i class="bi bi-file-earmark-text"></i>Sale details</a>
                            <a class="mobile-nav-link" href="/app/sales-create"><i class="bi bi-plus-square"></i>New sales order</a>
                            <div class="mega-title">Billing</div>
                            <a class="mobile-nav-link" href="/app/sales-returns"><i class="bi bi-arrow-counterclockwise"></i>Returns</a>
                            <a class="mobile-nav-link" href="/app/sales-invoices"><i class="bi bi-file-earmark-ruled"></i>Invoices</a>
                            <a class="mobile-nav-link" href="/app/sales-payments"><i class="bi bi-wallet2"></i>Payments</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-catalog" aria-expanded="false" aria-controls="mnav-catalog">
                            <span class="dd-icon"><i class="bi bi-box-seam"></i></span>Catalog
                        </button>
                    </h2>
                    <div id="mnav-catalog" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <div class="mega-title">Products</div>
                            <a class="mobile-nav-link" href="/app/products-list"><i class="bi bi-box-seam"></i>All products</a>
                            <a class="mobile-nav-link" href="/app/product-details"><i class="bi bi-card-text"></i>Product details</a>
                            <a class="mobile-nav-link" href="/app/product-create"><i class="bi bi-plus-circle"></i>Add product</a>
                            <a class="mobile-nav-link" href="/app/product-categories"><i class="bi bi-tags"></i>Categories</a>
                            <a class="mobile-nav-link" href="/app/product-variants"><i class="bi bi-palette"></i>Variants</a>
                            <a class="mobile-nav-link" href="/app/product-pricing"><i class="bi bi-currency-dollar"></i>Pricing</a>
                            <div class="mega-title">Inventory</div>
                            <a class="mobile-nav-link" href="/app/inventory-overview"><i class="bi bi-boxes"></i>Inventory overview</a>
                            <a class="mobile-nav-link" href="/app/stock-levels"><i class="bi bi-bar-chart-steps"></i>Stock levels</a>
                            <a class="mobile-nav-link" href="/app/stock-adjustments"><i class="bi bi-sliders"></i>Adjustments</a>
                            <a class="mobile-nav-link" href="/app/stock-transfers"><i class="bi bi-arrow-left-right"></i>Transfers</a>
                            <a class="mobile-nav-link" href="/app/low-stock-alerts"><i class="bi bi-exclamation-triangle"></i>Low-stock alerts</a>
                            <a class="mobile-nav-link" href="/app/inventory-history"><i class="bi bi-clock-history"></i>Movement history</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-purchasing" aria-expanded="false" aria-controls="mnav-purchasing">
                            <span class="dd-icon"><i class="bi bi-truck"></i></span>Purchasing
                        </button>
                    </h2>
                    <div id="mnav-purchasing" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <div class="mega-title">Purchase orders</div>
                            <a class="mobile-nav-link" href="/app/purchase-orders"><i class="bi bi-clipboard-check"></i>Purchase orders</a>
                            <a class="mobile-nav-link" href="/app/purchase-order-details"><i class="bi bi-clipboard-data"></i>PO details</a>
                            <a class="mobile-nav-link" href="/app/purchase-create"><i class="bi bi-clipboard-plus"></i>Create PO</a>
                            <div class="mega-title">Suppliers</div>
                            <a class="mobile-nav-link" href="/app/suppliers-list"><i class="bi bi-buildings"></i>Supplier directory</a>
                            <a class="mobile-nav-link" href="/app/supplier-details"><i class="bi bi-building"></i>Supplier profile</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-people" aria-expanded="false" aria-controls="mnav-people">
                            <span class="dd-icon"><i class="bi bi-people"></i></span>People
                        </button>
                    </h2>
                    <div id="mnav-people" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <div class="mega-title">Customers</div>
                            <a class="mobile-nav-link" href="/app/customers-list"><i class="bi bi-person-lines-fill"></i>Customers</a>
                            <a class="mobile-nav-link" href="/app/customer-details"><i class="bi bi-person-badge"></i>Customer profile</a>
                            <a class="mobile-nav-link" href="/app/customer-create"><i class="bi bi-person-plus"></i>Add customer</a>
                            <a class="mobile-nav-link" href="/app/loyalty-program"><i class="bi bi-gem"></i>Loyalty program</a>
                            <a class="mobile-nav-link" href="/app/customer-groups"><i class="bi bi-diagram-3"></i>Customer groups</a>
                            <div class="mega-title">Team & registers</div>
                            <a class="mobile-nav-link" href="/app/employees-list"><i class="bi bi-person-workspace"></i>Employees</a>
                            <a class="mobile-nav-link" href="/app/employee-details"><i class="bi bi-person-vcard"></i>Employee profile</a>
                            <a class="mobile-nav-link" href="/app/registers-list"><i class="bi bi-pc-display"></i>Registers</a>
                            <a class="mobile-nav-link" href="/app/cash-management"><i class="bi bi-cash-stack"></i>Cash management</a>
                            <a class="mobile-nav-link" href="/app/shift-management"><i class="bi bi-calendar-week"></i>Shifts</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-reports" aria-expanded="false" aria-controls="mnav-reports">
                            <span class="dd-icon"><i class="bi bi-pie-chart"></i></span>Reports
                        </button>
                    </h2>
                    <div id="mnav-reports" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <a class="mobile-nav-link" href="/app/reports-overview"><i class="bi bi-pie-chart"></i>Reports overview</a>
                            <a class="mobile-nav-link" href="/app/sales-reports"><i class="bi bi-graph-up"></i>Sales reports</a>
                            <a class="mobile-nav-link" href="/app/product-performance"><i class="bi bi-trophy"></i>Product performance</a>
                            <a class="mobile-nav-link" href="/app/inventory-reports"><i class="bi bi-clipboard-pulse"></i>Inventory reports</a>
                            <a class="mobile-nav-link" href="/app/profit-margins"><i class="bi bi-percent"></i>Profit & margins</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-marketing" aria-expanded="false" aria-controls="mnav-marketing">
                            <span class="dd-icon"><i class="bi bi-megaphone"></i></span>Marketing
                        </button>
                    </h2>
                    <div id="mnav-marketing" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <a class="mobile-nav-link" href="/app/discounts"><i class="bi bi-percent"></i>Discounts</a>
                            <a class="mobile-nav-link" href="/app/coupons"><i class="bi bi-ticket-perforated"></i>Coupons</a>
                            <a class="mobile-nav-link" href="/app/promotions"><i class="bi bi-megaphone"></i>Campaigns</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-tools" aria-expanded="false" aria-controls="mnav-tools">
                            <span class="dd-icon"><i class="bi bi-tools"></i></span>Tools
                        </button>
                    </h2>
                    <div id="mnav-tools" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <div class="mega-title">Penjualan &amp; Kasir</div>
                            <a class="mobile-nav-link" href="/app/marketplace-orders"><i class="bi bi-bag"></i>Marketplace orders</a>
                            <a class="mobile-nav-link" href="/app/installments"><i class="bi bi-credit-card-2-front"></i>Cicilan</a>
                            <a class="mobile-nav-link" href="/app/customer-deposits"><i class="bi bi-piggy-bank"></i>Deposit pelanggan</a>
                            <a class="mobile-nav-link" href="/app/attendances"><i class="bi bi-fingerprint"></i>Absensi</a>
                            <div class="mega-title">Keuangan &amp; Akuntansi</div>
                            <a class="mobile-nav-link" href="/app/accounts"><i class="bi bi-journal-bookmark"></i>Akun perkiraan</a>
                            <a class="mobile-nav-link" href="/app/journal-entries"><i class="bi bi-journal-text"></i>Jurnal umum</a>
                            <a class="mobile-nav-link" href="/app/expenses"><i class="bi bi-wallet2"></i>Pengeluaran</a>
                            <a class="mobile-nav-link" href="/app/budgets"><i class="bi bi-pie-chart"></i>Anggaran</a>
                            <a class="mobile-nav-link" href="/app/company-assets"><i class="bi bi-building"></i>Aset perusahaan</a>
                            <a class="mobile-nav-link" href="/app/bank-statements"><i class="bi bi-bank"></i>Rekonsiliasi bank</a>
                            <a class="mobile-nav-link" href="/app/exchange-rates"><i class="bi bi-currency-exchange"></i>Kurs mata uang</a>
                            <div class="mega-title">Produksi &amp; Utilitas</div>
                            <a class="mobile-nav-link" href="/app/consignments"><i class="bi bi-boxes"></i>Konsinyasi</a>
                            <a class="mobile-nav-link" href="/app/purchase-requisitions"><i class="bi bi-clipboard-plus"></i>Permintaan pembelian</a>
                            <a class="mobile-nav-link" href="/app/supplier-contracts"><i class="bi bi-file-earmark-text"></i>Kontrak supplier</a>
                            <a class="mobile-nav-link" href="/app/supplier-ratings"><i class="bi bi-star"></i>Rating supplier</a>
                            <a class="mobile-nav-link" href="/app/raw-materials"><i class="bi bi-basket"></i>Bahan baku</a>
                            <a class="mobile-nav-link" href="/app/assembly-orders"><i class="bi bi-tools"></i>Perakitan</a>
                            <a class="mobile-nav-link" href="/app/bin-locations"><i class="bi bi-grid-1x2"></i>Lokasi bin</a>
                            <a class="mobile-nav-link" href="/app/serial-numbers"><i class="bi bi-upc"></i>Nomor seri</a>
                            <a class="mobile-nav-link" href="/app/unit-conversions"><i class="bi bi-arrow-left-right"></i>Konversi satuan</a>
                            <a class="mobile-nav-link" href="/app/providers"><i class="bi bi-plug"></i>Payment gateway</a>
                            <a class="mobile-nav-link" href="/app/cetak-label"><i class="bi bi-printer"></i>Cetak label</a>
                            <a class="mobile-nav-link" href="/app/stock-take-mobile"><i class="bi bi-phone"></i>Stok opname HP</a>
                            <a class="mobile-nav-link" href="/app/broadcast-wa"><i class="bi bi-whatsapp"></i>Broadcast WA</a>
                            <a class="mobile-nav-link" href="/app/notifications"><i class="bi bi-bell"></i>Notifikasi</a>
                            <a class="mobile-nav-link" href="/app/blog-posts"><i class="bi bi-newspaper"></i>Artikel blog</a>
                            <a class="mobile-nav-link" href="/app/audit-logs"><i class="bi bi-shield-check"></i>Audit log</a>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mnav-admin" aria-expanded="false" aria-controls="mnav-admin">
                            <span class="dd-icon"><i class="bi bi-gear"></i></span>Admin
                        </button>
                    </h2>
                    <div id="mnav-admin" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                        <div class="accordion-body">
                            <div class="mega-title">Administration</div>
                            <a class="mobile-nav-link" href="/app/stores-list"><i class="bi bi-shop-window"></i>Stores & branches</a>
                            <a class="mobile-nav-link" href="/app/users-roles"><i class="bi bi-shield-lock"></i>Users & roles</a>
                            <div class="mega-title">Settings</div>
                            <a class="mobile-nav-link" href="/app/settings-general"><i class="bi bi-sliders2"></i>General settings</a>
                            <a class="mobile-nav-link" href="/app/settings-appearance"><i class="bi bi-palette2"></i>Appearance</a>
                            <a class="mobile-nav-link" href="/app/profile"><i class="bi bi-person-circle"></i>My profile</a>
                            <a class="mobile-nav-link" href="/app"><i class="bi bi-box-arrow-in-right"></i>Sign-in screen</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
