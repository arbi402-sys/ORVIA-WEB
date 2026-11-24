@extends('layouts.orvia')

@section('title', 'Jelajahi Topik — ORVIA')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold text-[#FEFEFE]">Topik Komunitas</h1>
            <p class="text-xs text-[#A1A1A1] mt-1">Sub-komunitas diskusi kreatif (O/) yang dikurasi oleh Lembaga Terverifikasi.</p>
        </div>
        @if(auth()->check() && auth()->user()->canCreateTopics())
            <a href="{{ route('create.index', ['tab' => 'topic']) }}" class="px-5 py-2 orvia-btn-primary text-sm">
                + Buat Topik Baru
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('topics.index') }}" class="relative">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari topik O/..."
               class="w-full bg-[#15151C] border border-white/10 rounded-full pl-5 pr-12 py-2.5 text-sm text-white placeholder-[#A1A1A1] focus:outline-none focus:border-[#A332F7]">
        <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#A1A1A1] hover:text-white cursor-pointer">🔍</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($topics as $topic)
            @php $joined = in_array($topic->id, $joinedIds, true); @endphp
            <div class="orvia-card p-5 flex flex-col justify-between gap-4">
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('topics.show', $topic->slug) }}" class="flex items-center gap-3 min-w-0">
                            <img src="{{ $topic->avatar_url }}" alt="" class="w-12 h-12 rounded-full object-cover border border-white/10 shrink-0">
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-white hover:text-[#A332F7] truncate">{{ $topic->display_handle }}</h2>
                                <div class="text-xs text-[#A1A1A1]">{{ $topic->members_count }} anggota • {{ $topic->posts_count }} postingan</div>
                            </div>
                        </a>
                    </div>
                    @if($topic->description)
                        <p class="mt-3 text-xs text-[#C8C8D6] line-clamp-3 leading-relaxed">{{ $topic->description }}</p>
                    @endif
                </div>

                <div class="pt-3 border-t border-white/[0.06] flex items-center justify-between gap-2">
                    <div class="text-[11px] text-[#A1A1A1] flex items-center gap-1 truncate">
                        <span>Oleh</span>
                        <span class="text-white/90 font-medium truncate">{{ optional($topic->creator)->name }}</span>
                        <x-verified-badge :user="$topic->creator" size="xs" />
                    </div>
                    @auth
                        <form method="POST" action="{{ route('topics.join', $topic->slug) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3.5 py-1 rounded-full text-xs font-semibold cursor-pointer {{ $joined ? 'bg-white/10 text-white' : 'bg-[#A332F7] text-white hover:bg-[#B64DFF]' }}">
                                {{ $joined ? 'Bergabung ✓' : 'Bergabung' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3.5 py-1 rounded-full text-xs font-semibold bg-[#A332F7] text-white">Bergabung</a>
                    @endauth
                </div>
            </div>
        @empty
            <div class="col-span-full orvia-card p-10 text-center text-sm text-[#A1A1A1]">Belum ada topik ditemukan.</div>
        @endforelse
    </div>

    <div>{{ $topics->links() }}</div>
</div>
@endsection
