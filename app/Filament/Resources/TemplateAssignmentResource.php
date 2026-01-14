<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateAssignmentResource\Pages;
use App\Models\TemplateAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TemplateAssignmentResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = TemplateAssignment::class;
    protected static ?string $navigationGroup = 'Constructor';
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $modelLabel = 'Asignación de plantilla';
    protected static ?string $pluralModelLabel = 'Asignaciones de plantilla';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('component_type')
                ->label('Tipo de componente')
                ->required()
                ->options([
                    'system_base'     => 'Base de sistema',
                    'controller'      => 'Controladora',
                    'mechanical_unit' => 'Unidad mecánica',
                    'drive_unit'      => 'Drive Unit',
                ])
                ->reactive(),

            Forms\Components\Select::make('template_version_id')
                ->label('Versión de plantilla')
                ->relationship('templateVersion', 'version')
                ->searchable()
                ->preload()
                ->required()
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->template?->name . ' - ' . $record->version),

            Forms\Components\Select::make('robot_model_id')
                ->label('Modelo robot')
                ->relationship('robotModel', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('component_type') === 'mechanical_unit')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->manufacturer} {$record->name}"),

            Forms\Components\Select::make('controller_model_id')
                ->label('Modelo controladora')
                ->relationship('controllerModel', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('component_type') === 'controller')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->manufacturer} {$record->name}"),

            Forms\Components\Select::make('drive_unit_model_id')
                ->label('Modelo Drive Unit')
                ->relationship('driveUnitModel', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('component_type') === 'drive_unit')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->manufacturer} {$record->name}"),

            Forms\Components\TextInput::make('priority')
                ->label('Prioridad (menor = más específico)')
                ->numeric()
                ->default(100),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('component_type')->label('Tipo')->sortable(),
            Tables\Columns\TextColumn::make('templateVersion.template.name')->label('Plantilla')->wrap(),
            Tables\Columns\TextColumn::make('templateVersion.version')->label('Versión')->sortable(),
            Tables\Columns\TextColumn::make('priority')->label('Prioridad')->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTemplateAssignments::route('/'),
        ];
    }
}
