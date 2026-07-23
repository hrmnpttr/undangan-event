<?php

namespace App\Filament\Resources;

use App\Models\Penginapan;
use App\Models\Peserta;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DetailTamuMenginapResource extends Resource
{
    protected static ?string $model = Peserta::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Detail Tamu Menginap';

    protected static ?string $modelLabel = 'Tamu Menginap';

    protected static ?string $pluralModelLabel = 'Detail Tamu Menginap';

    protected static ?int $navigationSort = 6;

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user instanceof \App\Models\User
            && in_array($user->role, ['panitia', 'penginapan', 'panitia_penginapan'], true);
    }

    public static function canCreate(): bool  { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function getEloquentQuery(): Builder
    {
        $query = Peserta::query()
            ->with(['konfirmasi.tamu.penginapanRecord'])
            ->whereHas('konfirmasi', fn (Builder $q) => $q
                ->where('butuh_penginapan', true)
                ->whereHas('tamu', fn (Builder $q2) => $q2->whereNotNull('penginapan_id'))
            );

        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user instanceof \App\Models\User && $user->role === 'penginapan' && $user->penginapan_id) {
            $query->whereHas('konfirmasi.tamu', fn (Builder $q) =>
                $q->where('penginapan_id', $user->penginapan_id)
            );
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('konfirmasi.tamu.penginapanRecord.nama')
                    ->label('Penginapan')
                    ->sortable()
                    ->searchable(query: fn (Builder $q, string $s) =>
                        $q->whereHas('konfirmasi.tamu.penginapanRecord', fn ($q2) =>
                            $q2->where('nama', 'like', "%{$s}%")
                        )
                    ),

                TextColumn::make('konfirmasi.tamu.nama')
                    ->label('Nama Tamu')
                    ->searchable(query: fn (Builder $q, string $s) =>
                        $q->whereHas('konfirmasi.tamu', fn ($q2) =>
                            $q2->where('nama', 'like', "%{$s}%")
                        )
                    )
                    ->sortable(),

                TextColumn::make('konfirmasi.tamu.jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VVIP' => 'danger',
                        'VIP'  => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('konfirmasi.nomor_kamar')
                    ->label('No. Kamar')
                    ->placeholder('-'),

                TextColumn::make('konfirmasi.tanggal_datang')
                    ->label('Tgl Datang')
                    ->date('d/m/Y')
                    ->placeholder('-'),

                TextColumn::make('konfirmasi.tanggal_pulang')
                    ->label('Tgl Pulang')
                    ->date('d/m/Y')
                    ->placeholder('-'),

                TextColumn::make('konfirmasi.jumlah_menginap')
                    ->label('Jml Menginap')
                    ->placeholder('-'),

                TextColumn::make('nama')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'L' => 'info',
                        'P' => 'rose',
                        default => 'gray',
                    }),

                TextColumn::make('nama_pasangan')
                    ->label('Nama Pasangan')
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->defaultSort(fn (Builder $query) =>
                $query
                    // Select only peserta columns so the joined tables' `nama`
                    // columns don't clobber peserta.nama when the model is hydrated.
                    ->select('peserta.*')
                    ->join('konfirmasi as k_sort', 'k_sort.id', '=', 'peserta.konfirmasi_id')
                    ->join('tamu as t_sort', 't_sort.id', '=', 'k_sort.tamu_id')
                    ->join('penginapan as p_sort', 'p_sort.id', '=', 't_sort.penginapan_id')
                    ->orderBy('p_sort.nama')
                    ->orderBy('t_sort.nama')
            )
            ->filters([
                SelectFilter::make('penginapan')
                    ->label('Penginapan')
                    ->options(fn () => static::penginapanOptions())
                    ->query(fn (Builder $q, array $data) =>
                        $data['value']
                            ? $q->whereHas('konfirmasi.tamu', fn ($q2) =>
                                $q2->where('penginapan_id', $data['value'])
                              )
                            : $q
                    ),

                SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),

                SelectFilter::make('jenis_tamu')
                    ->label('Jenis Tamu')
                    ->options(['umum' => 'Umum', 'VIP' => 'VIP', 'VVIP' => 'VVIP'])
                    ->query(fn (Builder $q, array $data) =>
                        $data['value']
                            ? $q->whereHas('konfirmasi.tamu', fn ($q2) =>
                                $q2->where('jenis', $data['value'])
                              )
                            : $q
                    ),
            ])
            ->striped()
            ->paginated([25, 50, 100]);
    }

    private static function penginapanOptions(): array
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // penginapan role: only show their own hotel
        if ($user instanceof \App\Models\User && $user->role === 'penginapan' && $user->penginapan_id) {
            return Penginapan::where('id', $user->penginapan_id)->pluck('nama', 'id')->toArray();
        }

        return Penginapan::orderBy('nama')->pluck('nama', 'id')->toArray();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\DetailTamuMenginapResource\Pages\ListDetailTamuMenginap::route('/'),
        ];
    }
}
