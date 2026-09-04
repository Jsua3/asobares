<?php

namespace App\Filament\Pages;

use App\Models\Asociado;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use UnitEnum;

/**
 * La cola de fotos que subieron los propietarios y nadie ha mirado (OBS3-13).
 *
 * El directivo puso la condición en el mismo momento en que se le dijo que el
 * afiliado sube fotos: «lo tienen que aprobar ellos, no sea que pongan
 * imágenes… exóticas» (R23 00:45-01:05).
 *
 * Es una página y no una pestaña dentro de la ficha del asociado a propósito:
 * moderar es trabajo por lotes --se hace una vez al día, mirando todo lo que
 * entró-- y no ficha por ficha. Escondida dentro de cada establecimiento, una
 * foto subida un martes puede no verla nadie en un mes.
 */
class ModerarFotos extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Fotos por aprobar';

    protected static ?string $navigationLabel = 'Fotos por aprobar';

    protected static ?string $slug = 'fotos-por-aprobar';

    protected string $view = 'filament.pages.moderar-fotos';

    /** Mismo permiso que publicar la ficha: quien publica un asociado modera sus fotos. */
    public static function canAccess(): bool
    {
        return auth()->user()?->can('publicar_asociado') === true;
    }

    /** El contador de la navegación es lo que hace que la cola no se olvide. */
    public static function getNavigationBadge(): ?string
    {
        $pendientes = self::consultaBase()->count();

        return $pendientes > 0 ? (string) $pendientes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => self::consultaBase())
            ->defaultSort('created_at')
            ->emptyStateHeading('No hay fotos esperando')
            ->emptyStateDescription('Cuando un afiliado suba una foto desde su cuenta, aparecerá aquí.')
            ->columns([
                ImageColumn::make('id')
                    ->label('Foto')
                    ->state(fn (Media $registro): string => $registro->getUrl())
                    ->height(80),
                TextColumn::make('model.nombre')
                    ->label('Establecimiento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre del archivo')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label('Subida')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publicar esta foto')
                    ->modalDescription('Quedará visible en la ficha pública del establecimiento.')
                    ->modalSubmitActionLabel('Sí, publicar')
                    ->action(function (Media $registro): void {
                        $registro->setCustomProperty(Asociado::FOTO_APROBADA, true);
                        // El motivo de una devolución anterior se borra: si se
                        // queda, el dueño ve «Publicada» y «te la devolvimos
                        // porque…» al mismo tiempo.
                        $registro->forgetCustomProperty(Asociado::FOTO_MOTIVO);
                        $registro->save();

                        Notification::make()->title('Foto publicada')->success()->send();
                    }),

                Action::make('devolver')
                    ->label('Devolver')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    // Devolver sin decir por qué obliga al afiliado a llamar a
                    // la oficina, que es el trabajo que esto viene a ahorrar.
                    // Mismo criterio que las vacantes: el motivo es obligatorio.
                    ->schema([
                        Textarea::make('motivo')
                            ->label('¿Por qué se devuelve?')
                            ->required()
                            ->minLength(10)
                            ->maxLength(500)
                            ->helperText('Lo lee el afiliado en su cuenta. Sé concreto: «se ve borrosa», «aparece una persona sin autorización».'),
                    ])
                    ->action(function (Media $registro, array $data): void {
                        $registro->setCustomProperty(Asociado::FOTO_APROBADA, false);
                        $registro->setCustomProperty(Asociado::FOTO_MOTIVO, $data['motivo']);
                        $registro->save();

                        Notification::make()->title('Foto devuelta')->warning()->send();
                    }),
            ]);
    }

    /**
     * Las fotos de galería que ningún moderador ha aprobado todavía.
     *
     * Aprobada es la EXCEPCIÓN y tiene que ser explícita: entra en la cola todo
     * lo que no sea literalmente `true`, incluida la foto cuya propiedad nunca
     * se escribió. Sin aprobar es el defecto, incluido el defecto silencioso.
     *
     * ⚠️ La primera versión de esto era `whereNot(whereJsonContains(…, true))`
     * y ESTABA ROTA EN POSTGRESQL, que es donde va a correr. Medido con las dos
     * gramáticas: emitía `not (("custom_properties"->'aprobada')::jsonb @> ?)`,
     * y sobre una fila sin la clave el `->` da NULL, `NULL @> 'true'` da NULL,
     * `not NULL` da NULL y el `WHERE` descarta la fila. O sea que la foto que
     * más falta hacía moderar —la que nadie marcó— era justo la que no salía.
     * En SQLite no se reproduce: el `exists` da false y `not false` la incluye,
     * así que la suite pasaba en verde con el defecto dentro.
     *
     * La condición de ahora añade la rama de la clave ausente, que en
     * PostgreSQL compila con el `coalesce` que salva la lógica trivaluada.
     * `ColaDeFotosTest` lo afirma sobre la SQL GENERADA por cada gramática y
     * no sobre el resultado, siguiendo el §15 del runbook: sobre SQLite una
     * prueba de comportamiento saldría verde con el código roto.
     *
     * @return Builder<Media>
     */
    private static function consultaBase(): Builder
    {
        return Media::query()
            ->where('model_type', Asociado::class)
            ->where('collection_name', 'galeria')
            ->where(fn (Builder $q) => $q
                ->whereJsonDoesntContain('custom_properties->'.Asociado::FOTO_APROBADA, true)
                ->orWhereJsonDoesntContainKey('custom_properties->'.Asociado::FOTO_APROBADA));
    }
}
