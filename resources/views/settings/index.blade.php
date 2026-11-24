@extends('layouts.orvia')

@section('title', 'Pengaturan — ORVIA')

@section('content')
<div class="space-y-6">
    <!-- Header (Matches Figma Settings User 0:3452) -->
    <div>
        <h1 class="text-[32px] font-semibold leading-tight text-[#FEFEFE]">Pengaturan</h1>
        <p class="text-xs text-[#A1A1A1] mt-1">Kelola akun, privasi, dan preferensi pengguna.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Sub-Navigation Pills -->
        <aside class="lg:col-span-3 space-y-2">
            @foreach([
                'profile' => ['label' => 'Edit Profil', 'href' => '#sec-profile'],
                'appearance' => ['label' => 'Bahasa dan Tampilan', 'href' => '#sec-appearance'],
                'security' => ['label' => 'Akun dan Keamanan', 'href' => '#sec-security'],
                'danger' => ['label' => 'Hapus Akun', 'href' => '#sec-danger'],
            ] as $key => $item)
                <a href="{{ $item['href'] }}"
                   class="block px-5 py-2.5 rounded-full text-sm font-medium transition {{ $section === $key ? 'bg-[#A332F7] text-white' : 'bg-[#15151C] text-[#A1A1A1] hover:text-white hover:bg-white/[0.06]' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </aside>

        <!-- Right Settings Panels -->
        <div class="lg:col-span-9 space-y-6">
            <!-- 1. Edit Profil -->
            <section id="sec-profile" class="orvia-card p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-white">Edit Profil</h2>
                <p class="text-xs text-[#A1A1A1] mt-0.5">Identitas yang tampil di profil publik.</p>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
                    @csrf
                    @method('PUT')

                    <!-- Banner Preview -->
                    <div class="h-28 rounded-xl bg-[#650EA6] overflow-hidden relative">
                        @if($user->cover_url)
                            <img src="{{ $user->cover_url }}" alt="" class="w-full h-full object-cover">
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <img src="{{ $user->avatar_url }}" alt="" class="w-14 h-14 rounded-full object-cover border-2 border-[#A332F7]">
                        <label class="px-4 py-1.5 rounded-lg border border-white/20 text-xs text-white hover:border-[#A332F7] cursor-pointer">
                            Ganti Foto
                            <input type="file" name="avatar" accept="image/*" class="hidden">
                        </label>
                        <label class="px-4 py-1.5 rounded-lg border border-white/20 text-xs text-white hover:border-[#A332F7] cursor-pointer">
                            Ganti Banner
                            <input type="file" name="cover_image" accept="image/*" class="hidden">
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs text-[#A1A1A1] mb-1.5">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="orvia-input w-full px-4 py-2.5 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-[#A1A1A1] mb-1.5">Username</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" class="orvia-input w-full px-4 py-2.5 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-[#A1A1A1] mb-1.5">Tentang</label>
                        <textarea name="bio" rows="3" class="orvia-input w-full px-4 py-2.5 text-sm">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-[#A1A1A1] mb-1.5">Situs Web</label>
                            <input type="text" name="website" value="{{ old('website', $user->website) }}" placeholder="https://..." class="orvia-input w-full px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-[#A1A1A1] mb-1.5">Lokasi</label>
                            <input type="text" name="location" value="{{ old('location', $user->location) }}" placeholder="Bandung, Jawa Barat" class="orvia-input w-full px-4 py-2.5 text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 rounded-lg bg-[#A332F7] text-white text-xs font-semibold hover:bg-[#B64DFF] cursor-pointer">
                            Simpan Profil
                        </button>
                    </div>
                </form>
            </section>

            <!-- 2. Bahasa dan Tampilan (Matches Figma 3 theme cards: Terang, Gelap, Ikuti perangkat) -->
            <section id="sec-appearance" class="orvia-card p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-white">Bahasa dan Tampilan</h2>
                <p class="text-xs text-[#A1A1A1] mt-0.5">Pilih bahasa antarmuka dan tampilan layar.</p>

                <form method="POST" action="{{ route('settings.preferences') }}" class="mt-5 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs text-[#A1A1A1] mb-1.5">Bahasa</label>
                        <select name="language_preference" class="orvia-input w-full px-4 py-2.5 text-sm">
                            <option value="id" {{ ($user->language_preference ?? 'id') === 'id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                            <option value="en" {{ ($user->language_preference ?? 'id') === 'en' ? 'selected' : '' }}>English</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-[#A1A1A1] mb-2">Tema</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <label class="cursor-pointer space-y-2">
                                <div class="h-24 rounded-xl bg-white p-3 space-y-2 border-2 {{ ($user->theme_preference ?? 'dark') === 'light' ? 'border-[#A332F7]' : 'border-transparent' }}">
                                    <div class="h-3 w-16 rounded bg-[#A332F7]"></div>
                                    <div class="h-2 w-full rounded bg-gray-300"></div>
                                    <div class="h-2 w-4/5 rounded bg-gray-200"></div>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-[#A1A1A1]">
                                    <input type="radio" name="theme_preference" value="light" {{ ($user->theme_preference ?? 'dark') === 'light' ? 'checked' : '' }} class="accent-[#A332F7]">
                                    <span>Terang</span>
                                </div>
                            </label>

                            <label class="cursor-pointer space-y-2">
                                <div class="h-24 rounded-xl bg-[#0B0B0B] p-3 space-y-2 border-2 {{ ($user->theme_preference ?? 'dark') === 'dark' ? 'border-[#A332F7]' : 'border-white/10' }}">
                                    <div class="h-3 w-16 rounded bg-[#A332F7]"></div>
                                    <div class="h-2 w-full rounded bg-white/20"></div>
                                    <div class="h-2 w-4/5 rounded bg-white/15"></div>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-white">
                                    <input type="radio" name="theme_preference" value="dark" {{ ($user->theme_preference ?? 'dark') === 'dark' ? 'checked' : '' }} class="accent-[#A332F7]">
                                    <span>Gelap</span>
                                </div>
                            </label>

                            <label class="cursor-pointer space-y-2">
                                <div class="h-24 rounded-xl bg-[#A332F7] p-3 space-y-2 border-2 {{ ($user->theme_preference ?? 'dark') === 'system' ? 'border-white' : 'border-transparent' }}">
                                    <div class="h-3 w-16 rounded bg-white"></div>
                                    <div class="h-2 w-full rounded bg-white/75"></div>
                                    <div class="h-2 w-4/5 rounded bg-white/60"></div>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-[#A1A1A1]">
                                    <input type="radio" name="theme_preference" value="system" {{ ($user->theme_preference ?? 'dark') === 'system' ? 'checked' : '' }} class="accent-[#A332F7]">
                                    <span>Ikuti perangkat</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 rounded-lg bg-[#A332F7] text-white text-xs font-semibold hover:bg-[#B64DFF] cursor-pointer">
                            Simpan Tampilan
                        </button>
                    </div>
                </form>
            </section>

            <!-- 3. Akun dan Keamanan -->
            <section id="sec-security" class="orvia-card p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-white">Akun dan Keamanan</h2>
                <p class="text-xs text-[#A1A1A1] mt-0.5">Data masuk dan keamanan akun.</p>

                <form method="POST" action="{{ route('settings.security') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs text-[#A1A1A1] mb-1.5">Email Kontak</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="orvia-input w-full px-4 py-2.5 text-sm">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-[#A1A1A1] mb-1.5">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" placeholder="••••••••" class="orvia-input w-full px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-[#A1A1A1] mb-1.5">Kata Sandi Baru</label>
                            <input type="password" name="new_password" placeholder="Min. 8 karakter" class="orvia-input w-full px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-[#A1A1A1] mb-1.5">Konfirmasi Sandi Baru</label>
                            <input type="password" name="new_password_confirmation" placeholder="Ulangi sandi" class="orvia-input w-full px-3 py-2 text-sm">
                        </div>
                    </div>

                    <label class="flex items-center gap-2.5 text-xs text-white cursor-pointer pt-1">
                        <input type="checkbox" name="two_factor_enabled" value="1" {{ $user->two_factor_enabled ? 'checked' : '' }} class="accent-[#A332F7] w-4 h-4">
                        <span>Aktifkan verifikasi dua langkah</span>
                    </label>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 rounded-lg border border-white/20 text-xs font-semibold text-white hover:border-[#A332F7] cursor-pointer">
                            Simpan Keamanan
                        </button>
                    </div>
                </form>
            </section>

            <!-- 4. Hapus Akun -->
            <section id="sec-danger" class="orvia-card p-6 sm:p-8 border border-[#F31415]/30">
                <h2 class="text-lg font-semibold text-[#F31415]">Hapus Akun</h2>
                <p class="text-xs text-[#A1A1A1] mt-1">Tindakan ini permanen dan akan menghapus seluruh data profil Anda.</p>
                <form method="POST" action="{{ route('settings.account.destroy') }}" onsubmit="return confirm('Yakin ingin menghapus akun Anda secara permanen?')" class="mt-4 flex flex-col sm:flex-row gap-3">
                    @csrf
                    @method('DELETE')
                    <input type="password" name="confirm_password" required placeholder="Masukkan kata sandi Anda untuk konfirmasi" class="orvia-input flex-1 px-4 py-2 text-sm">
                    <button type="submit" class="px-5 py-2 rounded-lg bg-[#F31415] text-white text-xs font-semibold hover:bg-red-700 cursor-pointer">
                        Hapus Akun Permanen
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
