<?php

namespace Database\Seeders;

use App\Models\Asociado;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public const string CLAVE_DEMO = 'Asobares2026*';

    public function run(): void
    {
        // Son cuentas de demostración con una contraseña conocida y publicada
        // en el README. `firstOrCreate` en vez de `updateOrCreate` para no
        // reimponerla si alguien ya la cambió, y ni siquiera eso en producción.
        if (app()->isProduction()) {
            $this->command?->warn('UsuarioSeeder omitido: no se crean cuentas de demostración en producción.');

            return;
        }

        // El panel exige segundo factor: sin darlo de alta, estas cuentas se
        // quedarían en la pantalla de alta y el demo no pasaría de ahí. Se
        // deja el de correo, que no obliga a instalar nada. En producción este
        // seeder ni siquiera corre, así que las cuentas reales sí pasan por el
        // alta obligatoria.
        $direccion = User::firstOrCreate(
            ['email' => 'direccion@asobaresquindio.test'],
            [
                'name' => 'Natalia Gutiérrez',
                'password' => Hash::make(self::CLAVE_DEMO),
                'email_verified_at' => now(),
                'has_email_authentication' => true,
            ]
        );
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $oficina = User::updateOrCreate(
            ['email' => 'oficina@asobaresquindio.test'],
            [
                'name' => 'Secretaría del capítulo',
                'password' => Hash::make(self::CLAVE_DEMO),
                'email_verified_at' => now(),
                'has_email_authentication' => true,
            ]
        );
        $oficina->syncRoles([User::ROL_SUBADMIN]);

        // Vinculado a un asociado con 3 meses de mora: es el guion del demo
        // de cartera (entra, ve la deuda, paga, queda al día).
        $asociadoDemo = Asociado::where('slug', CarteraSeeder::ASOCIADO_DEMO)->firstOrFail();

        $duenio = User::updateOrCreate(
            ['email' => 'asociado@asobaresquindio.test'],
            [
                'name' => $asociadoDemo->representante,
                'password' => Hash::make(self::CLAVE_DEMO),
                'email_verified_at' => now(),
                'asociado_id' => $asociadoDemo->id,
            ]
        );
        $duenio->syncRoles([User::ROL_ASOCIADO]);
    }
}
