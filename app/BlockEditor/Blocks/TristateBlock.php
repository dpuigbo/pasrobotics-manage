<?php

namespace App\BlockEditor\Blocks;

class TristateBlock extends BaseBlock
{
    public static function type(): string { return 'tristate'; }
    public static function label(): string { return 'Inspección (OK/NOK/NA)'; }
    public static function icon(): string { return 'check-circle'; }
    public static function category(): string { return 'inspection'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Punto de inspección',
            'withObservation' => true,
            'required' => true,
            'maintenanceLevel' => 'general',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave', ['required' => true]),
            static::configField('text', 'label', 'Descripción del punto'),
            static::configField('toggle', 'withObservation', 'Incluir campo de observación'),
            static::configField('toggle', 'required', 'Obligatorio'),
            static::configField('select', 'maintenanceLevel', 'Nivel de mantenimiento', [
                'options' => [
                    'general' => 'General',
                    'level1' => 'Nivel 1',
                    'level2' => 'Nivel 2',
                    'level3' => 'Nivel 3',
                ],
            ]),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Punto de inspección');
        $withObs = $config['withObservation'] ?? true;
        $required = ($config['required'] ?? false) ? '<span style="color:#ef4444;margin-left:2px;">*</span>' : '';
        $level = $config['maintenanceLevel'] ?? 'general';

        $levelColors = [
            'general' => '#94a3b8',
            'level1' => '#22c55e',
            'level2' => '#f59e0b',
            'level3' => '#ef4444',
        ];
        $levelColor = $levelColors[$level] ?? '#94a3b8';

        $obsHtml = $withObs
            ? '<div style="margin-top:6px;border:1px dashed #e2e8f0;border-radius:4px;padding:6px 8px;font-size:10px;color:#94a3b8;min-height:16px;">Observaciones...</div>'
            : '';

        return <<<HTML
        <div style="padding:10px 16px;border:1px solid #f1f5f9;border-radius:6px;margin:4px 16px;background:#fff;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:4px;height:28px;border-radius:2px;background:{$levelColor};"></div>
                    <div style="font-size:12px;color:#334155;font-weight:500;">{$label}{$required}</div>
                </div>
                <div style="display:flex;gap:6px;">
                    <div style="padding:4px 12px;border-radius:4px;font-size:10px;font-weight:600;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">OK</div>
                    <div style="padding:4px 12px;border-radius:4px;font-size:10px;font-weight:600;background:#fff;color:#94a3b8;border:1px solid #e2e8f0;">NOK</div>
                    <div style="padding:4px 12px;border-radius:4px;font-size:10px;font-weight:600;background:#fff;color:#94a3b8;border:1px solid #e2e8f0;">N/A</div>
                </div>
            </div>
            {$obsHtml}
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Punto de inspección');
        $key = $config['key'] ?? '';
        $withObs = $config['withObservation'] ?? true;

        $value = $data[$key]['value'] ?? $data[$key] ?? null;
        $observation = $data[$key]['observation'] ?? '';

        $okStyle = $value === 'ok' ? 'background:#166534;color:#fff;' : 'color:#999;';
        $nokStyle = $value === 'nok' ? 'background:#dc2626;color:#fff;' : 'color:#999;';
        $naStyle = $value === 'na' ? 'background:#6b7280;color:#fff;' : 'color:#999;';

        $obsHtml = '';
        if ($withObs && $observation) {
            $obsHtml = '<div style="margin-top:3px;font-size:9px;color:#555;font-style:italic;padding-left:10px;">Obs: ' . static::e($observation) . '</div>';
        }

        return <<<HTML
        <div style="margin-bottom:4px;padding:4px 0;border-bottom:1px solid #eee;">
            <table style="width:100%;" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="font-size:10px;color:#222;padding:2px 0;">{$label}</td>
                    <td style="width:120px;text-align:right;">
                        <span style="display:inline-block;padding:2px 8px;font-size:8px;font-weight:bold;border:1px solid #ccc;border-radius:3px;{$okStyle}">OK</span>
                        <span style="display:inline-block;padding:2px 8px;font-size:8px;font-weight:bold;border:1px solid #ccc;border-radius:3px;{$nokStyle}">NOK</span>
                        <span style="display:inline-block;padding:2px 8px;font-size:8px;font-weight:bold;border:1px solid #ccc;border-radius:3px;{$naStyle}">N/A</span>
                    </td>
                </tr>
            </table>
            {$obsHtml}
        </div>
        HTML;
    }

    public static function initializeData(array $config): mixed
    {
        $base = ['value' => null];
        if ($config['withObservation'] ?? true) {
            $base['observation'] = '';
        }
        return $base;
    }
}
