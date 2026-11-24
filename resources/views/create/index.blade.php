@extends('layouts.orvia')

@section('title', 'Buat Konten — ORVIA')

@section('content')
@php $me = auth()->user(); @endphp
<div class="space-y-6">
    <!-- Header (Matches Figma Buat Postingan 1 & 2: 0:3303 & 0:1051) -->
    <div>
        <h1 class="text-[32px] font-semibold leading-tight text-[#FEFEFE]">Buat Postingan</h1>
        <p class="text-xs text-[#A1A1A1] mt-1">Bagikan informasi, wawasan, dan kegiatan terbaru ke komunitas ORVIA.</p>
    </div>

    <!-- Role-Aware Tab Switcher -->
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="{{ route('create.index', ['tab' => 'post']) }}"
           class="px-5 py-1.5 text-sm font-semibold {{ $tab === 'post' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
            Postingan
        </a>
        <a href="{{ route('create.index', ['tab' => 'article']) }}"
           class="px-5 py-1.5 text-sm font-semibold {{ $tab === 'article' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
            Artikel
        </a>
        @if($me->canCreateEvents())
            <a href="{{ route('create.index', ['tab' => 'event']) }}"
               class="px-5 py-1.5 text-sm font-semibold {{ $tab === 'event' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
                Agenda Kegiatan
            </a>
        @endif
        @if($me->canCreateTopics())
            <a href="{{ route('create.index', ['tab' => 'topic']) }}"
               class="px-5 py-1.5 text-sm font-semibold {{ $tab === 'topic' ? 'orvia-pill-active rounded-full' : 'orvia-pill' }}">
                Topik (O/)
            </a>
        @endif
    </div>

    <!-- TAB 1: POSTINGAN (Matches Figma Buat Postingan 1 0:3303) -->
    @if($tab === 'post')
        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="orvia-card p-6 sm:p-8 space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Topik Komunitas (O/...)</label>
                    <select name="topic_id" class="orvia-input w-full px-4 py-2.5 text-sm">
                        <option value="">Umum (Tanpa Topik Khusus)</option>
                        @foreach($topics as $t)
                            <option value="{{ $t->id }}">{{ $t->display_handle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Kategori</label>
                    <select name="category_id" class="orvia-input w-full px-4 py-2.5 text-sm">
                        <option value="">Pilih Kategori...</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs text-[#A1A1A1] mb-1.5">Judul</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Tulis judul diskusi atau karya..."
                       class="orvia-input w-full px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block text-xs text-[#A1A1A1] mb-1.5">Isi</label>
                <textarea name="body" rows="7" placeholder="Ceritakan ide, pertanyaan, atau karya Anda..."
                          class="orvia-input w-full px-4 py-3 text-sm">{{ old('body') }}</textarea>
            </div>

            <div>
                <label class="block text-xs text-[#A1A1A1] mb-1.5">Media (Gambar / Video opsional)</label>
                <input type="file" name="media" accept="image/*,video/*" class="orvia-input w-full px-4 py-2.5 text-xs text-[#A1A1A1]">
            </div>

            @if($me->canCollaborate() && $verifiedInstitutions->isNotEmpty())
                <div class="p-4 rounded-xl bg-[#0B0B0B] border border-[#FACC15]/25">
                    <label class="block text-xs font-semibold text-[#FACC15] mb-2">Undang Kolaborasi Lembaga Terverifikasi (Opsional)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($verifiedInstitutions as $inst)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#15151C] border border-white/10 text-xs cursor-pointer hover:border-[#FACC15]">
                                <input type="checkbox" name="collaborator_ids[]" value="{{ $inst->id }}" class="accent-[#A332F7]">
                                <span>{{ $inst->name }}</span>
                                <x-verified-badge :user="$inst" size="xs" />
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="submit" name="status" value="draft"
                        class="px-5 py-2 rounded-lg border border-white/20 text-xs font-semibold text-[#A1A1A1] hover:text-white hover:border-white/40 transition cursor-pointer">
                    Simpan Draf
                </button>
                <button type="submit" name="status" value="published"
                        class="px-7 py-2 rounded-lg bg-[#A332F7] text-white text-xs font-semibold hover:bg-[#B64DFF] transition cursor-pointer">
                    Posting
                </button>
            </div>
        </form>
    @endif

    <!-- TAB 2: ARTIKEL (Matches Figma Buat Postingan 2 0:1051 Substack Editor!) -->
    @if($tab === 'article')
        <form method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data" class="orvia-card p-6 sm:p-10 space-y-6">
            @csrf
            <!-- Rich Text Toolbar -->
            <div class="flex flex-wrap items-center justify-center gap-2 pb-4 border-b border-white/[0.08] text-xs text-[#A1A1A1]">
                <button type="button" onclick="orviaFmt('bold')" class="px-2.5 py-1 rounded hover:bg-white/10 text-white font-bold cursor-pointer">B</button>
                <button type="button" onclick="orviaFmt('italic')" class="px-2.5 py-1 rounded hover:bg-white/10 text-white italic cursor-pointer">I</button>
                <button type="button" onclick="orviaFmt('h2')" class="px-2.5 py-1 rounded hover:bg-white/10 text-white cursor-pointer">H2</button>
                <button type="button" onclick="orviaFmt('quote')" class="px-2.5 py-1 rounded hover:bg-white/10 text-white cursor-pointer">“ Quote</button>
                <button type="button" onclick="orviaFmt('ul')" class="px-2.5 py-1 rounded hover:bg-white/10 text-white cursor-pointer">• List</button>
                <span class="mx-2 text-white/15">|</span>
                <select name="topic_id" class="bg-[#0B0B0B] border border-white/10 rounded-full px-3 py-1 text-xs text-white">
                    <option value="">Topik (Opsional)</option>
                    @foreach($topics as $t)
                        <option value="{{ $t->id }}">{{ $t->display_handle }}</option>
                    @endforeach
                </select>
                <label class="px-3 py-1 rounded-full bg-[#0B0B0B] border border-white/10 text-xs text-white cursor-pointer hover:border-[#A332F7]">
                    🖼 Cover Artikel
                    <input type="file" name="image" accept="image/*" class="hidden">
                </label>
            </div>

            <!-- Centered Substack-style Title & Subtitle Inputs -->
            <div class="max-w-2xl mx-auto space-y-4">
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="JUDUL"
                       class="w-full bg-transparent text-center text-3xl sm:text-5xl font-bold text-white placeholder-white/40 focus:outline-none">

                <input type="text" name="subtitle" value="{{ old('subtitle') }}" placeholder="Sub Judul"
                       class="w-full bg-transparent text-center text-base text-[#A1A1A1] placeholder-[#A1A1A1]/60 focus:outline-none">

                <textarea id="articleEditorBody" name="body" rows="12" required placeholder="Mulai Menulis....."
                          class="w-full bg-transparent pt-6 text-base text-[#E6E6F0] placeholder-[#A1A1A1]/60 leading-relaxed focus:outline-none border-t border-white/[0.06]">{{ old('body') }}</textarea>
            </div>

            @if($me->canCollaborate() && $verifiedInstitutions->isNotEmpty())
                <div class="max-w-2xl mx-auto p-4 rounded-xl bg-[#0B0B0B] border border-[#FACC15]/25">
                    <label class="block text-xs font-semibold text-[#FACC15] mb-2">Kolaborasi Mitra Editorial Lembaga Terverifikasi</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($verifiedInstitutions as $inst)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#15151C] border border-white/10 text-xs cursor-pointer">
                                <input type="checkbox" name="collaborator_ids[]" value="{{ $inst->id }}" class="accent-[#A332F7]">
                                <span>{{ $inst->name }}</span>
                                <x-verified-badge :user="$inst" size="xs" />
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="max-w-2xl mx-auto flex items-center justify-end gap-3 pt-2">
                <button type="submit" name="status" value="draft" class="px-5 py-2 rounded-lg border border-white/20 text-xs font-semibold text-[#A1A1A1] hover:text-white cursor-pointer">
                    Simpan Draf
                </button>
                <button type="submit" name="status" value="published" class="px-7 py-2 rounded-lg bg-[#A332F7] text-white text-xs font-semibold hover:bg-[#B64DFF] cursor-pointer">
                    Terbitkan Artikel
                </button>
            </div>
        </form>
    @endif

    <!-- TAB 3: AGENDA KEGIATAN (Verified Institution & Admin ONLY) -->
    @if($tab === 'event' && $me->canCreateEvents())
        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="orvia-card p-6 sm:p-8 space-y-5">
            @csrf
            <div class="flex items-center gap-2 text-xs text-[#FACC15] bg-[#FACC15]/10 border border-[#FACC15]/30 rounded-xl px-4 py-2.5">
                <x-verified-badge :user="$me" size="sm" />
                <span>Fitur eksklusif Lembaga Terverifikasi — Agenda Anda akan tampil di Direktori Agenda Acara & Widget Beranda.</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Nama Acara / Agenda *</label>
                    <input type="text" name="title" required placeholder="Contoh: KMDGI 2026 Bandung" class="orvia-input w-full px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Jenis Acara *</label>
                    <select name="event_type" required class="orvia-input w-full px-4 py-2.5 text-sm">
                        <option value="Pameran">Pameran</option>
                        <option value="Kompetisi">Kompetisi</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Seminar & Talkshow">Seminar & Talkshow</option>
                        <option value="Festival">Festival</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Tanggal Acara *</label>
                    <input type="date" name="event_date" required class="orvia-input w-full px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Batas Akhir Pendaftaran</label>
                    <input type="date" name="registration_deadline" class="orvia-input w-full px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Kota / Wilayah *</label>
                    <input type="text" name="city" required placeholder="Bandung, Jawa Barat" class="orvia-input w-full px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Lokasi / Venue</label>
                    <input type="text" name="location_name" placeholder="Universitas Pendidikan Indonesia" class="orvia-input w-full px-4 py-2.5 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs text-[#A1A1A1] mb-1.5">Deskripsi Lengkap Acara *</label>
                <textarea name="description" rows="5" required placeholder="Jelaskan tema, syarat pendaftaran, dan manfaat acara..." class="orvia-input w-full px-4 py-3 text-sm"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Poster Acara (Kotak)</label>
                    <input type="file" name="poster" accept="image/*" class="orvia-input w-full px-4 py-2 text-xs text-[#A1A1A1]">
                </div>
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Cover Banner Acara</label>
                    <input type="file" name="banner" accept="image/*" class="orvia-input w-full px-4 py-2 text-xs text-[#A1A1A1]">
                </div>
            </div>

            <!-- Timeline Schedules -->
            <div class="space-y-2.5">
                <label class="block text-xs font-semibold text-white">Rangkaian Jadwal Kegiatan</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <input type="text" name="schedule_titles[]" placeholder="Tahap 1 (mis. Pendaftaran Dibuka)" class="orvia-input px-3 py-2 text-xs">
                    <input type="text" name="schedule_dates[]" placeholder="Tanggal (mis. 01 Oktober 2026)" class="orvia-input px-3 py-2 text-xs">
                    <input type="text" name="schedule_titles[]" placeholder="Tahap 2 (mis. Batas Pengumpulan)" class="orvia-input px-3 py-2 text-xs">
                    <input type="text" name="schedule_dates[]" placeholder="Tanggal (mis. 01 November 2026)" class="orvia-input px-3 py-2 text-xs">
                    <input type="text" name="schedule_titles[]" placeholder="Tahap 3 (mis. Puncak Acara)" class="orvia-input px-3 py-2 text-xs">
                    <input type="text" name="schedule_dates[]" placeholder="Tanggal (mis. 09 November 2026)" class="orvia-input px-3 py-2 text-xs">
                </div>
            </div>

            @if($verifiedInstitutions->isNotEmpty())
                <div class="p-4 rounded-xl bg-[#0B0B0B] border border-[#FACC15]/25">
                    <label class="block text-xs font-semibold text-[#FACC15] mb-2">Undang Lembaga Co-Organizer (Kolaborasi Agenda)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($verifiedInstitutions as $inst)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#15151C] border border-white/10 text-xs cursor-pointer">
                                <input type="checkbox" name="collaborator_ids[]" value="{{ $inst->id }}" class="accent-[#A332F7]">
                                <span>{{ $inst->name }}</span>
                                <x-verified-badge :user="$inst" size="xs" />
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                <button type="submit" class="px-8 py-2.5 orvia-btn-primary text-sm cursor-pointer">Terbitkan Agenda Kegiatan</button>
            </div>
        </form>
    @endif

    <!-- TAB 4: BUAT TOPIK BARU (Verified Institution & Admin ONLY) -->
    @if($tab === 'topic' && $me->canCreateTopics())
        <form method="POST" action="{{ route('topics.store') }}" enctype="multipart/form-data" class="orvia-card p-6 sm:p-8 space-y-5 max-w-2xl">
            @csrf
            <div class="flex items-center gap-2 text-xs text-[#FACC15] bg-[#FACC15]/10 border border-[#FACC15]/30 rounded-xl px-4 py-2.5">
                <x-verified-badge :user="$me" size="sm" />
                <span>Fitur eksklusif Lembaga Terverifikasi — Buat ruang sub-komunitas baru berformat <strong>O/NamaTopik</strong>.</span>
            </div>

            <div>
                <label class="block text-xs text-[#A1A1A1] mb-1.5">Nama Topik *</label>
                <div class="flex items-center">
                    <span class="px-3.5 py-2.5 bg-[#1D1D27] border border-r-0 border-[#262634] rounded-l-[10px] text-sm font-bold text-[#A332F7]">O/</span>
                    <input type="text" name="name" required placeholder="Illustration" class="orvia-input flex-1 rounded-l-none px-4 py-2.5 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs text-[#A1A1A1] mb-1.5">Deskripsi Topik</label>
                <textarea name="description" rows="4" placeholder="Jelaskan fokus diskusi dan aturan komunitas..." class="orvia-input w-full px-4 py-3 text-sm"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Ikon / Avatar Topik</label>
                    <input type="file" name="avatar" accept="image/*" class="orvia-input w-full px-3 py-2 text-xs text-[#A1A1A1]">
                </div>
                <div>
                    <label class="block text-xs text-[#A1A1A1] mb-1.5">Banner Topik</label>
                    <input type="file" name="banner" accept="image/*" class="orvia-input w-full px-3 py-2 text-xs text-[#A1A1A1]">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-7 py-2.5 orvia-btn-primary text-sm cursor-pointer">Buat Topik</button>
            </div>
        </form>
    @endif
</div>

@push('scripts')
<script>
    function orviaFmt(type) {
        const ta = document.getElementById('articleEditorBody');
        if (!ta) return;
        const start = ta.selectionStart;
        const end = ta.selectionEnd;
        const selected = ta.value.substring(start, end) || 'teks';
        const wrap = {
            bold: `<strong>${selected}</strong>`,
            italic: `<em>${selected}</em>`,
            h2: `\n<h2>${selected}</h2>\n`,
            quote: `\n<blockquote>${selected}</blockquote>\n`,
            ul: `\n<ul>\n  <li>${selected}</li>\n</ul>\n`
        }[type] || selected;
        ta.setRangeText(wrap, start, end, 'end');
        ta.focus();
    }
</script>
@endpush
@endsection
