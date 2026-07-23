<?php

namespace App\Filament\Resources;

use App\Models\Kehadiran;
use App\Models\Penginapan;
use App\Models\Setting;
use App\Models\Tamu;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class TamuResource extends Resource
{
    protected static ?string $model = Tamu::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Master Tamu';

    protected static ?string $modelLabel = 'Tamu';

    protected static ?string $pluralModelLabel = 'Tamu';

    protected static ?int $navigationSort = 1;

    private static function me(): ?\App\Models\User
    {
        $u = Auth::user();
        return $u instanceof \App\Models\User ? $u : null;
    }

    private static function allowedRoles(): array
    {
        return ['panitia', 'scan', 'penginapan', 'panitia_penginapan'];
    }

    public static function canAccess(): bool
    {
        return in_array(self::me()?->role, self::allowedRoles(), true);
    }

    public static function canCreate(): bool
    {
        return self::me()?->isPanitia() ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return self::me()?->isPanitia() ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return self::me()?->isPanitia() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3),//contoh nya kepala, dibagian, dll
                TextInput::make('nomor_wa')
                    ->label('Nomor WhatsApp')
                    ->placeholder('08xxx / 628xxx / +628xxx')
                    ->maxLength(20),
                TextInput::make('jumlah_orang')
                    ->label('Jumlah Orang')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),
                Select::make('bahasa')
                    ->options([
                        'ID' => 'Indonesia',
                        'EN' => 'English',
                    ])
                    ->default('ID')
                    ->required(),
                Toggle::make('luar_kota')
                    ->label('Luar Kota')
                    ->default(false),
                Toggle::make('dapat_transport')
                    ->label('Dapat Transport')
                    ->helperText('Aktifkan jika tamu ini mendapat layanan antar jemput (VIP/VVIP luar kota)')
                    ->default(false),
                Select::make('penginapan_id')
                    ->label('Penginapan')
                    ->options(fn () => Penginapan::orderBy('nama')
                        ->get()
                        ->mapWithKeys(fn ($p) => [
                            $p->id => $p->nama . ($p->kode ? ' [' . $p->kode . ']' : ''),
                        ]))
                    ->searchable()
                    ->placeholder('— Tidak ada penginapan —')
                    ->nullable()
                    ->live(),
                Select::make('tipe_kamar')
                    ->label('Tipe Kamar')
                    ->options([
                        'double' => 'Double (kamar sendiri / pasutri)',
                        'twin'   => 'Twin',
                    ])
                    ->placeholder('— Otomatis (berdasarkan data peserta) —')
                    ->nullable()
                    ->visible(fn ($get) => filled($get('penginapan_id'))),
                Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->placeholder('— Otomatis dari nama —')
                    ->helperText('Otomatis terdeteksi dari prefix nama (Romo/RP/RD/Fr/Br → L, Sr/Suster → P). Bisa diubah manual.')
                    ->nullable()
                    ->visible(fn ($get) => filled($get('penginapan_id'))),
                Toggle::make('tipe')
                    ->label('Online')
                    ->helperText('Off = Offline/Cetak, On = Online')
                    ->default(false),
                Select::make('jenis')
                    ->options([
                        'umum' => 'Umum',
                        'VIP' => 'VIP',
                        'VVIP' => 'VVIP',
                    ])
                    ->default('umum')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = self::me();
                if ($user?->role === 'penginapan' && $user->penginapan_id) {
                    $query->where('penginapan_id', $user->penginapan_id);
                }
            })
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Tamu $record) => match ($record->jenis) {
                        'VVIP' => '🔴 VVIP',
                        'VIP' => '🟡 VIP',
                        default => null,
                    })
                    ->color(fn (Tamu $record) => match ($record->jenis) {
                        'VVIP' => 'danger',
                        'VIP' => 'warning',
                        default => null,
                    }),
                TextColumn::make('jumlah_orang')
                    ->label('Jml Orang')
                    ->sortable(),
                TextColumn::make('bahasa')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ID' => 'info',
                        'EN' => 'success',
                    }),
                IconColumn::make('luar_kota')
                    ->label('Luar Kota')
                    ->boolean(),
                ToggleColumn::make('dapat_transport')
                    ->label('Transport'),
                SelectColumn::make('penginapan_id')
                    ->label('Penginapan')
                    ->options(fn () => ['' => '— Tidak ada —'] + Penginapan::orderBy('nama')
                        ->get()
                        ->mapWithKeys(fn ($p) => [
                            $p->id => $p->nama . ($p->kode ? ' [' . $p->kode . ']' : ''),
                        ])->toArray())
                    ->placeholder('— Tidak ada —'),
                SelectColumn::make('tipe_kamar')
                    ->label('Kamar')
                    ->options([
                        ''       => '— Otomatis —',
                        'double' => 'Double',
                        'twin'   => 'Twin',
                    ])
                    ->placeholder('— Otomatis —')
                    ->toggleable(isToggledHiddenByDefault: true),
                SelectColumn::make('jenis_kelamin')
                    ->label('L/P')
                    ->options([
                        ''  => '—',
                        'L' => 'L',
                        'P' => 'P',
                    ])
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('tipe')
                    ->label('Online')
                    ->boolean(),
                TextColumn::make('jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VVIP' => 'danger',
                        'VIP' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('nomor_wa')
                    ->label('No. WA')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('wa_terkirim_at')
                    ->label('WA Terkirim')
                    ->state(fn (Tamu $record) => $record->wa_terkirim_at !== null)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (Tamu $record) => $record->wa_terkirim_at
                        ? 'Dikirim: ' . $record->wa_terkirim_at->format('d/m/Y H:i')
                        : 'Belum dikirim'),
                TextColumn::make('kode_unik')
                    ->label('Kode')
                    ->copyable()
                    ->searchable()
                    ->url(fn (Tamu $record) => $record->getQrUrl())
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn (string $state) => rtrim(config('undangan.invite_base_url') ?: config('app.url'), '/') . '/i/' . $state),
                IconColumn::make('konfirmasi_status')
                    ->label('Konfirmasi')
                    ->state(fn (Tamu $record) => $record->konfirmasi()->exists())
                    ->boolean(),
                IconColumn::make('hadir_status')
                    ->label('Hadir')
                    ->state(fn (Tamu $record) => $record->kehadirans()->exists())
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->deselectAllRecordsWhenFiltered(false)
            ->filters([
                SelectFilter::make('jenis')
                    ->options([
                        'umum' => 'Umum',
                        'VIP' => 'VIP',
                        'VVIP' => 'VVIP',
                    ]),
                TernaryFilter::make('tipe')
                    ->label('Tipe')
                    ->trueLabel('Online')
                    ->falseLabel('Offline/Cetak'),
                TernaryFilter::make('luar_kota')
                    ->label('Luar Kota'),
                TernaryFilter::make('dapat_transport')
                    ->label('Transport'),
                \Filament\Tables\Filters\Filter::make('penginapan')
                    ->label('Ada Penginapan')
                    ->query(fn ($query) => $query->whereNotNull('penginapan_id'))
                    ->toggle(),
                SelectFilter::make('penginapan_id')
                    ->label('Penginapan')
                    ->options(fn () => Penginapan::orderBy('nama')->pluck('nama', 'id'))
                    ->searchable(),
                SelectFilter::make('konfirmasi')
                    ->label('Status Konfirmasi')
                    ->options([
                        'sudah' => 'Sudah Konfirmasi',
                        'belum' => 'Belum Konfirmasi',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'sudah') {
                            $query->whereHas('konfirmasi');
                        } elseif ($data['value'] === 'belum') {
                            $query->whereDoesntHave('konfirmasi');
                        }
                    }),
                SelectFilter::make('kehadiran')
                    ->label('Status Kehadiran')
                    ->options([
                        'hadir' => 'Sudah Hadir',
                        'belum' => 'Belum Hadir',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'hadir') {
                            $query->whereHas('kehadirans');
                        } elseif ($data['value'] === 'belum') {
                            $query->whereDoesntHave('kehadirans');
                        }
                    }),
                TernaryFilter::make('wa_terkirim_at')
                    ->label('WA Terkirim')
                    ->nullable()
                    ->trueLabel('Sudah Dikirim')
                    ->falseLabel('Belum Dikirim'),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => self::me()?->isPanitia() ?? false),
                Action::make('tandai_hadir')
                    ->label('Hadir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Kehadiran')
                    ->modalDescription(fn (Tamu $record) => 'Tandai ' . $record->nama . ' sebagai hadir? (Manual tanpa scan QR)')
                    ->action(function (Tamu $record) {
                        Kehadiran::create([
                            'tamu_id' => $record->id,
                            'scanned_at' => now(),
                        ]);
                    })
                    ->visible(fn (Tamu $record) => !$record->kehadirans()->exists()
                        && (self::me()?->canMarkHadir() ?? false)),
                Action::make('batal_hadir')
                    ->label('Batal Hadir')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Tamu $record) {
                        $record->kehadirans()->delete();
                    })
                    ->visible(fn (Tamu $record) => $record->kehadirans()->exists()
                        && (self::me()?->canMarkHadir() ?? false)),
                Action::make('qrcode')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->modalHeading(fn (Tamu $record) => 'QR Code - ' . $record->nama)
                    ->modalContent(fn (Tamu $record) => new HtmlString(
                        view('filament.components.qrcode-modal', ['tamu' => $record])->render()
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->visible(fn () => self::me()?->isPanitia() ?? false),
                Action::make('print_qr')
                    ->label('Print QR')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (Tamu $record) {
                        $pdf = Pdf::loadView('pdf.qrcode-single', ['tamu' => $record]);
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'qr-' . $record->kode_unik . '.pdf'
                        );
                    })
                    ->visible(fn () => self::me()?->isPanitia() ?? false),
                Action::make('share_wa')
                    ->label(fn (Tamu $record) => $record->wa_terkirim_at ? 'WA ✓' : 'WA')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color(fn (Tamu $record) => $record->wa_terkirim_at ? 'success' : 'gray')
                    ->modalHeading(fn (Tamu $record) => 'Kirim via WhatsApp — ' . $record->nama)
                    ->modalContent(fn (Tamu $record) => new HtmlString(
                        view('filament.components.share-wa-modal', [
                            'tamu'  => $record,
                            'pesan' => self::buildWaMessage($record),
                        ])->render()
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->visible(fn () => self::me()?->isPanitia() ?? false),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => self::me()?->isPanitia() ?? false),
                    BulkAction::make('print_qr_bulk_a4')
                        ->label('Print QR Code (A4)')
                        ->visible(fn () => self::me()?->isPanitia() ?? false)
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $pdf = Pdf::loadView('pdf.qrcode-bulk', [
                                'tamuList' => $records,
                                'columns' => 3,
                            ]);
                            $pdf->setPaper('a4', 'portrait');
                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                'qr-codes-bulk-a4.pdf'
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('print_qr_bulk_a3plus')
                        ->label('Print QR Code (A3+)')
                        ->visible(fn () => self::me()?->isPanitia() ?? false)
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $pdf = Pdf::loadView('pdf.qrcode-bulk', [
                                'tamuList' => $records,
                                'columns' => 4,
                            ]);
                            // A3+ = 329mm × 483mm in points (1pt = 0.3528mm)
                            $pdf->setPaper([0, 0, 932.6, 1369.1], 'portrait');
                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                'qr-codes-bulk-a3plus.pdf'
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Deteksi jenis titel dari nama.
     * Returns: 'uskup' | 'romo' | 'suster' | 'frater' | 'bruder' | 'none'
     */
    /**
     * Deteksi jenis titel dari nama.
     *
     * Membedakan singkatan (RP., Sr., Fr., Br.) vs kata penuh (Romo, Suster, Frater, Bruder).
     * Singkatan → greeting ditambah kata title supaya lebih jelas.
     * Kata penuh → greeting cukup "Yang Terhormat" karena title sudah ada di nama.
     *
     * Returns:
     *   'uskup_abbr'   Mgr.
     *   'uskup_word'   Kardinal
     *   'romo_abbr'    RP. | RD. | Pastor
     *   'romo_word'    Romo (kata lengkap)
     *   'suster_abbr'  Sr.
     *   'suster_word'  Suster
     *   'frater_abbr'  Fr.
     *   'frater_word'  Frater
     *   'bruder_abbr'  Br.
     *   'bruder_word'  Bruder
     *   'none'
     */
    private static function detectTitleType(string $nama): string
    {
        if (preg_match('/\bMgr\./i', $nama))               return 'uskup_abbr';
        if (preg_match('/\bKardinal\b/i', $nama))          return 'uskup_word';
        if (preg_match('/\b(RP\.|RD\.|Pastor)/i', $nama))  return 'romo_abbr';
        if (preg_match('/\bRomo\b/i', $nama))               return 'romo_word';
        if (preg_match('/\bSr\./i', $nama))                 return 'suster_abbr';
        if (preg_match('/\bSuster\b/i', $nama))             return 'suster_word';
        if (preg_match('/\bFr\./i', $nama))                 return 'frater_abbr';
        if (preg_match('/\bFrater\b/i', $nama))             return 'frater_word';
        if (preg_match('/\bBr\./i', $nama))                 return 'bruder_abbr';
        if (preg_match('/\bBruder\b/i', $nama))             return 'bruder_word';
        return 'none';
    }

    /** Kelompok kategori dari tipe titel. */
    private static function titleGroup(string $type): string
    {
        return match (true) {
            str_starts_with($type, 'uskup')  => 'uskup',
            str_starts_with($type, 'romo')   => 'romo',
            str_starts_with($type, 'suster') => 'suster',
            str_starts_with($type, 'frater') => 'frater',
            str_starts_with($type, 'bruder') => 'bruder',
            default                          => 'none',
        };
    }

    /**
     * Pilih prefix hormat secara deterministik per tamu.
     * Uskup selalu "Yang Terhormat" (tidak boleh disingkat).
     */
    private static function buildWaPrefix(Tamu $tamu, string $group): string
    {
        if ($group === 'uskup') {
            return 'Yang Terhormat';
        }

        $pool = ['Yth.', 'Yang Terhormat', 'Yang Terkasih'];
        return $pool[$tamu->id % count($pool)];
    }

    /**
     * Greeting pembuka berdasarkan titel, gender, jumlah orang, dan variasi prefix.
     *
     * Singkatan (RP., Sr., Fr., Br.) → prefix saja, titel sudah ada di nama (tidak dobel).
     *   "Yth. RP. Vien Nguyen SCJ,"
     *   "Yang Terkasih Sr. Maria Goretti OSF,"
     *
     * Kata penuh (Romo, Suster, …) → prefix + kata titel (tidak dobel karena kata
     *   titel SEBELUM nama, sedangkan nama itu sendiri dimulai dengan kata tersebut).
     *   "Yang Terhormat Romo Agus Supriyanto,"
     *
     * Komunitas → prefix + "Para Suster / Para Romo / Bapak, Ibu, ..."
     */
    private static function buildWaGreeting(Tamu $tamu): string
    {
        $nama   = $tamu->nama;
        $jumlah = (int) ($tamu->jumlah_orang ?? 1);
        $gender = $tamu->jenis_kelamin;
        $type   = self::detectTitleType($nama);
        $group  = self::titleGroup($type);
        $prefix = self::buildWaPrefix($tamu, $group);

        // Uskup — selalu formal penuh, prefix "Yang Terhormat"
        if ($group === 'uskup') {
            return "{$prefix} Bapak Uskup";
        }

        // Komunitas (lebih dari 2 orang)
        if ($jumlah > 2) {
            // Jika nama sudah mengandung titel (Sr., Suster, Fr., Romo, dsb.)
            // cukup prefix saja — nama sudah menjelaskan siapa penerimanya.
            // "Yth. Sr. Yohana dan komunitas," bukan "Yth. Para Suster Sr. Yohana..."
            if ($group !== 'none') {
                return $prefix;
            }
            // Tanpa titel di nama → tambahkan label komunitas
            return "{$prefix} Bapak, Ibu, dan Saudara-Saudari";
        }

        // Singkatan (RP., Sr., Fr., Br.) → prefix saja, titel sudah ada di nama
        if (str_ends_with($type, '_abbr')) {
            return $prefix;
        }

        // Kata penuh (Romo, Suster, Frater, Bruder) — titel sudah jadi kata pertama nama,
        // cukup prefix saja agar tidak dobel:
        // "Yth. Romo Agus..." bukan "Yth. Romo Romo Agus..."
        if (str_ends_with($type, '_word')) {
            return $prefix;
        }

        // Tanpa titel — gunakan gender
        if ($gender === 'P')                              return "{$prefix} Ibu";
        if ($gender === 'L')                              return "{$prefix} Bapak";
        if (preg_match('/\b(Ibu|Bu)\b/i', $nama))       return "{$prefix} Ibu";
        if (preg_match('/\b(Bapak|Pak|Bp\.)/i', $nama)) return "{$prefix} Bapak";

        return "{$prefix} Bapak/Ibu";
    }

    /**
     * Sapaan singkat untuk dipakai di dalam badan pesan.
     * Contoh: "Romo", "Suster", "Bapak", "Ibu", "Para Suster"
     */
    private static function buildWaSapaan(Tamu $tamu): string
    {
        $nama   = $tamu->nama;
        $jumlah = (int) ($tamu->jumlah_orang ?? 1);
        $gender = $tamu->jenis_kelamin;
        $group  = self::titleGroup(self::detectTitleType($nama));

        if ($jumlah > 2) {
            return match ($group) {
                'suster' => 'Para Suster',
                'frater' => 'Para Frater',
                'bruder' => 'Para Bruder',
                'romo'   => 'Para Romo',
                default  => 'Bapak, Ibu, dan Saudara-Saudari',
            };
        }

        if ($group !== 'none') {
            return match ($group) {
                'uskup'  => 'Bapak Uskup',
                'romo'   => 'Romo',
                'suster' => 'Suster',
                'frater' => 'Frater',
                'bruder' => 'Bruder',
                default  => 'Bapak/Ibu',
            };
        }

        if ($gender === 'P')                               return 'Ibu';
        if ($gender === 'L')                               return 'Bapak';
        if (preg_match('/\b(Ibu|Bu)\b/i', $nama))        return 'Ibu';
        if (preg_match('/\b(Bapak|Pak|Bp\.)/i', $nama))  return 'Bapak';

        return 'Bapak/Ibu';
    }

    public static function buildWaMessage(Tamu $tamu): string
    {
        $greeting = self::buildWaGreeting($tamu);
        $sapaan   = self::buildWaSapaan($tamu);
        $nama     = $tamu->nama;
        $url      = $tamu->getQrUrl();
        $acara    = Setting::getValue('nama_acara', config('undangan.event_full_name'));

        $variations = [
            // 1
            "{$greeting} {$nama}, berikut tautan undangan Perayaan Syukur {$acara}.\n{$url}\n\nTerima kasih atas kehadiran dan doa {$sapaan}. Tuhan memberkati.",

            // 2
            "Salam hormat {$greeting} {$nama}, dengan sukacita kami mengundang {$sapaan} dalam Perayaan Syukur {$acara}.\n{$url}\n\nMohon kehadiran dan doanya. Tuhan memberkati.",

            // 3
            "{$greeting} {$nama}, dengan penuh syukur kami mengundang {$sapaan} merayakan {$acara}.\n{$url}\n\nKehadiran dan doa {$sapaan} sangat berarti bagi kami. Tuhan memberkati.",

            // 4
            "Dengan hormat {$greeting} {$nama}, perkenankan kami menyampaikan undangan Perayaan Syukur {$acara}.\n{$url}\n\nBesar harapan kami atas kehadiran {$sapaan}. Tuhan memberkati.",

            // 5
            "Syalom {$greeting} {$nama}, bersama ini kami sampaikan undangan Perayaan Syukur {$acara}.\n{$url}\n\nKami nantikan kehadiran dan dukungan doa {$sapaan}. Tuhan memberkati.",

            // 6
            "Salam dalam kasih {$greeting} {$nama}, kami mengundang {$sapaan} bersukacita dalam Perayaan {$acara}.\n{$url}\n\nTerima kasih atas perhatian dan doanya. Salam berkat.",

            // 7
            "Teriring salam hormat {$greeting} {$nama}, izinkan kami mengundang {$sapaan} pada Perayaan Syukur {$acara}.\n{$url}\n\nTerima kasih atas kehadiran dan doanya. Tuhan memberkati.",

            // 8
            "{$greeting} {$nama}, dengan gembira kami mengundang {$sapaan} pada Perayaan {$acara}.\n{$url}\n\nKehadiran {$sapaan} adalah berkat bagi kami. Tuhan memberkati.",

            // 9
            "Salam sejahtera {$greeting} {$nama}, kami dengan rendah hati mengundang {$sapaan} dalam Perayaan Syukur {$acara}.\n{$url}\n\nTerima kasih atas kehadiran dan doa {$sapaan}. Tuhan memberkati.",

            // 10
            "Puji Tuhan {$greeting} {$nama}, kami bersukacita menyampaikan undangan Perayaan Syukur {$acara}.\n{$url}\n\nTerima kasih atas doa dan kehadiran {$sapaan}. Tuhan memberkati berlimpah.",

            // 11
            "{$greeting} {$nama}, dalam rangka Perayaan Syukur {$acara}, kami mengundang {$sapaan} untuk hadir.\n{$url}\n\nMohon berkenan hadir dan mendoakannya. Tuhan memberkati.",

            // 12
            "Salam kasih dalam Kristus {$greeting} {$nama}, bersama ini kami kirimkan undangan Perayaan Syukur {$acara}.\n{$url}\n\nKehadiran dan doa {$sapaan} adalah berkat bagi kami. Tuhan memberkati.",

            // 13
            "{$greeting} {$nama}, hari istimewa semakin dekat — kami mengundang {$sapaan} merayakan {$acara}.\n{$url}\n\nTerima kasih atas kehadiran dan dukungan {$sapaan}. Tuhan memberkati.",

            // 14
            "Dengan segala hormat {$greeting} {$nama}, kami menyampaikan undangan Perayaan Syukur {$acara}.\n{$url}\n\nKami sangat mengharapkan kehadiran dan doa restu {$sapaan}. Tuhan memberkati.",

            // 15
            "Terpujilah Tuhan, {$greeting} {$nama}, kami mengundang {$sapaan} bersama memuji dan bersyukur dalam Perayaan {$acara}.\n{$url}\n\nTerima kasih atas kehadiran dan doanya. Tuhan memberkati.",

            // 16
            "{$greeting} {$nama}, sungguh sukacita bagi kami mengundang {$sapaan} dalam Perayaan Syukur {$acara}.\n{$url}\n\nMohon kehadiran dan doa {$sapaan}. Tuhan memberkati.",

            // 17
            "Salam hangat {$greeting} {$nama}, dengan syukur kami mengundang {$sapaan} berbagi sukacita dalam Perayaan {$acara}.\n{$url}\n\nKehadiran {$sapaan} melengkapi kegembiraan kami. Tuhan memberkati.",

            // 18
            "{$greeting} {$nama}, momen {$acara} kami bagikan kepada {$sapaan}.\n{$url}\n\nTerima kasih atas kesetiaan doa dan dukungan {$sapaan} selama ini. Tuhan memberkati.",

            // 19
            "Dalam kasih Kristus {$greeting} {$nama}, kami mengundang {$sapaan} bersyukur dalam Perayaan {$acara}.\n{$url}\n\nTerima kasih atas kehadiran dan doa tulus {$sapaan}. Tuhan memberkati.",

            // 20
            "{$greeting} {$nama}, dengan suka cita kami mengundang {$sapaan} merayakan {$acara}.\n{$url}\n\nHarapan kami {$sapaan} berkenan hadir dan mendoakannya. Tuhan memberkati.",

            // 21
            "Doa dan salam {$greeting} {$nama}, dengan penuh kebanggaan kami mengundang {$sapaan} dalam Perayaan Syukur {$acara}.\n{$url}\n\nKehadiran dan doa {$sapaan} adalah anugerah bagi kami. Tuhan memberkati.",

            // 22
            "{$greeting} {$nama}, Perayaan {$acara} segera tiba — kami menantikan kehadiran {$sapaan}.\n{$url}\n\nMohon kehadiran dan doa restunya. Tuhan memberkati.",

            // 23
            "Salam damai {$greeting} {$nama}, bersama ini kami haturkan undangan Perayaan Syukur {$acara}.\n{$url}\n\nTerima kasih atas kehadiran dan doa {$sapaan}. Tuhan memberkati.",

            // 24
            "{$greeting} {$nama}, sebagai bagian dari perayaan {$acara}, kami sampaikan undangan ini kepada {$sapaan}.\n{$url}\n\nTerima kasih atas doa, dukungan, dan kehadiran {$sapaan}. Tuhan memberkati.",

            // 25
            "Dengan kerendahan hati {$greeting} {$nama}, kami mengundang {$sapaan} dalam Perayaan Syukur {$acara}.\n{$url}\n\nKami bersyukur atas doa dan kasih {$sapaan}. Tuhan memberkati.",
        ];

        // Pilih variasi secara deterministik berdasarkan ID tamu (konsisten per tamu)
        $index = $tamu->id % count($variations);

        return $variations[$index];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\TamuResource\Pages\ListTamu::route('/'),
            'create' => \App\Filament\Resources\TamuResource\Pages\CreateTamu::route('/create'),
            'edit' => \App\Filament\Resources\TamuResource\Pages\EditTamu::route('/{record}/edit'),
        ];
    }
}
