<?php

namespace App\Filament\Resources;

use App\Models\ComponentModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

abstract class ComponentModelBaseResource extends Resource
{
    protected static ?string $model = ComponentModel::class;

    /**
     * Cada Resource hijo debe devolver:
     * - controller
     * - drive_unit
     * - mechanical_unit
     */
    abstract public static function componentType(): string;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', static::componentType())
            ->orderBy('model_name');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('manufacturer_id')
                ->relationship('manufacturer', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('model_name')
                ->label('Modelo')
                ->maxLength(255)
                ->required(),

            Forms\Components\TextInput::make('variant')
                ->label('Variante')
                ->maxLength(255)
                ->helperText('Opcional: IRC5 Single Cabinet, Omnicore, KRC4, etc.'),

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
                Tables\Columns\TextColumn::make('manufacturer.name')
                    ->label('Fabricante')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_name')
                    ->label('Modelo')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('variant')
                    ->label('Variante')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('manufacturer_id')
                    ->relationship('manufacturer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('model_name');
    }
}
