<?php

namespace App\BlockEditor\Blocks;

class ImageBlock extends BaseBlock
{
    public static function type(): string { return 'image'; }
    public static function label(): string { return 'Imagen'; }
    public static function icon(): string { return 'photo'; }
    public static function category(): string { return 'media'; }

    public static function defaultConfig(): array
    {
        return [
            'key' => '',
            'label' => 'Imagen',
            'multiple' => false,
            'maxFiles' => 1,
            'maxSizeMb' => 5,
            'width' => 'full',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'key', 'Clave', ['required' => true]),
            static::configField('text', 'label', 'Etiqueta'),
            static::configField('toggle', 'multiple', 'Permitir múltiples imágenes'),
            static::configField('number', 'maxFiles', 'Máximo de archivos'),
            static::configField('number', 'maxSizeMb', 'Tamaño máximo (MB)'),
            static::configField('select', 'width', 'Ancho', [
                'options' => ['full' => '100%', 'half' => '50%'],
            ]),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $label = static::e($config['label'] ?? 'Imagen');
        $multiple = $config['multiple'] ?? false;
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'full');

        $hint = $multiple ? 'Arrastra imágenes aquí o haz clic para seleccionar' : 'Arrastra una imagen o haz clic para seleccionar';

        return <<<HTML
        <div style="{$width}padding:8px 16px;">
            <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;">{$label}</div>
            <div style="border:2px dashed #e2e8f0;border-radius:8px;padding:24px;text-align:center;background:#fafbfc;">
                <div style="font-size:24px;color:#cbd5e1;margin-bottom:6px;">&#128247;</div>
                <div style="font-size:10px;color:#94a3b8;">{$hint}</div>
            </div>
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $label = static::e($config['label'] ?? 'Imagen');
        $key = $config['key'] ?? '';
        $images = (array)($data[$key] ?? []);
        $width = TextFieldBlock::widthStyle($config['width'] ?? 'full');

        if (empty($images)) {
            return <<<HTML
            <div style="{$width}margin-bottom:6px;">
                <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:2px;">{$label}</div>
                <div style="font-size:9px;color:#999;font-style:italic;">Sin imágenes</div>
            </div>
            HTML;
        }

        $imagesHtml = '';
        foreach ($images as $img) {
            $src = static::e((string)$img);
            $imagesHtml .= "<img src=\"{$src}\" style=\"max-width:200px;max-height:150px;margin:4px;border:1px solid #ddd;\" />";
        }

        return <<<HTML
        <div style="{$width}margin-bottom:6px;">
            <div style="font-size:9px;font-weight:bold;color:#333;margin-bottom:2px;">{$label}</div>
            {$imagesHtml}
        </div>
        HTML;
    }

    public static function initializeData(array $config): mixed
    {
        return ($config['multiple'] ?? false) ? [] : null;
    }
}
