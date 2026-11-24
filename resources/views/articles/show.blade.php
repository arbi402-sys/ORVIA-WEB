@extends('layouts.orvia')

@section('title', $article->title . ' — ORVIA')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Substack-style Reader Card (Matches Figma Article Page 2 0:2792) -->
    <article class="orvia-card p-6 sm:p-10">
        <!-- Top Bar: Publication Name + Close X -->
        <div class="flex items-center justify-between gap-4">
            <div class="text-sm text-[#A1A1A1] flex flex-wrap items-center gap-2">
                @if($article->user)
                    <x-author-credits :user="$article->user" :collaborators="$article->acceptedCollaborators" size="sm" />
                @endif
                @if($article->topic)
                    <span>•</span>
                    <a href="{{ route('topics.show', $article->topic->slug) }}" class="text-[#A332F7] hover:underline">{{ $article->topic->display_handle }}</a>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->check() && (auth()->id() === $article->user_id || auth()->user()->isAdmin()))
                    <form method="POST" action="{{ route('articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-[#F31415] hover:underline cursor-pointer">Hapus</button>
                    </form>
                @endif
                <a href="{{ route('articles.index') }}" class="w-8 h-8 rounded-full flex items-center justify-center text-[#A1A1A1] hover:text-white hover:bg-white/10 transition" title="Tutup">
                    ✕
                </a>
            </div>
        </div>

        <!-- Giant Title & Subtitle -->
        <h1 class="mt-4 text-3xl sm:text-5xl font-bold text-[#FEFEFE] leading-[1.15] tracking-tight">
            {{ $article->title }}
        </h1>

        @if($article->subtitle)
            <p class="mt-3 text-base sm:text-lg text-[#A1A1A1] leading-relaxed">
                {{ $article->subtitle }}
            </p>
        @endif

        <!-- Author Bar with Ikuti & Pesan Buttons (Matches Figma Article Page 2) -->
        @if($article->user)
            <div class="mt-6 pb-6 border-b border-[#A332F7]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <a href="{{ route('profile.show', $article->user->username ?: $article->user->id) }}">
                        <img src="{{ $article->user->avatar_url }}" alt="{{ $article->user->name }}" class="w-12 h-12 rounded-full object-cover border border-white/10">
                    </a>
                    <div>
                        <div class="text-sm font-bold uppercase tracking-wide text-white flex items-center gap-1.5">
                            <a href="{{ route('profile.show', $article->user->username ?: $article->user->id) }}" class="hover:text-[#A332F7]">
                                {{ $article->user->name }}
                            </a>
                            <x-verified-badge :user="$article->user" size="sm" />
                        </div>
                        <div class="text-xs text-[#A1A1A1]">
                            {{ $article->created_at->translatedFormat('d F Y') }}
                            @if($article->acceptedCollaborators->isNotEmpty())
                                • Kolaborasi bersama {{ $article->acceptedCollaborators->pluck('collaborator.name')->filter()->join(', ') }}
                            @endif
                        </div>
                    </div>
                </div>

                @if(!auth()->check() || auth()->id() !== $article->user_id)
                    <div class="flex items-center gap-2.5">
                        @auth
                            <form method="POST" action="{{ $isFollowing ? route('profile.unfollow', $article->user->username ?: $article->user->id) : route('profile.follow', $article->user->username ?: $article->user->id) }}">
                                @csrf
                                <button type="submit" class="px-6 py-1.5 rounded-full text-xs font-semibold cursor-pointer {{ $isFollowing ? 'bg-white/10 text-white' : 'bg-[#A332F7] text-white hover:bg-[#B64DFF]' }}">
                                    {{ $isFollowing ? 'Mengikuti' : 'Ikuti' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('messages.start', $article->user_id) }}">
                                @csrf
                                <button type="submit" class="px-6 py-1.5 rounded-full text-xs font-semibold bg-[#A332F7] text-white hover:bg-[#B64DFF] cursor-pointer">
                                    Pesan
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-1.5 rounded-full text-xs font-semibold bg-[#A332F7] text-white">Ikuti</a>
                            <a href="{{ route('login') }}" class="px-6 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-white hover:bg-white/15">Pesan</a>
                        @endauth
                    </div>
                @endif
            </div>
        @endif

        <!-- Hero Cover Image -->
        @if($article->image_url)
            <div class="mt-7 rounded-[14px] overflow-hidden bg-[#0B0B0B] border border-white/[0.06]">
                <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full max-h-[440px] object-cover">
            </div>
        @endif

        <!-- Long-Form Article Body -->
        <div class="mt-8 orvia-prose">
            {!! $article->body !!}
        </div>

        <!-- Bottom Action Bar -->
        <div class="mt-10 pt-5 border-t border-[#A332F7]/25 flex flex-wrap items-center justify-between gap-4 text-sm text-[#A1A1A1]">
            <div class="flex items-center gap-6">
                @auth
                    <button type="button"
                            onclick="window.orviaToggleLike('{{ route('articles.like', $article) }}', this, 'reader-art-like')"
                            class="inline-flex items-center gap-2 hover:text-white cursor-pointer">
                        <span data-like-circle class="w-8 h-8 rounded-full border flex items-center justify-center {{ $isLiked ? 'bg-[#A332F7] border-[#A332F7] text-white' : 'border-white/25' }}">
                            ♥
                        </span>
                        <span id="reader-art-like">{{ $article->liked_by_count }}</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 hover:text-white">
                        <span class="w-8 h-8 rounded-full border border-white/25 flex items-center justify-center">♥</span>
                        <span>{{ $article->liked_by_count }}</span>
                    </a>
                @endauth

                <a href="#comments" class="inline-flex items-center gap-2 hover:text-white">
                    <span class="w-8 h-8 rounded-full border border-white/25 flex items-center justify-center">💬</span>
                    <span>{{ $article->comments_count }}</span>
                </a>

                <span class="inline-flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full border border-white/25 flex items-center justify-center">👁</span>
                    <span>{{ number_format($article->views_count) }}</span>
                </span>
            </div>

            <button type="button"
                    onclick="window.orviaShare('{{ route('articles.show', $article) }}', '{{ route('articles.share', $article) }}', 'reader-art-share')"
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-white/15 hover:border-[#A332F7] hover:text-white transition cursor-pointer">
                <span>Bagikan Artikel</span>
                <span id="reader-art-share">{{ $article->share_count }}</span>
            </button>
        </div>

        <!-- Institution Collaboration Panel -->
        @if(auth()->check() && auth()->id() === $article->user_id && auth()->user()->canCollaborate() && $verifiedInstitutions->isNotEmpty())
            <div class="mt-6 p-4 rounded-xl bg-[#0B0B0B] border border-[#FACC15]/30">
                <div class="text-xs font-semibold text-[#FACC15] mb-2">Undang Kolaborasi Lembaga Terverifikasi pada Artikel Ini</div>
                <form method="POST" action="{{ route('collaborations.invite') }}" class="flex flex-col sm:flex-row gap-2.5">
                    @csrf
                    <input type="hidden" name="type" value="article">
                    <input type="hidden" name="id" value="{{ $article->id }}">
                    <select name="collaborator_id" required class="orvia-input px-3 py-2 text-xs flex-1">
                        <option value="">Pilih lembaga terverifikasi...</option>
                        @foreach($verifiedInstitutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="role_label" placeholder="Peran (Mitra Editorial)" class="orvia-input px-3 py-2 text-xs sm:w-44">
                    <button type="submit" class="px-4 py-2 orvia-btn-primary text-xs cursor-pointer">Undang</button>
                </form>
            </div>
        @endif
    </article>

    <!-- Reader Comments -->
    <section id="comments" class="orvia-card p-6 sm:p-8 space-y-5">
        <h2 class="text-lg font-semibold text-white">Komentar Pembaca ({{ $article->comments_count }})</h2>

        @auth
            <form method="POST" action="{{ route('articles.comments.store', $article) }}" class="space-y-3">
                @csrf
                <textarea name="body" rows="3" required placeholder="Bagikan perspektif Anda tentang artikel ini..."
                          class="orvia-input w-full px-4 py-3 text-sm"></textarea>
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2 orvia-btn-primary text-sm cursor-pointer">Kirim Komentar</button>
                </div>
            </form>
        @else
            <div class="rounded-xl bg-[#0B0B0B] border border-white/10 p-4 flex items-center justify-between gap-4">
                <p class="text-sm text-[#A1A1A1]">Masuk untuk menulis komentar pada artikel ini.</p>
                <a href="{{ route('login') }}" class="px-4 py-1.5 orvia-btn-primary text-xs">Masuk</a>
            </div>
        @endauth

        <div class="space-y-4">
            @forelse($article->comments as $comment)
                <div id="comment-{{ $comment->id }}" class="rounded-xl bg-[#0B0B0B]/70 border border-white/[0.05] p-4">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ $comment->user->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                        <span class="text-sm font-semibold text-white inline-flex items-center gap-1">
                            {{ $comment->user->name }}
                            <x-verified-badge :user="$comment->user" size="xs" />
                        </span>
                        <span class="text-xs text-[#A1A1A1]">• {{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-2 text-sm text-[#E0E0EA]">{{ $comment->body }}</p>
                </div>
            @empty
                <p class="text-sm text-[#A1A1A1] text-center py-3">Belum ada komentar pembaca.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
