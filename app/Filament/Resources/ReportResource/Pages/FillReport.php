<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\BlockEditor\BlockRegistry;
use App\Filament\Resources\ReportResource;
use App\Support\SchemaToFilament;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class FillReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    public function form(Form $form): Form
    {
        $this->record->load(['system', 'components']);

        $tabs = [];
        foreach ($this->record->components as $component) {
            $schema = is_array($component->schema_json) ? $component->schema_json : [];
            $label = strtoupper($component->component_type) . ' — ' . ($component->label ?? ('#'.$component->id));

            // Support both new block-based and old section-based schemas
            if (isset($schema['blocks'])) {
                $fields = $this->buildFieldsFromBlocks($schema['blocks'], "componentData.{$component->id}");
            } elseif (class_exists(SchemaToFilament::class)) {
                $fields = SchemaToFilament::build($schema, "componentData.{$component->id}");
            } else {
                $fields = [Forms\Components\Placeholder::make('no-schema')
                    ->content('Schema no disponible. Regenera el informe.')];
            }

            $tabs[] = Forms\Components\Tabs\Tab::make($label)->schema($fields);
        }

        return $form->schema([
            Forms\Components\Section::make('Informe del sistema')->schema([
                Forms\Components\TextInput::make('system.name')->label('Sistema')->disabled(),
                Forms\Components\Select::make('status')->label('Estado')->options([
                    'draft' => 'Borrador',
                    'finalized' => 'Finalizado',
                    'delivered' => 'Entregado',
                ])->required(),

                Forms\Components\DateTimePicker::make('performed_start_at')->label('Inicio realización'),
                Forms\Components\DateTimePicker::make('performed_end_at')->label('Fin realización'),

                Forms\Components\Textarea::make('notes')->label('Notas del informe')->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Tabs::make('Componentes')->tabs($tabs)->columnSpanFull(),
        ])->columns(1);
    }

    protected function buildFieldsFromBlocks(array $blocks, string $prefix): array
    {
        $fields = [];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $config = $block['config'] ?? [];
            $key = $config['key'] ?? null;

            if (!$key) {
                // Layout blocks (header, section_title, divider) become placeholders
                if ($type === 'section_title') {
                    $fields[] = Forms\Components\Section::make($config['title'] ?? 'Sección')
                        ->description($config['description'] ?? null)
                        ->collapsible()
                        ->schema([]);
                }
                continue;
            }

            $fullKey = "{$prefix}.{$key}";
            $label = $config['label'] ?? $key;
            $required = $config['required'] ?? false;

            $field = match ($type) {
                'text_field' => Forms\Components\TextInput::make($fullKey)
                    ->label($label)
                    ->required($required)
                    ->placeholder($config['placeholder'] ?? ''),

                'number_field' => Forms\Components\TextInput::make($fullKey)
                    ->label($label)
                    ->numeric()
                    ->required($required)
                    ->suffix($config['unit'] ?? null),

                'date_field' => Forms\Components\DatePicker::make($fullKey)
                    ->label($label)
                    ->required($required),

                'text_area' => Forms\Components\Textarea::make($fullKey)
                    ->label($label)
                    ->rows($config['rows'] ?? 3)
                    ->required($required),

                'select_field' => Forms\Components\Select::make($fullKey)
                    ->label($label)
                    ->options(collect($config['options'] ?? [])->pluck('label', 'value')->all())
                    ->required($required),

                'tristate' => Forms\Components\Fieldset::make($label)->schema([
                    Forms\Components\Radio::make("{$fullKey}.value")
                        ->label('Estado')
                        ->options(['ok' => 'OK', 'nok' => 'NOK', 'na' => 'N/A'])
                        ->inline()
                        ->required($required),
                    ...($config['withObservation'] ?? true
                        ? [Forms\Components\Textarea::make("{$fullKey}.observation")
                            ->label('Observaciones')
                            ->rows(2)]
                        : []),
                ])->columns(1),

                'checklist' => Forms\Components\CheckboxList::make($fullKey)
                    ->label($label)
                    ->options(collect($config['items'] ?? [])->pluck('label', 'key')->all()),

                'table' => Forms\Components\Repeater::make($fullKey)
                    ->label($label)
                    ->schema(collect($config['columns'] ?? [])->map(fn ($col) => match ($col['type'] ?? 'text') {
                        'number' => Forms\Components\TextInput::make($col['key'])->label($col['label'] ?? '')->numeric(),
                        'tristate' => Forms\Components\Select::make($col['key'])->label($col['label'] ?? '')->options(['ok' => 'OK', 'nok' => 'NOK', 'na' => 'N/A']),
                        default => Forms\Components\TextInput::make($col['key'])->label($col['label'] ?? ''),
                    })->all())
                    ->columns(count($config['columns'] ?? []))
                    ->defaultItems(1)
                    ->reorderable(false),

                'image' => Forms\Components\FileUpload::make($fullKey)
                    ->label($label)
                    ->image()
                    ->multiple($config['multiple'] ?? false)
                    ->maxSize(($config['maxSizeMb'] ?? 5) * 1024),

                'signature' => Forms\Components\Textarea::make($fullKey)
                    ->label($label . ' (datos de firma)')
                    ->rows(2),

                default => null,
            };

            if ($field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('components');

        $data['componentData'] = [];
        foreach ($this->record->components as $c) {
            $data['componentData'][(string)$c->id] = $c->data_json ?? [];
        }

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $componentData = $data['componentData'] ?? [];
        unset($data['componentData']);

        $record->update([
            'status' => $data['status'] ?? $record->status,
            'performed_start_at' => $data['performed_start_at'] ?? $record->performed_start_at,
            'performed_end_at' => $data['performed_end_at'] ?? $record->performed_end_at,
            'notes' => $data['notes'] ?? $record->notes,
        ]);

        $record->load('components');
        foreach ($record->components as $component) {
            $payload = $componentData[(string)$component->id] ?? $component->data_json ?? [];
            $component->update(['data_json' => $payload]);
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('report.download', $this->record->id))
                ->openUrlInNewTab(),

            Actions\Action::make('view_pdf')
                ->label('Ver PDF')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => route('report.pdf', $this->record->id))
                ->openUrlInNewTab(),
        ];
    }
}
