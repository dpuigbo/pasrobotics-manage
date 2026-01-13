<?php

namespace App\Filament\Resources\SystemResource\RelationManagers;

use App\Models\ComponentModel;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ComponentsRelationManager extends RelationManager
{
    protected static string $relationship = 'components';
    protected static ?string $title = 'Componentes del sistema';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
                Tables\Columns\TextColumn::make('label')->label('Etiqueta')->searchable(),
                Tables\Columns\TextColumn::make('componentModel.name')->label('Modelo')->searchable(),
                Tables\Columns\TextColumn::make('serial_number')->label('Serial')->toggleable()->searchable(),
                Tables\Columns\TextColumn::make('axes_count')->label('Ejes')->toggleable()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Añadir componente')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'controller' => 'Controladora',
                                'mechanical_unit' => 'Unidad mecánica',
                                'drive_unit' => 'Drive unit',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('component_model_id')
                            ->label('Modelo (catálogo)')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(function (Get $get) {
                                $system = $this->getOwnerRecord();

                                $q = ComponentModel::query();

                                // Filtrar por fabricante del sistema
                                if ($system?->manufacturer_id) {
                                    $q->where('manufacturer_id', $system->manufacturer_id);
                                }

                                // Filtrar por tipo elegido en el formulario
                                if ($get('type')) {
                                    $q->where('type', $get('type'));
                                }

                                return $q->orderBy('name')->pluck('name', 'id');
                            }),

                        Forms\Components\TextInput::make('label')
                            ->label('Etiqueta (editable)')
                            ->helperText('Ej: ROB_1, ROB_2, Cabinet, DU_1...')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial number')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('axes_count')
                            ->label('Número de ejes (si aplica)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'controller' => 'Controladora',
                                'mechanical_unit' => 'Unidad mecánica',
                                'drive_unit' => 'Drive unit',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('component_model_id')
                            ->label('Modelo (catálogo)')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(function (Get $get) {
                                $system = $this->getOwnerRecord();

                                $q = ComponentModel::query();

                                if ($system?->manufacturer_id) {
                                    $q->where('manufacturer_id', $system->manufacturer_id);
                                }

                                if ($get('type')) {
                                    $q->where('type', $get('type'));
                                }

                                return $q->orderBy('name')->pluck('name', 'id');
                            }),

                        Forms\Components\TextInput::make('label')
                            ->label('Etiqueta (editable)')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial number')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('axes_count')
                            ->label('Número de ejes (si aplica)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}

