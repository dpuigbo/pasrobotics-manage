<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriveUnitModelResource\Pages;
use App\Models\DriveUnitModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DriveUnitModelResource extends Resource
{
    protected static ?string $model = DriveUnitModel::class;
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $modelLabel = 'Modelo de Drive Unit';
    protected static ?string $pluralModelLabel = 'Modelos de Drive Unit';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('manufacturer')->label('Fabricante')->required()->maxLength(50),
            Forms\Components\TextInput::make('name')->label('Modelo')->required()->maxLength(100),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('manufacturer')->label('Fabricante')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Modelo')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDriveUnitModels::route('/'),
        ];
    }
}
