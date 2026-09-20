<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('Auth.Login');
    }

    /**
     * Proses login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Cari pengguna berdasarkan username atau email
        $pengguna = Pengguna::where('username', $request->login)
            ->orWhere('email', $request->login)
            ->first();

        if (!$pengguna || !Hash::check($request->password, $pengguna->password)) {
            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'Username/email atau password salah.']);
        }

        if ($pengguna->status !== 'Aktif') {
            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'Akun Anda tidak aktif. Hubungi administrator.']);
        }

        Auth::login($pengguna, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectByRole($pengguna);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }

    /**
     * Redirect berdasarkan role pengguna.
     */
    private function redirectByRole(Pengguna $pengguna)
    {
        return match ($pengguna->role) {
            'PPL' => redirect()->route('admin.dashboard'),
            'Pengurus' => redirect()->route('pengurus.dashboard'),
            default => redirect()->route('login')->withErrors(['login' => 'Role tidak dikenali.']),
        };
    }
}
