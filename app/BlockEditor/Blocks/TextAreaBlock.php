<?php

namespace App\BlockEditor\Blocks;

class TextAreaBlock extends BaseBlock
{
    public static function type(): string { return 'text_area'; }
    public static function label(): string { return 'Texto largo'; }
    public static function icon(): string { return 'document-text'; }
    public static function category(): string { return 'fields'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Observaciones',
            'rows' => 3,
            'placeholder' => '',
            'required' => false,
            'width' => 'full',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave', ['required' => true]),
            static::configField('text', 'label', 'Etiqueta'),
            static::configField('number', 'rows', 'Filas'),
            static::configField('text', 'placeholder', 'Placeholder'),
            static::configField('toggle', 'required', 'Obligatorio'),
            static::configField('select', 'width', 'Ancho', [
                'options' => ['full' => '100%', 'half' => '50%'],
            ]),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Observaciones');
        $rows = max(2, (int)($config['rows'] ?? 3));
        $placeholder = static::e($config['placeholder'] ?? '');
        $required = ($config['required'] ?? false) ? '<span style="color:#ef4444;">*</span>' : '';
        $height = $rows * 18;
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'full');

        return <<<HTML
        <div style="{$width}padding:8px 16px;">
            <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;">{$label}{$required}</div>
            <div style="border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;font-size:11px;color:#94a3b8;background:#fff;min-height:{$height}px;">{$placeholder}</div>
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Observaciones');
        $key = $config['key'] ?? '';
        $value = static::e((string)($data[$key] ?? ''));
        $rows = max(2, (int)($config['rows'] ?? 3));
        $height = $rows * 14;
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'full');

        return <<<HTML
        <div style="{$width}margin-bottom:6px;">
            <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:2px;">{$label}</div>
            <div style="border:1px solid #ddd;padding:4px;font-size:10px;min-height:{$height}px;">{$value}</div>
        </div>
        HTML;
    }

    public static function initializeData(array $config): mixed
    {
        return '';
    }
}
