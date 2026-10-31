<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasTransaction extends Model
{
    protected $table = 'kas_transactions';

    protected $fillable = [
        'nomor_transaksi',
        'jenis',
        'tanggal',
        'deskripsi',
        'nominal',
        'account_id',
        'cash_account_id',
        'jurnal_id',
        'cabang_id',
        'user_id',
        'bukti_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_id');
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
