<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use App\Models\Visit;
use App\Services\InterventionComposer;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageVisits extends ManageRecords
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data) {
                    $systemIds = $data['system_ids'] ?? [];
                    unset($data['system_ids']);

                    /** @var Visit $visit */
                    $visit = Visit::create($data);

                    foreach ($systemIds as $systemId) {
                        // Creamos un "informe" (tabla interventions) por sistema
                        $report = $visit->reports()->create([
                            'system_id'     => $systemId,
                            'type'          => $visit->type,
                            'status'        => $visit->status,
                            'reference'     => $visit->reference,
                            'title'         => $visit->title,
                            'performed_at'  => $visit->performed_at,
                            'notes'         => $visit->notes,
                        ]);

                        // Componer componentes del informe (controladora + robots + drives…)
                        app(InterventionComposer::class)->compose($report);
                    }

                    Notification::make()
                        ->success()
                        ->title('Visita creada')
                        ->body('Se han generado los informes por sistema.')
                        ->send();

                    return $visit;
                }),
        ];
    }
}
