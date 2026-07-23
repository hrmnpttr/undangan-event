<?php

namespace App\Filament\Resources;

use App\Models\Kehadiran;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KehadiranResource extends Resource
{
    protected static ?string $model = Kehadiran::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Log Kehadiran';

    protected static ?string $modelLabel = 'Kehadiran';

    protected static ?string $pluralModelLabel = 'Kehadiran';

    protected static ?int $navigationSort = 5;

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
                    ->sortable(),
                TextColumn::make('tamu.jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VVIP' => 'danger',
                        'VIP' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('scanned_at')
                    ->label('Waktu Scan')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                TextColumn::make('scan_count')
                    ->label('Total Scan')
                    ->state(fn (Kehadiran $record) => $record->tamu->kehadirans()->count())
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                SelectFilter::make('jenis')
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
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scanned_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\KehadiranResource\Pages\ListKehadiran::route('/'),
        ];
    }
}
