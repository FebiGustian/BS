<?php

namespace App\Listeners;

use App\Models\LogLogin;
use Illuminate\Auth\Events\Failed;

class CatatLoginGagal
{
    public function handle(Failed $event): void
    {
        LogLogin::catat(
            'gagal',
            $event->credentials['email'] ?? null,
            $event->user?->getAuthIdentifier(),
            request()->integer('cabang_id') ?: null
        );
    }
}
