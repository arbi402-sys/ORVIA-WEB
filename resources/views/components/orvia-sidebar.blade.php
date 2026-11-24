@php
    $authUser = auth()->user();
    $isRoute = fn (string $pattern) => request()->routeIs($pattern);
@endphp

<!-- Desktop Left Floating Sidebar (Matches Figma ORVIA WEB: 220px card #15151C, rounded-16px, right vertical divider) -->
<aside class="hidden lg:flex lg:flex-col lg:w-[260px] lg:shrink-0 lg:sticky lg:top-0 lg:h-screen lg:py-8 lg:pl-8 lg:pr-5 border-r border-[#A332F7]/20">
    <div class="bg-[#15151C] rounded-[16px] flex flex-col h-full px-6 py-8 border border-white/[0.04]">
        <!-- Logo ORVIA -->
        <a href="{{ route('beranda') }}" class="flex items-center justify-center mb-10 group">
            @if(file_exists(public_path('images/orvia/orvia-logo.png')) && filesize(public_path('images/orvia/orvia-logo.png')) > 500)
                <img src="{{ asset('images/orvia/orvia-logo.png') }}" alt="ORVIA" class="h-9 w-auto object-contain group-hover:opacity-90 transition">
            @else
                <span class="text-2xl font-extrabold tracking-[0.22em] text-white">ORVIA</span>
            @endif
        </a>

        <!-- Navigation Links -->
        <nav class="flex flex-col gap-2 text-[15px]">
            <a href="{{ route('beranda') }}"
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition {{ $isRoute('beranda') || $isRoute('explore') || $isRoute('posts.*') || $isRoute('topics.*') ? 'text-[#A332F7] bg-[#A332F7]/10 font-semibold' : 'text-white/90 hover:text-white hover:bg-white/[0.04]' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5v-6h-6v6H4a1 1 0 01-1-1v-9.5z"/>
                </svg>
                <span>Beranda</span>
            </a>

            <a href="{{ route('articles.index') }}"
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition {{ $isRoute('articles.*') || $isRoute('community.index') ? 'text-[#A332F7] bg-[#A332F7]/10 font-semibold' : 'text-white/90 hover:text-white hover:bg-white/[0.04]' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2zm2 5h6m-6 4h6m-6 4h4"/>
                </svg>
                <span>Artikel</span>
            </a>

            <a href="{{ route('events.index') }}"
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition {{ $isRoute('events.*') ? 'text-[#A332F7] bg-[#A332F7]/10 font-semibold' : 'text-white/90 hover:text-white hover:bg-white/[0.04]' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                </svg>
                <span>Agenda</span>
            </a>

            @auth
                <a href="{{ route('messages.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ $isRoute('messages.*') ? 'text-[#A332F7] bg-[#A332F7]/10 font-semibold' : 'text-white/90 hover:text-white hover:bg-white/[0.04]' }}">
                    <span class="flex items-center gap-3.5">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                        </svg>
                        <span>Pesan</span>
                    </span>
                    @if(($globalUnreadMessages ?? 0) > 0)
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-bold rounded-full bg-[#A332F7] text-white" data-unread-messages-badge>
                            {{ $globalUnreadMessages > 99 ? '99+' : $globalUnreadMessages }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('notifications.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ $isRoute('notifications.*') ? 'text-[#A332F7] bg-[#A332F7]/10 font-semibold' : 'text-white/90 hover:text-white hover:bg-white/[0.04]' }}">
                    <span class="flex items-center gap-3.5">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                        </svg>
                        <span>Notifikasi</span>
                    </span>
                    @if(($globalUnreadNotifications ?? 0) > 0)
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-bold rounded-full bg-[#A332F7] text-white">
                            {{ $globalUnreadNotifications > 99 ? '99+' : $globalUnreadNotifications }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('dashboard') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ $isRoute('dashboard') ? 'text-[#A332F7] bg-[#A332F7]/10 font-semibold' : 'text-white/90 hover:text-white hover:bg-white/[0.04]' }}">
                    <span class="flex items-center gap-3.5">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M6 15l4-4 3 3 5-6"/>
                        </svg>
                        <span>Dashboard</span>
                    </span>
                    @if(($globalPendingCollaborations ?? 0) > 0)
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-bold rounded-full bg-[#FACC15] text-[#0B0B0B]" title="Undangan kolaborasi menunggu">
                            {{ $globalPendingCollaborations }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition {{ $isRoute('settings.*') ? 'text-[#A332F7] bg-[#A332F7]/10 font-semibold' : 'text-white/90 hover:text-white hover:bg-white/[0.04]' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6zm7.4-3a7.4 7.4 0 00-.1-1.2l2-1.5-2-3.5-2.4 1a7.4 7.4 0 00-2-.9L14.5 3h-5l-.4 2.9a7.4 7.4 0 00-2 .9l-2.4-1-2 3.5 2 1.5a7.4 7.4 0 000 2.4l-2 1.5 2 3.5 2.4-1a7.4 7.4 0 002 .9L9.5 21h5l.4-2.9a7.4 7.4 0 002-.9l2.4 1 2-3.5-2-1.5c.1-.4.1-.8.1-1.2z"/>
                    </svg>
                    <span>Pengaturan</span>
                </a>

                <a href="{{ route('profile.show', $authUser->username ?: $authUser->id) }}"
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition {{ $isRoute('profile.show') ? 'text-[#A332F7] bg-[#A332F7]/10 font-semibold' : 'text-white/90 hover:text-white hover:bg-white/[0.04]' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 9a7 7 0 0114 0"/>
                    </svg>
                    <span class="truncate">Profil</span>
                    <x-verified-badge :user="$authUser" size="xs" />
                </a>

                @if($authUser->isAdmin())
                    <a href="{{ route('admin.index') }}"
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ $isRoute('admin.*') ? 'text-[#FACC15] bg-[#FACC15]/10 font-semibold' : 'text-[#FACC15]/90 hover:bg-white/[0.04]' }}">
                        <span class="flex items-center gap-3.5">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4v5c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V7l8-4z"/>
                            </svg>
                            <span>Panel Admin</span>
                        </span>
                        @if(($globalPendingInstitutionsCount ?? 0) > 0)
                            <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-bold rounded-full bg-[#F31415] text-white">
                                {{ $globalPendingInstitutionsCount }}
                            </span>
                        @endif
                    </a>
                @endif
            @endauth
        </nav>

        <!-- Role-Aware "Buat" CTA Button (Matches white pill with purple text & chevron in Figma) -->
        <div class="mt-8 relative">
            @auth
                <button type="button"
                        onclick="document.getElementById('orviaCreateDropdown').classList.toggle('hidden')"
                        class="w-full h-9 bg-white text-[#A332F7] font-semibold rounded-full flex items-center justify-center gap-2 hover:bg-white/90 transition shadow-md cursor-pointer">
                    <span>Buat</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div id="orviaCreateDropdown"
                     class="hidden absolute left-0 right-0 mt-2 bg-[#1D1D27] border border-white/10 rounded-xl shadow-2xl p-1.5 z-40">
                    <a href="{{ route('create.index', ['tab' => 'post']) }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-white hover:bg-[#A332F7]/20 transition">
                        <span class="w-2 h-2 rounded-full bg-[#A332F7]"></span> Postingan
                    </a>
                    <a href="{{ route('create.index', ['tab' => 'article']) }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-white hover:bg-[#A332F7]/20 transition">
                        <span class="w-2 h-2 rounded-full bg-[#37B9D6]"></span> Artikel
                    </a>
                    @if($authUser->canCreateEvents())
                        <a href="{{ route('create.index', ['tab' => 'event']) }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-white hover:bg-[#A332F7]/20 transition">
                            <span class="w-2 h-2 rounded-full bg-[#FACC15]"></span> Agenda Kegiatan
                        </a>
                    @endif
                    @if($authUser->canCreateTopics())
                        <a href="{{ route('create.index', ['tab' => 'topic']) }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-white hover:bg-[#A332F7]/20 transition">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Topik Baru (O/)
                        </a>
                    @endif
                </div>
            @else
                <div class="space-y-2.5">
                    <a href="{{ route('login') }}"
                       class="w-full h-9 bg-[#A332F7] text-white font-semibold rounded-full flex items-center justify-center hover:bg-[#B64DFF] transition text-sm">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                       class="w-full h-9 bg-white text-[#A332F7] font-semibold rounded-full flex items-center justify-center hover:bg-white/90 transition text-sm">
                        Daftar Akun
                    </a>
                    <a href="{{ route('institution.register.form') }}"
                       class="block text-center text-xs text-[#FACC15] hover:underline pt-1">
                        Daftar sebagai Lembaga →
                    </a>
                </div>
            @endauth
        </div>

        <!-- Bottom User Chip & Logout -->
        <div class="mt-auto pt-5 border-t border-white/[0.06]">
            @auth
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('profile.show', $authUser->username ?: $authUser->id) }}" class="flex items-center gap-2.5 min-w-0">
                        <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser->name }}" class="w-8 h-8 rounded-full object-cover border border-white/10 shrink-0">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-white truncate flex items-center gap-1">
                                <span class="truncate">{{ $authUser->name }}</span>
                                <x-verified-badge :user="$authUser" size="xs" />
                            </div>
                            <div class="text-[11px] text-[#A1A1A1] truncate">@ {{ $authUser->username }}</div>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="p-1.5 rounded-lg text-[#A1A1A1] hover:text-[#F31415] hover:bg-white/[0.05] transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @else
                <div class="text-[11px] text-[#A1A1A1] text-center leading-relaxed">
                    Mode Tamu (Viewer)<br>
                    Masuk untuk berinteraksi & mengirim pesan.
                </div>
            @endauth
        </div>
    </div>
</aside>

<!-- Mobile Top Header & Bottom Bar -->
<header class="lg:hidden sticky top-0 z-40 bg-[#15151C]/95 backdrop-blur border-b border-white/[0.06] px-4 py-3 flex items-center justify-between">
    <a href="{{ route('beranda') }}" class="flex items-center gap-2">
        @if(file_exists(public_path('images/orvia/orvia-logo.png')) && filesize(public_path('images/orvia/orvia-logo.png')) > 500)
            <img src="{{ asset('images/orvia/orvia-logo.png') }}" alt="ORVIA" class="h-6 w-auto">
        @else
            <span class="text-lg font-extrabold tracking-[0.2em] text-white">ORVIA</span>
        @endif
    </a>
    <div class="flex items-center gap-2">
        @auth
            <a href="{{ route('create.index') }}" class="px-3 py-1 rounded-full bg-white text-[#A332F7] text-xs font-semibold">+ Buat</a>
            @if($authUser->isAdmin())
                <a href="{{ route('admin.index') }}" class="px-2.5 py-1 rounded-full bg-[#FACC15] text-[#0B0B0B] text-xs font-bold">Admin</a>
            @endif
            <a href="{{ route('profile.show', $authUser->username ?: $authUser->id) }}">
                <img src="{{ $authUser->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover border border-white/15">
            </a>
        @else
            <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-full bg-[#A332F7] text-white text-xs font-semibold">Masuk</a>
        @endauth
    </div>
</header>

<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-[#15151C]/95 backdrop-blur border-t border-white/[0.08] px-2 py-2 flex items-center justify-around text-[11px]">
    <a href="{{ route('beranda') }}" class="flex flex-col items-center gap-0.5 {{ $isRoute('beranda') ? 'text-[#A332F7]' : 'text-[#A1A1A1]' }}">
        <span>Beranda</span>
    </a>
    <a href="{{ route('articles.index') }}" class="flex flex-col items-center gap-0.5 {{ $isRoute('articles.*') ? 'text-[#A332F7]' : 'text-[#A1A1A1]' }}">
        <span>Artikel</span>
    </a>
    <a href="{{ route('events.index') }}" class="flex flex-col items-center gap-0.5 {{ $isRoute('events.*') ? 'text-[#A332F7]' : 'text-[#A1A1A1]' }}">
        <span>Agenda</span>
    </a>
    @auth
        <a href="{{ route('messages.index') }}" class="relative flex flex-col items-center gap-0.5 {{ $isRoute('messages.*') ? 'text-[#A332F7]' : 'text-[#A1A1A1]' }}">
            <span>Pesan</span>
            @if(($globalUnreadMessages ?? 0) > 0)
                <span class="absolute -top-1 -right-2.5 w-4 h-4 rounded-full bg-[#A332F7] text-white text-[9px] font-bold flex items-center justify-center">{{ $globalUnreadMessages }}</span>
            @endif
        </a>
        <a href="{{ route('notifications.index') }}" class="flex flex-col items-center gap-0.5 {{ $isRoute('notifications.*') ? 'text-[#A332F7]' : 'text-[#A1A1A1]' }}">
            <span>Notif</span>
        </a>
    @else
        <a href="{{ route('register') }}" class="flex flex-col items-center gap-0.5 text-[#A1A1A1]">
            <span>Daftar</span>
        </a>
    @endauth
</nav>
