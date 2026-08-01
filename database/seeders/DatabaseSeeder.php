<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
