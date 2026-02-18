<?php

namespace App\BlockEditor\Blocks;

class SectionTitleBlock extends BaseBlock
{
    public static function type(): string { return 'section_title'; }
    public static function label(): string { return 'Título de sección'; }
    public static function icon(): string { return 'bookmark'; }
    public static function category(): string { return 'layout'; }

    public static function defaultConfig(): array
    {
        return [
            'title' => 'Nueva sección',
            'description' => '',
            'level' => 1,
            'color' => '#f59e0b',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'title', 'Título'),
            static::configField('text', 'description', 'Descripción'),
            static::configField('select', 'level', 'Nivel', [
                'options' => [1 => 'Nivel 1 (grande)', 2 => 'Nivel 2 (medio)', 3 => 'Nivel 3 (pequeño)'],
            ]),
            static::configField('color', 'color', 'Color del acento'),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $title = static::e($config['title'] ?? 'Sección');
        $description = static::e($config['description'] ?? '');
        $level = (int)($config['level'] ?? 1);
        $color = static::e($config['color'] ?? '#f59e0b');

        $sizes = [1 => '15px', 2 => '13px', 3 => '12px'];
        $paddings = [1 => '12px 16px', 2 => '10px 16px', 3 => '8px 16px'];
        $fontSize = $sizes[$level] ?? '15px';
        $padding = $paddings[$level] ?? '12px 16px';

        $descHtml = $description
            ? "<div style=\"font-size:11px;color:#64748b;margin-top:2px;font-weight:400;\">{$description}</div>"
            : '';

        return <<<HTML
        <div style="padding:{$padding};border-left:4px solid {$color};background:#f8fafc;margin:4px 0;">
            <div style="font-size:{$fontSize};font-weight:600;color:#1e293b;">{$title}</div>
            {$descHtml}
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $title = static::e($config['title'] ?? 'Sección');
        $description = static::e($config['description'] ?? '');
        $level = (int)($config['level'] ?? 1);
        $color = static::e($config['color'] ?? '#f59e0b');

        $sizes = [1 => '14px', 2 => '12px', 3 => '11px'];
        $fontSize = $sizes[$level] ?? '14px';

        $descHtml = $description ? "<div style=\"font-size:9px;color:#666;margin-top:2px;\">{$description}</div>" : '';

        return <<<HTML
        <div style="padding:8px 10px;border-left:3px solid {$color};background:#f9fafb;margin:8px 0 4px;">
            <div style="font-size:{$fontSize};font-weight:bold;color:#111;">{$title}</div>
            {$descHtml}
        </div>
        HTML;
    }
}
