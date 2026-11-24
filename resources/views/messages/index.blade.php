@extends('layouts.orvia')

@section('title', 'Pesan — ORVIA')

@section('content')
@php $me = auth()->user(); @endphp
<div class="space-y-5">
    <!-- Header (Matches Figma Chat 0:2207) -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <h1 class="text-[32px] font-semibold leading-tight text-[#FEFEFE]">Pesan</h1>
            <p class="text-xs text-[#A1A1A1] mt-0.5">Mulai percakapan, bangun koneksi, dan koordinasikan kolaborasi antar-lembaga.</p>
        </div>
        <button type="button"
                onclick="document.getElementById('newChatModal').classList.remove('hidden')"
                class="px-5 py-2 orvia-btn-primary text-xs cursor-pointer">
            + Percakapan Baru
        </button>
    </div>

    <!-- 2-Panel Chat Workspace (Matches Figma Chat 0:2207) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 min-h-[640px]">
        <!-- Left Conversation List Panel -->
        <section class="lg:col-span-4 orvia-card p-5 flex flex-col max-h-[720px]">
            <!-- Search -->
            <form method="GET" action="{{ route('messages.index') }}" class="relative">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari...."
                       class="w-full bg-[#0B0B0B] border border-white/[0.08] rounded-full pl-4 pr-10 py-2 text-xs text-white placeholder-[#A1A1A1] focus:outline-none focus:border-[#A332F7]">
                <button type="submit" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#A1A1A1] hover:text-white cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                </button>
            </form>

            <!-- Filter Tabs (Semua / Grup / Komunitas) -->
            <div class="mt-4 flex items-center gap-6 border-b border-[#A332F7]/20 pb-2.5 text-sm">
                @foreach(['all' => 'Semua', 'group' => 'Grup', 'community' => 'Komunitas'] as $k => $lbl)
                    <a href="{{ route('messages.index', array_filter(['filter' => $k, 'q' => $search])) }}"
                       class="transition {{ $filter === $k ? 'text-white font-semibold border-b-2 border-[#A332F7] -mb-[11px] pb-2.5' : 'text-[#A1A1A1] hover:text-white' }}">
                        {{ $lbl }}
                    </a>
                @endforeach
            </div>

            <!-- Conversations List -->
            <div class="mt-3 flex-1 overflow-y-auto divide-y divide-[#A332F7]/15 pr-1">
                @forelse($conversations as $conv)
                    @php
                        $peer = $conv->otherParticipant($me);
                        $unread = $conv->unreadCountFor($me);
                        $isActive = $activeConversation && $activeConversation->id === $conv->id;
                    @endphp
                    <a href="{{ route('messages.show', $conv) }}"
                       class="flex items-center gap-3 py-3.5 px-2.5 rounded-xl transition {{ $isActive ? 'bg-[#A332F7]/10 border-l-2 border-[#A332F7]' : 'hover:bg-white/[0.03]' }}">
                        <img src="{{ $peer ? $peer->avatar_url : asset('/images/icon/profil.png') }}"
                             alt=""
                             class="w-11 h-11 rounded-full object-cover border border-white/10 shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-white truncate flex items-center gap-1">
                                    <span class="truncate">{{ $conv->title ?: ($peer ? $peer->name : 'Percakapan') }}</span>
                                    @if($peer)
                                        <x-verified-badge :user="$peer" size="xs" />
                                    @endif
                                </div>
                                @if($unread > 0)
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#A332F7] shrink-0" title="{{ $unread }} pesan belum dibaca"></span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                <p class="text-xs truncate {{ $unread > 0 ? 'text-white font-medium' : 'text-[#A1A1A1]' }}">
                                    {{ $conv->latestMessage ? \Illuminate\Support\Str::limit($conv->latestMessage->body, 36) : 'Mulai percakapan...' }}
                                </p>
                                <span class="text-[10px] text-[#A1A1A1] shrink-0">
                                    {{ $conv->last_message_at ? $conv->last_message_at->format('H.i') : '' }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="py-12 text-center text-xs text-[#A1A1A1]">
                        Belum ada percakapan.<br>Klik <strong>+ Percakapan Baru</strong> untuk mulai mengirim pesan.
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Right Active Chat Room Panel -->
        <section class="lg:col-span-8 orvia-card flex flex-col max-h-[720px] overflow-hidden">
            @if($activeConversation)
                @php $peer = $activeConversation->otherParticipant($me); @endphp
                <!-- Chat Header -->
                <div class="px-6 py-4 border-b border-[#A332F7]/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $peer ? $peer->avatar_url : asset('/images/icon/profil.png') }}"
                             alt=""
                             class="w-11 h-11 rounded-full object-cover border border-white/10 shrink-0">
                        <div class="min-w-0">
                            <div class="text-base font-semibold text-white flex items-center gap-1.5">
                                @if($peer)
                                    <a href="{{ route('profile.show', $peer->username ?: $peer->id) }}" class="hover:text-[#A332F7] truncate">
                                        {{ $peer->name }}
                                    </a>
                                    <x-verified-badge :user="$peer" size="sm" />
                                @else
                                    <span>{{ $activeConversation->title ?? 'Percakapan' }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-[#A1A1A1]">
                                @if($peer && $peer->last_activity && (time() - (int)$peer->last_activity) < 300)
                                    <span class="text-emerald-400">● Aktif</span>
                                @else
                                    {{ $peer ? $peer->role_label : 'Percakapan ORVIA' }}
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($peer)
                        <a href="{{ route('profile.show', $peer->username ?: $peer->id) }}" class="text-xs text-[#A1A1A1] hover:text-white">
                            Lihat Profil →
                        </a>
                    @endif
                </div>

                <!-- Messages Stream -->
                <div id="chatMessagesBox" class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                    @forelse($messages as $msg)
                        @php $isMine = $msg->sender_id === $me->id; @endphp
                        <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }}">
                            @if($msg->sharedEvent)
                                <!-- Embedded Event Card inside Chat (Matches Figma Chat 0:2207!) -->
                                <div class="max-w-md w-full rounded-[16px] bg-[#D9D9D9] text-[#15151C] p-3.5 mb-1.5 flex items-center gap-3.5 shadow">
                                    <img src="{{ $msg->sharedEvent->poster_url }}" alt="" class="w-16 h-16 rounded-xl object-cover shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-bold leading-snug line-clamp-2">{{ $msg->sharedEvent->title }}</div>
                                        <div class="text-[11px] text-[#555] mt-0.5">
                                            {{ $msg->sharedEvent->event_date->translatedFormat('d F Y') }} • {{ $msg->sharedEvent->city }}
                                        </div>
                                        <a href="{{ route('events.show', $msg->sharedEvent->slug) }}"
                                           class="mt-2 inline-block px-3.5 py-1 rounded-full bg-[#A332F7] text-white text-[11px] font-semibold hover:bg-[#B64DFF]">
                                            Daftar Sekarang
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($msg->body)
                                <div class="max-w-[78%] px-4 py-2.5 text-sm leading-relaxed {{ $isMine ? 'bg-[#A332F7] text-white rounded-tl-[16px] rounded-tr-[16px] rounded-bl-[16px]' : 'bg-[#D9D9D9] text-[#15151C] rounded-tl-[16px] rounded-tr-[16px] rounded-br-[16px]' }}">
                                    {{ $msg->body }}
                                </div>
                            @endif

                            @if($msg->attachment_path)
                                <a href="{{ asset('storage/'.$msg->attachment_path) }}" target="_blank" class="mt-1 text-xs text-[#37B9D6] hover:underline">
                                    📎 Lihat Lampiran
                                </a>
                            @endif

                            <div class="mt-1 text-[10px] text-[#A1A1A1] flex items-center gap-1">
                                <span>{{ $msg->created_at->format('H.i') }}</span>
                                @if($isMine)
                                    <span class="{{ $msg->read_at ? 'text-[#A332F7]' : 'text-[#A1A1A1]' }}" title="{{ $msg->read_at ? 'Dibaca' : 'Terkirim' }}">
                                        {{ $msg->read_at ? '✓✓' : '✓' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex items-center justify-center text-xs text-[#A1A1A1]">
                            Belum ada pesan. Kirim sapaan pertama Anda!
                        </div>
                    @endforelse
                </div>

                <!-- Message Input Bar (Matches Figma Chat rounded light bar + purple + & send button) -->
                <form method="POST" action="{{ route('messages.store', $activeConversation) }}" enctype="multipart/form-data"
                      class="px-5 py-4 border-t border-white/[0.06] space-y-2.5">
                    @csrf
                    <div id="chatAttachTray" class="hidden flex flex-wrap items-center gap-3 p-3 rounded-xl bg-[#0B0B0B] border border-white/10 text-xs">
                        <select name="shared_event_id" class="orvia-input px-3 py-1.5 text-xs flex-1 min-w-[200px]">
                            <option value="">Bagikan kartu Agenda (opsional)...</option>
                            @foreach($shareableEvents as $ev)
                                <option value="{{ $ev->id }}">{{ $ev->title }} ({{ $ev->event_date->format('d M Y') }})</option>
                            @endforeach
                        </select>
                        <input type="file" name="attachment" class="text-xs text-[#A1A1A1]">
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex-1 flex items-center bg-[#D9D9D9] rounded-full px-3 py-1.5">
                            <button type="button"
                                    onclick="document.getElementById('chatAttachTray').classList.toggle('hidden')"
                                    class="w-7 h-7 rounded-full flex items-center justify-center text-[#A332F7] font-bold text-lg hover:bg-black/10 transition cursor-pointer"
                                    title="Lampirkan file atau bagikan Agenda">
                                +
                            </button>
                            <input type="text"
                                   name="body"
                                   autocomplete="off"
                                   placeholder="Tulis pesan..."
                                   class="flex-1 bg-transparent border-0 px-3 py-1 text-sm text-[#15151C] placeholder-[#666] focus:outline-none">
                        </div>

                        <button type="submit"
                                class="w-10 h-10 rounded-full bg-[#A332F7] text-white flex items-center justify-center hover:bg-[#B64DFF] transition shadow cursor-pointer shrink-0"
                                title="Kirim Pesan">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            @else
                <div class="flex-1 flex flex-col items-center justify-center p-10 text-center">
                    <div class="w-16 h-16 rounded-full bg-[#A332F7]/15 text-[#A332F7] flex items-center justify-center text-2xl mb-4">✉</div>
                    <h2 class="text-lg font-semibold text-white">Pilih atau Mulai Percakapan</h2>
                    <p class="text-xs text-[#A1A1A1] mt-1 max-w-sm">
                        Hubungi lembaga terverifikasi untuk koordinasi kolaborasi agenda, atau kirim pesan langsung ke sesama kreator ORVIA.
                    </p>
                    <button type="button"
                            onclick="document.getElementById('newChatModal').classList.remove('hidden')"
                            class="mt-5 px-6 py-2 orvia-btn-primary text-xs cursor-pointer">
                        Mulai Pesan Baru
                    </button>
                </div>
            @endif
        </section>
    </div>
</div>

<!-- New Conversation Modal -->
<div id="newChatModal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="orvia-card max-w-md w-full p-6 border border-white/10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Mulai Percakapan Baru</h3>
            <button type="button" onclick="document.getElementById('newChatModal').classList.add('hidden')" class="text-[#A1A1A1] hover:text-white cursor-pointer">✕</button>
        </div>
        <div class="max-h-80 overflow-y-auto divide-y divide-white/[0.06]">
            @foreach($contacts as $contact)
                <form method="POST" action="{{ route('messages.start', $contact->id) }}" class="py-2.5 flex items-center justify-between gap-3">
                    @csrf
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $contact->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-white truncate flex items-center gap-1">
                                <span class="truncate">{{ $contact->name }}</span>
                                <x-verified-badge :user="$contact" size="xs" />
                            </div>
                            <div class="text-[11px] text-[#A1A1A1]">@ {{ $contact->username }} • {{ $contact->role_label }}</div>
                        </div>
                    </div>
                    <button type="submit" class="px-3.5 py-1.5 rounded-full bg-[#A332F7] text-white text-xs font-semibold hover:bg-[#B64DFF] cursor-pointer shrink-0">
                        Pesan
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    const box = document.getElementById('chatMessagesBox');
    if (box) box.scrollTop = box.scrollHeight;
</script>
@endpush
@endsection
