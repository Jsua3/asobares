<?php

namespace App\Filament\Resources\Municipios\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MunicipioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
            ]);
    }
}
