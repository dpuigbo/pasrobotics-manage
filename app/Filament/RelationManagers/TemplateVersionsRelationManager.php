<?php

namespace App\Filament\RelationManagers;

use App\Models\ComponentModelTemplateVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TemplateVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'templateVersions';
    protected static ?string $title = 'Versiones de Template';
    protected static ?string $icon = 'heroicon-o-document-text';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('version')
                    ->label('Versión')
                    ->numeric()
                    ->default(fn () => ($this->getOwnerRecord()->templateVersions()->max('version') ?? 0) + 1)
                    ->required()
                    ->disabled(fn (?Model $record) => $record !== null),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'active' => 'Activo',
                        'deprecated' => 'Obsoleto',
                    ])
                    ->default('draft')
                    ->required(),
            ]),

            Forms\Components\Textarea::make('notes')
                ->label('Notas')
                ->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->label('Versión')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => "v{$state}")
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        'deprecated' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => 'Activo',
                        'draft' => 'Borrador',
                        'deprecated' => 'Obsoleto',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('blocks_count')
                    ->label('Bloques')
                    ->getStateUsing(function (Model $record) {
                        $schema = $record->schema ?? [];
                        return count($schema['blocks'] ?? []);
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('version', 'desc')
            ->actions([
                Tables\Actions\Action::make('open_editor')
                    ->label('Abrir editor')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn (Model $record) => "/admin/template-editor/{$record->id}"),

                Tables\Actions\Action::make('preview_pdf')
                    ->label('Vista previa PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (Model $record) => route('template.preview-pdf', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar versión')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Duplicar versión')
                    ->modalDescription('Se creará una nueva versión como borrador con el mismo contenido.')
                    ->action(function (Model $record) {
                        $newVersion = $record->replicate();
                        $newVersion->version = ($this->getOwnerRecord()->templateVersions()->max('version') ?? 0) + 1;
                        $newVersion->status = 'draft';
                        $newVersion->notes = "Duplicada desde v{$record->version}";
                        $newVersion->save();

                        Notification::make()
                            ->success()
                            ->title("Versión v{$newVersion->version} creada")
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->label('Editar meta')
                    ->icon('heroicon-o-cog-6-tooth'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva versión')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['schema'] = [
                            'blocks' => [
                                [
                                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                                    'type' => 'header',
                                    'config' => \App\BlockEditor\BlockRegistry::defaultConfig('header'),
                                ],
                                [
                                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                                    'type' => 'section_title',
                                    'config' => array_merge(
                                        \App\BlockEditor\BlockRegistry::defaultConfig('section_title'),
                                        ['title' => 'Inspección General']
                                    ),
                                ],
                                [
                                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                                    'type' => 'tristate',
                                    'config' => array_merge(
                                        \App\BlockEditor\BlockRegistry::defaultConfig('tristate'),
                                        ['key' => 'estado_general', 'label' => 'Estado general del equipo']
                                    ),
                                ],
                            ],
                            'pageConfig' => [
                                'orientation' => 'portrait',
                                'margins' => ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
                                'fontSize' => 10,
                            ],
                        ];
                        return $data;
                    })
                    ->after(function (Model $record) {
                        Notification::make()
                            ->success()
                            ->title("Template v{$record->version} creado")
                            ->body('Abre el editor para diseñar los bloques del informe.')
                            ->send();
                    }),
            ]);
    }
}
