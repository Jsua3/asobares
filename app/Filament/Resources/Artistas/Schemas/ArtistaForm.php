<?php

namespace App\Filament\Resources\Artistas\Schemas;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ArtistaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('tipo')
                    ->options(TipoArtista::class)
                    ->default('dj')
                    ->required(),
                TextInput::make('genero_musical'),
                Textarea::make('descripcion')
                    ->columnSpanFull(),
                TextInput::make('tarifa_desde')
                    ->numeric(),
                TextInput::make('video_url')
                    ->url(),
                TextInput::make('whatsapp'),
                TextInput::make('instagram_url')
                    ->url(),
                TextInput::make('foto'),
                Select::make('municipio_id')
                    ->relationship('municipio', 'id'),
                Select::make('estado')
                    ->options(EstadoPublicacion::class)
                    ->default('borrador')
                    ->required(),
            ]);
    }
}
