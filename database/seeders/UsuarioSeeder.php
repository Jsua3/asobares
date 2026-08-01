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
        $direccion = User::updateOrCreate(
            ['email' => 'direccion@asobaresquindio.test'],
            [
                'name' => 'Natalia Gutiérrez',
                'password' => Hash::make(self::CLAVE_DEMO),
                'email_verified_at' => now(),
            ]
        );
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $oficina = User::updateOrCreate(
            ['email' => 'oficina@asobaresquindio.test'],
            [
                'name' => 'Secretaría del capítulo',
                'password' => Hash::make(self::CLAVE_DEMO),
                'email_verified_at' => now(),
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
