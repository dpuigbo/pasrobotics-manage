<?php

namespace App\Filament\Pages;

use App\Models\ComponentModelTemplateVersion;
use Filament\Pages\Page;

class TemplateEditor extends Page
{
    protected static string $view = 'filament.pages.template-editor';
    protected static ?string $slug = 'template-editor/{templateVersionId}';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Editor de Templates';

    public ?int $templateVersionId = null;
    public ?ComponentModelTemplateVersion $templateVersion = null;

    public function mount(int $templateVersionId): void
    {
        $this->templateVersionId = $templateVersionId;
        $this->templateVersion = ComponentModelTemplateVersion::with('componentModel.manufacturer')
            ->findOrFail($templateVersionId);
    }

    public function getTitle(): string
    {
        if ($this->templateVersion) {
            $model = $this->templateVersion->componentModel;
            $manufacturer = $model?->manufacturer?->name ?? '';
            return "Editor: {$manufacturer} {$model?->name} (v{$this->templateVersion->version})";
        }
        return 'Editor de Templates';
    }

    public function getBreadcrumbs(): array
    {
        $crumbs = [];

        if ($this->templateVersion) {
            $model = $this->templateVersion->componentModel;
            $type = $model?->type ?? 'component';

            $resourceSlug = match ($type) {
                'controller' => 'controller-models',
                'mechanical_unit' => 'robot-models',
                'drive_unit' => 'drive-unit-models',
                default => 'component-models',
            };

            $crumbs["/admin/{$resourceSlug}"] = ucfirst(str_replace('_', ' ', $type)) . ' Models';
            if ($model) {
                $crumbs["/admin/{$resourceSlug}/{$model->id}/edit"] = $model->name;
            }
            $crumbs[''] = "Template v{$this->templateVersion->version}";
        }

        return $crumbs;
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'filament.admin.pages.template-editor';
    }
}
