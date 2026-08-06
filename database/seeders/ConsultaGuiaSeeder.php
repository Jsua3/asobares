<?php

namespace Database\Seeders;

use App\Models\ConsultaGuia;
use App\Models\Municipio;
use Illuminate\Database\Seeder;

/**
 * Consultas a la guía repartidas en 18 meses.
 *
 * La forma importa tanto como el volumen: Armenia concentra —es la capital y
 * donde está el grueso del sector—, los municipios turísticos van detrás, y
 * los pequeños dejan una cola fina. Una distribución plana no se parece a
 * nada y delata la semilla en la primera mirada.
 */
class ConsultaGuiaSeeder extends Seeder
{
    /** Peso relativo de cada municipio en las consultas. */
    private const array PESOS = [
        'Armenia' => 45,
        'Calarcá' => 12,
        'Salento' => 11,
        'Montenegro' => 8,
        'La Tebaida' => 7,
        'Quimbaya' => 6,
        'Circasia' => 6,
        'Filandia' => 5,
    ];

    public function run(): void
    {
        if (ConsultaGuia::count() > 0) {
            return;
        }

        $municipios = Municipio::pluck('id', 'nombre');
        $filas = [];

        foreach (self::PESOS as $nombre => $peso) {
            $municipioId = $municipios[$nombre] ?? null;

            if ($municipioId === null) {
                continue;
            }

            foreach (range(0, 17) as $mesesAtras) {
                // El divisor y el suelo van juntos a proposito: con `/10` y sin suelo, un
                // peso de 6 daba round(0,64)=1 y round(1,2)=1, o sea dieciocho meses
                // planos. El suelo de 2 garantiza que hasta el municipio mas pequeno
                // duplique a lo largo de la serie, que es lo que la grafica tiene que
                // poder mostrar.
                $crecimiento = 1 + ((17 - $mesesAtras) / 17);
                $base = max($peso / 4, 2);
                $cuantas = (int) round($base * $crecimiento);

                foreach (range(1, $cuantas) as $i) {
                    $fecha = now()
                        ->subMonths($mesesAtras)
                        ->startOfMonth()
                        ->addDays(random_int(0, 27))
                        ->addHours(random_int(8, 22));

                    $filas[] = [
                        'municipio_id' => $municipioId,
                        'requisito_apertura_id' => null,
                        'created_at' => $fecha,
                        'updated_at' => $fecha,
                    ];
                }
            }
        }

        // Inserción en lote: son cientos de filas y no hace falta un modelo
        // por cada una.
        foreach (array_chunk($filas, 200) as $lote) {
            ConsultaGuia::insert($lote);
        }
    }
}
