@extends('layouts.orvia')

@section('title', 'Dashboard — ORVIA')

@section('content')
<div class="space-y-6">
    <!-- Header (Matches Figma Dashboard User 0:921) -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-[32px] font-semibold leading-tight text-[#FEFEFE]">Dashboard</h1>
            <p class="text-xs text-[#A1A1A1] mt-1">Performa konten, draf, dan aktivitas kolaborasi Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('create.index', ['tab' => 'post']) }}" class="px-5 py-2 orvia-btn-primary text-xs">+ Buat Konten</a>
        </div>
    </div>

    <!-- Top 2 Cards (Matches Figma Dashboard 2-column top cards) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 1: Performance Metrics -->
        <section class="orvia-card p-6 flex flex-col justify-between min-h-[290px]">
            <div>
                <h2 class="text-base font-semibold text-white">Ringkasan Performa</h2>
                <p class="text-xs text-[#A1A1A1] mt-0.5">Statistik keseluruhan dari konten yang telah diterbitkan.</p>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-[#0B0B0B] border border-white/[0.06] p-4">
                        <div class="text-xs text-[#A1A1A1]">Total Tayangan</div>
                        <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalViews) }}</div>
                    </div>
                    <div class="rounded-xl bg-[#0B0B0B] border border-white/[0.06] p-4">
                        <div class="text-xs text-[#A1A1A1]">Total Suka</div>
                        <div class="text-2xl font-bold text-[#A332F7] mt-1">{{ number_format($totalLikes) }}</div>
                    </div>
                    <div class="rounded-xl bg-[#0B0B0B] border border-white/[0.06] p-4">
                        <div class="text-xs text-[#A1A1A1]">Total Komentar</div>
                        <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalComments) }}</div>
                    </div>
                    <div class="rounded-xl bg-[#0B0B0B] border border-white/[0.06] p-4">
                        <div class="text-xs text-[#A1A1A1]">Pengikut</div>
                        <div class="text-2xl font-bold text-[#FACC15] mt-1">{{ number_format($followersCount) }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-white/[0.06] flex flex-wrap gap-4 text-xs text-[#A1A1A1]">
                <span>Postingan: <strong class="text-white">{{ $postsCount }}</strong></span>
                <span>Artikel: <strong class="text-white">{{ $articlesCount }}</strong></span>
                @if($user->canCreateEvents())
                    <span>Agenda: <strong class="text-white">{{ $eventsCount }}</strong></span>
                @endif
            </div>
        </section>

        <!-- Card 2: Drafts Panel (Matches Figma Dashboard right Drafts card!) -->
        <section class="orvia-card p-6 min-h-[290px] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-white">Drafts</h2>
                <a href="{{ route('create.index', ['tab' => 'article']) }}" class="text-xs text-[#A332F7] hover:underline">+ Buat Draf</a>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto">
                @forelse($draftPosts as $dp)
                    <div class="rounded-xl bg-[#0B0B0B] border border-white/[0.06] px-4 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-white truncate">{{ $dp->title ?: 'Untitled' }}</div>
                            <div class="text-[11px] text-[#A1A1A1]">Postingan • Diubah {{ $dp->updated_at->diffForHumans() }}</div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-0.5 rounded bg-white/10 text-[10px] font-semibold text-[#A1A1A1]">Draft</span>
                            <form method="POST" action="{{ route('drafts.post.publish', $dp) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded-full bg-[#A332F7] text-white text-[11px] font-semibold cursor-pointer">Terbitkan</button>
                            </form>
                        </div>
                    </div>
                @empty
                @endforelse

                @forelse($draftArticles as $da)
                    <div class="rounded-xl bg-[#0B0B0B] border border-white/[0.06] px-4 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-white truncate">{{ $da->title ?: 'Untitled' }}</div>
                            <div class="text-[11px] text-[#A1A1A1]">Artikel • Diubah {{ $da->updated_at->diffForHumans() }}</div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-0.5 rounded bg-white/10 text-[10px] font-semibold text-[#A1A1A1]">Draft</span>
                            <form method="POST" action="{{ route('drafts.article.publish', $da) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded-full bg-[#A332F7] text-white text-[11px] font-semibold cursor-pointer">Terbitkan</button>
                            </form>
                        </div>
                    </div>
                @empty
                @endforelse

                @if($draftPosts->isEmpty() && $draftArticles->isEmpty())
                    <div class="h-full flex items-center justify-center text-xs text-[#A1A1A1] py-10">
                        Belum ada draf tersimpan.
                    </div>
                @endif
            </div>
        </section>
    </div>

    <!-- Institution Collaboration Management Card (Verified Institutions only) -->
    @if($user->isVerifiedInstitution())
        <section class="orvia-card p-6 space-y-5 border border-[#FACC15]/25">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span>Kolaborasi Antar-Lembaga</span>
                        <x-verified-badge :user="$user" size="sm" />
                    </h2>
                    <p class="text-xs text-[#A1A1A1] mt-0.5">Kelola undangan kolaborasi Postingan, Artikel, dan Agenda dari lembaga terverifikasi lain.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Incoming Invites -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-[#FACC15]">Undangan Masuk ({{ $incomingCollaborations->count() }})</h3>
                    @forelse($incomingCollaborations as $collab)
                        <div class="rounded-xl bg-[#0B0B0B] border border-white/[0.06] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-white truncate">
                                    {{ optional($collab->collaboratable)->title ?? 'Konten' }}
                                </div>
                                <div class="text-xs text-[#A1A1A1] flex items-center gap-1 mt-0.5">
                                    <span>Dari {{ optional($collab->inviter)->name }}</span>
                                    <x-verified-badge :user="$collab->inviter" size="xs" />
                                    <span>• {{ $collab->role_label }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($collab->status === 'pending')
                                    <form method="POST" action="{{ route('collaborations.respond', $collab) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="accepted">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-full bg-[#A332F7] text-white text-xs font-semibold cursor-pointer">Terima</button>
                                    </form>
                                    <form method="POST" action="{{ route('collaborations.respond', $collab) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="declined">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-full bg-white/10 text-[#A1A1A1] hover:text-white text-xs cursor-pointer">Tolak</button>
                                    </form>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[11px] font-semibold {{ $collab->status === 'accepted' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                                        {{ $collab->status === 'accepted' ? 'Disetujui ✓' : 'Ditolak' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#A1A1A1]">Belum ada undangan kolaborasi masuk.</p>
                    @endforelse
                </div>

                <!-- Outgoing Invites -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-[#A1A1A1]">Undangan Terkirim ({{ $outgoingCollaborations->count() }})</h3>
                    @forelse($outgoingCollaborations as $collab)
                        <div class="rounded-xl bg-[#0B0B0B] border border-white/[0.06] p-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-white truncate">{{ optional($collab->collaboratable)->title ?? 'Konten' }}</div>
                                <div class="text-xs text-[#A1A1A1] flex items-center gap-1 mt-0.5">
                                    <span>Kepada {{ optional($collab->collaborator)->name }}</span>
                                    <x-verified-badge :user="$collab->collaborator" size="xs" />
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[11px] font-semibold {{ $collab->status === 'accepted' ? 'bg-emerald-500/20 text-emerald-300' : ($collab->status === 'pending' ? 'bg-[#FACC15]/20 text-[#FACC15]' : 'bg-red-500/20 text-red-300') }}">
                                {{ ucfirst($collab->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-[#A1A1A1]">Belum ada undangan kolaborasi terkirim.</p>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    <!-- Bottom Wide Activity Table (Matches Figma Dashboard bottom wide card) -->
    <section class="orvia-card p-6">
        <h2 class="text-base font-semibold text-white mb-4">Konten Terbaru Anda</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-[#A1A1A1] border-b border-white/[0.06]">
                    <tr>
                        <th class="pb-3 font-medium">Judul</th>
                        <th class="pb-3 font-medium">Tipe</th>
                        <th class="pb-3 font-medium">Suka</th>
                        <th class="pb-3 font-medium">Komentar</th>
                        <th class="pb-3 font-medium">Tayangan</th>
                        <th class="pb-3 font-medium">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05] text-xs">
                    @foreach($recentPosts as $p)
                        <tr>
                            <td class="py-3 font-semibold text-white">
                                <a href="{{ route('posts.show', $p) }}" class="hover:text-[#A332F7]">{{ $p->title }}</a>
                            </td>
                            <td class="py-3"><span class="px-2.5 py-0.5 rounded-full bg-[#A332F7]/20 text-[#A332F7]">Postingan</span></td>
                            <td class="py-3">{{ $p->liked_by_count }}</td>
                            <td class="py-3">{{ $p->comments_count }}</td>
                            <td class="py-3">{{ number_format($p->views_count) }}</td>
                            <td class="py-3 text-[#A1A1A1]">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                        </tr>
                    @endforeach
                    @foreach($recentArticles as $a)
                        <tr>
                            <td class="py-3 font-semibold text-white">
                                <a href="{{ route('articles.show', $a) }}" class="hover:text-[#A332F7]">{{ $a->title }}</a>
                            </td>
                            <td class="py-3"><span class="px-2.5 py-0.5 rounded-full bg-[#37B9D6]/20 text-[#37B9D6]">Artikel</span></td>
                            <td class="py-3">{{ $a->liked_by_count }}</td>
                            <td class="py-3">{{ $a->comments_count }}</td>
                            <td class="py-3">{{ number_format($a->views_count) }}</td>
                            <td class="py-3 text-[#A1A1A1]">{{ $a->created_at->translatedFormat('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
