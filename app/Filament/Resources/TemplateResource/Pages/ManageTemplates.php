<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTemplates extends ManageRecords
{
    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
