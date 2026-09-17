<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DescargarAccesosProvisionales
{
    public function descargar(?User $direccion): StreamedResponse
    {
        abort_unless($direccion?->esSuperAdmin() && auth()->id() === $direccion->id, 403);

        $filas = DB::transaction(function (): array {
            $filas = [];

            User::query()
                ->with('asociado')
                ->where('contrasena_provisional', true)
                ->whereNotNull('asociado_id')
                ->whereHas('asociado')
                ->whereHas('roles', fn ($query) => $query->where('name', User::ROL_ASOCIADO))
                ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', [User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN]))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->each(function (User $usuario) use (&$filas): void {
                    $clave = self::generarClave();

                    $usuario->forceFill([
                        'password' => Hash::make($clave),
                        'remember_token' => Str::random(60),
                    ])->save();

                    $filas[] = [
                        self::celda($usuario->asociado->nombre),
                        self::celda($usuario->name),
                        self::celda($usuario->email),
                        $clave,
                    ];
                });

            return $filas;
        });

        return response()->streamDownload(function () use ($filas): void {
            $salida = fopen('php://output', 'w');
            fputcsv($salida, ['establecimiento', 'nombre', 'correo', 'contraseña'], escape: '');

            foreach ($filas as $fila) {
                fputcsv($salida, $fila, escape: '');
            }

            fclose($salida);
        }, 'accesos-provisionales.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store, no-cache, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private static function celda(string $dato): string
    {
        return preg_match('/^[=+\-@\t\r]/u', $dato) ? "'{$dato}" : $dato;
    }

    private static function generarClave(): string
    {
        $mayusculas = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $minusculas = 'abcdefghijkmnopqrstuvwxyz';
        $numeros = '23456789';
        $simbolos = '!#$%&*?';
        $todos = $mayusculas.$minusculas.$numeros.$simbolos;

        $caracteres = [
            $mayusculas[random_int(0, strlen($mayusculas) - 1)],
            $minusculas[random_int(0, strlen($minusculas) - 1)],
            $numeros[random_int(0, strlen($numeros) - 1)],
            $simbolos[random_int(0, strlen($simbolos) - 1)],
        ];

        while (count($caracteres) < 12) {
            $caracteres[] = $todos[random_int(0, strlen($todos) - 1)];
        }

        for ($i = count($caracteres) - 1; $i > 1; $i--) {
            $j = random_int(1, $i);
            [$caracteres[$i], $caracteres[$j]] = [$caracteres[$j], $caracteres[$i]];
        }

        return implode('', $caracteres);
    }
}
