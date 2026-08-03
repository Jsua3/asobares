<?php

namespace App\Filament\Resources\Vacantes\Schemas;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoVacante;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VacanteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('La vacante')
                    ->description('Solo los establecimientos asociados publican vacantes: cada una va a nombre de uno.')
                    ->columns(2)
                    ->schema([
                        Select::make('asociado_id')
                            ->label('Establecimiento que busca')
                            ->relationship('asociado', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('cargo')
                            ->label('Cargo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Bartender'),
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(TipoVacante::class)
                            ->default(TipoVacante::TiempoCompleto)
                            ->required()
                            ->native(false),
                        TextInput::make('franja_horaria')
                            ->label('Franja horaria')
                            ->maxLength(255)
                            ->placeholder('Vie y sáb, 6:00 p. m. – 2:00 a. m.')
                            ->helperText('Para vacantes por turnos.'),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Contacto')
                    ->schema([
                        TextInput::make('whatsapp_contacto')
                            ->label('WhatsApp de contacto')
                            ->tel()
                            ->maxLength(30)
                            ->helperText('Donde el establecimiento quiere recibir a los candidatos.'),
                    ]),

                Section::make('Publicación')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_vacante')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }
}
