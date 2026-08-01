<?php

namespace App\Observers;

use App\Enums\EstadoPublicacion;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Hace cumplir el flujo de aprobación en el modelo, no en el formulario.
 *
 * Da igual que alguien altere el HTML o mande la petición a mano: si el
 * usuario no tiene permiso de publicar ese recurso, el registro se guarda
 * como `pendiente_aprobacion` (RF-37).
 */
class FlujoDeAprobacionObserver
{
    public function saving(Model $modelo): void
    {
        $usuario = Auth::user();

        // Semillas, comandos de consola y jobs no pasan por el flujo.
        if (! $usuario instanceof User) {
            return;
        }

        if ($modelo->estado !== EstadoPublicacion::Publicado) {
            return;
        }

        if ($usuario->cannot('publicar', $modelo)) {
            $modelo->estado = EstadoPublicacion::PendienteAprobacion;
        }
    }

    public function saved(Model $modelo): void
    {
        if ($modelo->estado !== EstadoPublicacion::PendienteAprobacion) {
            return;
        }

        // La notificación existe porque alguien envió algo a revisión: si no
        // hay sesión (semillas, consola, jobs) no hay a quién avisarle de qué.
        if (! Auth::user() instanceof User) {
            return;
        }

        // Tras un INSERT, `wasChanged` siempre es falso porque el original ya
        // se sincronizó; hay que mirar `wasRecentlyCreated` aparte.
        if (! $modelo->wasRecentlyCreated && ! $modelo->wasChanged('estado')) {
            return;
        }

        $this->avisarALaDireccion($modelo);
    }

    /** Notificación de base de datos: el súper admin la ve con badge en el panel. */
    private function avisarALaDireccion(Model $modelo): void
    {
        $autor = Auth::user()?->name ?? 'El sistema';
        $etiqueta = $modelo->nombre ?? $modelo->titulo ?? $modelo->cargo ?? $modelo->entidad ?? "#{$modelo->getKey()}";
        $tipo = class_basename($modelo);

        $direccion = User::role(User::ROL_SUPER_ADMIN)->get();

        foreach ($direccion as $superAdmin) {
            Notification::make()
                ->title('Contenido pendiente de aprobación')
                ->body("{$autor} envió «{$etiqueta}» ({$tipo}) para revisión.")
                ->icon('heroicon-o-clock')
                ->iconColor('warning')
                ->actions([
                    Action::make('revisar')
                        ->label('Revisar')
                        ->url($this->urlDeRevision($modelo))
                        ->markAsRead(),
                ])
                ->sendToDatabase($superAdmin);
        }
    }

    private function urlDeRevision(Model $modelo): string
    {
        $recurso = Filament::getModelResource($modelo::class);

        return $recurso
            ? $recurso::getUrl('edit', ['record' => $modelo])
            : url('/admin');
    }
}
