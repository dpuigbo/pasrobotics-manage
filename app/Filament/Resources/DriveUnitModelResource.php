<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriveUnitModelResource\Pages;

class DriveUnitModelResource extends ComponentModelBaseResource
{
    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $modelLabel = 'Drive Unit';
    protected static ?string $pluralModelLabel = 'Modelos de Drive Unit';

    public static function componentType(): string
    {
        return 'drive_unit';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDriveUnitModels::route('/'),
        ];
    }
}
