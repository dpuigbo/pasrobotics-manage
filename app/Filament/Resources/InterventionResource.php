<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterventionResource\Pages;
use App\Filament\Resources\InterventionResource\RelationManagers\SystemsRelationManager;
use App\Models\Intervention;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InterventionResource extends Resource
{
    protected static ?string $model = Intervention::class;
    protected static ?string $navigationGroup = 'Intervenciones';
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $modelLabel = 'Intervención';
    protected static ?string $pluralModelLabel = 'Intervenciones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('client_id')
                ->label('Cliente')
                ->relationship('client', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('type')->label('Tipo')->required()->options([
                'preventive' => 'Preventivo',
                'corrective' => 'Correctivo',
            ]),
            Forms\Components\Select::make('status')->label('Estado')->required()->options([
                'draft' => 'Borrador',
                'in_progress' => 'En curso',
                'closed' => 'Cerrada',
            ])->default('draft'),

            Forms\Components\TextInput::make('reference')->label('Referencia')->maxLength(50),
            Forms\Components\TextInput::make('title')->label('Título')->maxLength(150),

            Forms\Components\DateTimePicker::make('start_at')->label('Inicio intervención'),
            Forms\Components\DateTimePicker::make('end_at')->label('Fin intervención'),

            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('client_name')->label('Cliente')->searchable(),
            Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Estado')->sortable(),
            Tables\Columns\TextColumn::make('start_at')->label('Inicio')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('end_at')->label('Fin')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ])->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [SystemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInterventions::route('/'),
            'create' => Pages\CreateIntervention::route('/create'),
            'edit' => Pages\EditIntervention::route('/{record}/edit'),
        ];
    }
}
