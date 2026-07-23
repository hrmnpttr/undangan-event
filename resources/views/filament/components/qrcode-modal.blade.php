<div class="flex flex-col items-center gap-4 p-4">
    <div class="w-64 h-64">
        {!! $tamu->getQrCodeSvg(8) !!}
    </div>
    <p class="text-lg font-semibold text-center">{{ $tamu->nama }}</p>
    <p class="text-sm text-gray-500">{{ $tamu->getQrUrl() }}</p>
    @if($tamu->jenis !== 'umum')
        <span class="inline-flex items-center px-4 py-2 text-lg font-bold rounded-full
            {{ $tamu->jenis === 'VVIP' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
            {{ $tamu->jenis }}
        </span>
    @endif
    <p class="text-xs text-gray-400">Kode: {{ $tamu->kode_unik }}</p>
</div>
