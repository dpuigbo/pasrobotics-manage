<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Resources\Resource;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    // No lo mostramos en el menú: se accede desde la intervención
    protected static bool $shouldRegisterNavigation = false;

    public static function getPages(): array
    {
        return [
            'fill'   => Pages\FillReport::route('/reports/{record}/fill'),
            'report' => Pages\ViewReport::route('/reports/{record}/report'),
        ];
    }
}
