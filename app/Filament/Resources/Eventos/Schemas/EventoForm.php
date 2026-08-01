<?php

namespace App\Filament\Resources\Eventos\Schemas;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoEvento;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('tipo')
                    ->options(TipoEvento::class)
                    ->default('evento')
                    ->required(),
                Textarea::make('descripcion')
                    ->columnSpanFull(),
                TextInput::make('lugar'),
                DateTimePicker::make('fecha_inicio')
                    ->required(),
                DateTimePicker::make('fecha_fin'),
                TextInput::make('imagen'),
                TextInput::make('cupos')
                    ->numeric(),
                TextInput::make('precio')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('permite_inscripcion')
                    ->required(),
                TextInput::make('enlace_externo'),
                Select::make('estado')
                    ->options(EstadoPublicacion::class)
                    ->default('borrador')
                    ->required(),
            ]);
    }
}
