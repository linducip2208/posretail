        <footer class="app-footer">
            <div class="app-container d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="d-flex align-items-center gap-2"><img src="{{ asset('assets/posretail/01-logo-posretail.png') }}" alt="" width="20" height="20">© {{ date('Y') }} {{ config('app.name','POS Retail') }} · Point of sale &amp; retail operations</span>
                <span class="d-flex gap-3">
                    <a href="/docs">Documentation</a>
                    <a href="/app/settings-general">Settings</a>
                    <a href="#" data-toast="Our support team would reply here in a real deployment." data-toast-title="Support" data-toast-tone="info">Support</a>
                    <span class="footer-status"><span class="live-dot"></span>All systems normal</span>
                </span>
            </div>
        </footer>
