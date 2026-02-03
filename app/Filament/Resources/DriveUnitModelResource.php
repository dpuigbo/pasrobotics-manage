<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriveUnitModelResource\Pages;
use App\Filament\Resources\DriveUnitModelResource\RelationManagers;
use App\Models\DriveUnitModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DriveUnitModelResource extends Resource
{
    protected static ?string $model = DriveUnitModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Catálogos';
    protected static ?string $modelLabel = 'Modelo de drive unit';
    protected static ?string $pluralModelLabel = 'Modelos de drive unit';

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
            // Temporarily disabled for debugging
            // RelationManagers\TemplateVersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDriveUnitModels::route('/'),
            'create' => Pages\CreateDriveUnitModel::route('/create'),
            'edit'   => Pages\EditDriveUnitModel::route('/{record}/edit'),
        ];
    }
}
