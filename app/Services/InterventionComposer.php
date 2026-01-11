<?php

namespace App\Services;

use App\Models\Intervention;
use App\Models\InterventionComponent;
use App\Models\TemplateAssignment;
use App\Models\TemplateVersion;
use Illuminate\Support\Arr;
use RuntimeException;

class InterventionComposer
{
    public function compose(Intervention $intervention): void
    {
        $system = $intervention->system()->with(['controllerUnit.controllerModel', 'mechanicalUnits.robotModel', 'driveUnits.driveUnitModel'])->firstOrFail();

        // Limpieza por si se recomponen
        $intervention->components()->delete();

        $order = 10;

        // 1) Base del sistema (opcional): si existe assignment system_base
        $base = $this->resolveTemplateVersion('system_base', null, null, null, true);
        if ($base) {
            $this->createComponent(
                intervention: $intervention,
                componentType: 'system_base',
                label: 'Sistema',
                sort: $order,
                templateVersion: $base,
                sysControllerId: null,
                sysMechId: null,
                sysDriveId: null
            );
            $order += 10;
        }

        // 2) Controladora (obligatoria si existe en el sistema)
        if ($system->controllerUnit) {
            $tv = $this->resolveTemplateVersion(
                'controller',
                robotModelId: null,
                controllerModelId: $system->controllerUnit->controller_model_id,
                driveUnitModelId: null,
                allowNull: false
            );

            $this->createComponent(
                intervention: $intervention,
                componentType: 'controller',
                label: $system->controllerUnit->label ?: 'Controladora',
                sort: $order,
                templateVersion: $tv,
                sysControllerId: $system->controllerUnit->id,
                sysMechId: null,
                sysDriveId: null
            );
            $order += 10;
        }

        // 3) Unidades mecánicas
        foreach ($system->mechanicalUnits as $mu) {
            $tv = $this->resolveTemplateVersion(
                'mechanical_unit',
                robotModelId: $mu->robot_model_id,
                controllerModelId: null,
                driveUnitModelId: null,
                allowNull: false
            );

            $this->createComponent(
                intervention: $intervention,
                componentType: 'mechanical_unit',
                label: $mu->label ?: ('Unidad mecánica #' . $mu->id),
                sort: $order,
                templateVersion: $tv,
                sysControllerId: null,
                sysMechId: $mu->id,
                sysDriveId: null
            );
            $order += 10;
        }

        // 4) Drive units
        foreach ($system->driveUnits as $du) {
            $tv = $this->resolveTemplateVersion(
                'drive_unit',
                robotModelId: null,
                controllerModelId: null,
                driveUnitModelId: $du->drive_unit_model_id,
                allowNull: false
            );

            $this->createComponent(
                intervention: $intervention,
                componentType: 'drive_unit',
                label: $du->label ?: 'Drive Unit',
                sort: $order,
                templateVersion: $tv,
                sysControllerId: null,
                sysMechId: null,
                sysDriveId: $du->id
            );
            $order += 10;
        }
    }

    private function resolveTemplateVersion(
        string $componentType,
        ?int $robotModelId,
        ?int $controllerModelId,
        ?int $driveUnitModelId,
        bool $allowNull
    ): ?TemplateVersion {
        $q = TemplateAssignment::query()
            ->where('component_type', $componentType)
            ->orderBy('priority')
            ->orderByDesc('id');

        if ($robotModelId !== null) {
            $q->where('robot_model_id', $robotModelId);
        } else {
            $q->whereNull('robot_model_id');
        }

        if ($controllerModelId !== null) {
            $q->where('controller_model_id', $controllerModelId);
        } else {
            $q->whereNull('controller_model_id');
        }

        if ($driveUnitModelId !== null) {
            $q->where('drive_unit_model_id', $driveUnitModelId);
        } else {
            $q->whereNull('drive_unit_model_id');
        }

        $assignment = $q->first();

        if (!$assignment) {
            if ($allowNull) return null;
            throw new RuntimeException("No hay TemplateAssignment para {$componentType} (revisa asignaciones).");
        }

        return $assignment->templateVersion;
    }

    private function createComponent(
        Intervention $intervention,
        string $componentType,
        ?string $label,
        int $sort,
        TemplateVersion $templateVersion,
        ?int $sysControllerId,
        ?int $sysMechId,
        ?int $sysDriveId,
    ): InterventionComponent {
        $schema = $templateVersion->schema_json ?? [];
        $data = $this->initDataFromSchema($schema);

        return $intervention->components()->create([
            'component_type' => $componentType,
            'label' => $label,
            'sort_order' => $sort,
            'system_controller_unit_id' => $sysControllerId,
            'system_mechanical_unit_id' => $sysMechId,
            'system_drive_unit_id' => $sysDriveId,
            'template_version_id' => $templateVersion->id,
            'schema_json' => $schema,
            'data_json' => $data,
        ]);
    }

    private function initDataFromSchema(array $schema): array
    {
        $sections = Arr::get($schema, 'sections', []);
        $out = [];

        foreach ($sections as $section) {
            $fields = $section['fields'] ?? [];
            foreach ($fields as $field) {
                $key = $field['key'] ?? null;
                if (!$key) continue;

                $type = $field['type'] ?? 'text';

                if ($type === 'tristate') {
                    $out[$key] = [
                        'value' => null, // ok|nok|na
                        'observation' => '',
                    ];
                    continue;
                }

                if ($type === 'table') {
                    $rows = $field['rows'] ?? [];
                    $cols = $field['columns'] ?? [];
                    $table = [];
                    foreach ($rows as $r) {
                        $row = [
                            '_row_key' => $r['key'] ?? '',
                            '_row_label' => $r['label'] ?? '',
                        ];
                        foreach ($cols as $c) {
                            $ck = $c['key'] ?? null;
                            if ($ck) $row[$ck] = null;
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
