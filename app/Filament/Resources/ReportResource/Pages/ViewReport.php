<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;
    protected static string $view = 'filament.reports.view';

    protected function resolveRecord($key): Model
    {
        return Report::query()
            ->with(['intervention', 'system', 'components.templateVersion.template'])
            ->findOrFail($key);
    }
}
