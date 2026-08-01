<?php

namespace App\Filament\Resources\Vacantes\Schemas;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoVacante;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VacanteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('asociado_id')
                    ->relationship('asociado', 'id')
                    ->required(),
                TextInput::make('cargo')
                    ->required(),
                Select::make('tipo')
                    ->options(TipoVacante::class)
                    ->default('tiempo_completo')
                    ->required(),
                Textarea::make('descripcion')
                    ->columnSpanFull(),
                TextInput::make('franja_horaria'),
                TextInput::make('whatsapp_contacto'),
                Select::make('estado')
                    ->options(EstadoPublicacion::class)
                    ->default('borrador')
                    ->required(),
            ]);
    }
}
