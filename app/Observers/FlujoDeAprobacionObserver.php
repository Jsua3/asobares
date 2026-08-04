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

        $this->avisarAQuienAprueba($modelo);
    }

    /**
     * Notificación de base de datos para quien pueda aprobar ESTE modelo.
     *
     * Se pregunta por la policy y no por un rol fijo: así las bolsas avisan a
     * la secretaría —que sí las aprueba— y el resto del contenido sigue
     * avisando solo a la dirección, sin ninguna lógica especial por recurso.
     */
    private function avisarAQuienAprueba(Model $modelo): void
    {
        $autor = Auth::user();
        $nombreDelAutor = $autor?->name ?? 'El sistema';
        $etiqueta = $modelo->nombre ?? $modelo->titulo ?? $modelo->cargo ?? $modelo->entidad ?? "#{$modelo->getKey()}";
        $tipo = class_basename($modelo);

        $revisores = User::query()
            ->whereHas('roles')
            ->get()
            ->filter(fn (User $revisor): bool => $revisor->can('publicar', $modelo))
            // Quien lo envió no se avisa a sí mismo.
            ->reject(fn (User $revisor): bool => $revisor->is($autor));

        foreach ($revisores as $revisor) {
            Notification::make()
                ->title('Contenido pendiente de aprobación')
                ->body("{$nombreDelAutor} envió «{$etiqueta}» ({$tipo}) para revisión.")
                ->icon('heroicon-o-clock')
                ->iconColor('warning')
                ->actions([
                    Action::make('revisar')
                        ->label('Revisar')
                        ->url($this->urlDeRevision($modelo))
                        ->markAsRead(),
                ])
                ->sendToDatabase($revisor);
        }
    }

    private function urlDeRevision(Model $modelo): string
    {
        $recurso = Filament::getModelResource($modelo::class);

        if ($recurso === null) {
            return url('/admin');
        }

        // Las vacantes ya no se editan desde el panel: se moderan desde el
        // listado, así que ahí es donde tiene que aterrizar el revisor.
        return $recurso::hasPage('edit')
            ? $recurso::getUrl('edit', ['record' => $modelo])
            : $recurso::getUrl('index');
    }
}
