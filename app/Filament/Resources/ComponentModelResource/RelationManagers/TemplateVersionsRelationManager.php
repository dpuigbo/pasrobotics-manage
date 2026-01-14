<?php

namespace App\Filament\Resources\ComponentModelResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TemplateVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'templateVersions';
    protected static ?string $title = 'Plantillas (versiones)';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version')
            ->columns([
                Tables\Columns\TextColumn::make('version')->label('Versión')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Añadir versión')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Si no especificas versión, auto-incrementa
                        if (empty($data['version'])) {
                            $max = $this->getOwnerRecord()
                                ->templateVersions()
                                ->max('version');

                            $data['version'] = ($max ?? 0) + 1;
                        }

                        // Normalizar schema: si te pegan JSON malformado, al menos lo dejamos como texto
                        $data['schema'] = trim((string)($data['schema'] ?? ''));

                        return $data;
                    })
                    ->form([
                        Forms\Components\TextInput::make('version')
                            ->label('Versión')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Déjalo vacío para autogenerar (max+1).'),

                        Forms\Components\Textarea::make('schema')
                            ->label('Schema (JSON)')
                            ->rows(16)
                            ->helperText('Define los campos del formulario en JSON. Luego lo usaremos para construir el informe.')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('version')
                            ->label('Versión')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\Textarea::make('schema')
                            ->label('Schema (JSON)')
                            ->rows(16)
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('version', 'desc');
    }
}
