<?php

namespace App\BlockEditor\Contracts;

interface BlockInterface
{
    public static function type(): string;

    public static function label(): string;

    public static function icon(): string;

    public static function category(): string;

    public static function defaultConfig(): array;

    public static function configSchema(): array;

    public static function renderPreview(array $config): string;

    public static function renderPdf(array $config, array $data = []): string;

    public static function initializeData(array $config): mixed;
}
