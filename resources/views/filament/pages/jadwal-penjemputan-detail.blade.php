<div class="space-y-3 p-2">
    @forelse($guests as $konfirmasi)
        <div class="rounded-lg border p-4
            {{ (!$konfirmasi->acknowledged || $konfirmasi->jumlah_perubahan_baru > 0)
                ? 'border-warning-300 bg-warning-50 dark:border-warning-600 dark:bg-warning-400/10'
                : 'border-gray-200 dark:border-gray-700' }}">

            {{-- Header: nama + badge + jumlah --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-950 dark:text-white">{{ $konfirmasi->tamu->nama }}</span>
                    @php
                        $jenis = $konfirmasi->tamu->jenis ?? 'umum';
                        $badgeClass = match($jenis) {
                            'VVIP' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30',
                            'VIP' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/30',
                            default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/30',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">
                        {{ $jenis }}
                    </span>
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $konfirmasi->jumlah_hadir }} orang
                </span>
            </div>

            {{-- Info grid --}}
            <div class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                <div>
                    <span class="text-gray-400 dark:text-gray-500">Datang:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $konfirmasi->tanggal_datang?->translatedFormat('d M Y') ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 dark:text-gray-500">Pulang:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $konfirmasi->tanggal_pulang?->translatedFormat('d M Y') ?? '-' }}</span>
                </div>
                @if($konfirmasi->butuh_penginapan)
                    <div class="col-span-2">
                        <span class="text-gray-400 dark:text-gray-500">🏨 Butuh penginapan</span>
                    </div>
                @endif
            </div>

            {{-- Status --}}
            @if(!$konfirmasi->acknowledged)
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-md bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-400 px-2 py-0.5 text-xs font-medium ring-1 ring-inset ring-blue-600/20 dark:ring-blue-400/30">Baru</span>
                </div>
            @elseif($konfirmasi->jumlah_perubahan_baru > 0)
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-md bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400 px-2 py-0.5 text-xs font-medium ring-1 ring-inset ring-warning-600/20 dark:ring-warning-400/30">
                        ⚠ {{ $konfirmasi->jumlah_perubahan_baru }} perubahan belum diketahui
                    </span>
                </div>
            @endif

            {{-- Catatan Perubahan (collapsed) --}}
            @if($konfirmasi->catatan_perubahan && count($konfirmasi->catatan_perubahan) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        📝 {{ count($konfirmasi->catatan_perubahan) }} catatan perubahan
                    </summary>
                    <div class="mt-2 space-y-1.5">
                        @foreach(array_reverse($konfirmasi->catatan_perubahan) as $log)
                            <div class="rounded border p-2 text-xs
                                {{ empty($log['acknowledged'])
                                    ? 'border-warning-300 bg-warning-50 dark:border-warning-700 dark:bg-warning-900/30'
                                    : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log['field'] ?? '-' }}</span>
                                    @if(empty($log['acknowledged']))
                                        <span class="text-warning-600 dark:text-warning-400 font-semibold">BARU</span>
                                    @else
                                        <span class="text-green-600 dark:text-green-400">✓</span>
                                    @endif
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    <span class="line-through">{{ $log['old'] ?? '-' }}</span> →
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log['new'] ?? '-' }}</span>
                                </div>
                                @if(!empty($log['changed_at']))
                                    <div class="text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($log['changed_at'])->translatedFormat('d M Y H:i') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>Tidak ada data tamu untuk tanggal ini.</p>
        </div>
    @endforelse
</div>
