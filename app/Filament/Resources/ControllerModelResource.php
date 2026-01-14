<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControllerModelResource\Pages;

class ControllerModelResource extends ComponentModelBaseResource
{
    protected static string $type = 'controller';
    protected static ?string $navigationLabel = 'Modelos De Controladora';
    protected static ?string $modelLabel = 'Modelo de Controladora';
    protected static ?string $pluralModelLabel = 'Modelos de Controladora';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageControllerModels::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('type', 'controller');
    }
}
