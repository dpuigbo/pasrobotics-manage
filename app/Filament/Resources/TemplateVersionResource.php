<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateVersionResource\Pages;
use App\Models\TemplateVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TemplateVersionResource extends Resource
{
    protected static ?string $model = TemplateVersion::class;
    protected static ?string $navigationGroup = 'Constructor';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Versión de plantilla';
    protected static ?string $pluralModelLabel = 'Versiones de plantilla';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificación')->schema([
                Forms\Components\Select::make('template_id')
                    ->label('Plantilla')
                    ->relationship('template', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('version')
                    ->label('Versión')
                    ->required()
                    ->placeholder('v1')
                    ->maxLength(20),

                Forms\Components\Toggle::make('is_published')->label('Publicada'),
            ])->columns(3),

            Forms\Components\Section::make('Constructor (schema)')
                ->description('Define secciones y campos. Esto genera el formulario web y también el PDF.')
                ->schema([
                    Forms\Components\Repeater::make('schema_json.sections')
                        ->label('Secciones')
                        ->defaultItems(1)
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Título de sección')
                                ->required()
                                ->maxLength(200),

                            Forms\Components\Textarea::make('description')
                                ->label('Descripción')
                                ->rows(2)
                                ->columnSpanFull(),

                            Forms\Components\Toggle::make('repeatable')
                                ->label('Repetible')
                                ->helperText('Actívalo si esta sección se repite por unidad (ej: una por robot).')
                                ->reactive(),

                            Forms\Components\TextInput::make('repeat_key')
                                ->label('Clave de repetición')
                                ->placeholder('mechanical_unit')
                                ->visible(fn (Get $get) => (bool) $get('repeatable'))
                                ->helperText('Ejemplos: mechanical_unit, drive_unit')
                                ->maxLength(50),

                            Forms\Components\Repeater::make('fields')
                                ->label('Campos')
                                ->defaultItems(1)
                                ->schema([
                                    Forms\Components\TextInput::make('key')
                                        ->label('Key (ID)')
                                        ->required()
                                        ->helperText('Sin espacios. Ej: estado_frenos, aceite_eje_1')
                                        ->maxLength(80),

                                    Forms\Components\TextInput::make('label')
                                        ->label('Etiqueta')
                                        ->required()
                                        ->maxLength(200),

                                    Forms\Components\Select::make('type')
                                        ->label('Tipo')
                                        ->required()
                                        ->options([
                                            'text'     => 'Texto',
                                            'textarea' => 'Texto largo',
                                            'number'   => 'Número',
                                            'date'     => 'Fecha',
                                            'select'   => 'Selector',
                                            'tristate' => 'OK / NOK / N/A (+ observación)',
                                            'table'    => 'Tabla',
                                        ])
                                        ->reactive(),

                                    Forms\Components\Toggle::make('required')->label('Obligatorio')->default(false),

                                    Forms\Components\TextInput::make('help')
                                        ->label('Ayuda')
                                        ->maxLength(200),

                                    // Opciones para select
                                    Forms\Components\Repeater::make('options')
                                        ->label('Opciones')
                                        ->schema([
                                            Forms\Components\TextInput::make('value')->label('Valor')->required(),
                                            Forms\Components\TextInput::make('label')->label('Etiqueta')->required(),
                                        ])
                                        ->columns(2)
                                        ->visible(fn (Get $get) => $get('type') === 'select'),

                                    // Config para tristate
                                    Forms\Components\Toggle::make('with_observation')
                                        ->label('Añadir observación')
                                        ->default(true)
                                        ->visible(fn (Get $get) => $get('type') === 'tristate'),

                                    // Config para tabla
                                    Forms\Components\Repeater::make('columns')
                                        ->label('Columnas (tabla)')
                                        ->schema([
                                            Forms\Components\TextInput::make('key')->label('Key')->required(),
                                            Forms\Components\TextInput::make('label')->label('Etiqueta')->required(),
                                            Forms\Components\Select::make('type')->label('Tipo')->options([
                                                'text' => 'Texto',
                                                'number' => 'Número',
                                                'select' => 'Selector',
                                            ])->required(),
                                        ])
                                        ->columns(3)
                                        ->visible(fn (Get $get) => $get('type') === 'table'),

                                    Forms\Components\Repeater::make('rows')
                                        ->label('Filas (tabla)')
                                        ->schema([
                                            Forms\Components\TextInput::make('key')->label('Key')->required(),
                                            Forms\Components\TextInput::make('label')->label('Etiqueta')->required(),
                                        ])
                                        ->columns(2)
                                        ->helperText('En v1 las filas son estáticas. Luego las haremos dinámicas por ejes (1..6).')
                                        ->visible(fn (Get $get) => $get('type') === 'table'),
                                ])
                                ->columns(2)
                                ->collapsed()
                                ->columnSpanFull(),
                        ])
                        ->collapsed()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('template.name')->label('Plantilla')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('version')->label('Versión')->sortable(),
            Tables\Columns\IconColumn::make('is_published')->label('Publicada')->boolean(),
            Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTemplateVersions::route('/'),
        ];
    }
}
