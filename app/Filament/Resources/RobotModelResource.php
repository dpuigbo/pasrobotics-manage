<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RobotModelResource\Pages;
use App\Filament\RelationManagers\TemplateVersionsRelationManager;
use App\Models\MechanicalUnitModel;
use App\Models\Oil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RobotModelResource extends Resource
{
    protected static ?string $model = MechanicalUnitModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $modelLabel = 'Modelo de robot';
    protected static ?string $pluralModelLabel = 'Modelos de robots';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('manufacturer_id')
                ->relationship('manufacturer', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Modelo')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('notes')
                ->columnSpanFull()
                ->nullable(),

            // TEMPORALMENTE DESACTIVADO: Configuración de aceites por eje
            // Se reactivará cuando la tabla oils esté funcionando correctamente
            /*
            Forms\Components\Section::make('Configuración de aceites por eje')
                ->description('Configure el tipo de aceite y volumen para cada eje del robot')
                ->schema([
                    Forms\Components\Repeater::make('axis_oils_config')
                        ->label('Ejes')
                        ->schema([
                            Forms\Components\TextInput::make('axis_number')
                                ->label('Eje')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(6)
                                ->required()
                                ->default(fn ($state, $get, $set, Forms\Get $get_context) =>
                                    count($get_context('../../axis_oils_config') ?? []) + 1
                                ),

                            Forms\Components\Select::make('oil_id')
                                ->label('Tipo de aceite')
                                ->options(fn () => Oil::pluck('name', 'id'))
                                ->searchable()
                                ->nullable()
                                ->placeholder('Seleccione un aceite'),

                            Forms\Components\TextInput::make('volume_ml')
                                ->label('Volumen (ml)')
                                ->numeric()
                                ->suffix('ml')
                                ->minValue(0)
                                ->step(100)
                                ->nullable(),
                        ])
                        ->columns(3)
                        ->defaultItems(6)
                        ->itemLabel(fn (array $state): ?string =>
                            'Eje ' . ($state['axis_number'] ?? '?') .
                            (isset($state['volume_ml']) ? ' • ' . $state['volume_ml'] . 'ml' : '')
                        )
                        ->collapsible()
                        ->reorderable(false)
                        ->deletable(false)
                        ->addable(false)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(fn ($record) => $record !== null)
                ->columnSpanFull(),
            */
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('manufacturer.name')->label('Fabricante')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Modelo')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            TemplateVersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRobotModels::route('/'),
            'create' => Pages\CreateRobotModel::route('/create'),
            'edit'   => Pages\EditRobotModel::route('/{record}/edit'),
        ];
    }
}
