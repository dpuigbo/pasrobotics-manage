<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RobotModelResource\Pages;

class RobotModelResource extends ComponentModelBaseResource
{
    protected static string $type = 'mechanical_unit';
    protected static ?string $navigationLabel = 'Modelos De Robot';
    protected static ?string $modelLabel = 'Modelo de Robot';
    protected static ?string $pluralModelLabel = 'Modelos de Robot';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRobotModels::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('type', 'robot');
    }
}
