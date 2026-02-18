
<?php

use App\Models\ComponentModelTemplateVersion;
use App\Models\Report;
use App\Services\PdfGenerator;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', fn () => 'NOK NANU');

// PDF Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/template/{id}/preview-pdf', function (int $id) {
        $version = ComponentModelTemplateVersion::findOrFail($id);
        $schema = $version->schema ?? [];

        $generator = new PdfGenerator();
        $pdf = $generator->renderTemplatePreviewPdf($schema);

        return $pdf->stream("template-v{$version->version}-preview.pdf");
    })->name('template.preview-pdf');

    Route::get('/report/{id}/pdf', function (int $id) {
        $report = Report::with(['intervention.client', 'system.manufacturer', 'components'])->findOrFail($id);

        $generator = new PdfGenerator();
        $pdf = $generator->generateReportPdf($report);

        return $pdf->stream("report-{$report->id}.pdf");
    })->name('report.pdf');

    Route::get('/report/{id}/download', function (int $id) {
        $report = Report::with(['intervention.client', 'system.manufacturer', 'components'])->findOrFail($id);

        $generator = new PdfGenerator();
        $pdf = $generator->generateReportPdf($report);

        return $pdf->download("informe-{$report->id}.pdf");
    })->name('report.download');
});
