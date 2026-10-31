<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\LogLogin;
use App\Models\Perangkat;
use Illuminate\Http\Request;

class LogLoginController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang boleh melihat halaman ini.');

        $query = LogLogin::with(['user', 'cabang'])->latest('id');

        if ($request->filled('aksi') && in_array($request->aksi, ['login', 'logout', 'gagal', 'ditolak'], true)) {
            $query->where('aksi', $request->aksi);
        }

        if ($request->filled('cabang_id')) {
            $query->where('cabang_id', (int) $request->cabang_id);
        }

        $logs = $query->paginate(20)->withQueryString();

        $namaPerangkat = Perangkat::whereIn('device_hash', $logs->pluck('device_hash')->filter()->unique()->values())
            ->pluck('nama', 'device_hash');

        $hariIni = LogLogin::whereDate('created_at', today());
        $loginHariIni = (clone $hariIni)->where('aksi', 'login')->count();
        $penggunaHariIni = (clone $hariIni)->where('aksi', 'login')->distinct()->count('user_id');
        $gagalHariIni = (clone $hariIni)->whereIn('aksi', ['gagal', 'ditolak'])->count();
        $baruHariIni = (clone $hariIni)->where('perangkat_baru', true)->count();

        $cabangs = Cabang::orderBy('nama_cabang')->get(['id', 'nama_cabang']);

        return view('activity-logs.index', compact(
            'logs', 'cabangs', 'namaPerangkat',
            'loginHariIni', 'penggunaHariIni', 'gagalHariIni', 'baruHariIni'
        ));
    }

    public function namai(Request $request)
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang boleh mengubah nama perangkat.');

        $data = $request->validate([
            'device_hash' => ['required', 'string', 'size:64', 'exists:log_logins,device_hash'],
            'nama' => ['nullable', 'string', 'max:60'],
        ]);

        Perangkat::updateOrCreate(
            ['device_hash' => $data['device_hash']],
            ['nama' => $data['nama'] ?: null]
        );

        return back()->with('success', 'Nama perangkat disimpan.');
    }
}
