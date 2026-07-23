<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Mode Switcher --}}
        <div class="flex gap-2">
            <x-filament::button
                :color="$mode === 'hardware' ? 'primary' : 'gray'"
                wire:click="switchMode('hardware')"
                icon="heroicon-o-computer-desktop"
            >
                Scanner Alat / Manual
            </x-filament::button>
            <x-filament::button
                :color="$mode === 'camera' ? 'primary' : 'gray'"
                wire:click="switchMode('camera')"
                icon="heroicon-o-camera"
            >
                Kamera HP
            </x-filament::button>
        </div>

        {{-- Hardware Scanner Mode --}}
        @if($mode === 'hardware')
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
                <div class="text-center mb-4">
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Arahkan scanner ke QR Code atau ketik kode manual
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Scanner alat (Honeywell, dll) akan otomatis terdeteksi
                    </p>
                </div>
                <form wire:submit="submitManual" class="flex gap-3 max-w-lg mx-auto">
                    <input
                        type="text"
                        wire:model="manualInput"
                        id="hardware-scanner-input"
                        class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-lg px-4 py-3"
                        placeholder="Scan atau ketik kode..."
                        autofocus
                        autocomplete="off"
                    />
                    <x-filament::button type="submit" size="lg">
                        Proses
                    </x-filament::button>
                </form>
            </div>
        @endif

        {{-- Camera Mode --}}
        @if($mode === 'camera')
            @if($cameraActive)
                <div
                    class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6"
                    x-data
                    x-init="$nextTick(() => { if (window.__qrStartCamera) window.__qrStartCamera(); })"
                >
                    <div id="qr-reader" class="mx-auto max-w-md" wire:ignore></div>
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-3">
                        Arahkan kamera ke QR Code — otomatis berhenti setelah scan berhasil
                    </p>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50 p-8 text-center">
                    <div class="flex flex-col items-center gap-4">
                        <p class="text-gray-500 dark:text-gray-400">
                            Kamera tidak aktif. Klik tombol di bawah untuk mulai scan.
                        </p>
                        <x-filament::button
                            wire:click="startScanning"
                            icon="heroicon-o-qr-code"
                            size="lg"
                        >
                            🔄 Scan Lagi
                        </x-filament::button>
                    </div>
                </div>
            @endif
        @endif

        {{-- Last Scanned Result --}}
        @if($lastScanned)
            <div class="rounded-xl border-2 p-6
                {{ $lastScanned['found']
                    ? ($lastScanned['sudah_konfirmasi'] ?? false
                        ? 'border-green-500 bg-green-50 dark:bg-green-950/30'
                        : 'border-yellow-500 bg-yellow-50 dark:bg-yellow-950/30')
                    : 'border-red-500 bg-red-50 dark:bg-red-950/30' }}">

                @if($lastScanned['found'])
                    <div class="text-center space-y-3">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $lastScanned['nama'] }}
                        </h2>

                        @if($lastScanned['jenis'] !== 'umum')
                            <span class="inline-flex items-center px-6 py-2 text-2xl font-extrabold rounded-full
                                {{ $lastScanned['jenis'] === 'VVIP' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                                {{ $lastScanned['jenis'] }}
                            </span>
                        @endif

                        <div class="flex justify-center gap-6 text-lg">
                            <div>
                                <span class="text-gray-500">Scan ke-</span>
                                <span class="font-bold text-2xl {{ $lastScanned['jumlah_scan'] > 1 ? 'text-orange-600' : 'text-green-600' }}">
                                    {{ $lastScanned['jumlah_scan'] }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">Undangan:</span>
                                <span class="font-bold">{{ $lastScanned['jumlah_orang'] }} orang</span>
                            </div>
                            @if($lastScanned['jumlah_konfirmasi'])
                                <div>
                                    <span class="text-gray-500">Konfirmasi:</span>
                                    <span class="font-bold">{{ $lastScanned['jumlah_konfirmasi'] }} orang</span>
                                </div>
                            @endif
                        </div>

                        @if(!$lastScanned['sudah_konfirmasi'])
                            <div class="bg-yellow-100 dark:bg-yellow-900/50 rounded-lg px-4 py-2 inline-block">
                                <span class="text-yellow-800 dark:text-yellow-200 font-semibold text-lg">
                                    ⚠️ Tamu ini BELUM KONFIRMASI
                                </span>
                            </div>
                        @endif

                        <p class="text-gray-400 text-sm">{{ $lastScanned['waktu'] }}</p>
                    </div>
                @else
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-red-600 dark:text-red-400">
                            ❌ Tamu Tidak Ditemukan
                        </h2>
                        <p class="text-gray-500 mt-2">Kode: {{ $lastScanned['kode'] }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Scan History --}}
        @if(count($scanHistory) > 0)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Riwayat Scan ({{ count($scanHistory) }})
                    </h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($scanHistory as $scan)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($scan['found'])
                                    <span class="text-green-500">✓</span>
                                    <div>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $scan['nama'] }}</span>
                                        @if($scan['jenis'] !== 'umum')
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full
                                                {{ $scan['jenis'] === 'VVIP' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $scan['jenis'] }}
                                            </span>
                                        @endif
                                        @if(!$scan['sudah_konfirmasi'])
                                            <span class="ml-2 text-xs text-yellow-600 font-semibold">Belum konfirmasi</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-red-500">✗</span>
                                    <span class="text-gray-500">Tidak ditemukan: {{ $scan['kode'] }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                @if($scan['found'])
                                    <span>Scan ke-{{ $scan['jumlah_scan'] }}</span>
                                @endif
                                <span>{{ $scan['waktu'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @script
    <script>
        // ===== Hardware Scanner Detection =====
        let scanBuffer = '';
        let scanTimeout = null;
        const SCAN_THRESHOLD_MS = 80;

        document.addEventListener('keydown', (e) => {
            const input = document.getElementById('hardware-scanner-input');
            if (!input) return;

            if (document.activeElement === input) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $wire.call('submitManual');
                    setTimeout(() => input.focus(), 100);
                }
                return;
            }

            if (e.key === 'Enter' && scanBuffer.length > 3) {
                e.preventDefault();
                $wire.call('processScan', scanBuffer);
                scanBuffer = '';
                return;
            }

            if (e.key.length === 1) {
                clearTimeout(scanTimeout);
                scanBuffer += e.key;
                scanTimeout = setTimeout(() => {
                    scanBuffer = '';
                }, SCAN_THRESHOLD_MS);
            }
        });

        $wire.on('scan-processed', () => {
            setTimeout(() => {
                const input = document.getElementById('hardware-scanner-input');
                if (input) {
                    input.value = '';
                    input.focus();
                }
            }, 200);
        });

        // ===== Camera Mode (html5-qrcode) =====
        let html5QrScanner = null;
        let isScanning = false;
        let scanLocked = false;

        async function loadHtml5Qrcode() {
            if (typeof window.Html5Qrcode !== 'undefined') return;
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
                script.onload = resolve;
                script.onerror = () => reject(new Error('Failed to load html5-qrcode'));
                document.head.appendChild(script);
            });
        }

        async function startCamera() {
            // Guard: don't start twice
            if (isScanning) return;
            scanLocked = false;

            try {
                await loadHtml5Qrcode();
            } catch (err) {
                console.error('Failed to load html5-qrcode library:', err);
                return;
            }

            // Wait for DOM element
            const readerEl = document.getElementById('qr-reader');
            if (!readerEl) {
                console.warn('qr-reader element not found');
                return;
            }

            // Clean up any previous instance
            if (html5QrScanner) {
                try { await html5QrScanner.stop(); } catch(e) {}
                html5QrScanner = null;
            }

            // Clear previous scanner DOM leftovers
            readerEl.innerHTML = '';

            html5QrScanner = new window.Html5Qrcode('qr-reader');
            isScanning = true;

            try {
                await html5QrScanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    (decodedText) => {
                        // Lock immediately — only process ONE scan
                        if (scanLocked) return;
                        scanLocked = true;

                        // Stop camera synchronously as possible
                        stopCamera();

                        // Send result to server
                        $wire.call('processScan', decodedText);
                    },
                    (errorMessage) => {
                        // no QR in frame — ignore
                    }
                );
            } catch (err) {
                console.error('Camera start error:', err);
                isScanning = false;
            }
        }

        function stopCamera() {
            isScanning = false;
            if (html5QrScanner) {
                try {
                    html5QrScanner.stop().catch(() => {});
                } catch(e) {}
                html5QrScanner = null;
            }
        }

        // Expose globally so Alpine x-init can call it
        window.__qrStartCamera = startCamera;
        window.__qrStopCamera = stopCamera;

        // Watch for mode switch to hardware
        $wire.$watch('mode', (value) => {
            if (value !== 'camera') {
                stopCamera();
                setTimeout(() => {
                    const input = document.getElementById('hardware-scanner-input');
                    if (input) input.focus();
                }, 300);
            }
        });

        // Initial setup for hardware mode
        if ($wire.mode === 'hardware') {
            setTimeout(() => {
                const input = document.getElementById('hardware-scanner-input');
                if (input) input.focus();
            }, 500);
        }

        // Cleanup on navigate away
        document.addEventListener('livewire:navigated', () => {
            stopCamera();
        });
    </script>
    @endscript
</x-filament-panels::page>
