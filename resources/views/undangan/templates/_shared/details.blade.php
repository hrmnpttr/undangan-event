{{-- Shared, data-driven detail blocks. Rendered only when data is present.
     Themed through CSS variables so every template matches its palette. --}}
@php
    $kind = $theme['kind'] ?? 'general';
    $venueName = $landing['venue_name'] ?? '';
    $venueAddress = $landing['venue_address'] ?? '';

    // Free-map coordinates take priority; fall back to a Google search/URL.
    $coords = \App\Support\LandingConfig::coords();
    if ($coords) {
        $mapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . $coords[0] . ',' . $coords[1];
    } else {
        $venueMaps = $landing['venue_maps'] ?: config('undangan.venue_maps');
        $mapsUrl = $venueMaps
            ? (\Illuminate\Support\Str::startsWith(strtolower($venueMaps), 'http')
                ? $venueMaps
                : 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($venueMaps))
            : null;
    }
@endphp

{{-- Quote / verse --}}
@if(!empty($landing['quote']))
    <div class="glass-card rounded-2xl p-6 text-center shadow-lg">
        <p class="font-display italic text-gray-700 leading-relaxed">“{{ $landing['quote'] }}”</p>
        @if(!empty($landing['quote_source']))
            <div class="u-divider"></div>
            <p class="text-sm text-accent font-medium">{{ $landing['quote_source'] }}</p>
        @endif
    </div>
@endif

{{-- Wedding schedule --}}
@if($kind === 'wedding' && (!empty($landing['akad_text']) || !empty($landing['resepsi_text'])))
    <div class="grid gap-4 {{ (!empty($landing['akad_text']) && !empty($landing['resepsi_text'])) ? 'sm:grid-cols-2' : '' }}">
        @if(!empty($landing['akad_text']))
            <div class="glass-card rounded-2xl p-5 text-center shadow-lg">
                <h3 class="font-display text-lg font-bold text-accent">
                    {{ $template === 'wedding-islam' ? 'Akad Nikah' : 'Pemberkatan' }}
                </h3>
                <div class="u-divider"></div>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $landing['akad_text'] }}</p>
            </div>
        @endif
        @if(!empty($landing['resepsi_text']))
            <div class="glass-card rounded-2xl p-5 text-center shadow-lg">
                <h3 class="font-display text-lg font-bold text-accent">Resepsi</h3>
                <div class="u-divider"></div>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $landing['resepsi_text'] }}</p>
            </div>
        @endif
    </div>
@endif

{{-- Event agenda / rundown --}}
@if($kind === 'event' && count($agenda))
    <div class="glass-card rounded-2xl p-6 shadow-lg">
        <h3 class="font-display text-lg font-bold text-gray-800 mb-4 text-center">
            {{ app()->getLocale() === 'en' ? 'Agenda' : 'Rundown Acara' }}
        </h3>
        <div class="space-y-3">
            @foreach($agenda as $row)
                <div class="flex gap-3">
                    <div class="shrink-0 w-20 text-sm font-semibold text-accent">{{ $row['time'] ?? '' }}</div>
                    <div class="flex-1 border-l-2 border-accent pl-3">
                        <p class="text-sm font-medium text-gray-800">{{ $row['title'] ?? '' }}</p>
                        @if(!empty($row['desc']))
                            <p class="text-xs text-gray-500">{{ $row['desc'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Event meta (host / speaker / dress code) --}}
@if($kind === 'event' && (!empty($landing['host_org']) || !empty($landing['speaker']) || !empty($landing['dress_code'])))
    <div class="glass-card rounded-2xl p-6 shadow-lg space-y-2 text-center">
        @if(!empty($landing['host_org']))
            <p class="text-sm text-gray-500">Diselenggarakan oleh</p>
            <p class="font-semibold text-gray-800">{{ $landing['host_org'] }}</p>
        @endif
        @if(!empty($landing['speaker']))
            <div class="u-divider"></div>
            <p class="text-sm text-gray-500">Pembicara</p>
            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $landing['speaker'] }}</p>
        @endif
        @if(!empty($landing['dress_code']))
            <p class="text-xs text-gray-500 pt-1">Dress code: <span class="text-accent font-medium">{{ $landing['dress_code'] }}</span></p>
        @endif
    </div>
@endif

{{-- Venue --}}
@if($venueName || $venueAddress || $mapsUrl)
    <div class="glass-card rounded-2xl p-6 shadow-lg text-center">
        <h3 class="font-display text-lg font-bold text-gray-800">
            {{ app()->getLocale() === 'en' ? 'Location' : 'Lokasi Acara' }}
        </h3>
        <div class="u-divider"></div>
        @if($venueName)<p class="font-medium text-gray-800">{{ $venueName }}</p>@endif
        @if($venueAddress)<p class="text-sm text-gray-500 mt-1 whitespace-pre-line">{{ $venueAddress }}</p>@endif

        {{-- Free embedded map (OpenStreetMap / Leaflet) — only when coordinates exist. --}}
        @if($coords)
            @assets
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            @endassets

            <div wire:ignore class="mt-4">
                <div id="undangan-venue-map" class="h-56 w-full rounded-xl overflow-hidden ring-1 ring-black/10"
                     style="background:#e5e7eb;" data-lat="{{ $coords[0] }}" data-lng="{{ $coords[1] }}"></div>
            </div>

            @script
            <script>
                (function () {
                    const el = document.getElementById('undangan-venue-map');
                    if (!el || el._leafletInit) return;
                    const lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
                    const start = () => {
                        if (!window.L) { return setTimeout(start, 80); }
                        el._leafletInit = true;
                        const map = L.map(el, { scrollWheelZoom: false }).setView([lat, lng], 16);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19, attribution: '&copy; OpenStreetMap'
                        }).addTo(map);
                        const icon = L.divIcon({ className: '', html: '<div style="font-size:26px;line-height:1">📍</div>', iconSize: [26, 26], iconAnchor: [13, 26] });
                        L.marker([lat, lng], { icon }).addTo(map);
                        setTimeout(() => map.invalidateSize(), 200);
                        // Redraw when the container becomes visible (guest taps "enter").
                        if (window.ResizeObserver) {
                            new ResizeObserver(() => map.invalidateSize()).observe(el);
                        }
                    };
                    start();
                })();
            </script>
            @endscript
        @endif

        @if($mapsUrl)
            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener"
               class="mt-3 inline-flex items-center gap-2 px-6 py-2 rounded-full text-sm btn-gold shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ app()->getLocale() === 'en' ? 'Open in Google Maps' : 'Buka di Google Maps' }}
            </a>
        @endif
    </div>
@endif

{{-- Gallery --}}
@if(count($galleryImages))
    <div class="glass-card rounded-2xl p-4 shadow-lg">
        <div class="grid grid-cols-3 gap-2">
            @foreach($galleryImages as $img)
                <img src="{{ $img }}" class="aspect-square w-full rounded-lg object-cover" alt="Galeri" loading="lazy">
            @endforeach
        </div>
    </div>
@endif

{{-- Custom HTML (admin-provided) --}}
@if(($theme['kind'] ?? '') === 'custom' && !empty($landing['custom_html']))
    <div class="glass-card rounded-2xl p-4 shadow-lg overflow-hidden">
        {!! $landing['custom_html'] !!}
    </div>
@endif
