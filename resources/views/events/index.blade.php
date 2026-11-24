@extends('layouts.orvia')

@section('title', 'Agenda Acara — ORVIA')

@section('content')
<div class="space-y-6">
    <!-- Header (Matches Figma Agenda Page 0:2038) -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-[32px] font-semibold leading-tight text-[#FEFEFE]">Agenda Acara</h1>
            <p class="text-xs text-[#A1A1A1] mt-1">Jelajahi berbagai agenda kreatif, pameran, kompetisi, dan kegiatan desain dari lembaga terverifikasi.</p>
        </div>
        @if(auth()->check() && auth()->user()->canCreateEvents())
            <a href="{{ route('create.index', ['tab' => 'event']) }}" class="px-5 py-2 orvia-btn-primary text-sm">
                + Buat Agenda Kegiatan
            </a>
        @endif
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('events.index') }}" class="relative">
        <input type="text"
               name="q"
               value="{{ $q }}"
               placeholder="Cari Acara....."
               class="w-full bg-[#15151C] border border-white/[0.06] rounded-full pl-6 pr-12 py-3 text-sm text-white placeholder-[#A1A1A1] focus:outline-none focus:border-[#A332F7]">
        <button type="submit" class="absolute right-5 top-1/2 -translate-y-1/2 text-[#A1A1A1] hover:text-white cursor-pointer" aria-label="Cari acara">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
        </button>
    </form>

    <!-- FilmFreeway-style Event Cards -->
    <div class="space-y-5">
        @forelse($events as $event)
            @php
                $isRegistered = in_array($event->id, $registeredEventIds, true);
                $isOpen = $event->isOpenForRegistration();
            @endphp
            <article class="orvia-card p-5 sm:p-6 flex flex-col lg:flex-row items-stretch justify-between gap-6 hover:border-white/10 transition">
                <!-- Left Poster + Info -->
                <div class="flex flex-col sm:flex-row items-start gap-5 flex-1 min-w-0">
                    <a href="{{ route('events.show', $event->slug) }}" class="w-full sm:w-[195px] h-[195px] rounded-[14px] overflow-hidden shrink-0 bg-[#0B0B0B] border border-white/10">
                        <img src="{{ $event->poster_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover hover:scale-[1.03] transition duration-300">
                    </a>

                    <div class="flex flex-col justify-between h-full py-1 min-w-0">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-block px-3 py-1 rounded-[5px] text-[10px] font-bold uppercase tracking-wider {{ $isOpen ? 'bg-[#A332F7] text-white' : 'bg-white/10 text-[#A1A1A1]' }}">
                                    {{ $isOpen ? 'PENDAFTARAN DIBUKA' : 'PENDAFTARAN DITUTUP' }}
                                </span>
                                <span class="text-xs text-[#A1A1A1]">{{ $event->event_type }}</span>
                            </div>

                            <a href="{{ route('events.show', $event->slug) }}" class="block mt-2.5 group">
                                <h2 class="text-xl sm:text-2xl font-bold text-[#FEFEFE] group-hover:text-[#A332F7] transition leading-snug">
                                    {{ $event->title }}
                                </h2>
                            </a>

                            <div class="mt-1.5 text-sm text-[#A1A1A1]">
                                {{ $event->event_date->translatedFormat('d F Y') }} • {{ $event->city }}
                            </div>

                            <div class="mt-2.5 text-xs text-[#A1A1A1] flex flex-wrap items-center gap-1.5">
                                <span>Penyelenggara:</span>
                                <x-author-credits :user="$event->organizer" :collaborators="$event->acceptedCollaborators" size="xs" />
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-[#A1A1A1]">
                            @if($event->registration_deadline)
                                Batas akhir: <span class="text-white/90">{{ $event->registration_deadline->translatedFormat('d F Y') }}</span>
                            @endif
                            <span class="mx-2">•</span>
                            <span>{{ $event->registrations_count }} pendaftar</span>
                        </div>
                    </div>
                </div>

                <!-- Right Divider & CTA Column (Matches Figma Agenda Page) -->
                <div class="lg:w-[180px] lg:border-l lg:border-[#A332F7]/20 lg:pl-6 flex flex-row lg:flex-col items-center justify-center gap-3 shrink-0">
                    @if($isRegistered)
                        <span class="px-5 py-2 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-semibold text-center w-full">
                            Terdaftar ✓
                        </span>
                    @elseif($isOpen)
                        @auth
                            <form method="POST" action="{{ route('events.register', $event->slug) }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full px-5 py-2 orvia-btn-primary text-xs cursor-pointer">
                                    Daftar Sekarang
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="w-full px-5 py-2 orvia-btn-primary text-xs text-center">
                                Daftar Sekarang
                            </a>
                        @endauth
                    @else
                        <span class="px-5 py-2 rounded-full bg-white/5 text-[#A1A1A1] text-xs font-semibold text-center w-full">
                            Ditutup
                        </span>
                    @endif

                    <a href="{{ route('events.show', $event->slug) }}" class="text-xs text-[#A1A1A1] hover:text-white transition">
                        Lihat Acara
                    </a>
                </div>
            </article>
        @empty
            <div class="orvia-card p-12 text-center text-sm text-[#A1A1A1]">
                Belum ada agenda acara yang sesuai pencarian.
            </div>
        @endforelse
    </div>

    <div>{{ $events->links() }}</div>
</div>
@endsection
