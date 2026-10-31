<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\Cabang;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $cabangId = (int) session('cabang_id');
        $cabang = Cabang::find($cabangId);

        $stok = fn () => DB::table('stok_cabangs')
            ->join('barangs', 'barangs.id', '=', 'stok_cabangs.barang_id')
            ->whereNull('barangs.deleted_at')
            ->where('stok_cabangs.cabang_id', $cabangId);

        $totalProduk = $stok()->count();
        $nilaiStok = (float) $stok()->sum(DB::raw('stok_cabangs.stok * barangs.harga_beli'));
        $stokMenipis = $stok()
            ->where('stok_cabangs.stok', '>', 0)
            ->whereColumn('stok_cabangs.stok', '<=', 'stok_cabangs.stok_minimum')
            ->count();
        $stokHabis = $stok()->where('stok_cabangs.stok', 0)->count();

        $awal = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();

        $masukBulanIni = BarangMasuk::where('cabang_id', $cabangId)->whereBetween('tanggal', [$awal, $akhir]);
        $keluarBulanIni = BarangKeluar::where('cabang_id', $cabangId)->whereBetween('tanggal', [$awal, $akhir]);

        $nilaiMasuk = (float) (clone $masukBulanIni)->sum('total');
        $jumlahMasuk = (clone $masukBulanIni)->count();
        $nilaiKeluar = (float) (clone $keluarBulanIni)->sum('total');
        $jumlahKeluar = (clone $keluarBulanIni)->count();

        $grafik = [];
        for ($i = 6; $i >= 0; $i--) {
            $mulai = now()->startOfMonth()->subMonths($i);
            $selesai = $mulai->copy()->endOfMonth();
            $rentang = [$mulai->toDateString(), $selesai->toDateString()];

            $grafik[] = [
                'bulan' => $mulai->format('M'),
                'masuk' => (int) DB::table('detail_barang_masuks as d')
                    ->join('barang_masuks as t', 't.id', '=', 'd.barang_masuk_id')
                    ->where('t.cabang_id', $cabangId)
                    ->whereBetween('t.tanggal', $rentang)
                    ->sum('d.jumlah'),
                'keluar' => (int) DB::table('detail_barang_keluars as d')
                    ->join('barang_keluars as t', 't.id', '=', 'd.barang_keluar_id')
                    ->where('t.cabang_id', $cabangId)
                    ->whereBetween('t.tanggal', $rentang)
                    ->sum('d.jumlah'),
            ];
        }
        $maks = max(1, ...array_merge(array_column($grafik, 'masuk'), array_column($grafik, 'keluar')));

        $aktivitas = BarangMasuk::where('cabang_id', $cabangId)->latest()->take(5)->get()
            ->map(fn ($t) => [
                'jenis' => 'masuk',
                'id' => $t->id,
                'nomor' => $t->nomor_transaksi,
                'total' => (float) $t->total,
                'waktu' => $t->created_at->timestamp,
            ])
            ->concat(
                BarangKeluar::where('cabang_id', $cabangId)->latest()->take(5)->get()
                    ->map(fn ($t) => [
                        'jenis' => 'keluar',
                        'id' => $t->id,
                        'nomor' => $t->nomor_transaksi,
                        'total' => (float) $t->total,
                        'waktu' => $t->created_at->timestamp,
                    ])
            )
            ->sortByDesc('waktu')
            ->take(5)
            ->values();

        return view('dashboard', compact(
            'cabang', 'totalProduk', 'nilaiStok', 'stokMenipis', 'stokHabis',
            'nilaiMasuk', 'jumlahMasuk', 'nilaiKeluar', 'jumlahKeluar',
            'grafik', 'maks', 'aktivitas'
        ));
    }
}
