<?php

namespace App\Livewire;

use App\Models\ContactPerson;
use App\Models\Konfirmasi;
use App\Models\Peserta;
use App\Models\Setting;
use App\Models\Tamu;
use App\Support\LandingConfig;
use Carbon\Carbon;
use Livewire\Component;

class Undangan extends Component
{
    public Tamu $tamu;
    public ?Konfirmasi $konfirmasiData = null;

    // Form fields
    public ?int $jumlah_hadir = 1;
    public bool $butuh_antar_jemput = false;
    public ?string $tanggal_datang = null;
    public ?string $tanggal_pulang = null;
    public bool $butuh_penginapan = false;
    public ?int $jumlah_menginap = null;

    // Peserta fields (array of attendees)
    public array $pesertaList = [];

    // State
    public bool $showForm = false;
    public bool $showDateForm = false;
    public bool $entered = false;
    public string $batasKonfirmasi = '';
    public bool $isExpired = false;

    // Date constraints
    public ?string $tanggalAcara = null;
    public ?string $minTanggalDatang = null;
    public ?string $maxTanggalDatang = null;
    public ?string $minTanggalPulang = null;
    public ?string $maxTanggalPulang = null;

    // Computed flags
    public bool $showAntarJemput = false;
    public bool $showPenginapanQuestion = false;
    public bool $showDates = false;
    public bool $publishLokasiMenginap = false;

    /**
     * Runs on every request (fresh load + hydration).
     * If konfirmasiData was deleted externally between requests, reset to default state.
     */
    public function boot(): void
    {
        try {
            if ($this->konfirmasiData !== null && !$this->konfirmasiData->exists) {
                $this->konfirmasiData    = null;
                $this->jumlah_hadir      = 1;
                $this->butuh_antar_jemput = false;
                $this->tanggal_datang    = null;
                $this->tanggal_pulang    = null;
                $this->butuh_penginapan  = false;
                $this->jumlah_menginap   = null;
                $this->pesertaList       = [];
            }
        } catch (\Throwable) {
            $this->konfirmasiData    = null;
            $this->jumlah_hadir      = 1;
            $this->butuh_antar_jemput = false;
            $this->tanggal_datang    = null;
            $this->tanggal_pulang    = null;
            $this->butuh_penginapan  = false;
            $this->jumlah_menginap   = null;
            $this->pesertaList       = [];
        }
    }

    public function mount(string $kode): void
    {
        $this->tamu = Tamu::where('kode_unik', $kode)->firstOrFail();

        // Set locale based on tamu language
        app()->setLocale($this->tamu->bahasa === 'EN' ? 'en' : 'id');

        // Load existing confirmation with relations
        try {
            $this->konfirmasiData = $this->tamu->konfirmasi?->load(['sopir', 'penginapan', 'pesertas']);
        } catch (\Throwable) {
            $this->konfirmasiData = null;
        }

        if ($this->konfirmasiData) {
            $this->jumlah_hadir = $this->konfirmasiData->jumlah_hadir;
            $this->butuh_antar_jemput = (bool) $this->konfirmasiData->butuh_antar_jemput;
            $this->tanggal_datang = $this->konfirmasiData->tanggal_datang?->format('Y-m-d');
            $this->tanggal_pulang = $this->konfirmasiData->tanggal_pulang?->format('Y-m-d');
            $this->butuh_penginapan = (bool) $this->konfirmasiData->butuh_penginapan;
            $this->jumlah_menginap = $this->konfirmasiData->jumlah_menginap;

            // Load peserta list
            $this->pesertaList = $this->konfirmasiData->pesertas->map(fn ($p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'jenis_kelamin' => $p->jenis_kelamin,
                'nama_pasangan' => $p->nama_pasangan ?? '',
            ])->toArray();
        } else {
            // Default attendees to the number invited
            $this->jumlah_hadir = max(1, (int) $this->tamu->jumlah_orang);
            // All guests: transport auto-yes only for VVIP or dapat_transport luar_kota
            $this->butuh_antar_jemput = $this->tamu->luar_kota && $this->tamu->dapat_transport;
            // All guests: penginapan auto-yes if assigned
            $this->butuh_penginapan = $this->tamu->penginapan;
        }

        // Penginapan is auto-yes for any guest with an assigned penginapan
        if ($this->tamu->penginapan) {
            $this->butuh_penginapan = true;
        }

        // Default jumlah_menginap to jumlah_hadir when penginapan is set and no existing value
        if ($this->butuh_penginapan && $this->jumlah_menginap === null) {
            $this->jumlah_menginap = $this->jumlah_hadir;
        }

        // Ensure pesertaList matches the target count
        $this->syncPesertaList();

        // Pre-fill first peserta with tamu nama if no existing confirmation
        if (!$this->konfirmasiData && !empty($this->pesertaList) && empty($this->pesertaList[0]['nama'])) {
            $this->pesertaList[0]['nama'] = $this->tamu->nama;
        }

        // Set visibility flags
        $this->updateVisibilityFlags();

        // Check deadline
        $this->batasKonfirmasi = Setting::getValue('tgl_terakhir_konfirmasi', config('undangan.confirmation_until'));
        $this->isExpired = Carbon::parse($this->batasKonfirmasi)->endOfDay()->isPast();
        $this->publishLokasiMenginap = $this->isSettingTruthy(
            Setting::getValue('publish_lokasi_menginap', '0')
        );

        // Date constraints for arrival/departure
        $this->tanggalAcara = Setting::getValue('tanggal_acara', config('undangan.event_date'));
        $eventDate = Carbon::parse($this->tanggalAcara);
        // Arrival must be on or before the event date; departure on or after it.
        $this->minTanggalDatang = $eventDate->copy()->subDays(15)->format('Y-m-d');
        $this->maxTanggalDatang = $eventDate->format('Y-m-d');
        $this->minTanggalPulang = $eventDate->format('Y-m-d');
        $this->maxTanggalPulang = $eventDate->copy()->addDays(15)->format('Y-m-d');

        // When the entry cover is disabled, open the invitation directly.
        if (! LandingConfig::truthy('show_default_cover', true)) {
            $this->entered = true;
        }
    }

    /**
     * Determine visibility flags for transport, penginapan, and dates sections.
     */
    private function updateVisibilityFlags(): void
    {
        $isVvip = $this->tamu->jenis === 'VVIP';

        // VVIP: transport is auto-yes, question is hidden
        // VIP/umum: show question only if dapat_transport=true and luar_kota
        $this->showAntarJemput = !$isVvip
            && $this->tamu->luar_kota
            && $this->tamu->dapat_transport;

        // All guests: penginapan is auto-yes if assigned, question always hidden
        $this->showPenginapanQuestion = false;

        // Show date fields when transport or penginapan is confirmed (for luar_kota guests)
        $this->showDates = $this->tamu->luar_kota
            && ($this->butuh_antar_jemput || $this->butuh_penginapan);
    }

    /**
     * Target number of peserta entries.
     * For guests with penginapan the list collects those who will stay overnight,
     * so it follows jumlah_menginap; otherwise it follows jumlah_hadir.
     */
    private function pesertaTargetCount(): int
    {
        if ($this->tamu->penginapan) {
            return max(0, (int) ($this->jumlah_menginap ?? 0));
        }

        return max(1, (int) ($this->jumlah_hadir ?? 1));
    }

    /**
     * Keep pesertaList in sync with the target count.
     */
    private function syncPesertaList(): void
    {
        $count = $this->pesertaTargetCount();
        $current = count($this->pesertaList);

        if ($current < $count) {
            $isEn = app()->getLocale() === 'en';
            for ($i = $current; $i < $count; $i++) {
                $num = $i + 1;
                $defaultNama = $isEn ? "Guest #{$num}" : "Tamu Undangan #{$num}";
                $this->pesertaList[] = [
                    'id' => null,
                    'nama' => $defaultNama,
                    'jenis_kelamin' => null,
                    'nama_pasangan' => '',
                ];
            }
        } elseif ($current > $count) {
            $this->pesertaList = array_slice($this->pesertaList, 0, $count);
        }
    }

    public function updatedJumlahHadir(): void
    {
        // Clamp jumlah_menginap to new jumlah_hadir first (skip while field is being cleared)
        if ($this->jumlah_hadir !== null && $this->jumlah_menginap !== null && $this->jumlah_menginap > $this->jumlah_hadir) {
            $this->jumlah_menginap = $this->jumlah_hadir;
        }
        // Then re-sync peserta list (count follows jumlah_menginap for penginapan guests)
        $this->syncPesertaList();
    }

    public function updatedJumlahMenginap(): void
    {
        // Peserta list follows the number staying overnight
        $this->syncPesertaList();
    }

    public function updatedButuhAntarJemput(): void
    {
        $this->updateVisibilityFlags();
    }

    public function updatedButuhPenginapan(): void
    {
        $this->updateVisibilityFlags();
        // Default jumlah_menginap to jumlah_hadir when penginapan is chosen
        if ($this->butuh_penginapan && $this->jumlah_menginap === null) {
            $this->jumlah_menginap = $this->jumlah_hadir;
        }
        if (!$this->butuh_penginapan) {
            $this->jumlah_menginap = null;
        }
    }

    public function enter(): void
    {
        $this->entered = true;
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        $this->showDateForm = false;
    }

    public function toggleDateForm(): void
    {
        $this->showDateForm = !$this->showDateForm;
        $this->showForm = false;
    }

    /**
     * Build change log entries comparing old vs new data.
     */
    private function buildChangeLog(array $newData): array
    {
        if (!$this->konfirmasiData) {
            return [];
        }

        $changes = [];
        $labels = [
            'jumlah_hadir' => __('undangan.jumlah_hadir'),
            'butuh_antar_jemput' => __('undangan.butuh_antar_jemput'),
            'tanggal_datang' => __('undangan.tanggal_datang'),
            'tanggal_pulang' => __('undangan.tanggal_pulang'),
            'butuh_penginapan' => __('undangan.butuh_penginapan'),
        ];

        $fieldsToTrack = ['jumlah_hadir', 'butuh_antar_jemput', 'tanggal_datang', 'tanggal_pulang', 'butuh_penginapan'];

        foreach ($fieldsToTrack as $field) {
            $oldVal = $this->konfirmasiData->getRawOriginal($field);
            $newVal = $newData[$field] ?? null;

            // Normalize for comparison
            if ($oldVal instanceof \Carbon\Carbon) {
                $oldVal = $oldVal->format('Y-m-d');
            }
            if (is_bool($oldVal)) {
                $oldVal = $oldVal ? '1' : '0';
            }
            if (is_bool($newVal)) {
                $newVal = $newVal ? '1' : '0';
            }

            $oldStr = (string) ($oldVal ?? '-');
            $newStr = (string) ($newVal ?? '-');

            if ($oldStr !== $newStr) {
                // Human-readable values
                $oldDisplay = $this->formatValue($field, $oldVal);
                $newDisplay = $this->formatValue($field, $newVal);

                $changes[] = [
                    'field' => $labels[$field] ?? $field,
                    'dari' => $oldDisplay,
                    'ke' => $newDisplay,
                    'waktu' => now()->toIso8601String(),
                    'acknowledged' => false,
                ];
            }
        }

        return $changes;
    }

    private function formatValue(string $field, $value): string
    {
        if ($value === null || $value === '' || $value === '-') {
            return '-';
        }

        if (in_array($field, ['butuh_antar_jemput', 'butuh_penginapan'])) {
            return ((int) $value) ? __('undangan.ya') : __('undangan.tidak');
        }

        if (in_array($field, ['tanggal_datang', 'tanggal_pulang'])) {
            try {
                return Carbon::parse($value)->format('d M Y');
            } catch (\Exception $e) {
                return (string) $value;
            }
        }

        return (string) $value;
    }

    public function simpanKonfirmasi(): void
    {
        if ($this->isExpired) {
            session()->flash('error', __('undangan.batas_konfirmasi_lewat'));
            return;
        }

        $hasPenginapan = $this->tamu->penginapan;
        $isVvip = $this->tamu->jenis === 'VVIP';
        // Penginapan is auto-yes for any guest with an assigned penginapan
        $penginapanActive = $hasPenginapan || ($this->showPenginapanQuestion && $this->butuh_penginapan);

        $rules = [
            'jumlah_hadir' => ['required', 'integer', 'min:1', 'max:' . $this->tamu->jumlah_orang],
            'tanggal_datang' => ['nullable', 'date', 'after_or_equal:' . $this->minTanggalDatang, 'before_or_equal:' . $this->maxTanggalDatang],
            'tanggal_pulang' => ['nullable', 'date', 'after_or_equal:' . $this->minTanggalPulang, 'before_or_equal:' . $this->maxTanggalPulang],
            'pesertaList.*.nama' => ['nullable', 'string', 'max:255'],
            'pesertaList.*.jenis_kelamin' => ['nullable', 'in:L,P'],
            'pesertaList.*.nama_pasangan' => ['nullable', 'string', 'max:255'],
        ];

        if ($penginapanActive) {
            // 0 diperbolehkan: peserta tidak mengambil penginapan yang disediakan
            $rules['jumlah_menginap'] = ['required', 'integer', 'min:0', 'max:' . $this->jumlah_hadir];
        }

        $this->validate($rules, [
            'jumlah_hadir.required' => __('undangan.jumlah_hadir_invalid'),
            'jumlah_hadir.integer'  => __('undangan.jumlah_hadir_invalid'),
            'jumlah_hadir.min'      => __('undangan.jumlah_hadir_invalid'),
            'jumlah_menginap.required' => __('undangan.jumlah_menginap_required'),
            'jumlah_menginap.max'      => __('undangan.jumlah_menginap_max', ['max' => $this->jumlah_hadir]),
        ]);

        // Conditional: if nama is filled and tamu has penginapan, jenis_kelamin is required
        $hasGenderError = false;
        if ($this->tamu->penginapan) {
            foreach ($this->pesertaList as $index => $peserta) {
                if (!empty($peserta['nama']) && empty($peserta['jenis_kelamin'])) {
                    $this->addError("pesertaList.$index.jenis_kelamin", __('undangan.jenis_kelamin_required'));
                    $hasGenderError = true;
                }
            }
        }
        if ($hasGenderError) {
            return;
        }

        // Determine if dates should be saved
        $saveDates = $this->showDates;

        $data = [
            'tamu_id' => $this->tamu->id,
            'jumlah_hadir' => $this->jumlah_hadir,
            'butuh_antar_jemput' => $isVvip
                ? ($this->tamu->luar_kota && $this->tamu->dapat_transport ? true : null)
                : ($this->showAntarJemput ? $this->butuh_antar_jemput : null),
            'tanggal_datang' => $saveDates ? $this->tanggal_datang : null,
            'tanggal_pulang' => $saveDates ? $this->tanggal_pulang : null,
            'butuh_penginapan' => $hasPenginapan
                ? true
                : ($this->showPenginapanQuestion ? $this->butuh_penginapan : null),
            'jumlah_menginap' => $penginapanActive ? $this->jumlah_menginap : null,
            'confirmed_at' => now(),
        ];

        $isUpdate = $this->konfirmasiData !== null;

        // Build change log for updates
        if ($isUpdate) {
            $newChanges = $this->buildChangeLog($data);
            if (count($newChanges) > 0) {
                $existingLog = $this->konfirmasiData->catatan_perubahan ?? [];
                $data['catatan_perubahan'] = array_merge($existingLog, $newChanges);
                // Reset acknowledged when there are changes
                $data['acknowledged'] = false;
            }
        }

        $this->konfirmasiData = Konfirmasi::updateOrCreate(
            ['tamu_id' => $this->tamu->id],
            $data
        );

        // Save peserta
        $this->savePeserta();

        $this->konfirmasiData->refresh();
        $this->konfirmasiData->load(['sopir', 'penginapan', 'pesertas']);
        $this->showForm = false;

        session()->flash('success', $isUpdate ? __('undangan.konfirmasi_updated') : __('undangan.konfirmasi_success'));
    }

    /**
     * Record that the guest will NOT attend (jumlah_hadir = 0).
     */
    public function tidakHadir(): void
    {
        if ($this->isExpired) {
            session()->flash('error', __('undangan.batas_konfirmasi_lewat'));
            return;
        }

        $data = [
            'tamu_id'            => $this->tamu->id,
            'jumlah_hadir'       => 0,
            'butuh_antar_jemput' => null,
            'tanggal_datang'     => null,
            'tanggal_pulang'     => null,
            'butuh_penginapan'   => null,
            'jumlah_menginap'    => null,
            'confirmed_at'       => now(),
        ];

        $isUpdate = $this->konfirmasiData !== null;

        // Build change log for updates
        if ($isUpdate) {
            $newChanges = $this->buildChangeLog($data);
            if (count($newChanges) > 0) {
                $existingLog = $this->konfirmasiData->catatan_perubahan ?? [];
                $data['catatan_perubahan'] = array_merge($existingLog, $newChanges);
                $data['acknowledged'] = false;
            }
        }

        $this->konfirmasiData = Konfirmasi::updateOrCreate(
            ['tamu_id' => $this->tamu->id],
            $data
        );

        // Not attending: remove any previously recorded peserta
        $this->konfirmasiData->pesertas()->delete();

        // Reset local form state
        $this->jumlah_hadir       = 0;
        $this->butuh_antar_jemput = false;
        $this->tanggal_datang     = null;
        $this->tanggal_pulang     = null;
        $this->butuh_penginapan   = false;
        $this->jumlah_menginap    = null;
        $this->pesertaList        = [];

        $this->konfirmasiData->refresh();
        $this->konfirmasiData->load(['sopir', 'penginapan', 'pesertas']);
        $this->showForm = false;

        session()->flash('success', __('undangan.tidak_hadir_success'));
    }

    /**
     * Save peserta list to database.
     */
    private function savePeserta(): void
    {
        if (!$this->konfirmasiData) {
            return;
        }

        // Delete existing and recreate
        $this->konfirmasiData->pesertas()->delete();

        foreach ($this->pesertaList as $p) {
            if (!empty($p['nama'])) {
                Peserta::create([
                    'konfirmasi_id' => $this->konfirmasiData->id,
                    'nama' => $p['nama'],
                    'jenis_kelamin' => $p['jenis_kelamin'] ?: null,
                    'nama_pasangan' => !empty($p['nama_pasangan']) ? $p['nama_pasangan'] : null,
                ]);
            }
        }
    }

    /**
     * Save only date changes (for post-deadline).
     */
    public function simpanJadwal(): void
    {
        if (!$this->konfirmasiData || !$this->tamu->luar_kota) {
            return;
        }

        $this->validate([
            'tanggal_datang' => ['nullable', 'date', 'after_or_equal:' . $this->minTanggalDatang, 'before_or_equal:' . $this->maxTanggalDatang],
            'tanggal_pulang' => ['nullable', 'date', 'after_or_equal:' . $this->minTanggalPulang, 'before_or_equal:' . $this->maxTanggalPulang],
        ]);

        $saveDates = ($this->showAntarJemput && $this->butuh_antar_jemput) || $this->butuh_penginapan;

        $newData = [
            'tanggal_datang' => $saveDates ? $this->tanggal_datang : null,
            'tanggal_pulang' => $saveDates ? $this->tanggal_pulang : null,
        ];

        // Build change log
        $newChanges = $this->buildChangeLog($newData + [
            'jumlah_hadir' => $this->konfirmasiData->jumlah_hadir,
            'butuh_antar_jemput' => $this->konfirmasiData->butuh_antar_jemput,
            'butuh_penginapan' => $this->konfirmasiData->butuh_penginapan,
        ]);

        $updateData = [
            'tanggal_datang' => $newData['tanggal_datang'],
            'tanggal_pulang' => $newData['tanggal_pulang'],
        ];

        if (count($newChanges) > 0) {
            $existingLog = $this->konfirmasiData->catatan_perubahan ?? [];
            $updateData['catatan_perubahan'] = array_merge($existingLog, $newChanges);
            $updateData['acknowledged'] = false;
        }

        $this->konfirmasiData->update($updateData);
        $this->konfirmasiData->refresh();
        $this->konfirmasiData->load(['sopir', 'penginapan', 'pesertas']);
        $this->showDateForm = false;

        session()->flash('success', __('undangan.jadwal_updated'));
    }

    public function render()
    {
        app()->setLocale($this->tamu->bahasa === 'EN' ? 'en' : 'id');
        if ($this->tamu->jenis === 'VVIP') {
            $contactPersons = ContactPerson::where('vvip', true)->get();    
        } else {
            $contactPersons = ContactPerson::where('vvip', false)->get();
        }

        $locale = $this->tamu->bahasa === 'EN' ? 'en' : 'id';
        $ogImageFile = $locale === 'en' ? config('undangan.og_image_en') : config('undangan.og_image_id');
        $ogImage = $ogImageFile ? asset('storage/' . $ogImageFile) : asset('favicon.svg');
        $ogDescription = $locale === 'en'
            ? 'Dear ' . $this->tamu->nama . ' – you are cordially invited to ' . __('undangan.event_title') . '.'
            : 'Kepada Yth. ' . $this->tamu->nama . ' – Anda diundang dalam ' . __('undangan.event_title') . '.';

        // Landing template + branding (all admin-configurable via settings).
        $template = LandingConfig::template();
        $landingTheme = LandingConfig::themeFor($template);
        $galleryImages = collect(LandingConfig::jsonArray('gallery'))
            ->map(fn ($p) => LandingConfig::urlFor((string) $p))
            ->filter()
            ->values()
            ->all();

        return view('livewire.undangan', [
            'contactPersons' => $contactPersons,
            'template'       => $template,
            'theme'          => $landingTheme,
            'landing'        => LandingConfig::all(),
            'musicUrl'       => LandingConfig::musicUrl(),
            'musicAutoplay'  => LandingConfig::truthy('music_autoplay', true),
            'coverImage'     => LandingConfig::assetUrl('cover_image'),
            'contentImage'   => LandingConfig::assetUrl('content_image'),
            'heroPhoto'      => LandingConfig::assetUrl('hero_photo'),
            'galleryImages'  => $galleryImages,
            'agenda'         => LandingConfig::jsonArray('agenda'),
        ])->layout('layouts.undangan', [
            'ogTitle'       => __('undangan.event_title'),
            'ogDescription' => $ogDescription,
            'ogImage'       => $ogImage,
        ]);
    }

    private function isSettingTruthy(?string $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'ya', 'on'], true);
    }
}
