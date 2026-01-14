<?php

namespace App\Filament\Resources\ComponentModelResource\Pages;

use App\Filament\Resources\ComponentModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComponentModels extends ListRecords
{
    protected static string $resource = ComponentModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
