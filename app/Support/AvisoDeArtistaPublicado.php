<?php

namespace App\Support;

use App\Filament\Resources\Artistas\ArtistaResource;
use App\Models\Artista;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

/**
 * Avisa en la campana del panel que un artista se publicó desde el sitio.
 *
 * La bolsa de artistas publica al instante y se modera después: el aviso le
 * llega a quien la modera —`publicar_artista`, secretaría y dirección— con
 * acceso directo a la ficha para editarla, despublicarla o borrarla. Solo
 * base de datos: un correo saldría dentro de la misma petición del artista.
 */
final class AvisoDeArtistaPublicado
{
    public static function enviar(Artista $artista): void
    {
        $panel = Filament::getPanel('admin');
        $url = ArtistaResource::getUrl('edit', ['record' => $artista], panel: 'admin');

        User::permission('publicar_artista')->get()
            ->filter(fn (User $usuario): bool => $usuario->canAccessPanel($panel) && $usuario->can('update', $artista))
            ->each(function (User $usuario) use ($artista, $url): void {
                $usuario->notifyNow(
                    Notification::make()
                        ->title('Nuevo artista publicado: '.e($artista->nombre))
                        ->body('Ya aparece en la bolsa de artistas. Revísalo y, si hace falta, edítalo o despublícalo.')
                        ->actions([
                            Action::make('revisar')
                                ->label('Revisar artista')
                                ->button()
                                ->url($url)
                                ->markAsRead(),
                        ])
                        ->toDatabase(),
                );
            });
    }
}
