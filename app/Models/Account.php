<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = [
        'code',
        'name',
        'type',
        'normal_balance',
        'parent_id',
        'description',
        'is_active',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    // Relasi ke akun induk
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    // Relasi ke akun anak
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }
}
