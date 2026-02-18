<?php

namespace App\Services;

use App\BlockEditor\BlockRegistry;
use App\Models\Report;
use RuntimeException;

class ReportComposer
{
    public function compose(Report $report): void
    {
        $system = $report->system()->with([
            'controllerUnit.componentModel',
            'mechanicalUnits.componentModel',
            'driveUnits.componentModel',
        ])->firstOrFail();

        $report->components()->delete();

        $order = 10;

        // CONTROLADORA (1)
        if ($system->controllerUnit) {
            $tv = $this->resolveTemplateVersionOrFail('controller', $system->controllerUnit->component_model_id);

            $report->components()->create([
                'component_type' => 'controller',
                'label' => $system->controllerUnit->label ?: 'Controladora',
                'sort_order' => $order,
                'system_controller_unit_id' => $system->controllerUnit->id,
                'template_version_id' => $tv['id'],
                'schema_json' => $tv['schema'],
                'data_json' => $this->initDataFromSchema($tv['schema']),
            ]);
            $order += 10;
        }

        // MECÁNICAS (N)
        foreach ($system->mechanicalUnits->sortBy('id') as $mu) {
            $tv = $this->resolveTemplateVersionOrFail('mechanical_unit', $mu->component_model_id);

            $report->components()->create([
                'component_type' => 'mechanical_unit',
                'label' => $mu->label ?: 'Unidad mecánica',
                'sort_order' => $order,
                'system_mechanical_unit_id' => $mu->id,
                'template_version_id' => $tv['id'],
                'schema_json' => $tv['schema'],
                'data_json' => $this->initDataFromSchema($tv['schema']),
            ]);
            $order += 10;
        }

        // DRIVE UNITS
        $mechanicals = $system->mechanicalUnits->sortBy('id')->values();

        foreach ($system->driveUnits()->orderBy('id')->get() as $du) {
            $tv = $this->resolveTemplateVersionOrFail('drive_unit', $du->component_model_id);

            $label = $du->label ?: 'Drive Unit';

            $report->components()->create([
                'component_type' => 'drive_unit',
                'label' => $label,
                'sort_order' => $order,
                'system_drive_unit_id' => $du->id,
                'template_version_id' => $tv['id'],
                'schema_json' => $tv['schema'],
                'data_json' => $this->initDataFromSchema($tv['schema']),
            ]);
            $order += 10;
        }
    }

    private function resolveTemplateVersionOrFail(string $componentType, int $modelId): array
    {
        $tv = \App\Models\ComponentModelTemplateVersion::query()
            ->where('component_model_id', $modelId)
            ->where('status', 'active')
            ->orderByDesc('version')
            ->first();

        if (!$tv) {
            $tv = \App\Models\ComponentModelTemplateVersion::query()
                ->where('component_model_id', $modelId)
                ->orderByDesc('version')
                ->first();
        }

        if (!$tv) {
            throw new RuntimeException("No hay plantilla definida para {$componentType} con model_id={$modelId}. Por favor, crea una plantilla primero.");
        }

        return [
            'id' => $tv->id,
            'schema' => $tv->schema ?? [],
        ];
    }

    private function initDataFromSchema(array $schema): array
    {
        // New block-based schema format
        if (isset($schema['blocks'])) {
            return PdfGenerator::initializeDataFromSchema($schema);
        }

        // Legacy section-based schema format (backward compatibility)
        $sections = $schema['sections'] ?? [];
        $out = [];

        foreach ($sections as $section) {
            foreach (($section['fields'] ?? []) as $field) {
                $key = $field['key'] ?? ($field['data']['key'] ?? null);
                if (!$key) continue;

                $type = $field['type'] ?? 'text';

                if ($type === 'tristate') {
                    $out[$key] = ['value' => null, 'observation' => ''];
                    continue;
                }

                if ($type === 'table') {
                    $data = $field['data'] ?? $field;
                    $rows = $data['rows'] ?? $data['fixedRows'] ?? [];
                    $cols = $data['columns'] ?? [];
                    $table = [];
                    foreach ($rows as $r) {
                        $row = [];
                        foreach ($cols as $c) {
                            if (!empty($c['key'])) $row[$c['key']] = null;
                        }
                        $table[] = $row;
                    }
                    $out[$key] = $table;
                    continue;
                }

                $out[$key] = null;
            }
        }

        return $out;
    }
}
