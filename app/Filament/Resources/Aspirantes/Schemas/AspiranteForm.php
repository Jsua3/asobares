<?php

namespace App\Filament\Resources\Aspirantes\Schemas;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AspiranteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El aspirante')
                    ->description('Perfil registrado desde la bolsa de empleo del sitio.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('cargo_interes')
                            ->label('Cargo de interés')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Bartender'),
                        TextInput::make('correo')
                            ->label('Correo')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(30),
                        Select::make('categoria_cargo')
                            ->label('Área del establecimiento')
                            ->options(CargoDelSector::class)
                            ->required()
                            ->native(false),
                        Select::make('estado')
                            ->label('Estado de gestión')
                            ->options(EstadoDeGestion::class)
                            ->required()
                            ->native(false),
                        Textarea::make('experiencia')
                            ->label('Experiencia')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Habeas Data')
                    ->description('Consentimiento registrado al enviar el formulario. No lo edites: es la constancia legal.')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Toggle::make('acepta_datos')
                            ->label('Aceptó el tratamiento de datos')
                            ->disabled(),
                        DateTimePicker::make('consentimiento_at')
                            ->label('Fecha del consentimiento')
                            ->disabled(),
                    ]),
            ]);
    }
}
