@extends('layouts.orvia')

@section('title', $user->name . ' — Profil ORVIA')

@section('content')
@php $isOwner = auth()->check() && auth()->id() === $user->id; @endphp
<div class="space-y-6">
    <!-- Profile Cover & Identity Card (Matches Figma PROFILE 1 & 2 0:1184, 0:2977) -->
    <section class="orvia-card overflow-hidden">
        <div class="h-44 sm:h-56 bg-gradient-to-r from-[#650EA6] via-[#A332F7] to-[#1D1D27] relative">
            @if($user->cover_url)
                <img src="{{ $user->cover_url }}" alt="" class="w-full h-full object-cover">
            @endif
        </div>

        <div class="px-6 sm:px-8 pb-6 -mt-14 relative flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-end gap-5">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover border-4 border-[#15151C] bg-[#15151C] shadow-xl shrink-0">

                <div class="pb-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl sm:text-3xl font-bold text-white leading-tight">{{ $user->name }}</h1>
                        <x-verified-badge :user="$user" size="md" />
                    </div>
                    <div class="text-xs sm:text-sm text-[#A1A1A1] mt-0.5 flex items-center gap-2">
                        <span>{{ $user->role_label }}</span>
                        <span>• @ {{ $user->username }}</span>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-5 text-sm">
                        <span><strong class="text-white">{{ $totalPostsCount }}</strong> <span class="text-[#A1A1A1]">Postingan</span></span>
                        <span><strong class="text-white">{{ $followersCount }}</strong> <span class="text-[#A1A1A1]">Pengikut</span></span>
                        <span><strong class="text-white">{{ $followingCount }}</strong> <span class="text-[#A1A1A1]">Mengikuti</span></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons: Edit Profile (owner) OR + Ikuti & Pesan (visitor) -->
            <div class="flex items-center gap-2.5 shrink-0">
                @if($isOwner)
                    <a href="{{ route('settings.index', ['section' => 'profile']) }}" class="px-7 py-2.5 orvia-btn-primary text-sm">
                        Edit Profile
                    </a>
                @else
                    @auth
                        <form method="POST" action="{{ $isFollowing ? route('profile.unfollow', $user->username ?: $user->id) : route('profile.follow', $user->username ?: $user->id) }}">
                            @csrf
                            <button type="submit" class="px-6 py-2.5 rounded-full text-sm font-semibold cursor-pointer {{ $isFollowing ? 'bg-white/10 text-white hover:bg-white/15' : 'bg-[#A332F7] text-white hover:bg-[#B64DFF]' }}">
                                {{ $isFollowing ? 'Mengikuti' : '+ Ikuti' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('messages.start', $user->id) }}">
                            @csrf
                            <button type="submit" class="px-6 py-2.5 rounded-full text-sm font-semibold bg-[#A332F7] text-white hover:bg-[#B64DFF] cursor-pointer">
                                Pesan
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2.5 orvia-btn-primary text-sm">+ Ikuti</a>
                        <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-full text-sm font-semibold bg-white/10 text-white">Pesan</a>
                    @endauth
                @endif
            </div>
        </div>
    </section>

    <!-- 2-Column Body: Left Info Cards + Right Tabbed Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Info Column -->
        <aside class="lg:col-span-4 space-y-5">
            <div class="orvia-card p-6 space-y-4">
                <h2 class="text-base font-semibold text-white">Tentang</h2>
                <p class="text-xs sm:text-sm text-[#A1A1A1] leading-relaxed">
                    {{ $user->bio ?: 'Belum ada deskripsi profil.' }}
                </p>

                <div class="pt-3 border-t border-white/[0.06] space-y-1.5 text-xs text-[#A1A1A1]">
                    @if($user->location || $user->city)
                        <div>📍 {{ $user->location ?: $user->city }}</div>
                    @endif
                    @if($user->email)
                        <div>✉ {{ $user->email }}</div>
                    @endif
                    @if($user->website)
                        <div>🌐 <a href="{{ $user->website }}" target="_blank" class="text-[#37B9D6] hover:underline">{{ $user->website }}</a></div>
                    @endif
                </div>
            </div>

            @if($isOwner)
                <div class="orvia-card p-6 space-y-3">
                    <h2 class="text-base font-semibold text-white">Ringkasan minggu ini</h2>
                    <p class="text-xs text-[#A1A1A1] leading-relaxed">
                        Pembaca, komentar, kolaborasi, dan pengikut baru ada di Dashboard.
                    </p>
                    <a href="{{ route('dashboard') }}" class="w-full py-2 orvia-btn-primary text-xs inline-flex items-center justify-center">
                        Buka Dashboard
                    </a>
                </div>
            @endif
        </aside>

        <!-- Right Tabs & Feed -->
        <div class="lg:col-span-8 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                @foreach(['activity' => 'Aktivitas', 'posts' => 'Postingan', 'events' => 'Agenda', 'articles' => 'Artikel', 'topics' => 'Topik'] as $k => $lbl)
                    <a href="{{ route('profile.show', [$user->username ?: $user->id, 'tab' => $k]) }}"
                       class="px-4 py-1.5 text-xs font-semibold {{ $tab === $k ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
                        {{ $lbl }}
                    </a>
                @endforeach
            </div>

            @if($tab === 'articles' || $tab === 'activity')
                @foreach($articles as $article)
                    <article class="orvia-card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5 text-xs text-[#A1A1A1]">
                            <img src="{{ $user->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                            <x-author-credits :user="$article->user" :collaborators="$article->acceptedCollaborators" size="xs" />
                            <span>• Artikel • {{ $article->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <a href="{{ route('articles.show', $article) }}" class="mt-3 flex flex-col sm:flex-row items-start justify-between gap-4 group">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold text-white group-hover:text-[#A332F7]">{{ $article->title }}</h3>
                                <p class="mt-1.5 text-xs text-[#A1A1A1] line-clamp-3">{{ $article->subtitle ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 180) }}</p>
                            </div>
                            @if($article->image_url)
                                <img src="{{ $article->image_url }}" alt="" class="w-full sm:w-44 h-28 rounded-xl object-cover shrink-0">
                            @endif
                        </a>
                        <div class="mt-4 flex items-center gap-5 text-xs text-[#A1A1A1]">
                            <span>♥ {{ $article->liked_by_count }}</span>
                            <span>💬 {{ $article->comments_count }}</span>
                            <span>👁 {{ number_format($article->views_count) }}</span>
                        </div>
                    </article>
                @endforeach
            @endif

            @if($tab === 'posts' || $tab === 'activity')
                @foreach($posts as $post)
                    <article class="orvia-card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5 text-xs text-[#A1A1A1]">
                            <img src="{{ $user->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                            <x-author-credits :user="$post->user" :collaborators="$post->acceptedCollaborators" size="xs" />
                            <span>• {{ $post->topic ? $post->topic->display_handle : 'Postingan' }} • {{ $post->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <a href="{{ route('posts.show', $post) }}" class="block mt-3 group">
                            <h3 class="text-lg font-bold text-white group-hover:text-[#A332F7]">{{ $post->title }}</h3>
                            @if($post->media_url)
                                <img src="{{ $post->media_url }}" alt="" class="mt-3 rounded-xl max-h-80 w-full object-cover">
                            @endif
                        </a>
                        <div class="mt-4 flex items-center gap-5 text-xs text-[#A1A1A1]">
                            <span>♥ {{ $post->liked_by_count }}</span>
                            <span>💬 {{ $post->comments_count }}</span>
                            <span>👁 {{ number_format($post->views_count) }}</span>
                        </div>
                    </article>
                @endforeach
            @endif

            @if($tab === 'events')
                @forelse($events as $event)
                    <a href="{{ route('events.show', $event->slug) }}" class="orvia-card p-5 flex items-center gap-4 hover:border-white/10 transition">
                        <img src="{{ $event->poster_url }}" alt="" class="w-20 h-20 rounded-xl object-cover shrink-0">
                        <div class="min-w-0">
                            <span class="px-2.5 py-0.5 rounded bg-[#A332F7] text-[10px] font-bold uppercase text-white">{{ $event->event_type }}</span>
                            <h3 class="mt-1.5 text-base font-bold text-white truncate">{{ $event->title }}</h3>
                            <p class="text-xs text-[#A1A1A1]">{{ $event->event_date->translatedFormat('d F Y') }} • {{ $event->city }}</p>
                        </div>
                    </a>
                @empty
                    <div class="orvia-card p-8 text-center text-xs text-[#A1A1A1]">Belum ada agenda kegiatan.</div>
                @endforelse
            @endif

            @if($tab === 'topics')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($topics->merge($joinedTopics)->unique('id') as $topic)
                        <a href="{{ route('topics.show', $topic->slug) }}" class="orvia-card p-4 flex items-center gap-3 hover:border-[#A332F7]/50 transition">
                            <img src="{{ $topic->avatar_url }}" alt="" class="w-11 h-11 rounded-full object-cover">
                            <div>
                                <div class="text-sm font-bold text-white">{{ $topic->display_handle }}</div>
                                <div class="text-xs text-[#A1A1A1]">{{ $topic->members_count }} anggota • {{ $topic->posts_count }} pos</div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full orvia-card p-8 text-center text-xs text-[#A1A1A1]">Belum tergabung pada topik mana pun.</div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
