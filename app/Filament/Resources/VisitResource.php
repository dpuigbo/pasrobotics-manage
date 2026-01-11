<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitResource\Pages;
use App\Models\System;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationGroup = 'Intervenciones';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $modelLabel = 'Visita';
    protected static ?string $pluralModelLabel = 'Visitas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->required()
                ->options([
                    'preventive' => 'Preventivo',
                    'corrective' => 'Correctivo',
                ]),

            Forms\Components\Select::make('status')
                ->label('Estado')
                ->required()
                ->options([
                    'draft' => 'Borrador',
                    'finalized' => 'Finalizado',
                    'delivered' => 'Entregado',
                ])
                ->default('draft'),

            Forms\Components\DateTimePicker::make('performed_at')->label('Fecha/Hora intervención'),
            Forms\Components\TextInput::make('reference')->label('Referencia')->maxLength(50),
            Forms\Components\TextInput::make('title')->label('Título')->maxLength(150),

            Forms\Components\TextInput::make('client_id')
                ->label('Client ID (temporal)')
                ->numeric()
                ->helperText('Luego lo cambiaremos por selector de cliente.'),

            Forms\Components\Select::make('system_ids')
                ->label('Sistemas incluidos')
                ->multiple()
                ->required()
                ->options(fn () => System::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->helperText('Se creará un informe por cada sistema.'),

            Forms\Components\Textarea::make('notes')->label('Notas generales')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Estado')->sortable(),
            Tables\Columns\TextColumn::make('performed_at')->label('Fecha')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('reference')->label('Ref')->searchable(),
            Tables\Columns\TextColumn::make('title')->label('Título')->wrap()->searchable(),
            Tables\Columns\TextColumn::make('reports_count')->counts('reports')->label('Informes')->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVisits::route('/'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\VisitResource\RelationManagers\ReportsRelationManager::class,
        ];
    }
}
