<?php

namespace App\Filament\Resources;

use App\Models\Penginapan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PenginapanResource extends Resource
{
    protected static ?string $model = Penginapan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Master Penginapan';

    protected static ?string $modelLabel = 'Penginapan';

    protected static ?string $pluralModelLabel = 'Penginapan';

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user instanceof \App\Models\User && $user->isPanitia();
    }

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Penginapan')
                    ->placeholder('Contoh: Rumah Retret, Hotel XYZ')
                    ->required()
                    ->maxLength(255),
                TextInput::make('kode')
                    ->label('Kode (short code untuk import)')
                    ->placeholder('Contoh: rrgn, hotel, scj, immanuel, wisma_asrama')
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Select::make('kategori')
                    ->label('Kategori Tamu')
                    ->options([
                        'semua' => 'Semua',
                        'umum' => 'Umum',
                        'VIP' => 'VIP',
                        'VVIP' => 'VVIP',
                    ])
                    ->default('semua')
                    ->required(),
                Textarea::make('alamat')
                    ->label('Alamat')
                    ->rows(3),
                TextInput::make('no_telp')
                    ->label('No. Telp Penginapan')
                    ->maxLength(50)
                    ->tel(),
                TextInput::make('koordinat_maps')
                    ->label('Koordinat / Link Maps')
                    ->placeholder('Contoh: -2.99123,104.75654 atau https://maps.google.com/...')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Penginapan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kode')
                    ->label('Kode')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VVIP' => 'danger',
                        'VIP' => 'warning',
                        'umum' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('alamat')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('no_telp')
                    ->label('No. Telp')
                    ->searchable(),
                TextColumn::make('konfirmasis_count')
                    ->label('Jumlah Tamu')
                    ->counts('konfirmasis')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PenginapanResource\Pages\ManagePenginapans::route('/'),
        ];
    }
}
