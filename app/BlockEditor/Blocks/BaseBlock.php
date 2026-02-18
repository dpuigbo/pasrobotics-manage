<?php

namespace App\BlockEditor\Blocks;

use App\BlockEditor\Contracts\BlockInterface;

abstract class BaseBlock implements BlockInterface
{
    public static function category(): string
    {
        return 'general';
    }

    public static function initializeData(array $config): mixed
    {
        return null;
    }

    protected static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    protected static function configField(string $type, string $key, string $label, array $options = []): array
    {
        return array_merge([
            'type' => $type,
            'key' => $key,
            'label' => $label,
        ], $options);
    }
}
