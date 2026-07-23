<?php

namespace App\Filament\Widgets;

use App\Models\Tamu;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TamuTanpaKonfirmasiHadirWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Hadir Tanpa Konfirmasi')
            ->description('Tamu yang hadir (scan) tetapi belum konfirmasi kehadiran')
            ->query(
                Tamu::query()
                    ->whereDoesntHave('konfirmasi')
                    ->whereHas('kehadirans')
                    ->withCount('kehadirans')
            )
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VVIP' => 'danger',
                        'VIP' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('jumlah_orang')
                    ->label('Jml Undangan'),
                TextColumn::make('kehadirans_count')
                    ->label('Jumlah Scan')
                    ->badge()
                    ->color('info'),
                TextColumn::make('kehadirans_min_scanned_at')
                    ->label('Scan Pertama')
                    ->state(fn (Tamu $record) => $record->kehadirans()->min('scanned_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('kehadirans_count', 'desc')
            ->emptyStateHeading('Tidak ada data')
            ->emptyStateDescription('Belum ada tamu yang hadir tanpa konfirmasi');
    }
}
