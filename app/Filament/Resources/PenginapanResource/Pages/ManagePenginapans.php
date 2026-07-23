<?php

namespace App\Filament\Resources\PenginapanResource\Pages;

use App\Filament\Resources\PenginapanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePenginapans extends ManageRecords
{
    protected static string $resource = PenginapanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
