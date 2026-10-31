@extends('layouts.panel')

@section('title', 'Buat Jurnal')
@section('judul', 'Buat Jurnal Umum')
@section('subjudul', 'Input transaksi debit dan kredit')

@section('content')
<style>
.journal-form{color:#253047}
.journal-card{background:#fff;border:1px solid #e7ebf1;border-radius:12px;padding:20px;margin-bottom:18px}
.journal-form label{display:block;font-size:13px;font-weight:600;margin-bottom:7px}
.journal-form input,.journal-form select{width:100%;padding:10px;border:1px solid #dce2ea;border-radius:7px;font:inherit}
.journal-grid{display:grid;grid-template-columns:1fr 2fr;gap:15px}
.journal-detail-scroll{overflow-x:auto}
.journal-detail-table{width:100%;min-width:850px;border-collapse:collapse}
.journal-detail-table th,.journal-detail-table td{padding:10px;border-bottom:1px solid #edf0f4;text-align:left}
.journal-detail-table th{font-size:12px;color:#667085;background:#f7f9fc}
.journal-detail-table input,.journal-detail-table select{min-width:120px}
.journal-primary{background:#2457d6;color:white;border:0;border-radius:7px;padding:11px 16px;text-decoration:none;cursor:pointer}
.journal-secondary{background:#667085;color:white;border:0;border-radius:7px;padding:10px 14px;text-decoration:none;cursor:pointer}
.journal-danger{background:#fff0f0;color:#bd3333;border:0;border-radius:6px;padding:8px 10px;cursor:pointer}
.journal-error{background:#fff1f0;color:#b42318;padding:12px;border-radius:8px;margin-bottom:15px}
.journal-total{font-weight:700;font-size:15px}
@media(max-width:700px){.journal-grid{grid-template-columns:1fr}}
</style>

<div class="journal-form">
    @if($errors->any())
        <div class="journal-error">
            <strong>Periksa kembali data jurnal:</strong>
            <ul style="margin:8px 0 0 18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($accounts->isEmpty())
        <div class="journal-card">Belum ada akun aktif. Periksa kembali Master Chart of Accounts.</div>
    @else
        <form method="POST" action="{{ route('jurnal.store') }}">
            @csrf

            <div class="journal-card">
                <div class="journal-grid">
                    <div>
                        <label for="tanggal">Tanggal Jurnal</label>
                        <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                    <div>
                        <label for="deskripsi">Keterangan Transaksi</label>
                        <input id="deskripsi" type="text" name="deskripsi" value="{{ old('deskripsi') }}" placeholder="Contoh: Pembayaran biaya operasional toko" maxlength="255" required>
                    </div>
                </div>
            </div>

            <div class="journal-card">
                <h3 style="margin-top:0">Rincian Jurnal</h3>
                <p style="font-size:13px;color:#788399">Isi nilai pada kolom debit atau kredit. Setiap baris hanya boleh diisi pada salah satu sisi.</p>

                <div class="journal-detail-scroll">
                    <table class="journal-detail-table">
                        <thead>
                            <tr>
                                <th style="width:30%">Akun</th>
                                <th style="width:20%">Debit (Rp)</th>
                                <th style="width:20%">Kredit (Rp)</th>
                                <th>Keterangan Baris</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="journal-rows">
                            @php
                                $oldRows = old('details', [
                                    ['account_id' => '', 'debit' => '', 'kredit' => '', 'keterangan' => ''],
                                    ['account_id' => '', 'debit' => '', 'kredit' => '', 'keterangan' => ''],
                                ]);
                            @endphp

                            @foreach($oldRows as $i => $row)
                                <tr class="journal-row">
                                    <td>
                                        <select name="details[{{ $i }}][account_id]" required>
                                            <option value="">Pilih akun</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" @selected(($row['account_id'] ?? '') == $account->id)>
                                                    {{ $account->code }} — {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input class="debit-input" type="number" name="details[{{ $i }}][debit]" min="0" step="0.01" value="{{ $row['debit'] ?? '' }}" placeholder="0"></td>
                                    <td><input class="kredit-input" type="number" name="details[{{ $i }}][kredit]" min="0" step="0.01" value="{{ $row['kredit'] ?? '' }}" placeholder="0"></td>
                                    <td><input type="text" name="details[{{ $i }}][keterangan]" value="{{ $row['keterangan'] ?? '' }}" placeholder="Opsional"></td>
                                    <td><button type="button" class="journal-danger remove-row">Hapus</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-top:18px">
                    <button type="button" id="add-row" class="journal-secondary">+ Tambah Baris</button>
                    <div style="display:flex;gap:25px;flex-wrap:wrap">
                        <div class="journal-total">Total Debit: Rp <span id="total-debit">0</span></div>
                        <div class="journal-total">Total Kredit: Rp <span id="total-kredit">0</span></div>
                    </div>
                </div>
                <p id="balance-status" style="font-size:13px;margin-bottom:0;color:#667085">Pastikan total debit sama dengan total kredit.</p>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end">
                <a href="{{ route('jurnal.index') }}" class="journal-secondary">Batal</a>
                <button type="submit" class="journal-primary">Simpan Jurnal</button>
            </div>
        </form>
    @endif
</div>

<template id="journal-row-template">
    <tr class="journal-row">
        <td>
            <select name="details[__INDEX__][account_id]" required>
                <option value="">Pilih akun</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input class="debit-input" type="number" name="details[__INDEX__][debit]" min="0" step="0.01" placeholder="0"></td>
        <td><input class="kredit-input" type="number" name="details[__INDEX__][kredit]" min="0" step="0.01" placeholder="0"></td>
        <td><input type="text" name="details[__INDEX__][keterangan]" placeholder="Opsional"></td>
        <td><button type="button" class="journal-danger remove-row">Hapus</button></td>
    </tr>
</template>

<script>
let journalRowIndex = document.querySelectorAll('#journal-rows .journal-row').length;

function calculateJournalTotals() {
    let debit = 0;
    let kredit = 0;

    document.querySelectorAll('.debit-input').forEach(input => debit += Number(input.value || 0));
    document.querySelectorAll('.kredit-input').forEach(input => kredit += Number(input.value || 0));

    document.getElementById('total-debit').textContent = debit.toLocaleString('id-ID');
    document.getElementById('total-kredit').textContent = kredit.toLocaleString('id-ID');

    const status = document.getElementById('balance-status');
    if (debit > 0 && Math.abs(debit - kredit) < 0.005) {
        status.textContent = '✓ Seimbang. Jurnal siap disimpan.';
        status.style.color = '#147a43';
    } else {
        status.textContent = 'Belum seimbang. Total debit harus sama dengan total kredit.';
        status.style.color = '#b42318';
    }
}

document.getElementById('add-row')?.addEventListener('click', () => {
    const template = document.getElementById('journal-row-template').innerHTML;
    const html = template.replaceAll('__INDEX__', journalRowIndex++);
    document.getElementById('journal-rows').insertAdjacentHTML('beforeend', html);
    calculateJournalTotals();
});

document.getElementById('journal-rows')?.addEventListener('click', event => {
    if (event.target.classList.contains('remove-row')) {
        const rows = document.querySelectorAll('#journal-rows .journal-row');
        if (rows.length <= 2) {
            alert('Jurnal harus memiliki minimal dua baris.');
            return;
        }
        event.target.closest('tr').remove();
        calculateJournalTotals();
    }
});

document.getElementById('journal-rows')?.addEventListener('input', calculateJournalTotals);
calculateJournalTotals();
</script>
@endsection
