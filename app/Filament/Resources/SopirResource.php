<?php

namespace App\Filament\Resources;

use App\Models\Sopir;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SopirResource extends Resource
{
    protected static ?string $model = Sopir::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Master Transport';

    protected static ?string $modelLabel = 'Transport';

    protected static ?string $pluralModelLabel = 'Transport';

    protected static ?int $navigationSort = 6;

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
                    ->label('Nama Transport')
                    ->required()
                    ->maxLength(255),
                TextInput::make('no_hp')
                    ->label('No. HP')
                    ->required()
                    ->tel()
                    ->maxLength(20),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email Login')
                    ->default('—')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin'),
                TextColumn::make('no_hp')
                    ->label('Password (= No. HP)')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('konfirmasis_count')
                    ->label('Tamu Dijemput')
                    ->counts('konfirmasis')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make()
                    ->after(function (Sopir $record) {
                        if ($record->user_id) {
                            // Sync name & password if no_hp changed
                            $newPassword = preg_replace('/[^0-9]/', '', $record->no_hp);
                            $record->user()->update([
                                'name'     => $record->nama,
                                'password' => Hash::make($newPassword),
                            ]);
                        } else {
                            static::createUserIfNeeded($record);
                        }
                    }),
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
            'index' => \App\Filament\Resources\SopirResource\Pages\ManageSopirs::route('/'),
        ];
    }

    /**
     * Auto-create a User account for this transport if not yet linked.
     */
    public static function createUserIfNeeded(Sopir $sopir, ?string &$plainPassword = null): void
    {
        if ($sopir->user_id) {
            return;
        }

        $baseEmail = Str::slug($sopir->nama, '.') . '@' . config('undangan.driver_email_domain');
        $email = $baseEmail;
        $i = 1;
        while (User::where('email', $email)->exists()) {
            $email = Str::slug($sopir->nama, '.') . $i . '@' . config('undangan.driver_email_domain');
            $i++;
        }

        // Generate a secure random password (readable alphanumeric, no symbols).
        // The plain password is returned via reference and shown once to the admin.
        $password = Str::password(10, letters: true, numbers: true, symbols: false);
        $plainPassword = $password;

        $user = User::create([
            'name'     => $sopir->nama,
            'email'    => $email,
            'password' => Hash::make($password),
            'role'     => 'transport',
        ]);

        $sopir->update(['user_id' => $user->id]);

        Notification::make()
            ->title('Akun transport dibuat')
            ->body("Email: {$email}\nPassword: {$password}\nPanel: /transport")
            ->success()
            ->persistent()
            ->send();
    }
}
