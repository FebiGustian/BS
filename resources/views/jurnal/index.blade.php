@extends('layouts.panel')

@section('title', 'Jurnal Umum')
@section('judul', 'Jurnal Umum')
@section('subjudul', 'General Journal')

@section('content')
<style>
.journal-page{color:#253047}
.journal-box{background:#fff;border:1px solid #e7ebf1;border-radius:12px;padding:20px;margin-bottom:20px}
.journal-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.journal-toolbar form{display:flex;gap:8px;flex-wrap:wrap}
.journal-page input{padding:10px;border:1px solid #dce2ea;border-radius:7px}
.journal-page table{width:100%;border-collapse:collapse;min-width:750px}
.journal-page th,.journal-page td{padding:13px 12px;text-align:left;border-bottom:1px solid #edf0f4;font-size:13px}
.journal-page th{background:#f7f9fc;color:#667085}
.journal-page .table-scroll{overflow-x:auto}
.journal-btn{display:inline-block;padding:10px 15px;background:#2457d6;color:white;border:0;border-radius:7px;text-decoration:none;font-size:13px;cursor:pointer}
.journal-muted{color:#788399;font-size:13px}
.journal-number{font-weight:700;color:#2457d6;text-decoration:none}
.journal-pagination{margin-top:18px}
</style>

<div class="journal-page">
    @if(session('success'))
        <div class="journal-box" style="color:#147a43">{{ session('success') }}</div>
    @endif

    <div class="journal-box">
        <div class="journal-toolbar">
            <div>
                <h3 style="margin:0 0 5px">Daftar Jurnal</h3>
                <div class="journal-muted">Pencatatan transaksi debit dan kredit cabang aktif.</div>
            </div>
            <a href="{{ route('jurnal.create') }}" class="journal-btn">+ Buat Jurnal</a>
        </div>

        <form method="GET" class="journal-toolbar">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor atau keterangan...">
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
                <button class="journal-btn" type="submit">Filter</button>
                <a href="{{ route('jurnal.index') }}" class="journal-btn" style="background:#667085">Reset</a>
        </form>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor Jurnal</th>
                        <th>Keterangan</th>
                        <th>Total Debit</th>
                        <th>Total Kredit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurnals as $jurnal)
                        <tr>
                            <td>{{ $jurnal->tanggal->format('d/m/Y') }}</td>
                            <td>
                                <a class="journal-number" href="{{ route('jurnal.show', $jurnal) }}">
                                    {{ $jurnal->nomor_jurnal }}
                                </a>
                            </td>
                            <td>{{ $jurnal->deskripsi }}</td>
                            <td>Rp {{ number_format($jurnal->details_sum_debit ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($jurnal->details_sum_kredit ?? 0, 0, ',', '.') }}</td>
                            <td>{{ strtoupper($jurnal->status) }}</td>
                            <td><a href="{{ route('jurnal.show', $jurnal) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:30px">Belum ada jurnal pada cabang ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="journal-pagination">{{ $jurnals->links() }}</div>
    </div>
</div>
@endsection
