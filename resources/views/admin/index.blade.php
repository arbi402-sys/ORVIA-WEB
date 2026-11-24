@extends('layouts.orvia')

@section('title', 'Panel Administrator — ORVIA')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FACC15]/15 border border-[#FACC15]/40 text-[#FACC15] text-[11px] font-bold mb-2">
                ADMINISTRATOR PANEL
            </div>
            <h1 class="text-[32px] font-semibold leading-tight text-[#FEFEFE]">Verifikasi Lembaga & Moderasi</h1>
            <p class="text-xs text-[#A1A1A1] mt-1">Tinjau pengajuan lembaga, terbitkan Kode Akses Masuk Lembaga, dan kelola moderasi konten ORVIA.</p>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <div class="orvia-card p-4">
            <div class="text-[11px] text-[#A1A1A1]">Menunggu Verifikasi</div>
            <div class="text-2xl font-bold text-[#F31415] mt-1">{{ $stats['pending_institutions'] }}</div>
        </div>
        <div class="orvia-card p-4">
            <div class="text-[11px] text-[#A1A1A1]">Lembaga Aktif</div>
            <div class="text-2xl font-bold text-[#FACC15] mt-1">{{ $stats['verified_institutions'] }}</div>
        </div>
        <div class="orvia-card p-4">
            <div class="text-[11px] text-[#A1A1A1]">Total Pengguna</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $stats['users'] }}</div>
        </div>
        <div class="orvia-card p-4">
            <div class="text-[11px] text-[#A1A1A1]">Postingan</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $stats['posts'] }}</div>
        </div>
        <div class="orvia-card p-4">
            <div class="text-[11px] text-[#A1A1A1]">Artikel</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $stats['articles'] }}</div>
        </div>
        <div class="orvia-card p-4">
            <div class="text-[11px] text-[#A1A1A1]">Agenda</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $stats['events'] }}</div>
        </div>
        <div class="orvia-card p-4">
            <div class="text-[11px] text-[#A1A1A1]">Topik (O/)</div>
            <div class="text-2xl font-bold text-[#A332F7] mt-1">{{ $stats['topics'] }}</div>
        </div>
    </div>

    <!-- Admin Tabs -->
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="{{ route('admin.index', ['tab' => 'institutions']) }}"
           class="px-5 py-1.5 text-sm font-semibold {{ $tab === 'institutions' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
            Verifikasi Lembaga ({{ $pendingInstitutions->count() }})
        </a>
        <a href="{{ route('admin.index', ['tab' => 'moderation']) }}"
           class="px-5 py-1.5 text-sm font-semibold {{ $tab === 'moderation' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
            Moderasi Konten
        </a>
        <a href="{{ route('admin.index', ['tab' => 'users']) }}"
           class="px-5 py-1.5 text-sm font-semibold {{ $tab === 'users' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
            Daftar Pengguna
        </a>
    </div>

    @if($tab === 'institutions')
        <!-- Pending Institutions Queue -->
        <section class="orvia-card p-6 space-y-4 border border-[#FACC15]/25">
            <h2 class="text-lg font-semibold text-white">Antrean Verifikasi Lembaga ({{ $pendingInstitutions->count() }})</h2>
            @forelse($pendingInstitutions as $inst)
                <div class="rounded-2xl bg-[#0B0B0B] border border-white/[0.08] p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="space-y-1.5 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-bold text-white">{{ $inst->name }}</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#FACC15]/20 text-[#FACC15] text-[11px] font-semibold">PENDING</span>
                        </div>
                        <div class="text-xs text-[#A1A1A1]">
                            @ {{ $inst->username }} • {{ $inst->email }} • {{ $inst->location ?: '-' }}
                        </div>
                        @if($inst->bio)
                            <p class="text-xs text-[#D5D5E2]">{{ $inst->bio }}</p>
                        @endif
                        @if($inst->proof_image)
                            <a href="{{ asset('storage/'.$inst->proof_image) }}" target="_blank" class="inline-block text-xs text-[#37B9D6] hover:underline pt-1">
                                📄 Lihat Dokumen Bukti Lembaga ↗
                            </a>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                        <form method="POST" action="{{ route('admin.institutions.approve', $inst) }}">
                            @csrf
                            <button type="submit" class="px-5 py-2 rounded-full bg-[#A332F7] text-white text-xs font-semibold hover:bg-[#B64DFF] cursor-pointer">
                                ✓ Setujui & Terbitkan Kode Akses
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.institutions.reject', $inst) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="rejection_reason" placeholder="Alasan tolak (opsional)" class="orvia-input px-3 py-1.5 text-xs w-44">
                            <button type="submit" class="px-4 py-2 rounded-full border border-[#F31415]/50 text-[#F31415] hover:bg-[#F31415] hover:text-white text-xs font-semibold cursor-pointer">
                                Tolak
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-xs text-[#A1A1A1] py-4">Tidak ada pengajuan lembaga yang menunggu verifikasi saat ini.</p>
            @endforelse
        </section>

        <!-- Approved Institutions & Access Codes -->
        <section class="orvia-card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-white">Lembaga Disetujui & Kode Akses Masuk ({{ $approvedInstitutions->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[#A1A1A1] border-b border-white/[0.08]">
                        <tr>
                            <th class="pb-3 font-medium">Lembaga</th>
                            <th class="pb-3 font-medium">Email</th>
                            <th class="pb-3 font-medium">Status Aktivasi</th>
                            <th class="pb-3 font-medium">Kode Akses Lembaga</th>
                            <th class="pb-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @foreach($approvedInstitutions as $inst)
                            <tr>
                                <td class="py-3.5 font-semibold text-white">
                                    <span class="inline-flex items-center gap-1.5">
                                        {{ $inst->name }}
                                        <x-verified-badge :user="$inst" size="xs" />
                                    </span>
                                </td>
                                <td class="py-3.5 text-[#A1A1A1]">{{ $inst->email }}</td>
                                <td class="py-3.5">
                                    @if($inst->is_verified)
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-semibold">Aktif (Badge Kuning)</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#FACC15]/20 text-[#FACC15] font-semibold">Menunggu Input Kode</span>
                                    @endif
                                </td>
                                <td class="py-3.5">
                                    <code class="px-2.5 py-1 rounded bg-[#0B0B0B] border border-[#FACC15]/30 text-[#FACC15] font-mono">
                                        {{ $inst->institution_access_code ?: '-' }}
                                    </code>
                                </td>
                                <td class="py-3.5 text-right">
                                    <form method="POST" action="{{ route('admin.institutions.regenerate', $inst) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[#37B9D6] hover:underline cursor-pointer">Generate Kode Baru</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($tab === 'moderation')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Posts Moderation -->
            <section class="orvia-card p-6 space-y-3">
                <h2 class="text-base font-semibold text-white">Moderasi Postingan Terbaru</h2>
                @foreach($recentPosts as $p)
                    <div class="rounded-xl bg-[#0B0B0B] p-3.5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <a href="{{ route('posts.show', $p) }}" class="font-semibold text-white hover:text-[#A332F7] truncate block">{{ $p->title }}</a>
                            <div class="text-[#A1A1A1]">Oleh {{ optional($p->user)->name }} • {{ $p->created_at->diffForHumans() }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.content.delete', ['post', $p->id]) }}" onsubmit="return confirm('Hapus postingan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[#F31415] hover:underline cursor-pointer shrink-0">Hapus</button>
                        </form>
                    </div>
                @endforeach
            </section>

            <!-- Articles Moderation -->
            <section class="orvia-card p-6 space-y-3">
                <h2 class="text-base font-semibold text-white">Moderasi Artikel Terbaru</h2>
                @foreach($recentArticles as $a)
                    <div class="rounded-xl bg-[#0B0B0B] p-3.5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <a href="{{ route('articles.show', $a) }}" class="font-semibold text-white hover:text-[#A332F7] truncate block">{{ $a->title }}</a>
                            <div class="text-[#A1A1A1]">Oleh {{ optional($a->user)->name }} • {{ $a->created_at->diffForHumans() }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.content.delete', ['article', $a->id]) }}" onsubmit="return confirm('Hapus artikel ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[#F31415] hover:underline cursor-pointer shrink-0">Hapus</button>
                        </form>
                    </div>
                @endforeach
            </section>

            <!-- Events Moderation -->
            <section class="orvia-card p-6 space-y-3">
                <h2 class="text-base font-semibold text-white">Moderasi Agenda Acara</h2>
                @foreach($recentEvents as $e)
                    <div class="rounded-xl bg-[#0B0B0B] p-3.5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <a href="{{ route('events.show', $e->slug) }}" class="font-semibold text-white hover:text-[#A332F7] truncate block">{{ $e->title }}</a>
                            <div class="text-[#A1A1A1]">{{ optional($e->organizer)->name }} • {{ $e->event_date->translatedFormat('d M Y') }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.content.delete', ['event', $e->id]) }}" onsubmit="return confirm('Hapus agenda ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[#F31415] hover:underline cursor-pointer shrink-0">Hapus</button>
                        </form>
                    </div>
                @endforeach
            </section>

            <!-- Topics Moderation -->
            <section class="orvia-card p-6 space-y-3">
                <h2 class="text-base font-semibold text-white">Moderasi Topik (O/)</h2>
                @foreach($recentTopics as $t)
                    <div class="rounded-xl bg-[#0B0B0B] p-3.5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <a href="{{ route('topics.show', $t->slug) }}" class="font-semibold text-white hover:text-[#A332F7] truncate block">{{ $t->display_handle }}</a>
                            <div class="text-[#A1A1A1]">Dibuat {{ optional($t->creator)->name }} • {{ $t->members_count }} anggota</div>
                        </div>
                        <form method="POST" action="{{ route('admin.content.delete', ['topic', $t->id]) }}" onsubmit="return confirm('Hapus topik ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[#F31415] hover:underline cursor-pointer shrink-0">Hapus</button>
                        </form>
                    </div>
                @endforeach
            </section>
        </div>
    @endif

    @if($tab === 'users')
        <section class="orvia-card p-6">
            <h2 class="text-base font-semibold text-white mb-4">Seluruh Pengguna Terdaftar</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[#A1A1A1] border-b border-white/[0.08]">
                        <tr>
                            <th class="pb-3 font-medium">Nama</th>
                            <th class="pb-3 font-medium">Username</th>
                            <th class="pb-3 font-medium">Email</th>
                            <th class="pb-3 font-medium">Role</th>
                            <th class="pb-3 font-medium">Bergabung</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @foreach($allUsers as $u)
                            <tr>
                                <td class="py-3 font-semibold text-white">
                                    <a href="{{ route('profile.show', $u->username ?: $u->id) }}" class="inline-flex items-center gap-1 hover:text-[#A332F7]">
                                        {{ $u->name }}
                                        <x-verified-badge :user="$u" size="xs" />
                                    </a>
                                </td>
                                <td class="py-3 text-[#A1A1A1]">@ {{ $u->username }}</td>
                                <td class="py-3 text-[#A1A1A1]">{{ $u->email }}</td>
                                <td class="py-3"><span class="px-2.5 py-0.5 rounded-full bg-white/10 text-white">{{ $u->role }}</span></td>
                                <td class="py-3 text-[#A1A1A1]">{{ $u->created_at->translatedFormat('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $allUsers->links() }}</div>
        </section>
    @endif
</div>
@endsection
