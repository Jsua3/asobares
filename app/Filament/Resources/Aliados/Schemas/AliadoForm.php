<?php

namespace App\Filament\Resources\Aliados\Schemas;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoAliado;
use App\Filament\Forms\Components\SubidaSegura;
use App\Models\Aliado;
use App\Models\Municipio;
use App\Support\ReglaDeAlcaldias;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AliadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El aliado')
                    ->description('Aliados con convenio, distintos de los proveedores que pagan por aparecer.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Select::make('tipo')
                            ->label('Nivel')
                            ->options(TipoAliado::class)
                            ->default(TipoAliado::Comercial)
                            ->required()
                            ->live()
                            ->helperText('Institucional sale en la banda de arriba de la portada, aparte de las marcas con convenio.'),

                        // OBS3-05. Atar el aliado a un municipio es lo que lo
                        // convierte en «la alcaldía de X», y lo que activa la
                        // regla de «a todos o nada» (R21 03:47).
                        Select::make('municipio_id')
                            ->label('Alcaldía de')
                            ->relationship('municipio', 'nombre')
                            ->searchable()
                            ->preload()
                            ->visible(fn (callable $get): bool => $get('tipo') === TipoAliado::Institucional->value
                                || $get('tipo') === TipoAliado::Institucional)
                            ->helperText(fn (): string => static::avisoDeAlcaldias()),
                        TextInput::make('url')
                            ->label('Sitio web')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('orden')
                            ->label('Orden')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Menor número, aparece primero en el carrusel.'),
                        Toggle::make('activo')
                            ->label('Convenio activo')
                            ->helperText('Apágalo para retirarlo del sitio sin borrar el registro.')
                            ->inline(false),
                        Textarea::make('descripcion')
                            ->label('Descripción pública')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Convenio para afiliados')
                    ->description('Contenido privado: solo lo ven los empresarios afiliados en /mi-cuenta.')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Textarea::make('detalle_convenio')
                            ->label('Condiciones del convenio')
                            ->rows(4)
                            ->maxLength(2000)
                            ->placeholder('15 % de descuento en pedidos superiores a $500.000, entrega en Armenia.'),
                    ]),

                Section::make('Logo')
                    ->schema([
                        SubidaSegura::make('logo')
                            ->label('Logo del aliado')
                            ->imagen()
                            ->directory('aliados')
                            ->helperText('JPG, PNG o WebP, máximo 5 MB.'),
                    ]),

                Section::make('Publicación')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_aliado')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }

    /**
     * Le dice a quien clasifica un aliado en qué estado está la regla dura de
     * las alcaldías, porque la regla se aplica al pintar: si el juego está
     * incompleto el sitio no muestra ninguna, y sin este aviso el síntoma
     * --«cargué la alcaldía y no sale»-- parece un fallo.
     */
    private static function avisoDeAlcaldias(): string
    {
        $regla = app(ReglaDeAlcaldias::class);
        $faltantes = $regla->faltantes(Aliado::visible()->with('municipio')->get());

        if ($faltantes->isEmpty()) {
            return 'Déjalo vacío salvo que este aliado sea una alcaldía. Ahora mismo están las '
                .Municipio::query()->count().' alcaldías de los municipios cubiertos, así que la portada las muestra.';
        }

        return 'Déjalo vacío salvo que este aliado sea una alcaldía. Regla del gremio: se muestran todas o ninguna, '
            .'para no abrir susceptibilidades. Faltan '.$faltantes->count().' ('
            .$faltantes->pluck('nombre')->join(', ', ' y ').'), así que por ahora la portada no muestra ninguna.';
    }
}
