<?php

/*
|--------------------------------------------------------------------------
| Event Configuration / Konfigurasi Acara
|--------------------------------------------------------------------------
|
| EN: Central place for event-specific content so the application stays
|     reusable for any event. Set these values in your .env file. The
|     runtime values shown to guests (event name, date, deadline) are
|     managed from the admin panel (Settings) and take precedence over
|     these defaults.
|
| ID: Tempat terpusat untuk konten khusus acara agar aplikasi dapat dipakai
|     ulang untuk acara apa pun. Atur nilai-nilai ini di file .env. Nilai yang
|     tampil ke tamu (nama acara, tanggal, batas konfirmasi) dikelola dari
|     panel admin (Settings) dan lebih diutamakan dari default ini.
|
*/

return [

    // Short event name. / Nama acara singkat.
    'event_name' => env('EVENT_NAME', 'Your Event'),

    // Full event name used inside invitation messages / OG description.
    // Nama lengkap acara untuk pesan undangan / deskripsi Open Graph.
    'event_full_name' => env('EVENT_FULL_NAME', env('EVENT_NAME', 'acara kami')),

    // Event tagline. / Tagline acara.
    'tagline' => env('EVENT_TAGLINE', ''),

    // Default event date & confirmation deadline (Y-m-d) if no Setting exists.
    // Tanggal acara & batas konfirmasi default (Y-m-d) bila belum ada Setting.
    'event_date'         => env('EVENT_DATE', '2026-01-01'),
    'confirmation_until' => env('EVENT_CONFIRMATION_UNTIL', '2026-01-01'),

    // Venue for the "Navigate to Venue" button. Provide either a full Google
    // Maps URL or a plain address / search query.
    // Lokasi untuk tombol "Navigasi ke Lokasi Acara". Isi dengan URL Google
    // Maps lengkap atau alamat / kata kunci pencarian biasa.
    'venue_name' => env('EVENT_VENUE_NAME', 'Event Venue'),
    'venue_maps' => env('EVENT_VENUE_MAPS', ''),

    // Optional images placed in storage/app/public (user-supplied assets).
    // Leave empty to disable. / Gambar opsional di storage/app/public
    // (aset milik pengguna). Kosongkan untuk menonaktifkan.
    'og_image_en'  => env('EVENT_OG_IMAGE_EN', ''),
    'og_image_id'  => env('EVENT_OG_IMAGE_ID', ''),
    'pdf_image_en' => env('EVENT_PDF_IMAGE_EN', ''),
    'pdf_image_id' => env('EVENT_PDF_IMAGE_ID', ''),

    // Invitation hero images (entry overlay). Filenames are built as:
    //   {prefix}{lang}{1|2}{"" | "big"}.png  inside storage/app/public.
    // Leave prefix empty to disable the image overlay.
    // Gambar utama undangan. Kosongkan prefix untuk menonaktifkan.
    'image_prefix'  => env('EVENT_IMAGE_PREFIX', ''),
    'image_lang_en' => env('EVENT_IMAGE_LANG_EN', 'En'),
    'image_lang_id' => env('EVENT_IMAGE_LANG_ID', 'Ind'),

    // Public base URL shown for a guest's invitation link in the admin table.
    // Falls back to APP_URL when empty. / Base URL tautan undangan di admin.
    'invite_base_url' => env('EVENT_INVITE_BASE_URL', ''),

    // Email domain used for auto-generated driver (sopir) login accounts.
    // Domain email untuk akun login sopir yang dibuat otomatis.
    'driver_email_domain' => env('EVENT_DRIVER_EMAIL_DOMAIN', 'drivers.local'),

];
