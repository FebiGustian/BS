<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurnal extends Model
{
    protected $table = 'jurnals';

    protected $fillable = [
        'nomor_jurnal',
        'tanggal',
        'deskripsi',
        'cabang_id',
        'user_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailJurnal::class, 'jurnal_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
