<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControllerModelResource\Pages;
use App\Models\ControllerModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ControllerModelResource extends Resource
{
    protected static ?string $model = ControllerModel::class;
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $modelLabel = 'Modelo de Controladora';
    protected static ?string $pluralModelLabel = 'Modelos de Controladora';

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
            'index' => Pages\ManageControllerModels::route('/'),
        ];
    }
}
