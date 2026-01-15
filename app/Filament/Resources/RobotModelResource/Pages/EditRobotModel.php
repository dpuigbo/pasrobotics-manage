<?php

namespace App\Filament\Resources\RobotModelResource\Pages;

use App\Filament\Resources\RobotModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRobotModel extends EditRecord
{
    protected static string $resource = RobotModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
