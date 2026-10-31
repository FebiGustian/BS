<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barang_cabang') ||
            !Schema::hasTable('stok_cabangs')) {
            throw new RuntimeException('Tabel sumber atau tujuan tidak ditemukan.');
        }

        $dataLama = DB::table('barang_cabang')->get();

        if ($dataLama->isEmpty()) {
            throw new RuntimeException('Data stok lama tidak ditemukan.');
        }

        DB::transaction(function () use ($dataLama) {
            foreach ($dataLama as $data) {
                $stokBaru = DB::table('stok_cabangs')
                    ->where('barang_id', $data->barang_id)
                    ->where('cabang_id', $data->cabang_id)
                    ->first();

                if ($stokBaru) {
                    if (
                        $stokBaru->stok != $data->stok ||
                        $stokBaru->stok_minimum != $data->stok_minimum
                    ) {
                        throw new RuntimeException(
                            "Data stok barang ID {$data->barang_id} berbeda."
                        );
                    }

                    continue;
                }

                DB::table('stok_cabangs')->insert([
                    'barang_id' => $data->barang_id,
                    'cabang_id' => $data->cabang_id,
                    'stok' => $data->stok,
                    'stok_minimum' => $data->stok_minimum,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Data hasil pemindahan tidak dihapus saat rollback.
        // Tabel lama tetap dipertahankan sebagai cadangan.
    }
};