<?php

namespace App\Livewire;

use App\BlockEditor\BlockRegistry;
use App\Models\ComponentModelTemplateVersion;
use Illuminate\Support\Str;
use Livewire\Component;

class BlockEditor extends Component
{
    public ?int $templateVersionId = null;
    public string $templateName = '';
    public string $templateStatus = 'draft';
    public string $templateNotes = '';

    public array $blocks = [];
    public ?string $selectedBlockId = null;
    public array $pageConfig = [
        'orientation' => 'portrait',
        'margins' => ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
        'fontSize' => 10,
    ];

    public bool $showPalette = true;
    public bool $unsavedChanges = false;

    protected $listeners = ['refreshPreview' => '$refresh'];

    public function mount(?int $templateVersionId = null): void
    {
        if ($templateVersionId) {
            $this->templateVersionId = $templateVersionId;
            $this->loadTemplate();
        } else {
            $this->blocks = [
                $this->createBlock('header'),
            ];
        }
    }

    public function loadTemplate(): void
    {
        $version = ComponentModelTemplateVersion::with('componentModel')
            ->findOrFail($this->templateVersionId);

        $schema = $version->schema ?? [];
        $this->templateName = $version->componentModel?->name ?? 'Template';
        $this->templateStatus = $version->status;
        $this->templateNotes = $version->notes ?? '';
        $this->pageConfig = $schema['pageConfig'] ?? $this->pageConfig;
        $this->blocks = $schema['blocks'] ?? [];

        if (empty($this->blocks)) {
            $this->migrateOldSchema($schema);
        }
    }

    protected function migrateOldSchema(array $schema): void
    {
        $this->blocks = [];
        $this->blocks[] = $this->createBlock('header');

        $sections = $schema['sections'] ?? [];
        foreach ($sections as $section) {
            $this->blocks[] = $this->createBlock('section_title', [
                'title' => $section['title'] ?? 'Sección',
                'description' => $section['description'] ?? '',
            ]);

            $fields = $section['fields'] ?? [];
            foreach ($fields as $field) {
                $type = $field['type'] ?? null;
                $data = $field['data'] ?? $field;

                $blockType = $this->mapOldBlockType($type);
                if ($blockType) {
                    $config = $this->mapOldFieldConfig($blockType, $data);
                    $this->blocks[] = $this->createBlock($blockType, $config);
                }
            }
        }
    }

    protected function mapOldBlockType(?string $type): ?string
    {
        return match ($type) {
            'text' => 'text_field',
            'number' => 'number_field',
            'date' => 'date_field',
            'textarea' => 'text_area',
            'select' => 'select_field',
            'tristate' => 'tristate',
            'table' => 'table',
            'image' => 'image',
            'signature' => 'signature',
            'checkbox_group' => 'checklist',
            default => null,
        };
    }

    protected function mapOldFieldConfig(string $newType, array $old): array
    {
        $config = [];
        $config['key'] = $old['key'] ?? Str::snake(Str::random(8));
        $config['label'] = $old['label'] ?? '';

        if (isset($old['required'])) $config['required'] = (bool)$old['required'];
        if (isset($old['width'])) $config['width'] = $old['width'];
        if (isset($old['help'])) $config['help'] = $old['help'];
        if (isset($old['placeholder'])) $config['placeholder'] = $old['placeholder'];
        if (isset($old['unit'])) $config['unit'] = $old['unit'];
        if (isset($old['with_observation'])) $config['withObservation'] = (bool)$old['with_observation'];
        if (isset($old['rows'])) $config['rows'] = (int)$old['rows'];

        if ($newType === 'select_field' && isset($old['options'])) {
            $config['options'] = array_map(fn($opt) => [
                'value' => $opt['value'] ?? $opt,
                'label' => $opt['label'] ?? $opt,
            ], $old['options']);
        }

        if ($newType === 'table' && isset($old['columns'])) {
            $config['columns'] = $old['columns'];
        }

        return $config;
    }

    public function addBlock(string $type, ?int $afterIndex = null): void
    {
        $block = $this->createBlock($type);

        if ($afterIndex !== null && $afterIndex < count($this->blocks)) {
            array_splice($this->blocks, $afterIndex + 1, 0, [$block]);
        } else {
            $this->blocks[] = $block;
        }

        $this->selectedBlockId = $block['id'];
        $this->unsavedChanges = true;
    }

    public function insertBlockAfterSelected(string $type): void
    {
        $index = $this->getSelectedIndex();
        $this->addBlock($type, $index);
    }

    protected function createBlock(string $type, array $configOverrides = []): array
    {
        $defaults = BlockRegistry::defaultConfig($type);
        $config = array_merge($defaults, $configOverrides);

        if (empty($config['key']) && isset($defaults['key'])) {
            $config['key'] = $type . '_' . Str::random(6);
        }

        return [
            'id' => Str::uuid()->toString(),
            'type' => $type,
            'config' => $config,
        ];
    }

    public function selectBlock(?string $blockId): void
    {
        $this->selectedBlockId = $blockId;
    }

    public function removeBlock(string $blockId): void
    {
        $this->blocks = array_values(
            array_filter($this->blocks, fn($b) => $b['id'] !== $blockId)
        );

        if ($this->selectedBlockId === $blockId) {
            $this->selectedBlockId = null;
        }

        $this->unsavedChanges = true;
    }

    public function duplicateBlock(string $blockId): void
    {
        $index = null;
        $original = null;

        foreach ($this->blocks as $i => $block) {
            if ($block['id'] === $blockId) {
                $index = $i;
                $original = $block;
                break;
            }
        }

        if ($original === null) return;

        $duplicate = $original;
        $duplicate['id'] = Str::uuid()->toString();

        if (isset($duplicate['config']['key'])) {
            $duplicate['config']['key'] = $duplicate['config']['key'] . '_copy_' . Str::random(4);
        }

        array_splice($this->blocks, $index + 1, 0, [$duplicate]);
        $this->selectedBlockId = $duplicate['id'];
        $this->unsavedChanges = true;
    }

    public function moveBlockUp(string $blockId): void
    {
        $this->moveBlock($blockId, -1);
    }

    public function moveBlockDown(string $blockId): void
    {
        $this->moveBlock($blockId, 1);
    }

    protected function moveBlock(string $blockId, int $direction): void
    {
        $index = null;
        foreach ($this->blocks as $i => $block) {
            if ($block['id'] === $blockId) {
                $index = $i;
                break;
            }
        }

        if ($index === null) return;

        $newIndex = $index + $direction;
        if ($newIndex < 0 || $newIndex >= count($this->blocks)) return;

        $temp = $this->blocks[$index];
        $this->blocks[$index] = $this->blocks[$newIndex];
        $this->blocks[$newIndex] = $temp;
        $this->blocks = array_values($this->blocks);
        $this->unsavedChanges = true;
    }

    public function reorderBlocks(array $orderedIds): void
    {
        $blocksById = [];
        foreach ($this->blocks as $block) {
            $blocksById[$block['id']] = $block;
        }

        $reordered = [];
        foreach ($orderedIds as $id) {
            if (isset($blocksById[$id])) {
                $reordered[] = $blocksById[$id];
            }
        }

        $this->blocks = $reordered;
        $this->unsavedChanges = true;
    }

    public function updateBlockConfig(string $blockId, string $key, mixed $value): void
    {
        foreach ($this->blocks as &$block) {
            if ($block['id'] === $blockId) {
                $block['config'][$key] = $value;
                break;
            }
        }
        $this->unsavedChanges = true;
    }

    public function updateNestedConfig(string $blockId, string $path, mixed $value): void
    {
        foreach ($this->blocks as &$block) {
            if ($block['id'] === $blockId) {
                data_set($block['config'], $path, $value);
                break;
            }
        }
        $this->unsavedChanges = true;
    }

    public function save(): void
    {
        if (!$this->templateVersionId) return;

        $version = ComponentModelTemplateVersion::findOrFail($this->templateVersionId);

        $version->schema = [
            'blocks' => $this->blocks,
            'pageConfig' => $this->pageConfig,
        ];
        $version->status = $this->templateStatus;
        $version->notes = $this->templateNotes;
        $version->save();

        $this->unsavedChanges = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Template guardado correctamente',
        ]);
    }

    public function getSelectedBlock(): ?array
    {
        if (!$this->selectedBlockId) return null;

        foreach ($this->blocks as $block) {
            if ($block['id'] === $this->selectedBlockId) {
                return $block;
            }
        }

        return null;
    }

    protected function getSelectedIndex(): ?int
    {
        if (!$this->selectedBlockId) return null;

        foreach ($this->blocks as $i => $block) {
            if ($block['id'] === $this->selectedBlockId) {
                return $i;
            }
        }

        return null;
    }

    public function getBlockPaletteProperty(): array
    {
        return BlockRegistry::grouped();
    }

    public function getCategoryLabelsProperty(): array
    {
        return BlockRegistry::categoryLabels();
    }

    public function getSelectedBlockSchemaProperty(): array
    {
        $block = $this->getSelectedBlock();
        if (!$block) return [];

        return BlockRegistry::configSchema($block['type']);
    }

    public function buildSchema(): array
    {
        return [
            'blocks' => $this->blocks,
            'pageConfig' => $this->pageConfig,
        ];
    }

    public function render()
    {
        return view('livewire.block-editor', [
            'selectedBlock' => $this->getSelectedBlock(),
            'blockPalette' => $this->blockPalette,
            'categoryLabels' => $this->categoryLabels,
            'configSchema' => $this->selectedBlockSchema,
            'registry' => new BlockRegistry(),
        ]);
    }
}
