<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        $cabangs = Cabang::where('is_active', true)
            ->orderByRaw("FIELD(wilayah, 'Jakarta', 'Jawa Barat')")
            ->orderBy('nama_cabang')
            ->get()
            ->groupBy('wilayah');

        return view('auth.login', compact('cabangs'));
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate([
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'cabang_id.required' => 'Pilih cabang terlebih dahulu.',
        ]);

        $credentials = ['email' => $data['email'], 'password' => $data['password']];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->onlyInput('email', 'cabang_id');
        }

        $user = Auth::user();

        if ($user->role !== 'admin' && (int) $user->cabang_id !== (int) $data['cabang_id']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['cabang_id' => 'Akun ini tidak terdaftar di cabang yang dipilih.'])
                ->onlyInput('email', 'cabang_id');
        }

        $request->session()->regenerate();
        $request->session()->put('cabang_id', (int) $data['cabang_id']);

        return redirect()->route('selamat-datang');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
