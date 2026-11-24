<!DOCTYPE html>
<html lang="{{ auth()->user()->language_preference ?? 'id' }}"
      class="{{ (auth()->user()->theme_preference ?? 'dark') === 'light' ? 'theme-light' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ORVIA — Ruang Komunitas, Artikel & Agenda Kreatif')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-[#0B0B0B] text-[#FEFEFE] font-sans antialiased selection:bg-[#A332F7] selection:text-white">
    <div class="min-h-screen flex flex-col lg:flex-row max-w-[1440px] mx-auto">
        <x-orvia-sidebar />

        <main class="flex-1 min-w-0 px-4 sm:px-6 lg:px-10 py-6 lg:py-8 pb-24 lg:pb-12">
            @if(session('success'))
                <div class="mb-6 flex items-center justify-between gap-3 rounded-2xl bg-[#1E1529] border border-[#A332F7]/50 px-5 py-3.5 text-sm text-white shadow-lg">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#A332F7] shrink-0"></span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-[#A1A1A1] hover:text-white cursor-pointer">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 flex items-center justify-between gap-3 rounded-2xl bg-[#2A1216] border border-[#F31415]/50 px-5 py-3.5 text-sm text-white shadow-lg">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#F31415] shrink-0"></span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-[#A1A1A1] hover:text-white cursor-pointer">✕</button>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 flex items-center justify-between gap-3 rounded-2xl bg-[#13222C] border border-[#37B9D6]/50 px-5 py-3.5 text-sm text-white shadow-lg">
                    <span>{{ session('info') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-[#A1A1A1] hover:text-white cursor-pointer">✕</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-2xl bg-[#2A1216] border border-[#F31415]/50 px-5 py-3.5 text-sm text-white">
                    <div class="font-semibold mb-1">Periksa kembali input Anda:</div>
                    <ul class="list-disc list-inside text-xs text-red-200 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
