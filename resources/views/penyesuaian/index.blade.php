@extends('layouts.panel')

@section('title', 'Adjustments')
@section('judul', 'Adjustments')
@section('subjudul', 'Koreksi stok cabang')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>Riwayat Penyesuaian Stok</h3>
        <a class="btn" href="{{ route('penyesuaian.create') }}">+ Tambah Penyesuaian</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Barang</th>
                    <th class="num">Stok sistem</th>
                    <th class="num">Stok fisik</th>
                    <th class="num">Selisih</th>
                    <th>Alasan</th>
                    <th>Dicatat oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($penyesuaians as $p)
                    <tr>
                        <td>{{ date('d/m/Y', strtotime($p->tanggal)) }}</td>
                        <td>{{ $p->barang->nama_barang ?? '-' }}</td>
                        <td class="num">{{ $p->stok_sistem }}</td>
                        <td class="num">{{ $p->stok_fisik }}</td>
                        <td class="num" style="font-weight:700;color:{{ $p->selisih > 0 ? '#166534' : '#991b1b' }}">{{ $p->selisih > 0 ? '+' : '' }}{{ $p->selisih }}</td>
                        <td>{{ $p->alasan }}@if ($p->catatan)<br><small style="color:#8491a5">{{ $p->catatan }}</small>@endif</td>
                        <td>{{ $p->user->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:#8491a5;padding:30px">Belum ada penyesuaian stok di cabang ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($penyesuaians->hasPages())
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;font-size:13px">
            @if ($penyesuaians->onFirstPage())
                <span style="color:#aab4c5">‹ Sebelumnya</span>
            @else
                <a href="{{ $penyesuaians->previousPageUrl() }}" style="color:#17243b;text-decoration:none">‹ Sebelumnya</a>
            @endif

            <span style="color:#8491a5">Halaman {{ $penyesuaians->currentPage() }} dari {{ $penyesuaians->lastPage() }}</span>

            @if ($penyesuaians->hasMorePages())
                <a href="{{ $penyesuaians->nextPageUrl() }}" style="color:#17243b;text-decoration:none">Berikutnya ›</a>
            @else
                <span style="color:#aab4c5">Berikutnya ›</span>
            @endif
        </div>
    @endif
</div>
@endsection
