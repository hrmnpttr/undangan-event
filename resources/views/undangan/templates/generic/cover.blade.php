{{-- Generic entry cover for all non-default templates.
     Branches on $template for religion / event ornaments. Themed via CSS vars. --}}
@php
    $kind = $theme['kind'] ?? 'general';
    $isWedding = $kind === 'wedding';
    $title = $landing['title'] ?: __('undangan.event_title');
    $groom = $landing['groom_name'] ?? '';
    $bride = $landing['bride_name'] ?? '';
    $greetLabel = app()->getLocale() === 'en' ? 'Dearest' : 'Kepada Yth.';
@endphp

<div class="undangan-entry-wrap mx-auto py-8 px-4">
    <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-white/70 backdrop-blur"
         style="box-shadow: 0 12px 40px rgba(0,0,0,0.18);">

        {{-- Optional cover photo band --}}
        @if($coverImage)
            <div class="w-full aspect-[3/4] sm:aspect-[16/10] overflow-hidden">
                <img src="{{ $coverImage }}" class="w-full h-full object-cover" alt="{{ $title }}">
            </div>
        @endif

        <div class="p-8 text-center space-y-3">
            {{-- Ornament / opening --}}
            @switch($template)
                @case('wedding-islam')
                    <p class="font-display text-xl text-accent" dir="rtl" lang="ar">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
                    <p class="text-xs uppercase tracking-[0.3em] text-gray-500">Walimatul 'Urs</p>
                    @break
                @case('wedding-kristen')
                @case('wedding-katolik')
                    <p class="text-3xl text-accent">✝</p>
                    <p class="text-xs uppercase tracking-[0.3em] text-gray-500">{{ app()->getLocale() === 'en' ? 'The Wedding Of' : 'Undangan Pernikahan' }}</p>
                    @break
                @case('wedding-budha')
                    <p class="text-3xl text-accent">☸</p>
                    <p class="text-xs uppercase tracking-[0.3em] text-gray-500">Undangan Pernikahan</p>
                    @break
                @case('corporate')
                    <p class="text-xs uppercase tracking-[0.3em] text-accent font-semibold">{{ $landing['host_org'] ?: 'Undangan Resmi' }}</p>
                    @break
                @case('tech')
                    <p class="font-mono text-sm text-accent">&lt;/&gt; {{ $landing['host_org'] ?: 'meetup' }}</p>
                    @break
                @default
                    <p class="text-xs uppercase tracking-[0.3em] text-gray-500">{{ __('undangan.greeting') }}</p>
            @endswitch

            {{-- Title / couple --}}
            @if($isWedding && ($groom || $bride))
                <h1 class="font-display text-3xl font-bold text-gray-800 leading-snug">
                    {{ $groom ?: '—' }}<br>
                    <span class="text-accent text-2xl">&amp;</span><br>
                    {{ $bride ?: '—' }}
                </h1>
            @else
                <h1 class="font-display text-3xl font-bold text-gray-800 leading-snug">{{ $title }}</h1>
            @endif

            @if($landing['subtitle'])
                <p class="text-sm text-gray-500 italic">{{ $landing['subtitle'] }}</p>
            @endif

            <div class="u-divider"></div>

            @if($landing['event_date_text'])
                <p class="text-sm font-medium text-gray-700">{{ $landing['event_date_text'] }}</p>
            @endif
            @if($landing['event_time_text'])
                <p class="text-xs text-gray-500">{{ $landing['event_time_text'] }}</p>
            @endif

            {{-- Addressed guest --}}
            <div class="pt-4">
                <p class="text-xs text-gray-500">{{ $greetLabel }}</p>
                <p class="font-display text-lg font-semibold text-gray-800">{{ $tamu->nama }}</p>
                @if(!empty($tamu->deskripsi))
                    <p class="text-sm text-gray-500">{{ $tamu->deskripsi }}</p>
                @endif
            </div>
        </div>
    </div>

    <style>
        .undangan-entry-wrap { max-width: 28rem; }
    </style>

    <div class="h-28"></div>
</div>
