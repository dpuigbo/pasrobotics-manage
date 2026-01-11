<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateResource\Pages;
use App\Models\Template;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;
    protected static ?string $navigationGroup = 'Constructor';
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $modelLabel = 'Plantilla';
    protected static ?string $pluralModelLabel = 'Plantillas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(200),

            Forms\Components\Select::make('component_type')
                ->label('Tipo de componente')
                ->required()
                ->options([
                    'system_base'     => 'Base de sistema',
                    'controller'      => 'Controladora',
                    'mechanical_unit' => 'Unidad mecánica',
                    'drive_unit'      => 'Drive Unit',
                    'corrective'      => 'Correctivo (estándar)',
                ]),

            Forms\Components\TextInput::make('manufacturer')->label('Fabricante')->maxLength(50),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('component_type')->label('Tipo')->sortable(),
            Tables\Columns\TextColumn::make('manufacturer')->label('Fabricante')->toggleable(),
            Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTemplates::route('/'),
        ];
    }
}
