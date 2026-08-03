<?php

namespace App\Filament\Resources\Inscripcions\Schemas;

use App\Enums\EstadoInscripcion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InscripcionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('La inscripción')
                    ->columns(2)
                    ->schema([
                        Select::make('evento_id')
                            ->label('Evento')
                            ->relationship('evento', 'titulo')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('correo')
                            ->label('Correo')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('establecimiento')
                            ->label('Establecimiento')
                            ->maxLength(255)
                            ->helperText('Si se inscribió en nombre de un negocio.'),
                    ]),

                Section::make('Estado y pago')
                    ->columns(2)
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoInscripcion::class)
                            ->default(EstadoInscripcion::Registrada)
                            ->required()
                            ->helperText('En eventos de pago, la confirmación la dispara el pago aprobado: no la marques a mano.'),
                        Select::make('transaccion_id')
                            ->label('Transacción')
                            ->relationship('transaccion', 'referencia')
                            ->disabled()
                            ->helperText('La crea la pasarela de pago.'),
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
