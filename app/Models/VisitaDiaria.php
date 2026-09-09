<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Contador anónimo de visitas al sitio público, por ruta y día.
 *
 * ⚠️ No guarda IP, agente de usuario ni sesión, a propósito, igual que
 * `ConsultaGuia`: así es un agregado y no un dato personal.
 * `AnaliticaDelSitioTest::test_la_tabla_no_guarda_ningun_dato_personal` lo
 * vigila leyendo las columnas de verdad, no el modelo.
 *
 * No hay una fila por visita: hay una fila por ruta y día que se incrementa.
 * Por eso esta tabla no lleva purga --como `consultas_guia`, y por el mismo
 * motivo-- y por eso tampoco puede reconstruirse de aquí el paso de nadie por
 * el sitio: no queda la hora.
 */
class VisitaDiaria extends Model
{
    protected $table = 'visitas_diarias';

    protected $fillable = ['ruta', 'dia', 'total', 'entradas'];

    /**
     * `dia` NO se castea a fecha a propósito. Con el casteo, Eloquent la
     * serializa con el formato de fecha y hora del modelo y SQLite --que no
     * impone tipos-- guarda «2026-09-08 00:00:00» donde PostgreSQL guarda
     * «2026-09-08»: la misma fila con dos formas según el motor, y las
     * comparaciones de la ventana de treinta días dependiendo de eso.
     *
     * Aquí `dia` no es un instante sino la CLAVE del cubo, así que se trata
     * como lo que es: una cadena «Y-m-d» que escribe y lee un solo sitio.
     */
    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'entradas' => 'integer',
        ];
    }

    /**
     * Punto único de escritura. Suma uno al contador del día, y lo crea si no
     * existe.
     *
     * El orden --incrementar primero, insertar después-- no es casual: el caso
     * frecuente es que la fila ya exista, y así se resuelve en una sola
     * consulta. El `catch` cubre la carrera del primer visitante del día: dos
     * peticiones que no encuentran fila e intentan crearla a la vez. La segunda
     * choca contra el índice único y suma, que es exactamente lo que quería
     * hacer.
     *
     * `$esEntrada` distingue la LLEGADA al sitio del resto de la navegación
     * (Acta 08, A-03). Las dos cifras se escriben juntas y en la misma fila
     * porque son el mismo cubo visto con dos preguntas: cuánto se mira, y cuánta
     * gente entra. Quién decide si es entrada es el middleware, que es el único
     * que ve la petición.
     */
    public static function registrar(string $ruta, bool $esEntrada = false): void
    {
        $dia = now()->toDateString();
        $sumas = $esEntrada ? ['total' => 1, 'entradas' => 1] : ['total' => 1];

        if (static::query()->where('ruta', $ruta)->where('dia', $dia)->incrementEach($sumas) > 0) {
            return;
        }

        try {
            static::query()->create([
                'ruta' => $ruta,
                'dia' => $dia,
                'total' => 1,
                'entradas' => $esEntrada ? 1 : 0,
            ]);
        } catch (UniqueConstraintViolationException) {
            static::query()->where('ruta', $ruta)->where('dia', $dia)->incrementEach($sumas);
        }
    }
}
