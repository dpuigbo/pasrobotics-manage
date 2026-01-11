<?php

namespace App\Filament\Resources\InterventionResource\Pages;

use App\Filament\Resources\InterventionResource;
use App\Models\Intervention;
use App\Services\InterventionComposer;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInterventions extends ManageRecords
{
    protected static string $resource = InterventionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->after(function (Intervention $record) {
                    app(InterventionComposer::class)->compose($record);
                }),
        ];
    }
}
