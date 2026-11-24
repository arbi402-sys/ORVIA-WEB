<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $section = $request->get('section', 'profile');
        return view('settings.index', compact('user', 'section'));
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'language_preference' => 'required|in:id,en',
            'theme_preference' => 'required|in:dark,light,system',
        ]);

        $user->update($data);

        return back()->with('success', 'Preferensi bahasa & tampilan berhasil disimpan.');
    }

    public function updateSecurity(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'two_factor_enabled' => 'nullable|boolean',
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $update = [
            'email' => $data['email'],
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
        ];

        if (!empty($data['new_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak sesuai.']);
            }
            $update['password'] = Hash::make($data['new_password']);
        }

        $user->update($update);

        return back()->with('success', 'Pengaturan akun & keamanan berhasil diperbarui.');
    }

    public function destroyAccount(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'confirm_password' => 'required|string',
        ]);

        if (!Hash::check($request->input('confirm_password'), $user->password)) {
            return back()->withErrors(['confirm_password' => 'Kata sandi konfirmasi tidak cocok.']);
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Akun Anda telah dihapus.');
    }
}
