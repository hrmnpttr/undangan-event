<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

/**
 * Panduan (in-panel usage tutorial).
 *
 * EN: A quick-start guide shown inside the admin panel so first-time
 *     organisers know how to set up an invitation end to end.
 * ID: Panduan cepat di dalam panel admin agar panitia baru tahu cara
 *     menyiapkan undangan dari awal hingga siap dibagikan.
 */
class Panduan extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Panduan';

    protected static ?string $title = 'Panduan Penggunaan';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.panduan';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->isPanitia();
    }
}
