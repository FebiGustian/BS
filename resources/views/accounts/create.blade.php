@extends('layouts.panel')

@section('title', 'Tambah Akun')
@section('judul', 'Finance & Accounting')
@section('subjudul', 'Tambah Akun')

@section('content')
<style>

*{box-sizing:border-box}body{margin:0;background:#f5f7fb;font-family:Arial;color:#253047}
.container{max-width:800px;margin:35px auto;padding:20px}
.card{background:white;border:1px solid #e7ebf1;border-radius:12px;padding:25px}
h1{font-size:25px}.muted{color:#788399;font-size:13px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:25px}
.field{display:flex;flex-direction:column;gap:8px}.full{grid-column:1/-1}
label{font-size:13px;font-weight:bold}
input,select,textarea{padding:12px;border:1px solid #dce2ea;border-radius:8px;font:inherit;width:100%}
button,.btn{padding:12px 18px;border:0;border-radius:8px;text-decoration:none;cursor:pointer;font-size:13px}
.primary{background:#2457d6;color:white}.light{background:white;color:#344054;border:1px solid #ddd}
.errors{background:#fff0f0;color:#b42318;padding:12px;border-radius:8px}
.actions{display:flex;gap:10px;margin-top:25px}
@media(max-width:600px){.grid{grid-template-columns:1fr}.full{grid-column:auto}}

</style>

<div class="container"><div class="card">
    <div class="muted">FINANCE & ACCOUNTING / MASTER COA</div>
    <h1>Tambah Akun Baru</h1>
    <p class="muted">Tambahkan akun ke struktur akuntansi Bintang Silver.</p>

    @if($errors->any())
        <div class="errors">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form action="{{ route('accounts.store') }}" method="POST">
        @csrf
        <div class="grid">
            <div class="field">
                <label>Kode Akun *</label>
                <input name="code" value="{{ old('code') }}" required maxlength="20" placeholder="Contoh: 6180">
            </div>
            <div class="field">
                <label>Nama Akun *</label>
                <input name="name" value="{{ old('name') }}" required placeholder="Contoh: Beban Internet">
            </div>
            <div class="field">
                <label>Jenis Akun *</label>
                <select name="type" id="accountType" required>
                    <option value="">Pilih jenis</option>
                    @foreach($types as $key=>$label)
                        <option value="{{ $key }}" @selected(old('type')===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Saldo Normal *</label>
                <select name="normal_balance" required>
                    <option value="debit" @selected(old('normal_balance','debit')==='debit')>Debit</option>
                    <option value="credit" @selected(old('normal_balance')==='credit')>Kredit</option>
                </select>
            </div>
            <div class="field full">
                <label>Akun Induk</label>
                <select name="parent_id" id="parentAccount">
                    <option value="">Tanpa akun induk</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" data-type="{{ $parent->type }}" @selected(old('parent_id')==$parent->id)>
                            {{ $parent->code }} - {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field full">
                <label>Keterangan</label>
                <textarea name="description" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="field full">
                <label><span><input type="checkbox" name="is_active" value="1" style="width:auto" @checked(old('is_active','1')=='1')> Akun aktif</span></label>
            </div>
        </div>
        <div class="actions">
            <button class="primary">Simpan Akun</button>
            <a href="{{ route('accounts.index') }}" class="btn light">Kembali</a>
        </div>
    </form>
</div></div>
<script>
const typeSelect=document.getElementById('accountType');
const parentSelect=document.getElementById('parentAccount');
function filterParents(){
    [...parentSelect.options].forEach(option=>{
        if(!option.value)return;
        option.hidden=typeSelect.value!=='' && option.dataset.type!==typeSelect.value;
        if(option.hidden && option.selected)parentSelect.value='';
    });
}
typeSelect.addEventListener('change',filterParents);
filterParents();
</script>

@endsection
