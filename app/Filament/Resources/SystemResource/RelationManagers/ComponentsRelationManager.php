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
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
                Tables\Columns\TextColumn::make('label')->label('Etiqueta')->searchable(),
                Tables\Columns\TextColumn::make('componentModel.name')->label('Modelo')->searchable(),
                Tables\Columns\TextColumn::make('serial_number')->label('Serial')->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
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
                            ->options(function (Get $get) {
                                $owner = $this->getOwnerRecord();
                                $manufacturerId = $owner?->manufacturer_id;

                                $q = ComponentModel::query();

                                if ($manufacturerId) {
                                    $q->where('manufacturer_id', $manufacturerId);
                                }

                                if ($get('type')) {
                                    $q->where('type', $get('type'));
                                }

                                return $q->orderBy('name')->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('label')
                            ->label('Etiqueta (editable)')
                            ->helperText('Ej: IRB2600 #1, IRC5 Drive Unit #2, etc.')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')->maxLength(255),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
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
                            ->options(function (Get $get) {
                                $owner = $this->getOwnerRecord();
                                $manufacturerId = $owner?->manufacturer_id;

                                $q = ComponentModel::query();

                                if ($manufacturerId) {
                                    $q->where('manufacturer_id', $manufacturerId);
                                }

                                if ($get('type')) {
                                    $q->where('type', $get('type'));
                                }

                                return $q->orderBy('name')->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('label')
                            ->label('Etiqueta (editable)')
                            ->helperText('Ej: IRB2600 #1, IRC5 Drive Unit #2, etc.')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')->maxLength(255),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
