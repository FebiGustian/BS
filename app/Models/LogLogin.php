<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class LogLogin extends Model
{
    protected $table = 'log_logins';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['perangkat_baru' => 'boolean'];
    }

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
            $userId = $userId ?? auth()->id();

            $cabangId = $cabangId ?: (session('cabang_id') ? (int) session('cabang_id') : null);
            if ($cabangId && ! Cabang::whereKey($cabangId)->exists()) {
                $cabangId = null;
            }

            // Penanda perangkat: dibuat sekali, disimpan di browser selama 5 tahun
            $token = $request->cookie('device_id');
            if (! is_string($token) || strlen($token) < 20) {
                $token = Str::random(40);
                Cookie::queue('device_id', $token, 60 * 24 * 365 * 5);
            }
            $hash = hash('sha256', $token);

            $info = self::bacaPerangkat((string) $request->userAgent());

            $baru = $aksi === 'login' && $userId
                && ! self::where('device_hash', $hash)
                    ->where('user_id', $userId)
                    ->where('aksi', 'login')
                    ->exists();

            self::create([
                'user_id' => $userId,
                'cabang_id' => $cabangId,
                'aksi' => $aksi,
                'email' => $email ?? auth()->user()?->email,
                'ip_address' => $request->ip(),
                'perangkat' => $info['ringkas'],
                'jenis_perangkat' => $info['jenis'],
                'device_hash' => $hash,
                'perangkat_baru' => $baru,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function bacaPerangkat(string $ua): array
    {
        $jenis = match (true) {
            str_contains($ua, 'iPad') => 'Tablet',
            str_contains($ua, 'Android') && ! str_contains($ua, 'Mobile') => 'Tablet',
            str_contains($ua, 'iPhone'), str_contains($ua, 'Mobile') => 'HP',
            default => 'Komputer',
        };

        $model = null;

        if (preg_match('/(?:iPhone OS|CPU OS) (\d+)[_.](\d+)/', $ua, $m)) {
            $os = 'iOS ' . $m[1] . '.' . $m[2];
        } elseif (preg_match('/Android (\d+(?:\.\d+)?)(?:; ([^;)]+))?/', $ua, $m)) {
            $os = 'Android ' . $m[1];
            $kandidat = trim((string) preg_replace('/\s*Build\/.*/', '', $m[2] ?? ''));
            if (strlen($kandidat) > 2) {
                $model = $kandidat;
            }
        } elseif (preg_match('/Windows NT (\d+\.\d+)/', $ua, $m)) {
            $os = $m[1] === '10.0' ? 'Windows 10/11' : 'Windows';
        } elseif (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS X')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'CrOS')) {
            $os = 'ChromeOS';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux';
        } else {
            $os = 'OS tidak dikenal';
        }

        $browser = 'Browser tidak dikenal';
        $pola = [
            '/Edg(?:e|A|iOS)?\/(\d+)/' => 'Edge',
            '/OPR\/(\d+)/' => 'Opera',
            '/(?:Chrome|CriOS)\/(\d+)/' => 'Chrome',
            '/(?:Firefox|FxiOS)\/(\d+)/' => 'Firefox',
            '/Version\/(\d+)[\d.]*.*Safari/' => 'Safari',
        ];
        foreach ($pola as $regex => $nama) {
            if (preg_match($regex, $ua, $m)) {
                $browser = $nama . ' ' . $m[1];
                break;
            }
        }

        $osTeks = $model ? "{$os} ({$model})" : $os;

        return ['jenis' => $jenis, 'ringkas' => "{$osTeks} · {$browser}"];
    }
}
