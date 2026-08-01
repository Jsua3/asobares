<?php

namespace App\Filament\Support;

use App\Enums\EstadoPublicacion;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
