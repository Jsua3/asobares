<?php

namespace App\Support;

use App\Filament\Resources\Eventos\EventoResource;
use App\Models\Evento;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

final class AvisoDeEventoComunitario
{
    public static function enviar(Evento $evento): void
    {
        $panel = Filament::getPanel('admin');
        $url = EventoResource::getUrl('edit', ['record' => $evento], panel: 'admin');

        User::permission('eliminar_evento')->get()
            ->filter(fn (User $usuario): bool => $usuario->canAccessPanel($panel) && $usuario->can('delete', $evento))
            ->each(function (User $usuario) use ($evento, $url): void {
                $usuario->notifyNow(
                    Notification::make()
                        ->title('Nuevo evento de la comunidad')
                        ->body(e($evento->titulo).' fue publicado para el '.$evento->fecha_inicio->format('d/m/Y').'.')
                        ->actions([
                            Action::make('revisar')
                                ->label('Revisar evento')
                                ->button()
                                ->url($url)
                                ->markAsRead(),
                        ])
                        ->toDatabase(),
                );
            });
    }
}
