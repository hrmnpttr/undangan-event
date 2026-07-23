<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $ogDescription ?? __('undangan.tagline') }}">
    <meta name="author" content="{{ config('app.name') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $ogTitle ?? __('undangan.event_title') }}">
    <meta property="og:description" content="{{ $ogDescription ?? __('undangan.tagline') }}">
    @php($ogMetaImage = app()->getLocale() === 'en' ? config('undangan.og_image_en') : config('undangan.og_image_id'))
    <meta property="og:image" content="{{ $ogMetaImage ? asset('storage/' . $ogMetaImage) : asset('favicon.svg') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="500">
    <meta property="og:image:height" content="263">
    <meta property="og:image:alt" content="{{ $ogTitle ?? __('undangan.event_title') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $ogTitle ?? __('undangan.event_title') }}">
    <meta property="twitter:description" content="{{ $ogDescription ?? __('undangan.tagline') }}">
    <meta property="twitter:image" content="{{ $ogMetaImage ? asset('storage/' . $ogMetaImage) : asset('favicon.svg') }}">
    
    <title>{{ __('undangan.event_title') }}</title>
    <link rel="icon" href="/favicon1.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
            background: linear-gradient(135deg, #fdf2f8 0%, #fef3c7 50%, #fdf2f8 100%);
            min-height: 100vh;
        }
        .font-display {
            font-family: 'Playfair Display', serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .btn-gold {
            background: linear-gradient(135deg, #d4a574 0%, #c4956a 50%, #b8860b 100%);
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(180, 140, 80, 0.4);
        }
        .badge-vip {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        .badge-vvip {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}

    @livewireScripts
</body>
</html>
