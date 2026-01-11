<?php

namespace App\Filament\Resources\ReportResource\Pages;

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

            $tabs[] = Forms\Components\Tabs\Tab::make($label)
                ->schema(SchemaToFilament::build($schema, "componentData.{$component->id}"));
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
            Actions\Action::make('view')
                ->label('Ver informe')
                ->icon('heroicon-o-printer')
                ->url(fn () => ReportResource::getUrl('report', ['record' => $this->record->id]))
                ->openUrlInNewTab(),
        ];
    }
}
