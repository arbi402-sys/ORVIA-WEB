<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Aktivasi Kode Akses Lembaga — ORVIA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0B0B0B] text-white flex items-center justify-center p-4 sm:p-8" style="font-family: 'Poppins', sans-serif;">
    <div class="w-full max-w-lg bg-[#15151C] rounded-[28px] p-8 sm:p-10 border border-[#FACC15]/30 shadow-2xl">
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('beranda') }}" class="text-lg font-extrabold tracking-[0.2em] text-white">ORVIA</a>
            <span class="px-3 py-1 rounded-full bg-[#FACC15]/15 text-[#FACC15] text-[11px] font-semibold">Aktivasi Lembaga</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-white">Aktivasi Kode Akses Lembaga</h1>
        <p class="mt-1.5 text-xs sm:text-sm text-[#A4A4A4]">
            Masukkan <strong>Kode Akses Masuk Lembaga</strong> yang diterbitkan oleh Administrator ORVIA setelah verifikasi disetujui.
        </p>

        @if(session('success'))
            <div class="mt-4 rounded-xl bg-[#1E1529] border border-[#A332F7]/50 px-4 py-3 text-xs text-white">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="mt-4 rounded-xl bg-[#13222C] border border-[#37B9D6]/50 px-4 py-3 text-xs text-cyan-100">{{ session('info') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-4 rounded-xl bg-[#2A1216] border border-[#F31415]/50 px-4 py-3 text-xs text-red-200">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('institution.activate') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-white mb-1.5">Email Resmi Lembaga</label>
                <input type="email" name="email" value="{{ old('email', $prefillEmail) }}" required placeholder="official@lembaga.id"
                       class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#FACC15] rounded-[10px] px-4 py-2.5 text-sm text-white focus:outline-none">
                @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-[#FACC15] mb-1.5">Kode Akses Masuk Lembaga</label>
                <input type="text" name="access_code" value="{{ old('access_code') }}" required placeholder="ORV-XXXX-XXXX"
                       class="w-full bg-[#0B0B0B] border-2 border-[#FACC15]/50 focus:border-[#FACC15] rounded-[10px] px-4 py-3 text-base font-mono tracking-widest uppercase text-[#FACC15] placeholder-[#A4A4A4]/50 focus:outline-none">
                @error('access_code') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-white mb-1.5">Password Akun Lembaga</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-4 py-2.5 text-sm text-white focus:outline-none">
                @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full py-3 orvia-btn-gradient text-sm sm:text-base font-semibold cursor-pointer shadow-lg">
                Aktifkan & Masuk sebagai Lembaga Terverifikasi
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-white/[0.08] flex items-center justify-between text-xs text-[#A4A4A4]">
            <a href="{{ route('institution.register.form') }}" class="text-[#37B9D6] hover:underline">Belum mengajukan? Daftar Lembaga</a>
            <a href="{{ route('login') }}" class="hover:text-white">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
