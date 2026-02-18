<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsumableResource\Pages;
use App\Models\Consumable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConsumableResource extends Resource
{
    protected static ?string $model = Consumable::class;
    protected static ?string $navigationIcon = 'heroicon-o-battery-100';
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $navigationLabel = 'Consumibles';
    protected static ?string $modelLabel = 'Consumible';
    protected static ?string $pluralModelLabel = 'Consumibles';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre del consumible')
                ->required()
                ->maxLength(255)
                ->placeholder('ej: Batería SMB'),

            Forms\Components\TextInput::make('reference')
                ->label('Referencia')
                ->maxLength(255)
                ->placeholder('ej: 3HAC16831-1'),

            Forms\Components\TextInput::make('manufacturer')
                ->label('Fabricante')
                ->maxLength(255)
                ->placeholder('ej: ABB'),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('cost')
                    ->label('Coste (€)')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->minValue(0)
                    ->placeholder('0.00'),

                Forms\Components\TextInput::make('selling_price')
                    ->label('Precio de venta (€)')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->minValue(0)
                    ->placeholder('0.00'),

                Forms\Components\Select::make('unit_type')
                    ->label('Tipo de unidad')
                    ->options([
                        'piece' => 'Unidad',
                        'pack' => 'Paquete',
                        'liter' => 'Litro',
                        'kg' => 'Kilogramo',
                        'meter' => 'Metro',
                        'box' => 'Caja',
                    ])
                    ->default('piece')
                    ->required(),
            ])->columnSpanFull(),

            Forms\Components\Textarea::make('notes')
                ->label('Notas')
                ->rows(3)
                ->placeholder('Información adicional sobre el consumible')
                ->columnSpanFull(),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('reference')
                    ->label('Referencia')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Fabricante')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('unit_type')
                    ->label('Unidad')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('cost')
                    ->label('Coste')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Precio venta')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_type')
                    ->label('Tipo de unidad')
                    ->options([
                        'piece' => 'Unidad',
                        'pack' => 'Paquete',
                        'liter' => 'Litro',
                        'kg' => 'Kilogramo',
                        'meter' => 'Metro',
                        'box' => 'Caja',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsumables::route('/'),
            'create' => Pages\CreateConsumable::route('/create'),
            'edit' => Pages\EditConsumable::route('/{record}/edit'),
        ];
    }
}
