<?php

namespace App\Support;

use Filament\Forms;

class SchemaToFilament
{
    public static function build(array $schema, string $statePath = 'data_json'): array
    {
        $sections = $schema['sections'] ?? [];
        $out = [];

        foreach ($sections as $section) {
            $title = $section['title'] ?? 'Sección';
            $fields = $section['fields'] ?? [];

            $out[] = Forms\Components\Section::make($title)
                ->schema(self::buildFields($fields, $statePath))
                ->columns(2)
                ->collapsible();
        }

        return $out;
    }

    private static function buildFields(array $fields, string $statePath): array
    {
        $out = [];

        foreach ($fields as $f) {
            $key = $f['key'] ?? null;
            if (!$key) continue;

            $label = $f['label'] ?? $key;
            $type = $f['type'] ?? 'text';
            $required = (bool)($f['required'] ?? false);
            $help = $f['help'] ?? null;

            $full = $statePath . '.' . $key;

            if ($type === 'text') {
                $c = Forms\Components\TextInput::make($full)->label($label);
                if ($required) $c->required();
                if ($help) $c->helperText($help);
                $out[] = $c;
                continue;
            }

            if ($type === 'number') {
                $c = Forms\Components\TextInput::make($full)->label($label)->numeric();
                if ($required) $c->required();
                if ($help) $c->helperText($help);
                $out[] = $c;
                continue;
            }

            if ($type === 'date') {
                $c = Forms\Components\DatePicker::make($full)->label($label);
                if ($required) $c->required();
                if ($help) $c->helperText($help);
                $out[] = $c;
                continue;
            }

            if ($type === 'textarea') {
                $c = Forms\Components\Textarea::make($full)->label($label)->rows(3)->columnSpanFull();
                if ($required) $c->required();
                if ($help) $c->helperText($help);
                $out[] = $c;
                continue;
            }

            if ($type === 'select') {
                $options = [];
                foreach (($f['options'] ?? []) as $opt) {
                    if (isset($opt['value'])) $options[$opt['value']] = $opt['label'] ?? $opt['value'];
                }
                $c = Forms\Components\Select::make($full)->label($label)->options($options);
                if ($required) $c->required();
                if ($help) $c->helperText($help);
                $out[] = $c;
                continue;
            }

            if ($type === 'tristate') {
                $withObs = (bool)($f['with_observation'] ?? true);

                $out[] = Forms\Components\Fieldset::make($label)->schema([
                    Forms\Components\Radio::make($full . '.value')
                        ->label('Resultado')
                        ->options(['ok' => 'OK', 'nok' => 'NOK', 'na' => 'N/A'])
                        ->inline()
                        ->required($required),

                    Forms\Components\Textarea::make($full . '.observation')
                        ->label('Observación')
                        ->rows(2)
                        ->visible($withObs)
                        ->columnSpanFull(),
                ])->columnSpanFull();

                continue;
            }

            if ($type === 'table') {
                $cols = $f['columns'] ?? [];

                $rowSchema = [
                    Forms\Components\Hidden::make('_row_key'),
                    Forms\Components\TextInput::make('_row_label')->label('')->disabled(),
                ];

                foreach ($cols as $col) {
                    $ck = $col['key'] ?? null;
                    if (!$ck) continue;
                    $cl = $col['label'] ?? $ck;

                    $rowSchema[] = Forms\Components\TextInput::make($ck)->label($cl);
                }

                $out[] = Forms\Components\Repeater::make($full)
                    ->label($label)
                    ->schema($rowSchema)
                    ->disableItemCreation()
                    ->disableItemDeletion()
                    ->disableItemMovement()
                    ->columnSpanFull();

                continue;
            }
        }

        return $out;
    }
}
