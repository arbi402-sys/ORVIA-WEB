<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstitutionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isPendingInstitution()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('institution.activate.form')
                ->with('error', 'Akun lembaga belum aktif. Masukkan Kode Akses Lembaga dari Administrator.');
        }

        return $next($request);
    }
}
