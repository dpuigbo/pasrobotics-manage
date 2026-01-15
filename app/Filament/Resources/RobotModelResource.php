<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RobotModelResource\Pages;

class RobotModelResource extends ComponentModelBaseResource
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $navigationLabel = 'Modelos de Robot';
    protected static ?string $modelLabel = 'Robot';
    protected static ?string $pluralModelLabel = 'Robots';

    public static function componentType(): string
    {
        return 'mechanical_unit';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRobotModels::route('/'),
        ];
    }
}
