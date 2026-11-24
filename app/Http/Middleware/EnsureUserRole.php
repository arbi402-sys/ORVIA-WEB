<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     * Supports roles: admin, institution, user
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Silakan masuk terlebih dahulu.'], 401);
            }
            return redirect()->route('login')->with('error', 'Silakan masuk untuk mengakses fitur ini.');
        }

        // Pending institutions cannot perform write/restricted actions
        if ($user->isPendingInstitution()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun lembaga Anda belum diaktifkan dengan kode akses.'], 403);
            }
            return redirect()->route('institution.activate.form')
                ->with('error', 'Akun lembaga Anda perlu diaktifkan menggunakan Kode Akses dari Administrator.');
        }

        $allowed = false;
        foreach ($roles as $role) {
            if ($role === 'admin' && $user->isAdmin()) {
                $allowed = true;
                break;
            }
            if ($role === 'institution' && $user->isVerifiedInstitution()) {
                $allowed = true;
                break;
            }
            if ($role === 'user' && ($user->isRegularUser() || $user->isVerifiedInstitution() || $user->isAdmin())) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak memiliki hak akses untuk fitur ini.'], 403);
            }
            abort(403, 'Akses ditolak. Fitur ini hanya tersedia untuk peran tertentu.');
        }

        return $next($request);
    }
}
