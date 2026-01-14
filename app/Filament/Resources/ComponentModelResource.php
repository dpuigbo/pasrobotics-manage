<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComponentModelResource\Pages;
use App\Filament\Resources\ComponentModelResource\RelationManagers\TemplateVersionsRelationManager;
use App\Models\ComponentModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComponentModelResource extends Resource
{
    protected static ?string $model = ComponentModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?string $modelLabel = 'Modelo de componente';
    protected static ?string $pluralModelLabel = 'Modelos de componentes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('manufacturer_id')
                ->label('Fabricante')
                ->relationship('manufacturer', 'name', fn ($q) => $q->where('is_active', true)->orderBy('sort'))
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'controller' => 'Controladora',
                    'mechanical_unit' => 'Unidad mecánica',
                    'drive_unit' => 'Drive unit',
                ])
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Nombre del modelo')
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
                Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Modelo')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('manufacturer_id')
                    ->label('Fabricante')
                    ->relationship('manufacturer', 'name', fn ($q) => $q->orderBy('sort'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'controller' => 'Controladora',
                        'mechanical_unit' => 'Unidad mecánica',
                        'drive_unit' => 'Drive unit',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListComponentModels::route('/'),
            'create' => Pages\CreateComponentModel::route('/create'),
            'edit' => Pages\EditComponentModel::route('/{record}/edit'),
        ];
    }
}
