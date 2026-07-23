<x-filament-panels::page>
    {{-- Toolbar: Datang / Pulang + Tanggal --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <button wire:click="switchTipe('datang')"
            class="px-4 py-2 rounded-full text-sm font-medium transition
                {{ $tipeView === 'datang' ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            🛬 Kedatangan
        </button>
        <button wire:click="switchTipe('pulang')"
            class="px-4 py-2 rounded-full text-sm font-medium transition
                {{ $tipeView === 'pulang' ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            🛫 Kepulangan
        </button>
        <input type="date" wire:model.live="selectedDate"
            class="ml-auto border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>

    {{-- List tamu --}}
    @php $jadwal = $this->myJadwal; @endphp

    @if($jadwal->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <x-heroicon-o-calendar class="w-12 h-12 mx-auto mb-2 opacity-40"/>
            <p class="text-sm">Tidak ada jadwal {{ $tipeView === 'pulang' ? 'kepulangan' : 'kedatangan' }} untuk tanggal ini.</p>
        </div>
    @else
        <div class="space-y-3" x-data="transportPage()" @penjemputan-updated.window="$wire.$refresh()">
            @foreach($jadwal as $k)
                @php
                    $status = $k->status_penjemputan;
                    $cardColor = match($status) {
                        'selesai'  => 'border-green-400 bg-green-50',
                        'dijemput' => 'border-yellow-400 bg-yellow-50',
                        default    => 'border-gray-200 bg-white',
                    };
                    $flightCode = $tipeView === 'pulang' ? $k->pesawat_pulang : $k->pesawat_datang;
                    $flightTime = $tipeView === 'pulang' ? $k->jam_berangkat  : $k->jam_tiba;
                @endphp

                <div class="rounded-xl border-2 {{ $cardColor }} p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        {{-- Info tamu --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if($k->tamu?->jenis === 'VVIP')
                                    <span class="inline-block w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                                @elseif($k->tamu?->jenis === 'VIP')
                                    <span class="inline-block w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0"></span>
                                @endif
                                <p class="font-semibold text-gray-800 truncate">{{ $k->tamu?->nama }}</p>
                            </div>
                            <p class="text-xs text-gray-500">{{ $k->jumlah_hadir }} orang</p>

                            {{-- Flight info --}}
                            @if($flightCode || $flightTime)
                                <div class="mt-1.5 flex items-center gap-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg px-2 py-1 w-fit">
                                    <x-heroicon-s-paper-airplane class="w-3 h-3"/>
                                    <span>{{ $flightCode ?? '—' }}</span>
                                    @if($flightTime)
                                        <span class="font-semibold">{{ $flightTime }}</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Status & timestamps --}}
                            @if($status === 'dijemput')
                                <p class="text-xs text-yellow-600 mt-1">
                                    🚗 Sedang dijemput sejak {{ $k->dijemput_at?->format('H:i') }}
                                    @if($k->dijemput_koordinat)
                                        &nbsp;<a href="https://www.google.com/maps?q={{ $k->dijemput_koordinat }}" target="_blank" class="underline">Lihat titik</a>
                                    @endif
                                </p>
                            @elseif($status === 'selesai')
                                <p class="text-xs text-green-600 mt-1">
                                    ✅ Selesai pukul {{ $k->selesai_at?->format('H:i') }}
                                    @if($k->selesai_koordinat)
                                        &nbsp;<a href="https://www.google.com/maps?q={{ $k->selesai_koordinat }}" target="_blank" class="underline">Lihat titik</a>
                                    @endif
                                </p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col gap-1.5 flex-shrink-0">
                            {{-- Flight info button --}}
                            <button
                                x-on:click="$dispatch('open-modal', { id: 'flight-modal-{{ $k->id }}' })"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50">
                                <x-heroicon-o-paper-airplane class="w-3 h-3"/>
                                Pesawat
                            </button>

                            {{-- Check-in / Check-out --}}
                            @if($status === null || $status === '')
                                <button
                                    x-on:click="checkInWithGps({{ $k->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs rounded-lg bg-yellow-500 text-white hover:bg-yellow-600">
                                    <x-heroicon-o-play class="w-3 h-3"/>
                                    Mulai Jemput
                                </button>
                            @elseif($status === 'dijemput')
                                <button
                                    x-on:click="checkOutWithGps({{ $k->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs rounded-lg bg-green-600 text-white hover:bg-green-700">
                                    <x-heroicon-o-check-circle class="w-3 h-3"/>
                                    Selesai
                                </button>
                            @else
                                <span class="text-xs text-green-600 font-medium px-2 py-1">✅ Selesai</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Flight modal --}}
                <x-filament::modal id="flight-modal-{{ $k->id }}" width="sm">
                    <x-slot name="heading">Info Penerbangan — {{ $k->tamu?->nama }}</x-slot>
                    <form wire:submit.prevent="saveFlight({{ $k->id }})">
                        <div class="space-y-3 p-1">
                            @if($tipeView === 'datang')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode / Nama Pesawat</label>
                                    <input type="text" wire:model="flightData.{{ $k->id }}.pesawat_datang"
                                        placeholder="e.g. GA-112" value="{{ $k->pesawat_datang }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam Tiba</label>
                                    <input type="time" wire:model="flightData.{{ $k->id }}.jam_tiba"
                                        value="{{ $k->jam_tiba }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode / Nama Pesawat</label>
                                    <input type="text" wire:model="flightData.{{ $k->id }}.pesawat_pulang"
                                        placeholder="e.g. GA-212" value="{{ $k->pesawat_pulang }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam Berangkat</label>
                                    <input type="time" wire:model="flightData.{{ $k->id }}.jam_berangkat"
                                        value="{{ $k->jam_berangkat }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                                </div>
                            @endif
                        </div>
                        <div class="mt-4 flex justify-end gap-2">
                            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'flight-modal-{{ $k->id }}' })">
                                Batal
                            </x-filament::button>
                            <x-filament::button type="submit" wire:click="saveFlightDirect({{ $k->id }})">
                                Simpan
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::modal>
            @endforeach
        </div>

        {{-- Summary footer --}}
        <div class="mt-4 text-xs text-gray-400 text-center">
            {{ $jadwal->count() }} tamu · {{ $jadwal->sum('jumlah_hadir') }} orang
        </div>
    @endif

    {{-- Jadwal Mendatang --}}
    @php $mendatang = $this->jadwalMendatang; @endphp
    @if($mendatang->isNotEmpty())
        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">📅 Jadwal Mendatang</h3>
            <div class="space-y-3">
                @foreach($mendatang as $hari)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 overflow-hidden">
                        {{-- Header tanggal --}}
                        <div class="px-4 py-2 bg-gray-100 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">{{ $hari['label'] }}</span>
                            <span class="text-xs text-gray-400">{{ $hari['items']->count() }} tamu</span>
                        </div>
                        {{-- List tamu per hari --}}
                        <div class="divide-y divide-gray-100">
                            @foreach($hari['items'] as $item)
                                <div class="px-4 py-2.5 flex items-center gap-3">
                                    {{-- Warna jenis --}}
                                    @if($item['tamu_jenis'] === 'VVIP')
                                        <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                                    @elseif($item['tamu_jenis'] === 'VIP')
                                        <span class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></span>
                                    @endif

                                    {{-- Nama & info --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item['tamu_nama'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $item['jumlah'] }} orang</p>
                                    </div>

                                    {{-- Tipe & pesawat --}}
                                    <div class="text-right flex-shrink-0">
                                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium
                                            {{ $item['tipe'] === 'datang' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                                            {{ $item['tipe'] === 'datang' ? '🛬' : '🛫' }}
                                        </span>
                                        @if($item['pesawat'] || $item['jam'])
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ $item['pesawat'] ?? '' }}
                                                @if($item['jam']) <span class="font-medium">{{ $item['jam'] }}</span> @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- JS for GPS capture --}}
    <script>
    function transportPage() {
        return {
            checkInWithGps(konfirmasiId) {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        pos => {
                            const coord = pos.coords.latitude + ',' + pos.coords.longitude;
                            @this.doCheckIn(konfirmasiId, coord);
                        },
                        () => { @this.doCheckIn(konfirmasiId, null); }
                    );
                } else {
                    @this.doCheckIn(konfirmasiId, null);
                }
            },
            checkOutWithGps(konfirmasiId) {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        pos => {
                            const coord = pos.coords.latitude + ',' + pos.coords.longitude;
                            @this.doCheckOut(konfirmasiId, coord);
                        },
                        () => { @this.doCheckOut(konfirmasiId, null); }
                    );
                } else {
                    @this.doCheckOut(konfirmasiId, null);
                }
            }
        }
    }
    </script>
</x-filament-panels::page>
