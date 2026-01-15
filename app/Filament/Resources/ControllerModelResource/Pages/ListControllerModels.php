<?php

namespace App\Filament\Resources\ControllerModelResource\Pages;

use App\Filament\Resources\ControllerModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListControllerModels extends ListRecords
{
    protected static string $resource = ControllerModelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
