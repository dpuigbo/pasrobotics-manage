<?php

namespace App\Filament\Resources\ControllerModelResource\Pages;

use App\Filament\Resources\ControllerModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControllerModel extends EditRecord
{
    protected static string $resource = ControllerModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
