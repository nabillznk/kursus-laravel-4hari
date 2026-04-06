{{--
    LAYOUT UTAMA — Sistem Pengurusan Zakat Kedah
    Simpan di: resources/views/layouts/app.blade.php
--}}

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Zakat Kedah')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; color: #333; }

        /* Header / Navigasi */
        header { background: #1B5E20; color: white; }
        .header-top {
            background: #0D3B0E; padding: 0.5rem 2rem;
            font-size: 0.8rem; text-align: right;
        }
        nav { padding: 0.8rem 2rem; display: flex; align-items: center; gap: 2rem; }
        .nav-brand {
            font-size: 1.2rem; font-weight: 700; color: white;
            text-decoration: none; white-space: nowrap;
        }
        .nav-brand small { font-weight: 400; font-size: 0.75rem; display: block; opacity: 0.8; }
        .nav-links { display: flex; gap: 1rem; }
        .nav-links a {
            color: rgba(255,255,255,0.9); text-decoration: none;
            padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.9rem;
        }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.15); color: white; }

        /* Kandungan utama */
        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }

        /* Tajuk halaman */
        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem;
        }
        .page-header h1 { font-size: 1.5rem; color: #1B5E20; }

        /* Mesej kejayaan & ralat */
        .alert {
            padding: 0.75rem 1rem; border-radius: 4px;
            margin-bottom: 1rem; font-size: 0.9rem;
        }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger  { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-info    { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }

        /* Butang */
        .btn {
            display: inline-block; padding: 0.5rem 1rem;
            border: none; border-radius: 4px; cursor: pointer;
            text-decoration: none; font-size: 0.9rem; font-weight: 500;
        }
        .btn-primary  { background: #1B5E20; color: white; }
        .btn-primary:hover { background: #145218; }
        .btn-danger   { background: #C62828; color: white; }
        .btn-danger:hover  { background: #A52020; }
        .btn-success  { background: #2E7D32; color: white; }
        .btn-warning  { background: #F9A825; color: #333; }
        .btn-sm       { padding: 0.3rem 0.6rem; font-size: 0.8rem; }

        /* Jadual */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 6px; overflow: hidden; }
        th { background: #1B5E20; color: white; padding: 0.7rem 0.8rem; text-align: left; font-size: 0.85rem; }
        td { padding: 0.6rem 0.8rem; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        tr:hover { background: #f9f9f9; }

        /* Borang */
        .form-card { background: white; padding: 1.5rem; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.3rem; font-weight: 600; font-size: 0.9rem; }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%; padding: 0.5rem; border: 1px solid #ccc;
            border-radius: 4px; font-size: 1rem;
        }
        .form-group textarea { min-height: 80px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .error { color: #C62828; font-size: 0.8rem; margin-top: 0.2rem; }

        /* Kad maklumat */
        .info-card {
            background: white; padding: 1.5rem; border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1rem;
        }
        .info-card h3 { color: #1B5E20; margin-bottom: 0.8rem; }
        .info-row { display: flex; padding: 0.4rem 0; border-bottom: 1px solid #f0f0f0; }
        .info-label { width: 180px; font-weight: 600; color: #666; font-size: 0.9rem; }
        .info-value { flex: 1; font-size: 0.9rem; }

        /* Dashboard statistik */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card {
            background: white; padding: 1.2rem; border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center;
            border-top: 3px solid #1B5E20;
        }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #1B5E20; }
        .stat-card .label { font-size: 0.85rem; color: #666; margin-top: 0.3rem; }

        /* Carian */
        .search-bar {
            display: flex; gap: 0.5rem; margin-bottom: 1rem;
        }
        .search-bar input { flex: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }

        /* Pagination */
        .pagination { display: flex; gap: 0.3rem; justify-content: center; margin-top: 1rem; }
        .pagination a, .pagination span {
            padding: 0.4rem 0.8rem; border: 1px solid #ddd;
            border-radius: 4px; text-decoration: none; font-size: 0.85rem;
        }
        .pagination span.current { background: #1B5E20; color: white; border-color: #1B5E20; }

        /* Footer */
        footer {
            text-align: center; padding: 2rem; color: #999;
            font-size: 0.8rem; margin-top: 2rem; border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <header>
        <div class="header-top">
            @auth
                Selamat datang, {{ Auth::user()->name }} |
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:white;cursor:pointer;font-size:0.8rem;">Log Keluar</button>
                </form>
            @endauth
        </div>
        <nav>
            <a href="{{ url('/') }}" class="nav-brand">
                Pusat Zakat Negeri Kedah
                <small>Sistem Pengurusan Zakat</small>
            </a>
            <div class="nav-links">
                <a href="{{ url('/dashboard') }}">Dashboard</a>
                <a href="{{ route('pembayar.index') }}">Pembayar</a>
                <a href="{{ route('jenis-zakat.index') }}">Jenis Zakat</a>
                <a href="{{ route('pembayaran.index') }}">Pembayaran</a>
            </div>
        </nav>
    </header>

    {{-- Kandungan utama --}}
    <div class="container">
        {{-- Mesej kejayaan (flash message) --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Mesej ralat --}}
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    {{-- Footer --}}
    <footer>
        &copy; {{ date('Y') }} Pusat Zakat Negeri Kedah — Sistem Pengurusan Zakat v1.0
    </footer>
</body>
</html>
