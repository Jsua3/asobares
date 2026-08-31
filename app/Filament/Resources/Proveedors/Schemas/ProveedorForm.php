<?php

namespace App\Filament\Resources\Proveedors\Schemas;

use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProveedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El proveedor')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('categoria_proveedor')
                            ->label('Categoría')
                            ->options(CategoriaProveedor::class)
                            ->default(CategoriaProveedor::Otros)
                            ->required()
                            ->native(false),
                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->relationship('municipio', 'nombre')
                            ->searchable()
                            ->preload(),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Contacto')
                    ->columns(2)
                    ->schema([
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('correo')
                            ->label('Correo')
                            ->email()
                            ->maxLength(255),
                    ]),

                // OBS3-12. Seccion aparte de «Visibilidad» a proposito: que un
                // proveedor haya pagado por aparecer no dice que su telefono
                // siga sonando. Son dos preguntas distintas y confundirlas es
                // lo que produjo la queja del 28 de agosto.
                Section::make('Verificación del contacto')
                    ->description('Cada cuánto se confirma que el proveedor sigue existiendo y respondiendo. Se muestra en su ficha pública.')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('verificado_el')
                            ->label('Verificado el')
                            ->native(false)
                            ->maxDate(now())
                            ->helperText('El día en que alguien confirmó que este contacto responde. Vacío significa «nadie lo ha comprobado», y así sale en el sitio.'),
                        TextInput::make('verificado_con')
                            ->label('Verificado con')
                            ->maxLength(255)
                            ->placeholder('Llamada a Marta, 3xx xxx xxxx')
                            ->helperText('Con quién o por qué canal. Sirve para volver a preguntar sin empezar de cero.'),
                    ]),

                Section::make('Visibilidad')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('visible_hasta')
                            ->label('Visible hasta')
                            ->native(false)
                            ->helperText('Fecha en que la ficha deja de mostrarse. Es la palanca del cobro por aparecer en la bolsa.'),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_proveedor')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),

                Section::make('Habeas Data')
                    ->description('Consentimiento registrado si la ficha entró por el formulario público. No lo edites: es la constancia legal.')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Toggle::make('acepta_datos')
                            ->label('Aceptó el tratamiento de datos')
                            ->disabled(),
                        DateTimePicker::make('consentimiento_at')
                            ->label('Fecha del consentimiento')
                            ->disabled(),
                    ]),
            ]);
    }
}
