@extends('layouts.panel')

@section('title', 'Buku Besar')
@section('judul', 'Buku Besar')
@section('subjudul', 'General Ledger')

@section('content')
<style>
.ledger-page{color:#253047}
.ledger-card{background:white;border:1px solid #e7ebf1;border-radius:12px;padding:20px;margin-bottom:20px}
.ledger-filter{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:10px;align-items:end}
.ledger-page label{display:block;font-size:13px;font-weight:600;margin-bottom:7px}
.ledger-page input,.ledger-page select{width:100%;padding:10px;border:1px solid #dce2ea;border-radius:7px;background:white}
.ledger-btn{display:inline-block;padding:11px 15px;background:#2457d6;color:white;border:0;border-radius:7px;text-decoration:none;cursor:pointer;font-size:13px}
.ledger-reset{background:#667085}
.ledger-scroll{overflow-x:auto}
.ledger-page table{width:100%;min-width:850px;border-collapse:collapse}
.ledger-page th,.ledger-page td{padding:13px 12px;border-bottom:1px solid #edf0f4;text-align:left;font-size:13px}
.ledger-page th{background:#f7f9fc;color:#667085}
.ledger-number{font-weight:700;color:#2457d6}
.ledger-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.ledger-stat{background:#f7f9fc;border-radius:9px;padding:15px}
.ledger-stat small{color:#788399}
.ledger-stat strong{display:block;font-size:19px;margin-top:8px}
.ledger-muted{color:#788399;font-size:13px}
@media(max-width:850px){.ledger-filter{grid-template-columns:1fr 1fr}.ledger-summary{grid-template-columns:1fr}}
</style>

<div class="ledger-page">
    <div class="ledger-card">
        <h3 style="margin-top:0">Filter Buku Besar</h3>

        <form method="GET" action="{{ route('buku-besar.index') }}">
            <div class="ledger-filter">
                <div>
                    <label for="account_id">Pilih Akun</label>
                    <select id="account_id" name="account_id">
                        <option value="">-- Pilih akun COA --</option>
                        @foreach($accounts as $item)
                            <option value="{{ $item->id }}" @selected(request('account_id') == $item->id)>
                                {{ $item->code }} — {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tanggal_mulai">Dari Tanggal</label>
                    <input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                </div>
                <div>
                    <label for="tanggal_akhir">Sampai Tanggal</label>
                    <input id="tanggal_akhir" type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
                </div>
                <button type="submit" class="ledger-btn">Tampilkan</button>
                <a href="{{ route('buku-besar.index') }}" class="ledger-btn ledger-reset">Reset</a>
            </div>
        </form>
    </div>

    @if($account)
        @php
            $normalIsDebit = strtoupper($account->normal_balance) === 'DEBIT';
            $openingSide = $openingBalance >= 0
                ? ($normalIsDebit ? 'D' : 'K')
                : ($normalIsDebit ? 'K' : 'D');
        @endphp

        <div class="ledger-card">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px">
                <div>
                    <h3 style="margin:0 0 6px">{{ $account->name }}</h3>
                    <div class="ledger-muted">
                        Kode Akun: {{ $account->code }} · Saldo Normal: {{ $account->normal_balance }}
                    </div>
                </div>
                <div class="ledger-muted">Buku besar cabang aktif</div>
            </div>

            <div class="ledger-summary">
                <div class="ledger-stat">
                    <small>Saldo Awal</small>
                    <strong>Rp {{ number_format(abs($openingBalance), 0, ',', '.') }} ({{ $openingSide }})</strong>
                </div>
                <div class="ledger-stat">
                    <small>Total Debit Periode</small>
                    <strong>Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong>
                </div>
                <div class="ledger-stat">
                    <small>Total Kredit Periode</small>
                    <strong>Rp {{ number_format($totalKredit, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="ledger-card">
            <h3 style="margin-top:0">Rincian Mutasi Akun</h3>

            <div class="ledger-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor Jurnal</th>
                            <th>Keterangan</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(request('tanggal_mulai'))
                            <tr style="background:#f7f9fc">
                                <td colspan="3"><strong>Saldo Awal</strong></td>
                                <td>-</td>
                                <td>-</td>
                                <td class="ledger-number">
                                    Rp {{ number_format(abs($openingBalance), 0, ',', '.') }} ({{ $openingSide }})
                                </td>
                            </tr>
                        @endif

                        @forelse($rows as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                <td class="ledger-number">{{ $row->nomor_jurnal }}</td>
                                <td>
                                    {{ $row->keterangan_detail ?: $row->deskripsi }}
                                    @if($row->keterangan_detail)
                                        <div class="ledger-muted">{{ $row->deskripsi }}</div>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($row->debit, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->kredit, 0, ',', '.') }}</td>
                                <td class="ledger-number">
                                    Rp {{ number_format($row->saldo, 0, ',', '.') }} ({{ $row->saldo_side }})
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:28px">
                                    Belum ada transaksi untuk akun ini pada periode tersebut.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background:#f7f9fc">
                            <th colspan="3">Total Mutasi Periode</th>
                            <th>Rp {{ number_format($totalDebit, 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($totalKredit, 0, ',', '.') }}</th>
                            <th>-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="ledger-card" style="text-align:center;padding:45px 20px">
            <h3>Pilih akun untuk melihat Buku Besar</h3>
            <p class="ledger-muted">Pilih salah satu akun dari Master COA, kemudian klik Tampilkan.</p>
        </div>
    @endif
</div>
@endsection
