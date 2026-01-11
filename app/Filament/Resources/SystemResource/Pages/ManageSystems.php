<?php

namespace App\Filament\Resources\SystemResource\Pages;

use App\Filament\Resources\SystemResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSystems extends ManageRecords
{
    protected static string $resource = SystemResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
