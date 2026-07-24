<x-filament-panels::page>
    @php($kind = $this->templateKind())

    <form wire:submit="save" class="space-y-6">

        {{-- Template picker --}}
        <section class="fi-section rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 space-y-4">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">1. Pilih Template Undangan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Setiap template punya tema warna & tata letak sendiri. Field di bawah menyesuaikan jenis acara.</p>
            </div>

            <div>
                <select wire:model.live="template"
                    class="block w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    @foreach($this->templateGroups() as $group => $items)
                        <optgroup label="{{ $group }}">
                            @foreach($items as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" wire:model="show_default_cover" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                Tampilkan halaman sampul (amplop) sebelum masuk undangan
            </label>
        </section>

        {{-- Media --}}
        <section class="fi-section rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 space-y-5">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">2. Musik & Gambar</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Unggah aset undangan Anda sendiri.</p>
            </div>

            {{-- Music --}}
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Musik latar (khusus .mp3, maks 12&nbsp;MB)</label>
                <input type="file" wire:model="musicUpload" accept="audio/mpeg,.mp3"
                    class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-primary-700 hover:file:bg-primary-100">
                @error('musicUpload') <p class="text-xs text-danger-600">{{ $message }}</p> @enderror
                <div wire:loading wire:target="musicUpload" class="text-xs text-gray-500">Mengunggah…</div>
                @if($this->currentAsset('music'))
                    <div class="flex items-center gap-3 pt-1">
                        <audio controls src="{{ $this->currentAsset('music') }}" class="h-8"></audio>
                        <button type="button" wire:click="clearAsset('music')" class="text-xs text-danger-600 hover:underline">Hapus</button>
                    </div>
                @else
                    <p class="text-xs text-gray-400">Belum ada musik diunggah — memakai <code>storage/audio/background.mp3</code> bila tersedia.</p>
                @endif
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 pt-1">
                    <input type="checkbox" wire:model="music_autoplay" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Putar musik otomatis saat undangan dibuka
                </label>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400">
                Satu gambar dipakai untuk tampilan HP &amp; desktop (di-crop otomatis mengikuti layar).
                Rasio tetap sama di semua perangkat, jadi utamakan gambar <strong>portrait</strong> (mobile-first).
                Ukuran berkas maks 8&nbsp;MB — idealnya di bawah 1&nbsp;MB agar cepat dibuka.
            </p>

            <div class="grid gap-5 sm:grid-cols-3">
                @foreach([
                    ['coverUpload', 'cover_image', 'Gambar sampul (amplop)', '1080 × 1440 px · portrait (3:4)'],
                    ['contentUpload', 'content_image', 'Gambar detail / isi', '1080 × 1350 px · portrait (4:5)'],
                    ['heroPhotoUpload', 'hero_photo', 'Foto utama (mempelai / acara)', '800 × 800 px · persegi (1:1)'],
                ] as [$prop, $key, $label, $hint])
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        <p class="text-[11px] text-gray-400">Saran: {{ $hint }}</p>
                        @if($this->currentAsset($key))
                            <img src="{{ $this->currentAsset($key) }}" class="h-24 w-full rounded-lg object-cover ring-1 ring-gray-200">
                            <button type="button" wire:click="clearAsset('{{ $key }}')" class="text-xs text-danger-600 hover:underline">Hapus</button>
                        @endif
                        <input type="file" wire:model="{{ $prop }}" accept="image/*"
                            class="block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-2 file:rounded-md file:border-0 file:bg-primary-50 file:px-2 file:py-1 file:text-primary-700">
                        @error($prop) <p class="text-xs text-danger-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            {{-- Gallery --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Galeri foto (opsional, bisa banyak)</label>
                <p class="text-[11px] text-gray-400">Saran: 1000 × 1000 px · persegi (1:1) — ditampilkan sebagai grid.</p>
                <input type="file" wire:model="galleryUpload" multiple accept="image/*"
                    class="block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-2 file:rounded-md file:border-0 file:bg-primary-50 file:px-2 file:py-1 file:text-primary-700">
                @if(count($this->galleryImages()))
                    <div class="flex flex-wrap gap-2 pt-1">
                        @foreach($this->galleryImages() as $img)
                            <div class="relative">
                                <img src="{{ $img['url'] }}" class="h-16 w-16 rounded-lg object-cover ring-1 ring-gray-200">
                                <button type="button" wire:click="removeGalleryImage({{ $img['index'] }})"
                                    class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-danger-600 text-xs text-white">×</button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- Content --}}
        <section class="fi-section rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 space-y-5">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">3. Konten Undangan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kosongkan bila tidak dipakai — bagian kosong otomatis disembunyikan.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @include('filament.pages.partials.field', ['name' => 'title', 'label' => 'Judul acara', 'placeholder' => 'mis. The Wedding of / Annual Tech Summit'])
                @include('filament.pages.partials.field', ['name' => 'subtitle', 'label' => 'Sub judul / tagline'])
                @include('filament.pages.partials.field', ['name' => 'event_date_text', 'label' => 'Tanggal (teks bebas)', 'placeholder' => 'Sabtu, 12 Juli 2026'])
                @include('filament.pages.partials.field', ['name' => 'event_time_text', 'label' => 'Waktu', 'placeholder' => '10.00 WIB – selesai'])
                @include('filament.pages.partials.field', ['name' => 'venue_name', 'label' => 'Nama lokasi'])
                @include('filament.pages.partials.field', ['name' => 'venue_maps', 'label' => 'Google Maps (URL / alamat) — opsional', 'placeholder' => 'dipakai bila koordinat kosong'])
            </div>
            @include('filament.pages.partials.field', ['name' => 'venue_address', 'label' => 'Alamat lengkap', 'textarea' => true])

            {{-- Free map picker (OpenStreetMap / Leaflet — tanpa biaya, tanpa API key) --}}
            @assets
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            @endassets

            <div class="rounded-lg border border-dashed border-gray-300 dark:border-white/10 p-4 space-y-3"
                 wire:ignore
                 x-data="venueMap({ lat: @js($venue_lat), lng: @js($venue_lng) })" x-init="init()">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Titik lokasi acara (peta gratis)</h3>
                    <button type="button" x-show="lat && lng" x-on:click="clear()" class="text-xs text-danger-600 hover:underline">Hapus titik</button>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Cari nama tempat, <strong>klik di peta</strong>, atau tempel koordinat. Peta di undangan hanya
                    muncul bila koordinat terisi. Peta memakai OpenStreetMap (gratis); tombol di undangan tetap
                    bisa membuka Google Maps.
                </p>

                {{-- Search --}}
                <div class="flex gap-2">
                    <input type="text" x-model="q" x-on:keydown.enter.prevent="search()" placeholder="Cari lokasi, mis. Gedung Sasana Budaya Ganesha Bandung"
                        class="flex-1 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                    <button type="button" x-on:click="search()"
                        class="rounded-lg bg-primary-600 px-4 py-2 text-sm text-white hover:bg-primary-500">Cari</button>
                </div>

                {{-- Map --}}
                <div x-ref="map" class="h-64 w-full rounded-lg overflow-hidden ring-1 ring-gray-200 dark:ring-white/10" style="background:#e5e7eb;"></div>

                {{-- Coordinate paste / display --}}
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 shrink-0">Koordinat (lat, lng)</label>
                    <input type="text" x-model="coord" x-on:change="parseCoord()" placeholder="-6.200000, 106.816666"
                        class="flex-1 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm font-mono">
                </div>
            </div>

            <script>
                window.venueMap = function (initial) {
                    return {
                        q: '',
                        lat: initial.lat ? parseFloat(initial.lat) : null,
                        lng: initial.lng ? parseFloat(initial.lng) : null,
                        coord: (initial.lat && initial.lng) ? (initial.lat + ', ' + initial.lng) : '',
                        map: null, marker: null,

                        loadLeaflet() {
                            return new Promise((resolve) => {
                                if (window.L) return resolve();
                                const t = setInterval(() => { if (window.L) { clearInterval(t); resolve(); } }, 60);
                            });
                        },

                        async init() {
                            await this.loadLeaflet();
                            const hasPt = Number.isFinite(this.lat) && Number.isFinite(this.lng);
                            const start = hasPt ? [this.lat, this.lng] : [-2.5, 118];
                            this.map = L.map(this.$refs.map).setView(start, hasPt ? 15 : 4);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19, attribution: '&copy; OpenStreetMap'
                            }).addTo(this.map);
                            if (hasPt) this.setMarker(this.lat, this.lng, false);
                            this.map.on('click', (e) => this.apply(e.latlng.lat, e.latlng.lng));
                            // Redraw once the container has its final size.
                            setTimeout(() => this.map.invalidateSize(), 200);
                        },

                        parseCoord() {
                            const p = (this.coord || '').split(',').map((s) => parseFloat(s.trim()));
                            if (p.length === 2 && Number.isFinite(p[0]) && Number.isFinite(p[1])) {
                                this.apply(p[0], p[1], true);
                            }
                        },

                        apply(lat, lng, fromInput = false) {
                            this.lat = lat; this.lng = lng;
                            if (!fromInput) this.coord = lat.toFixed(6) + ', ' + lng.toFixed(6);
                            this.setMarker(lat, lng, true);
                            // Deferred: no network per click; sent to the server on Save.
                            this.$wire.set('venue_lat', String(lat), false);
                            this.$wire.set('venue_lng', String(lng), false);
                        },

                        setMarker(lat, lng, pan) {
                            const icon = L.divIcon({
                                className: '',
                                html: '<div style="font-size:26px;line-height:1">📍</div>',
                                iconSize: [26, 26], iconAnchor: [13, 26]
                            });
                            if (this.marker) this.marker.setLatLng([lat, lng]);
                            else this.marker = L.marker([lat, lng], { icon }).addTo(this.map);
                            if (pan) this.map.setView([lat, lng], Math.max(this.map.getZoom(), 15));
                        },

                        async search() {
                            if (!this.q) return;
                            try {
                                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(this.q);
                                const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
                                const d = await r.json();
                                if (d && d[0]) this.apply(parseFloat(d[0].lat), parseFloat(d[0].lon));
                                else alert('Lokasi tidak ditemukan. Coba kata kunci lain atau klik langsung di peta.');
                            } catch (e) {
                                alert('Gagal mencari lokasi. Periksa koneksi internet.');
                            }
                        },

                        clear() {
                            this.lat = null; this.lng = null; this.coord = '';
                            if (this.marker) { this.map.removeLayer(this.marker); this.marker = null; }
                            this.$wire.set('venue_lat', '', false);
                            this.$wire.set('venue_lng', '', false);
                        },
                    };
                };
            </script>

            <div class="grid gap-4 sm:grid-cols-2">
                @include('filament.pages.partials.field', ['name' => 'quote', 'label' => 'Kutipan / ayat pembuka', 'textarea' => true])
                @include('filament.pages.partials.field', ['name' => 'quote_source', 'label' => 'Sumber kutipan', 'placeholder' => 'QS. Ar-Rum: 21 / Kejadian 2:24'])
            </div>

            {{-- Wedding fields --}}
            @if($kind === 'wedding')
                <div class="rounded-lg border border-dashed border-gray-300 dark:border-white/10 p-4 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Data Mempelai</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @include('filament.pages.partials.field', ['name' => 'groom_name', 'label' => 'Nama panggilan mempelai pria'])
                        @include('filament.pages.partials.field', ['name' => 'bride_name', 'label' => 'Nama panggilan mempelai wanita'])
                        @include('filament.pages.partials.field', ['name' => 'groom_full', 'label' => 'Nama lengkap mempelai pria'])
                        @include('filament.pages.partials.field', ['name' => 'bride_full', 'label' => 'Nama lengkap mempelai wanita'])
                        @include('filament.pages.partials.field', ['name' => 'groom_parents', 'label' => 'Orang tua mempelai pria', 'textarea' => true])
                        @include('filament.pages.partials.field', ['name' => 'bride_parents', 'label' => 'Orang tua mempelai wanita', 'textarea' => true])
                        @include('filament.pages.partials.field', ['name' => 'akad_text', 'label' => 'Akad / Pemberkatan (waktu & tempat)', 'textarea' => true])
                        @include('filament.pages.partials.field', ['name' => 'resepsi_text', 'label' => 'Resepsi (waktu & tempat)', 'textarea' => true])
                    </div>
                </div>
            @endif

            {{-- Event / corporate fields --}}
            @if($kind === 'event')
                <div class="rounded-lg border border-dashed border-gray-300 dark:border-white/10 p-4 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Detail Acara</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @include('filament.pages.partials.field', ['name' => 'host_org', 'label' => 'Penyelenggara / perusahaan'])
                        @include('filament.pages.partials.field', ['name' => 'dress_code', 'label' => 'Dress code'])
                        @include('filament.pages.partials.field', ['name' => 'speaker', 'label' => 'Pembicara / narasumber', 'textarea' => true])
                    </div>

                    {{-- Agenda / rundown --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Rundown / Agenda</label>
                            <button type="button" wire:click="addAgenda" class="text-xs text-primary-600 hover:underline">+ Tambah baris</button>
                        </div>
                        @forelse($agenda as $i => $row)
                            <div class="grid grid-cols-12 gap-2 items-start" wire:key="agenda-{{ $i }}">
                                <input type="text" wire:model="agenda.{{ $i }}.time" placeholder="09.00"
                                    class="col-span-3 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                                <input type="text" wire:model="agenda.{{ $i }}.title" placeholder="Judul sesi"
                                    class="col-span-4 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                                <input type="text" wire:model="agenda.{{ $i }}.desc" placeholder="Keterangan (opsional)"
                                    class="col-span-4 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                                <button type="button" wire:click="removeAgenda({{ $i }})"
                                    class="col-span-1 text-danger-600 text-lg leading-none">×</button>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400">Belum ada agenda. Klik “Tambah baris”.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Custom template --}}
            @if($kind === 'custom')
                <div class="rounded-lg border border-dashed border-gray-300 dark:border-white/10 p-4 space-y-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">HTML Kustom</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Tempel HTML undangan Anda sendiri. Ditampilkan sebagai isi undangan; form RSVP tetap muncul di bawahnya.
                        Jika kosong, gambar sampul & detail yang diunggah di atas yang akan ditampilkan.
                    </p>
                    @include('filament.pages.partials.field', ['name' => 'custom_html', 'label' => 'Kode HTML', 'textarea' => true, 'rows' => 10])
                </div>
            @endif
        </section>

        <div class="flex items-center justify-between gap-3">
            <p class="text-xs text-gray-400">Pratinjau: buka tautan undangan salah satu tamu di menu <strong>Tamu</strong>.</p>
            <x-filament::button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Simpan Desain</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
