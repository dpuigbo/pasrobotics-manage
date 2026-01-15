<?php

namespace App\Filament\Resources\RobotModelResource\Pages;

use App\Filament\Resources\RobotModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRobotModels extends ListRecords
{
    protected static string $resource = RobotModelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
