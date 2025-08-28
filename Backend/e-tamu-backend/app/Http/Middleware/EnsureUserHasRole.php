<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penjaga Keamanan (Middleware)
 * Tugasnya adalah memeriksa peran (role) pengguna yang sedang login.
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Periksa apakah pengguna yang login memiliki peran yang sesuai.
        //    $request->user()->role adalah peran pengguna saat ini.
        //    $role adalah peran yang dibutuhkan (misalnya, 'super_admin').
        if ($request->user() && $request->user()->role === $role) {
            // 2. Jika perannya cocok, izinkan permintaan untuk melanjutkan.
            return $next($request);
        }

        // 3. Jika tidak cocok, tolak permintaan dengan pesan 'Unauthorized'.
        return response()->json(['message' => 'Akses ditolak. Anda tidak memiliki izin yang cukup.'], 403);
    }
}
