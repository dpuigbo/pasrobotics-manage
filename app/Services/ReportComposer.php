<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportComponent;
use RuntimeException;

class ReportComposer
{
    public function compose(Report $report): void
    {
        $system = $report->system()->with([
            'controllerUnit.controllerModel',
            'mechanicalUnits.robotModel',
            'driveUnits.driveUnitModel',
        ])->firstOrFail();

        $report->components()->delete();

        $order = 10;

        // CONTROLADORA (1)
        if ($system->controllerUnit) {
            $tv = $this->resolveTemplateVersionOrFail('controller', $system->controllerUnit->controller_model_id);

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
            $tv = $this->resolveTemplateVersionOrFail('mechanical_unit', $mu->robot_model_id);

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

        // DRIVE UNITS (auto-assign a ROB_2.. si no asignadas)
        $mechanicals = $system->mechanicalUnits->sortBy('id')->values();
        $drives = $system->driveUnits->sortBy('id')->values();

        $alreadyAssigned = $drives->pluck('system_mechanical_unit_id')->filter()->values()->all();
        $candidates = $mechanicals->slice(1)->reject(fn ($mu) => in_array($mu->id, $alreadyAssigned))->values();

        $idx = 0;
        foreach ($drives as $du) {
            if (!$du->system_mechanical_unit_id && isset($candidates[$idx])) {
                $du->system_mechanical_unit_id = $candidates[$idx]->id;
                $du->save();
                $idx++;
            }
        }

        $mechanicalById = $mechanicals->keyBy('id');

        foreach ($system->driveUnits()->orderBy('id')->get() as $du) {
            $tv = $this->resolveTemplateVersionOrFail('drive_unit', $du->drive_unit_model_id);

            $muLabel = null;
            if ($du->system_mechanical_unit_id && $mechanicalById->has($du->system_mechanical_unit_id)) {
                $muLabel = $mechanicalById[$du->system_mechanical_unit_id]->label;
            }

            $label = $du->label ?: 'Drive Unit';
            if ($muLabel) $label .= " → {$muLabel}";

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

    /**
     * TEMPORAL: aquí enchufamos tu lógica real de plantillas en el siguiente commit.
     * Ahora mismo devuelve la última TemplateVersion de esa familia (según cómo lo tengas).
     */
    private function resolveTemplateVersionOrFail(string $componentType, int $modelId): array
    {
        // ✅ Sustituye esto por tu forma actual de obtener TemplateVersion
        // Por ahora: fallback genérico (si no lo tienes, te saltará error y lo ajustamos al instante)
        $tv = \App\Models\TemplateVersion::query()->latest('id')->first();

        if (!$tv) throw new RuntimeException("No hay TemplateVersion en BD para {$componentType} (crear plantilla primero).");

        return [
            'id' => $tv->id,
            'schema' => $tv->schema_json ?? [],
        ];
    }

    private function initDataFromSchema(array $schema): array
    {
        $sections = $schema['sections'] ?? [];
        $out = [];

        foreach ($sections as $section) {
            foreach (($section['fields'] ?? []) as $field) {
                $key = $field['key'] ?? null;
                if (!$key) continue;

                $type = $field['type'] ?? 'text';

                if ($type === 'tristate') {
                    $out[$key] = ['value' => null, 'observation' => ''];
                    continue;
                }

                if ($type === 'table') {
                    $rows = $field['rows'] ?? [];
                    $cols = $field['columns'] ?? [];
                    $table = [];
                    foreach ($rows as $r) {
                        $row = ['_row_key' => $r['key'] ?? '', '_row_label' => $r['label'] ?? ''];
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
