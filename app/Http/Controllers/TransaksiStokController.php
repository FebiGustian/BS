<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\DetailBarangKeluar;
use App\Models\DetailBarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransaksiStokController extends Controller
{
    private function konfig(string $jenis): array
    {
        return $jenis === 'masuk'
            ? [
                'model' => BarangMasuk::class,
                'detail' => DetailBarangMasuk::class,
                'prefix' => 'BM',
                'judul' => 'Barang Masuk',
                'pihak' => 'Pemasok',
                'kolom_harga' => 'harga_beli',
            ]
            : [
                'model' => BarangKeluar::class,
                'detail' => DetailBarangKeluar::class,
                'prefix' => 'BK',
                'judul' => 'Barang Keluar',
                'pihak' => 'Pelanggan / Tujuan',
                'kolom_harga' => 'harga_jual',
            ];
    }

    public function index(string $jenis)
    {
        $k = $this->konfig($jenis);
        $cabangId = (int) session('cabang_id');

        $transaksis = $k['model']::where('cabang_id', $cabangId)
            ->withCount('details')
            ->latest('tanggal')
            ->latest('id')
            ->paginate(10);

        $bulanIni = $k['model']::where('cabang_id', $cabangId)
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);

        $totalBulanIni = (float) (clone $bulanIni)->sum('total');
        $jumlahBulanIni = (clone $bulanIni)->count();

        return view('stok.index', compact('jenis', 'k', 'transaksis', 'totalBulanIni', 'jumlahBulanIni'));
    }

    public function create(string $jenis)
    {
        $k = $this->konfig($jenis);
        $cabangId = (int) session('cabang_id');

        $stokCabang = DB::table('stok_cabangs')->where('cabang_id', $cabangId)->pluck('stok', 'barang_id');

        $barangs = Barang::where('is_active', true)
            ->orderBy('nama_barang')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'nama' => $b->nama_barang,
                'sku' => $b->sku,
                'satuan' => $b->satuan,
                'harga' => (float) $b->{$k['kolom_harga']},
                'stok' => (int) ($stokCabang[$b->id] ?? 0),
            ]);

        if ($jenis === 'keluar') {
            $barangs = $barangs->filter(fn ($b) => $b['stok'] > 0)->values();
        }

        return view('stok.create', compact('jenis', 'k', 'barangs'));
    }

    public function store(Request $request, string $jenis)
    {
        $k = $this->konfig($jenis);
        $cabangId = (int) session('cabang_id');

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'pihak' => ['nullable', 'string', 'max:255'],
            'nomor_referensi' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.barang_id' => ['required', 'integer', 'exists:barangs,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.harga' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Tambahkan minimal satu barang.',
            'items.*.barang_id.required' => 'Pilih barang pada setiap baris.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
        ]);

        $trx = DB::transaction(function () use ($data, $k, $jenis, $cabangId) {
            $trx = $k['model']::create([
                'cabang_id' => $cabangId,
                'user_id' => auth()->id(),
                'nomor_transaksi' => $this->buatNomor($k, $cabangId),
                'tanggal' => $data['tanggal'],
                'pihak' => $data['pihak'] ?? null,
                'nomor_referensi' => $data['nomor_referensi'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $barangId = (int) $item['barang_id'];
                $jumlah = (int) $item['jumlah'];
                $harga = (float) $item['harga'];

                if ($jenis === 'masuk') {
                    $this->tambahStok($cabangId, $barangId, $jumlah);
                } elseif (! $this->kurangiStok($cabangId, $barangId, $jumlah)) {
                    $barang = Barang::withTrashed()->find($barangId);
                    $tersedia = (int) DB::table('stok_cabangs')
                        ->where('cabang_id', $cabangId)
                        ->where('barang_id', $barangId)
                        ->value('stok');

                    throw ValidationException::withMessages([
                        'items' => "Stok {$barang->nama_barang} di cabang ini tidak cukup (tersedia {$tersedia}).",
                    ]);
                }

                $subtotal = $jumlah * $harga;

                $trx->details()->create([
                    'barang_id' => $barangId,
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $trx->update(['total' => $total]);

            return $trx;
        });

        return redirect()->route('stok.show', ['jenis' => $jenis, 'id' => $trx->id])
            ->with('success', $k['judul'] . ' berhasil dicatat dan stok cabang diperbarui.');
    }

    public function show(string $jenis, int $id)
    {
        $k = $this->konfig($jenis);

        $trx = $k['model']::where('cabang_id', (int) session('cabang_id'))
            ->with(['details.barang', 'user'])
            ->findOrFail($id);

        return view('stok.show', compact('jenis', 'k', 'trx'));
    }

    public function destroy(string $jenis, int $id)
    {
        $k = $this->konfig($jenis);

        $trx = $k['model']::where('cabang_id', (int) session('cabang_id'))
            ->with('details')
            ->findOrFail($id);

        DB::transaction(function () use ($trx, $jenis) {
            foreach ($trx->details as $d) {
                if ($jenis === 'masuk') {
                    if (! $this->kurangiStok($trx->cabang_id, $d->barang_id, $d->jumlah)) {
                        throw ValidationException::withMessages([
                            'hapus' => 'Transaksi tidak bisa dihapus karena sebagian stoknya sudah terpakai.',
                        ]);
                    }
                } else {
                    $this->tambahStok($trx->cabang_id, $d->barang_id, $d->jumlah);
                }
            }

            $trx->delete();
        });

        return redirect()->route('stok.index', $jenis)
            ->with('success', 'Transaksi dihapus dan stok cabang dikembalikan.');
    }

    private function buatNomor(array $k, int $cabangId): string
    {
        $n = 1;

        do {
            $nomor = sprintf('%s-%d-%s-%04d', $k['prefix'], $cabangId, now()->format('Ymd'), $n++);
        } while ($k['model']::where('nomor_transaksi', $nomor)->exists());

        return $nomor;
    }

    private function tambahStok(int $cabangId, int $barangId, int $jumlah): void
    {
        $query = DB::table('stok_cabangs')
            ->where('cabang_id', $cabangId)
            ->where('barang_id', $barangId);

        if ($query->exists()) {
            $query->increment('stok', $jumlah, ['updated_at' => now()]);

            return;
        }

        DB::table('stok_cabangs')->insert([
            'barang_id' => $barangId,
            'cabang_id' => $cabangId,
            'stok' => $jumlah,
            'stok_minimum' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function kurangiStok(int $cabangId, int $barangId, int $jumlah): bool
    {
        return DB::table('stok_cabangs')
            ->where('cabang_id', $cabangId)
            ->where('barang_id', $barangId)
            ->where('stok', '>=', $jumlah)
            ->decrement('stok', $jumlah, ['updated_at' => now()]) > 0;
    }
}
