<?php

namespace App\Filament\Resources\DriveUnitModelResource\Pages;

use App\Filament\Resources\DriveUnitModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDriveUnitModels extends ListRecords
{
    protected static string $resource = DriveUnitModelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
