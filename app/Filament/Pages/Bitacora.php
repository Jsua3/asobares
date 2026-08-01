<?php

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

/**
 * RF-39. Quién hizo qué y cuándo, en español y sin jerga:
 * «Natalia aprobó el asociado X — hace 2 horas».
 */
class Bitacora extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.bitacora';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Bitácora';

    protected static ?string $navigationLabel = 'Bitácora';

    protected static ?string $slug = 'bitacora';

    /** Traducción de los eventos técnicos de activitylog. */
    private const array VERBOS = [
        'created' => 'creó',
        'updated' => 'actualizó',
        'deleted' => 'eliminó',
        'restored' => 'restauró',
    ];

    /** Nombre del tipo de contenido tal como lo llama el gremio. */
    private const array TIPOS = [
        'asociado' => 'el asociado',
        'evento' => 'el evento',
        'noticia' => 'la entrada del boletín',
        'requisito' => 'el requisito',
        'vacante' => 'la vacante',
        'artista' => 'el artista',
        'proveedor' => 'el proveedor',
        'aliado' => 'el aliado',
        'beneficio' => 'el beneficio',
        'municipio' => 'el municipio',
        'categoria' => 'la categoría',
        'usuario' => 'el usuario',
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ver_bitacora') === true;
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->with(['causer', 'subject']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Cuándo')
                    ->since()
                    ->tooltip(fn (Activity $registro): string => $registro->created_at->format('d/m/Y H:i'))
                    ->sortable(),
                TextColumn::make('descripcion_legible')
                    ->label('Qué pasó')
                    ->state(fn (Activity $registro): string => $this->frase($registro))
                    ->wrap()
                    ->weight('medium'),
                TextColumn::make('log_name')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('cambios')
                    ->label('Campos modificados')
                    ->state(fn (Activity $registro): string => $this->camposCambiados($registro))
                    ->wrap()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Tipo de contenido')
                    ->options(fn (): array => Activity::query()
                        ->distinct()
                        ->orderBy('log_name')
                        ->pluck('log_name', 'log_name')
                        ->all()),
                SelectFilter::make('causer_id')
                    ->label('Usuario')
                    ->options(fn (): array => User::orderBy('name')->pluck('name', 'id')->all()),
                Filter::make('ultima_semana')
                    ->label('Últimos 7 días')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subWeek())),
            ])
            ->emptyStateHeading('La bitácora está vacía')
            ->emptyStateDescription('Aquí quedará registrado cada cambio que se haga desde el panel.');
    }

    /** Arma «Natalia actualizó el asociado La Cava del Yipao». */
    private function frase(Activity $registro): string
    {
        $quien = $registro->causer?->name ?? 'El sistema';
        $verbo = self::VERBOS[$registro->event] ?? $registro->event;
        $tipo = self::TIPOS[$registro->log_name] ?? 'un registro';
        $etiqueta = $this->etiquetaDelSujeto($registro);

        return trim("{$quien} {$verbo} {$tipo} {$etiqueta}");
    }

    private function etiquetaDelSujeto(Activity $registro): string
    {
        $sujeto = $registro->subject;

        if ($sujeto === null) {
            // El registro ya no existe: se recupera el nombre de la instantánea.
            $propiedades = $registro->properties['attributes'] ?? [];

            return $propiedades['nombre'] ?? $propiedades['titulo'] ?? $propiedades['entidad'] ?? '(eliminado)';
        }

        return $sujeto->nombre ?? $sujeto->titulo ?? $sujeto->cargo ?? $sujeto->entidad ?? $sujeto->name ?? '';
    }

    private function camposCambiados(Activity $registro): string
    {
        $cambios = array_keys($registro->properties['attributes'] ?? []);

        return $cambios === [] ? '—' : implode(', ', $cambios);
    }
}
