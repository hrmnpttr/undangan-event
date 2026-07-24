<div class="min-h-screen flex flex-col">
    {{-- Apply the selected template's colour palette. --}}
    <style>
        :root {
            --u-accent: {{ $theme['accent'] ?? '#b8860b' }};
            --u-accent-strong: {{ $theme['accent_strong'] ?? '#8a5a00' }};
            --u-bg: {{ $theme['bg'] ?? 'linear-gradient(135deg,#fdf2f8 0%,#fef3c7 50%,#fdf2f8 100%)' }};
        }
    </style>

    @php
        $tpl = fn (string $part) => view()->exists("undangan.templates.$template.$part")
            ? "undangan.templates.$template.$part"
            : "undangan.templates.generic.$part";
    @endphp

    {{-- Audio Player (uploaded track, else bundled default). --}}
    @if($musicUrl)
        <audio id="bg-audio" loop preload="auto" wire:ignore>
            <source src="{{ $musicUrl }}" type="audio/mpeg">
        </audio>
        @if($musicAutoplay)
            <script>
                (function () {
                    function startAudio() {
                        var a = document.getElementById('bg-audio');
                        if (a && a.paused) { a.play().catch(function () {}); }
                        document.removeEventListener('click', startAudio);
                        document.removeEventListener('touchstart', startAudio);
                        document.removeEventListener('scroll', startAudio);
                    }
                    var audio = document.getElementById('bg-audio');
                    if (audio) {
                        audio.play().catch(function () {
                            document.addEventListener('click', startAudio, { once: true });
                            document.addEventListener('touchstart', startAudio, { once: true });
                            document.addEventListener('scroll', startAudio, { once: true });
                        });
                    }
                })();
            </script>
        @endif
    @endif

    {{-- Entry Overlay --}}
    @if(!$entered)
        <div class="fixed inset-0 z-50 overflow-y-auto" style="background: var(--u-bg);">
            @include($tpl('cover'))

            {{-- Floating CTA Button --}}
            <div class="fixed bottom-0 left-0 right-0 z-10 pb-6 pt-8 flex flex-col items-center gap-3"
                 style="background: linear-gradient(to top, rgba(255,255,255,1) 55%, transparent 100%);">
                @if($konfirmasiData)
                    {{-- Already confirmed: show view + download options --}}
                    <button
                        wire:click="enter"
                        onclick="var a=document.getElementById('bg-audio'); if(a){a.play().catch(()=>{})}"
                        class="btn-gold px-10 py-4 rounded-full text-lg shadow-xl inline-flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('undangan.lihat_konfirmasi') }}
                    </button>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('undangan.pdf', $tamu->kode_unik) }}"
                           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-medium border-2 border-amber-600 text-amber-700 bg-white hover:bg-amber-50 shadow"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ __('undangan.card_download') }}
                        </a>
                        @if(!$isExpired)
                            <button
                                wire:click="enter"
                                onclick="var a=document.getElementById('bg-audio'); if(a){a.play().catch(()=>{})} setTimeout(()=>{ @this.call('toggleForm') }, 300)"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-medium border-2 border-gray-400 text-gray-600 bg-white hover:bg-gray-50 shadow"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                {{ __('undangan.konfirmasi_update') }}
                            </button>
                        @endif
                    </div>
                @else
                    {{-- Not yet confirmed --}}
                    <button
                        wire:click="enter"
                        onclick="var a=document.getElementById('bg-audio'); if(a){a.play().catch(()=>{})}"
                        class="btn-gold px-10 py-4 rounded-full text-lg shadow-xl"
                    >
                        {{ __('undangan.konfirmasi_button') }}
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="flex-1 px-4 py-8 max-w-lg mx-auto w-full space-y-6 {{ !$entered ? 'hidden' : '' }}">

        {{-- Audio Control --}}
        @if($musicUrl)
            <div class="fixed top-4 right-4 z-40">
                <button
                    onclick="const a=document.getElementById('bg-audio'); if(!a)return; if(a.paused){a.play();this.innerHTML='🔊'}else{a.pause();this.innerHTML='🔇'}"
                    class="glass-card w-10 h-10 rounded-full flex items-center justify-center text-lg shadow-md"
                >
                    🔊
                </button>
            </div>
        @endif

        {{-- Template hero / header --}}
        @include($tpl('hero'))

        {{-- Color indicator for VIP/VVIP (no text, just colored accent) --}}
        @if($tamu->jenis !== 'umum')
            <div class="flex justify-center">
                <div class="w-16 h-1.5 rounded-full
                    {{ $tamu->jenis === 'VVIP' ? 'bg-red-500' : 'bg-yellow-400' }}"></div>
            </div>
        @endif

        {{-- Greeting --}}
        <div class="glass-card rounded-2xl p-6 text-center shadow-lg
            {{ $tamu->jenis === 'VVIP' ? 'border-l-4 border-red-500' : ($tamu->jenis === 'VIP' ? 'border-l-4 border-yellow-400' : '') }}">
            <p class="text-gray-500 text-sm mb-1">{{ __('undangan.greeting') }}</p>
            <h2 class="font-display text-2xl font-bold text-gray-800">{{ $tamu->nama }}</h2>
            @if(!empty($tamu->deskripsi))            
            <h3 class="font-display text-xl font-bold text-gray-800">{{ $tamu->deskripsi }}</h3>
            @endif
            <p class="text-gray-500 text-sm mt-2">
                {{ __('undangan.undangan_untuk', ['count' => $tamu->jumlah_orang]) }}
            </p>
        </div>

        {{-- Template detail blocks (quote, schedule/agenda, venue, gallery) --}}
        @include($tpl('details'))

        {{-- Flash Messages --}}
        @if(session()->has('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 text-center">
                {{ session('success') }}
            </div>
        @endif
        @if(session()->has('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 text-center">
                {{ session('error') }}
            </div>
        @endif

        {{-- Konfirmasi Status --}}
        @if($konfirmasiData)
            @php $tidakHadir = (int) $konfirmasiData->jumlah_hadir === 0; @endphp
            <div class="glass-card rounded-2xl p-6 shadow-lg space-y-4
                {{ $tidakHadir ? 'border-l-4 border-gray-400' : ($tamu->jenis === 'VVIP' ? 'border-l-4 border-red-500' : ($tamu->jenis === 'VIP' ? 'border-l-4 border-yellow-400' : '')) }}">
                <div class="text-center">
                    @if($tidakHadir)
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-3">
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-gray-600">{{ __('undangan.tidak_hadir_status') }}</p>
                    @else
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    @endif
                    <p class="text-sm text-gray-500">{{ __('undangan.konfirmasi_terakhir') }}</p>
                    <p class="font-semibold text-gray-800">
                        {{ $konfirmasiData->confirmed_at->format('d F Y, H:i') }}
                    </p>
                </div>

                {{-- Confirmation Card (hidden when guest declined attendance) --}}
                @if(!$tidakHadir)
                <div class="border-2 rounded-xl p-5 bg-white space-y-4
                    {{ $tamu->jenis === 'VVIP' ? 'border-red-200' : ($tamu->jenis === 'VIP' ? 'border-yellow-200' : 'border-amber-200') }}">
                    <h3 class="font-display text-lg font-bold text-center text-gray-800">
                        {{ __('undangan.card_title') }}
                    </h3>

                    <div class="flex justify-center">
                        <div class="w-40 h-40">
                            {!! $tamu->getQrCodeSvg(5) !!}
                        </div>
                    </div>

                    <div class="text-center space-y-1">
                        <p class="font-bold text-xl text-gray-800">{{ $tamu->nama }}</p>
                        @if($tamu->jenis !== 'umum')
                            <div class="w-10 h-1 mx-auto rounded-full
                                {{ $tamu->jenis === 'VVIP' ? 'bg-red-500' : 'bg-yellow-400' }}"></div>
                        @endif
                    </div>

                    <div class="text-center text-sm text-gray-600 space-y-1">
                        <p>{{ __('undangan.card_jumlah') }}: <strong>{{ $konfirmasiData->jumlah_hadir }} {{ __('undangan.card_orang') }}</strong></p>
                        <p>{{ __('undangan.card_dikonfirmasi') }}: <strong>{{ $konfirmasiData->confirmed_at->format('d F Y') }}</strong></p>
                    </div>

                    {{-- Show penginapan info if assigned --}}
                    @if($konfirmasiData->penginapan)
                        <div class="border-t border-gray-100 pt-3">
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">{{ __('undangan.penginapan_info') }}</h4>
                            <p class="text-sm text-gray-600">{{ $konfirmasiData->penginapan->nama }}</p>
                            @if($publishLokasiMenginap)
                                @if($konfirmasiData->penginapan->alamat)
                                    <p class="text-xs text-gray-500">{{ $konfirmasiData->penginapan->alamat }}</p>
                                @endif
                                @if($konfirmasiData->penginapan->no_telp)
                                    <a href="tel:{{ $konfirmasiData->penginapan->no_telp }}" class="text-sm text-amber-700 hover:underline">
                                        {{ __('undangan.no_telp_penginapan') }}: {{ $konfirmasiData->penginapan->no_telp }}
                                    </a>
                                @endif
                                @if($konfirmasiData->penginapan->koordinat_maps)
                                    @php
                                        $mapsRaw = $konfirmasiData->penginapan->koordinat_maps;
                                        $mapsUrl = str_starts_with(strtolower($mapsRaw), 'http')
                                            ? $mapsRaw
                                            : 'https://www.google.com/maps?q=' . urlencode($mapsRaw);
                                    @endphp
                                    <div class="mt-1">
                                        <a href="{{ $mapsUrl }}" target="_blank" class="text-sm text-amber-700 hover:underline">
                                            {{ __('undangan.buka_maps') }}
                                        </a>
                                    </div>
                                @endif
                            @else
                                <p class="text-xs text-gray-500">{{ __('undangan.lokasi_menginap_belum_publish') }}</p>
                            @endif
                            @if($konfirmasiData->nomor_kamar)
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ __('undangan.nomor_kamar') }}: <strong>{{ $konfirmasiData->nomor_kamar }}</strong>
                                </p>
                            @endif
                        </div>
                    @endif

                    <div class="flex flex-col items-center gap-2">
                        {{-- Download PDF --}}
                        <a href="{{ route('undangan.pdf', $tamu->kode_unik) }}"
                           class="btn-gold inline-flex items-center gap-2 px-6 py-2 rounded-full text-sm shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ __('undangan.card_download') }}
                        </a>

                        {{-- Maps to event location --}}
                        @php
                            $eventVenue = config('undangan.venue_maps') ?: config('undangan.venue_name');
                            $eventMapsUrl = \Illuminate\Support\Str::startsWith(strtolower($eventVenue), 'http')
                                ? $eventVenue
                                : 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($eventVenue);
                        @endphp
                        <a href="{{ $eventMapsUrl }}" target="_blank"
                           class="inline-flex items-center gap-2 px-6 py-2 rounded-full text-sm border border-amber-600 text-amber-700 hover:bg-amber-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('undangan.navigasi_acara') }}
                        </a>

                        {{-- Maps to penginapan --}}
                        @if($konfirmasiData->penginapan && $publishLokasiMenginap && $konfirmasiData->penginapan->koordinat_maps)
                            @php
                                $pMapsRaw = $konfirmasiData->penginapan->koordinat_maps;
                                $pMapsUrl = str_starts_with(strtolower($pMapsRaw), 'http')
                                    ? $pMapsRaw
                                    : 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($pMapsRaw);
                            @endphp
                            <a href="{{ $pMapsUrl }}" target="_blank"
                               class="inline-flex items-center gap-2 px-6 py-2 rounded-full text-sm border border-amber-600 text-amber-700 hover:bg-amber-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                {{ __('undangan.navigasi_penginapan') }}
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Update button --}}
                @if(!$isExpired)
                    <div class="text-center">
                        <button wire:click="toggleForm" class="text-amber-700 underline text-sm hover:text-amber-900">
                            {{ $showForm ? '✕ Tutup' : '✎ ' . __('undangan.konfirmasi_update') }}
                        </button>
                    </div>
                @elseif($tamu->luar_kota && ($konfirmasiData->butuh_antar_jemput || $konfirmasiData->butuh_penginapan))
                    <div class="text-center">
                        <button wire:click="toggleDateForm" class="text-amber-700 underline text-sm hover:text-amber-900">
                            {{ $showDateForm ? '✕ Tutup' : '✎ ' . __('undangan.ubah_jadwal') }}
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Batas Konfirmasi --}}
        <div class="text-center text-sm {{ $isExpired ? 'text-red-600' : 'text-gray-500' }}">
            {{ __('undangan.batas_konfirmasi') }}:
            <strong>{{ \Carbon\Carbon::parse($batasKonfirmasi)->translatedFormat('d F Y') }}</strong>
            @if($isExpired)
                <br>{{ __('undangan.batas_konfirmasi_lewat') }}
            @endif
        </div>

        {{-- Konfirmasi Form --}}
        @if(!$isExpired && ($showForm || !$konfirmasiData))
            <div class="glass-card rounded-2xl p-6 shadow-lg space-y-5">
                <h3 class="font-display text-xl font-bold text-center text-gray-800">
                    {{ __('undangan.konfirmasi_heading') }}
                </h3>

                <form wire:submit="simpanKonfirmasi" class="space-y-5">
                    {{-- Jumlah Hadir --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('undangan.jumlah_hadir') }}
                        </label>
                        <input
                            type="number"
                            wire:model.live="jumlah_hadir"
                            min="1"
                            max="{{ $tamu->jumlah_orang }}"
                            oninput="let v=parseInt(this.value)||1,mn=1,mx={{ $tamu->jumlah_orang }};if(v<mn)this.value=mn;if(v>mx)this.value=mx;"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-lg py-3 text-center"
                        />
                        <p class="text-xs text-gray-500 mt-1 text-center">
                            {{ __('undangan.jumlah_hadir_max', ['max' => $tamu->jumlah_orang]) }}
                        </p>
                        @error('jumlah_hadir')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Antar Jemput (only VIP/VVIP luar_kota) --}}
                    @if($showAntarJemput)
                        <div class="border border-amber-200 rounded-xl p-4 space-y-4 bg-amber-50/50">
                            <h4 class="font-semibold text-gray-700">{{ __('undangan.luar_kota_section') }}</h4>

                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">{{ __('undangan.butuh_antar_jemput') }}</label>
                                <div class="flex gap-3">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" wire:model.live="butuh_antar_jemput" value="1" class="text-amber-600 focus:ring-amber-500">
                                        <span class="text-sm">{{ __('undangan.ya') }}</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" wire:model.live="butuh_antar_jemput" value="0" class="text-amber-600 focus:ring-amber-500">
                                        <span class="text-sm">{{ __('undangan.tidak') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Penginapan (auto-yes for all guests with assigned penginapan) --}}
                    @if($tamu->penginapan)
                        <div class="border border-amber-200 rounded-xl p-4 space-y-2 bg-amber-50/50">
                            <h4 class="font-semibold text-gray-700">{{ __('undangan.penginapan_section') }}</h4>
                            <p class="text-xs text-gray-500">{{ __('undangan.penginapan_vvip_auto') }}</p>

                            {{-- Penginapan info --}}
                            @if($tamu->penginapanRecord)
                                <div class="bg-white rounded-lg p-3 border border-amber-100 space-y-1">
                                    <p class="font-medium text-gray-800 text-sm">{{ $tamu->penginapanRecord->nama }}</p>
                                    @if($tamu->penginapanRecord->alamat)
                                        <p class="text-xs text-gray-500">{{ $tamu->penginapanRecord->alamat }}</p>
                                    @endif
                                    @if($tamu->penginapanRecord->koordinat_maps)
                                        @php
                                            $tMapsRaw = $tamu->penginapanRecord->koordinat_maps;
                                            $tMapsUrl = str_starts_with(strtolower($tMapsRaw), 'http')
                                                ? $tMapsRaw
                                                : 'https://www.google.com/maps?q=' . urlencode($tMapsRaw);
                                        @endphp
                                        <a href="{{ $tMapsUrl }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-sm text-amber-700 hover:underline">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ __('undangan.buka_maps') }}
                                        </a>
                                    @endif
                                </div>
                            @endif

                            <div class="bg-white rounded-lg p-3 border border-amber-100">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('undangan.jumlah_menginap_label') }}
                                </label>
                                <input
                                    type="number"
                                    wire:model.live="jumlah_menginap"
                                    min="0"
                                    max="{{ $jumlah_hadir }}"
                                    oninput="let v=parseInt(this.value),mn=0,mx={{ $jumlah_hadir }};if(isNaN(v))return;if(v<mn)this.value=mn;if(v>mx)this.value=mx;"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-lg py-2 text-center"
                                />
                                <p class="text-xs text-gray-500 mt-1 text-center">
                                    {{ __('undangan.jumlah_menginap_desc', ['max' => $jumlah_hadir]) }}
                                </p>
                                @error('jumlah_menginap')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Data peserta yang menginap (jumlahnya mengikuti jumlah menginap) --}}
                            @if((int) $jumlah_menginap > 0)
                                <div class="space-y-3 pt-1">
                                    <div>
                                        <h5 class="font-semibold text-gray-700 text-sm">{{ __('undangan.peserta_heading') }}</h5>
                                        <p class="text-xs text-gray-500">{{ __('undangan.peserta_desc') }}</p>
                                    </div>

                                    @foreach($pesertaList as $index => $peserta)
                                        <div class="p-3 bg-white rounded-lg border border-amber-100 space-y-2">
                                            <p class="text-xs font-semibold text-gray-600">{{ __('undangan.peserta_ke', ['num' => $index + 1]) }}</p>

                                            <div>
                                                <input
                                                    type="text"
                                                    wire:model="pesertaList.{{ $index }}.nama"
                                                    placeholder="{{ __('undangan.peserta_nama') }}"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm"
                                                />
                                                @error("pesertaList.{$index}.nama")
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <select
                                                        wire:model="pesertaList.{{ $index }}.jenis_kelamin"
                                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm"
                                                    >
                                                        <option value="">{{ __('undangan.jenis_kelamin') }}</option>
                                                        <option value="L">{{ __('undangan.laki_laki') }}</option>
                                                        <option value="P">{{ __('undangan.perempuan') }}</option>
                                                    </select>
                                                    @error("pesertaList.{$index}.jenis_kelamin")
                                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                @if($tamu->jumlah_orang > 1)
                                                <div>
                                                    <input
                                                        type="text"
                                                        wire:model="pesertaList.{{ $index }}.nama_pasangan"
                                                        placeholder="{{ __('undangan.nama_pasangan') }}"
                                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm"
                                                    />
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Warning: fewer names filled than jumlah_menginap --}}
                                    @php $filledNama = collect($pesertaList)->filter(fn($p) => !empty($p['nama']))->count(); @endphp
                                    @if($filledNama < (int) $jumlah_menginap)
                                        <div class="bg-amber-100 border border-amber-300 rounded-xl p-3 text-sm text-amber-800">
                                            {{ __('undangan.peserta_nama_kurang', ['isi' => $filledNama, 'total' => $jumlah_menginap]) }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Dates (show when antar jemput active OR menginap active) --}}
                    @if($showDates)
                        <div class="border border-amber-200 rounded-xl p-4 space-y-3 bg-amber-50/50">
                            <h4 class="font-semibold text-gray-700">{{ __('undangan.jadwal_section') }}</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        {{ __('undangan.tanggal_datang') }}
                                    </label>
                                    <input type="date" wire:model="tanggal_datang"
                                        min="{{ $minTanggalDatang }}" max="{{ $maxTanggalDatang }}"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" />
                                    @error('tanggal_datang')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        {{ __('undangan.tanggal_pulang') }}
                                    </label>
                                    <input type="date" wire:model="tanggal_pulang"
                                        min="{{ $minTanggalPulang }}" max="{{ $maxTanggalPulang }}"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" />
                                    @error('tanggal_pulang')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Submit --}}
                    <button type="submit" class="w-full btn-gold py-3 rounded-full text-lg shadow-lg" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            {{ $konfirmasiData ? __('undangan.konfirmasi_update') : __('undangan.konfirmasi_button') }}
                        </span>
                        <span wire:loading>
                            <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>

                    {{-- Tidak Hadir --}}
                    <button
                        type="button"
                        wire:click="tidakHadir"
                        wire:confirm="{{ __('undangan.tidak_hadir_confirm') }}"
                        wire:loading.attr="disabled"
                        class="w-full py-2.5 rounded-full text-sm font-medium border-2 border-gray-300 text-gray-600 bg-white hover:bg-gray-50"
                    >
                        {{ __('undangan.tidak_hadir_button') }}
                    </button>
                    
                </form>
            </div>
        @endif

        {{-- Post-deadline: Date-only change form --}}
        @if($isExpired && $showDateForm && $konfirmasiData && $tamu->luar_kota)
            <div class="glass-card rounded-2xl p-6 shadow-lg space-y-5">
                <h3 class="font-display text-xl font-bold text-center text-gray-800">
                    {{ __('undangan.ubah_jadwal') }}
                </h3>
                <p class="text-sm text-gray-500 text-center">{{ __('undangan.ubah_jadwal_desc') }}</p>

                <form wire:submit="simpanJadwal" class="space-y-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                {{ __('undangan.tanggal_datang') }}
                            </label>
                            <input type="date" wire:model="tanggal_datang"
                                min="{{ $minTanggalDatang }}" max="{{ $maxTanggalDatang }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" />
                            @error('tanggal_datang')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                {{ __('undangan.tanggal_pulang') }}
                            </label>
                            <input type="date" wire:model="tanggal_pulang"
                                min="{{ $minTanggalPulang }}" max="{{ $maxTanggalPulang }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" />
                            @error('tanggal_pulang')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="w-full btn-gold py-3 rounded-full text-lg shadow-lg" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('undangan.simpan_jadwal') }}</span>
                        <span wire:loading>
                            <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </form>
            </div>
        @endif

        {{-- Contact Person --}}
        @if($contactPersons->count() > 0)
            <div class="glass-card rounded-2xl p-6 shadow-lg">
                <h3 class="font-display text-lg font-bold text-gray-800 mb-2">
                    {{ __('undangan.contact_person') }}
                </h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('undangan.contact_person_desc') }}</p>
                <div class="space-y-3">
                    @foreach($contactPersons as $cp)
                        <div class="flex items-center gap-3 p-3 bg-white/60 rounded-xl">
                            <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $cp->nama }}</p>
                                <a href="tel:{{ $cp->telepon }}" class="text-amber-700 text-sm hover:underline">
                                    {{ $cp->telepon }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="text-center py-6">
            <p class="text-xs text-gray-400">
                {{ __('undangan.event_title') }} &copy; {{ date('Y') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Supported by
                <a href="https://sriwijayahost.id" target="_blank" rel="noopener"
                   class="text-amber-700 hover:underline">PT SRIWIJAYA UTAMA KARYA</a>
            </p>
        </div>
    </div>
</div>
