@extends('layouts.orvia')

@section('title', 'Artikel — ORVIA')

@section('content')
<div class="space-y-6">
    <!-- Header (Matches Figma Article Page 0:1734) -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-[32px] font-semibold leading-tight text-[#FEFEFE]">Artikel</h1>
            <p class="text-xs text-[#A1A1A1] mt-1">Ruang berbagi pengetahuan dan perspektif desain.</p>
        </div>
        @auth
            <a href="{{ route('create.index', ['tab' => 'article']) }}" class="px-5 py-2 orvia-btn-primary text-sm">
                + Tulis Artikel
            </a>
        @endauth
    </div>

    <!-- Full-width Pill Search Bar (Matches Figma Article Page) -->
    <form method="GET" action="{{ route('articles.index') }}" class="relative">
        <input type="text"
               name="q"
               value="{{ $q }}"
               placeholder="Cari Artikel....."
               class="w-full bg-[#15151C] border border-white/[0.06] rounded-full pl-6 pr-12 py-3 text-sm text-white placeholder-[#A1A1A1] focus:outline-none focus:border-[#A332F7]">
        <button type="submit" class="absolute right-5 top-1/2 -translate-y-1/2 text-[#A1A1A1] hover:text-white cursor-pointer" aria-label="Cari artikel">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
        </button>
    </form>

    <!-- Substack-style Horizontal Article Cards -->
    <div class="space-y-5">
        @forelse($articles as $article)
            @php
                $isLiked = in_array($article->id, $userLikedArticleIds, true);
                $isFollowing = $article->user && in_array($article->user->id, $userFollowingIds, true);
            @endphp
            <article class="orvia-card p-6 hover:border-white/10 transition">
                <!-- Author & Follow Row -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <a href="{{ route('profile.show', $article->user->username ?: $article->user->id) }}" class="shrink-0">
                            <img src="{{ $article->user->avatar_url }}" alt="{{ $article->user->name }}" class="w-10 h-10 rounded-full object-cover border border-white/10">
                        </a>
                        <div class="min-w-0">
                            <div class="text-sm">
                                <x-author-credits :user="$article->user" :collaborators="$article->acceptedCollaborators" size="sm" />
                            </div>
                            <div class="text-xs text-[#A1A1A1]">
                                Artikel • {{ $article->created_at->translatedFormat('d F Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if(!auth()->check() || auth()->id() !== $article->user_id)
                            @auth
                                <form method="POST" action="{{ $isFollowing ? route('profile.unfollow', $article->user->username ?: $article->user->id) : route('profile.follow', $article->user->username ?: $article->user->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-5 py-1.5 rounded-full text-xs font-semibold transition cursor-pointer {{ $isFollowing ? 'bg-white/10 text-white' : 'bg-[#A332F7] text-white hover:bg-[#B64DFF]' }}">
                                        {{ $isFollowing ? 'Mengikuti' : 'Ikuti' }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="px-5 py-1.5 rounded-full text-xs font-semibold bg-[#A332F7] text-white hover:bg-[#B64DFF]">
                                    Ikuti
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>

                <!-- Content + Right Thumbnail -->
                <div class="mt-4 flex flex-col md:flex-row items-start justify-between gap-6">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('articles.show', $article) }}" class="group block">
                            <h2 class="text-xl font-bold text-[#FEFEFE] group-hover:text-[#A332F7] transition leading-snug">
                                {{ $article->title }}
                            </h2>
                            <p class="mt-2 text-sm text-[#B8B8C6] leading-relaxed line-clamp-3">
                                {{ $article->subtitle ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 240) }}
                            </p>
                        </a>

                        <div class="mt-3 text-[11px] font-semibold uppercase tracking-wider text-[#A1A1A1]">
                            {{ strtoupper($article->user->name) }}
                        </div>

                        <!-- Metrics Bar -->
                        <div class="mt-4 flex flex-wrap items-center gap-5 text-xs text-[#A1A1A1]">
                            @auth
                                <button type="button"
                                        onclick="window.orviaToggleLike('{{ route('articles.like', $article) }}', this, 'art-like-{{ $article->id }}')"
                                        class="inline-flex items-center gap-2 hover:text-white transition cursor-pointer">
                                    <span data-like-circle class="w-7 h-7 rounded-full border flex items-center justify-center {{ $isLiked ? 'bg-[#A332F7] border-[#A332F7] text-white' : 'border-white/25' }}">
                                        ♥
                                    </span>
                                    <span id="art-like-{{ $article->id }}">{{ $article->liked_by_count }}</span>
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 hover:text-white">
                                    <span class="w-7 h-7 rounded-full border border-white/25 flex items-center justify-center">♥</span>
                                    <span>{{ $article->liked_by_count }}</span>
                                </a>
                            @endauth

                            <a href="{{ route('articles.show', $article) }}#comments" class="inline-flex items-center gap-2 hover:text-white">
                                <span class="w-7 h-7 rounded-full border border-white/25 flex items-center justify-center">💬</span>
                                <span>{{ $article->comments_count }}</span>
                            </a>

                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-7 h-7 rounded-full border border-white/25 flex items-center justify-center">👁</span>
                                <span>{{ number_format($article->views_count) }}</span>
                            </span>

                            <button type="button"
                                    onclick="window.orviaShare('{{ route('articles.show', $article) }}', '{{ route('articles.share', $article) }}', 'art-share-{{ $article->id }}')"
                                    class="inline-flex items-center gap-1.5 hover:text-white cursor-pointer">
                                <span>Bagikan</span>
                                <span id="art-share-{{ $article->id }}">({{ $article->share_count }})</span>
                            </button>
                        </div>
                    </div>

                    @if($article->image_url)
                        <a href="{{ route('articles.show', $article) }}" class="w-full md:w-[270px] h-[165px] rounded-[12px] overflow-hidden shrink-0 bg-[#0B0B0B] border border-white/[0.06]">
                            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover hover:scale-[1.02] transition duration-300">
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="orvia-card p-12 text-center">
                <p class="text-[#A1A1A1]">Belum ada artikel ditemukan.</p>
                @auth
                    <a href="{{ route('create.index', ['tab' => 'article']) }}" class="mt-4 inline-flex px-5 py-2 orvia-btn-primary text-sm">
                        Tulis Artikel Pertama
                    </a>
                @endauth
            </div>
        @endforelse
    </div>

    <div>{{ $articles->links() }}</div>
</div>
@endsection
