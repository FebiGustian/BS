@extends('layouts.panel')

@section('title', 'Activity Logs')
@section('judul', 'Activity Logs')
@section('subjudul', 'Riwayat login pengguna')

@section('content')
@php
    $teks = ['login' => 'Login', 'logout' => 'Logout', 'gagal' => 'Gagal login', 'ditolak' => 'Ditolak (cabang salah)'];
    $warna = [
        'login' => ['#dcfce7', '#166534'],
        'logout' => ['#e8edf5', '#475569'],
        'gagal' => ['#fee2e2', '#991b1b'],
        'ditolak' => ['#ffedd5', '#9a3412'],
    ];
@endphp

<section class="cards" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
    <div class="card"><p>LOGIN HARI INI</p><h2>{{ $loginHariIni }}</h2><small>Login berhasil</small></div>
    <div class="card"><p>PENGGUNA HARI INI</p><h2>{{ $penggunaHariIni }}</h2><small>Pengguna berbeda yang login</small></div>
    <div class="card"><p>PERANGKAT BARU</p><h2>{{ $baruHariIni }}</h2><small>Login dari perangkat baru hari ini</small></div>
    <div class="card"><p>LOGIN BERMASALAH</p><h2>{{ $gagalHariIni }}</h2><small>Gagal atau ditolak hari ini</small></div>
</section>

<div class="panel">
    <form method="GET" action="{{ route('activity-logs.index') }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-bottom:18px">
        <div class="field" style="margin:0;min-width:190px">
            <label for="aksi">Aksi</label>
            <select id="aksi" name="aksi">
                <option value="">Semua</option>
                @foreach ($teks as $k => $t)
                    <option value="{{ $k }}" @selected(request('aksi') === $k)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="margin:0;min-width:210px">
            <label for="cabang_id">Cabang</label>
            <select id="cabang_id" name="cabang_id">
                <option value="">Semua cabang</option>
                @foreach ($cabangs as $c)
                    <option value="{{ $c->id }}" @selected((string) request('cabang_id') === (string) $c->id)>{{ $c->nama_cabang }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn">Terapkan</button>
        <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Cabang</th>
                    <th>Aksi</th>
                    <th>Alamat IP</th>
                    <th>Perangkat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $l)
                    @php $nama = $l->device_hash ? ($namaPerangkat[$l->device_hash] ?? null) : null; @endphp
                    <tr>
                        <td>{{ $l->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            {{ $l->user->name ?? ($l->email ?: '-') }}
                            @if ($l->user && $l->email)
                                <br><small style="color:#8491a5">{{ $l->email }}</small>
                            @elseif (! $l->user && $l->email)
                                <br><small style="color:#8491a5">akun tidak dikenali</small>
                            @endif
                        </td>
                        <td>{{ $l->cabang->nama_cabang ?? '-' }}</td>
                        <td>
                            <span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:{{ $warna[$l->aksi][0] ?? '#e8edf5' }};color:{{ $warna[$l->aksi][1] ?? '#475569' }}">
                                {{ $teks[$l->aksi] ?? $l->aksi }}
                            </span>
                        </td>
                        <td>{{ $l->ip_address ?: '-' }}</td>
                        <td>
                            @if ($nama)
                                <strong>{{ $nama }}</strong><br>
                            @endif
                            <small style="color:#8491a5">{{ trim(($l->jenis_perangkat ? $l->jenis_perangkat . ' · ' : '') . ($l->perangkat ?: '-')) }}</small>
                            @if ($l->perangkat_baru && ! $nama)
                                <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;font-size:11px;background:#fef9c3;color:#854d0e">Perangkat baru</span>
                            @endif
                            @if ($l->device_hash)
                                <details style="margin-top:6px">
                                    <summary style="cursor:pointer;color:#3478f6;font-size:12px">{{ $nama ? 'Ubah nama' : 'Beri nama' }}</summary>
                                    <form method="POST" action="{{ route('activity-logs.namai') }}" style="display:flex;gap:6px;margin-top:6px">
                                        @csrf
                                        <input type="hidden" name="device_hash" value="{{ $l->device_hash }}">
                                        <input type="text" name="nama" value="{{ $nama }}" maxlength="60" placeholder="Contoh: MacBook Febi" style="min-width:150px">
                                        <button type="submit" class="btn">Simpan</button>
                                    </form>
                                </details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:#8491a5;padding:30px">Belum ada aktivitas yang tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($logs->hasPages())
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;font-size:13px">
            @if ($logs->onFirstPage())
                <span style="color:#aab4c5">‹ Sebelumnya</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}" style="color:#17243b;text-decoration:none">‹ Sebelumnya</a>
            @endif

            <span style="color:#8491a5">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>

            @if ($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" style="color:#17243b;text-decoration:none">Berikutnya ›</a>
            @else
                <span style="color:#aab4c5">Berikutnya ›</span>
            @endif
        </div>
    @endif
</div>
@endsection
