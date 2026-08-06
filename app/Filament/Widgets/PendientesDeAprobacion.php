<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Panel\ColaDePendientes;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Banda 1 del tablero: lo que hay que hacer hoy.
 *
 * Va arriba de todo y antes de cualquier cifra. Lo pendiente de aprobación
 * vivía solo en la campanita de notificaciones, y es el trabajo diario de la
 * secretaría: si no está a la vista, no existe.
 */
class PendientesDeAprobacion extends Widget
{
    protected string $view = 'filament.widgets.pendientes-de-aprobacion';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{
     *     etiqueta: string,
     *     conteo: int,
     *     url: string,
     *     antiguedad: ?string,
     *     urgente: bool
     * }>
     */
    public function filas(): array
    {
        $usuario = Auth::user();

        return $usuario instanceof User
            ? app(ColaDePendientes::class)->para($usuario)
            : [];
    }

    /**
     * Una cola vacía no se dibuja: un recuadro que dice «no hay nada» ocupa
     * el lugar más valioso del tablero para no decir nada.
     */
    public static function canView(): bool
    {
        $usuario = Auth::user();

        return $usuario instanceof User
            && app(ColaDePendientes::class)->total($usuario) > 0;
    }
}
