<?php

namespace App\Filament\Resources\Transaccions\Schemas;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransaccionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('referencia')
                    ->required(),
                Select::make('concepto')
                    ->options(ConceptoTransaccion::class)
                    ->required(),
                Select::make('inscripcion_id')
                    ->relationship('inscripcion', 'id'),
                Select::make('asociado_id')
                    ->relationship('asociado', 'id'),
                TextInput::make('monto')
                    ->required()
                    ->numeric(),
                TextInput::make('moneda')
                    ->required()
                    ->default('COP'),
                Select::make('estado')
                    ->options(EstadoTransaccion::class)
                    ->default('pendiente')
                    ->required(),
                Select::make('metodo')
                    ->options(MetodoPago::class)
                    ->default('pse')
                    ->required(),
                Textarea::make('payload')
                    ->columnSpanFull(),
            ]);
    }
}
