<?php

namespace App\Filament\Resources\KonfirmasiResource\Pages;

use App\Filament\Resources\KonfirmasiResource;
use App\Models\Konfirmasi;
use App\Models\Tamu;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListKonfirmasi extends ListRecords
{
    protected static string $resource = KonfirmasiResource::class;

    public function getTabs(): array
    {
        // Count konfirmasi that have unacknowledged change log entries
        $perubahanBaruCount = Konfirmasi::where(function (Builder $q) {
            $q->whereNotNull('catatan_perubahan')
              ->whereJsonLength('catatan_perubahan', '>', 0);
        })->get()->filter(fn ($k) => $k->jumlah_perubahan_baru > 0)->count();

        return [
            'semua' => Tab::make('Semua'),
            'baru' => Tab::make('Baru Konfirmasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('acknowledged', false))
                ->badge(fn () => Konfirmasi::where('acknowledged', false)->count()),
            'perubahan' => Tab::make('Ada Perubahan')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNotNull('catatan_perubahan')
                    ->whereJsonLength('catatan_perubahan', '>', 0)
                )
                ->badge($perubahanBaruCount > 0 ? $perubahanBaruCount : null)
                ->badgeColor('warning'),
            'diketahui' => Tab::make('Sudah Diketahui')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('acknowledged', true)),
        ];
    }
}
