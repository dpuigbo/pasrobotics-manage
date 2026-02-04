<?php

namespace App\Filament\Resources\ControllerModelResource\RelationManagers;

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
                ->description('🎨 Construye tu formulario añadiendo bloques. Arrastra para reordenar.')
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
                                ->blocks([
                                    // BLOQUE: Texto corto
                                    Forms\Components\Builder\Block::make('text')
                                        ->label('📝 Texto corto')
                                        ->icon('heroicon-o-pencil')
                                        ->schema([
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
                                        ])
                                        ->columns(1),

                                    // BLOQUE: Número
                                    Forms\Components\Builder\Block::make('number')
                                        ->label('🔢 Número')
                                        ->icon('heroicon-o-hashtag')
                                        ->schema([
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

                                    // BLOQUE: Tabla
                                    Forms\Components\Builder\Block::make('table')
                                        ->label('📊 Tabla de datos')
                                        ->icon('heroicon-o-table-cells')
                                        ->schema([
                                            Forms\Components\TextInput::make('label')
                                                ->label('Título de la tabla')
                                                ->required()
                                                ->placeholder('ej: Lista de componentes revisados'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Repeater::make('columns')
                                                ->label('Columnas de la tabla')
                                                ->schema([
                                                    Forms\Components\TextInput::make('label')
                                                        ->label('📌 Encabezado')
                                                        ->required()
                                                        ->placeholder('ej: Componente'),
                                                    Forms\Components\TextInput::make('key')
                                                        ->label('🔑 ID')
                                                        ->required()
                                                        ->regex('/^[a-z0-9_-]+$/')
                                                        ->placeholder('ej: componente'),
                                                    Forms\Components\Select::make('type')
                                                        ->label('Tipo')
                                                        ->options([
                                                            'text' => 'Texto',
                                                            'number' => 'Número',
                                                        ])
                                                        ->default('text'),
                                                ])
                                                ->columns(3)
                                                ->defaultItems(2)
                                                ->collapsible()
                                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Columna')
                                                ->helperText('El técnico podrá añadir múltiples filas'),
                                        ]),
                                ])
                                ->blockNumbers(false)
                                ->addActionLabel('➕ Añadir bloque')
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
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
