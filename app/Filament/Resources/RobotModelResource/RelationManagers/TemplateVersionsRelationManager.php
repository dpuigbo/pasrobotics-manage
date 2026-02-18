<?php

namespace App\Filament\Resources\RobotModelResource\RelationManagers;

use App\Support\SchemaToFilament;
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
                            Forms\Components\TextInput::make('title')
                                ->label('📋 Título de la sección')
                                ->required()
                                ->placeholder('ej: Inspección General')
                                ->live(onBlur: true)
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('category')
                                    ->label('📂 Categoría de sección')
                                    ->options([
                                        'informacion_general' => '📋 Información general',
                                        'control_unidad_mecanica' => '🤖 Control de la unidad mecánica',
                                        'control_armario' => '🗄️ Control del armario',
                                        'control_programacion' => '💻 Control de la unidad de programación',
                                        'control_sistema' => '⚙️ Control del sistema',
                                        'intercambio_equipos' => '🔄 Intercambio de equipos',
                                        'observaciones_generales' => '📝 Observaciones generales',
                                        'estado_aceptacion' => '✅ Estado y aceptación',
                                    ])
                                    ->required()
                                    ->default('informacion_general')
                                    ->live()
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
                                ->blocks([
                                    // BLOQUE: Texto corto
                                    Forms\Components\Builder\Block::make('text')
                                        ->label('📝 Texto corto')
                                        ->icon('heroicon-o-pencil')
                                        ->schema([
                                            // Visual Preview Section
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Campo sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';

                                                            $categoryColors = [
                                                                'general' => '🟢',
                                                                'level1' => '🔵',
                                                                'level2' => '🟡',
                                                                'level3' => '🔴',
                                                            ];

                                                            $widthBars = [
                                                                'full' => '▓▓▓▓▓▓▓▓▓▓ 100%',
                                                                'half' => '▓▓▓▓▓░░░░░ 50%',
                                                                'third' => '▓▓▓░░░░░░░ 33%',
                                                                'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%',
                                                            ];

                                                            $categoryIcon = $categoryColors[$category] ?? '⚪';
                                                            $widthBar = $widthBars[$width] ?? $width;

                                                            return "📝 {$label}{$required}\n{$categoryIcon} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBar}";
                                                        })
                                                        ->columnSpanFull(),
                                                ])
                                                ->columnSpanFull()
                                                ->collapsed(false),

                                            // Configuration Section
                                            Forms\Components\Section::make('Configuración del campo')
                                                ->schema([
                                                    Forms\Components\TextInput::make('label')
                                                        ->label('📌 Etiqueta del campo')
                                                        ->required()
                                                        ->live(onBlur: true)
                                                        ->placeholder('¿Qué pregunta hacemos?')
                                                        ->columnSpanFull(),

                                                    Forms\Components\TextInput::make('key')
                                                        ->label('🔑 ID interno')
                                                        ->required()
                                                        ->placeholder('ej: temperatura_aceite')
                                                        ->regex('/^[a-z0-9_-]+$/')
                                                        ->helperText('Solo letras minúsculas, números, guiones y guiones bajos')
                                                        ->columnSpanFull(),

                                                    Forms\Components\Grid::make(3)->schema([
                                                        Forms\Components\Select::make('category')
                                                            ->label('🏷️ Nivel de mantenimiento')
                                                            ->options([
                                                                'general' => '🟢 General (todos los niveles)',
                                                                'level1' => '🔵 Nivel 1',
                                                                'level2' => '🟡 Nivel 2',
                                                                'level3' => '🔴 Nivel 3',
                                                            ])
                                                            ->default('general')
                                                            ->required()
                                                            ->live()
                                                            ->columnSpan(2),

                                                        Forms\Components\Select::make('width')
                                                            ->label('📐 Ancho en pantalla')
                                                            ->options([
                                                                'full' => '▓▓▓▓▓▓▓▓▓▓ 100%',
                                                                'half' => '▓▓▓▓▓░░░░░ 50%',
                                                                'third' => '▓▓▓░░░░░░░ 33%',
                                                                'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%',
                                                            ])
                                                            ->default('full')
                                                            ->required()
                                                            ->live()
                                                            ->columnSpan(1),
                                                    ]),
                                                ])
                                                ->columnSpanFull()
                                                ->collapsible(),

                                            // Options Section
                                            Forms\Components\Section::make('Opciones adicionales')
                                                ->schema([
                                                    Forms\Components\Grid::make(2)->schema([
                                                        Forms\Components\Toggle::make('required')
                                                            ->label('⚠️ Campo obligatorio')
                                                            ->default(false)
                                                            ->live()
                                                            ->inline(false),
                                                        Forms\Components\Toggle::make('with_observation')
                                                            ->label('💬 Permitir observaciones')
                                                            ->default(false)
                                                            ->inline(false),
                                                    ]),

                                                    Forms\Components\TextInput::make('placeholder')
                                                        ->label('💡 Texto de ayuda (placeholder)')
                                                        ->placeholder('Aparece dentro del campo cuando está vacío')
                                                        ->columnSpanFull(),

                                                    Forms\Components\Textarea::make('help')
                                                        ->label('📖 Descripción de ayuda')
                                                        ->placeholder('Explicación adicional que aparece debajo del campo')
                                                        ->rows(2)
                                                        ->columnSpanFull(),
                                                ])
                                                ->columnSpanFull()
                                                ->collapsible()
                                                ->collapsed(),
                                        ]),

                                    // BLOQUE: Número
                                    Forms\Components\Builder\Block::make('number')
                                        ->label('🔢 Número')
                                        ->icon('heroicon-o-hashtag')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Campo numérico sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $unit = $get('placeholder') ? ' (' . $get('placeholder') . ')' : '';
                                                            $required = $get('required') ? ' *' : '';

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'];

                                                            return "🔢 {$label}{$unit}{$required}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')
                                                    ->label('📌 Etiqueta del campo')
                                                    ->required()->live(onBlur: true)
                                                    ->placeholder('¿Qué medimos?')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')
                                                    ->label('🔑 ID interno')->required()
                                                    ->placeholder('ej: temperatura_motor')->regex('/^[a-z0-9_-]+$/')
                                                    ->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')
                                                        ->label('🏷️ Nivel de mantenimiento')
                                                        ->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])
                                                        ->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')
                                                        ->label('📐 Ancho en pantalla')
                                                        ->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'])
                                                        ->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones adicionales')->schema([
                                                Forms\Components\Grid::make(2)->schema([
                                                    Forms\Components\Toggle::make('required')->label('⚠️ Campo obligatorio')->default(false)->live()->inline(false),
                                                    Forms\Components\Toggle::make('with_observation')->label('💬 Permitir observaciones')->default(false)->inline(false),
                                                ]),
                                                Forms\Components\TextInput::make('placeholder')->label('📏 Unidad de medida')->placeholder('ej: mm, kg, ºC')->live(onBlur: true)->columnSpanFull(),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Fecha
                                    Forms\Components\Builder\Block::make('date')
                                        ->label('📅 Fecha')
                                        ->icon('heroicon-o-calendar')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Campo de fecha sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'];

                                                            return "📅 {$label}{$required}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')->label('📌 Etiqueta del campo')->required()->live(onBlur: true)->placeholder('ej: Fecha de mantenimiento')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')->label('🔑 ID interno')->required()->placeholder('ej: fecha_mantenimiento')->regex('/^[a-z0-9_-]+$/')->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')->label('🏷️ Nivel de mantenimiento')->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')->label('📐 Ancho en pantalla')->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'])->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones adicionales')->schema([
                                                Forms\Components\Toggle::make('required')->label('⚠️ Campo obligatorio')->default(false)->live()->inline(false),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Texto largo
                                    Forms\Components\Builder\Block::make('textarea')
                                        ->label('📄 Texto largo')
                                        ->icon('heroicon-o-document-text')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Campo de texto largo sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'];

                                                            return "📄 {$label}{$required}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')->label('📌 Etiqueta del campo')->required()->live(onBlur: true)->placeholder('ej: Observaciones generales')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')->label('🔑 ID interno')->required()->placeholder('ej: observaciones_generales')->regex('/^[a-z0-9_-]+$/')->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')->label('🏷️ Nivel de mantenimiento')->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')->label('📐 Ancho en pantalla')->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'])->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones adicionales')->schema([
                                                Forms\Components\Toggle::make('required')->label('⚠️ Campo obligatorio')->default(false)->live()->inline(false),
                                                Forms\Components\TextInput::make('placeholder')->label('💡 Texto de ayuda (placeholder)')->placeholder('Aparece dentro del campo cuando está vacío')->columnSpanFull(),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Lista desplegable
                                    Forms\Components\Builder\Block::make('select')
                                        ->label('📑 Lista desplegable')
                                        ->icon('heroicon-o-queue-list')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Lista desplegable sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';
                                                            $options = $get('options') ?: [];
                                                            $optCount = count($options);

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'];

                                                            return "📑 {$label}{$required}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}\n\n📋 Opciones configuradas: {$optCount}";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')->label('📌 Etiqueta del campo')->required()->live(onBlur: true)->placeholder('ej: Estado del equipo')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')->label('🔑 ID interno')->required()->placeholder('ej: estado_equipo')->regex('/^[a-z0-9_-]+$/')->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')->label('🏷️ Nivel de mantenimiento')->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')->label('📐 Ancho en pantalla')->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'])->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones de la lista')->schema([
                                                Forms\Components\Toggle::make('required')->label('⚠️ Campo obligatorio')->default(false)->live()->inline(false),
                                                Forms\Components\Repeater::make('options')
                                                    ->label('📋 Opciones disponibles')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('value')->label('Valor')->required(),
                                                        Forms\Components\TextInput::make('label')->label('Texto visible')->required(),
                                                    ])
                                                    ->columns(2)->defaultItems(2)->collapsible()
                                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Opción')
                                                    ->live()
                                                    ->columnSpanFull(),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Tres estados (OK/Mal/N/A)
                                    Forms\Components\Builder\Block::make('tristate')
                                        ->label('✓✗ Tres estados')
                                        ->icon('heroicon-o-check-circle')
                                        ->schema([
                                            // Visual Preview Section
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Verificación sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';
                                                            $withObs = $get('with_observation') ? ' + 💬 Observaciones' : '';

                                                            $categoryColors = [
                                                                'general' => '🟢',
                                                                'level1' => '🔵',
                                                                'level2' => '🟡',
                                                                'level3' => '🔴',
                                                            ];

                                                            $widthBars = [
                                                                'full' => '▓▓▓▓▓▓▓▓▓▓ 100%',
                                                                'half' => '▓▓▓▓▓░░░░░ 50%',
                                                                'third' => '▓▓▓░░░░░░░ 33%',
                                                                'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%',
                                                            ];

                                                            $categoryIcon = $categoryColors[$category] ?? '⚪';
                                                            $widthBar = $widthBars[$width] ?? $width;

                                                            return "✓✗ {$label}{$required}\n{$categoryIcon} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBar}\n\n📋 Vista previa: ✓ OK  |  ✗ Mal  |  - N/A{$withObs}";
                                                        })
                                                        ->columnSpanFull(),
                                                ])
                                                ->columnSpanFull()
                                                ->collapsed(false),

                                            // Configuration Section
                                            Forms\Components\Section::make('Configuración del campo')
                                                ->schema([
                                                    Forms\Components\TextInput::make('label')
                                                        ->label('📌 ¿Qué verificamos?')
                                                        ->required()
                                                        ->live(onBlur: true)
                                                        ->placeholder('ej: Estado del cableado')
                                                        ->columnSpanFull(),

                                                    Forms\Components\TextInput::make('key')
                                                        ->label('🔑 ID interno')
                                                        ->required()
                                                        ->placeholder('ej: estado_cableado')
                                                        ->regex('/^[a-z0-9_-]+$/')
                                                        ->helperText('Solo letras minúsculas, números, guiones y guiones bajos')
                                                        ->columnSpanFull(),

                                                    Forms\Components\Grid::make(3)->schema([
                                                        Forms\Components\Select::make('category')
                                                            ->label('🏷️ Nivel de mantenimiento')
                                                            ->options([
                                                                'general' => '🟢 General (todos los niveles)',
                                                                'level1' => '🔵 Nivel 1',
                                                                'level2' => '🟡 Nivel 2',
                                                                'level3' => '🔴 Nivel 3',
                                                            ])
                                                            ->default('general')
                                                            ->required()
                                                            ->live()
                                                            ->columnSpan(2),

                                                        Forms\Components\Select::make('width')
                                                            ->label('📐 Ancho en pantalla')
                                                            ->options([
                                                                'full' => '▓▓▓▓▓▓▓▓▓▓ 100%',
                                                                'half' => '▓▓▓▓▓░░░░░ 50%',
                                                                'third' => '▓▓▓░░░░░░░ 33%',
                                                                'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%',
                                                            ])
                                                            ->default('full')
                                                            ->required()
                                                            ->live()
                                                            ->columnSpan(1),
                                                    ]),
                                                ])
                                                ->columnSpanFull()
                                                ->collapsible(),

                                            // Options Section
                                            Forms\Components\Section::make('Opciones adicionales')
                                                ->schema([
                                                    Forms\Components\Grid::make(2)->schema([
                                                        Forms\Components\Toggle::make('required')
                                                            ->label('⚠️ Campo obligatorio')
                                                            ->default(true)
                                                            ->live()
                                                            ->inline(false),
                                                        Forms\Components\Toggle::make('with_observation')
                                                            ->label('💬 Con campo de observaciones')
                                                            ->default(true)
                                                            ->live()
                                                            ->inline(false)
                                                            ->helperText('Añade un área de texto para comentarios'),
                                                    ]),

                                                    Forms\Components\Textarea::make('help')
                                                        ->label('📖 Descripción de ayuda')
                                                        ->placeholder('Explicación adicional que aparece debajo del campo')
                                                        ->rows(2)
                                                        ->columnSpanFull(),
                                                ])
                                                ->columnSpanFull()
                                                ->collapsible()
                                                ->collapsed(),
                                        ]),

                                    // BLOQUE: Imagen
                                    Forms\Components\Builder\Block::make('image')
                                        ->label('📷 Imagen')
                                        ->icon('heroicon-o-photo')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Campo de imagen sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';
                                                            $multiple = $get('multiple') ? ' (múltiples)' : '';
                                                            $maxSize = $get('max_size') ?: 5;

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'];

                                                            return "📷 {$label}{$multiple}{$required}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}\n\n💾 Tamaño máx: {$maxSize} MB";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')->label('📌 Etiqueta del campo')->required()->live(onBlur: true)->placeholder('ej: Foto del componente')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')->label('🔑 ID interno')->required()->placeholder('ej: foto_componente')->regex('/^[a-z0-9_-]+$/')->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')->label('🏷️ Nivel de mantenimiento')->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')->label('📐 Ancho en pantalla')->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%', 'third' => '▓▓▓░░░░░░░ 33%', 'two-thirds' => '▓▓▓▓▓▓▓░░░ 67%'])->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones adicionales')->schema([
                                                Forms\Components\Grid::make(2)->schema([
                                                    Forms\Components\Toggle::make('required')->label('⚠️ Campo obligatorio')->default(false)->live()->inline(false),
                                                    Forms\Components\Toggle::make('multiple')->label('📸 Múltiples imágenes')->default(false)->live()->inline(false)->helperText('Permitir subir varias imágenes'),
                                                ]),
                                                Forms\Components\TextInput::make('max_size')->label('💾 Tamaño máximo (MB)')->numeric()->default(5)->minValue(1)->maxValue(50)->live(onBlur: true),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Archivo
                                    Forms\Components\Builder\Block::make('file')
                                        ->label('📎 Archivo adjunto')
                                        ->icon('heroicon-o-paper-clip')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Campo de archivo sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';
                                                            $multiple = $get('multiple') ? ' (múltiples)' : '';
                                                            $maxSize = $get('max_size') ?: 10;
                                                            $types = $get('accepted_types') ?: 'todos';

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%'];

                                                            return "📎 {$label}{$multiple}{$required}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}\n\n💾 Tamaño máx: {$maxSize} MB | 📄 Tipos: {$types}";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')->label('📌 Etiqueta del campo')->required()->live(onBlur: true)->placeholder('ej: Documentos de calibración')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')->label('🔑 ID interno')->required()->placeholder('ej: docs_calibracion')->regex('/^[a-z0-9_-]+$/')->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')->label('🏷️ Nivel de mantenimiento')->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')->label('📐 Ancho en pantalla')->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%'])->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones adicionales')->schema([
                                                Forms\Components\Grid::make(2)->schema([
                                                    Forms\Components\Toggle::make('required')->label('⚠️ Campo obligatorio')->default(false)->live()->inline(false),
                                                    Forms\Components\Toggle::make('multiple')->label('📁 Múltiples archivos')->default(false)->live()->inline(false),
                                                ]),
                                                Forms\Components\TextInput::make('accepted_types')->label('📄 Tipos de archivo permitidos')->placeholder('ej: pdf,doc,docx,xlsx')->helperText('Separar con comas')->live(onBlur: true)->columnSpanFull(),
                                                Forms\Components\TextInput::make('max_size')->label('💾 Tamaño máximo (MB)')->numeric()->default(10)->minValue(1)->maxValue(100)->live(onBlur: true),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Grupo de checkboxes
                                    Forms\Components\Builder\Block::make('checkbox_group')
                                        ->label('☑️ Grupo de checkboxes')
                                        ->icon('heroicon-o-check-badge')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Grupo de checkboxes sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $options = $get('options') ?: [];
                                                            $optCount = count($options);
                                                            $min = $get('min_selections') ?: 0;
                                                            $max = $get('max_selections') ?: '∞';

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%'];

                                                            return "☑️ {$label}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}\n\n📋 Opciones: {$optCount} | Selecciones: {$min}-{$max}";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')->label('📌 Título del grupo')->required()->live(onBlur: true)->placeholder('ej: Verificaciones de seguridad')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')->label('🔑 ID interno')->required()->placeholder('ej: verificaciones_seguridad')->regex('/^[a-z0-9_-]+$/')->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')->label('🏷️ Nivel de mantenimiento')->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')->label('📐 Ancho en pantalla')->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%'])->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones y validación')->schema([
                                                Forms\Components\Repeater::make('options')
                                                    ->label('📋 Opciones de checkbox')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('value')->label('Valor')->required(),
                                                        Forms\Components\TextInput::make('label')->label('Texto visible')->required(),
                                                    ])
                                                    ->columns(2)->defaultItems(3)->collapsible()
                                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Opción')
                                                    ->live()
                                                    ->columnSpanFull(),
                                                Forms\Components\Grid::make(2)->schema([
                                                    Forms\Components\TextInput::make('min_selections')->label('Mínimo de selecciones')->numeric()->minValue(0)->placeholder('0 = opcional')->live(onBlur: true),
                                                    Forms\Components\TextInput::make('max_selections')->label('Máximo de selecciones')->numeric()->minValue(1)->placeholder('Dejar vacío = sin límite')->live(onBlur: true),
                                                ]),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Radio buttons
                                    Forms\Components\Builder\Block::make('radio')
                                        ->label('🔘 Botones de opción')
                                        ->icon('heroicon-o-radio')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Pregunta sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';
                                                            $layout = $get('layout') ?: 'vertical';
                                                            $options = $get('options') ?: [];
                                                            $optCount = count($options);

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%'];

                                                            return "🔘 {$label}{$required}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}\n\n📋 Opciones: {$optCount} | Disposición: " . ucfirst($layout);
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')->label('📌 Pregunta')->required()->live(onBlur: true)->placeholder('ej: ¿Estado del equipo?')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')->label('🔑 ID interno')->required()->placeholder('ej: estado_equipo')->regex('/^[a-z0-9_-]+$/')->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')->label('🏷️ Nivel de mantenimiento')->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')->label('📐 Ancho en pantalla')->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%'])->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones de respuesta')->schema([
                                                Forms\Components\Toggle::make('required')->label('⚠️ Campo obligatorio')->default(true)->live()->inline(false),
                                                Forms\Components\Repeater::make('options')
                                                    ->label('📋 Opciones disponibles')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('value')->label('Valor')->required(),
                                                        Forms\Components\TextInput::make('label')->label('Texto visible')->required(),
                                                    ])
                                                    ->columns(2)->defaultItems(3)->collapsible()
                                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Opción')
                                                    ->live()
                                                    ->columnSpanFull(),
                                                Forms\Components\Select::make('layout')->label('📐 Disposición')->options(['vertical' => '⬇️ Vertical', 'horizontal' => '➡️ Horizontal'])->default('vertical')->live(),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Firma
                                    Forms\Components\Builder\Block::make('signature')
                                        ->label('✍️ Firma digital')
                                        ->icon('heroicon-o-pencil-square')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Firma sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $width = $get('width') ?: 'full';
                                                            $required = $get('required') ? ' *' : '';

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];
                                                            $widthBars = ['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%'];

                                                            return "✍️ {$label}{$required}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📐 Ancho: {$widthBars[$width]}\n\n⚡ El técnico podrá firmar con el dedo o mouse";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración del campo')->schema([
                                                Forms\Components\TextInput::make('label')->label('📌 Etiqueta del campo')->required()->live(onBlur: true)->placeholder('ej: Firma del técnico')->columnSpanFull(),
                                                Forms\Components\TextInput::make('key')->label('🔑 ID interno')->required()->placeholder('ej: firma_tecnico')->regex('/^[a-z0-9_-]+$/')->helperText('Solo letras minúsculas, números, guiones y guiones bajos')->columnSpanFull(),
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\Select::make('category')->label('🏷️ Nivel de mantenimiento')->options(['general' => '🟢 General (todos los niveles)', 'level1' => '🔵 Nivel 1', 'level2' => '🟡 Nivel 2', 'level3' => '🔴 Nivel 3'])->default('general')->required()->live()->columnSpan(2),
                                                    Forms\Components\Select::make('width')->label('📐 Ancho en pantalla')->options(['full' => '▓▓▓▓▓▓▓▓▓▓ 100%', 'half' => '▓▓▓▓▓░░░░░ 50%'])->default('full')->required()->live()->columnSpan(1),
                                                ]),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Opciones adicionales')->schema([
                                                Forms\Components\Toggle::make('required')->label('⚠️ Campo obligatorio')->default(true)->live()->inline(false),
                                                Forms\Components\Textarea::make('help')->label('📖 Descripción de ayuda')->placeholder('Explicación adicional')->rows(2)->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),

                                    // BLOQUE: Tabla con campos anidados
                                    Forms\Components\Builder\Block::make('table')
                                        ->label('📊 Tabla de datos')
                                        ->icon('heroicon-o-table-cells')
                                        ->schema([
                                            Forms\Components\Section::make()
                                                ->schema([
                                                    Forms\Components\Placeholder::make('preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $label = $get('label') ?: 'Tabla sin título';
                                                            $category = $get('category') ?: 'general';
                                                            $columns = $get('columns') ?: [];
                                                            $colCount = count($columns);

                                                            $categoryColors = ['general' => '🟢', 'level1' => '🔵', 'level2' => '🟡', 'level3' => '🔴'];

                                                            return "📊 {$label}\n{$categoryColors[$category]} Categoría: " . ucfirst($category) . " | 📋 Columnas: {$colCount}\n\n⚡ El técnico podrá añadir múltiples filas a esta tabla";
                                                        })
                                                        ->columnSpanFull(),
                                                ])->columnSpanFull()->collapsed(false),

                                            Forms\Components\Section::make('Configuración de la tabla')->schema([
                                                Forms\Components\TextInput::make('label')
                                                    ->label('📌 Título de la tabla')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->placeholder('ej: Lista de componentes revisados')
                                                    ->columnSpanFull(),

                                                Forms\Components\TextInput::make('key')
                                                    ->label('🔑 ID interno')
                                                    ->required()
                                                    ->placeholder('ej: lista_componentes')
                                                    ->regex('/^[a-z0-9_-]+$/')
                                                    ->helperText('Solo letras minúsculas, números, guiones y guiones bajos')
                                                    ->columnSpanFull(),

                                                Forms\Components\Select::make('category')
                                                    ->label('🏷️ Nivel de mantenimiento')
                                                    ->options([
                                                        'general' => '🟢 General (todos los niveles)',
                                                        'level1' => '🔵 Nivel 1',
                                                        'level2' => '🟡 Nivel 2',
                                                        'level3' => '🔴 Nivel 3',
                                                    ])
                                                    ->default('general')
                                                    ->required()
                                                    ->live(),
                                            ])->columnSpanFull()->collapsible(),

                                            Forms\Components\Section::make('Columnas de la tabla')->schema([
                                                Forms\Components\Repeater::make('columns')
                                                    ->label('📊 Definir columnas')
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
                                                ->live()
                                                ->columnSpanFull(),
                                            ])->columnSpanFull()->collapsible()->collapsed(),
                                        ]),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->itemLabel(fn (array $state): ?string =>
                            ($state['title'] ?? 'Nueva sección') . ' • ' . match($state['category'] ?? 'informacion_general') {
                                'informacion_general' => '📋 Info General',
                                'control_unidad_mecanica' => '🤖 Unidad Mecánica',
                                'control_armario' => '🗄️ Armario',
                                'control_programacion' => '💻 Programación',
                                'control_sistema' => '⚙️ Sistema',
                                'intercambio_equipos' => '🔄 Intercambio',
                                'observaciones_generales' => '📝 Observaciones',
                                'estado_aceptacion' => '✅ Estado',
                                default => '📋 Info General',
                            }
                        )
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
                Tables\Actions\Action::make('preview_form')
                    ->label('Vista previa')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn ($record) => 'Vista previa del formulario — Plantilla v' . $record->version)
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(function ($record) {
                        $rawSections = $record->schema['sections'] ?? [];

                        $transformedSections = array_map(function ($section) {
                            $rawFields = $section['fields'] ?? [];
                            $fields = array_map(
                                fn ($block) => array_merge(['type' => $block['type']], $block['data'] ?? []),
                                $rawFields
                            );
                            return ['title' => $section['title'] ?? 'Sección', 'fields' => $fields];
                        }, $rawSections);

                        return SchemaToFilament::build(['sections' => $transformedSections], 'preview');
                    }),
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
