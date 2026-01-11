<?php

namespace App\Filament\Resources\InterventionResource\Pages;

use App\Filament\Resources\InterventionResource;
use App\Models\Intervention;
use Filament\Resources\Pages\Page;

class ViewInterventionReport extends Page
{
    protected static string $resource = InterventionResource::class;

    protected static string $view = 'filament.resources.intervention-resource.pages.report';

    public Intervention $record;

    public function mount(int|string $record): void
    {
        $this->record = Intervention::query()
            ->with([
                'system.controllerUnit.controllerModel',
                'system.mechanicalUnits.robotModel',
                'system.driveUnits.driveUnitModel',
                'components.templateVersion.template',
            ])
            ->findOrFail($record);
    }
}
