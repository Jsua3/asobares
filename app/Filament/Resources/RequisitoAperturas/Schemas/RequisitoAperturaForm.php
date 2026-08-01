<?php

namespace App\Filament\Resources\RequisitoAperturas\Schemas;

use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RequisitoAperturaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('municipio_id')
                    ->relationship('municipio', 'id')
                    ->required(),
                TextInput::make('entidad')
                    ->required(),
                Textarea::make('descripcion')
                    ->columnSpanFull(),
                Textarea::make('checklist')
                    ->columnSpanFull(),
                TextInput::make('enlace_externo'),
                TextInput::make('adjunto'),
                TextInput::make('adjunto_nombre'),
                TextInput::make('costo_aproximado')
                    ->numeric(),
                TextInput::make('orden')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('estado')
                    ->options(EstadoPublicacion::class)
                    ->default('borrador')
                    ->required(),
            ]);
    }
}
