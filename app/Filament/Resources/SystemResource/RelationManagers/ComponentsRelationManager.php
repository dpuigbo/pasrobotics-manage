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
                Tables\Columns\TextColumn::make('componentModel.model_name')->label('Modelo')->searchable(),
                Tables\Columns\TextColumn::make('serial_number')->label('Serial')->toggleable(),
                Tables\Columns\TextColumn::make('axes_count')->label('Ejes')->toggleable(),
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
                            ->searchable()
                            ->preload()
                            ->options(function (Get $get) {
                                $system = $this->getOwnerRecord();
                                $q = ComponentModel::query();

                                // si System ya tiene manufacturer_id úsalo (recomendado)
                                if (isset($system->manufacturer_id) && $system->manufacturer_id) {
                                    $q->where('manufacturer_id', $system->manufacturer_id);
                                }

                                if ($get('type')) {
                                    $q->where('type', $get('type'));
                                }

                                return $q
                                    ->orderBy('model_name')
                                    ->pluck('model_name', 'id');
                            })
                            ->required(),

                        Forms\Components\TextInput::make('label')
                            ->label('Etiqueta (editable)')
                            ->helperText('Ej: IRB2600 #1, IRC5 Cabinet, Drive Unit #2...')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')->maxLength(255),

                        Forms\Components\TextInput::make('axes_count')
                            ->numeric()
                            ->minValue(0)
                            ->label('Nº ejes (si aplica)'),

                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
