<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && $user->status === 'Aktif' && $user->role === 'Anggota', 403, 'Fitur ini hanya tersedia untuk akun Anggota aktif.');

        $membership = $user->anggota;
        abort_unless(
            $membership && $membership->status_keanggotaan === 'Aktif' && $user->id_kelompok,
            403,
            'Keanggotaan Anda tidak aktif. Anda masih dapat mengakses profil dan mengajukan keanggotaan baru melalui aplikasi.'
        );

        return $next($request);
    }
}
