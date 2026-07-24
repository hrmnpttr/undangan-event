{{-- Default (classic gold) cover — preserves the original entry overlay. --}}
@php
    $locale = app()->getLocale();
    $imgPrefix = config('undangan.image_prefix');
    $imgLang = $locale === 'en' ? config('undangan.image_lang_en') : config('undangan.image_lang_id');
    $prefixImg = fn (string $n, string $big = '') => $imgPrefix
        ? asset('storage/' . $imgPrefix . $imgLang . $n . $big . '.png')
        : null;

    // Prefer uploaded assets; fall back to the legacy prefix-based images.
    $cover        = $coverImage ?: $prefixImg('1');
    $content      = $contentImage ?: $prefixImg('2');
    $coverBig     = $coverImage ?: $prefixImg('1', 'big');
    $contentBig   = $contentImage ?: $prefixImg('2', 'big');
@endphp

<div class="undangan-entry-wrap mx-auto py-6 px-4 space-y-6">
    {{-- Image 1: cover with name overlay --}}
    <div class="relative rounded-2xl overflow-hidden shadow-2xl" style="box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.10);">
        <picture>
            @if($coverBig)<source media="(min-width: 768px)" srcset="{{ $coverBig }}">@endif
            <img src="{{ $cover ?: asset('favicon.svg') }}" class="w-full" alt="{{ $landing['title'] ?: __('undangan.event_title') }}">
        </picture>
        <div class="absolute undangan-overlay-mobile" style="top: 57%; left: 50%; transform: translateX(-50%); width: 62%; text-align: center; overflow: hidden;">
            <p class="text-gray-600 leading-tight" style="font-size: clamp(0.5rem, 2.4vw, 0.85rem);">{{ $locale === 'en' ? 'Dearest' : 'Kepada Yth.' }}</p>
            @if(str_starts_with($tamu->nama, 'Keluarga'))
                <p class="font-display font-bold text-gray-800 leading-tight mt-0.5" style="font-size: clamp(0.6rem, 3vw, 1.1rem); word-break: break-word;">Keluarga</p>
                <p class="font-display font-bold text-gray-800 leading-tight" style="font-size: clamp(0.6rem, 3vw, 1.1rem); word-break: break-word;">{{ trim(substr($tamu->nama, 8)) }}</p>
            @else
                <p class="font-display font-bold text-gray-800 leading-tight mt-0.5" style="font-size: clamp(0.6rem, 3vw, 1.1rem); word-break: break-word;">{{ $tamu->nama }}</p>
            @endif
            @if(!empty($tamu->deskripsi))
                <p class="text-gray-700 mt-0.5" style="font-size: clamp(0.5rem, 2.4vw, 0.9rem); word-break: break-word;">{{ $tamu->deskripsi }}</p>
            @endif
        </div>
        <div class="absolute undangan-overlay-desktop" style="top: 58%; left: 50%; transform: translateX(-50%); width: 42%; text-align: center;">
            <p class="text-gray-600 leading-tight" style="font-size: clamp(0.65rem, 1vw, 0.85rem);">{{ $locale === 'en' ? 'Dearest' : 'Kepada Yth.' }}</p>
            @if(str_starts_with($tamu->nama, 'Keluarga'))
                <p class="font-display font-bold text-gray-800 leading-tight mt-0.5" style="font-size: clamp(0.8rem, 1.2vw, 1.1rem); word-break: break-word;">Keluarga</p>
                <p class="font-display font-bold text-gray-800 leading-tight" style="font-size: clamp(0.8rem, 1.2vw, 1.1rem); word-break: break-word;">{{ trim(substr($tamu->nama, 8)) }}</p>
            @else
                <p class="font-display font-bold text-gray-800 leading-tight mt-0.5" style="font-size: clamp(0.8rem, 1.2vw, 1.1rem); word-break: break-word;">{{ $tamu->nama }}</p>
            @endif
            @if(!empty($tamu->deskripsi))
                <p class="text-gray-700 mt-0.5" style="font-size: clamp(0.65rem, 1vw, 0.85rem); word-break: break-word;">{{ $tamu->deskripsi }}</p>
            @endif
        </div>
    </div>

    {{-- Image 2: content --}}
    @if($content)
        <div class="rounded-2xl overflow-hidden shadow-2xl" style="box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.10);">
            <picture>
                @if($contentBig)<source media="(min-width: 768px)" srcset="{{ $contentBig }}">@endif
                <img src="{{ $content }}" class="w-full" alt="{{ $landing['title'] ?: __('undangan.event_title') }}">
            </picture>
        </div>
    @endif

    <style>
        .undangan-entry-wrap { max-width: 24rem; }
        .undangan-overlay-desktop { display: none; }
        @media (min-width: 768px) {
            .undangan-entry-wrap { max-width: 56rem; }
            .undangan-overlay-mobile { display: none; }
            .undangan-overlay-desktop { display: block; }
        }
    </style>

    <div class="h-28"></div>
</div>
