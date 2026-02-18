<?php

namespace App\BlockEditor\Blocks;

class TableBlock extends BaseBlock
{
    public static function type(): string { return 'table'; }
    public static function label(): string { return 'Tabla de datos'; }
    public static function icon(): string { return 'table-cells'; }
    public static function category(): string { return 'fields'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Tabla',
            'columns' => [
                ['key' => 'col_1', 'label' => 'Columna 1', 'type' => 'text', 'width' => 'auto'],
                ['key' => 'col_2', 'label' => 'Columna 2', 'type' => 'text', 'width' => 'auto'],
                ['key' => 'col_3', 'label' => 'Columna 3', 'type' => 'tristate', 'width' => '80px'],
            ],
            'fixedRows' => [],
            'allowAddRows' => true,
            'minRows' => 1,
            'maxRows' => 20,
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave', ['required' => true]),
            static::configField('text', 'label', 'Título de la tabla'),
            static::configField('table_columns', 'columns', 'Definición de columnas'),
            static::configField('table_rows', 'fixedRows', 'Filas predefinidas'),
            static::configField('toggle', 'allowAddRows', 'Permitir añadir filas'),
            static::configField('number', 'minRows', 'Filas mínimas'),
            static::configField('number', 'maxRows', 'Filas máximas'),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Tabla');
        $columns = $config['columns'] ?? [];
        $fixedRows = $config['fixedRows'] ?? [];

        $headerCells = '';
        foreach ($columns as $col) {
            $colLabel = static::e($col['label'] ?? '');
            $w = ($col['width'] ?? 'auto') !== 'auto' ? "width:{$col['width']};" : '';
            $headerCells .= "<th style=\"{$w}padding:6px 8px;text-align:left;font-size:10px;font-weight:600;color:#fff;background:#475569;\">{$colLabel}</th>";
        }

        $rows = '';
        $displayRows = !empty($fixedRows) ? array_slice($fixedRows, 0, 3) : [[]];
        foreach ($displayRows as $i => $row) {
            $bg = $i % 2 === 0 ? '#fff' : '#f8fafc';
            $cells = '';
            foreach ($columns as $col) {
                $colKey = $col['key'] ?? '';
                $cellValue = static::e($row[$colKey] ?? '');
                $colType = $col['type'] ?? 'text';

                if ($colType === 'tristate') {
                    $cellContent = '<span style="font-size:9px;color:#94a3b8;">OK / NOK / NA</span>';
                } else {
                    $cellContent = $cellValue ?: '<span style="color:#cbd5e1;">...</span>';
                }

                $cells .= "<td style=\"padding:6px 8px;font-size:10px;border-bottom:1px solid #f1f5f9;background:{$bg};\">{$cellContent}</td>";
            }
            $rows .= "<tr>{$cells}</tr>";
        }

        if (empty($fixedRows)) {
            $cells = '';
            foreach ($columns as $col) {
                $cells .= '<td style="padding:6px 8px;font-size:10px;border-bottom:1px solid #f1f5f9;color:#cbd5e1;">...</td>';
            }
            $rows = "<tr>{$cells}</tr>";
        }

        return <<<HTML
        <div style="padding:8px 16px;">
            <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:6px;">{$label}</div>
            <table style="width:100%;border-collapse:collapse;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;">
                <thead><tr>{$headerCells}</tr></thead>
                <tbody>{$rows}</tbody>
            </table>
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Tabla');
        $key = $config['key'] ?? '';
        $columns = $config['columns'] ?? [];
        $tableData = $data[$key] ?? [];

        $headerCells = '';
        foreach ($columns as $col) {
            $colLabel = static::e($col['label'] ?? '');
            $w = ($col['width'] ?? 'auto') !== 'auto' ? "width:{$col['width']};" : '';
            $headerCells .= "<th style=\"{$w}padding:4px 6px;font-size:8px;font-weight:bold;background:#4b5563;color:#fff;text-align:left;\">{$colLabel}</th>";
        }

        $rows = '';
        foreach ($tableData as $i => $row) {
            $bg = $i % 2 === 0 ? '#fff' : '#f9fafb';
            $cells = '';
            foreach ($columns as $col) {
                $colKey = $col['key'] ?? '';
                $colType = $col['type'] ?? 'text';
                $cellValue = $row[$colKey] ?? '';

                if ($colType === 'tristate') {
                    $display = match ($cellValue) {
                        'ok' => '<span style="color:#166534;font-weight:bold;">OK</span>',
                        'nok' => '<span style="color:#dc2626;font-weight:bold;">NOK</span>',
                        'na' => '<span style="color:#6b7280;">N/A</span>',
                        default => '',
                    };
                    $cells .= "<td style=\"padding:3px 6px;font-size:9px;border-bottom:1px solid #e5e7eb;background:{$bg};text-align:center;\">{$display}</td>";
                } else {
                    $cells .= "<td style=\"padding:3px 6px;font-size:9px;border-bottom:1px solid #e5e7eb;background:{$bg};\">" . static::e((string)$cellValue) . "</td>";
                }
            }
            $rows .= "<tr>{$cells}</tr>";
        }

        return <<<HTML
        <div style="margin-bottom:8px;">
            <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:3px;">{$label}</div>
            <table style="width:100%;border-collapse:collapse;border:1px solid #d1d5db;" cellpadding="0" cellspacing="0">
                <thead><tr>{$headerCells}</tr></thead>
                <tbody>{$rows}</tbody>
            </table>
        </div>
        HTML;
    }

    public static function initializeData(array $config): mixed
    {
        $columns = $config['columns'] ?? [];
        $fixedRows = $config['fixedRows'] ?? [];

        if (!empty($fixedRows)) {
            return $fixedRows;
        }

        $emptyRow = [];
        foreach ($columns as $col) {
            $colType = $col['type'] ?? 'text';
            $emptyRow[$col['key'] ?? ''] = $colType === 'tristate' ? null : '';
        }

        return [$emptyRow];
    }
}
