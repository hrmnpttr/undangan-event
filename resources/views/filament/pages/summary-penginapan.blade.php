<x-filament-panels::page>
    {{-- Tab switcher --}}
    <div class="flex gap-2">
        <x-filament::button
            :color="$activeTab === 'total' ? 'primary' : 'gray'"
            wire:click="switchTab('total')"
            size="sm"
            icon="heroicon-o-table-cells"
        >Total per Penginapan</x-filament::button>
        <x-filament::button
            :color="$activeTab === 'perhari' ? 'primary' : 'gray'"
            wire:click="switchTab('perhari')"
            size="sm"
            icon="heroicon-o-calendar-days"
        >Per Malam</x-filament::button>
        <x-filament::button
            :color="$activeTab === 'estimasi' ? 'primary' : 'gray'"
            wire:click="switchTab('estimasi')"
            size="sm"
            icon="heroicon-o-clock"
        >Estimasi Belum Konfirmasi</x-filament::button>
    </div>

    {{-- ──────────── TAB TOTAL ──────────── --}}
    @if($activeTab === 'total')
        @php $rows = $this->totalSummary; @endphp

        @if($rows->isEmpty())
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-8 text-center text-gray-400">
                Belum ada data penginapan.
            </div>
        @else
        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5 text-sm">
                    <thead>
                        {{-- Group header row --}}
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <th rowspan="2"
                                class="px-4 py-3.5 text-start font-semibold text-gray-950 dark:text-white border-b border-gray-200 dark:border-white/5 border-r border-r-gray-200 dark:border-r-white/5 min-w-[200px]">
                                Penginapan
                            </th>
                            <th colspan="4"
                                class="px-4 py-2.5 text-center text-xs font-bold tracking-wide text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border-b border-gray-200 dark:border-white/5 border-r border-r-gray-200 dark:border-r-white/5">
                                Estimasi
                            </th>
                            <th colspan="7"
                                class="px-4 py-2.5 text-center text-xs font-bold tracking-wide text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border-b border-gray-200 dark:border-white/5">
                                Konfirmasi
                            </th>
                        </tr>
                        {{-- Sub-header row --}}
                        <tr class="bg-gray-50 dark:bg-white/5 text-xs">
                            <th class="px-4 py-2.5 text-center font-semibold text-blue-600 bg-blue-50/60 dark:bg-blue-900/10 border-b border-gray-200 dark:border-white/5 border-l border-l-gray-200 dark:border-l-white/5 whitespace-nowrap">Undangan</th>
                            <th class="px-4 py-2.5 text-center font-semibold text-blue-600 bg-blue-50/60 dark:bg-blue-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">Orang</th>
                            <th class="px-4 py-2.5 text-center font-semibold text-blue-600 bg-blue-50/60 dark:bg-blue-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">Double Est.</th>
                            <th class="px-4 py-2.5 text-center font-semibold text-blue-600 bg-blue-50/60 dark:bg-blue-900/10 border-b border-gray-200 dark:border-white/5 border-r border-r-gray-200 dark:border-r-white/5 whitespace-nowrap">Twin Est.</th>
                            <th class="px-4 py-2.5 text-center font-semibold text-green-600 bg-green-50/60 dark:bg-green-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">Undangan</th>
                            <th class="px-4 py-2.5 text-center font-semibold text-green-600 bg-green-50/60 dark:bg-green-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">Orang</th>
                            <th class="px-4 py-2.5 text-center font-semibold text-orange-600 bg-green-50/60 dark:bg-green-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">Double</th>
                            <th class="px-4 py-2.5 text-center font-semibold text-indigo-600 bg-green-50/60 dark:bg-green-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">Twin L<br><span class="font-normal text-gray-400">(kmr/org)</span></th>
                            <th class="px-4 py-2.5 text-center font-semibold text-pink-600 bg-green-50/60 dark:bg-green-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">Twin P<br><span class="font-normal text-gray-400">(kmr/org)</span></th>
                            <th class="px-4 py-2.5 text-center font-semibold text-gray-400 bg-green-50/60 dark:bg-green-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">?</th>
                            <th class="px-4 py-2.5 text-center font-bold text-gray-700 dark:text-gray-200 bg-green-50/60 dark:bg-green-900/10 border-b border-gray-200 dark:border-white/5 whitespace-nowrap">Total Kamar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach($rows as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white border-r border-gray-200 dark:border-white/5">
                                {{ $r['nama'] }}
                                @if($r['kode'])
                                    <span class="ml-1.5 inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400">{{ $r['kode'] }}</span>
                                @endif
                            </td>
                            {{-- Estimasi --}}
                            <td class="px-4 py-3.5 text-center text-blue-600 dark:text-blue-400 font-semibold border-l border-gray-100 dark:border-white/5">{{ $r['estimasi_tamu'] }}</td>
                            <td class="px-4 py-3.5 text-center text-blue-700 dark:text-blue-300 font-bold">{{ $r['estimasi_orang'] }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($r['estimasi_double'])
                                    <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-0.5 text-xs font-semibold text-orange-700 dark:text-orange-300">{{ $r['estimasi_double'] }}</span>
                                @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center border-r border-gray-200 dark:border-white/5">
                                @if($r['estimasi_twin_orang'])
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-400/20 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ $r['estimasi_twin_orang'] }}</span>
                                @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                            </td>
                            {{-- Konfirmasi --}}
                            <td class="px-4 py-3.5 text-center text-green-600 dark:text-green-400 font-semibold">{{ $r['konfirmasi_tamu'] }}</td>
                            <td class="px-4 py-3.5 text-center text-green-700 dark:text-green-300 font-bold">{{ $r['konfirmasi_orang'] }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($r['double_rooms'])
                                    <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-0.5 text-xs font-semibold text-orange-700 dark:text-orange-300">{{ $r['double_rooms'] }} kmr</span>
                                @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-indigo-600 dark:text-indigo-400 text-xs">
                                @if($r['twin_l_rooms'])
                                    <span class="font-semibold">{{ $r['twin_l_rooms'] }} kmr</span><span class="text-gray-400"> / {{ $r['twin_l_orang'] }} org</span>
                                @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-pink-600 dark:text-pink-400 text-xs">
                                @if($r['twin_p_rooms'])
                                    <span class="font-semibold">{{ $r['twin_p_rooms'] }} kmr</span><span class="text-gray-400"> / {{ $r['twin_p_orang'] }} org</span>
                                @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($r['undetermined'])
                                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $r['undetermined'] }}</span>
                                @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($r['total_kamar'])
                                    <span class="inline-flex items-center rounded-full bg-gray-900 dark:bg-white px-3 py-1 text-sm font-bold text-white dark:text-gray-900">{{ $r['total_kamar'] }}</span>
                                @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-amber-50 dark:bg-amber-400/10 border-t-2 border-amber-200 dark:border-amber-400/30">
                            <td class="px-4 py-3.5 font-bold text-gray-950 dark:text-white border-r border-gray-200 dark:border-white/5">Grand Total</td>
                            <td class="px-4 py-3.5 text-center text-blue-600 font-bold">{{ $rows->sum('estimasi_tamu') }}</td>
                            <td class="px-4 py-3.5 text-center text-blue-700 font-bold text-base">{{ $rows->sum('estimasi_orang') }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($rows->sum('estimasi_double'))
                                    <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-0.5 text-xs font-bold text-orange-700 dark:text-orange-300">{{ $rows->sum('estimasi_double') }}</span>
                                @else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center border-r border-gray-200 dark:border-white/5">
                                @if($rows->sum('estimasi_twin_orang'))
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-400/20 px-2.5 py-0.5 text-xs font-bold text-indigo-700 dark:text-indigo-300">{{ $rows->sum('estimasi_twin_orang') }}</span>
                                @else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-green-600 font-bold">{{ $rows->sum('konfirmasi_tamu') }}</td>
                            <td class="px-4 py-3.5 text-center text-green-700 font-bold text-base">{{ $rows->sum('konfirmasi_orang') }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($rows->sum('double_rooms'))
                                    <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-0.5 text-xs font-bold text-orange-700 dark:text-orange-300">{{ $rows->sum('double_rooms') }} kmr</span>
                                @else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-indigo-600 text-xs font-bold">
                                @if($rows->sum('twin_l_rooms')){{ $rows->sum('twin_l_rooms') }} kmr / {{ $rows->sum('twin_l_orang') }} org@else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-pink-600 text-xs font-bold">
                                @if($rows->sum('twin_p_rooms')){{ $rows->sum('twin_p_rooms') }} kmr / {{ $rows->sum('twin_p_orang') }} org@else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($rows->sum('undetermined'))
                                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-2 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-300">{{ $rows->sum('undetermined') }}</span>
                                @else<span class="text-gray-400">–</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center rounded-full bg-gray-900 dark:bg-white px-3 py-1 text-base font-bold text-white dark:text-gray-900">{{ $rows->sum('total_kamar') }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 space-y-1.5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Keterangan</p>
            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-blue-600">Estimasi</span> — berdasarkan data tamu (semua yang punya penginapan), belum perlu konfirmasi.</p>
            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-orange-600">Double</span> — pasutri (peserta dengan nama_pasangan) + tamu yang ditandai tipe kamar double.</p>
            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-indigo-600">Twin L/P</span> — peserta laki-laki/perempuan tanpa pasangan, 2 orang per kamar (dibulatkan ke atas).</p>
            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-amber-600">?</span> — data peserta belum diisi, perlu pengecekan manual.</p>
        </div>
        @endif

    {{-- ──────────── TAB PER MALAM ──────────── --}}
    @elseif($activeTab === 'perhari')
        @php $days = $this->perHariSummary; @endphp

        @if($days->isEmpty())
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-8 text-center text-gray-400">
                Belum ada data tanggal menginap.
            </div>
        @else
            @foreach($days as $day)
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                {{-- Day header --}}
                <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/5">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-base font-bold text-gray-950 dark:text-white">🌙 Malam {{ $day['label'] }}</span>
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-white/10 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-300">
                                {{ $day['total_tamu'] }} tamu · {{ $day['total_orang'] }} orang · {{ $day['total_kamar'] }} kamar
                            </span>
                        </div>
                        <div class="flex gap-2 text-xs">
                            @if($day['double_total'])
                                <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-1 font-semibold text-orange-700 dark:text-orange-300">Double: {{ $day['double_total'] }} kmr</span>
                            @endif
                            @if($day['twin_l_total'])
                                <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-400/20 px-2.5 py-1 font-semibold text-indigo-700 dark:text-indigo-300">Twin L: {{ $day['twin_l_total'] }} kmr</span>
                            @endif
                            @if($day['twin_p_total'])
                                <span class="inline-flex items-center rounded-full bg-pink-100 dark:bg-pink-400/20 px-2.5 py-1 font-semibold text-pink-700 dark:text-pink-300">Twin P: {{ $day['twin_p_total'] }} kmr</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5 text-sm">
                        <thead>
                            <tr class="text-xs bg-gray-50/50 dark:bg-white/5">
                                <th class="px-4 py-3 text-start font-semibold text-gray-600 dark:text-gray-400">Penginapan</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Undangan</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Orang</th>
                                <th class="px-4 py-3 text-center font-semibold text-orange-600">Double</th>
                                <th class="px-4 py-3 text-center font-semibold text-indigo-600">Twin L<br><span class="font-normal text-gray-400">(kmr/org)</span></th>
                                <th class="px-4 py-3 text-center font-semibold text-pink-600">Twin P<br><span class="font-normal text-gray-400">(kmr/org)</span></th>
                                <th class="px-4 py-3 text-center font-semibold text-amber-500">?</th>
                                <th class="px-4 py-3 text-center font-bold text-gray-700 dark:text-gray-300">Total Kamar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            @foreach($day['per_penginapan'] as $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">{{ $p['penginapan'] }}</td>
                                <td class="px-4 py-3.5 text-center text-gray-500">{{ $p['tamu'] }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-gray-900 dark:text-white">{{ $p['orang'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($p['double_rooms'])
                                        <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-0.5 text-xs font-semibold text-orange-700 dark:text-orange-300">{{ $p['double_rooms'] }} kmr</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center text-indigo-600 dark:text-indigo-400 text-xs">
                                    @if($p['twin_l'])
                                        <span class="font-semibold">{{ $p['twin_l'] }} kmr</span><span class="text-gray-400"> / {{ $p['twin_l_orang'] }} org</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center text-pink-600 dark:text-pink-400 text-xs">
                                    @if($p['twin_p'])
                                        <span class="font-semibold">{{ $p['twin_p'] }} kmr</span><span class="text-gray-400"> / {{ $p['twin_p_orang'] }} org</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($p['undetermined'])
                                        <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $p['undetermined'] }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($p['total_kamar'])
                                        <span class="inline-flex items-center rounded-full bg-gray-900 dark:bg-white px-3 py-1 text-sm font-bold text-white dark:text-gray-900">{{ $p['total_kamar'] }}</span>
                                    @else<span class="text-gray-300 dark:text-gray-600">–</span>@endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

            <p class="text-xs text-gray-400 px-1">Setiap blok = 1 malam. Tamu dihitung di semua malam antara tanggal datang s.d. sebelum tanggal pulang.</p>
        @endif
    {{-- ──────────── TAB ESTIMASI BELUM KONFIRMASI ──────────── --}}
    @elseif($activeTab === 'estimasi')
        @php $tamus = $this->estimasiDetail; @endphp

        @if($tamus->isEmpty())
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-8 text-center text-gray-400">
                Semua tamu yang memiliki penginapan sudah konfirmasi. 🎉
            </div>
        @else
            @php
                $grouped = $tamus->groupBy(fn ($t) => $t->penginapanRecord?->nama ?? 'Tidak diketahui');

                // Grand total with pooled logic per penginapan — split by gender
                $grandTotalOrang = 0;
                $grandTotalKamar = 0;
                $groupedStats = [];
                foreach ($grouped as $pNama => $tamuList) {
                    $nonDouble = $tamuList->where('tipe_kamar', '!=', 'double');

                    $doubleKamar     = $tamuList->where('tipe_kamar', 'double')->count();
                    $doubleOrang     = $tamuList->where('tipe_kamar', 'double')->sum(fn ($t) => $t->jumlah_orang ?? 1);
                    $twinLOrang      = $nonDouble->where('jenis_kelamin', 'L')->sum(fn ($t) => $t->jumlah_orang ?? 1);
                    $twinPOrang      = $nonDouble->where('jenis_kelamin', 'P')->sum(fn ($t) => $t->jumlah_orang ?? 1);
                    $undetermined    = $nonDouble->filter(fn ($t) => empty($t->jenis_kelamin))->sum(fn ($t) => $t->jumlah_orang ?? 1);
                    $twinLKamar      = (int) ceil($twinLOrang / 2);
                    $twinPKamar      = (int) ceil($twinPOrang / 2);
                    $undeterminedKmr = (int) ceil($undetermined / 2);
                    $totalOrang      = $doubleOrang + $twinLOrang + $twinPOrang + $undetermined;
                    $totalKamar      = $doubleKamar + $twinLKamar + $twinPKamar + $undeterminedKmr;

                    $groupedStats[$pNama] = compact(
                        'doubleKamar', 'doubleOrang',
                        'twinLOrang', 'twinLKamar',
                        'twinPOrang', 'twinPKamar',
                        'undetermined', 'undeterminedKmr',
                        'totalOrang', 'totalKamar'
                    );
                    $grandTotalOrang += $totalOrang;
                    $grandTotalKamar += $totalKamar;
                }
            @endphp

            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <span class="text-base font-bold text-gray-950 dark:text-white">Tamu Belum Konfirmasi Penginapan</span>
                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-3 py-1 text-sm font-semibold text-amber-700 dark:text-amber-300">
                        {{ $tamus->count() }} tamu · {{ $grandTotalOrang }} orang · Est. {{ $grandTotalKamar }} kamar
                    </span>
                </div>
            </div>

            @foreach($grouped as $penginapanNama => $tamuList)
            @php $stats = $groupedStats[$penginapanNama]; @endphp
            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/5">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="text-sm font-bold text-gray-950 dark:text-white">{{ $penginapanNama }}</span>
                        <div class="flex items-center gap-2 text-xs flex-wrap">
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-white/10 px-2.5 py-1 font-medium text-gray-600 dark:text-gray-300">
                                {{ $tamuList->count() }} tamu · {{ $stats['totalOrang'] }} orang
                            </span>
                            @if($stats['doubleKamar'])
                                <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-400/20 px-2.5 py-1 font-semibold text-orange-700 dark:text-orange-300">
                                    Double: {{ $stats['doubleKamar'] }} kmr
                                </span>
                            @endif
                            @if($stats['twinLOrang'])
                                <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-400/20 px-2.5 py-1 font-semibold text-indigo-700 dark:text-indigo-300">
                                    Twin L: {{ $stats['twinLOrang'] }} org → {{ $stats['twinLKamar'] }} kmr
                                </span>
                            @endif
                            @if($stats['twinPOrang'])
                                <span class="inline-flex items-center rounded-full bg-pink-100 dark:bg-pink-400/20 px-2.5 py-1 font-semibold text-pink-700 dark:text-pink-300">
                                    Twin P: {{ $stats['twinPOrang'] }} org → {{ $stats['twinPKamar'] }} kmr
                                </span>
                            @endif
                            @if($stats['undetermined'])
                                <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-400/20 px-2.5 py-1 font-semibold text-amber-700 dark:text-amber-300">
                                    ?: {{ $stats['undetermined'] }} org → {{ $stats['undeterminedKmr'] }} kmr
                                </span>
                            @endif
                            <span class="inline-flex items-center rounded-full bg-gray-900 dark:bg-white px-2.5 py-1 font-bold text-white dark:text-gray-900">
                                {{ $stats['totalKamar'] }} kamar
                            </span>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5 text-sm">
                        <thead>
                            <tr class="text-xs bg-gray-50/50 dark:bg-white/5">
                                <th class="px-4 py-3 text-start font-semibold text-gray-600 dark:text-gray-400 w-10">No</th>
                                <th class="px-4 py-3 text-start font-semibold text-gray-600 dark:text-gray-400">Nama Tamu</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Jenis</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">L/P</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Jumlah Orang</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Tipe Kamar</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            @foreach($tamuList as $idx => $tamu)
                            @php
                                $jumlahOrang = $tamu->jumlah_orang ?? 1;
                                $tipeKamar = match ($tamu->tipe_kamar) {
                                    'double' => 'Double',
                                    'twin'   => 'Twin',
                                    default  => 'Auto',
                                };
                                $konfirmasi = $tamu->konfirmasi;
                                $status = $konfirmasi ? 'Belum konfirmasi penginapan' : 'Belum konfirmasi';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3 text-gray-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $tamu->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($tamu->jenis)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold
                                            {{ $tamu->jenis === 'VVIP' ? 'bg-red-100 text-red-700 dark:bg-red-400/20 dark:text-red-300' : ($tamu->jenis === 'VIP' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-400/20 dark:text-yellow-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400') }}">
                                            {{ $tamu->jenis }}
                                        </span>
                                    @else<span class="text-gray-300">–</span>@endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($tamu->jenis_kelamin === 'L')
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-400/20 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300">L</span>
                                    @elseif($tamu->jenis_kelamin === 'P')
                                        <span class="inline-flex items-center rounded-full bg-pink-100 dark:bg-pink-400/20 px-2 py-0.5 text-xs font-semibold text-pink-700 dark:text-pink-300">P</span>
                                    @else
                                        <span class="text-amber-500 font-semibold text-xs">?</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">{{ $jumlahOrang }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold
                                        {{ $tamu->tipe_kamar === 'double' ? 'bg-orange-100 text-orange-700 dark:bg-orange-400/20 dark:text-orange-300' : ($tamu->tipe_kamar === 'twin' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-400/20 dark:text-indigo-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400') }}">
                                        {{ $tipeKamar }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($konfirmasi)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-400/20 dark:text-amber-300">
                                            {{ $status }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-400/20 dark:text-red-300">
                                            {{ $status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

            <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 space-y-1.5">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Keterangan</p>
                <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-red-600">Belum konfirmasi</span> — tamu belum mengisi formulir konfirmasi sama sekali.</p>
                <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-amber-600">Belum konfirmasi penginapan</span> — tamu sudah konfirmasi kehadiran, tapi belum centang butuh penginapan.</p>
                <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-orange-600">Double</span> — 1 undangan = 1 kamar (1–2 orang).</p>
                <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-indigo-600">Twin L</span> — laki-laki non-double di-pool per penginapan, dibagi 2 per kamar.</p>
                <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-pink-600">Twin P</span> — perempuan non-double di-pool per penginapan, dibagi 2 per kamar.</p>
                <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-amber-600">?</span> — jenis kelamin belum diisi. Set manual di Master Tamu atau otomatis dari prefix nama (Romo/RP/RD/Fr/Br → L, Sr/Suster → P).</p>
            </div>
        @endif
    @endif
</x-filament-panels::page>
