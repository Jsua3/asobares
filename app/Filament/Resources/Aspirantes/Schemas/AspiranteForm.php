<?php

namespace App\Filament\Resources\Aspirantes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AspiranteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vacante_id')
                    ->relationship('vacante', 'id'),
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('correo')
                    ->required(),
                TextInput::make('telefono')
                    ->tel(),
                TextInput::make('cargo_interes')
                    ->required(),
                Textarea::make('experiencia')
                    ->columnSpanFull(),
                Toggle::make('acepta_datos')
                    ->required(),
                DateTimePicker::make('consentimiento_at'),
            ]);
    }
}
