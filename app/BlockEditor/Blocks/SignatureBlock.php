<?php

namespace App\BlockEditor\Blocks;

class SignatureBlock extends BaseBlock
{
    public static function type(): string { return 'signature'; }
    public static function label(): string { return 'Firma digital'; }
    public static function icon(): string { return 'pencil-square'; }
    public static function category(): string { return 'media'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Firma',
            'role' => 'Técnico',
            'required' => true,
            'width' => 'half',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave', ['required' => true]),
            static::configField('text', 'label', 'Etiqueta'),
            static::configField('text', 'role', 'Rol (ej: Técnico, Cliente)'),
            static::configField('toggle', 'required', 'Obligatorio'),
            static::configField('select', 'width', 'Ancho', [
                'options' => ['full' => '100%', 'half' => '50%'],
            ]),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Firma');
        $role = static::e($config['role'] ?? 'Técnico');
        $required = ($config['required'] ?? true) ? '<span style="color:#ef4444;">*</span>' : '';
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'half');

        return <<<HTML
        <div style="{$width}padding:8px 16px;">
            <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;">{$label}{$required}</div>
            <div style="border:1px solid #e2e8f0;border-radius:6px;padding:16px;text-align:center;background:#fff;min-height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <div style="font-size:20px;color:#e2e8f0;margin-bottom:4px;">&#9997;</div>
                <div style="font-size:10px;color:#94a3b8;">Firmar aquí</div>
            </div>
            <div style="font-size:10px;color:#64748b;margin-top:4px;text-align:center;">{$role}</div>
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Firma');
        $role = static::e($config['role'] ?? 'Técnico');
        $key = $config['key'] ?? '';
        $signatureData = $data[$key] ?? null;
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'half');

        $signatureHtml = $signatureData
            ? "<img src=\"{$signatureData}\" style=\"max-width:180px;max-height:60px;\" />"
            : '<div style="height:50px;border-bottom:1px solid #333;margin-top:20px;"></div>';

        return <<<HTML
        <div style="{$width}margin-bottom:8px;text-align:center;">
            <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:4px;">{$label}</div>
            {$signatureHtml}
            <div style="font-size:8px;color:#666;margin-top:4px;">{$role}</div>
        </div>
        HTML;
    }

    public static function initializeData(array $config): mixed
    {
        return null;
    }
}
