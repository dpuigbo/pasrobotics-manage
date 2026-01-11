<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterventionResource\Pages;
use App\Models\Intervention;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InterventionResource extends Resource
{
    protected static ?string $model = Intervention::class;
    protected static ?string $navigationGroup = 'Intervenciones';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $modelLabel = 'Intervención';
    protected static ?string $pluralModelLabel = 'Intervenciones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('system_id')
                ->label('Sistema')
                ->relationship('system', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->required()
                ->options([
                    'preventive' => 'Preventivo',
                    'corrective' => 'Correctivo',
                ]),

            Forms\Components\Select::make('status')
                ->label('Estado')
                ->required()
                ->options([
                    'draft' => 'Borrador',
                    'finalized' => 'Finalizado',
                    'delivered' => 'Entregado',
                ])
                ->default('draft'),

            Forms\Components\DateTimePicker::make('performed_at')->label('Fecha/Hora intervención'),
            Forms\Components\TextInput::make('reference')->label('Referencia')->maxLength(50),
            Forms\Components\TextInput::make('title')->label('Título')->maxLength(150),

            Forms\Components\Textarea::make('notes')->label('Notas generales')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('system.name')->label('Sistema')->searchable(),
            Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Estado')->sortable(),
            Tables\Columns\TextColumn::make('performed_at')->label('Fecha')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('report')
                ->label('Informe')
                ->icon('heroicon-o-printer')
                ->url(fn (Intervention $record) => static::getUrl('report', ['record' => $record]))
                ->openUrlInNewTab(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ManageInterventions::route('/'),
            'report' => Pages\ViewInterventionReport::route('/{record}/report'),
        ];
    }

}
