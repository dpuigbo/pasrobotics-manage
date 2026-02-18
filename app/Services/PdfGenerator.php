<?php

namespace App\Services;

use App\BlockEditor\BlockRegistry;
use App\Models\Report;
use App\Models\ReportComponent;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfGenerator
{
    public function generateReportPdf(Report $report): \Barryvdh\DomPDF\PDF
    {
        $report->load(['intervention.client', 'system.manufacturer', 'components']);

        $componentsHtml = [];
        foreach ($report->components as $component) {
            $componentsHtml[] = $this->renderComponent($component);
        }

        $html = view('pdf.report', [
            'report' => $report,
            'componentsHtml' => $componentsHtml,
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('defaultFont', 'sans-serif')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    public function renderComponent(ReportComponent $component): string
    {
        $schema = $component->schema_json ?? [];
        $data = $component->data_json ?? [];
        $blocks = $schema['blocks'] ?? [];

        $html = '';
        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $config = $block['config'] ?? [];

            $blockData = $data;
            if (isset($config['key']) && isset($data[$config['key']])) {
                $blockData = array_merge($data, [$config['key'] => $data[$config['key']]]);
            }

            $html .= BlockRegistry::renderPdf($type, $config, $blockData);
        }

        return $html;
    }

    public function renderTemplatePreviewPdf(array $schema): \Barryvdh\DomPDF\PDF
    {
        $blocks = $schema['blocks'] ?? [];
        $pageConfig = $schema['pageConfig'] ?? [];

        $blocksHtml = '';
        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $config = $block['config'] ?? [];
            $blocksHtml .= BlockRegistry::renderPdf($type, $config);
        }

        $html = view('pdf.template-preview', [
            'blocksHtml' => $blocksHtml,
            'pageConfig' => $pageConfig,
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('defaultFont', 'sans-serif')
            ->setOption('isHtml5ParserEnabled', true);
    }

    public static function initializeDataFromSchema(array $schema): array
    {
        $data = [];
        $blocks = $schema['blocks'] ?? [];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $config = $block['config'] ?? [];
            $key = $config['key'] ?? null;

            if ($key) {
                $data[$key] = BlockRegistry::initializeData($type, $config);
            }
        }

        return $data;
    }
}
