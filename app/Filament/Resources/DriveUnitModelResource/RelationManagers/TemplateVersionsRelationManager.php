<?php

namespace App\Filament\Resources\DriveUnitModelResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TemplateVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'templateVersions';
    protected static ?string $title = 'Plantillas de mantenimiento';
    protected static ?string $modelLabel = 'Plantilla';
    protected static ?string $pluralModelLabel = 'Plantillas';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('version')
                    ->label('Versión')
                    ->numeric()
                    ->default(function ($livewire) {
                        $maxVersion = $livewire->getOwnerRecord()
                            ->templateVersions()
                            ->max('version');
                        return ($maxVersion ?? 0) + 1;
                    })
                    ->required()
                    ->minValue(1),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'active' => 'Activa',
                        'deprecated' => 'Obsoleta',
                    ])
                    ->default('draft')
                    ->required(),

                Forms\Components\Placeholder::make('info')
                    ->label('')
                    ->content('💡 La plantilla activa se usará en nuevos informes'),
            ]),

            Forms\Components\Textarea::make('notes')
                ->label('Notas')
                ->rows(2)
                ->columnSpanFull()
                ->helperText('Notas internas sobre esta versión de la plantilla'),

            Forms\Components\Section::make('Constructor Visual de Plantilla')
                ->description('🎨 Construye tu formulario con bloques flexibles. Los campos se organizan en filas y puedes controlar su ancho.')
                ->schema([
                    Forms\Components\Repeater::make('schema.sections')
                        ->label('Secciones del formulario')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('📋 Título de la sección')
                                    ->required()
                                    ->placeholder('ej: Inspección General')
                                    ->columnSpan(2),

                                Forms\Components\Select::make('style')
                                    ->label('🎨 Estilo')
                                    ->options([
                                        'default' => 'Normal',
                                        'card' => 'Tarjeta',
                                        'bordered' => 'Con borde',
                                    ])
                                    ->default('default')
                                    ->columnSpan(1),
                            ]),

                            Forms\Components\Textarea::make('description')
                                ->label('Descripción (opcional)')
                                ->rows(2)
                                ->placeholder('Breve descripción de esta sección'),

                            Forms\Components\Builder::make('fields')
                                ->label('🎨 Canvas de Bloques - Arrastra y configura')
                                ->blockNumbers(false)
                                ->addActionLabel('➕ Añadir bloque al canvas')
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
                                ->itemLabel(function (array $state): string {
                                    $label = $state['label'] ?? 'Sin título';
                                    $category = $state['category'] ?? 'general';
                                    $width = $state['width'] ?? 'full';

                                    $categoryBadges = [
                                        'general' => '🟢 General',
                                        'level1' => '🔵 Nivel 1',
                                        'level2' => '🟡 Nivel 2',
                                        'level3' => '🔴 Nivel 3',
                                    ];

                                    $widthIndicators = [
                                        'full' => '█████████ 100%',
                                        'half' => '████▒▒▒▒▒ 50%',
                                        'third' => '███▒▒▒▒▒▒ 33%',
                                        'two-thirds' => '██████▒▒▒ 66%',
                                    ];

                                    $categoryBadge = $categoryBadges[$category] ?? $category;
                                    $widthIndicator = $widthIndicators[$width] ?? $width;

                                    return "📌 {$label} | {$categoryBadge} | {$widthIndicator}";
                                })
                                ->blocks([
                                    // BLOQUE: Texto corto
                                    Forms\Components\Builder\Block::make('text')
                                        ->label('📝 Texto corto')
                                        ->icon('heroicon-o-pencil')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General (todos los niveles)',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo (100%)',
                                                        'half' => 'Mitad (50%)',
                                                        'third' => 'Tercio (33%)',
                                                        'two-thirds' => 'Dos tercios (66%)',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required()
                                                ->placeholder('¿Qué pregunta hacemos?'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->placeholder('ej: temperatura_aceite')
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Obligatorio')
                                                    ->default(false),
                                                Forms\Components\Toggle::make('with_observation')
                                                    ->label('Con observaciones')
                                                    ->default(false),
                                            ]),
                                            Forms\Components\TextInput::make('placeholder')
                                                ->label('Texto de ayuda (placeholder)')
                                                ->placeholder('Aparece dentro del campo'),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda (descripción)')
                                                ->placeholder('Explicación adicional que aparece debajo del campo')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Número
                                    Forms\Components\Builder\Block::make('number')
                                        ->label('🔢 Número')
                                        ->icon('heroicon-o-hashtag')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo (100%)',
                                                        'half' => 'Mitad (50%)',
                                                        'third' => 'Tercio (33%)',
                                                        'two-thirds' => 'Dos tercios (66%)',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required()
                                                ->placeholder('¿Qué medimos?'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Obligatorio'),
                                                Forms\Components\Toggle::make('with_observation')
                                                    ->label('Con observaciones'),
                                            ]),
                                            Forms\Components\TextInput::make('placeholder')
                                                ->label('Unidad de medida')
                                                ->placeholder('ej: mm, kg, ºC'),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda (descripción)')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Fecha
                                    Forms\Components\Builder\Block::make('date')
                                        ->label('📅 Fecha')
                                        ->icon('heroicon-o-calendar')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                        'third' => 'Tercio',
                                                        'two-thirds' => 'Dos tercios',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required(),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Toggle::make('required')
                                                ->label('Obligatorio'),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Texto largo
                                    Forms\Components\Builder\Block::make('textarea')
                                        ->label('📄 Texto largo')
                                        ->icon('heroicon-o-document-text')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                        'third' => 'Tercio',
                                                        'two-thirds' => 'Dos tercios',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required(),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Toggle::make('required')
                                                ->label('Obligatorio'),
                                            Forms\Components\TextInput::make('placeholder')
                                                ->label('Texto de ayuda (placeholder)'),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda (descripción)')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Lista desplegable
                                    Forms\Components\Builder\Block::make('select')
                                        ->label('📑 Lista desplegable')
                                        ->icon('heroicon-o-queue-list')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                        'third' => 'Tercio',
                                                        'two-thirds' => 'Dos tercios',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required(),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Toggle::make('required')
                                                ->label('Obligatorio'),
                                            Forms\Components\Repeater::make('options')
                                                ->label('Opciones disponibles')
                                                ->schema([
                                                    Forms\Components\TextInput::make('value')
                                                        ->label('Valor')
                                                        ->required(),
                                                    Forms\Components\TextInput::make('label')
                                                        ->label('Texto')
                                                        ->required(),
                                                ])
                                                ->columns(2)
                                                ->defaultItems(2)
                                                ->collapsible()
                                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Opción'),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Tres estados (OK/Mal/N/A)
                                    Forms\Components\Builder\Block::make('tristate')
                                        ->label('✓✗ Tres estados')
                                        ->icon('heroicon-o-check-circle')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                        'third' => 'Tercio',
                                                        'two-thirds' => 'Dos tercios',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('¿Qué verificamos?')
                                                ->required()
                                                ->placeholder('ej: Estado del cableado'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Obligatorio')
                                                    ->default(true),
                                                Forms\Components\Toggle::make('with_observation')
                                                    ->label('Con campo de observaciones')
                                                    ->default(true)
                                                    ->helperText('Añade un área de texto'),
                                            ]),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                            Forms\Components\Placeholder::make('preview')
                                                ->label('')
                                                ->content('✓ OK  |  ✗ Mal  |  - N/A + Observaciones'),
                                        ]),

                                    // BLOQUE: Imagen
                                    Forms\Components\Builder\Block::make('image')
                                        ->label('📷 Imagen')
                                        ->icon('heroicon-o-photo')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                        'third' => 'Tercio',
                                                        'two-thirds' => 'Dos tercios',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required()
                                                ->placeholder('ej: Foto del componente'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Obligatorio'),
                                                Forms\Components\Toggle::make('multiple')
                                                    ->label('Múltiples imágenes')
                                                    ->helperText('Permitir subir varias imágenes'),
                                            ]),
                                            Forms\Components\TextInput::make('max_size')
                                                ->label('Tamaño máximo (MB)')
                                                ->numeric()
                                                ->default(5)
                                                ->minValue(1)
                                                ->maxValue(50),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Archivo
                                    Forms\Components\Builder\Block::make('file')
                                        ->label('📎 Archivo adjunto')
                                        ->icon('heroicon-o-paper-clip')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required()
                                                ->placeholder('ej: Documentos de calibración'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Obligatorio'),
                                                Forms\Components\Toggle::make('multiple')
                                                    ->label('Múltiples archivos'),
                                            ]),
                                            Forms\Components\TextInput::make('accepted_types')
                                                ->label('Tipos de archivo permitidos')
                                                ->placeholder('ej: pdf,doc,docx,xlsx')
                                                ->helperText('Separar con comas'),
                                            Forms\Components\TextInput::make('max_size')
                                                ->label('Tamaño máximo (MB)')
                                                ->numeric()
                                                ->default(10)
                                                ->minValue(1)
                                                ->maxValue(100),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Grupo de checkboxes
                                    Forms\Components\Builder\Block::make('checkbox_group')
                                        ->label('☑️ Grupo de checkboxes')
                                        ->icon('heroicon-o-check-badge')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Título del grupo')
                                                ->required()
                                                ->placeholder('ej: Verificaciones de seguridad'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Repeater::make('options')
                                                ->label('Opciones de checkbox')
                                                ->schema([
                                                    Forms\Components\TextInput::make('value')
                                                        ->label('Valor')
                                                        ->required(),
                                                    Forms\Components\TextInput::make('label')
                                                        ->label('Texto')
                                                        ->required(),
                                                ])
                                                ->columns(2)
                                                ->defaultItems(3)
                                                ->collapsible()
                                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Opción'),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\TextInput::make('min_selections')
                                                    ->label('Mínimo de selecciones')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->placeholder('0 = opcional'),
                                                Forms\Components\TextInput::make('max_selections')
                                                    ->label('Máximo de selecciones')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->placeholder('Dejar vacío = sin límite'),
                                            ]),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Radio buttons
                                    Forms\Components\Builder\Block::make('radio')
                                        ->label('🔘 Botones de opción')
                                        ->icon('heroicon-o-radio')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Pregunta')
                                                ->required()
                                                ->placeholder('ej: ¿Estado del equipo?'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Toggle::make('required')
                                                ->label('Obligatorio')
                                                ->default(true),
                                            Forms\Components\Repeater::make('options')
                                                ->label('Opciones de respuesta')
                                                ->schema([
                                                    Forms\Components\TextInput::make('value')
                                                        ->label('Valor')
                                                        ->required(),
                                                    Forms\Components\TextInput::make('label')
                                                        ->label('Texto')
                                                        ->required(),
                                                ])
                                                ->columns(2)
                                                ->defaultItems(3)
                                                ->collapsible()
                                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Opción'),
                                            Forms\Components\Select::make('layout')
                                                ->label('Disposición')
                                                ->options([
                                                    'vertical' => 'Vertical',
                                                    'horizontal' => 'Horizontal',
                                                ])
                                                ->default('vertical'),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                        ]),

                                    // BLOQUE: Firma
                                    Forms\Components\Builder\Block::make('signature')
                                        ->label('✍️ Firma digital')
                                        ->icon('heroicon-o-pencil-square')
                                        ->schema([
                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => 'General',
                                                        'level1' => 'Nivel 1',
                                                        'level2' => 'Nivel 2',
                                                        'level3' => 'Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->columnSpan(2),
                                                Forms\Components\Select::make('width')
                                                    ->label('📐 Ancho')
                                                    ->options([
                                                        'full' => 'Completo',
                                                        'half' => 'Mitad',
                                                    ])
                                                    ->default('full')
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Etiqueta')
                                                ->required()
                                                ->placeholder('ej: Firma del técnico'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),
                                            Forms\Components\Toggle::make('required')
                                                ->label('Obligatorio')
                                                ->default(true),
                                            Forms\Components\Textarea::make('help')
                                                ->label('Texto de ayuda')
                                                ->placeholder('Explicación adicional')
                                                ->rows(2),
                                            Forms\Components\Placeholder::make('info')
                                                ->label('')
                                                ->content('⚡ El técnico podrá firmar con el dedo o mouse'),
                                        ]),

                                    // BLOQUE: Tabla con campos anidados
                                    Forms\Components\Builder\Block::make('table')
                                        ->label('📊 Tabla de datos')
                                        ->icon('heroicon-o-table-cells')
                                        ->schema([
                                            Forms\Components\Select::make('category')
                                                ->label('🏷️ Nivel de mantenimiento')
                                                ->options([
                                                    'general' => 'General',
                                                    'level1' => 'Nivel 1',
                                                    'level2' => 'Nivel 2',
                                                    'level3' => 'Nivel 3',
                                                ])
                                                ->default('general')
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label('Título de la tabla')
                                                ->required()
                                                ->placeholder('ej: Lista de componentes revisados'),
                                            Forms\Components\TextInput::make('key')
                                                ->label('ID interno')
                                                ->required()
                                                ->regex('/^[a-z0-9_-]+$/'),

                                            Forms\Components\Repeater::make('columns')
                                                ->label('📊 Columnas de la tabla')
                                                ->schema([
                                                    Forms\Components\Select::make('type')
                                                        ->label('Tipo de campo')
                                                        ->options([
                                                            'text' => '📝 Texto',
                                                            'number' => '🔢 Número',
                                                            'date' => '📅 Fecha',
                                                            'select' => '📑 Lista desplegable',
                                                            'tristate' => '✓✗ Tres estados (OK/Mal/N/A)',
                                                        ])
                                                        ->default('text')
                                                        ->required()
                                                        ->live()
                                                        ->afterStateUpdated(function ($state, callable $set) {
                                                            // Reset conditional fields when type changes
                                                            if ($state !== 'number') $set('unit', null);
                                                            if ($state !== 'select') $set('options', null);
                                                        }),

                                                    Forms\Components\TextInput::make('label')
                                                        ->label('Encabezado de columna')
                                                        ->required()
                                                        ->placeholder('ej: Componente, Medida, Estado...'),

                                                    Forms\Components\TextInput::make('key')
                                                        ->label('ID interno')
                                                        ->required()
                                                        ->regex('/^[a-z0-9_-]+$/')
                                                        ->placeholder('ej: componente, medida_mm'),

                                                    // Campos condicionales según el tipo
                                                    Forms\Components\TextInput::make('placeholder')
                                                        ->label('Texto de ayuda')
                                                        ->placeholder('Aparece dentro del campo')
                                                        ->visible(fn (callable $get) => in_array($get('type'), ['text', 'number'])),

                                                    Forms\Components\TextInput::make('unit')
                                                        ->label('Unidad de medida')
                                                        ->placeholder('ej: mm, kg, ºC')
                                                        ->visible(fn (callable $get) => $get('type') === 'number'),

                                                    Forms\Components\Repeater::make('options')
                                                        ->label('Opciones disponibles')
                                                        ->simple(
                                                            Forms\Components\TextInput::make('value')
                                                                ->required()
                                                                ->placeholder('Opción')
                                                        )
                                                        ->defaultItems(2)
                                                        ->visible(fn (callable $get) => $get('type') === 'select')
                                                        ->columns(1),
                                                ])
                                                ->itemLabel(fn (array $state): ?string =>
                                                    ($state['label'] ?? 'Nueva columna') .
                                                    ' (' . match($state['type'] ?? 'text') {
                                                        'text' => '📝',
                                                        'number' => '🔢',
                                                        'date' => '📅',
                                                        'select' => '📑',
                                                        'tristate' => '✓✗',
                                                        default => '📝'
                                                    } . ')'
                                                )
                                                ->collapsible()
                                                ->collapsed()
                                                ->reorderable()
                                                ->cloneable()
                                                ->defaultItems(2)
                                                ->helperText('El técnico podrá añadir múltiples filas con estos campos en la tabla')
                                                ->columnSpanFull(),
                                        ]),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Nueva sección')
                        ->collapsed()
                        ->collapsible()
                        ->reorderable()
                        ->cloneable()
                        ->addActionLabel('➕ Añadir sección')
                        ->defaultItems(1)
                        ->default([[
                            'title' => 'Inspección General',
                            'description' => 'Verificaciones básicas del componente',
                            'fields' => [
                                [
                                    'type' => 'tristate',
                                    'data' => [
                                        'category' => 'general',
                                        'key' => 'estado_general',
                                        'label' => 'Estado general',
                                        'required' => true,
                                        'with_observation' => true,
                                    ],
                                ],
                            ],
                        ]]),
                ])
                ->columnSpanFull(),
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
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        'deprecated' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activa',
                        'draft' => 'Borrador',
                        'deprecated' => 'Obsoleta',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('version', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva plantilla'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalWidth('7xl'),
                Tables\Actions\EditAction::make()
                    ->modalWidth('7xl'),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
