<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterventionComponentResource\Pages;
use App\Models\InterventionComponent;
use App\Support\SchemaToFilament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InterventionComponentResource extends Resource
{
    protected static ?string $model = InterventionComponent::class;
    protected static ?string $navigationGroup = 'Intervenciones';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Componente de informe';
    protected static ?string $pluralModelLabel = 'Componentes de informe';

    // Si luego quieres ocultarlo del menú, cambia a false
    // protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Info')->schema([
                Forms\Components\TextInput::make('intervention_id')->label('Intervención')->disabled(),
                Forms\Components\TextInput::make('component_type')->label('Tipo')->disabled(),
                Forms\Components\TextInput::make('label')->label('Etiqueta')->disabled(),
            ])->columns(3),

            Forms\Components\Group::make()
                ->schema(function (Get $get) {
                    $schema = $get('schema_json') ?? [];
                    return SchemaToFilament::build(is_array($schema) ? $schema : [], 'data_json');
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('intervention.system.name')->label('Sistema')->searchable(),
            Tables\Columns\TextColumn::make('intervention.type')->label('Tipo intervención')->sortable(),
            Tables\Columns\TextColumn::make('component_type')->label('Componente')->sortable(),
            Tables\Columns\TextColumn::make('label')->label('Etiqueta')->sortable(),
            Tables\Columns\TextColumn::make('templateVersion.template.name')->label('Plantilla')->wrap(),
            Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make()->label('Rellenar'),
        ])
        ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInterventionComponents::route('/'),
        ];
    }
}
