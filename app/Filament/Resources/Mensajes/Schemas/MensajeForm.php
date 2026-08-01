<?php

namespace App\Filament\Resources\Mensajes\Schemas;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MensajeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')
                    ->options(TipoMensaje::class)
                    ->default('contacto')
                    ->required(),
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('correo')
                    ->required(),
                TextInput::make('telefono')
                    ->tel(),
                Textarea::make('mensaje')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('acepta_datos')
                    ->required(),
                DateTimePicker::make('consentimiento_at'),
                TextInput::make('radicado'),
                Select::make('estado')
                    ->options(EstadoMensaje::class)
                    ->default('nuevo')
                    ->required(),
                Textarea::make('nota_respuesta')
                    ->columnSpanFull(),
                DateTimePicker::make('respondido_at'),
            ]);
    }
}
