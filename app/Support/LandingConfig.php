<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * LandingConfig
 * -------------
 * EN: Central, database-backed configuration for the public invitation
 *     landing page. Every value lives in the existing `settings` table
 *     (key/value), so the invitation can be re-branded for any kind of
 *     event — weddings (Islamic / Christian / Catholic / Buddhist),
 *     corporate gatherings, tech meetups, or a fully custom design —
 *     entirely from the admin panel, without code changes or migrations.
 *
 * ID: Konfigurasi terpusat (disimpan di database) untuk halaman undangan
 *     publik. Semua nilai disimpan di tabel `settings` yang sudah ada,
 *     sehingga undangan dapat diganti tema untuk acara apa pun —
 *     pernikahan (Islam / Kristen / Katolik / Buddha), acara perusahaan,
 *     pertemuan/tech meetup, atau desain kustom — sepenuhnya dari panel
 *     admin, tanpa perubahan kode maupun migrasi.
 *
 * All keys are prefixed with `landing_` inside the settings table.
 */
class LandingConfig
{
    /** Prefix used for every landing setting key. */
    public const PREFIX = 'landing_';

    /** Per-request cache of all landing_* settings (nama => value). */
    private static ?array $store = null;

    /**
     * Load every landing setting in a single query (cached per request).
     */
    private static function store(): array
    {
        if (self::$store === null) {
            try {
                self::$store = Setting::query()
                    ->where('nama', 'like', self::PREFIX . '%')
                    ->pluck('value', 'nama')
                    ->all();
            } catch (\Throwable) {
                self::$store = [];
            }
        }

        return self::$store;
    }

    /** Forget the cached settings (after a write). */
    public static function flush(): void
    {
        self::$store = null;
    }

    /**
     * Available invitation templates.
     * Each entry: label (admin), group, and the accent palette used to theme
     * the shared RSVP/confirmation UI via CSS variables.
     */
    public static function templates(): array
    {
        return [
            'default' => [
                'label' => 'Klasik (Gold / Default)',
                'group' => 'Umum',
                'accent' => '#b8860b',
                'accent_strong' => '#8a5a00',
                'bg' => 'linear-gradient(135deg, #fdf2f8 0%, #fef3c7 50%, #fdf2f8 100%)',
                'kind' => 'general',
            ],
            'wedding-islam' => [
                'label' => 'Pernikahan — Islam',
                'group' => 'Pernikahan',
                'accent' => '#0f766e',
                'accent_strong' => '#115e59',
                'bg' => 'linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 50%, #ecfdf5 100%)',
                'kind' => 'wedding',
            ],
            'wedding-kristen' => [
                'label' => 'Pernikahan — Kristen',
                'group' => 'Pernikahan',
                'accent' => '#1d4ed8',
                'accent_strong' => '#1e3a8a',
                'bg' => 'linear-gradient(135deg, #eff6ff 0%, #f8fafc 50%, #eff6ff 100%)',
                'kind' => 'wedding',
            ],
            'wedding-katolik' => [
                'label' => 'Pernikahan — Katolik',
                'group' => 'Pernikahan',
                'accent' => '#9d174d',
                'accent_strong' => '#701a3b',
                'bg' => 'linear-gradient(135deg, #fdf2f8 0%, #fbf7f0 50%, #fdf2f8 100%)',
                'kind' => 'wedding',
            ],
            'wedding-budha' => [
                'label' => 'Pernikahan — Buddha',
                'group' => 'Pernikahan',
                'accent' => '#b45309',
                'accent_strong' => '#92400e',
                'bg' => 'linear-gradient(135deg, #fffbeb 0%, #fff7ed 50%, #fffbeb 100%)',
                'kind' => 'wedding',
            ],
            'corporate' => [
                'label' => 'Acara Perusahaan / Formal',
                'group' => 'Acara & Pertemuan',
                'accent' => '#0e7490',
                'accent_strong' => '#155e75',
                'bg' => 'linear-gradient(135deg, #f0f9ff 0%, #f8fafc 50%, #eef2ff 100%)',
                'kind' => 'event',
            ],
            'tech' => [
                'label' => 'Tech Meetup / Konferensi',
                'group' => 'Acara & Pertemuan',
                'accent' => '#7c3aed',
                'accent_strong' => '#5b21b6',
                'bg' => 'linear-gradient(135deg, #f5f3ff 0%, #eef2ff 50%, #faf5ff 100%)',
                'kind' => 'event',
            ],
            'custom' => [
                'label' => 'Kustom (Unggah gambar / HTML sendiri)',
                'group' => 'Umum',
                'accent' => '#334155',
                'accent_strong' => '#1e293b',
                'bg' => 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #f8fafc 100%)',
                'kind' => 'custom',
            ],
        ];
    }

    /**
     * Default content values (used as fallbacks when a setting is empty).
     */
    public static function defaults(): array
    {
        return [
            'template'        => 'default',

            // Shared / general content
            'title'           => '',   // main heading; falls back to lang event_title
            'subtitle'        => '',   // tagline; falls back to lang tagline
            'quote'           => '',   // verse / opening quote
            'quote_source'    => '',   // e.g. "QS. Ar-Rum: 21"
            'event_date_text' => '',   // human friendly date, e.g. "Sabtu, 12 Juli 2026"
            'event_time_text' => '',   // e.g. "10.00 WIB - selesai"
            'venue_name'      => '',   // display name of venue
            'venue_address'   => '',   // full address
            'venue_maps'      => '',   // maps URL or search query

            // Wedding-specific
            'groom_name'      => '',
            'groom_full'      => '',
            'groom_parents'   => '',
            'bride_name'      => '',
            'bride_full'      => '',
            'bride_parents'   => '',
            'akad_text'       => '',   // ceremony (akad/pemberkatan) date-time
            'resepsi_text'    => '',   // reception date-time

            // Event / corporate-specific
            'host_org'        => '',   // hosting organisation
            'speaker'         => '',   // keynote / main speaker(s)
            'dress_code'      => '',
            'agenda'          => '[]',  // JSON: [{time, title, desc}]

            // Assets (paths on the public disk)
            'music'           => '',   // uploaded mp3 path
            'cover_image'     => '',   // entry-overlay cover image
            'content_image'   => '',   // secondary detail image
            'hero_photo'      => '',   // couple / event photo
            'gallery'         => '[]',  // JSON array of image paths

            // Custom template
            'custom_html'     => '',

            // Behaviour toggles
            'music_autoplay'  => '1',
            'show_default_cover' => '1',
        ];
    }

    /**
     * Return a single value with default fallback.
     */
    public static function get(string $key, $default = null)
    {
        $stored = self::store()[self::PREFIX . $key] ?? null;

        if ($stored === null || $stored === '') {
            return $default ?? (self::defaults()[$key] ?? null);
        }

        return $stored;
    }

    /**
     * Persist a single value.
     */
    public static function set(string $key, ?string $value): void
    {
        Setting::setValue(self::PREFIX . $key, $value);
        self::flush();
    }

    /**
     * Persist many values at once.
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value = json_encode(array_values($value));
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            self::set($key, $value === null ? null : (string) $value);
        }
    }

    /**
     * Full settings array (with defaults filled in) for form hydration
     * and view rendering.
     */
    public static function all(): array
    {
        $out = [];
        foreach (self::defaults() as $key => $default) {
            $out[$key] = self::get($key, $default);
        }

        return $out;
    }

    /**
     * The active template id (validated against the registry).
     */
    public static function template(): string
    {
        $t = self::get('template', 'default');

        return array_key_exists($t, self::templates()) ? $t : 'default';
    }

    /**
     * Palette + meta for the active (or given) template.
     */
    public static function themeFor(?string $template = null): array
    {
        $template ??= self::template();
        $templates = self::templates();

        return $templates[$template] ?? $templates['default'];
    }

    /**
     * Decode a JSON-array setting (agenda, gallery) into a PHP array.
     */
    public static function jsonArray(string $key): array
    {
        $raw = self::get($key, '[]');
        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Public URL for an uploaded asset path, or null when unset.
     */
    public static function assetUrl(string $key): ?string
    {
        return self::urlFor((string) self::get($key, ''));
    }

    /**
     * Public URL for a raw stored path (used for gallery items), or null.
     */
    public static function urlFor(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        // Already an absolute URL (allows linking an external asset).
        if (str_starts_with(strtolower($path), 'http')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Resolved background-music URL: uploaded track, else the bundled
     * default (storage/audio/background.mp3), else null.
     */
    public static function musicUrl(): ?string
    {
        $uploaded = self::assetUrl('music');
        if ($uploaded) {
            return $uploaded;
        }

        // Fall back to the conventional default file if present.
        if (Storage::disk('public')->exists('audio/background.mp3')) {
            return Storage::disk('public')->url('audio/background.mp3');
        }

        return null;
    }

    /**
     * Whether a value should be treated as "on".
     */
    public static function truthy(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default ? '1' : '0');

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'ya', 'on'], true);
    }
}
