<?php

namespace App\BlockEditor\Blocks;

class NumberFieldBlock extends BaseBlock
{
    public static function type(): string { return 'number_field'; }
    public static function label(): string { return 'Campo numérico'; }
    public static function icon(): string { return 'hashtag'; }
    public static function category(): string { return 'fields'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Valor numérico',
            'unit' => '',
            'min' => null,
            'max' => null,
            'required' => false,
            'width' => 'full',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave', ['required' => true]),
            static::configField('text', 'label', 'Etiqueta'),
            static::configField('text', 'unit', 'Unidad (ej: mm, kg, bar)'),
            static::configField('number', 'min', 'Valor mínimo'),
            static::configField('number', 'max', 'Valor máximo'),
            static::configField('toggle', 'required', 'Obligatorio'),
            static::configField('select', 'width', 'Ancho', [
                'options' => ['full' => '100%', 'half' => '50%', 'third' => '33%', 'two_thirds' => '66%'],
            ]),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Valor numérico');
        $unit = static::e($config['unit'] ?? '');
        $required = ($config['required'] ?? false) ? '<span style="color:#ef4444;">*</span>' : '';
        $unitBadge = $unit ? "<span style=\"font-size:10px;background:#f1f5f9;color:#64748b;padding:2px 6px;border-radius:4px;margin-left:6px;\">{$unit}</span>" : '';
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'full');

        return <<<HTML
        <div style="{$width}padding:8px 16px;">
            <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;">{$label}{$required}{$unitBadge}</div>
            <div style="border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;font-size:11px;color:#94a3b8;background:#fff;min-height:18px;">0.00</div>
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Valor numérico');
        $key = $config['key'] ?? '';
        $value = $data[$key] ?? '';
        $unit = static::e($config['unit'] ?? '');
        $display = $value !== '' ? static::e((string)$value) . ($unit ? " {$unit}" : '') : '';
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'full');

        return <<<HTML
        <div style="{$width}margin-bottom:6px;">
            <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:2px;">{$label}</div>
            <div style="border-bottom:1px solid #ccc;padding:2px 0;font-size:10px;min-height:14px;">{$display}</div>
        </div>
        HTML;
    }

    public static function initializeData(array $config): mixed
    {
        return null;
    }
}
