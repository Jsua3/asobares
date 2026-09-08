<?php

namespace App\Filament\Resources\SolicitudAfiliacions\Schemas;

use App\Enums\EstadoSolicitudAfiliacion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SolicitudAfiliacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Solicitante')
                    ->description('Datos entregados por la persona que diligenció el formulario público.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('solicitante_nombre')->label('Nombre completo')->disabled(),
                        TextInput::make('solicitante_identificacion')->label('Identificación')->disabled(),
                        TextInput::make('solicitante_telefono')->label('Teléfono o WhatsApp')->disabled(),
                        TextInput::make('solicitante_correo')->label('Correo')->disabled(),
                        TextInput::make('solicitante_cargo')->label('Cargo o rol')->disabled(),
                    ]),

                Section::make('Establecimiento')
                    ->description('Información base para preparar la visita y, más adelante, crear el asociado.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('establecimiento_nombre')->label('Nombre comercial')->disabled(),
                        TextInput::make('razon_social')->label('Razón social')->disabled(),
                        TextInput::make('nit')->label('NIT')->disabled(),
                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->relationship('municipio', 'nombre')
                            ->disabled(),
                        TextInput::make('direccion')->label('Dirección')->disabled(),
                        TextInput::make('establecimiento_telefono')->label('Teléfono o WhatsApp')->disabled(),
                        TextInput::make('establecimiento_correo')->label('Correo del establecimiento')->disabled(),
                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->relationship('categoria', 'nombre')
                            ->disabled(),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(5)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Seguimiento')
                    ->description('En esta fase solo se registra el avance. Aprobar y crear asociado vendrá después.')
                    ->columns(2)
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoSolicitudAfiliacion::class)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('visita_programada_at')
                            ->label('Visita programada')
                            ->native(false),
                        DateTimePicker::make('resuelto_at')
                            ->label('Fecha de cierre')
                            ->native(false),
                        TextInput::make('asociado_id')
                            ->label('Asociado creado')
                            ->disabled(),
                        TextInput::make('user_id')
                            ->label('Usuario creado')
                            ->disabled(),
                        Textarea::make('gestion_notas')
                            ->label('Notas de seguimiento')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Habeas Data')
                    ->description('Constancia capturada al enviar el formulario.')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Toggle::make('acepta_datos')
                            ->label('Aceptó tratamiento de datos')
                            ->disabled(),
                        DateTimePicker::make('consentimiento_at')
                            ->label('Fecha del consentimiento')
                            ->disabled(),
                        TextInput::make('consentimiento_ip')
                            ->label('IP')
                            ->disabled(),
                        TextInput::make('consentimiento_politica')
                            ->label('Versión de política')
                            ->disabled(),
                    ]),
            ]);
    }
}
