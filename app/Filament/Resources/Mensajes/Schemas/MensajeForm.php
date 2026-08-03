<?php

namespace App\Filament\Resources\Mensajes\Schemas;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MensajeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El mensaje')
                    ->description('Llegó por un formulario del sitio. Los datos del remitente no se editan.')
                    ->columns(2)
                    ->schema([
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(TipoMensaje::class)
                            ->default(TipoMensaje::Contacto)
                            ->required()
                            ->native(false),
                        TextInput::make('radicado')
                            ->label('Radicado')
                            ->disabled()
                            ->helperText('Solo las PQR reciben radicado; lo genera el sistema.'),
                        TextInput::make('nombre')
                            ->label('Remitente')
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
                        Textarea::make('mensaje')
                            ->label('Mensaje')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Section::make('Gestión')
                    ->columns(2)
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoMensaje::class)
                            ->default(EstadoMensaje::Nuevo)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('respondido_at')
                            ->label('Respondido el')
                            ->native(false),
                        Textarea::make('nota_respuesta')
                            ->label('¿Qué se respondió?')
                            ->rows(3)
                            ->helperText('Queda como constancia interna del trámite.')
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
