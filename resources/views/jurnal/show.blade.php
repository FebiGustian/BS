@extends('layouts.panel')

@section('title', 'Detail Jurnal')
@section('judul', 'Detail Jurnal Umum')
@section('subjudul', 'Rincian pencatatan transaksi')

@section('content')
<style>
.journal-detail-page{color:#253047}
.journal-detail-card{background:#fff;border:1px solid #e7ebf1;border-radius:12px;padding:20px;margin-bottom:18px}
.journal-detail-page table{width:100%;border-collapse:collapse;min-width:650px}
.journal-detail-page th,.journal-detail-page td{padding:13px;border-bottom:1px solid #edf0f4;text-align:left;font-size:13px}
.journal-detail-page th{background:#f7f9fc;color:#667085}
.journal-detail-page .scroll{overflow-x:auto}
.journal-back{display:inline-block;padding:10px 15px;background:#667085;color:white;border-radius:7px;text-decoration:none}
@media print{.sidebar,.header,.journal-back{display:none!important}.main{margin:0!important}.journal-detail-card{border:0}}
</style>

<div class="journal-detail-page">
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:15px">
        <a href="{{ route('jurnal.index') }}" class="journal-back">← Kembali</a>
        <button onclick="window.print()" class="journal-back" style="border:0;cursor:pointer">Cetak</button>
    </div>

    <div class="journal-detail-card">
        <h3 style="margin-top:0">Informasi Jurnal</h3>
        <p><strong>Nomor:</strong> {{ $jurnal->nomor_jurnal }}</p>
        <p><strong>Tanggal:</strong> {{ $jurnal->tanggal->format('d/m/Y') }}</p>
        <p><strong>Keterangan:</strong> {{ $jurnal->deskripsi }}</p>
        <p><strong>Dibuat oleh:</strong> {{ $jurnal->user->name ?? '-' }}</p>
        <p><strong>Status:</strong> {{ strtoupper($jurnal->status) }}</p>
    </div>

    @php
        $totalDebit = $jurnal->details->sum('debit');
        $totalKredit = $jurnal->details->sum('kredit');
    @endphp

    <div class="journal-detail-card">
        <h3 style="margin-top:0">Rincian Debit dan Kredit</h3>
        <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Keterangan</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurnal->details as $detail)
                        <tr>
                            <td>{{ $detail->account->code }}</td>
                            <td>{{ $detail->account->name }}</td>
                            <td>{{ $detail->keterangan ?? '-' }}</td>
                            <td>Rp {{ number_format($detail->debit, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->kredit, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">TOTAL</th>
                        <th>Rp {{ number_format($totalDebit, 0, ',', '.') }}</th>
                        <th>Rp {{ number_format($totalKredit, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
