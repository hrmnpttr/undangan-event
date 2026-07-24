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

            <div class="grid gap-5 sm:grid-cols-3">
                @foreach([
                    ['coverUpload', 'cover_image', 'Gambar sampul (amplop)'],
                    ['contentUpload', 'content_image', 'Gambar detail / isi'],
                    ['heroPhotoUpload', 'hero_photo', 'Foto utama (mempelai / acara)'],
                ] as [$prop, $key, $label])
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
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
                @include('filament.pages.partials.field', ['name' => 'venue_maps', 'label' => 'Google Maps (URL / alamat pencarian)'])
            </div>
            @include('filament.pages.partials.field', ['name' => 'venue_address', 'label' => 'Alamat lengkap', 'textarea' => true])

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
