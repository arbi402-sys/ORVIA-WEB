@extends('layouts.orvia')

@section('title', $event->title . ' — Agenda ORVIA')

@section('content')
@php $isOpen = $event->isOpenForRegistration(); @endphp
<div class="space-y-6">
    <!-- Top Hero Card (Matches Figma Agenda Page 2 0:2614) -->
    <section class="orvia-card overflow-hidden">
        <div class="h-48 sm:h-60 relative bg-[#1D1D27]">
            <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-[#15151C] via-transparent to-black/30"></div>
            <a href="{{ route('events.index') }}" class="absolute top-4 left-4 px-3.5 py-1.5 rounded-full bg-black/50 backdrop-blur text-xs text-white hover:bg-black/70 transition">
                ← Daftar Agenda
            </a>
        </div>

        <div class="px-6 sm:px-8 pb-6 -mt-12 relative flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-end gap-5">
                <img src="{{ $event->poster_url }}" alt="" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-[#15151C] bg-[#15151C] shadow-xl shrink-0">
                <div class="pb-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-[#FEFEFE] leading-snug">{{ $event->title }}</h1>
                    <div class="mt-1 text-xs sm:text-sm text-[#A1A1A1] flex flex-wrap items-center gap-2">
                        <span>Jenis Acara • <strong class="text-white/90">{{ $event->event_type }}</strong></span>
                        <span>• {{ $event->event_date->translatedFormat('d F Y') }}</span>
                        <span>• {{ $event->registrations_count }} Pendaftar</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                @if($isRegistered)
                    <span class="px-6 py-2.5 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-semibold">
                        Sudah Terdaftar ✓
                    </span>
                @elseif($isOpen)
                    @auth
                        <form method="POST" action="{{ route('events.register', $event->slug) }}">
                            @csrf
                            <button type="submit" class="px-8 py-2.5 orvia-btn-primary text-sm cursor-pointer">Daftar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-8 py-2.5 orvia-btn-primary text-sm">Daftar</a>
                    @endauth
                @else
                    <span class="px-6 py-2.5 rounded-full bg-white/10 text-[#A1A1A1] text-xs font-semibold">Pendaftaran Ditutup</span>
                @endif

                @if(auth()->check() && (auth()->id() === $event->user_id || auth()->user()->isAdmin()))
                    <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Hapus agenda ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-2 rounded-full text-xs border border-[#F31415]/40 text-[#F31415] hover:bg-[#F31415] hover:text-white cursor-pointer">Hapus</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <!-- 3-Column Detail Body (Matches Figma Agenda Page 2) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Sidebar: Organizer, Location, Share -->
        <aside class="lg:col-span-3 space-y-5">
            <div class="orvia-card p-5 space-y-5">
                <div>
                    <h3 class="text-sm font-semibold text-white">Penyelenggara:</h3>
                    <div class="mt-2 space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <img src="{{ $event->organizer->avatar_url }}" alt="" class="w-7 h-7 rounded-full object-cover">
                            <x-author-credits :user="$event->organizer" :collaborators="$event->acceptedCollaborators" size="xs" />
                        </div>
                        @auth
                            @if(auth()->id() !== $event->user_id)
                                <form method="POST" action="{{ route('messages.start', $event->user_id) }}" class="pt-1">
                                    @csrf
                                    <button type="submit" class="w-full py-1.5 rounded-full text-xs font-semibold border border-[#A332F7]/50 text-[#A332F7] hover:bg-[#A332F7] hover:text-white transition cursor-pointer">
                                        Kirim Pesan ke Penyelenggara
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="pt-4 border-t border-white/[0.06]">
                    <h3 class="text-sm font-semibold text-white">Lokasi:</h3>
                    <p class="mt-1 text-xs text-[#A1A1A1] leading-relaxed">
                        {{ $event->location_name ? $event->location_name . ', ' : '' }}{{ $event->city }}
                    </p>
                    <!-- Styled Map Card Preview -->
                    <div class="mt-3 h-28 rounded-xl bg-gradient-to-br from-[#23293A] via-[#1B2230] to-[#131824] border border-white/10 relative overflow-hidden flex items-center justify-center">
                        <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(to right, #37B9D6 1px, transparent 1px), linear-gradient(to bottom, #37B9D6 1px, transparent 1px); background-size: 20px 20px;"></div>
                        <div class="relative z-10 text-center px-3">
                            <span class="inline-block w-3 h-3 rounded-full bg-[#A332F7] ring-4 ring-[#A332F7]/30"></span>
                            <div class="text-[11px] font-semibold text-white mt-1">{{ $event->city }}</div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/[0.06]">
                    <h3 class="text-sm font-semibold text-white mb-2.5">Bagikan Acara Ini</h3>
                    <button type="button"
                            onclick="window.orviaShare('{{ route('events.show', $event->slug) }}', '{{ route('events.share', $event) }}', 'event-share-count')"
                            class="w-full py-2 px-4 rounded-full bg-[#A332F7]/15 border border-[#A332F7]/40 text-xs font-semibold text-white hover:bg-[#A332F7] transition cursor-pointer">
                        Salin Tautan Acara (<span id="event-share-count">{{ $event->share_count }}</span>)
                    </button>
                </div>
            </div>
        </aside>

        <!-- Center Content: Tabs (Semua / Tentang / Dokumentasi / Ulasan) -->
        <div class="lg:col-span-6 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                @foreach(['all' => 'Semua', 'about' => 'Tentang', 'gallery' => 'Dokumentasi', 'reviews' => 'Ulasan'] as $key => $label)
                    <a href="{{ route('events.show', [$event->slug, 'tab' => $key]) }}"
                       class="px-4 py-1.5 text-xs font-semibold {{ $tab === $key ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="orvia-card p-6 space-y-6">
                @if(in_array($tab, ['all', 'gallery']) && !empty($event->gallery))
                    <div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            @foreach(array_slice($event->gallery, 0, 4) as $img)
                                <div class="h-24 rounded-xl overflow-hidden bg-[#0B0B0B] border border-white/10">
                                    <img src="{{ str_starts_with($img, '/') ? $img : asset('storage/'.$img) }}" alt="" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(in_array($tab, ['all', 'about']))
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $event->title }}</h2>
                        <div class="mt-3 text-sm text-[#D5D5E2] leading-relaxed whitespace-pre-line">
                            {{ $event->description }}
                        </div>
                        @if($event->external_registration_url)
                            <div class="mt-4">
                                <a href="{{ $event->external_registration_url }}" target="_blank" rel="noopener" class="text-xs text-[#37B9D6] hover:underline">
                                    Tautan Pendaftaran Eksternal ↗
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                @if(in_array($tab, ['all', 'reviews']))
                    <div class="pt-4 border-t border-white/[0.06] space-y-4">
                        <h3 class="text-base font-semibold text-white">Ulasan & Pertanyaan ({{ $event->comments_count }})</h3>
                        @auth
                            <form method="POST" action="{{ route('events.comments.store', $event->slug) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="body" required placeholder="Tulis pertanyaan atau ulasan tentang acara ini..." class="orvia-input flex-1 px-4 py-2 text-xs">
                                <button type="submit" class="px-4 py-2 orvia-btn-primary text-xs cursor-pointer">Kirim</button>
                            </form>
                        @else
                            <p class="text-xs text-[#A1A1A1]"><a href="{{ route('login') }}" class="text-[#A332F7] hover:underline">Masuk</a> untuk menulis ulasan.</p>
                        @endauth

                        <div class="space-y-3">
                            @forelse($event->comments as $review)
                                <div class="rounded-xl bg-[#0B0B0B]/70 border border-white/[0.05] p-3.5">
                                    <div class="flex items-center gap-2 text-xs">
                                        <img src="{{ $review->user->avatar_url }}" alt="" class="w-6 h-6 rounded-full object-cover">
                                        <span class="font-semibold text-white inline-flex items-center gap-1">
                                            {{ $review->user->name }}
                                            <x-verified-badge :user="$review->user" size="xs" />
                                        </span>
                                        <span class="text-[#A1A1A1]">• {{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1.5 text-xs text-[#DCDCE8]">{{ $review->body }}</p>
                                </div>
                            @empty
                                <p class="text-xs text-[#A1A1A1]">Belum ada ulasan.</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                <!-- Invite Institution Collaborator (Owner Verified Institution only) -->
                @if(auth()->check() && auth()->id() === $event->user_id && auth()->user()->canCollaborate() && $verifiedInstitutions->isNotEmpty())
                    <div class="pt-4 border-t border-white/[0.06]">
                        <div class="p-4 rounded-xl bg-[#0B0B0B] border border-[#FACC15]/30">
                            <div class="text-xs font-semibold text-[#FACC15] mb-2">Undang Lembaga Kolaborator (Co-Organizer)</div>
                            <form method="POST" action="{{ route('collaborations.invite') }}" class="flex flex-col sm:flex-row gap-2">
                                @csrf
                                <input type="hidden" name="type" value="event">
                                <input type="hidden" name="id" value="{{ $event->id }}">
                                <select name="collaborator_id" required class="orvia-input px-3 py-2 text-xs flex-1">
                                    <option value="">Pilih lembaga terverifikasi...</option>
                                    @foreach($verifiedInstitutions as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="role_label" value="Co-Organizer" class="orvia-input px-3 py-2 text-xs sm:w-36">
                                <button type="submit" class="px-4 py-2 orvia-btn-primary text-xs cursor-pointer">Undang</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Rangkaian Jadwal Kegiatan Timeline (Matches Figma Agenda Page 2) -->
        <aside class="lg:col-span-3">
            <div class="orvia-card p-5">
                <h3 class="text-sm font-semibold text-white mb-5">Rangkaian Jadwal Kegiatan</h3>
                <ol class="relative border-l border-white/15 ml-2.5 space-y-6">
                    @foreach(($event->schedules ?: [['title' => 'Pelaksanaan Acara', 'date' => $event->event_date->translatedFormat('d F Y'), 'active' => true]]) as $step)
                        <li class="ml-5">
                            <span class="absolute -left-[7px] w-3.5 h-3.5 rounded-full {{ !empty($step['active']) ? 'bg-[#A332F7] ring-4 ring-[#A332F7]/25' : 'bg-[#A1A1A1]' }}"></span>
                            <div class="text-sm font-semibold text-white">{{ $step['title'] ?? 'Agenda' }}</div>
                            <div class="text-xs text-[#A1A1A1] mt-0.5">{{ $step['date'] ?? '' }}</div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </aside>
    </div>
</div>
@endsection
