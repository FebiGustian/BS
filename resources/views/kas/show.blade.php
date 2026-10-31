@extends('layouts.panel')

@section('title', 'Detail Transaksi Kas')
@section('judul', 'Detail Transaksi Kas')
@section('subjudul', 'Rincian transaksi dan jurnal otomatis')

@section('content')
<style>
.kas-detail{color:#253047}
.kas-detail-card{background:#fff;border:1px solid #e7ebf1;border-radius:12px;padding:20px;margin-bottom:18px}
.kas-detail table{width:100%;min-width:650px;border-collapse:collapse}
.kas-detail th,.kas-detail td{padding:12px;border-bottom:1px solid #edf0f4;text-align:left;font-size:13px}
.kas-detail th{background:#f7f9fc;color:#667085}
.kas-detail .scroll{overflow-x:auto}
.kas-detail a,.kas-detail button{display:inline-block;padding:10px 15px;background:#2457d6;color:white;border:0;border-radius:7px;text-decoration:none;cursor:pointer}
.kas-muted{font-size:12px;color:#788399}
@media print{.sidebar,.header,.no-print{display:none!important}.main{margin:0!important}}
</style>

<div class="kas-detail">
    @if(session('success'))
        <div class="kas-detail-card" style="color:#147a43">{{ session('success') }}</div>
    @endif

    <div class="no-print" style="display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap">
        <a href="{{ route('kas.index') }}">← Kembali</a>
        <button onclick="window.print()">Cetak</button>
        @if($cashTransaction->jurnal)
            <a href="{{ route('jurnal.show', $cashTransaction->jurnal) }}">Lihat Jurnal Umum</a>
        @endif
    </div>

    <div class="kas-detail-card">
        <h3 style="margin-top:0">Informasi Transaksi</h3>
        <p><strong>Nomor:</strong> {{ $cashTransaction->nomor_transaksi }}</p>
        <p><strong>Tanggal:</strong> {{ $cashTransaction->tanggal->format('d/m/Y') }}</p>
        <p><strong>Jenis:</strong>
            @switch($cashTransaction->jenis)
                @case('income') Pemasukan Toko @break
                @case('expense') Pengeluaran Toko @break
                @case('transfer_in') Transfer Pusat ke Cabang @break
                @case('transfer_out') Transfer Cabang ke Pusat @break
            @endswitch
        </p>
        <p><strong>Keterangan:</strong> {{ $cashTransaction->deskripsi }}</p>
        <p><strong>Nominal:</strong> Rp {{ number_format($cashTransaction->nominal, 0, ',', '.') }}</p>
        <p><strong>Akun Kategori:</strong> {{ $cashTransaction->account?->code }} {{ $cashTransaction->account?->name }}</p>
        <p><strong>Akun Kas/Bank:</strong> {{ $cashTransaction->cashAccount?->code }} {{ $cashTransaction->cashAccount?->name }}</p>
        <p><strong>Nomor Jurnal:</strong> {{ $cashTransaction->jurnal?->nomor_jurnal ?? '-' }}</p>
        <p><strong>Dicatat oleh:</strong> {{ $cashTransaction->user?->name ?? '-' }}</p>

        @if($cashTransaction->bukti_path)
            <p><strong>Bukti Transaksi:</strong>
                <a target="_blank" href="{{ asset('storage/' . $cashTransaction->bukti_path) }}">Lihat / Unduh Bukti</a>
            </p>
        @else
            <p class="kas-muted">Tidak ada bukti yang dilampirkan.</p>
        @endif
    </div>

    @if($cashTransaction->jurnal)
        <div class="kas-detail-card">
            <h3 style="margin-top:0">Jurnal yang Dibuat Otomatis</h3>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cashTransaction->jurnal->details as $detail)
                            <tr>
                                <td>{{ $detail->account->code }}</td>
                                <td>{{ $detail->account->name }}</td>
                                <td>Rp {{ number_format($detail->debit, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->kredit, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
