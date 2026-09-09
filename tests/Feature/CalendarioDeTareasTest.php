<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Que las tres purgas de datos personales sigan teniendo quien las dispare.
 *
 * Las purgas estaban escritas, configuradas y probadas --`DepuracionDeBolsasTest`,
 * `DepuracionDeMensajesTest` y `DepuracionDeInscripcionesTest` verifican que
 * BORRAN bien-- pero ninguna prueba miraba si alguien las LLAMA. Borrar las tres
 * líneas de `routes/console.php` dejaba la suite entera en verde y el sitio
 * incumpliendo en silencio la Ley 1581 y lo que promete `/politica-de-datos`:
 * «Pasado cada plazo, el borrado es automático».
 *
 * Esta clase es esa guarda. Comprobada en rojo el 9 de septiembre de 2026
 * comentando las tres tareas del calendario.
 *
 * ⚠️ Lo que esta prueba NO puede comprobar, y hay que verificar a mano: que el
 * entorno de producción ejecute `schedule:run` cada minuto. En Laravel Cloud eso
 * es un recurso «Scheduler» que se añade al entorno y no viene de fábrica. El
 * runbook §5.1 lo explica.
 */
class CalendarioDeTareasTest extends TestCase
{
    /**
     * Las tres purgas, con el plazo que cada una respeta.
     *
     * @return array<string, array{0: string}>
     */
    public static function purgas(): array
    {
        return [
            'bolsa de empleo (postulaciones y banco de talento)' => ['bolsas:depurar'],
            'mensajes de contacto y PQR' => ['mensajes:depurar'],
            'inscripciones a eventos' => ['inscripciones:depurar'],
        ];
    }

    #[DataProvider('purgas')]
    public function test_la_purga_de_datos_personales_esta_programada(string $comando): void
    {
        $this->assertTrue(
            $this->tareas()->contains(fn (Event $tarea): bool => str_contains((string) $tarea->command, $comando)),
            "La purga «{$comando}» no está en el calendario de tareas. Sin ella los datos personales "
            .'no se borran nunca, y /politica-de-datos promete que sí.'
        );
    }

    #[DataProvider('purgas')]
    public function test_la_purga_corre_todos_los_dias(string $comando): void
    {
        $tarea = $this->tareas()->first(fn (Event $t): bool => str_contains((string) $t->command, $comando));

        $this->assertNotNull($tarea, "La purga «{$comando}» no está programada.");

        // `m h * * *` -- cualquier día, cualquier mes, cualquier día de la
        // semana. Se afirma sobre los tres últimos campos y no sobre la
        // expresión entera para que cambiar la HORA de la purga no rompa la
        // prueba: la hora es una preferencia, la frecuencia es la promesa.
        [, , $diaDelMes, $mes, $diaDeLaSemana] = explode(' ', $tarea->expression);

        $this->assertSame(
            ['*', '*', '*'],
            [$diaDelMes, $mes, $diaDeLaSemana],
            "La purga «{$comando}» ya no corre todos los días: «{$tarea->expression}». "
            .'Los plazos de retención se cuentan en meses y se comprueban a diario.'
        );
    }

    /** @return Collection<int, Event> */
    private function tareas(): Collection
    {
        return collect($this->app->make(Schedule::class)->events());
    }
}
