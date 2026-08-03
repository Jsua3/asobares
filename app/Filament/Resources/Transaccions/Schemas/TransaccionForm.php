<?php

namespace App\Filament\Resources\Transaccions\Schemas;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Las transacciones las produce la pasarela: este formulario existe solo
 * para inspección, por eso todos los campos van deshabilitados.
 */
class TransaccionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('La transacción')
                    ->columns(2)
                    ->schema([
                        TextInput::make('referencia')
                            ->label('Referencia')
                            ->disabled(),
                        Select::make('concepto')
                            ->label('Concepto')
                            ->options(ConceptoTransaccion::class)
                            ->disabled(),
                        Select::make('asociado_id')
                            ->label('Establecimiento')
                            ->relationship('asociado', 'nombre')
                            ->disabled(),
                        Select::make('inscripcion_id')
                            ->label('Inscripción')
                            ->relationship('inscripcion', 'nombre')
                            ->disabled(),
                        TextInput::make('monto')
                            ->label('Monto')
                            ->prefix('$')
                            ->disabled(),
                        TextInput::make('moneda')
                            ->label('Moneda')
                            ->disabled(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoTransaccion::class)
                            ->disabled(),
                        Select::make('metodo')
                            ->label('Método')
                            ->options(MetodoPago::class)
                            ->disabled(),
                        Textarea::make('payload')
                            ->label('Detalle técnico de la pasarela')
                            ->rows(4)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
