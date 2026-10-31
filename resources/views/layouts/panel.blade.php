<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <title>@yield('title', 'Bintang Silver') | Bintang Silver</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f3f6fc; color: #17243b; }
        .sidebar { position: fixed; width: 250px; height: 100vh; background: #101d35; color: white; padding: 30px 20px; overflow-y: auto; }
        .logo { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: bold; letter-spacing: 2px; margin-bottom: 45px; }
        .logo img { width: 38px; height: 38px; border-radius: 50%; background: #fff; }
        .logo span span { color: #4b8bff; }
        .label { color: #8292ad; font-size: 11px; margin: 25px 10px 12px; }
        .menu a { display: block; padding: 14px; color: #c4cee0; text-decoration: none; border-radius: 8px; font-size: 13px; margin-bottom: 5px; }
        .menu a:hover, .menu a.active { background: #263d62; color: white; }
        .main { margin-left: 250px; padding: 35px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 16px; }
        .header h1 { font-size: 26px; }
        .header p { color: #8290a5; font-size: 13px; margin-top: 7px; }
        .admin { display: flex; align-items: center; gap: 14px; background: white; padding: 10px 14px 10px 20px; border-radius: 10px; font-size: 13px; }
        .admin button { padding: 8px 14px; border: 0; border-radius: 8px; background: #101d35; color: #fff; cursor: pointer; }
        .welcome { background: linear-gradient(110deg,#193665,#3478f6); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        .welcome p { color: #d2e1ff; margin-top: 10px; font-size: 13px; }
        .title { margin-bottom: 17px; font-size: 17px; }
        .cards { display: grid; grid-template-columns: repeat(4,1fr); gap: 18px; margin-bottom: 30px; }
        .card, .panel { background: white; padding: 24px; border-radius: 13px; border: 1px solid #e9eef5; }
        .card p { color: #8491a5; font-size: 12px; margin-bottom: 15px; }
        .card h2 { font-size: 24px; }
        .card small { display: block; color: #8996a8; margin-top: 10px; }
        .content { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; }
        .panel h3 { margin-bottom: 20px; }
        .panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .panel-head h3 { margin: 0; }
        .muted { color: #8491a5; font-size: 12px; margin-bottom: 6px; }
        .chart { height: 180px; display: flex; align-items: flex-end; justify-content: space-around; border-bottom: 1px solid #e7ecf3; }
        .grp { height: 100%; display: flex; align-items: flex-end; gap: 4px; }
        .bar { width: 14px; min-height: 2px; background: #3478f6; border-radius: 6px 6px 0 0; }
        .bar.out { background: #8ac4ff; }
        .months { display: flex; justify-content: space-around; margin-top: 12px; color: #8996a8; font-size: 11px; }
        .legend { display: flex; gap: 16px; font-size: 12px; color: #8491a5; margin-bottom: 12px; }
        .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; background: #3478f6; }
        .dot.out { background: #8ac4ff; }
        .activity { padding: 17px 0; border-bottom: 1px solid #edf0f5; font-size: 13px; }
        .activity a { color: inherit; text-decoration: none; }
        .activity small { display: block; color: #8996a8; margin-top: 7px; }
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 18px; }
        .alert.ok { background: #dcfce7; color: #166534; }
        .alert.err { background: #fee2e2; color: #991b1b; }
        .btn { display: inline-block; padding: 10px 16px; border: 0; border-radius: 8px; background: #101d35; color: #fff; font-size: 13px; text-decoration: none; cursor: pointer; }
        .btn-secondary { background: #e8edf5; color: #17243b; }
        .btn-danger { background: #dc2626; }
        .btn-x { border: 0; background: #fee2e2; color: #991b1b; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; font-size: 16px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; color: #8491a5; font-size: 11px; font-weight: 600; padding: 10px 12px; border-bottom: 1px solid #e7ecf3; }
        td { padding: 12px; border-bottom: 1px solid #edf0f5; vertical-align: middle; }
        .num { text-align: right; }
        .field { margin-bottom: 14px; }
        .field label { display: block; font-size: 12px; color: #5b6b82; margin-bottom: 6px; }
        input[type=text], input[type=date], input[type=number], select, textarea { width: 100%; padding: 10px 12px; border: 1px solid #d6dde8; border-radius: 8px; font-size: 13px; font-family: inherit; background: #fff; }
        .grid3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }
        .pagination { display: flex; gap: 6px; list-style: none; margin-top: 16px; }
        .pagination li a, .pagination li span { display: block; padding: 6px 11px; border: 1px solid #e1e7f0; border-radius: 6px; font-size: 13px; color: #17243b; text-decoration: none; }
        .pagination .active span { background: #101d35; color: #fff; }
        .pagination .disabled span { color: #aab4c5; }
        @media(max-width:1000px) { .cards { grid-template-columns: repeat(2,1fr); } .content, .grid3 { grid-template-columns: 1fr; } }
        @media(max-width:600px) { .sidebar { width: 75px; padding: 20px 7px; } .logo { font-size: 12px; letter-spacing: 0; } .logo img, .logo span { display: none; } .label { display: none; } .menu a { font-size: 10px; padding: 12px 5px; } .main { margin-left: 75px; padding: 15px; } .cards { gap: 9px; } .card { padding: 13px; } .card h2 { font-size: 17px; } .header { flex-direction: column; align-items: flex-start; } }
            /* TEMA-PUTIH-MULAI */
        body { background: #ffffff; color: #17243b; }
        .sidebar { background: #ffffff; color: #17243b; border-right: 1px solid #e7ecf3; }
        .logo, .logo span span { color: #17243b; }
        .label { color: #8491a5; }
        .menu a { color: #4b5a70; }
        .menu a:hover, .menu a.active { background: #f1f4f9; color: #101d35; }
        .welcome { background: #ffffff; color: #17243b; border: 1px solid #e7ecf3; }
        .welcome p { color: #8491a5; }
        .card, .panel, .admin { border: 1px solid #e7ecf3; box-shadow: none; }
        .btn, .admin button { background: #17243b; }
        .btn-secondary { background: #eef1f6; color: #17243b; }
        /* TEMA-PUTIH-SELESAI */
    </style>
</head>
<body>
@php $cabangAktif = \App\Models\Cabang::find(session('cabang_id')); @endphp
<aside class="sidebar">
    <div class="logo">
        <img src="{{ asset('images/logo.png') }}" alt="">
        <span>BINTANG <span>SILVER</span></span>
    </div>
    <div class="label">MAIN MENU</div>
    <nav class="menu">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">▦ &nbsp; Dashboard</a>
        <a href="{{ route('barang.index') }}">▤ &nbsp; Products</a>
        <a href="{{ route('kategori.index') }}">▧ &nbsp; Categories</a>
        <a href="#">♙ &nbsp; Suppliers</a>
    </nav>
    <div class="label">INVENTORY</div>
    <nav class="menu">
        <a href="{{ route('stok.index', 'masuk') }}" class="{{ request()->routeIs('stok.*') && request()->route('jenis') === 'masuk' ? 'active' : '' }}">↓ &nbsp; Stock In</a>
        <a href="{{ route('stok.index', 'keluar') }}" class="{{ request()->routeIs('stok.*') && request()->route('jenis') === 'keluar' ? 'active' : '' }}">↑ &nbsp; Stock Out</a>
        <a href="{{ route('penyesuaian.index') }}" class="{{ request()->routeIs('penyesuaian.*') ? 'active' : '' }}">⇄ &nbsp; Adjustments</a>
        <a href="#">▣ &nbsp; Barcode / QR</a>
    </nav>
    <div class="label">CASH MANAGEMENT</div>
    <nav class="menu">
        <a href="{{ route('kas.index') }}" class="{{ request()->routeIs('kas.*') ? 'active' : '' }}">▤ &nbsp; Manajemen Kas</a>
        <a href="{{ route('kas.create', ['jenis' => 'income']) }}">↓ &nbsp; Pemasukan Kas</a>
        <a href="{{ route('kas.create', ['jenis' => 'expense']) }}">↑ &nbsp; Pengeluaran Kas</a>
        <a href="{{ route('kas.create', ['jenis' => 'transfer_in']) }}">⇄ &nbsp; Transfer Pusat-Cabang</a>
    </nav>
    <div class="label">ACCOUNTING</div>
    <nav class="menu">
        <a href="{{ route('accounts.index') }}" class="{{ request()->routeIs('accounts.*') ? 'active' : '' }}">▤ &nbsp; Chart of Accounts</a>
        <a href="{{ route('jurnal.index') }}" class="{{ request()->routeIs('jurnal.*') ? 'active' : '' }}">▤ &nbsp; Jurnal Umum</a>
        <a href="{{ route('buku-besar.index') }}" class="{{ request()->routeIs('buku-besar.*') ? 'active' : '' }}">▤ &nbsp; Buku Besar</a>
    </nav>
    <div class="label">ANALYTICS</div>
    <nav class="menu">
        <a href="#">▥ &nbsp; Reports</a>
        @if (auth()->user()->role === 'admin')
        <a href="{{ route('activity-logs.index') }}" class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">◷ &nbsp; Activity Logs</a>
        @endif
    </nav>
</aside>
<main class="main">
    <header class="header">
        <div>
            <h1>@yield('judul')</h1>
            <p>@yield('subjudul') · {{ $cabangAktif->nama_cabang ?? '-' }}</p>
        </div>
        <div class="admin">
            <span>{{ auth()->user()->name }} · {{ $cabangAktif->nama_cabang ?? '-' }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </div>
    </header>

    @if (session('success'))
        <div class="alert ok">{{ session('success') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
