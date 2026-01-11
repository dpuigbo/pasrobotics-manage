<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemResource\Pages;
use App\Models\System;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemResource extends Resource
{
    protected static ?string $model = System::class;
    protected static ?string $navigationGroup = 'Operación';
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $modelLabel = 'Sistema';
    protected static ?string $pluralModelLabel = 'Sistemas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('General')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del sistema')
                    ->required()
                    ->maxLength(150),

                Forms\Components\TextInput::make('manufacturer')
                    ->label('Fabricante (opcional)')
                    ->maxLength(50),

                // si luego enlazamos Clients, esto se convierte a Select->relationship(...)
                Forms\Components\TextInput::make('client_id')
                    ->label('Client ID (temporal)')
                    ->numeric()
                    ->helperText('Luego lo cambiaremos por un selector de cliente.'),

                Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Controladora (1 por sistema)')
                ->relationship('controllerUnit')
                ->schema([
                    Forms\Components\Select::make('controller_model_id')
                        ->label('Modelo de controladora')
                        ->relationship('controllerModel', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->manufacturer} {$record->name}"),

                    Forms\Components\TextInput::make('label')->label('Etiqueta')->placeholder('Cabinet / KRC4...'),
                    Forms\Components\TextInput::make('serial_number')->label('Nº Serie')->maxLength(100),
                    Forms\Components\DatePicker::make('manufactured_at')->label('Fabricación'),
                    Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Unidades mecánicas (1..N)')
                ->schema([
                    Forms\Components\Repeater::make('mechanicalUnits')
                        ->relationship()
                        ->label('Unidades mecánicas')
                        ->defaultItems(1)
                        ->schema([
                            Forms\Components\Select::make('robot_model_id')
                                ->label('Modelo robot')
                                ->relationship('robotModel', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->manufacturer} {$record->name}"),

                            Forms\Components\TextInput::make('label')
                                ->label('Etiqueta')
                                ->placeholder('ROB_1, ROB_2...')
                                ->maxLength(50),

                            Forms\Components\TextInput::make('serial_number')->label('Nº Serie')->maxLength(100),
                            Forms\Components\TextInput::make('axes_count')->label('Nº ejes')->numeric()->minValue(1)->maxValue(12),
                            Forms\Components\DatePicker::make('manufactured_at')->label('Fabricación'),
                            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->collapsed(),
                ]),

            Forms\Components\Section::make('Drive Units (0..N)')
                ->schema([
                    Forms\Components\Repeater::make('driveUnits')
                        ->relationship()
                        ->label('Drive Units')
                        ->schema([
                            Forms\Components\Select::make('drive_unit_model_id')
                                ->label('Modelo Drive Unit')
                                ->relationship('driveUnitModel', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->manufacturer} {$record->name}"),

                            Forms\Components\TextInput::make('label')
                                ->label('Etiqueta')
                                ->placeholder('DU_1, DU_2...')
                                ->maxLength(50),

                            Forms\Components\TextInput::make('serial_number')->label('Nº Serie')->maxLength(100),

                            Forms\Components\Select::make('system_mechanical_unit_id')
                                ->label('Asignada a unidad mecánica (opcional)')
                                ->options(fn (?System $record) => $record
                                    ? $record->mechanicalUnits()->orderBy('id')->pluck('label', 'id')->toArray()
                                    : [])
                                ->disabled(fn (?System $record) => !$record)
                                ->helperText('Disponible al editar (cuando el sistema ya está guardado).'),

                            Forms\Components\DatePicker::make('manufactured_at')->label('Fabricación'),
                            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->collapsed(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Sistema')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('manufacturer')->label('Fabricante')->toggleable()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSystems::route('/'),
        ];
    }
}
