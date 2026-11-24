<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('beranda');
        }
        return view('login.index', ['title' => 'Masuk — ORVIA']);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Pre-check institution verification status before logging in
        $candidate = User::where('email', $credentials['email'])->first();
        if ($candidate && $candidate->isInstitution()) {
            if ($candidate->verification_status === 'rejected') {
                return back()->withErrors([
                    'email' => 'Pengajuan akun lembaga ini ditolak oleh Administrator.' .
                        ($candidate->rejection_reason ? ' Alasan: ' . $candidate->rejection_reason : ''),
                ])->onlyInput('email');
            }

            if ($candidate->verification_status === 'pending') {
                return back()->withErrors([
                    'email' => 'Akun lembaga Anda masih menunggu verifikasi Administrator. Setelah disetujui, Anda akan menerima Kode Akses Masuk Lembaga.',
                ])->onlyInput('email');
            }

            if (!$candidate->is_verified) {
                return redirect()->route('institution.activate.form', ['email' => $candidate->email])
                    ->with('info', 'Akun lembaga Anda sudah disetujui! Silakan masukkan Kode Akses Masuk Lembaga dari Administrator untuk aktivasi.');
            }
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.index'))->with('success', 'Selamat datang, Administrator!');
            }

            return redirect()->intended(route('beranda'))->with('success', 'Selamat datang kembali di ORVIA!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('success', 'Anda berhasil keluar.');
    }
}
