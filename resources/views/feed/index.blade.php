@extends('layouts.orvia')

@section('title', 'Beranda — ORVIA')

@section('content')
<div class="space-y-6">
    <!-- Hero Banner (Matches Figma Beranda 0:1461 purple rounded-16px card) -->
    <section class="relative overflow-hidden rounded-[16px] bg-gradient-to-r from-[#A332F7] via-[#962AE8] to-[#7B1ED1] px-6 sm:px-10 py-8 sm:py-10 shadow-xl">
        <div class="max-w-3xl">
            <h1 class="text-3xl sm:text-5xl font-bold leading-[1.08] tracking-tight text-[#FEFEFE]">
                Ruang Kolaborasi<br>Kreatif & Komunitas
            </h1>
            <p class="mt-3 text-sm sm:text-base text-white/85 max-w-xl leading-relaxed">
                Temukan diskusi topik kreatif, publikasi artikel mendalam, dan agenda kegiatan dari berbagai lembaga terverifikasi di seluruh Indonesia.
            </p>
        </div>
        <div class="mt-6 sm:mt-0 sm:absolute sm:right-8 sm:bottom-7 flex flex-wrap items-center gap-3">
            <a href="{{ route('events.index') }}"
               class="inline-flex items-center justify-center px-6 py-2.5 rounded-full bg-white text-[#A332F7] font-semibold text-sm hover:bg-white/90 transition shadow-md">
                Lihat Agenda Acara
            </a>
        </div>
    </section>

    <!-- Filter Pills + Search Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('beranda', array_filter(['tab' => 'for-you', 'q' => $q])) }}"
               class="px-4 py-1.5 text-sm font-medium {{ $tab === 'for-you' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
                Untuk Anda
            </a>
            <a href="{{ route('beranda', array_filter(['tab' => 'popular', 'q' => $q])) }}"
               class="px-4 py-1.5 text-sm font-medium {{ $tab === 'popular' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
                Populer
            </a>
            @auth
                <a href="{{ route('beranda', array_filter(['tab' => 'following', 'q' => $q])) }}"
                   class="px-4 py-1.5 text-sm font-medium {{ $tab === 'following' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
                    Mengikuti
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-4 py-1.5 text-sm font-medium orvia-pill" title="Masuk untuk melihat feed Mengikuti">
                    Mengikuti
                </a>
            @endauth
            <a href="{{ route('topics.index') }}"
               class="px-4 py-1.5 text-sm font-medium orvia-pill">
                Jelajahi Topik
            </a>
        </div>

        <form method="GET" action="{{ route('beranda') }}" class="relative w-full md:w-[290px]">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text"
                   name="q"
                   value="{{ $q }}"
                   placeholder="Cari Topik atau diskusi..."
                   class="w-full bg-[#15151C] border border-white/10 rounded-full pl-4 pr-10 py-2 text-xs text-white placeholder-[#A1A1A1] focus:outline-none focus:border-[#A332F7]">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#A1A1A1] hover:text-white cursor-pointer" aria-label="Cari">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
            </button>
        </form>
    </div>

    <!-- 2-Column Feed Layout: Main Feed Left (approx 72%) + Upcoming Events Right Sidebar (approx 28%) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        <!-- Main Feed Column -->
        <div class="xl:col-span-8 space-y-5">
            @forelse($posts as $post)
                @php
                    $isLiked = in_array($post->id, $userLikedPostIds, true);
                    $isJoinedTopic = $post->topic && in_array($post->topic->id, $userJoinedTopicIds, true);
                    $isFollowingAuthor = $post->user && in_array($post->user->id, $userFollowingIds, true);
                @endphp
                <article class="orvia-card p-5 sm:p-6 transition hover:border-white/10">
                    <!-- Post Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($post->topic)
                                <a href="{{ route('topics.show', $post->topic->slug) }}" class="shrink-0">
                                    <img src="{{ $post->topic->avatar_url }}" alt="{{ $post->topic->name }}" class="w-10 h-10 rounded-full object-cover border border-white/10">
                                </a>
                            @elseif($post->user)
                                <a href="{{ route('profile.show', $post->user->username ?: $post->user->id) }}" class="shrink-0">
                                    <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->name }}" class="w-10 h-10 rounded-full object-cover border border-white/10">
                                </a>
                            @endif

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5 text-sm">
                                    @if($post->topic)
                                        <a href="{{ route('topics.show', $post->topic->slug) }}" class="font-semibold text-white hover:text-[#A332F7]">
                                            {{ $post->topic->display_handle }}
                                        </a>
                                        <span class="text-[#A1A1A1]">•</span>
                                    @endif
                                    <x-author-credits :user="$post->user" :collaborators="$post->acceptedCollaborators" size="sm" />
                                </div>
                                <div class="text-xs text-[#A1A1A1] mt-0.5">
                                    {{ $post->topic ? 'Topik' : 'Postingan' }} • {{ $post->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <!-- Action Pill (Bergabung / Ikuti) -->
                        <div class="flex items-center gap-2 shrink-0">
                            @if($post->topic)
                                @auth
                                    <form method="POST" action="{{ route('topics.join', $post->topic->slug) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-4 py-1.5 rounded-full text-xs font-semibold transition cursor-pointer {{ $isJoinedTopic ? 'bg-white/10 text-white hover:bg-white/15' : 'bg-[#A332F7] text-white hover:bg-[#B64DFF]' }}">
                                            {{ $isJoinedTopic ? 'Bergabung ✓' : 'Bergabung' }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="px-4 py-1.5 rounded-full text-xs font-semibold bg-[#A332F7] text-white hover:bg-[#B64DFF]">
                                        Bergabung
                                    </a>
                                @endauth
                            @elseif($post->user && (!auth()->check() || auth()->id() !== $post->user_id))
                                @auth
                                    <form method="POST" action="{{ $isFollowingAuthor ? route('profile.unfollow', $post->user->username ?: $post->user->id) : route('profile.follow', $post->user->username ?: $post->user->id) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-4 py-1.5 rounded-full text-xs font-semibold transition cursor-pointer {{ $isFollowingAuthor ? 'bg-white/10 text-white' : 'bg-[#A332F7] text-white hover:bg-[#B64DFF]' }}">
                                            {{ $isFollowingAuthor ? 'Mengikuti' : 'Ikuti' }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="px-4 py-1.5 rounded-full text-xs font-semibold bg-[#A332F7] text-white hover:bg-[#B64DFF]">
                                        Ikuti
                                    </a>
                                @endauth
                            @endif
                        </div>
                    </div>

                    <!-- Post Body & Media -->
                    <a href="{{ route('posts.show', $post) }}" class="block mt-3.5 group">
                        <h2 class="text-lg font-semibold text-[#FEFEFE] group-hover:text-[#A332F7] transition">
                            {{ $post->title }}
                        </h2>
                        @if($post->body)
                            <p class="mt-1.5 text-sm text-[#D4D4DF] line-clamp-3 leading-relaxed">
                                {{ $post->body }}
                            </p>
                        @endif

                        @if($post->media_url)
                            <div class="mt-3.5 rounded-[14px] overflow-hidden bg-[#0B0B0B] border border-white/[0.05]">
                                @if($post->media_type === 'video')
                                    <video src="{{ $post->media_url }}" controls class="w-full max-h-[420px] object-contain"></video>
                                @else
                                    <img src="{{ $post->media_url }}" alt="{{ $post->title }}" class="w-full max-h-[420px] object-cover">
                                @endif
                            </div>
                        @endif
                    </a>

                    <!-- Post Metrics & Interactions -->
                    <div class="mt-4 pt-3 border-t border-white/[0.05] flex items-center justify-between gap-4 text-xs text-[#A1A1A1]">
                        <div class="flex items-center gap-5">
                            @auth
                                <button type="button"
                                        onclick="window.orviaToggleLike('{{ route('posts.like', $post) }}', this, 'post-like-{{ $post->id }}')"
                                        class="inline-flex items-center gap-2 hover:text-white transition cursor-pointer">
                                    <span data-like-circle class="w-7 h-7 rounded-full border flex items-center justify-center transition {{ $isLiked ? 'bg-[#A332F7] border-[#A332F7] text-white' : 'border-white/25 text-white/80' }}">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </span>
                                    <span id="post-like-{{ $post->id }}">{{ $post->liked_by_count }}</span>
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 hover:text-white transition" title="Masuk untuk menyukai">
                                    <span class="w-7 h-7 rounded-full border border-white/25 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </span>
                                    <span>{{ $post->liked_by_count }}</span>
                                </a>
                            @endauth

                            <a href="{{ route('posts.show', $post) }}#comments" class="inline-flex items-center gap-2 hover:text-white transition">
                                <span class="w-7 h-7 rounded-full border border-white/25 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8m-8 4h5m-9 6l2.5-2.5H19a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14z"/>
                                    </svg>
                                </span>
                                <span>{{ $post->comments_count }}</span>
                            </a>

                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>{{ number_format($post->views_count) }}</span>
                            </span>
                        </div>

                        <!-- Share (Allowed for ALL including Guest) -->
                        <button type="button"
                                onclick="window.orviaShare('{{ route('posts.show', $post) }}', '{{ route('posts.share', $post) }}', 'post-share-{{ $post->id }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-white/15 text-[#A1A1A1] hover:text-white hover:border-[#A332F7] transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342a3 3 0 110-2.684m6.632-3.316a3 3 0 105.368-2.684 3 3 0 00-5.368 2.684zm0 10.684a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684zM8.684 10.658l6.632-3.316m-6.632 6l6.632 3.316"/>
                            </svg>
                            <span>Bagikan</span>
                            <span id="post-share-{{ $post->id }}" class="text-white/70">{{ $post->share_count }}</span>
                        </button>
                    </div>
                </article>
            @empty
                <div class="orvia-card p-10 text-center">
                    <p class="text-[#A1A1A1]">Belum ada postingan pada filter ini.</p>
                    @auth
                        <a href="{{ route('create.index', ['tab' => 'post']) }}" class="mt-4 inline-flex px-5 py-2 orvia-btn-primary text-sm">
                            Buat Postingan Pertama
                        </a>
                    @endauth
                </div>
            @endforelse

            <!-- Highlighted Articles Strip in Feed (as shown at bottom of Figma Beranda) -->
            @if($featuredArticles->isNotEmpty() && $posts->currentPage() === 1 && $q === '')
                <div class="pt-2 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-[#A1A1A1]">Publikasi Artikel Pilihan</h3>
                        <a href="{{ route('articles.index') }}" class="text-xs text-[#A332F7] hover:underline">Lihat semua artikel →</a>
                    </div>
                    @foreach($featuredArticles as $article)
                        <article class="orvia-card p-5 flex flex-col sm:flex-row items-start justify-between gap-4 hover:border-white/10 transition">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2.5 text-xs text-[#A1A1A1]">
                                    <img src="{{ $article->user->avatar_url }}" alt="" class="w-6 h-6 rounded-full object-cover">
                                    <x-author-credits :user="$article->user" :collaborators="$article->acceptedCollaborators" size="xs" />
                                    <span>• Artikel • {{ $article->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                                <a href="{{ route('articles.show', $article) }}" class="block mt-2 group">
                                    <h4 class="text-base font-bold text-white group-hover:text-[#A332F7] transition">{{ $article->title }}</h4>
                                    <p class="mt-1 text-xs text-[#A1A1A1] line-clamp-2">{{ $article->subtitle ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 160) }}</p>
                                </a>
                            </div>
                            @if($article->image_url)
                                <a href="{{ route('articles.show', $article) }}" class="w-full sm:w-36 h-24 rounded-xl overflow-hidden shrink-0 bg-[#0B0B0B]">
                                    <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="pt-2">
                {{ $posts->links() }}
            </div>
        </div>

        <!-- Right Sidebar Column (Matches Figma Beranda "Agenda Mendatang" widget + Popular Topics) -->
        <aside class="xl:col-span-4 space-y-5">
            <!-- Agenda Mendatang Widget -->
            <div class="orvia-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-white">Agenda Mendatang</h2>
                    <a href="{{ route('events.index') }}" class="text-xs text-[#A1A1A1] hover:text-[#A332F7] transition">Lihat Semua</a>
                </div>

                <div class="space-y-3.5">
                    @forelse($upcomingEvents as $event)
                        <a href="{{ route('events.show', $event->slug) }}" class="flex items-center gap-3 group p-1.5 -mx-1.5 rounded-xl hover:bg-white/[0.04] transition">
                            <img src="{{ $event->poster_url }}" alt="{{ $event->title }}" class="w-12 h-12 rounded-lg object-cover shrink-0 border border-white/10">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-white group-hover:text-[#A332F7] transition truncate">
                                    {{ $event->title }}
                                </div>
                                <div class="text-[11px] text-[#A1A1A1] mt-0.5 truncate">
                                    {{ $event->event_date->translatedFormat('d F Y') }} • {{ $event->city }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-[#A1A1A1]">Belum ada agenda mendatang.</p>
                    @endforelse
                </div>
            </div>

            <!-- Topik Populer Widget -->
            <div class="orvia-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-white">Topik Populer</h2>
                    <a href="{{ route('topics.index') }}" class="text-xs text-[#A1A1A1] hover:text-[#A332F7] transition">Semua</a>
                </div>
                <div class="space-y-3">
                    @foreach($popularTopics as $topic)
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('topics.show', $topic->slug) }}" class="flex items-center gap-2.5 min-w-0 group">
                                <img src="{{ $topic->avatar_url }}" alt="{{ $topic->name }}" class="w-8 h-8 rounded-full object-cover border border-white/10 shrink-0">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-white group-hover:text-[#A332F7] truncate">{{ $topic->display_handle }}</div>
                                    <div class="text-[11px] text-[#A1A1A1]">{{ $topic->members_count }} anggota • {{ $topic->posts_count }} pos</div>
                                </div>
                            </a>
                            <a href="{{ route('topics.show', $topic->slug) }}" class="text-xs text-[#A332F7] hover:underline shrink-0">Buka</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
