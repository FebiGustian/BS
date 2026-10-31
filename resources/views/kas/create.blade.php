@extends('layouts.panel')

@section('title', 'Tambah Transaksi Kas')
@section('judul', 'Tambah Transaksi Kas')
@section('subjudul', 'Pemasukan, pengeluaran, dan transfer pusat-cabang')

@section('content')
<style>
.kas-form{color:#253047}
.kas-form-card{background:#fff;border:1px solid #e7ebf1;border-radius:12px;padding:20px;margin-bottom:18px}
.kas-form label{display:block;font-size:13px;font-weight:600;margin-bottom:7px}
.kas-form input,.kas-form select,.kas-form textarea{width:100%;padding:11px;border:1px solid #dce2ea;border-radius:7px;font:inherit;background:white}
.kas-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.kas-field{margin-bottom:15px}
.kas-help{font-size:12px;color:#788399;margin-top:6px}
.kas-primary{background:#2457d6;color:white;border:0;border-radius:7px;padding:11px 17px;cursor:pointer;text-decoration:none}
.kas-secondary{background:#667085;color:white;border:0;border-radius:7px;padding:11px 17px;cursor:pointer;text-decoration:none}
.kas-error{background:#fff1f0;color:#b42318;padding:13px;border-radius:8px;margin-bottom:15px}
.kas-info{background:#f0f5ff;border:1px solid #dce7ff;padding:15px;border-radius:9px;color:#34466a;font-size:13px}
@media(max-width:700px){.kas-grid{grid-template-columns:1fr}}
</style>

@php
    $currentJenis = old('jenis', $jenis);
    $butuhAkun = in_array($currentJenis, ['income', 'expense'], true);
@endphp

<div class="kas-form">
    @if($errors->any())
        <div class="kas-error">
            <strong>Periksa kembali data transaksi:</strong>
            <ul style="margin:8px 0 0 18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('kas.store') }}" enctype="multipart/form-data" id="kas-form">
        @csrf

        <div class="kas-form-card">
            <h3 style="margin-top:0">Informasi Transaksi</h3>

            <div class="kas-grid">
                <div class="kas-field">
                    <label for="jenis">Jenis Transaksi</label>
                    <select name="jenis" id="jenis" required>
                        <option value="income" @selected($currentJenis === 'income')>Pemasukan Toko</option>
                        <option value="expense" @selected($currentJenis === 'expense')>Pengeluaran Toko</option>
                        <option value="transfer_in" @selected($currentJenis === 'transfer_in')>Transfer dari Pusat ke Cabang</option>
                        <option value="transfer_out" @selected($currentJenis === 'transfer_out')>Transfer dari Cabang ke Pusat</option>
                    </select>
                </div>

                <div class="kas-field">
                    <label for="tanggal">Tanggal Transaksi</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="kas-field">
                <label for="deskripsi">Keterangan</label>
                <input type="text" id="deskripsi" name="deskripsi" value="{{ old('deskripsi') }}" maxlength="255" placeholder="Contoh: Penerimaan penjualan toko / pembayaran gaji karyawan" required>
            </div>

            <div class="kas-grid">
                <div class="kas-field">
                    <label for="nominal">Nominal (Rp)</label>
                    <input type="number" id="nominal" name="nominal" value="{{ old('nominal') }}" min="0.01" step="0.01" placeholder="Contoh: 1000000" required>
                    <div class="kas-help">Masukkan angka tanpa titik pemisah ribuan.</div>
                </div>

                <div class="kas-field" id="cash-account-wrap">
                    <label for="cash_account_id">Akun Kas / Bank</label>
                    <select id="cash_account_id" name="cash_account_id">
                        <option value="">Pilih Kas / Bank</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" @selected(old('cash_account_id') == $account->id)>
                                {{ $account->code }} — {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="kas-field" id="category-wrap">
                <label for="account_id"><span id="category-label">{{ $currentJenis === 'expense' ? 'Akun Beban' : 'Akun Pendapatan' }}</span></label>
                <select id="account_id" name="account_id">
                    <option value="">Pilih akun</option>
                    @foreach($incomeAccounts as $account)
                        <option data-kind="income" value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                    @foreach($expenseAccounts as $account)
                        <option data-kind="expense" value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                </select>
                <div class="kas-help">Daftar akun mengikuti Master Chart of Accounts.</div>
            </div>
        </div>

        <div class="kas-form-card" id="transfer-info" style="display:none">
            <h3 style="margin-top:0">Informasi Transfer</h3>
            <div class="kas-info" id="transfer-description">
                Transaksi transfer antara Kas Pusat dan Kas Cabang.
            </div>
            <p class="kas-help">Sistem menggunakan akun 1111 Kas Cabang dan 1112 Kas Pusat secara otomatis.</p>
        </div>

        <div class="kas-form-card">
            <h3 style="margin-top:0">Bukti Transaksi</h3>
            <div class="kas-field">
                <label for="bukti">Upload Nota / Bukti Transfer (Opsional)</label>
                <input type="file" id="bukti" name="bukti" accept=".jpg,.jpeg,.png,.pdf">
                <div class="kas-help">Format JPG, JPEG, PNG, atau PDF. Maksimal 5 MB.</div>
            </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
            <a href="{{ route('kas.index') }}" class="kas-secondary">Batal</a>
            <button type="submit" class="kas-primary">Simpan Transaksi dan Buat Jurnal</button>
        </div>
    </form>
</div>

<script>
const jenisInput = document.getElementById('jenis');
const categoryWrap = document.getElementById('category-wrap');
const cashWrap = document.getElementById('cash-account-wrap');
const categorySelect = document.getElementById('account_id');
const cashSelect = document.getElementById('cash_account_id');
const transferInfo = document.getElementById('transfer-info');
const categoryLabel = document.getElementById('category-label');
const transferDescription = document.getElementById('transfer-description');

function updateKasForm() {
    const jenis = jenisInput.value;
    const ordinary = jenis === 'income' || jenis === 'expense';

    categoryWrap.style.display = ordinary ? 'block' : 'none';
    cashWrap.style.display = ordinary ? 'block' : 'none';
    transferInfo.style.display = ordinary ? 'none' : 'block';

    categorySelect.required = ordinary;
    cashSelect.required = ordinary;
    categorySelect.disabled = !ordinary;
    cashSelect.disabled = !ordinary;

    categoryLabel.textContent = jenis === 'expense' ? 'Akun Beban' : 'Akun Pendapatan';

    [...categorySelect.options].forEach(option => {
        if (!option.value) {
            option.hidden = false;
            return;
        }

        const visible = option.dataset.kind === jenis;
        option.hidden = !visible;

        if (!visible && option.selected) {
            categorySelect.value = '';
        }
    });

    if (jenis === 'transfer_in') {
        transferDescription.textContent = 'Dana diterima dari kantor pusat. Jurnal: Debit Kas Cabang, Kredit Kas Pusat.';
    } else if (jenis === 'transfer_out') {
        transferDescription.textContent = 'Dana dikirim dari cabang ke kantor pusat. Jurnal: Debit Kas Pusat, Kredit Kas Cabang.';
    }
}

jenisInput.addEventListener('change', updateKasForm);
updateKasForm();
</script>
@endsection
