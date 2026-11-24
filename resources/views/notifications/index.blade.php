@extends('layouts.orvia')

@section('title', 'Notifikasi — ORVIA')

@section('content')
<div class="space-y-6">
    <!-- Header (Matches Figma Notification 0:2427) -->
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-[32px] font-semibold leading-tight text-[#FEFEFE]">Notifikasi</h1>
            <p class="text-xs text-[#A1A1A1] mt-1">Aktivitas terbaru</p>
        </div>
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit" class="text-xs text-[#A332F7] hover:underline cursor-pointer">Tandai semua dibaca</button>
        </form>
    </div>

    <!-- Section: Baru -->
    <section class="space-y-3">
        <div class="flex items-center justify-between text-sm text-[#A1A1A1]">
            <span>Baru</span>
            <span>•••</span>
        </div>

        @forelse($newNotifications as $notif)
            <div class="orvia-card px-5 py-4 flex items-center justify-between gap-4 hover:border-white/10 transition">
                <a href="{{ $notif->action_url ?: '#' }}" class="flex items-center gap-3.5 flex-1 min-w-0">
                    <img src="{{ $notif->actor ? $notif->actor->avatar_url : asset('/images/icon/profil.png') }}"
                         alt=""
                         class="w-11 h-11 rounded-full object-cover border border-white/10 shrink-0">
                    <div class="min-w-0">
                        <div class="text-sm text-white flex flex-wrap items-center gap-1.5">
                            <span class="font-semibold inline-flex items-center gap-1">
                                {{ $notif->actor ? $notif->actor->name : 'Sistem ORVIA' }}
                                @if($notif->actor)
                                    <x-verified-badge :user="$notif->actor" size="xs" />
                                @endif
                            </span>
                            <span class="text-[#A1A1A1]">• {{ $notif->message }}</span>
                        </div>
                        <div class="text-xs text-[#A1A1A1] mt-0.5">{{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                </a>

                <div class="shrink-0">
                    @if($notif->type === 'follow' && $notif->actor && !in_array($notif->actor_id, $followingIds, true))
                        <form method="POST" action="{{ route('profile.follow', $notif->actor->username ?: $notif->actor->id) }}">
                            @csrf
                            <button type="submit" class="px-5 py-1.5 rounded-lg bg-[#A332F7] text-white text-xs font-semibold hover:bg-[#B64DFF] cursor-pointer">
                                Ikuti balik
                            </button>
                        </form>
                    @elseif($notif->thumbnail)
                        <img src="{{ $notif->thumbnail }}" alt="" class="w-16 h-11 rounded-lg object-cover border border-white/10">
                    @endif
                </div>
            </div>
        @empty
            <div class="orvia-card p-6 text-center text-xs text-[#A1A1A1]">Tidak ada notifikasi baru.</div>
        @endforelse
    </section>

    <!-- Section: Minggu ini -->
    @if($thisWeekNotifications->isNotEmpty())
        <section class="space-y-3 pt-2">
            <div class="flex items-center justify-between text-sm text-[#A1A1A1]">
                <span>Minggu ini</span>
                <span>•••</span>
            </div>

            @foreach($thisWeekNotifications as $notif)
                <div class="orvia-card px-5 py-4 flex items-center justify-between gap-4 hover:border-white/10 transition">
                    <a href="{{ $notif->action_url ?: '#' }}" class="flex items-center gap-3.5 flex-1 min-w-0">
                        <img src="{{ $notif->actor ? $notif->actor->avatar_url : asset('/images/icon/profil.png') }}"
                             alt=""
                             class="w-11 h-11 rounded-full object-cover border border-white/10 shrink-0">
                        <div class="min-w-0">
                            <div class="text-sm text-white flex flex-wrap items-center gap-1.5">
                                <span class="font-semibold inline-flex items-center gap-1">
                                    {{ $notif->actor ? $notif->actor->name : 'Sistem ORVIA' }}
                                    @if($notif->actor)
                                        <x-verified-badge :user="$notif->actor" size="xs" />
                                    @endif
                                </span>
                                <span class="text-[#A1A1A1]">• {{ $notif->message }}</span>
                            </div>
                            <div class="text-xs text-[#A1A1A1] mt-0.5">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                    </a>

                    <div class="shrink-0">
                        @if($notif->type === 'follow' && $notif->actor && !in_array($notif->actor_id, $followingIds, true))
                            <form method="POST" action="{{ route('profile.follow', $notif->actor->username ?: $notif->actor->id) }}">
                                @csrf
                                <button type="submit" class="px-5 py-1.5 rounded-lg bg-[#A332F7] text-white text-xs font-semibold hover:bg-[#B64DFF] cursor-pointer">
                                    Ikuti balik
                                </button>
                            </form>
                        @elseif($notif->thumbnail)
                            <img src="{{ $notif->thumbnail }}" alt="" class="w-16 h-11 rounded-lg object-cover border border-white/10">
                        @endif
                    </div>
                </div>
            @endforeach
        </section>
    @endif
</div>
@endsection
