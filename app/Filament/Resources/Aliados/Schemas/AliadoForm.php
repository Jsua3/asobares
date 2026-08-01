<?php

namespace App\Filament\Resources\Aliados\Schemas;

use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AliadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('logo'),
                TextInput::make('url')
                    ->url(),
                Textarea::make('descripcion')
                    ->columnSpanFull(),
                Textarea::make('detalle_convenio')
                    ->columnSpanFull(),
                TextInput::make('orden')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('estado')
                    ->options(EstadoPublicacion::class)
                    ->default('borrador')
                    ->required(),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
