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

            Forms\Components\Section::make('Constructor de plantilla')
                ->description('Arrastra para reordenar. Añade secciones y campos según necesites.')
                ->schema([
                    Forms\Components\Repeater::make('schema.sections')
                        ->label('Secciones')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Título de la sección')
                                ->required()
                                ->placeholder('ej: Inspección General'),

                            Forms\Components\Textarea::make('description')
                                ->label('Descripción')
                                ->rows(2)
                                ->placeholder('Descripción breve de esta sección'),

                            Forms\Components\Repeater::make('fields')
                                ->label('Campos')
                                ->schema([
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('key')
                                            ->label('ID del campo')
                                            ->required()
                                            ->helperText('Identificador único (sin espacios, usar _ o -)')
                                            ->placeholder('ej: estado_general')
                                            ->regex('/^[a-z0-9_-]+$/'),

                                        Forms\Components\TextInput::make('label')
                                            ->label('Etiqueta')
                                            ->required()
                                            ->placeholder('ej: Estado general'),
                                    ]),

                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('Tipo de campo')
                                            ->options([
                                                'text' => 'Texto',
                                                'number' => 'Número',
                                                'date' => 'Fecha',
                                                'textarea' => 'Área de texto',
                                                'select' => 'Selección',
                                                'tristate' => 'Tres estados (OK/Mal/N/A)',
                                                'table' => 'Tabla',
                                            ])
                                            ->required()
                                            ->reactive()
                                            ->default('text'),

                                        Forms\Components\Toggle::make('required')
                                            ->label('Campo obligatorio')
                                            ->default(false),

                                        Forms\Components\Toggle::make('with_observation')
                                            ->label('Con observaciones')
                                            ->helperText('Añade campo de texto adicional')
                                            ->default(false),
                                    ]),

                                    // Opciones para campos tipo 'select'
                                    Forms\Components\Repeater::make('options')
                                        ->label('Opciones')
                                        ->schema([
                                            Forms\Components\TextInput::make('value')
                                                ->label('Valor')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required(),
                                        ])
                                        ->columns(2)
                                        ->visible(fn ($get) => $get('type') === 'select')
                                        ->helperText('Define las opciones disponibles para el campo'),

                                    // Configuración para campos tipo 'table'
                                    Forms\Components\Repeater::make('columns')
                                        ->label('Columnas de la tabla')
                                        ->schema([
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Título')
                                                ->required(),
                                            Forms\Components\Select::make('type')
                                                ->label('Tipo')
                                                ->options([
                                                    'text' => 'Texto',
                                                    'number' => 'Número',
                                                    'select' => 'Selección',
                                                ])
                                                ->default('text'),
                                        ])
                                        ->columns(3)
                                        ->visible(fn ($get) => $get('type') === 'table')
                                        ->helperText('Define las columnas de la tabla'),

                                    Forms\Components\TextInput::make('placeholder')
                                        ->label('Texto de ayuda')
                                        ->placeholder('ej: Introduce el valor en mm'),

                                    Forms\Components\Textarea::make('help_text')
                                        ->label('Texto de ayuda adicional')
                                        ->rows(2)
                                        ->placeholder('Instrucciones detalladas para el técnico'),
                                ])
                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                ->collapsible()
                                ->reorderable()
                                ->addActionLabel('Añadir campo')
                                ->defaultItems(0),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsible()
                        ->reorderable()
                        ->addActionLabel('Añadir sección')
                        ->defaultItems(1)
                        ->default([[
                            'title' => 'Inspección General',
                            'description' => 'Verificaciones básicas del componente',
                            'fields' => [[
                                'key' => 'estado_general',
                                'label' => 'Estado general',
                                'type' => 'tristate',
                                'required' => true,
                                'with_observation' => true,
                            ]],
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
                    ->modalWidth('5xl'),
                Tables\Actions\EditAction::make()
                    ->modalWidth('5xl'),
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
