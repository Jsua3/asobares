<?php

namespace App\Filament\Resources\Beneficios\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BeneficioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo')
                    ->required(),
                Textarea::make('descripcion')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('icono')
                    ->required()
                    ->default('heroicon-o-check-badge'),
                TextInput::make('orden')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
