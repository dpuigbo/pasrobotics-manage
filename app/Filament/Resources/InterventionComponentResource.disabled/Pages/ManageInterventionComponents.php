<?php

namespace App\Filament\Resources\InterventionComponentResource\Pages;

use App\Filament\Resources\InterventionComponentResource;
use Filament\Resources\Pages\ManageRecords;

class ManageInterventionComponents extends ManageRecords
{
    protected static string $resource = InterventionComponentResource::class;
    protected static bool $shouldRegisterNavigation = false;

    protected function canCreate(): bool
    {
        return false; // se generan automáticamente al crear la intervención
    }
}
