<?php

namespace App\BlockEditor\Blocks;

class SelectFieldBlock extends BaseBlock
{
    public static function type(): string { return 'select_field'; }
    public static function label(): string { return 'Lista desplegable'; }
    public static function icon(): string { return 'chevron-down'; }
    public static function category(): string { return 'fields'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Seleccionar',
            'options' => [
                ['value' => 'opcion_1', 'label' => 'Opción 1'],
                ['value' => 'opcion_2', 'label' => 'Opción 2'],
            ],
            'required' => false,
            'width' => 'full',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave', ['required' => true]),
            static::configField('text', 'label', 'Etiqueta'),
            static::configField('key_value_list', 'options', 'Opciones'),
            static::configField('toggle', 'required', 'Obligatorio'),
            static::configField('select', 'width', 'Ancho', [
                'options' => ['full' => '100%', 'half' => '50%', 'third' => '33%'],
            ]),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Seleccionar');
        $options = $config['options'] ?? [];
        $required = ($config['required'] ?? false) ? '<span style="color:#ef4444;">*</span>' : '';
        $optionCount = count($options);
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'full');

        return <<<HTML
        <div style="{$width}padding:8px 16px;">
            <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;">{$label}{$required}</div>
            <div style="border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;font-size:11px;color:#94a3b8;background:#fff;display:flex;align-items:center;justify-content:space-between;min-height:18px;">
                <span>Seleccionar... ({$optionCount} opciones)</span>
                <span>&#9660;</span>
            </div>
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Seleccionar');
        $key = $config['key'] ?? '';
        $selectedValue = $data[$key] ?? '';
        $options = $config['options'] ?? [];

        $displayValue = '';
        foreach ($options as $opt) {
            if (($opt['value'] ?? '') === $selectedValue) {
                $displayValue = $opt['label'] ?? $selectedValue;
                break;
            }
        }

        $width = TextFieldBlock::widthStyle($config['width'] ?? 'full');

        return <<<HTML
        <div style="{$width}margin-bottom:6px;">
            <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:2px;">{$label}</div>
            <div style="border-bottom:1px solid #ccc;padding:2px 0;font-size:10px;min-height:14px;">{$displayValue}</div>
        </div>
        HTML;
    }
}
