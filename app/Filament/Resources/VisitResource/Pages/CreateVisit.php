<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use App\Models\Visit;
use App\Services\InterventionComposer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateVisit extends CreateRecord
{
    protected static string $resource = VisitResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $systemIds = $data['system_ids'] ?? [];
        unset($data['system_ids']);

        /** @var Visit $visit */
        $visit = Visit::create($data);

        foreach ($systemIds as $systemId) {
            // "Informe" por sistema (tu tabla interventions)
            $report = $visit->reports()->create([
                'system_id'    => $systemId,
                'type'         => $visit->type,
                'status'       => $visit->status,
                'reference'    => $visit->reference,
                'title'        => $visit->title,
                'performed_at' => $visit->performed_at,
                'notes'        => $visit->notes,
            ]);

            app(InterventionComposer::class)->compose($report);
        }

        return $visit;
    }
}
