<?php

namespace App\Filament\Resources\TemplateAssignmentResource\Pages;

use App\Filament\Resources\TemplateAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTemplateAssignments extends ManageRecords
{
    protected static string $resource = TemplateAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
