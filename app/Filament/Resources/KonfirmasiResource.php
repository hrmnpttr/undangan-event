<?php

namespace App\Filament\Resources;

use App\Models\Konfirmasi;
use App\Models\Penginapan;
use App\Models\Sopir;
use App\Models\Tamu;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KonfirmasiResource extends Resource
{
    protected static ?string $model = Konfirmasi::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Konfirmasi';

    protected static ?string $modelLabel = 'Konfirmasi';

    protected static ?string $pluralModelLabel = 'Konfirmasi';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user instanceof \App\Models\User && $user->isPanitia();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tamu.nama')
                    ->label('Nama Tamu')
                    ->searchable()
                    ->sortable()
                    ->color(fn (Konfirmasi $record) => match ($record->tamu?->jenis) {
                        'VVIP' => 'danger',
                        'VIP' => 'warning',
                        default => null,
                    }),
                TextColumn::make('tamu.jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VVIP' => 'danger',
                        'VIP' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('jumlah_hadir')
                    ->label('Jml Hadir')
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 0 ? 'gray' : 'success')
                    ->formatStateUsing(fn ($state) => (int) $state === 0 ? 'Tidak Hadir' : $state)
                    ->sortable(),
                IconColumn::make('butuh_antar_jemput')
                    ->label('Antar Jemput')
                    ->boolean(),
                TextColumn::make('sopir.nama')
                    ->label('Sopir')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('tanggal_datang')
                    ->label('Tgl Datang')
                    ->date()
                    ->placeholder('-'),
                TextColumn::make('tanggal_pulang')
                    ->label('Tgl Pulang')
                    ->date()
                    ->placeholder('-'),
                IconColumn::make('butuh_penginapan')
                    ->label('Penginapan')
                    ->boolean(),
                TextColumn::make('jumlah_menginap')
                    ->label('Jml Menginap')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tempat_inap')
                    ->label('Tempat Inap')
                    ->state(fn (Konfirmasi $record) => $record->penginapan_efektif?->nama)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('nomor_kamar')
                    ->label('No. Kamar')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('confirmed_at')
                    ->label('Waktu Konfirmasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('jumlah_perubahan_baru')
                    ->label('Perubahan')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state . ' baru' : null)
                    ->default(null),
                IconColumn::make('acknowledged')
                    ->label('Diketahui')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'baru' => 'Baru (Belum Diketahui)',
                        'diketahui' => 'Sudah Diketahui',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'baru') {
                            $query->where('acknowledged', false);
                        } elseif ($data['value'] === 'diketahui') {
                            $query->where('acknowledged', true);
                        }
                    }),
                SelectFilter::make('tamu_jenis')
                    ->label('Jenis Tamu')
                    ->options([
                        'umum' => 'Umum',
                        'VIP' => 'VIP',
                        'VVIP' => 'VVIP',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('tamu', fn (Builder $q) => $q->where('jenis', $data['value']));
                        }
                    }),
                TernaryFilter::make('butuh_antar_jemput')
                    ->label('Antar Jemput'),
                TernaryFilter::make('menginap')
                    ->label('Menginap')
                    ->placeholder('Semua')
                    ->trueLabel('Ya, menginap')
                    ->falseLabel('Tidak menginap')
                    ->queries(
                        true: fn (Builder $query) => $query->where('butuh_penginapan', true)->where('jumlah_menginap', '>', 0),
                        false: fn (Builder $query) => $query->where(fn (Builder $q) => $q
                            ->where('butuh_penginapan', '!=', true)
                            ->orWhereNull('butuh_penginapan')
                            ->orWhere('jumlah_menginap', 0)
                            ->orWhereNull('jumlah_menginap')),
                        blank: fn (Builder $query) => $query,
                    ),
                SelectFilter::make('tempat_menginap')
                    ->label('Tempat Menginap')
                    ->multiple()
                    ->options(fn () => Penginapan::orderBy('nama')->pluck('nama', 'id'))
                    ->query(function (Builder $query, array $data) {
                        $values = $data['values'] ?? [];
                        if (empty($values)) {
                            return;
                        }
                        // Match either an admin-assigned room or the tamu's accommodation
                        $query->where(fn (Builder $q) => $q
                            ->whereIn('penginapan_id', $values)
                            ->orWhereHas('tamu', fn (Builder $t) => $t->whereIn('penginapan_id', $values)));
                    }),
                \Filament\Tables\Filters\Filter::make('tidak_hadir')
                    ->label('Tidak Hadir')
                    ->query(fn (Builder $query) => $query->where('jumlah_hadir', 0))
                    ->toggle(),
                \Filament\Tables\Filters\Filter::make('tanggal_datang')
                    ->label('Tgl Datang')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('tanggal_datang')
                            ->label('Tanggal Datang'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['tanggal_datang']) {
                            $query->whereDate('tanggal_datang', $data['tanggal_datang']);
                        }
                    }),
                \Filament\Tables\Filters\Filter::make('tanggal_pulang')
                    ->label('Tgl Pulang')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('tanggal_pulang')
                            ->label('Tanggal Pulang'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['tanggal_pulang']) {
                            $query->whereDate('tanggal_pulang', $data['tanggal_pulang']);
                        }
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye'),
                Action::make('assign_sopir')
                    ->label('Atur Sopir')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->fillForm(fn (Konfirmasi $record) => [
                        'sopir_id' => $record->sopir_id,
                    ])
                    ->form([
                        Select::make('sopir_id')
                            ->label('Sopir')
                            ->options(Sopir::pluck('nama', 'id'))
                            ->searchable()
                            ->placeholder('Pilih sopir...'),
                    ])
                    ->action(function (Konfirmasi $record, array $data) {
                        $record->update(['sopir_id' => $data['sopir_id']]);
                    })
                    ->visible(fn (Konfirmasi $record) => (bool) $record->butuh_antar_jemput),
                Action::make('assign_penginapan')
                    ->label('Atur Penginapan')
                    ->icon('heroicon-o-building-office')
                    ->color('info')
                    ->fillForm(fn (Konfirmasi $record) => [
                        'penginapan_id' => $record->penginapan_id,
                        'nomor_kamar' => $record->nomor_kamar,
                        'butuh_penginapan' => $record->butuh_penginapan,
                    ])
                    ->form([
                        Toggle::make('butuh_penginapan')
                            ->label('Butuh Penginapan')
                            ->helperText('Admin bisa override pilihan tamu'),
                        Select::make('penginapan_id')
                            ->label('Tempat Penginapan')
                            ->options(fn () => Penginapan::orderBy('nama')
                                ->get()
                                ->mapWithKeys(fn ($p) => [
                                    $p->id => $p->nama . ($p->kode ? ' [' . $p->kode . ']' : ''),
                                ]))
                            ->searchable()
                            ->placeholder('Pilih penginapan...'),
                        TextInput::make('nomor_kamar')
                            ->label('Nomor Kamar')
                            ->maxLength(50),
                    ])
                    ->action(function (Konfirmasi $record, array $data) {
                        $record->update([
                            'butuh_penginapan' => $data['butuh_penginapan'],
                            'penginapan_id' => $data['penginapan_id'],
                            'nomor_kamar' => $data['nomor_kamar'],
                        ]);
                    }),
                Action::make('acknowledge')
                    ->label('Diketahui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Konfirmasi $record) {
                        // Mark all change log entries as acknowledged too
                        $log = $record->catatan_perubahan ?? [];
                        $log = array_map(function ($entry) {
                            $entry['acknowledged'] = true;
                            return $entry;
                        }, $log);
                        $record->update([
                            'acknowledged' => true,
                            'catatan_perubahan' => $log,
                        ]);
                    })
                    ->visible(fn (Konfirmasi $record) => !$record->acknowledged),
                Action::make('unacknowledge')
                    ->label('Batal Diketahui')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Konfirmasi $record) => $record->update(['acknowledged' => false]))
                    ->visible(fn (Konfirmasi $record) => $record->acknowledged),
            ])
            ->defaultSort('confirmed_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Konfirmasi')
                    ->schema([
                        TextEntry::make('tamu.nama')->label('Nama Tamu'),
                        TextEntry::make('tamu.jenis')->label('Jenis')->badge(),
                        TextEntry::make('jumlah_hadir')
                            ->label('Jumlah Hadir')
                            ->badge()
                            ->color(fn ($state): string => (int) $state === 0 ? 'gray' : 'success')
                            ->formatStateUsing(fn ($state) => (int) $state === 0 ? 'Tidak Hadir' : $state),
                        TextEntry::make('tanggal_datang')->label('Tgl Datang')->date()->placeholder('-'),
                        TextEntry::make('tanggal_pulang')->label('Tgl Pulang')->date()->placeholder('-'),
                        TextEntry::make('confirmed_at')->label('Waktu Konfirmasi')->dateTime('d M Y H:i'),
                    ])->columns(2),
                Section::make('Transportasi & Penginapan')
                    ->schema([
                        TextEntry::make('butuh_antar_jemput')
                            ->label('Butuh Antar Jemput')
                            ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
                        TextEntry::make('sopir.nama')->label('Sopir')->placeholder('-'),
                        TextEntry::make('sopir.no_hp')->label('No. HP Sopir')->placeholder('-'),
                        TextEntry::make('butuh_penginapan')
                            ->label('Butuh Penginapan')
                            ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
                        TextEntry::make('jumlah_menginap')->label('Jumlah Menginap')->placeholder('-'),
                        TextEntry::make('tempat_inap')
                            ->label('Tempat Inap')
                            ->state(fn (Konfirmasi $record) => $record->penginapan_efektif?->nama)
                            ->placeholder('-'),
                        TextEntry::make('alamat_penginapan')
                            ->label('Alamat Penginapan')
                            ->state(fn (Konfirmasi $record) => $record->penginapan_efektif?->alamat)
                            ->placeholder('-'),
                        TextEntry::make('telp_penginapan')
                            ->label('No. Telp Penginapan')
                            ->state(fn (Konfirmasi $record) => $record->penginapan_efektif?->no_telp)
                            ->placeholder('-'),
                        TextEntry::make('koordinat_penginapan')
                            ->label('Koordinat / Maps')
                            ->state(fn (Konfirmasi $record) => $record->penginapan_efektif?->koordinat_maps)
                            ->placeholder('-'),
                        TextEntry::make('nomor_kamar')->label('Nomor Kamar')->placeholder('-'),
                    ])->columns(2),
                Section::make('Daftar Peserta')
                    ->schema([
                        TextEntry::make('pesertas')
                            ->label('')
                            ->formatStateUsing(function ($state, Konfirmasi $record) {
                                $pesertas = $record->pesertas;
                                if ($pesertas->isEmpty()) {
                                    return 'Belum ada data peserta.';
                                }

                                $html = '<div class="space-y-2">';
                                foreach ($pesertas as $i => $p) {
                                    $jk = $p->jenis_kelamin === 'L' ? '👨' : ($p->jenis_kelamin === 'P' ? '👩' : '');
                                    $pasangan = $p->nama_pasangan ? ' (Pasangan: ' . e($p->nama_pasangan) . ')' : '';
                                    $html .= '<div class="p-2 rounded border text-sm bg-gray-50 border-gray-200">';
                                    $html .= '<strong>' . ($i + 1) . '. ' . e($p->nama) . '</strong> ' . $jk . $pasangan;
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                return $html;
                            })
                            ->html(),
                    ])
                    ->collapsible(),
                Section::make('Riwayat Perubahan')
                    ->schema([
                        TextEntry::make('catatan_perubahan')
                            ->label('')
                            ->formatStateUsing(function ($state, Konfirmasi $record) {
                                $log = $record->catatan_perubahan;
                                if (!$log || count($log) === 0) {
                                    return 'Tidak ada riwayat perubahan.';
                                }

                                $html = '<div class="space-y-2">';
                                foreach (array_reverse($log) as $entry) {
                                    $waktu = \Carbon\Carbon::parse($entry['waktu'])->format('d M Y H:i');
                                    $ack = ($entry['acknowledged'] ?? false) ? '✓' : '🔴';
                                    $html .= '<div class="p-2 rounded border text-sm ' . (($entry['acknowledged'] ?? false) ? 'bg-gray-50 border-gray-200' : 'bg-amber-50 border-amber-300') . '">';
                                    $html .= "<strong>{$ack} {$entry['field']}</strong>: ";
                                    $html .= "<span class=\"text-red-600 line-through\">{$entry['dari']}</span> → <span class=\"text-green-600 font-semibold\">{$entry['ke']}</span>";
                                    $html .= " <span class=\"text-gray-400 text-xs\">({$waktu})</span>";
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                return $html;
                            })
                            ->html(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Eager load both the assigned room and the tamu's accommodation
        // so the "effective penginapan" accessor doesn't trigger N+1 queries.
        return parent::getEloquentQuery()
            ->with(['penginapan', 'tamu.penginapanRecord']);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\KonfirmasiResource\Pages\ListKonfirmasi::route('/'),
            'view' => \App\Filament\Resources\KonfirmasiResource\Pages\ViewKonfirmasi::route('/{record}'),
        ];
    }
}
