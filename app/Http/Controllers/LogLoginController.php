<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\LogLogin;
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

        $hariIni = LogLogin::whereDate('created_at', today());
        $loginHariIni = (clone $hariIni)->where('aksi', 'login')->count();
        $penggunaHariIni = (clone $hariIni)->where('aksi', 'login')->distinct()->count('user_id');
        $gagalHariIni = (clone $hariIni)->whereIn('aksi', ['gagal', 'ditolak'])->count();

        $cabangs = Cabang::orderBy('nama_cabang')->get(['id', 'nama_cabang']);

        return view('activity-logs.index', compact('logs', 'cabangs', 'loginHariIni', 'penggunaHariIni', 'gagalHariIni'));
    }
}
