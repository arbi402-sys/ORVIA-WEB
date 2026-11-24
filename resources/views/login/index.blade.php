<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk — ORVIA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0B0B0B] text-white flex items-center justify-center p-4 sm:p-8" style="font-family: 'Poppins', sans-serif;">
    <div class="w-full max-w-[1100px] bg-[#15151C] rounded-[32px] overflow-hidden shadow-[0_0_40px_rgba(0,0,0,0.45)] grid grid-cols-1 lg:grid-cols-2 border border-white/[0.06]">
        <!-- Left Illustration Column (Matches Figma Login 3:84) -->
        <div class="relative hidden lg:flex flex-col justify-between bg-gradient-to-br from-[#F05A1F] via-[#D83B48] to-[#7D1A69] p-10 overflow-hidden min-h-[600px]">
            <a href="{{ route('beranda') }}" class="relative z-10 inline-flex items-center gap-2 text-white font-bold tracking-[0.22em] text-xl">
                @if(file_exists(public_path('images/orvia/orvia-logo.png')) && filesize(public_path('images/orvia/orvia-logo.png')) > 500)
                    <img src="{{ asset('images/orvia/orvia-logo.png') }}" alt="ORVIA" class="h-8 w-auto">
                @else
                    ORVIA
                @endif
            </a>

            @if(file_exists(public_path('images/orvia/auth-login.png')) && filesize(public_path('images/orvia/auth-login.png')) > 1000)
                <img src="{{ asset('images/orvia/auth-login.png') }}" alt="Ilustrasi ORVIA" class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="relative z-10 my-auto">
                    <h2 class="text-3xl font-bold leading-snug">Selamat Datang Kembali di Ruang Kreatif ORVIA</h2>
                    <p class="mt-3 text-sm text-white/85">Terhubung dengan komunitas desainer, publikasi artikel, agenda kreatif, dan kolaborasi lembaga.</p>
                </div>
            @endif
        </div>

        <!-- Right Form Column -->
        <div class="p-8 sm:p-12 flex flex-col justify-center">
            <div class="lg:hidden mb-6">
                <a href="{{ route('beranda') }}" class="text-xl font-extrabold tracking-[0.2em] text-white">ORVIA</a>
            </div>

            <h1 class="text-3xl sm:text-4xl font-semibold text-white">Masuk</h1>
            <p class="mt-1.5 text-sm text-[#A4A4A4]">Silakan masukkan detail akun Anda</p>

            @if(session('success'))
                <div class="mt-4 rounded-xl bg-[#1E1529] border border-[#A332F7]/50 px-4 py-3 text-xs text-white">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mt-4 rounded-xl bg-[#2A1216] border border-[#F31415]/50 px-4 py-3 text-xs text-red-200">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="mt-4 rounded-xl bg-[#13222C] border border-[#37B9D6]/50 px-4 py-3 text-xs text-cyan-100">{{ session('info') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-white mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@gmail.com"
                           class="w-full bg-transparent border-2 border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-4 py-3 text-sm text-white placeholder-[#A4A4A4] focus:outline-none transition">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2">Password</label>
                    <input type="password" name="password" required placeholder="**********"
                           class="w-full bg-transparent border-2 border-[#D7D7D7]/60 focus:border-[#A332F7] rounded-[10px] px-4 py-3 text-sm text-white placeholder-[#A4A4A4] focus:outline-none transition">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs sm:text-sm">
                    <label class="inline-flex items-center gap-2 text-[#A4A4A4] cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 accent-[#A332F7] rounded">
                        <span>Ingat saya</span>
                    </label>
                    <a href="{{ route('institution.activate.form') }}" class="text-[#FACC15] hover:underline font-medium">
                        Punya Kode Akses Lembaga?
                    </a>
                </div>

                <button type="submit" class="w-full py-3.5 orvia-btn-gradient text-base font-semibold cursor-pointer shadow-lg">
                    Masuk Sekarang
                </button>
            </form>

            <div class="mt-7 pt-5 border-t border-white/[0.08] space-y-2.5 text-center text-sm text-[#A4A4A4]">
                <p>
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="text-[#37B9D6] hover:underline font-medium">Daftar di sini</a>
                </p>
                <p class="text-xs">
                    Mewakili komunitas atau lembaga?
                    <a href="{{ route('institution.register.form') }}" class="text-[#FACC15] hover:underline font-medium">Daftar Akun Lembaga →</a>
                </p>
                <p class="text-xs pt-1">
                    <a href="{{ route('beranda') }}" class="text-[#A1A1A1] hover:text-white">← Lanjutkan sebagai Tamu (Viewer)</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
