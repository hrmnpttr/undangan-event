<?php

namespace App\Filament\Resources;

use App\Models\Penginapan;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        return auth()->user()?->isPanitia() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Kosongkan jika tidak ingin mengubah password'),
                Select::make('role')
                    ->label('Role')
                    ->options([
                        'panitia'            => 'Panitia',
                        'transport'          => 'Transport',
                        'scan'               => 'Scan Kehadiran',
                        'penginapan'         => 'Penginapan (per hotel)',
                        'panitia_penginapan' => 'Panitia Penginapan',
                    ])
                    ->required()
                    ->live(),
                Select::make('penginapan_id')
                    ->label('Penginapan')
                    ->helperText('Wajib diisi untuk role Penginapan')
                    ->options(fn () => Penginapan::orderBy('nama')->pluck('nama', 'id'))
                    ->searchable()
                    ->nullable()
                    ->visible(fn ($get) => $get('role') === 'penginapan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'panitia'            => 'warning',
                        'transport'          => 'info',
                        'scan'               => 'success',
                        'penginapan'         => 'primary',
                        'panitia_penginapan' => 'danger',
                        default              => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'panitia'            => 'Panitia',
                        'transport'          => 'Transport',
                        'scan'               => 'Scan Kehadiran',
                        'penginapan'         => 'Penginapan',
                        'panitia_penginapan' => 'Panitia Penginapan',
                        default              => $state,
                    }),
                TextColumn::make('penginapan.nama')
                    ->label('Penginapan')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'panitia'            => 'Panitia',
                        'transport'          => 'Transport',
                        'scan'               => 'Scan Kehadiran',
                        'penginapan'         => 'Penginapan',
                        'panitia_penginapan' => 'Panitia Penginapan',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('role');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\UserResource\Pages\ListUsers::route('/'),
            'create' => \App\Filament\Resources\UserResource\Pages\CreateUser::route('/create'),
            'edit'   => \App\Filament\Resources\UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
