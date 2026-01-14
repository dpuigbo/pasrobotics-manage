<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControllerModelResource\Pages;

class ControllerModelResource extends ComponentModelBaseResource
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $modelLabel = 'Controladora';
    protected static ?string $pluralModelLabel = 'Modelos de Controladora';

    public static function componentType(): string
    {
        return 'controller';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageControllerModels::route('/'),
        ];
    }
}
