@extends('layouts.panel')

@section('title', 'Tambah Penyesuaian Stok')
@section('judul', 'Tambah Penyesuaian')
@section('subjudul', 'Samakan stok sistem dengan hasil hitung fisik')

@section('content')
@if ($errors->any())
    <div class="alert err">
        @foreach ($errors->all() as $e)
            <div>{{ $e }}</div>
        @endforeach
    </div>
@endif

@if ($barangs->isEmpty())
    <div class="alert err">Belum ada barang yang memiliki stok di cabang ini. Catat dulu lewat Stock In.</div>
@endif

<form action="{{ route('penyesuaian.store') }}" method="POST" class="panel">
    @csrf

    <div class="grid3">
        <div class="field">
            <label for="tanggal">Tanggal</label>
            <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required>
        </div>
        <div class="field">
            <label for="alasan">Alasan</label>
            <select id="alasan" name="alasan" required>
                @foreach ($alasans as $a)
                    <option value="{{ $a }}" @selected(old('alasan') === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="catatan">Catatan (opsional)</label>
            <input id="catatan" type="text" name="catatan" value="{{ old('catatan') }}">
        </div>
    </div>

    <h3 style="margin:18px 0 10px">Barang yang disesuaikan</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th class="num" style="width:120px">Stok sistem</th>
                    <th style="width:140px">Stok fisik</th>
                    <th class="num" style="width:100px">Selisih</th>
                    <th style="width:50px"></th>
                </tr>
            </thead>
            <tbody id="baris"></tbody>
        </table>
    </div>

    <button type="button" class="btn btn-secondary" id="tambah" style="margin-top:12px">+ Tambah baris</button>

    <div style="margin-top:22px;display:flex;gap:10px">
        <button type="submit" class="btn">Simpan penyesuaian</button>
        <a href="{{ route('penyesuaian.index') }}" class="btn btn-secondary">Batal</a>
    </div>
</form>

<script>
(function () {
    const barangs = @json($barangs);
    const lama = Object.values(@json(old('items', [])));
    const tbody = document.getElementById('baris');
    let urut = 0;

    function hitung(tr) {
        const o = tr.querySelector('.barang').selectedOptions[0];
        const fisik = tr.querySelector('.fisik');
        const sistemEl = tr.querySelector('.sistem');
        const selisihEl = tr.querySelector('.selisih');
        const sistem = o && o.dataset.stok !== undefined ? parseInt(o.dataset.stok) : null;

        sistemEl.textContent = sistem === null ? '-' : sistem;

        if (sistem === null || fisik.value === '') {
            selisihEl.textContent = '-';
            selisihEl.style.color = '';
            return;
        }

        const d = parseInt(fisik.value) - sistem;
        selisihEl.textContent = (d > 0 ? '+' : '') + d;
        selisihEl.style.color = d === 0 ? '#8491a5' : (d > 0 ? '#166534' : '#991b1b');
    }

    function tambahBaris(data = {}) {
        const i = urut++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select class="barang" name="items[${i}][barang_id]" required></select></td>
            <td class="num sistem">-</td>
            <td><input class="fisik" type="number" name="items[${i}][stok_fisik]" min="0" value="${data.stok_fisik ?? ''}" required></td>
            <td class="num selisih" style="font-weight:700">-</td>
            <td><button type="button" class="btn-x" title="Hapus baris">&times;</button></td>`;

        const sel = tr.querySelector('.barang');
        sel.add(new Option('Pilih barang', ''));
        barangs.forEach(b => {
            const o = new Option(`${b.nama} (${b.sku})`, b.id);
            o.dataset.stok = b.stok;
            if (String(b.id) === String(data.barang_id)) o.selected = true;
            sel.add(o);
        });

        const fisik = tr.querySelector('.fisik');
        sel.addEventListener('change', () => {
            const o = sel.selectedOptions[0];
            if (fisik.value === '' && o && o.dataset.stok !== undefined) fisik.value = o.dataset.stok;
            hitung(tr);
        });
        fisik.addEventListener('input', () => hitung(tr));
        tr.querySelector('.btn-x').addEventListener('click', () => {
            if (tbody.children.length > 1) tr.remove();
        });

        tbody.appendChild(tr);
        hitung(tr);
    }

    document.getElementById('tambah').addEventListener('click', () => tambahBaris());

    if (lama.length) { lama.forEach(item => tambahBaris(item)); } else { tambahBaris(); }
})();
</script>
@endsection
