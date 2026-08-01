<?php

namespace App\Filament\Resources\Asociados\Schemas;

use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AsociadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('categoria_id')
                    ->relationship('categoria', 'id')
                    ->required(),
                Select::make('municipio_id')
                    ->relationship('municipio', 'id')
                    ->required(),
                Textarea::make('descripcion')
                    ->columnSpanFull(),
                TextInput::make('direccion'),
                TextInput::make('whatsapp'),
                TextInput::make('instagram_url')
                    ->url(),
                TextInput::make('sitio_web'),
                TextInput::make('google_maps_url')
                    ->url(),
                TextInput::make('tripadvisor_url')
                    ->url(),
                TextInput::make('horario'),
                TextInput::make('lat')
                    ->numeric(),
                TextInput::make('lng')
                    ->numeric(),
                TextInput::make('foto_portada'),
                Toggle::make('destacado')
                    ->required(),
                Select::make('estado')
                    ->options(EstadoPublicacion::class)
                    ->default('borrador')
                    ->required(),
                TextInput::make('representante'),
                TextInput::make('correo_interno'),
                TextInput::make('telefono_interno')
                    ->tel(),
                DatePicker::make('fecha_afiliacion'),
                Textarea::make('notas_internas')
                    ->columnSpanFull(),
            ]);
    }
}
