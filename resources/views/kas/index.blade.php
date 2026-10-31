@extends('layouts.panel')

@section('title', 'Manajemen Kas')
@section('judul', 'Manajemen Kas')
@section('subjudul', 'Cash Management')

@section('content')
<style>
.kas-page{color:#253047}
.kas-card{background:#fff;border:1px solid #e7ebf1;border-radius:12px;padding:20px;margin-bottom:18px}
.kas-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.kas-stat{padding:15px;background:#f7f9fc;border-radius:9px}
.kas-stat small{color:#788399}
.kas-stat strong{display:block;font-size:18px;margin-top:8px}
.kas-toolbar{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
.kas-filter{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px}
.kas-page input,.kas-page select{padding:10px;border:1px solid #dce2ea;border-radius:7px}
.kas-btn{display:inline-block;padding:10px 14px;background:#2457d6;color:white;text-decoration:none;border:0;border-radius:7px;font-size:13px;cursor:pointer}
.kas-btn.gray{background:#667085}
.kas-table-scroll{overflow-x:auto;margin-top:18px}
.kas-page table{width:100%;min-width:850px;border-collapse:collapse}
.kas-page th,.kas-page td{padding:12px;border-bottom:1px solid #edf0f4;text-align:left;font-size:13px}
.kas-page th{background:#f7f9fc;color:#667085}
.kas-link{color:#2457d6;text-decoration:none;font-weight:700}
.kas-muted{color:#788399;font-size:12px}
.kas-pagination{margin-top:18px}
@media(max-width:900px){.kas-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:550px){.kas-stats{grid-template-columns:1fr}}
</style>

<div class="kas-page">
    @if(session('success'))
        <div class="kas-card" style="color:#147a43">{{ session('success') }}</div>
    @endif

    <div class="kas-card">
        <h3 style="margin-top:0">Ringkasan Transaksi Bulan Ini</h3>
        <div class="kas-stats">
            <div class="kas-stat">
                <small>Pemasukan Toko</small>
                <strong>Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</strong>
            </div>
            <div class="kas-stat">
                <small>Pengeluaran Toko</small>
                <strong>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong>
            </div>
            <div class="kas-stat">
                <small>Transfer Masuk</small>
                <strong>Rp {{ number_format($totalTransferMasuk, 0, ',', '.') }}</strong>
            </div>
            <div class="kas-stat">
                <small>Transfer Keluar</small>
                <strong>Rp {{ number_format($totalTransferKeluar, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <div class="kas-card">
        <div class="kas-toolbar">
            <div>
                <h3 style="margin:0 0 5px">Riwayat Transaksi Kas</h3>
                <div class="kas-muted">Semua transaksi pada cabang aktif.</div>
            </div>

            <a class="kas-btn" href="{{ route('kas.create', ['jenis' => 'income']) }}">+ Buat Transaksi</a>
        </div>

        <form method="GET" class="kas-filter">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nomor/keterangan...">
            <select name="jenis">
                <option value="">Semua Jenis</option>
                <option value="income" @selected(request('jenis') === 'income')>Pemasukan</option>
                <option value="expense" @selected(request('jenis') === 'expense')>Pengeluaran</option>
                <option value="transfer_in" @selected(request('jenis') === 'transfer_in')>Transfer Masuk</option>
                <option value="transfer_out" @selected(request('jenis') === 'transfer_out')>Transfer Keluar</option>
            </select>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <button class="kas-btn" type="submit">Filter</button>
            <a class="kas-btn gray" href="{{ route('kas.index') }}">Reset</a>
        </form>

        <div class="kas-table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor Transaksi</th>
                        <th>Jenis</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                        <th>Jurnal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $item)
                        <tr>
                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td><a class="kas-link" href="{{ route('kas.show', $item) }}">{{ $item->nomor_transaksi }}</a></td>
                            <td>
                                @switch($item->jenis)
                                    @case('income') Pemasukan @break
                                    @case('expense') Pengeluaran @break
                                    @case('transfer_in') Transfer Masuk @break
                                    @case('transfer_out') Transfer Keluar @break
                                @endswitch
                            </td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td>{{ $item->jurnal->nomor_jurnal ?? '-' }}</td>
                            <td><a href="{{ route('kas.show', $item) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;padding:30px">Belum ada transaksi kas pada cabang ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="kas-pagination">{{ $transaksi->links() }}</div>
    </div>
</div>
@endsection
