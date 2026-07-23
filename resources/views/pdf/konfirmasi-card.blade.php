<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #fff;
        }
        .card {
            max-width: 460px;
            margin: 15px auto;
            border: 2px solid #d4a574;
            border-radius: 12px;
            padding: 20px 24px;
            text-align: center;
        }
        .header {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e5e5;
        }
        .header h1 {
            font-size: 14px;
            color: #333;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 11px;
            color: #b8860b;
            font-style: italic;
        }
        .qr-container {
            margin: 12px auto;
        }
        .qr-container img {
            width: 150px;
            height: 150px;
        }
        .nama {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin: 8px 0 5px 0;
        }
        .badge-stripe {
            width: 50px;
            height: 5px;
            border-radius: 3px;
            margin: 0 auto 10px;
        }
        .info {
            font-size: 12px;
            color: #666;
            margin: 4px 0;
        }
        .info strong {
            color: #333;
        }
        .footer {
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid #e5e5e5;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{{ __('undangan.event_title') }}</h1>
            <p>{{ __('undangan.tagline') }}</p>
        </div>

        <div class="qr-container">
            <img src="{{ $tamu->getQrCodeForPdf(10) }}" alt="QR Code" style="width:150px;height:150px;" />
        </div>

        <div class="nama">{{ $tamu->nama }}</div>

        @if($tamu->jenis !== 'umum')
            <div class="badge-stripe" style="background: {{ $tamu->jenis === 'VVIP' ? '#ef4444' : '#f59e0b' }};"></div>
        @endif

        <div class="info">
            {{ __('undangan.card_jumlah') }}: <strong>{{ $konfirmasi->jumlah_hadir }} {{ __('undangan.card_orang') }}</strong>
        </div>
        <div class="info">
            {{ __('undangan.card_dikonfirmasi') }}: <strong>{{ $konfirmasi->confirmed_at->translatedFormat('d F Y, H:i') }}</strong>
        </div>

        @if($konfirmasi->butuh_antar_jemput)
            <div class="info">
                {{ __('undangan.butuh_antar_jemput') }} <strong>{{ __('undangan.ya') }}</strong>
            </div>
            @if($konfirmasi->tanggal_datang)
                <div class="info">
                    {{ __('undangan.tanggal_datang') }}: <strong>{{ $konfirmasi->tanggal_datang->translatedFormat('d F Y') }}</strong>
                </div>
            @endif
            @if($konfirmasi->tanggal_pulang)
                <div class="info">
                    {{ __('undangan.tanggal_pulang') }}: <strong>{{ $konfirmasi->tanggal_pulang->translatedFormat('d F Y') }}</strong>
                </div>
            @endif
        @endif

        @if($konfirmasi->butuh_penginapan)
            <div class="info">
                {{ __('undangan.butuh_penginapan') }} <strong>{{ __('undangan.ya') }}</strong>
            </div>
            @php
                $publishLokasiMenginap = in_array(
                    strtolower(trim((string) \App\Models\Setting::getValue('publish_lokasi_menginap', '0'))),
                    ['1', 'true', 'yes', 'ya', 'on'],
                    true
                );
            @endphp
            @if($konfirmasi->penginapan)
                <div class="info">
                    {{ __('undangan.penginapan_info') }}: <strong>{{ $konfirmasi->penginapan->nama }}</strong>
                </div>
                @if($publishLokasiMenginap)
                    @if($konfirmasi->penginapan->alamat)
                        <div class="info">{{ $konfirmasi->penginapan->alamat }}</div>
                    @endif
                    @if($konfirmasi->penginapan->no_telp)
                        <div class="info">{{ __('undangan.no_telp_penginapan') }}: <strong>{{ $konfirmasi->penginapan->no_telp }}</strong></div>
                    @endif
                    @if($konfirmasi->penginapan->koordinat_maps)
                        <div class="info">{{ __('undangan.buka_maps') }}: <strong>{{ $konfirmasi->penginapan->koordinat_maps }}</strong></div>
                    @endif
                @endif
            @endif
        @endif

        <div class="footer">
            {{ __('undangan.event_title') }} &copy; {{ date('Y') }}
        </div>
    </div>
@php
    $invImgFile = app()->getLocale() === 'en'
        ? config('undangan.pdf_image_en')
        : config('undangan.pdf_image_id');
    $invImg = $invImgFile ? storage_path('app/public/' . $invImgFile) : null;
    $invImgData = ($invImg && file_exists($invImg))
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($invImg))
        : null;
@endphp
@if($invImgData)
<div style="page-break-before: always; margin: 0; padding: 0; width: 210mm; height: 297mm; overflow: hidden; text-align: center; background: #fff;"><img src="{{ $invImgData }}" style="height: 297mm; width: auto; display: inline-block;" alt="Detail Undangan"></div>
@endif
</body>
</html>
