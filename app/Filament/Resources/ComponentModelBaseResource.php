<?php

namespace App\Filament\Resources;

use App\Models\ComponentModel;
use App\Models\Manufacturer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

abstract class ComponentModelBaseResource extends Resource
{
    protected static ?string $model = ComponentModel::class;

    // Cada Resource hijo debe devolver su type: controller | drive_unit | mechanical_unit
    abstract public static function componentType(): string;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', static::componentType());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('manufacturer_id')
                ->label('Fabricante')
                ->relationship('manufacturer', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Hidden::make('type')
                ->default(static::componentType())
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Modelo')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('notes')
                ->label('Notas')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('manufacturer.name')->label('Fabricante')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Modelo')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable()->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }
}
