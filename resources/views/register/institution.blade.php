<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Pendaftaran Lembaga — ORVIA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0B0B0B] text-white flex items-center justify-center p-4 sm:p-8" style="font-family: 'Poppins', sans-serif;">
    <div class="w-full max-w-[1140px] bg-[#15151C] rounded-[32px] overflow-hidden shadow-[0_0_40px_rgba(0,0,0,0.45)] grid grid-cols-1 lg:grid-cols-12 border border-[#FACC15]/25">
        <!-- Left Info Panel -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#2B1248] via-[#1B122C] to-[#111118] p-8 sm:p-10 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-white/[0.06]">
            <div>
                <a href="{{ route('beranda') }}" class="text-xl font-extrabold tracking-[0.22em] text-white">ORVIA</a>

                <div class="mt-8 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FACC15]/15 border border-[#FACC15]/40 text-[#FACC15] text-xs font-semibold">
                    <span>✓</span> Akun Lembaga & Komunitas Resmi
                </div>

                <h2 class="mt-4 text-2xl sm:text-3xl font-bold leading-snug text-white">
                    Verifikasi Lembaga Anda & Buka Hak Akses Penuh
                </h2>

                <ul class="mt-6 space-y-3.5 text-xs sm:text-sm text-[#D0D0DF]">
                    <li class="flex items-start gap-2.5">
                        <span class="text-[#FACC15] font-bold">✓</span>
                        <span><strong>Badge Verifikasi Centang Kuning</strong> tampil konsisten di profil, postingan, artikel, agenda, dan pesan.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="text-[#A332F7] font-bold">✓</span>
                        <span>Hak eksklusif membuat <strong>Agenda Kegiatan</strong> & <strong>Topik Sub-Komunitas (O/)</strong>.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="text-[#37B9D6] font-bold">✓</span>
                        <span><strong>Kolaborasi Antar-Lembaga</strong> untuk menerbitkan Postingan, Artikel, dan Agenda bersama.</span>
                    </li>
                </ul>
            </div>

            <div class="mt-8 p-4 rounded-2xl bg-[#0B0B0B]/80 border border-white/10 text-xs text-[#A4A4A4] leading-relaxed">
                <strong class="text-white block mb-1">Alur Verifikasi Lembaga:</strong>
                1. Kirim formulir & dokumen bukti lembaga.<br>
                2. Status akun menjadi <em>Menunggu Verifikasi Admin</em>.<br>
                3. Administrator menyetujui & menerbitkan <strong>Kode Akses Masuk Lembaga</strong>.<br>
                4. Aktifkan akun di halaman <a href="{{ route('institution.activate.form') }}" class="text-[#FACC15] underline">Aktivasi Kode Akses</a>.
            </div>
        </div>

        <!-- Right Form -->
        <div class="lg:col-span-7 p-8 sm:p-11">
            <div class="flex items-center justify-between gap-2">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Daftar Lembaga / Komunitas</h1>
                <a href="{{ route('register') }}" class="text-xs text-[#37B9D6] hover:underline">Daftar User Biasa →</a>
            </div>
            <p class="text-xs text-[#A4A4A4] mt-1">Lengkapi identitas lembaga resmi dan unggah dokumen legalitas / surat pengantar.</p>

            @if($errors->any())
                <div class="mt-4 rounded-xl bg-[#2A1216] border border-[#F31415]/50 px-4 py-3 text-xs text-red-200">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('institution.register') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Nama Lembaga / Komunitas *</label>
                        <input type="text" name="institution_name" value="{{ old('institution_name') }}" required placeholder="HIMDKV Nusa Putra"
                               class="w-full bg-transparent border border-[#D7D7D7]/50 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Username Lembaga *</label>
                        <input type="text" name="username" value="{{ old('username') }}" required placeholder="himdkv-nusaputra"
                               class="w-full bg-transparent border border-[#D7D7D7]/50 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Email Resmi Lembaga *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="official@lembaga.id"
                               class="w-full bg-transparent border border-[#D7D7D7]/50 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Kota / Lokasi *</label>
                        <input type="text" name="location" value="{{ old('location') }}" required placeholder="Sukabumi, Jawa Barat"
                               class="w-full bg-transparent border border-[#D7D7D7]/50 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-white mb-1.5">Website / Media Sosial Resmi</label>
                    <input type="text" name="website" value="{{ old('website') }}" placeholder="https://lembaga.or.id"
                           class="w-full bg-transparent border border-[#D7D7D7]/50 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-white mb-1.5">Deskripsi Singkat Lembaga *</label>
                    <textarea name="bio" rows="2" required placeholder="Visi, bidang kreatif, dan kegiatan utama lembaga..."
                              class="w-full bg-transparent border border-[#D7D7D7]/50 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2 text-sm text-white focus:outline-none">{{ old('bio') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#FACC15] mb-1.5">Dokumen Bukti Lembaga (SK / Surat Pengantar / Kartu Resmi — JPG/PNG/PDF) *</label>
                    <input type="file" name="proof_image" required accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="w-full bg-[#0B0B0B] border border-[#FACC15]/40 rounded-[10px] px-3.5 py-2 text-xs text-[#A4A4A4]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Password *</label>
                        <input type="password" name="password" required placeholder="Min. 8 karakter"
                               class="w-full bg-transparent border border-[#D7D7D7]/50 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white mb-1.5">Konfirmasi Password *</label>
                        <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                               class="w-full bg-transparent border border-[#D7D7D7]/50 focus:border-[#A332F7] rounded-[10px] px-3.5 py-2.5 text-sm text-white focus:outline-none">
                    </div>
                </div>

                <label class="flex items-center gap-2.5 text-xs text-[#A4A4A4] cursor-pointer">
                    <input type="checkbox" name="terms" value="1" required class="w-4 h-4 accent-[#A332F7]">
                    <span>Saya menyatakan data lembaga ini valid dan bersedia melalui verifikasi Administrator ORVIA.</span>
                </label>

                <button type="submit" class="w-full py-3 orvia-btn-gradient text-sm sm:text-base font-semibold cursor-pointer shadow-lg">
                    Ajukan Verifikasi Lembaga
                </button>
            </form>

            <div class="mt-5 pt-4 border-t border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[#A4A4A4]">
                <span>Sudah menerima Kode Akses? <a href="{{ route('institution.activate.form') }}" class="text-[#FACC15] hover:underline font-semibold">Aktivasi di sini</a></span>
                <a href="{{ route('login') }}" class="text-[#37B9D6] hover:underline">Kembali ke Login</a>
            </div>
        </div>
    </div>
</body>
</html>
