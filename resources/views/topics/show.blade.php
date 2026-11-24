@extends('layouts.orvia')

@section('title', $topic->display_handle . ' — ORVIA')

@section('content')
<div class="space-y-6">
    <!-- Topic Header -->
    <div class="orvia-card overflow-hidden">
        <div class="h-36 bg-gradient-to-r from-[#650EA6] via-[#A332F7] to-[#26113D] relative">
            @if($topic->banner)
                <img src="{{ str_starts_with($topic->banner, '/') ? $topic->banner : asset('storage/'.$topic->banner) }}" class="w-full h-full object-cover opacity-80" alt="">
            @endif
        </div>
        <div class="p-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4 -mt-10 relative">
            <div class="flex items-end gap-4">
                <img src="{{ $topic->avatar_url }}" alt="{{ $topic->name }}" class="w-20 h-20 rounded-full object-cover border-4 border-[#15151C] bg-[#15151C]">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ $topic->display_handle }}</h1>
                    <div class="text-xs text-[#A1A1A1] flex flex-wrap items-center gap-2 mt-1">
                        <span>{{ $topic->members_count }} anggota • {{ $topic->posts_count }} postingan</span>
                        <span>• Dibuat oleh</span>
                        <x-author-credits :user="$topic->creator" size="xs" />
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                @auth
                    <a href="{{ route('create.index', ['tab' => 'post']) }}" class="px-4 py-2 rounded-full text-xs font-semibold bg-white text-[#A332F7]">+ Postingan</a>
                    <form method="POST" action="{{ route('topics.join', $topic->slug) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2 rounded-full text-xs font-semibold cursor-pointer {{ $isMember ? 'bg-white/10 text-white' : 'bg-[#A332F7] text-white' }}">
                            {{ $isMember ? 'Bergabung ✓' : 'Bergabung' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2 orvia-btn-primary text-xs">Masuk untuk Bergabung</a>
                @endauth
            </div>
        </div>
        @if($topic->description)
            <div class="px-6 pb-5 text-sm text-[#D2D2DF]">{{ $topic->description }}</div>
        @endif
    </div>

    <!-- Posts in Topic -->
    <div class="space-y-4">
        @forelse($posts as $post)
            @php $isLiked = in_array($post->id, $userLikedPostIds, true); @endphp
            <article class="orvia-card p-6">
                <div class="flex items-center gap-2.5 text-xs text-[#A1A1A1]">
                    <img src="{{ $post->user->avatar_url }}" alt="" class="w-7 h-7 rounded-full object-cover">
                    <x-author-credits :user="$post->user" :collaborators="$post->acceptedCollaborators" size="xs" />
                    <span>• {{ $post->created_at->diffForHumans() }}</span>
                </div>
                <a href="{{ route('posts.show', $post) }}" class="block mt-3 group">
                    <h2 class="text-lg font-bold text-white group-hover:text-[#A332F7]">{{ $post->title }}</h2>
                    @if($post->body)
                        <p class="mt-1.5 text-sm text-[#D2D2DF] line-clamp-3">{{ $post->body }}</p>
                    @endif
                    @if($post->media_url)
                        <img src="{{ $post->media_url }}" alt="" class="mt-3 rounded-xl max-h-96 w-full object-cover">
                    @endif
                </a>
                <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-xs text-[#A1A1A1]">
                    <div class="flex items-center gap-4">
                        <span>♥ {{ $post->liked_by_count }}</span>
                        <a href="{{ route('posts.show', $post) }}#comments" class="hover:text-white">💬 {{ $post->comments_count }} Komentar</a>
                    </div>
                    <button type="button" onclick="window.orviaShare('{{ route('posts.show', $post) }}', '{{ route('posts.share', $post) }}', 'topic-share-{{ $post->id }}')" class="hover:text-white cursor-pointer">
                        Bagikan (<span id="topic-share-{{ $post->id }}">{{ $post->share_count }}</span>)
                    </button>
                </div>
            </article>
        @empty
            <div class="orvia-card p-10 text-center text-sm text-[#A1A1A1]">Belum ada postingan di topik ini.</div>
        @endforelse
    </div>

    <div>{{ $posts->links() }}</div>
</div>
@endsection
