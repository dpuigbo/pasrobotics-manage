<x-filament-panels::page>
    <style>
        @media print {
            .print-hidden { display: none !important; }
        }
        .report-block { border: 1px solid rgba(0,0,0,.15); border-radius: 10px; padding: 16px; }
        .report-title { font-weight: 700; font-size: 18px; }
        .report-sub { opacity: .75; font-size: 12px; }
        table.report-table { width: 100%; border-collapse: collapse; }
        table.report-table th, table.report-table td { border: 1px solid rgba(0,0,0,.15); padding: 6px; font-size: 12px; }
        .kv { display: grid; grid-template-columns: 220px 1fr; gap: 8px 12px; }
        .k { opacity:.7; }
        .v { font-weight: 600; }
    </style>

    <div class="print-hidden flex gap-2">
        <x-filament::button x-on:click="window.print()">Imprimir</x-filament::button>
        <x-filament::button color="gray" tag="a" href="{{ \App\Filament\Resources\InterventionResource::getUrl() }}">
            Volver
        </x-filament::button>
    </div>

    <div class="mt-4 space-y-6">
        <div class="report-block">
            <div class="report-title">Informe de intervención</div>
            <div class="report-sub">
                Sistema: <strong>{{ $this->record->system->name }}</strong> —
                Tipo: <strong>{{ $this->record->type }}</strong> —
                Estado: <strong>{{ $this->record->status }}</strong>
            </div>

            <div class="mt-3 kv">
                <div class="k">Fecha/Hora</div>
                <div class="v">{{ optional($this->record->performed_at)->format('Y-m-d H:i') }}</div>

                <div class="k">Referencia</div>
                <div class="v">{{ $this->record->reference ?? '—' }}</div>

                <div class="k">Título</div>
                <div class="v">{{ $this->record->title ?? '—' }}</div>

                <div class="k">Notas</div>
                <div class="v">{{ $this->record->notes ?? '—' }}</div>
            </div>
        </div>

        @foreach($this->record->components as $component)
            @php
                $schema = $component->schema_json ?? [];
                $sections = $schema['sections'] ?? [];
                $data = $component->data_json ?? [];
                $tpl = $component->templateVersion?->template?->name;
                $ver = $component->templateVersion?->version;
            @endphp

            <div class="report-block">
                <div class="report-title">
                    {{ strtoupper($component->component_type) }} — {{ $component->label }}
                </div>
                <div class="report-sub">
                    Plantilla: <strong>{{ $tpl ?? '—' }}</strong> ({{ $ver ?? '—' }})
                </div>

                <div class="mt-4 space-y-6">
                    @foreach($sections as $section)
                        <div>
                            <div class="font-semibold">{{ $section['title'] ?? 'Sección' }}</div>
                            @if(!empty($section['description']))
                                <div class="text-sm opacity-70">{{ $section['description'] }}</div>
                            @endif

                            <div class="mt-2 space-y-3">
                                @foreach(($section['fields'] ?? []) as $field)
                                    @php
                                        $key = $field['key'] ?? null;
                                        if (!$key) continue;
                                        $type = $field['type'] ?? 'text';
                                        $label = $field['label'] ?? $key;
                                        $value = $data[$key] ?? null;
                                    @endphp

                                    @if($type === 'table')
                                        @php
                                            $cols = $field['columns'] ?? [];
                                            $rows = is_array($value) ? $value : [];
                                        @endphp

                                        <div>
                                            <div class="font-medium">{{ $label }}</div>
                                            <div class="mt-2">
                                                <table class="report-table">
                                                    <thead>
                                                    <tr>
                                                        <th></th>
                                                        @foreach($cols as $c)
                                                            <th>{{ $c['label'] ?? $c['key'] ?? '' }}</th>
                                                        @endforeach
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($rows as $r)
                                                        <tr>
                                                            <td>{{ $r['_row_label'] ?? '' }}</td>
                                                            @foreach($cols as $c)
                                                                @php $ck = $c['key'] ?? null; @endphp
                                                                <td>{{ $ck ? ($r[$ck] ?? '—') : '—' }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    @elseif($type === 'tristate')
                                        @php
                                            $res = is_array($value) ? ($value['value'] ?? null) : null;
                                            $obs = is_array($value) ? ($value['observation'] ?? '') : '';
                                        @endphp
                                        <div class="kv">
                                            <div class="k">{{ $label }}</div>
                                            <div class="v">
                                                {{ $res ? strtoupper($res) : '—' }}
                                                @if($obs)
                                                    <div class="text-sm opacity-70 mt-1">{{ $obs }}</div>
                                                @endif
                                            </div>
                                        </div>

                                    @else
                                        <div class="kv">
                                            <div class="k">{{ $label }}</div>
                                            <div class="v">{{ $value ?? '—' }}</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
