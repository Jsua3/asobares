<?php

namespace App\Filament\Resources\Inscripcions\Schemas;

use App\Enums\EstadoInscripcion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InscripcionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('evento_id')
                    ->relationship('evento', 'id')
                    ->required(),
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('correo')
                    ->required(),
                TextInput::make('telefono')
                    ->tel(),
                TextInput::make('establecimiento'),
                Toggle::make('acepta_datos')
                    ->required(),
                DateTimePicker::make('consentimiento_at'),
                Select::make('estado')
                    ->options(EstadoInscripcion::class)
                    ->default('registrada')
                    ->required(),
                Select::make('transaccion_id')
                    ->relationship('transaccion', 'id'),
            ]);
    }
}
