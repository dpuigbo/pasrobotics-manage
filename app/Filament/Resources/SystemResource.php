<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemResource\Pages;
use App\Filament\Resources\SystemResource\RelationManagers\ComponentsRelationManager;
use App\Models\System;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemResource extends Resource
{
    protected static ?string $model = System::class;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Operación';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('client_id')
                ->relationship('client', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('plant_id')
                ->relationship('plant', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Select::make('machine_id')
                ->relationship('machine', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Select::make('manufacturer')
                ->options(config('manufacturers.list'))
                ->required()
                ->reactive(),

            Forms\Components\TextInput::make('name')
                ->label('Nombre del sistema')
                ->maxLength(255)
                ->required(),

            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display_name')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('manufacturer')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('client.name')->label('Cliente')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('plant.name')->label('Planta')->toggleable(),
                Tables\Columns\TextColumn::make('machine.name')->label('Máquina')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')->relationship('client', 'name')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('manufacturer')->options(config('manufacturers.list')),
                Tables\Filters\SelectFilter::make('plant_id')->relationship('plant', 'name')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('machine_id')->relationship('machine', 'name')->searchable()->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ComponentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystems::route('/'),
            'create' => Pages\CreateSystem::route('/create'),
            'edit' => Pages\EditSystem::route('/{record}/edit'),
        ];
    }
}
