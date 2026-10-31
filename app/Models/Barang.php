<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kategori_id', 'sku', 'barcode', 'qr_code',
        'nama_barang', 'slug', 'deskripsi', 'merek',
        'satuan', 'harga_beli', 'harga_jual',
        'stok', 'stok_minimum', 'lokasi_penyimpanan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'stok' => 'integer',
            'stok_minimum' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function cabangs(): BelongsToMany
    {
        return $this->belongsToMany(Cabang::class, 'stok_cabangs')
            ->withPivot(['stok', 'stok_minimum'])
            ->withTimestamps();
    }

    /**
     * Ambil barang beserta stok milik satu cabang.
     * Hasilnya punya kolom tambahan: stok_cabang dan stok_minimum_cabang.
     */
    public function scopeDiCabang($query, int $cabangId)
    {
        return $query
            ->join('stok_cabangs', function ($join) use ($cabangId) {
                $join->on('stok_cabangs.barang_id', '=', 'barangs.id')
                    ->where('stok_cabangs.cabang_id', '=', $cabangId);
            })
            ->select(
                'barangs.*',
                'stok_cabangs.stok as stok_cabang',
                'stok_cabangs.stok_minimum as stok_minimum_cabang'
            );
    }
}
