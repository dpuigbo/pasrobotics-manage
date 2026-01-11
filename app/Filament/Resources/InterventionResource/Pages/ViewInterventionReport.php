<?php

namespace App\Filament\Resources\InterventionResource\Pages;

use App\Filament\Resources\InterventionResource;
use App\Models\Intervention;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewInterventionReport extends ViewRecord
{
    protected static string $resource = InterventionResource::class;

    protected static string $view = 'filament.resources.intervention-resource.pages.report';

    protected function resolveRecord($key): Model
    {
        return Intervention::query()
            ->with([
                'system.controllerUnit.controllerModel',
                'system.mechanicalUnits.robotModel',
                'system.driveUnits.driveUnitModel',
                'components.templateVersion.template',
            ])
            ->findOrFail($key);
    }
}
