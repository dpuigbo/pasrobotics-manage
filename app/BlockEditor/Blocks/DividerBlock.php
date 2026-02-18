<?php

namespace App\BlockEditor\Blocks;

class DividerBlock extends BaseBlock
{
    public static function type(): string { return 'divider'; }
    public static function label(): string { return 'Separador'; }
    public static function icon(): string { return 'minus'; }
    public static function category(): string { return 'layout'; }

    public static function defaultConfig(): array
    {
        return [
            'style' => 'solid',
            'spacing' => 'medium',
            'color' => '#e2e8f0',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('select', 'style', 'Estilo', [
                'options' => ['solid' => 'Sólida', 'dashed' => 'Discontinua', 'dotted' => 'Punteada', 'space' => 'Solo espacio'],
            ]),
            static::configField('select', 'spacing', 'Espaciado', [
                'options' => ['small' => 'Pequeño', 'medium' => 'Medio', 'large' => 'Grande'],
            ]),
            static::configField('color', 'color', 'Color'),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $style = $config['style'] ?? 'solid';
        $spacing = $config['spacing'] ?? 'medium';
        $color = static::e($config['color'] ?? '#e2e8f0');

        $margins = ['small' => '6px', 'medium' => '12px', 'large' => '20px'];
        $margin = $margins[$spacing] ?? '12px';

        if ($style === 'space') {
            return "<div style=\"height:{$margin};\"></div>";
        }

        return "<div style=\"margin:{$margin} 16px;border-top:1px {$style} {$color};\"></div>";
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $style = $config['style'] ?? 'solid';
        $spacing = $config['spacing'] ?? 'medium';
        $color = static::e($config['color'] ?? '#e2e8f0');

        $margins = ['small' => '4px', 'medium' => '8px', 'large' => '16px'];
        $margin = $margins[$spacing] ?? '8px';

        if ($style === 'space') {
            return "<div style=\"height:{$margin};\"></div>";
        }

        return "<hr style=\"margin:{$margin} 0;border:none;border-top:1px {$style} {$color};\" />";
    }
}
