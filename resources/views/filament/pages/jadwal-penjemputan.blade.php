<x-filament-panels::page>
    {{-- Toggle Kedatangan / Kepulangan --}}
    <div class="flex gap-2">
        <x-filament::button
            :color="$tipeView === 'datang' ? 'primary' : 'gray'"
            wire:click="switchTipe('datang')"
            size="sm"
            icon="heroicon-o-arrow-down-tray"
        >
            Kedatangan
        </x-filament::button>
        <x-filament::button
            :color="$tipeView === 'pulang' ? 'primary' : 'gray'"
            wire:click="switchTipe('pulang')"
            size="sm"
            icon="heroicon-o-arrow-up-tray"
        >
            Kepulangan
        </x-filament::button>
    </div>

    {{-- Summary Table --}}
    @if($this->summary->isEmpty())
        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <x-heroicon-o-truck class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" />
                <p class="text-lg font-semibold text-gray-950 dark:text-white">Belum ada data</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada tamu yang membutuhkan antar jemput.</p>
            </div>
        </div>
    @else
        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                    <thead>
                        <tr class="bg-gray-50/75 dark:bg-white/5">
                            <th class="px-3 py-3 sm:ps-6 text-start text-sm font-semibold text-gray-950 dark:text-white">Tanggal</th>
                            <th class="px-3 py-3 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">Umum</th>
                            <th class="px-3 py-3 text-center text-sm font-semibold text-amber-600 dark:text-amber-400">VIP</th>
                            <th class="px-3 py-3 text-center text-sm font-semibold text-red-600 dark:text-red-400">VVIP</th>
                            <th class="px-3 py-3 text-center text-sm font-semibold text-gray-950 dark:text-white">Total</th>
                            <th class="px-3 py-3 sm:pe-6 text-center text-sm font-semibold text-gray-950 dark:text-white"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach($this->summary as $row)
                            <tr class="{{ $row['ada_perubahan'] ? 'bg-warning-50 dark:bg-warning-400/10' : '' }} transition hover:bg-gray-50 dark:hover:bg-white/5">
                                {{-- Tanggal --}}
                                <td class="px-3 py-3.5 sm:ps-6 text-sm">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="font-medium text-gray-950 dark:text-white">{{ $row['label'] }}</span>
                                        @if($row['is_hari_h'])
                                            <span class="inline-flex items-center rounded-full bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">⭐ Hari H</span>
                                        @endif
                                        @if($row['ada_perubahan'])
                                            <span class="inline-flex items-center rounded-full bg-warning-50 px-2 py-0.5 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30">⚠ Ada perubahan</span>
                                        @endif
                                    </div>
                                </td>
                                {{-- Umum --}}
                                <td class="px-3 py-3.5 text-center text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-semibold">{{ $row['umum'] }}</span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">({{ $row['umum_orang'] }})</span>
                                </td>
                                {{-- VIP --}}
                                <td class="px-3 py-3.5 text-center text-sm text-amber-600 dark:text-amber-400">
                                    <span class="font-semibold">{{ $row['vip'] }}</span>
                                    <span class="text-xs text-amber-400 dark:text-amber-500">({{ $row['vip_orang'] }})</span>
                                </td>
                                {{-- VVIP --}}
                                <td class="px-3 py-3.5 text-center text-sm text-red-600 dark:text-red-400">
                                    <span class="font-semibold">{{ $row['vvip'] }}</span>
                                    <span class="text-xs text-red-400 dark:text-red-500">({{ $row['vvip_orang'] }})</span>
                                </td>
                                {{-- Total --}}
                                <td class="px-3 py-3.5 text-center text-sm">
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $row['total_tamu'] }}</span>
                                    <span class="text-xs text-gray-500">({{ $row['total_orang'] }} org)</span>
                                </td>
                                {{-- Actions --}}
                                <td class="px-3 py-3.5 sm:pe-6 text-center">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-filament::button
                                            tag="a"
                                            :href="$row['detail_url']"
                                            target="_blank"
                                            size="xs"
                                            color="gray"
                                            icon="heroicon-o-arrow-top-right-on-square"
                                        >
                                            Detail
                                        </x-filament::button>
                                        @if($row['ada_perubahan'])
                                            <x-filament::button
                                                size="xs"
                                                color="success"
                                                icon="heroicon-o-check-circle"
                                                wire:click="acknowledgeDate('{{ $row['tanggal'] }}')"
                                                wire:confirm="Tandai semua tamu tanggal ini sebagai diketahui?"
                                            >
                                                Diketahui
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    {{-- Footer totals --}}
                    <tfoot>
                        <tr class="bg-gray-50/75 dark:bg-white/5">
                            <td class="px-3 py-3 sm:ps-6 text-sm font-bold text-gray-950 dark:text-white">Grand Total</td>
                            <td class="px-3 py-3 text-center text-sm font-semibold text-gray-600 dark:text-gray-400">
                                {{ $this->summary->sum('umum') }} <span class="text-xs font-normal">({{ $this->summary->sum('umum_orang') }})</span>
                            </td>
                            <td class="px-3 py-3 text-center text-sm font-semibold text-amber-600 dark:text-amber-400">
                                {{ $this->summary->sum('vip') }} <span class="text-xs font-normal">({{ $this->summary->sum('vip_orang') }})</span>
                            </td>
                            <td class="px-3 py-3 text-center text-sm font-semibold text-red-600 dark:text-red-400">
                                {{ $this->summary->sum('vvip') }} <span class="text-xs font-normal">({{ $this->summary->sum('vvip_orang') }})</span>
                            </td>
                            <td class="px-3 py-3 text-center text-sm font-bold text-gray-950 dark:text-white">
                                {{ $this->summary->sum('total_tamu') }} <span class="text-xs font-normal text-gray-500">({{ $this->summary->sum('total_orang') }} org)</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-400 dark:text-gray-500">
            Angka dalam kurung = jumlah orang (termasuk rombongan)
        </p>
    @endif
</x-filament-panels::page>
</x-filament-panels::page>
