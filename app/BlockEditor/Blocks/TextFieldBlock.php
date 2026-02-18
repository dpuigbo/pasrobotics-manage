<?php

namespace App\BlockEditor\Blocks;

class TextFieldBlock extends BaseBlock
{
    public static function type(): string { return 'text_field'; }
    public static function label(): string { return 'Campo de texto'; }
    public static function icon(): string { return 'pencil'; }
    public static function category(): string { return 'fields'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Campo de texto',
            'placeholder' => '',
            'required' => false,
            'width' => 'full',
            'help' => '',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave (identificador único)', ['required' => true]),
            static::configField('text', 'label', 'Etiqueta'),
            static::configField('text', 'placeholder', 'Placeholder'),
            static::configField('toggle', 'required', 'Obligatorio'),
            static::configField('select', 'width', 'Ancho', [
                'options' => ['full' => '100%', 'half' => '50%', 'third' => '33%', 'two_thirds' => '66%'],
            ]),
            static::configField('text', 'help', 'Texto de ayuda'),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Campo de texto');
        $placeholder = static::e($config['placeholder'] ?? '');
        $required = ($config['required'] ?? false) ? '<span style="color:#ef4444;margin-left:2px;">*</span>' : '';
        $help = ($config['help'] ?? '') ? '<div style="font-size:10px;color:#94a3b8;margin-top:2px;">' . static::e($config['help']) . '</div>' : '';
        $width = static::widthStyle($config['width'] ?? 'full');

        return <<<HTML
        <div style="{$width}padding:8px 16px;">
            <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;">{$label}{$required}</div>
            <div style="border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;font-size:11px;color:#94a3b8;background:#fff;min-height:18px;">{$placeholder}</div>
            {$help}
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Campo de texto');
        $key = $config['key'] ?? '';
        $value = static::e((string)($data[$key] ?? ''));
        $width = static::widthStyle($config['width'] ?? 'full');

        return <<<HTML
        <div style="{$width}margin-bottom:6px;">
            <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:2px;">{$label}</div>
            <div style="border-bottom:1px solid #ccc;padding:2px 0;font-size:10px;min-height:14px;">{$value}</div>
        </div>
        HTML;
    }

    protected static function widthStyle(string $width): string
    {
        return match ($width) {
            'half' => 'display:inline-block;width:48%;vertical-align:top;',
            'third' => 'display:inline-block;width:31%;vertical-align:top;',
            'two_thirds' => 'display:inline-block;width:64%;vertical-align:top;',
            default => '',
        };
    }
}
