<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tres roles con una frontera clara:
 * - super_admin (la dirección) es el único que publica.
 * - subadmin (secretaría y pasantes) redacta, pero su contenido queda pendiente.
 * - asociado no entra al panel; su sesión sirve para /mi-cuenta.
 */
class RolYPermisoSeeder extends Seeder
{
    /** Contenido sujeto al flujo de aprobación: tiene permiso `publicar_`. */
    public const array PUBLICABLES = [
        'asociado', 'evento', 'noticia', 'requisito', 'iniciativa',
        'vacante', 'artista', 'proveedor', 'aliado',
    ];

    /** Catálogos sin flujo editorial: quedan vivos al guardarlos. */
    public const array CATALOGOS = ['beneficio', 'municipio', 'categoria'];

    /** Bandejas que la secretaría sí gestiona por completo. */
    public const array BANDEJAS = ['mensaje', 'aspirante', 'inscripcion'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [];

        foreach (self::PUBLICABLES as $recurso) {
            foreach (['ver', 'crear', 'editar', 'eliminar', 'publicar'] as $accion) {
                $permisos[] = "{$accion}_{$recurso}";
            }
        }

        foreach (self::CATALOGOS as $recurso) {
            foreach (['ver', 'crear', 'editar', 'eliminar'] as $accion) {
                $permisos[] = "{$accion}_{$recurso}";
            }
        }

        foreach (self::BANDEJAS as $bandeja) {
            foreach (['ver', 'editar', 'eliminar'] as $accion) {
                $permisos[] = "{$accion}_{$bandeja}";
            }
        }

        // Permisos exclusivos de la dirección.
        $permisos = array_merge($permisos, [
            'ver_usuario', 'crear_usuario', 'editar_usuario', 'eliminar_usuario',
            'ver_ajustes', 'editar_ajustes',
            'ver_cartera', 'importar_cartera',
            'ver_transaccion',
            'ver_bitacora',
        ]);

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $superAdmin = Role::findOrCreate(User::ROL_SUPER_ADMIN, 'web');
        $superAdmin->syncPermissions(Permission::all());

        // La secretaría hace todo menos publicar, y no toca usuarios,
        // ajustes, cartera, transacciones ni bitácora.
        $permisosSubadmin = collect($permisos)
            ->reject(fn (string $permiso): bool => str_starts_with($permiso, 'publicar_')
                || str_ends_with($permiso, '_usuario')
                || str_ends_with($permiso, '_ajustes')
                || str_ends_with($permiso, '_cartera')
                || str_ends_with($permiso, '_transaccion')
                || str_ends_with($permiso, '_bitacora')
                || str_starts_with($permiso, 'eliminar_'))
            ->values()
            ->all();

        Role::findOrCreate(User::ROL_SUBADMIN, 'web')->syncPermissions($permisosSubadmin);

        // El asociado no recibe permisos de panel: su acceso es /mi-cuenta.
        Role::findOrCreate(User::ROL_ASOCIADO, 'web')->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
