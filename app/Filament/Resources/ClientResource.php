<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Operación';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información general')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del cliente')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('site')
                        ->label('Sede / Ubicación')
                        ->maxLength(255)
                        ->nullable(),
                ])->columns(2),

            Forms\Components\Section::make('Datos de desplazamiento')
                ->schema([
                    Forms\Components\TextInput::make('km')
                        ->label('Kilómetros')
                        ->numeric()
                        ->suffix('km')
                        ->nullable(),

                    Forms\Components\TextInput::make('travel_hours')
                        ->label('Horas de desplazamiento')
                        ->numeric()
                        ->suffix('h')
                        ->step(0.5)
                        ->nullable(),

                    Forms\Components\TextInput::make('travel_days')
                        ->label('Días de desplazamiento')
                        ->numeric()
                        ->suffix('días')
                        ->step(0.5)
                        ->nullable(),

                    Forms\Components\TextInput::make('tolls')
                        ->label('Peajes')
                        ->numeric()
                        ->prefix('€')
                        ->step(0.01)
                        ->nullable(),
                ])->columns(2),

            Forms\Components\Section::make('Tarifas')
                ->schema([
                    Forms\Components\TextInput::make('work_hour_rate')
                        ->label('Tarifa hora trabajo')
                        ->numeric()
                        ->prefix('€')
                        ->suffix('/h')
                        ->step(0.01)
                        ->nullable(),

                    Forms\Components\TextInput::make('travel_hour_rate')
                        ->label('Tarifa hora desplazamiento')
                        ->numeric()
                        ->prefix('€')
                        ->suffix('/h')
                        ->step(0.01)
                        ->nullable(),

                    Forms\Components\TextInput::make('diet_rate')
                        ->label('Dieta')
                        ->numeric()
                        ->prefix('€')
                        ->suffix('/día')
                        ->step(0.01)
                        ->nullable(),

                    Forms\Components\TextInput::make('access_mgmt_fee')
                        ->label('Gestión de acceso')
                        ->numeric()
                        ->prefix('€')
                        ->step(0.01)
                        ->nullable(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('site')
                    ->label('Sede')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('km')
                    ->label('Km')
                    ->suffix(' km')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('work_hour_rate')
                    ->label('€/h trabajo')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('€')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('travel_hour_rate')
                    ->label('€/h desplaz.')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('€')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('diet_rate')
                    ->label('Dieta')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('€')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit'   => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
