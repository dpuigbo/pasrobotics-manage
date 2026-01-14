<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriveUnitModelResource\Pages;

class DriveUnitModelResource extends ComponentModelBaseResource
{
    protected static string $type = 'drive_unit';
    protected static ?string $navigationLabel = 'Modelos De Drive Unit';
    protected static ?string $modelLabel = 'Modelo de Drive Unit';
    protected static ?string $pluralModelLabel = 'Modelos de Drive Unit';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDriveUnitModels::route('/'),
        ];
    }
}
