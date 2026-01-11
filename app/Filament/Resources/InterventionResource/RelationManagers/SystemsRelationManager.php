<?php

namespace App\Filament\Resources\InterventionResource\RelationManagers;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use App\Services\ReportComposer;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SystemsRelationManager extends BelongsToManyRelationManager
{
    protected static string $relationship = 'systems';
    protected static ?string $title = 'Sistemas en esta intervención';

    public function table(Table $table): Table
    {
        return $table
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

                        Notification::make()->success()->title('Informe creado')->send();
                    }),

                Tables\Actions\Action::make('fill')
                    ->label('Rellenar')
                    ->icon('heroicon-o-pencil-square')
                    ->url(function ($record) {
                        $intervention = $this->getOwnerRecord();
                        $report = Report::where('intervention_id', $intervention->id)
                            ->where('system_id', $record->id)
                            ->first();

                        return $report
                            ? ReportResource::getUrl('fill', ['record' => $report->id])
                            : null;
                    })
                    ->disabled(fn ($record) => !Report::where('intervention_id', $this->getOwnerRecord()->id)->where('system_id', $record->id)->exists()),

                Tables\Actions\Action::make('report')
                    ->label('Informe')
                    ->icon('heroicon-o-printer')
                    ->openUrlInNewTab()
                    ->url(function ($record) {
                        $intervention = $this->getOwnerRecord();
                        $report = Report::where('intervention_id', $intervention->id)
                            ->where('system_id', $record->id)
                            ->first();

                        return $report
                            ? ReportResource::getUrl('report', ['record' => $report->id])
                            : null;
                    })
                    ->disabled(fn ($record) => !Report::where('intervention_id', $this->getOwnerRecord()->id)->where('system_id', $record->id)->exists()),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
