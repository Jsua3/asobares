<?php

namespace App\Filament\Resources\Proveedors\Schemas;

use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProveedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('categoria_proveedor')
                    ->options(CategoriaProveedor::class)
                    ->default('otros')
                    ->required(),
                Textarea::make('descripcion')
                    ->columnSpanFull(),
                TextInput::make('whatsapp'),
                TextInput::make('correo'),
                Select::make('municipio_id')
                    ->relationship('municipio', 'id'),
                DatePicker::make('visible_hasta'),
                Select::make('estado')
                    ->options(EstadoPublicacion::class)
                    ->default('borrador')
                    ->required(),
            ]);
    }
}
