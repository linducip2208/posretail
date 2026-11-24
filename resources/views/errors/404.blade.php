<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman tidak ditemukan · {{ config('app.name', 'POS Retail') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('app-assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('app-assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <style>
        :root { --app-primary: #4F46E5; --app-navy: #0B1530; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #F2F5FB; font-family: Inter, system-ui, sans-serif; color: #172B4D; margin: 0; }
        .err-card { text-align: center; background: #fff; border-radius: 20px; padding: 3rem 3.5rem; box-shadow: 0 18px 50px -20px rgba(79,70,229,.35); border: 1px solid #E6EAF5; max-width: 480px; }
        .err-code { font-size: 5rem; font-weight: 800; line-height: 1; background: linear-gradient(135deg, #4F46E5, #06B6D4); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .btn-primary { background: #4F46E5; border: 0; border-radius: 10px; padding: .65rem 1.5rem; font-weight: 600; color: #fff; text-decoration: none; display: inline-block; }
        .btn-primary:hover { background: #4338CA; color: #fff; }
        .btn-lightx { border: 1px solid #E2E8F0; border-radius: 10px; padding: .65rem 1.5rem; font-weight: 600; color: #172B4D; text-decoration: none; display: inline-block; background: #fff; }
    </style>
</head>
<body>
    <div class="err-card">
        <div class="err-code">404</div>
        <h1 class="h4 mt-3 mb-2">Halaman tidak ditemukan</h1>
        <p class="text-muted mb-4">Alamat yang dibuka salah, datanya sudah dihapus, atau butuh parameter (mis. pilih dulu dari daftar).</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a class="btn-primary" href="{{ url('/app') }}"><i class="bi bi-speedometer2"></i> Kembali ke dashboard</a>
            <a class="btn-lightx" href="{{ url('/app/sales-list') }}"><i class="bi bi-receipt"></i> Daftar transaksi</a>
        </div>
    </div>
</body>
</html>
