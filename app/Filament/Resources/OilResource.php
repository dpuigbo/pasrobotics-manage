<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OilResource\Pages;
use App\Models\Oil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OilResource extends Resource
{
    protected static ?string $model = Oil::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $navigationLabel = 'Aceites';
    protected static ?string $modelLabel = 'Aceite';
    protected static ?string $pluralModelLabel = 'Aceites';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre del aceite')
                ->required()
                ->maxLength(255)
                ->placeholder('ej: Kyodo Yushi TMO 150'),

            Forms\Components\TextInput::make('manufacturer')
                ->label('Fabricante')
                ->maxLength(255)
                ->placeholder('ej: Kyodo Yushi'),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('cost_per_liter')
                    ->label('Coste por litro (€)')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->minValue(0)
                    ->placeholder('0.00'),

                Forms\Components\TextInput::make('selling_price_per_liter')
                    ->label('Precio de venta por litro (€)')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->minValue(0)
                    ->placeholder('0.00'),
            ]),

            Forms\Components\Textarea::make('specifications')
                ->label('Especificaciones técnicas')
                ->rows(3)
                ->placeholder('ej: Viscosidad ISO VG 150, temperatura de trabajo -20°C a +120°C')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Fabricante'),

                Tables\Columns\TextColumn::make('cost_per_liter')
                    ->label('Coste/L')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('€'),

                Tables\Columns\TextColumn::make('selling_price_per_liter')
                    ->label('Precio venta/L')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('€'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOils::route('/'),
            'create' => Pages\CreateOil::route('/create'),
            'edit' => Pages\EditOil::route('/{record}/edit'),
        ];
    }
}
