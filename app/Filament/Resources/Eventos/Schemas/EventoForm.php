<?php

namespace App\Filament\Resources\Eventos\Schemas;

use App\Enums\EstadoPublicacion;
use App\Enums\OrigenEvento;
use App\Enums\TipoEvento;
use App\Filament\Forms\Components\SubidaSegura;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El evento')
                    ->description('Eventos del gremio, aliados y comunidad.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Se usa en la dirección: /eventos/mi-evento'),
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(TipoEvento::class)
                            ->default(TipoEvento::Evento)
                            ->required()
                            ->native(false),
                        Select::make('origen')
                            ->label('Origen')
                            ->options(OrigenEvento::class)
                            ->default(OrigenEvento::Asobares)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (mixed $state, callable $set): void {
                                if ($state !== OrigenEvento::Aliado->value && $state !== OrigenEvento::Aliado) {
                                    $set('aliado_id', null);
                                }
                            }),
                        Select::make('aliado_id')
                            ->label('Aliado organizador')
                            ->relationship('aliado', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(fn (callable $get): bool => static::esEventoDeAliado($get('origen')))
                            ->visible(fn (callable $get): bool => static::esEventoDeAliado($get('origen')))
                            ->helperText('El evento solo sale en el sitio mientras este aliado esté publicado y activo.'),
                        TextInput::make('lugar')
                            ->label('Lugar')
                            ->maxLength(255)
                            ->placeholder('Centro de Convenciones, Armenia'),
                        DateTimePicker::make('fecha_inicio')
                            ->label('Empieza')
                            ->required()
                            ->native(false),
                        DateTimePicker::make('fecha_fin')
                            ->label('Termina')
                            ->native(false)
                            ->afterOrEqual('fecha_inicio')
                            ->helperText('Déjalo vacío si es de un solo momento.'),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                // El mismo contrato que la ficha de un asociado: con
                // coordenadas, el sitio puede pintar el punto en el mapa.
                Section::make('Ubicación')
                    ->description('Para mostrar el evento en un mapa. Todo es opcional.')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->relationship('municipio', 'nombre', fn ($query) => $query->where('activo', true)->orderBy('orden'))
                            ->searchable()
                            ->preload(),
                        TextInput::make('direccion')
                            ->label('Dirección')
                            ->maxLength(180)
                            ->placeholder('Carrera 14 # 23-15, piso 3'),
                        TextInput::make('lat')
                            ->label('Latitud')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90),
                        TextInput::make('lng')
                            ->label('Longitud')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),
                        TextInput::make('mapa_url')
                            ->label('Enlace de mapa')
                            ->rule('url:http,https')
                            ->maxLength(255)
                            ->helperText('Google Maps u OpenStreetMap. Si lo dejas vacío y hay coordenadas, se usa OpenStreetMap.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Inscripción y aforo')
                    ->columns(2)
                    ->schema([
                        Toggle::make('permite_inscripcion')
                            ->label('Permite inscripción en línea')
                            ->visible(fn (callable $get): bool => $get('origen') === OrigenEvento::Asobares->value || $get('origen') === OrigenEvento::Asobares)
                            ->helperText('Apágalo si la inscripción se hace por fuera del sitio.'),
                        TextInput::make('enlace_externo')
                            ->label('Enlace externo')
                            ->url()
                            ->maxLength(255)
                            ->helperText(fn (callable $get): string => static::esEventoDeAliado($get('origen'))
                                ? 'La inscripción de un evento de aliado la gestiona el aliado: el gremio no inscribe ni cobra a su nombre.'
                                : (($get('origen') === OrigenEvento::Comunidad->value || $get('origen') === OrigenEvento::Comunidad)
                                    ? 'Enlace externo del evento. ASOBARES no gestiona inscripciones de la comunidad.'
                                    : 'Para eventos cuya inscripción se gestiona por fuera del sitio.')),
                        TextInput::make('cupos')
                            ->label('Cupos')
                            ->visible(fn (callable $get): bool => $get('origen') !== OrigenEvento::Comunidad->value && $get('origen') !== OrigenEvento::Comunidad)
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Déjalo vacío si no hay límite.'),
                        TextInput::make('precio')
                            ->label('Precio')
                            ->visible(fn (callable $get): bool => $get('origen') !== OrigenEvento::Comunidad->value && $get('origen') !== OrigenEvento::Comunidad)
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('$')
                            ->helperText(fn (callable $get): string => static::esEventoDeAliado($get('origen'))
                                ? 'En pesos, solo informativo: el pago lo recibe el aliado.'
                                : 'En pesos. 0 = gratuito; con precio, la inscripción se confirma al aprobarse el pago.'),
                    ]),

                Section::make('Imagen')
                    ->schema([
                        SubidaSegura::make('imagen')
                            ->label('Imagen del evento')
                            ->imagen()
                            ->directory('eventos')
                            ->helperText('JPG, PNG o WebP, máximo 5 MB.'),
                    ]),

                Section::make('Publicación')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_evento')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }

    private static function esEventoDeAliado(mixed $origen): bool
    {
        return $origen === OrigenEvento::Aliado
            || $origen === OrigenEvento::Aliado->value;
    }
}
