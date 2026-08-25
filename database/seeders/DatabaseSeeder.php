<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Los datos de demostración no entran en producción.
     *
     * De los veinte sembradores sólo `UsuarioSeeder` se negaba por su cuenta.
     * Los demás corrían sin freno con `db:seed --force`: `MensajeSeeder`
     * inserta PQR ficticias con nombre, correo y teléfono —y consume
     * radicados del consecutivo anual de verdad—, `TransaccionSeeder` inserta
     * pagos aprobados que la conciliación y el widget de recaudo suman, y
     * `AsociadoSeeder` publica establecimientos inventados en el directorio.
     *
     * La guardia va aquí y no en cada sembrador porque cubre los veinte de
     * una sola vez y no toca la suite: ninguna prueba invoca `DatabaseSeeder`
     * en producción, y las que siembran llaman a los sembradores sueltos.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('DatabaseSeeder omitido: los datos de demostración no entran en producción.');

            return;
        }

        $this->call([
            // Catálogos y contenido institucional.
            MunicipioSeeder::class,
            CategoriaSeeder::class,
            BeneficioSeeder::class,
            AliadoSeeder::class,
            IniciativaSeeder::class,
            SettingSeeder::class,

            // Contenido del gremio.
            AsociadoSeeder::class,
            CarteraSeeder::class,
            RequisitoAperturaSeeder::class,
            ConsultaGuiaSeeder::class,
            EventoSeeder::class,
            VacanteSeeder::class,
            ArtistaSeeder::class,
            ProveedorSeeder::class,
            NoticiaSeeder::class,
            MensajeSeeder::class,
            TransaccionSeeder::class,

            // Roles y usuarios al final: dependen de los asociados.
            RolYPermisoSeeder::class,
            UsuarioSeeder::class,
        ]);

        $this->imprimirCredenciales();
    }

    private function imprimirCredenciales(): void
    {
        $clave = UsuarioSeeder::CLAVE_DEMO;

        $this->command?->newLine();
        $this->command?->info('=== ASOBARES Quindío — credenciales del demo ===');
        $this->command?->table(
            ['Rol', 'Correo', 'Contraseña', 'Entra por'],
            [
                ['super_admin', 'direccion@asobaresquindio.test', $clave, '/admin'],
                ['subadmin', 'oficina@asobaresquindio.test', $clave, '/admin'],
                ['asociado', 'asociado@asobaresquindio.test', $clave, '/mi-cuenta'],
            ]
        );
        $this->command?->line('  El usuario asociado está vinculado a un establecimiento con 3 meses de mora.');
        $this->command?->newLine();
    }
}
