<?php

namespace App\Filament\Support;

use App\Enums\EstadoPublicacion;
use App\Mail\FichaDeBolsaPublicada;
use App\Mail\VacanteAprobada;
use App\Mail\VacanteDevuelta;
use App\Models\Vacante;
use App\Support\DestinatariosDelAsociado;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * Las dos acciones con las que la dirección resuelve la bandeja de pendientes.
 *
 * Ambas se ocultan para quien no tenga permiso de publicar, y además la
 * policy vuelve a comprobarlo: la interfaz esconde, la policy impide.
 */
class AccionesDeAprobacion
{
    /** @return list<Action> */
    public static function paraFila(): array
    {
        return [self::aprobar(), self::devolver()];
    }

    public static function aprobar(): Action
    {
        return Action::make('aprobar')
            ->label('Aprobar y publicar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Publicar este contenido')
            ->modalDescription('Quedará visible en el sitio público de inmediato.')
            ->modalSubmitActionLabel('Sí, publicar')
            ->visible(fn (Model $registro): bool => $registro->estado !== EstadoPublicacion::Publicado
                && auth()->user()?->can('publicar', $registro) === true)
            ->action(function (Model $registro): void {
                self::publicar($registro);

                Notification::make()
                    ->title('Contenido publicado')
                    ->success()
                    ->send();
            });
    }

    private static function publicar(Model $registro): void
    {
        $registro->update(['estado' => EstadoPublicacion::Publicado]);
    }

    /**
     * Aprobación en lote genérica, para el contenido que solo necesita
     * cambiar de estado (asociados, eventos, iniciativas): mismo efecto que
     * `aprobar()` fila por fila.
     *
     * @param  string  $permiso  p. ej. `publicar_asociado`
     */
    public static function aprobarEnLote(string $permiso): BulkAction
    {
        return self::enLote($permiso, fn (Model $registro): mixed => self::publicar($registro));
    }

    public static function devolver(): Action
    {
        return Action::make('devolver')
            ->label('Devolver a borrador')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Devolver a borrador')
            ->modalDescription('Sale del sitio público y vuelve a quedar en edición.')
            ->modalSubmitActionLabel('Sí, devolver')
            ->visible(fn (Model $registro): bool => $registro->estado !== EstadoPublicacion::Borrador
                && auth()->user()?->can('publicar', $registro) === true)
            ->action(function (Model $registro): void {
                $registro->update(['estado' => EstadoPublicacion::Borrador]);

                Notification::make()
                    ->title('Contenido devuelto a borrador')
                    ->warning()
                    ->send();
            });
    }

    /**
     * Aprobar una vacante no es solo cambiar el estado: el asociado tiene que
     * enterarse, porque él no vive dentro del panel.
     */
    public static function aprobarVacante(): Action
    {
        return Action::make('aprobar')
            ->label('Aprobar y publicar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Publicar esta vacante')
            ->modalDescription('Quedará visible en la bolsa de empleo y le avisamos al establecimiento.')
            ->modalSubmitActionLabel('Sí, publicar')
            ->visible(fn (Vacante $registro): bool => $registro->estado !== EstadoPublicacion::Publicado
                && auth()->user()?->can('publicar', $registro) === true)
            ->action(function (Vacante $registro): void {
                self::publicarVacante($registro);

                Notification::make()->title('Vacante publicada')->success()->send();
            });
    }

    /**
     * El efecto real de aprobar una vacante, compartido por la fila y por el
     * lote: publicarla, borrar el motivo de una devolución anterior —si se
     * queda, la tarjeta del asociado dice «Publicada» y «pidieron un
     * ajuste» al mismo tiempo— y avisar al establecimiento.
     */
    private static function publicarVacante(Vacante $registro): void
    {
        $registro->update([
            'estado' => EstadoPublicacion::Publicado,
            'motivo_devolucion' => null,
        ]);

        $correos = DestinatariosDelAsociado::correos($registro->asociado);

        if ($correos !== []) {
            Mail::to($correos)->send(new VacanteAprobada($registro));
        }
    }

    /**
     * Devolver sin decir por qué obliga al asociado a llamar a la oficina.
     * El motivo es obligatorio y viaja hasta su cuenta y su correo.
     */
    public static function devolverConMotivo(): Action
    {
        return Action::make('devolver')
            ->label('Devolver al asociado')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->modalHeading('Devolver la vacante')
            ->modalSubmitActionLabel('Devolver')
            ->schema([
                Textarea::make('motivo_devolucion')
                    ->label('¿Qué tiene que corregir?')
                    ->rows(4)
                    ->required()
                    ->maxLength(600)
                    ->helperText('El asociado lo verá en su cuenta y le llegará por correo.'),
            ])
            ->visible(fn (Vacante $registro): bool => $registro->estado !== EstadoPublicacion::Borrador
                && auth()->user()?->can('publicar', $registro) === true)
            ->action(function (Vacante $registro, array $data): void {
                $registro->update([
                    'estado' => EstadoPublicacion::Borrador,
                    'motivo_devolucion' => $data['motivo_devolucion'],
                ]);

                $correos = DestinatariosDelAsociado::correos($registro->asociado);

                if ($correos !== []) {
                    Mail::to($correos)->send(new VacanteDevuelta($registro));
                }

                Notification::make()->title('Vacante devuelta al asociado')->warning()->send();
            });
    }

    /**
     * Aprobación de una ficha de artista o proveedor llegada por el formulario
     * público. Se avisa al solicitante si dejó correo.
     *
     * Recibe una función en vez de un nombre de ruta porque `route()` no es
     * seguro con un modelo de sobra: cuando la ruta no declara parámetros
     * (como `proveedores.index`), Laravel no lo descarta, lo cuelga como
     * query string (`/proveedores?el-slug-del-proveedor`). Cada recurso sabe
     * si su ruta pública necesita el registro o no, así que es quien decide
     * cómo construir el enlace: `fn (Model $r): string => route('artistas.show', $r)`
     * para artistas, `fn (): string => route('proveedores.index')` para
     * proveedores.
     *
     * @param  Closure(Model): string  $urlPublica
     */
    public static function aprobarFichaDeBolsa(Closure $urlPublica): Action
    {
        return Action::make('aprobar')
            ->label('Aprobar y publicar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Publicar esta ficha')
            ->modalDescription('Quedará visible en el sitio público de inmediato.')
            ->modalSubmitActionLabel('Sí, publicar')
            ->visible(fn (Model $registro): bool => $registro->estado !== EstadoPublicacion::Publicado
                && auth()->user()?->can('publicar', $registro) === true)
            ->action(function (Model $registro) use ($urlPublica): void {
                self::publicarFicha($registro, $urlPublica);

                Notification::make()->title('Ficha publicada')->success()->send();
            });
    }

    /**
     * El efecto real de aprobar una ficha de artista o proveedor, compartido
     * por la fila y por el lote.
     *
     * @param  Closure(Model): string  $urlPublica
     */
    private static function publicarFicha(Model $registro, Closure $urlPublica): void
    {
        $registro->update(['estado' => EstadoPublicacion::Publicado]);

        if (filled($registro->correo)) {
            Mail::to($registro->correo)->send(
                new FichaDeBolsaPublicada($registro->nombre, $urlPublica($registro))
            );
        }
    }

    /**
     * Aprobación en lote de vacantes para vaciar la bandeja de pendientes de
     * una vez: mismo efecto que `aprobarVacante()` fila por fila, así el
     * lote nunca deja un motivo de devolución colgado ni calla el correo.
     */
    public static function aprobarVacantesEnLote(): BulkAction
    {
        return self::enLote('publicar_vacante', fn (Vacante $registro): mixed => self::publicarVacante($registro));
    }

    /**
     * Aprobación en lote de fichas de artista o proveedor: mismo efecto que
     * `aprobarFichaDeBolsa()` fila por fila.
     *
     * @param  string  $permiso  p. ej. `publicar_artista`
     * @param  Closure(Model): string  $urlPublica
     */
    public static function aprobarFichasEnLote(string $permiso, Closure $urlPublica): BulkAction
    {
        return self::enLote($permiso, fn (Model $registro): mixed => self::publicarFicha($registro, $urlPublica));
    }

    /**
     * Esqueleto común a toda aprobación en lote: para que el lote sea de
     * verdad equivalente a aplicar la acción unitaria a cada registro,
     * filtra registro por registro con la misma condición que oculta la
     * acción de fila —estado distinto de publicado, y la policy, que la
     * visibilidad del botón nunca es la autorización—. Así un «seleccionar
     * todo» no le reescribe el estado ni reenvía el correo a lo que ya
     * estaba publicado.
     *
     * @param  string  $permiso  p. ej. `publicar_asociado`
     * @param  Closure(Model): mixed  $efecto
     */
    private static function enLote(string $permiso, Closure $efecto): BulkAction
    {
        return BulkAction::make('aprobar_lote')
            ->label('Aprobar y publicar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->visible(fn (): bool => auth()->user()?->can($permiso) === true)
            ->action(function (Collection $registros) use ($efecto): void {
                $publicados = $registros->filter(
                    fn (Model $registro): bool => $registro->estado !== EstadoPublicacion::Publicado
                        && auth()->user()?->can('publicar', $registro) === true
                );

                $publicados->each($efecto);

                Notification::make()
                    ->title("{$publicados->count()} registros publicados")
                    ->success()
                    ->send();
            });
    }
}
