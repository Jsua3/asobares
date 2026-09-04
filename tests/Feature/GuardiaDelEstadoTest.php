<?php

namespace Tests\Feature;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;
use Throwable;

/**
 * La guardia del expediente partido en cuatro (bitácora §32).
 *
 * Durante agosto el prompt maestro fue un solo archivo de 1.800 líneas donde
 * las reglas, el estado semanal, la referencia del producto y la historia se
 * tachaban unas a otras: la auditoría del §30.5 le contó 106 contradicciones.
 * El 1 de septiembre se repartió en cuatro archivos con una regla de
 * mantenimiento distinta para cada uno. Dos de esas reglas las puede vigilar
 * una máquina, y son las que están aquí:
 *
 * 1. `estado.md` cita en su encabezado el commit sobre el que se midió. Si ese
 *    commit no existe, la foto no es de nadie: alguien copió una cabecera
 *    vieja o escribió el hash de memoria. Se comprueba contra el repositorio
 *    de verdad, no contra una lista.
 * 2. El prompt maestro no lleva cifras ni fechas: lo que cambia cada semana va
 *    al estado. La primera cifra de la suite que alguien pegue ahí es el
 *    primer paso de vuelta al archivo de 1.800 líneas.
 */
class GuardiaDelEstadoTest extends TestCase
{
    private const ESTADO = 'material/estado.md';

    private const PROMPT_MAESTRO = 'material/prompt-maestro-laravel-filament.md';

    /**
     * Sin `index.lock` de por medio: `GIT_OPTIONAL_LOCKS=0` es regla de la
     * casa (prompt maestro §4.8) y `cat-file` de todos modos no escribe.
     */
    private function git(string ...$argumentos): ProcessResult
    {
        return Process::path(base_path())
            ->env(['GIT_OPTIONAL_LOCKS' => '0'])
            ->run(['git', ...$argumentos]);
    }

    public function test_el_encabezado_del_estado_cita_un_commit_que_existe(): void
    {
        $estado = File::get(base_path(self::ESTADO));

        // La fila `| `main` | `<hash>` · … |` de la tabla de medición. El
        // commit es la primera referencia entre acentos graves de esa fila.
        $patron = '/^\|\s*`main`\s*\|\s*`([0-9a-f]{7,40})`/mi';

        $this->assertMatchesRegularExpression(
            $patron,
            $estado,
            'estado.md perdió la fila `main` de su encabezado, o el commit ya no va entre acentos graves: la guardia no tiene qué comprobar.'
        );

        preg_match($patron, $estado, $coincidencia);
        $commit = $coincidencia[1];

        try {
            $repositorio = $this->git('rev-parse', '--git-dir');
        } catch (Throwable) {
            $repositorio = null;
        }

        if ($repositorio === null || ! $repositorio->successful()) {
            $this->markTestSkipped('Sin `git` o sin repositorio en esta máquina: no se puede comprobar el commit del encabezado.');
        }

        $tipo = $this->git('cat-file', '-t', $commit);

        $this->assertTrue(
            $tipo->successful() && trim($tipo->output()) === 'commit',
            "El encabezado de estado.md cita `{$commit}` y ese commit no existe en este repositorio: la foto no es de nadie. Reescríbelo con `git rev-parse --short HEAD` al cerrar la sesión."
        );
    }

    /**
     * Las cifras del árbol y de la suite caducan con cada commit; por eso viven
     * en la tabla del §5 del estado, cada una con el comando que la produjo.
     */
    public function test_el_prompt_maestro_no_lleva_cifras_que_caducan(): void
    {
        $prompt = File::get(base_path(self::PROMPT_MAESTRO));

        preg_match_all(
            '/\b\d[\d.,]*\s*(?:casos?|aserciones|pruebas?|tests?|migraciones|modelos|sembradores|vistas|recursos|policies|comandos|enums|rutas|confirmaciones|commits?)\b/iu',
            $prompt,
            $cifras
        );

        $this->assertSame(
            [],
            $cifras[0],
            'El prompt maestro lleva cifras que caducan («'.implode('», «', $cifras[0]).'»). Van en estado.md §5, con el comando que las mide.'
        );
    }

    /**
     * Las fechas de entrega cambian de semana en semana y viven en el §1 del
     * estado. La única parte del prompt maestro que puede fecharse es su
     * propio registro de cambios, que es la última sección.
     */
    public function test_el_prompt_maestro_no_lleva_fechas_fuera_de_su_registro_de_cambios(): void
    {
        $prompt = File::get(base_path(self::PROMPT_MAESTRO));
        $registro = strpos($prompt, '## 6. Registro de cambios');

        $this->assertNotFalse($registro, 'El prompt maestro perdió su §6 «Registro de cambios de este archivo».');

        preg_match_all(
            '/\b\d{1,2}\s+(?:de\s+)?(?:ene(?:ro)?|feb(?:rero)?|mar(?:zo)?|abr(?:il)?|may(?:o)?|jun(?:io)?|jul(?:io)?|ago(?:sto)?|sep(?:tiembre)?|oct(?:ubre)?|nov(?:iembre)?|dic(?:iembre)?)\b/iu',
            substr($prompt, 0, $registro),
            $fechas
        );

        $this->assertSame(
            [],
            $fechas[0],
            'El prompt maestro lleva fechas fuera de su registro de cambios («'.implode('», «', $fechas[0]).'»). Las entregas van en estado.md §1.'
        );
    }
}
