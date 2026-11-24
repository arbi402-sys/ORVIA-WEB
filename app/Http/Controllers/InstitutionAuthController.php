<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InstitutionAuthController extends Controller
{
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('beranda');
        }
        return view('register.institution', ['title' => 'Daftar Akun Lembaga — ORVIA']);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'institution_name' => 'required|string|max:120',
            'username' => 'required|string|unique:users,username|min:3|max:35|alpha_dash',
            'email' => 'required|email|unique:users,email',
            'location' => 'required|string|max:120',
            'website' => 'nullable|string|max:255',
            'bio' => 'required|string|max:500',
            'proof_image' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        $proofPath = $request->file('proof_image')->store('proofs', 'public');

        User::create([
            'role' => User::ROLE_INSTITUTION,
            'verification_status' => 'pending',
            'is_verified' => false,
            'institution_access_code' => null,
            'name' => $validated['institution_name'],
            'first_name' => $validated['institution_name'],
            'last_name' => '',
            'institution' => $validated['institution_name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'location' => $validated['location'],
            'website' => $validated['website'] ?? null,
            'bio' => $validated['bio'],
            'proof_image' => $proofPath,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('institution.activate.form', ['email' => $validated['email']])
            ->with('success', 'Pengajuan akun Lembaga berhasil dikirim! Status Anda saat ini Menunggu Verifikasi Administrator. Setelah disetujui, masukkan Kode Akses Masuk Lembaga di halaman ini.');
    }

    public function showActivateForm(Request $request)
    {
        return view('register.activate', [
            'title' => 'Aktivasi Kode Akses Lembaga — ORVIA',
            'prefillEmail' => $request->get('email', ''),
        ]);
    }

    public function activate(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'access_code' => 'required|string|max:64',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $data['email'])
            ->where('role', User::ROLE_INSTITUTION)
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Akun lembaga dengan email tersebut tidak ditemukan.'])->onlyInput('email');
        }

        if ($user->verification_status === 'pending') {
            return back()->withErrors(['access_code' => 'Pengajuan lembaga Anda masih menunggu persetujuan Administrator.'])->onlyInput('email');
        }

        if ($user->verification_status === 'rejected') {
            return back()->withErrors(['access_code' => 'Pengajuan lembaga Anda ditolak oleh Administrator.'])->onlyInput('email');
        }

        if (strtoupper(trim($data['access_code'])) !== strtoupper((string) $user->institution_access_code)) {
            return back()->withErrors(['access_code' => 'Kode Akses Masuk Lembaga tidak valid.'])->onlyInput('email');
        }

        if (!Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'Kata sandi yang Anda masukkan tidak sesuai.'])->onlyInput('email');
        }

        $user->update([
            'is_verified' => true,
            'access_code_used_at' => now(),
            'verified_at' => $user->verified_at ?? now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('beranda')
            ->with('success', 'Selamat! Akun Lembaga Anda aktif dengan Badge Verifikasi Kuning.');
    }
}
