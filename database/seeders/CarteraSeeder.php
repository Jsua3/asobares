<?php

namespace Database\Seeders;

use App\Models\Asociado;
use App\Models\Cartera;
use Illuminate\Database\Seeder;

/**
 * Estado de cuenta de los 24 afiliados: 16 al día y 8 en mora.
 * En producción esta tabla se alimenta del CSV de la contadora.
 */
class CarteraSeeder extends Seeder
{
    /** Mensualidad de referencia del gremio. */
    public const int MENSUALIDAD = 50000;

    /** El asociado del guion del demo: entra, ve 3 meses de mora y paga. */
    public const string ASOCIADO_DEMO = 'bruma-gastrobar';

    /** @var array<string, int> slug => meses de mora */
    private const array EN_MORA = [
        'bruma-gastrobar' => 3,
        'bar-la-estacion-1927' => 1,
        'el-guadual' => 2,
        'la-herradura' => 6,
        'cafe-circasia' => 4,
        'bar-puerto-espejo' => 2,
        'cafe-del-parque' => 5,
        'bar-luces-de-quimbaya' => 1,
    ];

    public function run(): void
    {
        foreach (Asociado::all() as $asociado) {
            $mesesMora = self::EN_MORA[$asociado->slug] ?? 0;

            Cartera::updateOrCreate(
                ['asociado_id' => $asociado->id],
                [
                    'saldo_pendiente' => $mesesMora * self::MENSUALIDAD,
                    'meses_mora' => $mesesMora,
                    'ultimo_pago_at' => $mesesMora > 0
                        ? now()->subMonths($mesesMora)->startOfMonth()->toDateString()
                        : now()->startOfMonth()->toDateString(),
                    'actualizado_at' => now(),
                ]
            );
        }
    }
}
