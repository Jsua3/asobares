<?php

namespace App\Filament\Support;

use App\Enums\EstadoPublicacion;
use App\Mail\FichaDeBolsaPublicada;
use App\Mail\VacanteAprobada;
use App\Mail\VacanteDevuelta;
use App\Models\Vacante;
use App\Support\DestinatariosDelAsociado;
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
                $registro->update(['estado' => EstadoPublicacion::Publicado]);

                Notification::make()
                    ->title('Contenido publicado')
                    ->success()
                    ->send();
            });
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
                $registro->update([
                    'estado' => EstadoPublicacion::Publicado,
                    'motivo_devolucion' => null,
                ]);

                $correos = DestinatariosDelAsociado::correos($registro->asociado);

                if ($correos !== []) {
                    Mail::to($correos)->send(new VacanteAprobada($registro));
                }

                Notification::make()->title('Vacante publicada')->success()->send();
            });
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
     * @param  string  $rutaPublica  nombre de ruta para armar el enlace, p. ej. `artistas.show`
     */
    public static function aprobarFichaDeBolsa(string $rutaPublica): Action
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
            ->action(function (Model $registro) use ($rutaPublica): void {
                $registro->update(['estado' => EstadoPublicacion::Publicado]);

                if (filled($registro->correo)) {
                    Mail::to($registro->correo)->send(
                        new FichaDeBolsaPublicada($registro->nombre, route($rutaPublica, $registro))
                    );
                }

                Notification::make()->title('Ficha publicada')->success()->send();
            });
    }

    /**
     * Aprobación en lote para vaciar la bandeja de pendientes de una vez.
     *
     * @param  string  $permiso  p. ej. `publicar_asociado`
     */
    public static function aprobarEnLote(string $permiso): BulkAction
    {
        return BulkAction::make('aprobar_lote')
            ->label('Aprobar y publicar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->visible(fn (): bool => auth()->user()?->can($permiso) === true)
            ->action(function (Collection $registros): void {
                // Se vuelve a comprobar registro por registro: la visibilidad
                // del botón nunca es la autorización.
                $publicados = $registros->filter(
                    fn (Model $registro): bool => auth()->user()?->can('publicar', $registro) === true
                );

                $publicados->each->update(['estado' => EstadoPublicacion::Publicado]);

                Notification::make()
                    ->title("{$publicados->count()} registros publicados")
                    ->success()
                    ->send();
            });
    }
}
