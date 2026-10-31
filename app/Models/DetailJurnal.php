<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailJurnal extends Model
{
    protected $table = 'detail_jurnals';

    protected $fillable = [
        'jurnal_id',
        'account_id',
        'debit',
        'kredit',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'kredit' => 'decimal:2',
        ];
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
