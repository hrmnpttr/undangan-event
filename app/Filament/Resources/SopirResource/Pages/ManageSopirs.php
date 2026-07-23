<?php

namespace App\Filament\Resources\SopirResource\Pages;

use App\Filament\Resources\SopirResource;
use App\Models\Sopir;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSopirs extends ManageRecords
{
    protected static string $resource = SopirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->after(function (Sopir $record) {
                    SopirResource::createUserIfNeeded($record);
                }),
        ];
    }
}
