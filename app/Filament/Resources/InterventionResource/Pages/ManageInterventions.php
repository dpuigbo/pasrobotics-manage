<?php

namespace App\Filament\Resources\InterventionResource\Pages;

use App\Filament\Resources\InterventionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInterventions extends ManageRecords
{
    protected static string $resource = InterventionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
