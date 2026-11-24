@extends('app.layout')
@section('title','Appearance Settings')

@section('content')


                
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/stores-list">Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Appearance</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Appearance</h1>
                        <p class="page-subtitle">Theme, colours, typography and layout. Changes apply instantly and are saved in this browser.</p>
                    </div>
                    <div class="page-actions">
                        <button type="button" class="btn btn-light" data-cz-reset><i class="bi bi-arrow-counterclockwise"></i>Reset to default</button>
                    </div>
                </div>


                <div class="alert-strip is-info mb-4">
                    <span class="icon-box icon-sm tone-primary"><i class="bi bi-palette"></i></span>
                    <div class="flex-grow-1 small"><strong class="text-heading">Preferensi tersimpan di browser ini</strong> (localStorage <code>posretail.theme</code>) — bukan di database, jadi tiap perangkat bisa punya tema sendiri. <a href="/docs">Lihat dokumentasi</a>.</div>
                </div>


                
                <div class="row g-app">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h2 class="card-title">Theme options</h2>            <p class="card-subtitle">Same controls as the quick customizer in the topbar</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="cz-section">
                                    <div class="cz-title"><span><i class="bi bi-circle-half"></i>Color mode</span></div>
                                    <div class="cz-options">
                                        <button type="button" class="cz-card" data-cz-mode="light">
                                            <span class="cz-thumb thumb-light"><span></span><span></span><span></span></span>
                                            <span class="cz-card-label"><i class="bi bi-sun"></i>Light</span>
                                        </button>
                                        <button type="button" class="cz-card" data-cz-mode="dark">
                                            <span class="cz-thumb thumb-dark"><span></span><span></span><span></span></span>
                                            <span class="cz-card-label"><i class="bi bi-moon-stars"></i>Dark</span>
                                        </button>
                                        <button type="button" class="cz-card" data-cz-mode="system">
                                            <span class="cz-thumb thumb-system"><span></span><span></span><span></span></span>
                                            <span class="cz-card-label"><i class="bi bi-display"></i>System</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="cz-section">
                                    <div class="cz-title"><span><i class="bi bi-droplet-half"></i>Accent color</span><small>Buttons, links, charts</small></div>
                                    <div class="cz-swatches">
                                        <button type="button" class="cz-swatch" style="--swatch: #4f46e5;" data-cz-preset="#4f46e5" aria-label="Indigo accent" title="Indigo"><span class="visually-hidden">Indigo</span></button>
                                        <button type="button" class="cz-swatch" style="--swatch: #2563eb;" data-cz-preset="#2563eb" aria-label="Royal blue accent" title="Royal blue"><span class="visually-hidden">Royal blue</span></button>
                                        <button type="button" class="cz-swatch" style="--swatch: #0891b2;" data-cz-preset="#0891b2" aria-label="Ocean accent" title="Ocean"><span class="visually-hidden">Ocean</span></button>
                                        <button type="button" class="cz-swatch" style="--swatch: #0d9488;" data-cz-preset="#0d9488" aria-label="Teal accent" title="Teal"><span class="visually-hidden">Teal</span></button>
                                        <button type="button" class="cz-swatch" style="--swatch: #7c3aed;" data-cz-preset="#7c3aed" aria-label="Violet accent" title="Violet"><span class="visually-hidden">Violet</span></button>
                                        <button type="button" class="cz-swatch" style="--swatch: #e11d48;" data-cz-preset="#e11d48" aria-label="Rose accent" title="Rose"><span class="visually-hidden">Rose</span></button>
                                        <button type="button" class="cz-swatch" style="--swatch: #ea580c;" data-cz-preset="#ea580c" aria-label="Amber accent" title="Amber"><span class="visually-hidden">Amber</span></button>
                                        <label class="cz-custom" title="Custom color">
                                            <input type="color" class="cz-color-input" value="#4f46e5" data-cz-custom aria-label="Custom accent color">
                                            <span>Custom</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="cz-section">
                                    <div class="cz-title"><span><i class="bi bi-window-desktop"></i>Header style</span></div>
                                    <div class="cz-options">
                                        <button type="button" class="cz-card" data-cz-header="navy">
                                            <span class="cz-thumb header-navy"><span></span><span></span><span></span></span>
                                            <span class="cz-card-label">Navy</span>
                                        </button>
                                        <button type="button" class="cz-card" data-cz-header="midnight">
                                            <span class="cz-thumb header-midnight"><span></span><span></span><span></span></span>
                                            <span class="cz-card-label">Midnight</span>
                                        </button>
                                        <button type="button" class="cz-card" data-cz-header="aurora">
                                            <span class="cz-thumb header-aurora"><span></span><span></span><span></span></span>
                                            <span class="cz-card-label">Aurora</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="cz-section">
                                    <div class="cz-title"><span><i class="bi bi-fonts"></i>Typography</span></div>
                                    <div class="cz-options cols-2">
                                        <button type="button" class="cz-card cz-font" data-cz-font="inter"><span class="cz-aa" style="font-family: 'Plus Jakarta Sans', sans-serif;">Aa</span><span class="cz-card-label">Jakarta + Inter</span></button>
                                        <button type="button" class="cz-card cz-font" data-cz-font="jakarta"><span class="cz-aa" style="font-family: 'Plus Jakarta Sans', sans-serif;">Ag</span><span class="cz-card-label">Jakarta</span></button>
                                        <button type="button" class="cz-card cz-font" data-cz-font="manrope"><span class="cz-aa" style="font-family: Manrope, sans-serif;">Aa</span><span class="cz-card-label">Manrope</span></button>
                                        <button type="button" class="cz-card cz-font" data-cz-font="system"><span class="cz-aa" style="font-family: system-ui, sans-serif;">Aa</span><span class="cz-card-label">System UI</span></button>
                                    </div>
                                </div>

                                <div class="cz-section">
                                    <div class="cz-title"><span><i class="bi bi-layout-text-window"></i>Density</span></div>
                                    <div class="cz-segment">
                                        <button type="button" class="cz-seg" data-cz-density="comfortable"><i class="bi bi-distribute-vertical"></i>Comfortable</button>
                                        <button type="button" class="cz-seg" data-cz-density="compact"><i class="bi bi-list"></i>Compact</button>
                                    </div>
                                </div>

                                <div class="cz-section">
                                    <div class="cz-title"><span><i class="bi bi-arrows"></i>Content width</span></div>
                                    <div class="cz-options">
                                        <button type="button" class="cz-card" data-cz-width="boxed"><span class="cz-thumb width-boxed"><span></span></span><span class="cz-card-label">Boxed</span></button>
                                        <button type="button" class="cz-card" data-cz-width="fluid"><span class="cz-thumb width-fluid"><span></span></span><span class="cz-card-label">Wide</span></button>
                                        <button type="button" class="cz-card" data-cz-width="full"><span class="cz-thumb width-full"><span></span></span><span class="cz-card-label">Full</span></button>
                                    </div>
                                </div>

                                <div class="cz-section">
                                    <div class="cz-title"><span><i class="bi bi-bounding-box-circles"></i>Corner radius</span></div>
                                    <div class="cz-options">
                                        <button type="button" class="cz-card" data-cz-radius="sharp"><span class="cz-shape" style="border-radius: 3px;"></span><span class="cz-card-label">Sharp</span></button>
                                        <button type="button" class="cz-card" data-cz-radius="default"><span class="cz-shape" style="border-radius: 9px;"></span><span class="cz-card-label">Soft</span></button>
                                        <button type="button" class="cz-card" data-cz-radius="round"><span class="cz-shape" style="border-radius: 15px;"></span><span class="cz-card-label">Round</span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h2 class="card-title">Live preview</h2>            <p class="card-subtitle">Reflects your current choices</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="theme-preview mb-4">
                                    <div class="tp-top"></div>
                                    <div class="tp-nav"></div>
                                    <div class="tp-body">
                                        <div class="tp-card"></div>
                                        <div class="tp-card"></div>
                                        <div class="tp-card"></div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <button type="button" class="btn btn-primary">Primary</button>
                                    <button type="button" class="btn btn-light">Secondary</button>
                                    <button type="button" class="btn btn-soft-primary">Soft</button>
                                    <span class="status-badge status-success">Paid</span>
                                    <span class="status-badge status-warning">Low stock</span>
                                </div>
                                <div class="input-icon mb-3"><i class="bi bi-search"></i><input type="search" class="form-control" placeholder="Form control preview" aria-label="Preview input"></div>
                                <div class="progress"><div class="progress-bar" style="width: 64%;"></div></div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    <span class="icon-box tone-primary"><i class="bi bi-code-slash"></i></span>
                                    <div>
                                        <h3 class="h6 mb-1">For developers</h3>
                                        <p class="small text-muted mb-2">Preferences are stored under <code>posretail.theme</code> in LocalStorage and applied as <code>data-*</code> attributes on <code>&lt;html&gt;</code>. Design tokens section di bawah (file CSS skin).</p>
                                        <a class="btn btn-light btn-sm" href="/docs"><i class="bi bi-book"></i>Dokumentasi tampilan</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            
@endsection
