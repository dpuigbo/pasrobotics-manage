<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComponentModelResource\Pages;
use App\Models\ComponentModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComponentModelResource extends Resource
{
    protected static ?string $model = ComponentModel::class;

    //protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?string $navigationLabel = 'Modelos de Componentes';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('manufacturer')
                ->label('Fabricante')
                ->options(config('manufacturers.list', [
                    'ABB' => 'ABB',
                    'KUKA' => 'KUKA',
                    'FANUC' => 'FANUC',
                    'YASKAWA' => 'Yaskawa',
                ]))
                ->searchable()
                ->required(),

            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'controller' => 'Controladora',
                    'mechanical_unit' => 'Unidad mecánica',
                    'drive_unit' => 'Drive unit',
                ])
                ->required(),

            Forms\Components\TextInput::make('model_name')
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
            // MUY IMPORTANTE: que el title sea un string simple
            ->recordTitleAttribute('model_name')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('manufacturer')->label('Fabricante')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
                Tables\Columns\TextColumn::make('model_name')->label('Modelo')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('manufacturer')
                    ->options(config('manufacturers.list', [
                        'ABB' => 'ABB',
                        'KUKA' => 'KUKA',
                        'FANUC' => 'FANUC',
                        'YASKAWA' => 'Yaskawa',
                    ])),
                Tables\Filters\SelectFilter::make('type')->options([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComponentModels::route('/'),
            'create' => Pages\CreateComponentModel::route('/create'),
            'edit' => Pages\EditComponentModel::route('/{record}/edit'),
        ];
    }
}
