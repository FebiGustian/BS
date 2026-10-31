<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <title>Selamat Datang - BINTANG SILVER</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #ffffff; font-family: Arial, sans-serif; color: #17243b; padding: 20px; }
        .card { width: 100%; max-width: 460px; text-align: center; padding: 48px 36px; border: 1px solid #e7ecf3; border-radius: 16px; animation: muncul .7s ease both; }
        .card img { width: 96px; height: 96px; border-radius: 50%; margin-bottom: 24px; }
        .sapaan { font-size: 13px; color: #8491a5; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
        h1 { font-size: 26px; margin-bottom: 14px; }
        .nama { font-size: 16px; margin-bottom: 6px; }
        .cabang { font-size: 13px; color: #8491a5; margin-bottom: 32px; }
        .btn { display: inline-block; padding: 12px 36px; background: #17243b; color: #fff; border-radius: 8px; text-decoration: none; font-size: 14px; }
        .btn:hover { background: #263d62; }
        @keyframes muncul { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    </style>
</head>
<body>
@php
    $jam = now()->hour;
    $waktu = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
    $cabangAktif = \App\Models\Cabang::find(session('cabang_id'));
@endphp
    <div class="card">
        <img src="{{ asset('images/logo.png') }}" alt="">
        <p class="sapaan">{{ $waktu }}</p>
        <h1>Selamat datang di Bintang Silver</h1>
        <p class="nama">{{ auth()->user()->name }}</p>
        <p class="cabang">Anda masuk di cabang {{ $cabangAktif->nama_cabang ?? '-' }}</p>
        <a class="btn" href="{{ route('dashboard') }}">Lanjut</a>
    </div>
</body>
</html>
