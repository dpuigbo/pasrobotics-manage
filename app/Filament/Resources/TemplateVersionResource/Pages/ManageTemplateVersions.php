<?php

namespace App\Filament\Resources\TemplateVersionResource\Pages;

use App\Filament\Resources\TemplateVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTemplateVersions extends ManageRecords
{
    protected static string $resource = TemplateVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
