<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Daftar Akun — ORVIA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0B0B0B] text-white flex items-center justify-center p-4 sm:p-8" style="font-family: 'Poppins', sans-serif;">
    <div class="w-full max-w-[1140px] bg-[#15151C] rounded-[32px] overflow-hidden shadow-[0_0_40px_rgba(0,0,0,0.45)] grid grid-cols-1 lg:grid-cols-2 border border-white/[0.06]">
        <!-- Left Illustration Column (Matches Figma Daftar 3:40) -->
        <div class="relative hidden lg:flex flex-col justify-between bg-gradient-to-br from-[#F05A1F] via-[#D83B48] to-[#7D1A69] p-10 overflow-hidden min-h-[700px]">
            <a href="{{ route('beranda') }}" class="relative z-10 inline-flex items-center gap-2 text-white font-bold tracking-[0.22em] text-xl">
                @if(file_exists(public_path('images/orvia/orvia-logo.png')) && filesize(public_path('images/orvia/orvia-logo.png')) > 500)
                    <img src="{{ asset('images/orvia/orvia-logo.png') }}" alt="ORVIA" class="h-8 w-auto">
                @else
                    ORVIA
                @endif
            </a>

            @if(file_exists(public_path('images/orvia/auth-register.png')) && filesize(public_path('images/orvia/auth-register.png')) > 1000)
                <img src="{{ asset('images/orvia/auth-register.png') }}" alt="Daftar ORVIA" class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="relative z-10 my-auto">
                    <h2 class="text-3xl font-bold leading-snug">Bergabung ke Ekosistem Kreatif ORVIA</h2>
                    <p class="mt-3 text-sm text-white/85">Tulis artikel, ikut berdiskusi, kirim pesan, dan daftar ke berbagai agenda kreatif nasional.</p>
                </div>
            @endif
        </div>

        <!-- Right Registration Form (Regular User) -->
        <div class="p-8 sm:p-11 flex flex-col justify-center">
            <div class="flex items-center justify-between gap-2">
                <h1 class="text-3xl font-bold text-white">Daftar Akun</h1>
                <a href="{{ route('institution.register.form') }}"
                   class="px-3.5 py-1.5 rounded-full bg-[#FACC15]/15 border border-[#FACC15]/40 text-[#FACC15] text-[11px] font-semibold hover:bg-[#FACC15] hover:text-[#0B0B0B] transition">
                    Daftar Lembaga →
                </a>
            </div>

            <div class="mt-3 flex items-center gap-3 text-xs text-[#A4A4A4]">
                <span class="h-px flex-1 bg-white/15"></span>
                <span>Akun Pengguna Biasa</span>
                <span class="h-px flex-1 bg-white/15"></span>
            </div>

            @if($errors->any())
                <div class="mt-4 rounded-xl bg-[#2A1216] border border-[#F31415]/50 px-4 py-3 text-xs text-red-200">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="mt-5 space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Nama Depan</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="Nama Depan"
                               class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white placeholder-[#A4A4A4] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Nama Belakang</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="Nama Belakang"
                               class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white placeholder-[#A4A4A4] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-white mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@gmail.com"
                           class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white placeholder-[#A4A4A4] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-white mb-1.5">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="Pilih username unik"
                           class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white placeholder-[#A4A4A4] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-white mb-1.5">Bio</label>
                    <input type="text" name="bio" value="{{ old('bio') }}" placeholder="Ceritakan tentang Anda"
                           class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white placeholder-[#A4A4A4] focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Password</label>
                        <input type="password" name="password" required placeholder="Min. 8 karakter"
                               class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white placeholder-[#A4A4A4] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                               class="w-full bg-transparent border border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white placeholder-[#A4A4A4] focus:outline-none">
                    </div>
                </div>

                <div class="space-y-2 pt-1 text-xs text-[#A4A4A4]">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="terms" value="1" required class="w-4 h-4 accent-[#A332F7]">
                        <span>Saya setuju dengan <span class="text-[#37B9D6]">Syarat dan Ketentuan</span></span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="newsletter" value="1" class="w-4 h-4 accent-[#A332F7]">
                        <span>Saya ingin menerima tips dan update terbaru</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 orvia-btn-gradient text-base font-semibold cursor-pointer shadow-lg">
                    Daftar Sekarang
                </button>
            </form>

            <p class="mt-6 pt-4 border-t border-white/[0.08] text-center text-sm text-[#A4A4A4]">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-[#37B9D6] hover:underline font-medium">Masuk di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
