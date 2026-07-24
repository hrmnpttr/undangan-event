<?php

namespace App\Filament\Pages;

use App\Support\LandingConfig;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * DesainUndangan (Landing / Invitation Designer)
 * ----------------------------------------------
 * EN: Admin page to pick an invitation template, upload background music
 *     (mp3 only) and images, and fill in the content that appears on the
 *     public landing page — all stored in the `settings` table so any kind
 *     of event can be configured without touching code.
 *
 * ID: Halaman admin untuk memilih template undangan, mengunggah musik latar
 *     (khusus mp3) dan gambar, serta mengisi konten yang tampil di halaman
 *     landing publik — semuanya disimpan di tabel `settings` sehingga acara
 *     apa pun bisa dikonfigurasi tanpa mengubah kode.
 */
class DesainUndangan extends Page
{
    use WithFileUploads;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Desain Undangan';

    protected static ?string $title = 'Desain Undangan';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.desain-undangan';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->isPanitia();
    }

    // ---- Form state -------------------------------------------------------

    public string $template = 'default';

    /** Plain text / textarea fields (keys map 1:1 to LandingConfig). */
    public array $fields = [];

    /** Agenda rows: each ['time' => .., 'title' => .., 'desc' => ..]. */
    public array $agenda = [];

    public bool $music_autoplay = true;

    public bool $show_default_cover = true;

    // ---- Uploads ----------------------------------------------------------

    public $musicUpload = null;

    public $coverUpload = null;

    public $contentUpload = null;

    public $heroPhotoUpload = null;

    public $galleryUpload = [];

    /** The text keys managed by the form. */
    private const TEXT_KEYS = [
        'title', 'subtitle', 'quote', 'quote_source',
        'event_date_text', 'event_time_text',
        'venue_name', 'venue_address', 'venue_maps',
        'groom_name', 'groom_full', 'groom_parents',
        'bride_name', 'bride_full', 'bride_parents',
        'akad_text', 'resepsi_text',
        'host_org', 'speaker', 'dress_code',
        'custom_html',
    ];

    public function mount(): void
    {
        $all = LandingConfig::all();

        $this->template = LandingConfig::template();

        foreach (self::TEXT_KEYS as $key) {
            $this->fields[$key] = (string) ($all[$key] ?? '');
        }

        $this->agenda = LandingConfig::jsonArray('agenda');
        $this->music_autoplay = LandingConfig::truthy('music_autoplay', true);
        $this->show_default_cover = LandingConfig::truthy('show_default_cover', true);
    }

    /** Templates grouped for the selector. */
    public function templateGroups(): array
    {
        $groups = [];
        foreach (LandingConfig::templates() as $id => $meta) {
            $groups[$meta['group']][$id] = $meta['label'];
        }

        return $groups;
    }

    /** Currently selected template kind (wedding / event / custom / general). */
    public function templateKind(): string
    {
        return LandingConfig::themeFor($this->template)['kind'] ?? 'general';
    }

    public function addAgenda(): void
    {
        $this->agenda[] = ['time' => '', 'title' => '', 'desc' => ''];
    }

    public function removeAgenda(int $index): void
    {
        unset($this->agenda[$index]);
        $this->agenda = array_values($this->agenda);
    }

    // ---- Asset actions ----------------------------------------------------

    public function clearAsset(string $key): void
    {
        $allowed = ['music', 'cover_image', 'content_image', 'hero_photo'];
        if (! in_array($key, $allowed, true)) {
            return;
        }

        LandingConfig::set($key, null);

        Notification::make()
            ->title('Berkas dihapus')
            ->success()
            ->send();
    }

    public function removeGalleryImage(int $index): void
    {
        $gallery = LandingConfig::jsonArray('gallery');
        unset($gallery[$index]);
        LandingConfig::setMany(['gallery' => array_values($gallery)]);

        Notification::make()->title('Gambar galeri dihapus')->success()->send();
    }

    // ---- Save -------------------------------------------------------------

    public function save(): void
    {
        $this->validate([
            'template' => 'required|string',
            'musicUpload' => 'nullable|file|mimes:mp3|max:12288',
            'coverUpload' => 'nullable|image|max:8192',
            'contentUpload' => 'nullable|image|max:8192',
            'heroPhotoUpload' => 'nullable|image|max:8192',
            'galleryUpload.*' => 'nullable|image|max:8192',
        ], [
            'musicUpload.mimes' => 'Musik latar harus berupa berkas .mp3.',
        ]);

        $values = [
            'template' => array_key_exists($this->template, LandingConfig::templates())
                ? $this->template
                : 'default',
            'music_autoplay' => $this->music_autoplay,
            'show_default_cover' => $this->show_default_cover,
            'agenda' => $this->cleanAgenda(),
        ];

        foreach (self::TEXT_KEYS as $key) {
            $values[$key] = trim((string) ($this->fields[$key] ?? ''));
        }

        // Handle uploads → public disk.
        if ($this->musicUpload instanceof TemporaryUploadedFile) {
            $values['music'] = $this->storeUpload($this->musicUpload, 'audio', 'mp3');
        }
        if ($this->coverUpload instanceof TemporaryUploadedFile) {
            $values['cover_image'] = $this->storeUpload($this->coverUpload, 'undangan');
        }
        if ($this->contentUpload instanceof TemporaryUploadedFile) {
            $values['content_image'] = $this->storeUpload($this->contentUpload, 'undangan');
        }
        if ($this->heroPhotoUpload instanceof TemporaryUploadedFile) {
            $values['hero_photo'] = $this->storeUpload($this->heroPhotoUpload, 'undangan');
        }

        // Append gallery images.
        if (! empty($this->galleryUpload)) {
            $gallery = LandingConfig::jsonArray('gallery');
            foreach ($this->galleryUpload as $img) {
                if ($img instanceof TemporaryUploadedFile) {
                    $gallery[] = $this->storeUpload($img, 'undangan');
                }
            }
            $values['gallery'] = $gallery;
        }

        LandingConfig::setMany($values);

        // Reset upload inputs.
        $this->reset(['musicUpload', 'coverUpload', 'contentUpload', 'heroPhotoUpload', 'galleryUpload']);

        Notification::make()
            ->title('Desain undangan tersimpan')
            ->body('Perubahan langsung tampil di halaman undangan.')
            ->success()
            ->send();
    }

    private function storeUpload(TemporaryUploadedFile $file, string $dir, ?string $forceExt = null): string
    {
        $ext = $forceExt ?: $file->getClientOriginalExtension();
        $name = Str::random(24) . '.' . strtolower($ext);

        return $file->storeAs($dir, $name, 'public');
    }

    private function cleanAgenda(): array
    {
        return collect($this->agenda)
            ->map(fn ($row) => [
                'time' => trim((string) ($row['time'] ?? '')),
                'title' => trim((string) ($row['title'] ?? '')),
                'desc' => trim((string) ($row['desc'] ?? '')),
            ])
            ->filter(fn ($row) => $row['title'] !== '' || $row['time'] !== '')
            ->values()
            ->all();
    }

    // ---- View helpers -----------------------------------------------------

    public function currentAsset(string $key): ?string
    {
        return LandingConfig::assetUrl($key);
    }

    /** @return array<int, array{index:int, url:string}> */
    public function galleryImages(): array
    {
        return collect(LandingConfig::jsonArray('gallery'))
            ->map(fn ($path, $i) => ['index' => $i, 'url' => LandingConfig::urlFor((string) $path)])
            ->filter(fn ($item) => $item['url'] !== null)
            ->values()
            ->all();
    }
}
