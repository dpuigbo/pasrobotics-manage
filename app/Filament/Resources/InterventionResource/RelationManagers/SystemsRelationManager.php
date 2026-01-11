<?php

namespace App\Filament\Resources\InterventionResource\RelationManagers;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use App\Services\ReportComposer;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SystemsRelationManager extends RelationManager
{
    protected static string $relationship = 'systems';
    protected static ?string $title = 'Sistemas en esta intervención';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Sistema')->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Añadir sistema existente')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\Action::make('createReport')
                    ->label('Crear informe')
                    ->icon('heroicon-o-document-plus')
                    ->action(function ($record) {
                        $intervention = $this->getOwnerRecord();

                        $report = Report::firstOrCreate(
                            ['intervention_id' => $intervention->id, 'system_id' => $record->id],
                            [
                                'status' => 'draft',
                                'performed_start_at' => $intervention->start_at,
                                'performed_end_at' => $intervention->end_at,
                                'notes' => null,
                            ]
                        );

                        app(ReportComposer::class)->compose($report);

                        Notification::make()
                            ->success()
                            ->title('Informe creado')
                            ->send();
                    }),

                Tables\Actions\Action::make('fill')
                    ->label('Rellenar')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn ($record) => Report::where('intervention_id', $this->getOwnerRecord()->id)
                        ->where('system_id', $record->id)
                        ->exists())
                    ->url(function ($record) {
                        $intervention = $this->getOwnerRecord();
                        $report = Report::where('intervention_id', $intervention->id)
                            ->where('system_id', $record->id)
                            ->firstOrFail();

                        return ReportResource::getUrl('fill', ['record' => $report->id]);
                    }),

                Tables\Actions\Action::make('report')
                    ->label('Informe')
                    ->icon('heroicon-o-printer')
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => Report::where('intervention_id', $this->getOwnerRecord()->id)
                        ->where('system_id', $record->id)
                        ->exists())
                    ->url(function ($record) {
                        $intervention = $this->getOwnerRecord();
                        $report = Report::where('intervention_id', $intervention->id)
                            ->where('system_id', $record->id)
                            ->firstOrFail();

                        return ReportResource::getUrl('report', ['record' => $report->id]);
                    }),

                Tables\Actions\DetachAction::make()->label('Quitar'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
