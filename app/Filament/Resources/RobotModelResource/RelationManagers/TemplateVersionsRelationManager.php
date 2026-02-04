<?php

namespace App\Filament\Resources\RobotModelResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TemplateVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'templateVersions';
    protected static ?string $title = 'Plantillas de mantenimiento';
    protected static ?string $modelLabel = 'Plantilla';
    protected static ?string $pluralModelLabel = 'Plantillas';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('version')
                    ->label('Versión')
                    ->numeric()
                    ->default(function ($livewire) {
                        $maxVersion = $livewire->getOwnerRecord()
                            ->templateVersions()
                            ->max('version');
                        return ($maxVersion ?? 0) + 1;
                    })
                    ->required()
                    ->minValue(1),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'active' => 'Activa',
                        'deprecated' => 'Obsoleta',
                    ])
                    ->default('draft')
                    ->required(),

                Forms\Components\Placeholder::make('info')
                    ->label('')
                    ->content('💡 La plantilla activa se usará en nuevos informes'),
            ]),

            Forms\Components\Textarea::make('notes')
                ->label('Notas')
                ->rows(2)
                ->columnSpanFull()
                ->helperText('Notas internas sobre esta versión de la plantilla'),

            Forms\Components\Section::make('Constructor Visual de Plantilla')
                ->description('🎨 Construye tu formulario añadiendo bloques organizados por nivel de mantenimiento.')
                ->schema([
                    Forms\Components\Repeater::make('schema.sections')
                        ->label('Secciones del formulario')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('📋 Título de la sección')
                                ->required()
                                ->placeholder('ej: Inspección General'),

                            Forms\Components\Textarea::make('description')
                                ->label('Descripción (opcional)')
                                ->rows(2)
                                ->placeholder('Breve descripción de esta sección'),

                            Forms\Components\Builder::make('fields')
                                ->label('Bloques de campos')
                                ->blockNumbers(false)
                                ->addActionLabel('➕ Añadir bloque')
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
                                ->blocks([
                                    // BLOQUE: Texto corto
                                    Forms\Components\Builder\Block::make('text')
                                        ->label('📝 Texto corto')
                                        ->icon('heroicon-o-pencil')
                                        ->schema([
                                            Forms\Components\Select::make('category')
                                                ->label('🏷️ Nivel de mantenimiento')
                                                ->options([
                                                    'general' => 'General (todos los niveles)',
                                                    'level1' => 'Nivel 1',
                                                    'level2' => 'Nivel 2',
                                                    'level3' => 'Nivel 3',
                                                ])
                                                ->default('general')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required()
                                                ->placeholder('¿Qué pregunta hacemos?'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->placeholder('ej: temperatura_aceite')
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Obligatorio')
                                                    ->default(false),
                                                Forms\Components\Toggle::make('with_observation')
                                                    ->label('Con observaciones')
                                                    ->default(false),
                                            ]),
                                            Forms\Components\TextInput::make('placeholder')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Aparece dentro del campo'),
                                        ]),

                                    // BLOQUE: Número
                                    Forms\Components\Builder\Block::make('number')
                                        ->label('🔢 Número')
                                        ->icon('heroicon-o-hashtag')
                                        ->schema([
                                            Forms\Components\Select::make('category')
                                                ->label('🏷️ Nivel de mantenimiento')
                                                ->options([
                                                    'general' => 'General',
                                                    'level1' => 'Nivel 1',
                                                    'level2' => 'Nivel 2',
                                                    'level3' => 'Nivel 3',
                                                ])
                                                ->default('general')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required()
                                                ->placeholder('¿Qué medimos?'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Obligatorio'),
                                                Forms\Components\Toggle::make('with_observation')
                                                    ->label('Con observaciones'),
                                            ]),
                                            Forms\Components\TextInput::make('placeholder')
                                                ->label('Unidad de medida')
                                                ->placeholder('ej: mm, kg, ºC'),
                                        ]),

                                    // BLOQUE: Fecha
                                    Forms\Components\Builder\Block::make('date')
                                        ->label('📅 Fecha')
                                        ->icon('heroicon-o-calendar')
                                        ->schema([
                                            Forms\Components\Select::make('category')
                                                ->label('🏷️ Nivel de mantenimiento')
                                                ->options([
                                                    'general' => 'General',
                                                    'level1' => 'Nivel 1',
                                                    'level2' => 'Nivel 2',
                                                    'level3' => 'Nivel 3',
                                                ])
                                                ->default('general')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required(),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Toggle::make('required')
                                                ->label('Obligatorio'),
                                        ]),

                                    // BLOQUE: Texto largo
                                    Forms\Components\Builder\Block::make('textarea')
                                        ->label('📄 Texto largo')
                                        ->icon('heroicon-o-document-text')
                                        ->schema([
                                            Forms\Components\Select::make('category')
                                                ->label('🏷️ Nivel de mantenimiento')
                                                ->options([
                                                    'general' => 'General',
                                                    'level1' => 'Nivel 1',
                                                    'level2' => 'Nivel 2',
                                                    'level3' => 'Nivel 3',
                                                ])
                                                ->default('general')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required(),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Toggle::make('required')
                                                ->label('Obligatorio'),
                                            Forms\Components\TextInput::make('placeholder')
                                                ->label('Texto de ayuda'),
                                        ]),

                                    // BLOQUE: Lista desplegable
                                    Forms\Components\Builder\Block::make('select')
                                        ->label('📑 Lista desplegable')
                                        ->icon('heroicon-o-queue-list')
                                        ->schema([
                                            Forms\Components\Select::make('category')
                                                ->label('🏷️ Nivel de mantenimiento')
                                                ->options([
                                                    'general' => 'General',
                                                    'level1' => 'Nivel 1',
                                                    'level2' => 'Nivel 2',
                                                    'level3' => 'Nivel 3',
                                                ])
                                                ->default('general')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required(),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Toggle::make('required')
                                                ->label('Obligatorio'),
                                            Forms\Components\Repeater::make('options')
                                                ->label('Opciones disponibles')
                                                ->schema([
                                                    Forms\Components\TextInput::make('value')
                                                        ->label('Valor')
                                                        ->required(),
                                                    Forms\Components\TextInput::make('label')
                                                        ->label('Texto')
                                                        ->required(),
                                                ])
                                                ->columns(2)
                                                ->defaultItems(2)
                                                ->collapsible()
                                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Opción'),
                                        ]),

                                    // BLOQUE: Tres estados (OK/Mal/N/A)
                                    Forms\Components\Builder\Block::make('tristate')
                                        ->label('✓✗ Tres estados')
                                        ->icon('heroicon-o-check-circle')
                                        ->schema([
                                            Forms\Components\Select::make('category')
                                                ->label('🏷️ Nivel de mantenimiento')
                                                ->options([
                                                    'general' => 'General',
                                                    'level1' => 'Nivel 1',
                                                    'level2' => 'Nivel 2',
                                                    'level3' => 'Nivel 3',
                                                ])
                                                ->default('general')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('¿Qué verificamos?')
                                                ->required()
                                                ->placeholder('ej: Estado del cableado'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Obligatorio')
                                                    ->default(true),
                                                Forms\Components\Toggle::make('with_observation')
                                                    ->label('Con campo de observaciones')
                                                    ->default(true)
                                                    ->helperText('Añade un área de texto'),
                                            ]),
                                            Forms\Components\Placeholder::make('preview')
                                                ->label('')
                                                ->content('✓ OK  |  ✗ Mal  |  - N/A + Observaciones'),
                                        ]),

                                    // BLOQUE: Tabla con campos anidados
                                    Forms\Components\Builder\Block::make('table')
                                        ->label('📊 Tabla de datos')
                                        ->icon('heroicon-o-table-cells')
                                        ->schema([
                                            Forms\Components\Select::make('category')
                                                ->label('🏷️ Nivel de mantenimiento')
                                                ->options([
                                                    'general' => 'General',
                                                    'level1' => 'Nivel 1',
                                                    'level2' => 'Nivel 2',
                                                    'level3' => 'Nivel 3',
                                                ])
                                                ->default('general')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Título de la tabla')
                                                ->required()
                                                ->placeholder('ej: Lista de componentes revisados'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),

                                            Forms\Components\Repeater::make('columns')
                                                ->label('📊 Columnas de la tabla')
                                                ->schema([
                                                    Forms\Components\Select::make('type')
                                                        ->label('Tipo de campo')
                                                        ->options([
                                                            'text' => '📝 Texto',
                                                            'number' => '🔢 Número',
                                                            'date' => '📅 Fecha',
                                                            'select' => '📑 Lista desplegable',
                                                            'tristate' => '✓✗ Tres estados (OK/Mal/N/A)',
                                                        ])
                                                        ->default('text')
                                                        ->required()
                                                        ->live()
                                                        ->afterStateUpdated(function ($state, callable $set) {
                                                            // Reset conditional fields when type changes
                                                            if ($state !== 'number') $set('unit', null);
                                                            if ($state !== 'select') $set('options', null);
                                                        }),

                                                    Forms\Components\TextInput::make('label')
                                                        ->label('Encabezado de columna')
                                                        ->required()
                                                        ->placeholder('ej: Componente, Medida, Estado...'),

                                                    Forms\Components\TextInput::make('key')
                                                        ->label('ID interno')
                                                        ->required()
                                                        ->regex('/^[a-z0-9_-]+$/')
                                                        ->placeholder('ej: componente, medida_mm'),

                                                    // Campos condicionales según el tipo
                                                    Forms\Components\TextInput::make('placeholder')
                                                        ->label('Texto de ayuda')
                                                        ->placeholder('Aparece dentro del campo')
                                                        ->visible(fn (callable $get) => in_array($get('type'), ['text', 'number'])),

                                                    Forms\Components\TextInput::make('unit')
                                                        ->label('Unidad de medida')
                                                        ->placeholder('ej: mm, kg, ºC')
                                                        ->visible(fn (callable $get) => $get('type') === 'number'),

                                                    Forms\Components\Repeater::make('options')
                                                        ->label('Opciones disponibles')
                                                        ->simple(
                                                            Forms\Components\TextInput::make('value')
                                                                ->required()
                                                                ->placeholder('Opción')
                                                        )
                                                        ->defaultItems(2)
                                                        ->visible(fn (callable $get) => $get('type') === 'select')
                                                        ->columns(1),
                                                ])
                                                ->itemLabel(fn (array $state): ?string =>
                                                    ($state['label'] ?? 'Nueva columna') .
                                                    ' (' . match($state['type'] ?? 'text') {
                                                        'text' => '📝',
                                                        'number' => '🔢',
                                                        'date' => '📅',
                                                        'select' => '📑',
                                                        'tristate' => '✓✗',
                                                        default => '📝'
                                                    } . ')'
                                                )
                                                ->collapsible()
                                                ->collapsed()
                                                ->reorderable()
                                                ->cloneable()
                                                ->defaultItems(2)
                                                ->helperText('El técnico podrá añadir múltiples filas con estos campos en la tabla')
                                                ->columnSpanFull(),
                                        ]),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Nueva sección')
                        ->collapsed()
                        ->collapsible()
                        ->reorderable()
                        ->cloneable()
                        ->addActionLabel('➕ Añadir sección')
                        ->defaultItems(1)
                        ->default([[
                            'title' => 'Inspección General',
                            'description' => 'Verificaciones básicas del componente',
                            'fields' => [
                                [
                                    'type' => 'tristate',
                                    'data' => [
                                        'category' => 'general',
                                        'key' => 'estado_general',
                                        'label' => 'Estado general',
                                        'required' => true,
                                        'with_observation' => true,
                                    ],
                                ],
                            ],
                        ]]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->label('Versión')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        'deprecated' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activa',
                        'draft' => 'Borrador',
                        'deprecated' => 'Obsoleta',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('version', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva plantilla'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalWidth('7xl'),
                Tables\Actions\EditAction::make()
                    ->modalWidth('7xl'),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
