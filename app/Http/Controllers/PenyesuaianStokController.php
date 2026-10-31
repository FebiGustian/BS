<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PenyesuaianStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PenyesuaianStokController extends Controller
{
    private const ALASAN = [
        'Selisih hitung (stock opname)',
        'Barang rusak',
        'Barang hilang',
        'Barang ditemukan',
        'Koreksi kesalahan input',
        'Lainnya',
    ];

    public function index()
    {
        $penyesuaians = PenyesuaianStok::with(['barang', 'user'])
            ->where('cabang_id', (int) session('cabang_id'))
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15);

        return view('penyesuaian.index', compact('penyesuaians'));
    }

    public function create()
    {
        $cabangId = (int) session('cabang_id');

        $barangs = Barang::diCabang($cabangId)
            ->where('barangs.is_active', true)
            ->orderBy('barangs.nama_barang')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'nama' => $b->nama_barang,
                'sku' => $b->sku,
                'stok' => (int) $b->stok_cabang,
            ]);

        $alasans = self::ALASAN;

        return view('penyesuaian.create', compact('barangs', 'alasans'));
    }

    public function store(Request $request)
    {
        $cabangId = (int) session('cabang_id');

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'alasan' => ['required', Rule::in(self::ALASAN)],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.barang_id' => ['required', 'integer', 'exists:barangs,id', 'distinct'],
            'items.*.stok_fisik' => ['required', 'integer', 'min:0'],
        ], [
            'items.required' => 'Tambahkan minimal satu barang.',
            'items.*.barang_id.required' => 'Pilih barang pada setiap baris.',
            'items.*.barang_id.distinct' => 'Satu barang hanya boleh muncul satu kali.',
            'items.*.stok_fisik.required' => 'Isi stok fisik pada setiap baris.',
            'items.*.stok_fisik.min' => 'Stok fisik tidak boleh minus.',
        ]);

        $dicatat = DB::transaction(function () use ($data, $cabangId) {
            $dicatat = 0;

            foreach ($data['items'] as $item) {
                $barangId = (int) $item['barang_id'];

                $baris = DB::table('stok_cabangs')
                    ->where('cabang_id', $cabangId)
                    ->where('barang_id', $barangId)
                    ->first();

                if (! $baris) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada barang yang belum memiliki stok di cabang ini. Catat dulu lewat Stock In.',
                    ]);
                }

                $stokSistem = (int) $baris->stok;
                $stokFisik = (int) $item['stok_fisik'];
                $selisih = $stokFisik - $stokSistem;

                if ($selisih === 0) {
                    continue;
                }

                DB::table('stok_cabangs')
                    ->where('cabang_id', $cabangId)
                    ->where('barang_id', $barangId)
                    ->update(['stok' => $stokFisik]);

                PenyesuaianStok::create([
                    'cabang_id' => $cabangId,
                    'barang_id' => $barangId,
                    'user_id' => auth()->id(),
                    'tanggal' => $data['tanggal'],
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $selisih,
                    'alasan' => $data['alasan'],
                    'catatan' => $data['catatan'] ?? null,
                ]);

                $dicatat++;
            }

            if ($dicatat === 0) {
                throw ValidationException::withMessages([
                    'items' => 'Tidak ada selisih: stok fisik sama dengan stok di sistem.',
                ]);
            }

            return $dicatat;
        });

        return redirect()->route('penyesuaian.index')
            ->with('success', "{$dicatat} penyesuaian stok berhasil dicatat dan stok cabang diperbarui.");
    }
}
