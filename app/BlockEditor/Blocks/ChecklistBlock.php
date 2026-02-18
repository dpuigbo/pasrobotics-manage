<?php

namespace App\BlockEditor\Blocks;

class ChecklistBlock extends BaseBlock
{
    public static function type(): string { return 'checklist'; }
    public static function label(): string { return 'Lista de verificación'; }
    public static function icon(): string { return 'clipboard-document-check'; }
    public static function category(): string { return 'inspection'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Lista de verificación',
            'items' => [
                ['key' => 'item_1', 'label' => 'Elemento 1'],
                ['key' => 'item_2', 'label' => 'Elemento 2'],
                ['key' => 'item_3', 'label' => 'Elemento 3'],
            ],
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave del grupo', ['required' => true]),
            static::configField('text', 'label', 'Título'),
            static::configField('key_value_list', 'items', 'Elementos de la lista'),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Lista de verificación');
        $items = $config['items'] ?? [];

        $itemsHtml = '';
        foreach ($items as $item) {
            $itemLabel = static::e($item['label'] ?? '');
            $itemsHtml .= <<<HTML
            <div style="display:flex;align-items:center;gap:8px;padding:4px 0;border-bottom:1px solid #f8fafc;">
                <div style="width:16px;height:16px;border:2px solid #cbd5e1;border-radius:3px;flex-shrink:0;"></div>
                <span style="font-size:11px;color:#475569;">{$itemLabel}</span>
            </div>
            HTML;
        }

        return <<<HTML
        <div style="padding:8px 16px;">
            <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:6px;">{$label}</div>
            <div style="padding:4px 8px;background:#fff;border:1px solid #f1f5f9;border-radius:6px;">
                {$itemsHtml}
            </div>
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Lista de verificación');
        $key = $config['key'] ?? '';
        $items = $config['items'] ?? [];
        $checkedItems = $data[$key] ?? [];

        $itemsHtml = '';
        foreach ($items as $item) {
            $itemKey = $item['key'] ?? '';
            $itemLabel = static::e($item['label'] ?? '');
            $checked = in_array($itemKey, (array)$checkedItems);
            $checkmark = $checked ? '&#9745;' : '&#9744;';

            $itemsHtml .= "<div style=\"font-size:10px;padding:2px 0;\"><span>{$checkmark}</span> {$itemLabel}</div>";
        }

        return <<<HTML
        <div style="margin-bottom:6px;">
            <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:3px;">{$label}</div>
            {$itemsHtml}
        </div>
        HTML;
    }

    public static function initializeData(array $config): mixed
    {
        return [];
    }
}
