<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogLogin extends Model
{
    protected $table = 'log_logins';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    /**
     * Catat satu aktivitas. Kegagalan mencatat tidak boleh mengganggu proses login.
     */
    public static function catat(string $aksi, ?string $email = null, ?int $userId = null, ?int $cabangId = null): void
    {
        try {
            $request = request();

            $cabangId = $cabangId ?: (session('cabang_id') ? (int) session('cabang_id') : null);
            if ($cabangId && ! Cabang::whereKey($cabangId)->exists()) {
                $cabangId = null;
            }

            self::create([
                'user_id' => $userId ?? auth()->id(),
                'cabang_id' => $cabangId,
                'aksi' => $aksi,
                'email' => $email ?? auth()->user()?->email,
                'ip_address' => $request->ip(),
                'perangkat' => self::ringkasPerangkat((string) $request->userAgent()),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function ringkasPerangkat(string $ua): string
    {
        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/'), str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Browser lain',
        };

        $os = match (true) {
            str_contains($ua, 'iPhone'), str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Macintosh'), str_contains($ua, 'Mac OS X') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'OS lain',
        };

        return $browser . ' · ' . $os;
    }
}
