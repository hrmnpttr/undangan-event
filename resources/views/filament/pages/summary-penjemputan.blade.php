<x-filament-panels::page>
    {{-- Tab switcher --}}
    <div class="flex gap-2 flex-wrap">
        <x-filament::button
            :color="$activeTab === 'total' ? 'primary' : 'gray'"
            wire:click="switchTab('total')"
            size="sm"
            icon="heroicon-o-chart-bar"
        >Total Overview</x-filament::button>
        <x-filament::button
            :color="$activeTab === 'perhari' ? 'primary' : 'gray'"
            wire:click="switchTab('perhari')"
            size="sm"
            icon="heroicon-o-calendar-days"
        >Per Hari</x-filament::button>
        <x-filament::button
            :color="$activeTab === 'mobil' ? 'primary' : 'gray'"
            wire:click="switchTab('mobil')"
            size="sm"
            icon="heroicon-o-truck"
        >Summary Mobil</x-filament::button>
    </div>

    {{-- ──────────── TAB TOTAL ──────────── --}}
    @if($activeTab === 'total')
        @php $d = $this->total; @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Estimasi card --}}
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="px-4 py-3.5 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800/50">
                    <h3 class="font-bold text-blue-700 dark:text-blue-400">Estimasi (Semua Eligible)</h3>
                    <p class="text-xs text-blue-500 mt-0.5">Semua tamu luar kota yang mendapat hak penjemputan</p>
                </div>
                <table class="w-full text-sm divide-y divide-gray-200 dark:divide-white/5">
                    <tbody>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">Total Tamu (undangan)</td>
                            <td class="px-4 py-3.5 text-right font-bold text-gray-900 dark:text-white">{{ $d['estimasi_tamu'] }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">Total Orang (estimasi)</td>
                            <td class="px-4 py-3.5 text-right font-bold text-blue-600">{{ $d['estimasi_total_orang'] }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 pl-8 text-gray-500 dark:text-gray-400 text-xs">→ VVIP</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-400/20 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300">{{ $d['estimasi_vvip'] }} orang</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 pl-8 text-gray-500 dark:text-gray-400 text-xs">→ VIP (dapat transport)</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $d['estimasi_vip'] }} orang</span>
                            </td>
                        </tr>
                        <tr class="bg-amber-50 dark:bg-amber-400/10">
                            <td class="px-4 py-3.5 text-amber-700 dark:text-amber-400 text-sm">Belum Konfirmasi</td>
                            <td class="px-4 py-3.5 text-right font-semibold text-amber-700 dark:text-amber-400">{{ $d['belum_konfirmasi'] }} tamu</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Konfirmasi card --}}
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="px-4 py-3.5 bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-800/50">
                    <h3 class="font-bold text-green-700 dark:text-green-400">Konfirmasi (Butuh Antar Jemput)</h3>
                    <p class="text-xs text-green-500 mt-0.5">Tamu yang sudah konfirmasi dan minta dijemput/diantar</p>
                </div>
                <table class="w-full text-sm divide-y divide-gray-200 dark:divide-white/5">
                    <tbody>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">Total Tamu (undangan)</td>
                            <td class="px-4 py-3.5 text-right font-bold text-gray-900 dark:text-white">{{ $d['konfirmasi_tamu'] }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">Total Orang</td>
                            <td class="px-4 py-3.5 text-right font-bold text-green-600">{{ $d['konfirmasi_total_orang'] }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 pl-8 text-gray-500 dark:text-gray-400 text-xs">→ VVIP</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-400/20 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300">{{ $d['konfirmasi_vvip'] }} orang</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 pl-8 text-gray-500 dark:text-gray-400 text-xs">→ VIP</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $d['konfirmasi_vip'] }} orang</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 text-xs">Persentase konfirmasi</td>
                            <td class="px-4 py-3.5 text-right text-gray-600 dark:text-gray-300 font-semibold">
                                @if($d['estimasi_tamu'] > 0)
                                    {{ round(($d['konfirmasi_tamu'] / $d['estimasi_tamu']) * 100) }}%
                                @else–@endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-400 px-1">* Orang = jumlah_hadir (bukan jumlah_orang undangan). VVIP luar kota otomatis eligible meski tidak ditandai dapat_transport.</p>

    {{-- ──────────── TAB PER HARI ──────────── --}}
    @elseif($activeTab === 'perhari')
        @php $days = $this->perHari; @endphp

        @if($days->isEmpty())
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-8 text-center text-gray-400">
                Belum ada data jadwal penjemputan.
            </div>
        @else
        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5 text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <th class="px-4 py-3.5 text-start font-semibold text-gray-950 dark:text-white">Tanggal</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-gray-950 dark:text-white">Arah</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-gray-500 dark:text-gray-400">Undangan</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-gray-700 dark:text-gray-300">Orang</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-red-600">VVIP</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-amber-600">VIP</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-orange-500">Belum Ada Sopir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($days as $day)
                            @if($day['datang']['tamu'] > 0)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">{{ $day['label'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 dark:bg-blue-400/20 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-600/20 dark:ring-blue-400/30">
                                        🛬 Datang
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center text-gray-600 dark:text-gray-400">{{ $day['datang']['tamu'] }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-gray-900 dark:text-white">{{ $day['datang']['orang'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($day['datang']['vvip'])
                                        <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-400/20 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300">{{ $day['datang']['vvip'] }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($day['datang']['vip'])
                                        <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $day['datang']['vip'] }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($day['datang']['belum_sopir'] > 0)
                                        <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-0.5 text-xs font-semibold text-orange-700 dark:text-orange-300">{{ $day['datang']['belum_sopir'] }}</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-400/20 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:text-green-300">✓ Lengkap</span>
                                    @endif
                                </td>
                            </tr>
                            @endif

                            @if($day['pulang']['tamu'] > 0)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors {{ $day['datang']['tamu'] > 0 ? 'bg-gray-50/30 dark:bg-white/[0.01]' : '' }}">
                                <td class="px-4 py-3.5 {{ $day['datang']['tamu'] > 0 ? 'text-gray-400' : 'font-medium text-gray-900 dark:text-white' }}">
                                    {{ $day['datang']['tamu'] > 0 ? '' : $day['label'] }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-400/20 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300 ring-1 ring-inset ring-amber-600/20 dark:ring-amber-400/30">
                                        🛫 Pulang
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center text-gray-600 dark:text-gray-400">{{ $day['pulang']['tamu'] }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-gray-900 dark:text-white">{{ $day['pulang']['orang'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($day['pulang']['vvip'])
                                        <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-400/20 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300">{{ $day['pulang']['vvip'] }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($day['pulang']['vip'])
                                        <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $day['pulang']['vip'] }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($day['pulang']['belum_sopir'] > 0)
                                        <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-0.5 text-xs font-semibold text-orange-700 dark:text-orange-300">{{ $day['pulang']['belum_sopir'] }}</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-400/20 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:text-green-300">✓ Lengkap</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-white/5 font-bold border-t-2 border-gray-200 dark:border-white/10">
                            <td class="px-4 py-3.5 text-gray-950 dark:text-white" colspan="2">Grand Total</td>
                            <td class="px-4 py-3.5 text-center text-gray-600 dark:text-gray-400">{{ $days->sum('datang.tamu') + $days->sum('pulang.tamu') }}</td>
                            <td class="px-4 py-3.5 text-center text-gray-900 dark:text-white text-base">{{ $days->sum('datang.orang') + $days->sum('pulang.orang') }}</td>
                            <td class="px-4 py-3.5 text-center text-red-600">{{ $days->sum('datang.vvip') + $days->sum('pulang.vvip') ?: '–' }}</td>
                            <td class="px-4 py-3.5 text-center text-amber-600">{{ $days->sum('datang.vip') + $days->sum('pulang.vip') ?: '–' }}</td>
                            <td class="px-4 py-3.5 text-center text-orange-600">{{ $days->sum('datang.belum_sopir') + $days->sum('pulang.belum_sopir') ?: '–' }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <p class="text-xs text-gray-400 px-1">* Orang = jumlah_hadir dari konfirmasi. Belum Ada Sopir = tamu yang belum diassign sopir.</p>
        @endif

    {{-- ──────────── TAB SUMMARY MOBIL ──────────── --}}
    @elseif($activeTab === 'mobil')
        @php
            $days = $this->mobil;
            // Asumsi kapasitas kendaraan
            $kapasitasRegular = 7;  // orang per mobil reguler
            // VVIP: 1 mobil dedikasi per tamu (terlepas jumlah orangnya)
        @endphp

        @if($days->isEmpty())
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-8 text-center text-gray-400">
                Belum ada data jadwal penjemputan.
            </div>
        @else

        {{-- Info asumsi --}}
        <div class="fi-ta rounded-xl bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-200 dark:ring-blue-800/50 p-4">
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-1">Asumsi Perhitungan</p>
            <div class="flex flex-wrap gap-4 text-sm text-blue-600 dark:text-blue-400">
                <span>🚗 VVIP → 1 mobil dedikasi per tamu undangan</span>
                <span>🚌 Reguler (VIP + Umum) → {{ $kapasitasRegular }} orang per kendaraan</span>
            </div>
        </div>

        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5 text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <th class="px-4 py-3.5 text-start font-semibold text-gray-950 dark:text-white">Tanggal</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-gray-950 dark:text-white">Arah</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-gray-600 dark:text-gray-400">Total Orang</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-red-600">VVIP<br><span class="font-normal text-xs text-gray-400">(orang)</span></th>
                            <th class="px-4 py-3.5 text-center font-semibold text-red-600 bg-red-50/50 dark:bg-red-900/10">Mobil VVIP<br><span class="font-normal text-xs text-gray-400">(dedikasi)</span></th>
                            <th class="px-4 py-3.5 text-center font-semibold text-gray-600 dark:text-gray-400">Reguler<br><span class="font-normal text-xs text-gray-400">(orang)</span></th>
                            <th class="px-4 py-3.5 text-center font-semibold text-blue-600 bg-blue-50/50 dark:bg-blue-900/10">Mobil Reguler<br><span class="font-normal text-xs text-gray-400">(est. @{{ $kapasitasRegular }} org)</span></th>
                            <th class="px-4 py-3.5 text-center font-bold text-gray-900 dark:text-white bg-gray-100/80 dark:bg-white/10">Total Mobil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @php
                            $grandOrang = 0;
                            $grandVvipOrang = 0;
                            $grandMobilVvip = 0;
                            $grandRegularOrang = 0;
                            $grandMobilRegular = 0;
                            $grandTotalMobil = 0;
                        @endphp
                        @foreach($days as $day)
                            @foreach(['datang' => '🛬 Datang', 'pulang' => '🛫 Pulang'] as $arah => $label)
                            @php
                                $grp = $day[$arah];
                                if ($grp['tamu'] === 0) continue;

                                $vvipOrang     = $grp['vvip_orang'] ?? 0;
                                $mobilVvip     = $grp['vvip'];  // 1 car per VVIP tamu
                                $regularOrang  = $grp['orang'] - $vvipOrang;
                                $mobilRegular  = $regularOrang > 0 ? (int) ceil($regularOrang / $kapasitasRegular) : 0;
                                $totalMobil    = $mobilVvip + $mobilRegular;

                                $grandOrang        += $grp['orang'];
                                $grandVvipOrang    += $vvipOrang;
                                $grandMobilVvip    += $mobilVvip;
                                $grandRegularOrang += $regularOrang;
                                $grandMobilRegular += $mobilRegular;
                                $grandTotalMobil   += $totalMobil;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">{{ $day['label'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($arah === 'datang')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 dark:bg-blue-400/20 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-600/20">{{ $label }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-400/20 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300 ring-1 ring-inset ring-amber-600/20">{{ $label }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-gray-900 dark:text-white">{{ $grp['orang'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($vvipOrang)
                                        <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-400/20 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300">{{ $vvipOrang }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center bg-red-50/30 dark:bg-red-900/5">
                                    @if($mobilVvip)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-400/20 px-3 py-1 text-sm font-bold text-red-700 dark:text-red-300">🚗 {{ $mobilVvip }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center text-gray-600 dark:text-gray-400">{{ $regularOrang ?: '–' }}</td>
                                <td class="px-4 py-3.5 text-center bg-blue-50/30 dark:bg-blue-900/5">
                                    @if($mobilRegular)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-400/20 px-3 py-1 text-sm font-bold text-blue-700 dark:text-blue-300">🚌 {{ $mobilRegular }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center bg-gray-100/50 dark:bg-white/5">
                                    <span class="inline-flex items-center rounded-full bg-gray-900 dark:bg-white px-3 py-1 text-sm font-bold text-white dark:text-gray-900">{{ $totalMobil }}</span>
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-amber-50 dark:bg-amber-400/10 border-t-2 border-amber-200 dark:border-amber-400/30 font-bold">
                            <td class="px-4 py-3.5 text-gray-950 dark:text-white" colspan="2">Grand Total</td>
                            <td class="px-4 py-3.5 text-center text-gray-900 dark:text-white text-base">{{ $grandOrang }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($grandVvipOrang)
                                    <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-400/20 px-2.5 py-0.5 text-xs font-bold text-red-700 dark:text-red-300">{{ $grandVvipOrang }}</span>
                                @else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center bg-red-50/30 dark:bg-red-900/5">
                                @if($grandMobilVvip)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-400/20 px-3 py-1 text-sm font-bold text-red-700 dark:text-red-300">🚗 {{ $grandMobilVvip }}</span>
                                @else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-gray-700 dark:text-gray-300">{{ $grandRegularOrang ?: '–' }}</td>
                            <td class="px-4 py-3.5 text-center bg-blue-50/30 dark:bg-blue-900/5">
                                @if($grandMobilRegular)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-400/20 px-3 py-1 text-sm font-bold text-blue-700 dark:text-blue-300">🚌 {{ $grandMobilRegular }}</span>
                                @else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center bg-gray-100/50 dark:bg-white/5">
                                <span class="inline-flex items-center rounded-full bg-gray-900 dark:bg-white px-3 py-1.5 text-base font-bold text-white dark:text-gray-900">{{ $grandTotalMobil }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 space-y-1.5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Catatan</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">• <span class="font-semibold text-red-600">Mobil VVIP</span> — 1 kendaraan dedikasi per tamu VVIP (berapapun jumlah orangnya).</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">• <span class="font-semibold text-blue-600">Mobil Reguler</span> — estimasi berdasarkan kapasitas {{ $kapasitasRegular }} orang per kendaraan, dibulatkan ke atas.</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">• Data hanya mencakup tamu yang sudah <span class="font-semibold">konfirmasi</span> dan meminta antar jemput.</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">• Grand Total adalah akumulasi semua hari (datang + pulang), bukan jumlah unik kendaraan.</p>
        </div>
        @endif
    @endif
</x-filament-panels::page>
