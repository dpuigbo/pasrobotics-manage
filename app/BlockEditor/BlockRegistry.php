<?php

namespace App\BlockEditor;

use App\BlockEditor\Blocks\ChecklistBlock;
use App\BlockEditor\Blocks\DateFieldBlock;
use App\BlockEditor\Blocks\DividerBlock;
use App\BlockEditor\Blocks\HeaderBlock;
use App\BlockEditor\Blocks\ImageBlock;
use App\BlockEditor\Blocks\NumberFieldBlock;
use App\BlockEditor\Blocks\SectionTitleBlock;
use App\BlockEditor\Blocks\SelectFieldBlock;
use App\BlockEditor\Blocks\SignatureBlock;
use App\BlockEditor\Blocks\TableBlock;
use App\BlockEditor\Blocks\TextAreaBlock;
use App\BlockEditor\Blocks\TextFieldBlock;
use App\BlockEditor\Blocks\TristateBlock;
use App\BlockEditor\Contracts\BlockInterface;

class BlockRegistry
{
    protected static array $blocks = [
        HeaderBlock::class,
        SectionTitleBlock::class,
        DividerBlock::class,
        TextFieldBlock::class,
        NumberFieldBlock::class,
        DateFieldBlock::class,
        TextAreaBlock::class,
        SelectFieldBlock::class,
        TristateBlock::class,
        ChecklistBlock::class,
        TableBlock::class,
        ImageBlock::class,
        SignatureBlock::class,
    ];

    public static function all(): array
    {
        $result = [];
        foreach (static::$blocks as $class) {
            $result[$class::type()] = $class;
        }
        return $result;
    }

    public static function grouped(): array
    {
        $groups = [];
        foreach (static::$blocks as $class) {
            $groups[$class::category()][] = [
                'type' => $class::type(),
                'label' => $class::label(),
                'icon' => $class::icon(),
            ];
        }
        return $groups;
    }

    public static function resolve(string $type): ?string
    {
        $all = static::all();
        return $all[$type] ?? null;
    }

    public static function renderPreview(string $type, array $config): string
    {
        $class = static::resolve($type);
        if (!$class) {
            return '<div style="padding:8px;color:#94a3b8;font-style:italic;">Bloque desconocido: ' . htmlspecialchars($type) . '</div>';
        }
        return $class::renderPreview($config);
    }

    public static function renderPdf(string $type, array $config, array $data = []): string
    {
        $class = static::resolve($type);
        if (!$class) return '';
        return $class::renderPdf($config, $data);
    }

    public static function defaultConfig(string $type): array
    {
        $class = static::resolve($type);
        return $class ? $class::defaultConfig() : [];
    }

    public static function configSchema(string $type): array
    {
        $class = static::resolve($type);
        return $class ? $class::configSchema() : [];
    }

    public static function initializeData(string $type, array $config): mixed
    {
        $class = static::resolve($type);
        return $class ? $class::initializeData($config) : null;
    }

    public static function categoryLabels(): array
    {
        return [
            'layout' => 'Estructura',
            'fields' => 'Campos de datos',
            'inspection' => 'Inspección',
            'media' => 'Media y firma',
        ];
    }
}
