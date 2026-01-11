<?php

namespace App\Filament\Resources\InterventionResource\Pages;

use App\Filament\Resources\InterventionResource;
use App\Support\SchemaToFilament;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class FillInterventionReport extends EditRecord
{
    protected static string $resource = InterventionResource::class;

    protected static ?string $title = 'Rellenar informe (por sistema)';

    public array $componentData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewReport')
                ->label('Ver informe')
                ->icon('heroicon-o-printer')
                ->url(fn () => InterventionResource::getUrl('report', ['record' => $this->record->getKey()]))
                ->openUrlInNewTab(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load(['components.templateVersion.template', 'system']);

        $data['componentData'] = [];
        foreach ($this->record->components as $c) {
            $data['componentData'][(string)$c->id] = $c->data_json ?? [];
        }

        return $data;
    }

    public function form(Form $form): Form
    {
        $this->record->load(['components.templateVersion.template', 'system']);

        $tabs = [];

        foreach ($this->record->components as $component) {
            $schema = is_array($component->schema_json) ? $component->schema_json : [];
            $tabLabel = strtoupper($component->component_type) . ' — ' . ($component->label ?? ('#' . $component->id));

            $tabs[] = Forms\Components\Tabs\Tab::make($tabLabel)
                ->schema(SchemaToFilament::build($schema, "componentData.{$component->id}"));
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Datos del informe')
                    ->schema([
                        Forms\Components\TextInput::make('id')->label('ID')->disabled(),
                        Forms\Components\TextInput::make('system.name')->label('Sistema')->disabled(),
                        Forms\Components\DateTimePicker::make('performed_at')->label('Fecha/Hora'),
                        Forms\Components\Select::make('status')->label('Estado')->options([
                            'draft' => 'Borrador',
                            'finalized' => 'Finalizado',
                            'delivered' => 'Entregado',
                        ]),
                        Forms\Components\Textarea::make('notes')->label('Notas generales')->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Tabs::make('Informe')
                    ->tabs($tabs)
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $componentData = $data['componentData'] ?? [];
        unset($data['componentData']);

        // Actualiza el informe (por sistema)
        $record->update([
            'performed_at' => $data['performed_at'] ?? $record->performed_at,
            'status' => $data['status'] ?? $record->status,
            'notes' => $data['notes'] ?? $record->notes,
        ]);

        // Guarda cada componente sin que el usuario lo note
        $record->load('components');
        foreach ($record->components as $component) {
            $payload = $componentData[(string)$component->id] ?? $component->data_json ?? [];
            $component->update(['data_json' => $payload]);
        }

        return $record;
    }
}
