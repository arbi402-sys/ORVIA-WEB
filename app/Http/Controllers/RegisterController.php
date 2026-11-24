<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('beranda');
        }
        return view('register.index', ['title' => 'Daftar Akun — ORVIA']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username|min:3|max:30|alpha_dash',
            'bio' => 'nullable|string|max:200',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'role' => User::ROLE_USER,
            'verification_status' => 'none',
            'is_verified' => false,
            'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'bio' => $validated['bio'] ?? null,
            'password' => Hash::make($validated['password']),
            'newsletter' => $request->boolean('newsletter'),
            'avatar' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('beranda')->with('success', 'Registrasi berhasil! Selamat datang di ORVIA.');
    }
}
