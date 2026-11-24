@extends('layouts.orvia')

@section('title', $post->title . ' — ORVIA')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('beranda') }}" class="inline-flex items-center gap-2 text-sm text-[#A1A1A1] hover:text-white transition">
            <span>←</span> Kembali ke Beranda
        </a>
        @if(auth()->check() && (auth()->id() === $post->user_id || auth()->user()->isAdmin()))
            <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Hapus postingan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-1.5 rounded-full text-xs border border-[#F31415]/40 text-[#F31415] hover:bg-[#F31415] hover:text-white transition cursor-pointer">
                    Hapus Postingan
                </button>
            </form>
        @endif
    </div>

    <article class="orvia-card p-6 sm:p-8">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img src="{{ $post->user->avatar_url }}" alt="" class="w-11 h-11 rounded-full object-cover border border-white/10">
                <div>
                    <div class="flex flex-wrap items-center gap-1.5 text-sm">
                        @if($post->topic)
                            <a href="{{ route('topics.show', $post->topic->slug) }}" class="font-semibold text-[#A332F7] hover:underline">
                                {{ $post->topic->display_handle }}
                            </a>
                            <span class="text-[#A1A1A1]">•</span>
                        @endif
                        <x-author-credits :user="$post->user" :collaborators="$post->acceptedCollaborators" size="sm" />
                    </div>
                    <div class="text-xs text-[#A1A1A1] mt-0.5">
                        Dipublikasikan {{ $post->created_at->translatedFormat('d F Y, H:i') }} • {{ number_format($post->views_count) }} tayangan
                    </div>
                </div>
            </div>

            @auth
                @if(auth()->id() !== $post->user_id)
                    <form method="POST" action="{{ route('messages.start', $post->user_id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-1.5 rounded-full text-xs font-semibold bg-[#A332F7]/15 text-[#A332F7] border border-[#A332F7]/40 hover:bg-[#A332F7] hover:text-white transition cursor-pointer">
                            Kirim Pesan
                        </button>
                    </form>
                @endif
            @endauth
        </div>

        <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-[#FEFEFE] leading-snug">{{ $post->title }}</h1>

        @if($post->media_url)
            <div class="mt-5 rounded-2xl overflow-hidden bg-[#0B0B0B] border border-white/[0.06]">
                @if($post->media_type === 'video')
                    <video src="{{ $post->media_url }}" controls class="w-full max-h-[520px]"></video>
                @else
                    <img src="{{ $post->media_url }}" alt="{{ $post->title }}" class="w-full max-h-[540px] object-cover">
                @endif
            </div>
        @endif

        @if($post->body)
            <div class="mt-5 text-base text-[#E2E2EC] leading-relaxed whitespace-pre-line">
                {{ $post->body }}
            </div>
        @endif

        <!-- Action Bar -->
        <div class="mt-6 pt-4 border-t border-white/[0.08] flex flex-wrap items-center justify-between gap-4 text-sm text-[#A1A1A1]">
            <div class="flex items-center gap-5">
                @auth
                    <button type="button"
                            onclick="window.orviaToggleLike('{{ route('posts.like', $post) }}', this, 'detail-post-like')"
                            class="inline-flex items-center gap-2 hover:text-white transition cursor-pointer">
                        <span data-like-circle class="w-8 h-8 rounded-full border flex items-center justify-center {{ $isLiked ? 'bg-[#A332F7] border-[#A332F7] text-white' : 'border-white/25' }}">
                            ♥
                        </span>
                        <span id="detail-post-like">{{ $post->liked_by_count }}</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 hover:text-white">
                        <span class="w-8 h-8 rounded-full border border-white/25 flex items-center justify-center">♥</span>
                        <span>{{ $post->liked_by_count }}</span>
                    </a>
                @endauth

                <span class="inline-flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full border border-white/25 flex items-center justify-center">💬</span>
                    <span>{{ $post->comments_count }} Komentar</span>
                </span>
            </div>

            <button type="button"
                    onclick="window.orviaShare('{{ route('posts.show', $post) }}', '{{ route('posts.share', $post) }}', 'detail-post-share')"
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-white/15 hover:border-[#A332F7] hover:text-white transition cursor-pointer">
                <span>Bagikan</span>
                <span id="detail-post-share">{{ $post->share_count }}</span>
            </button>
        </div>

        <!-- Institution Collaboration Invite Panel (Owner Verified Institution only) -->
        @if(auth()->check() && auth()->id() === $post->user_id && auth()->user()->canCollaborate() && $verifiedInstitutions->isNotEmpty())
            <div class="mt-6 p-4 rounded-xl bg-[#0B0B0B] border border-[#FACC15]/30">
                <div class="text-xs font-semibold text-[#FACC15] mb-2">Undang Kolaborasi Lembaga Terverifikasi</div>
                <form method="POST" action="{{ route('collaborations.invite') }}" class="flex flex-col sm:flex-row gap-2.5">
                    @csrf
                    <input type="hidden" name="type" value="post">
                    <input type="hidden" name="id" value="{{ $post->id }}">
                    <select name="collaborator_id" required class="orvia-input px-3 py-2 text-xs flex-1">
                        <option value="">Pilih lembaga terverifikasi...</option>
                        @foreach($verifiedInstitutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }} (@ {{ $inst->username }})</option>
                        @endforeach
                    </select>
                    <input type="text" name="role_label" placeholder="Peran (mis. Co-Author)" class="orvia-input px-3 py-2 text-xs sm:w-44">
                    <button type="submit" class="px-4 py-2 orvia-btn-primary text-xs cursor-pointer">Undang Kolaborasi</button>
                </form>
            </div>
        @endif
    </article>

    <!-- Comments Section (Reddit-style threaded discussion) -->
    <section id="comments" class="orvia-card p-6 sm:p-8 space-y-6">
        <h2 class="text-lg font-semibold text-white">Diskusi & Komentar ({{ $post->comments_count }})</h2>

        @auth
            <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="space-y-3">
                @csrf
                <textarea name="body" rows="3" required placeholder="Tulis tanggapan atau masukan konstruktif Anda..."
                          class="orvia-input w-full px-4 py-3 text-sm"></textarea>
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2 orvia-btn-primary text-sm cursor-pointer">Kirim Komentar</button>
                </div>
            </form>
        @else
            <div class="rounded-xl bg-[#0B0B0B] border border-white/10 p-4 flex items-center justify-between gap-4">
                <p class="text-sm text-[#A1A1A1]">Masuk untuk ikut berdiskusi dan menyukai postingan ini.</p>
                <a href="{{ route('login') }}" class="px-4 py-1.5 orvia-btn-primary text-xs shrink-0">Masuk</a>
            </div>
        @endauth

        <div class="space-y-4">
            @forelse($post->comments as $comment)
                <div id="comment-{{ $comment->id }}" class="rounded-xl bg-[#0B0B0B]/70 border border-white/[0.05] p-4">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ $comment->user->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                        <div class="text-sm font-semibold text-white flex items-center gap-1">
                            <a href="{{ route('profile.show', $comment->user->username ?: $comment->user->id) }}" class="hover:text-[#A332F7]">
                                {{ $comment->user->name }}
                            </a>
                            <x-verified-badge :user="$comment->user" size="xs" />
                        </div>
                        <span class="text-xs text-[#A1A1A1]">• {{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-2.5 text-sm text-[#E0E0EA] leading-relaxed">{{ $comment->body }}</p>

                    @auth
                        <button type="button"
                                onclick="document.getElementById('reply-form-{{ $comment->id }}').classList.toggle('hidden')"
                                class="mt-2 text-xs text-[#A332F7] hover:underline cursor-pointer">
                            Balas
                        </button>
                        <form id="reply-form-{{ $comment->id }}" method="POST" action="{{ route('posts.comments.store', $post) }}" class="hidden mt-3 flex gap-2">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <input type="text" name="body" required placeholder="Tulis balasan..." class="orvia-input flex-1 px-3 py-1.5 text-xs">
                            <button type="submit" class="px-3.5 py-1.5 orvia-btn-primary text-xs cursor-pointer">Kirim</button>
                        </form>
                    @endauth

                    @if($comment->replies->isNotEmpty())
                        <div class="mt-3 pl-4 border-l-2 border-[#A332F7]/30 space-y-3">
                            @foreach($comment->replies as $reply)
                                <div>
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $reply->user->avatar_url }}" alt="" class="w-6 h-6 rounded-full object-cover">
                                        <span class="text-xs font-semibold text-white inline-flex items-center gap-1">
                                            {{ $reply->user->name }}
                                            <x-verified-badge :user="$reply->user" size="xs" />
                                        </span>
                                        <span class="text-[11px] text-[#A1A1A1]">• {{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-[#D5D5E2]">{{ $reply->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-[#A1A1A1] text-center py-4">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
