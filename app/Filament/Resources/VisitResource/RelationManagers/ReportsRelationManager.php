<?php

namespace App\Filament\Resources\VisitResource\RelationManagers;

use App\Filament\Resources\InterventionResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';
    protected static ?string $title = 'Informes por sistema';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('system.name')->label('Sistema')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->sortable(),
                Tables\Columns\TextColumn::make('performed_at')->label('Fecha')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('fill')
                    ->label('Rellenar')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => InterventionResource::getUrl('fill', ['record' => $record->getKey()])),

                Tables\Actions\Action::make('report')
                    ->label('Informe')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => InterventionResource::getUrl('report', ['record' => $record->getKey()]))
                    ->openUrlInNewTab(),
            ]);
    }
}
