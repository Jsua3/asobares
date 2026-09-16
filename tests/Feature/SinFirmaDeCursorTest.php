<?php

namespace Tests\Feature;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;
use Throwable;

/**
 * La firma de Cursor no vuelve a entrar en la historia.
 *
 * El editor Cursor añade `Co-authored-by: Cursor <cursoragent@cursor.com>` a
 * los commits de su agente, y GitHub lista esa dirección como contribuidora
 * del repositorio. Los commits anteriores al corte la llevan y se quedan como
 * están: quitarla exigiría reescribir `main` a la fuerza, redesplegar y romper
 * los hashes que cita la documentación del proyecto. Por eso la historia no se
 * reescribe: la atribución se apaga en Cursor (Settings → Git & Pull Requests
 * → Commit Attribution) y esta guardia vigila que ningún commit posterior la
 * traiga.
 *
 * Solo mira la firma de Cursor. El `Co-Authored-By` de las sesiones de Claude
 * Code es una convención distinta y no entra aquí.
 */
class SinFirmaDeCursorTest extends TestCase
{
    /**
     * El último commit firmado: la cabeza de `cierre/asobares-final` el día del
     * corte. Todos los firmados anteriores son ancestros suyos, así que
     * `CORTE..HEAD` deja fuera la historia vieja en cualquier rama.
     */
    private const CORTE = '6c22d87';

    private const FIRMA = 'cursoragent@cursor.com';

    /**
     * Sin `index.lock` de por medio: `GIT_OPTIONAL_LOCKS=0` es regla de la
     * casa (prompt maestro §4.8) y `log` de todos modos no escribe.
     */
    private function git(string ...$argumentos): ProcessResult
    {
        return Process::path(base_path())
            ->env(['GIT_OPTIONAL_LOCKS' => '0'])
            ->run(['git', ...$argumentos]);
    }

    public function test_ningun_commit_posterior_al_corte_lleva_la_firma_de_cursor(): void
    {
        try {
            $corte = $this->git('rev-parse', '--verify', '--quiet', self::CORTE.'^{commit}');
        } catch (Throwable) {
            $corte = null;
        }

        if ($corte === null || ! $corte->successful()) {
            $this->markTestSkipped('Sin `git`, sin repositorio o sin el commit de corte (clon superficial): no se puede revisar la historia.');
        }

        // Solo los trailers `Co-authored-by`, no el texto del mensaje: un
        // commit que nombra la dirección para explicar esta guardia no firma.
        $historia = $this->git('log', '--format=%h%x1f%(trailers:key=Co-authored-by,valueonly)%x1e', self::CORTE.'..HEAD');

        $this->assertTrue($historia->successful(), 'No se pudo leer la historia desde el corte: '.$historia->errorOutput());

        $firmados = collect(explode("\x1e", $historia->output()))
            ->map(fn (string $registro): array => explode("\x1f", trim($registro), 2))
            ->filter(fn (array $partes): bool => count($partes) === 2 && str_contains(mb_strtolower($partes[1]), self::FIRMA))
            ->map(fn (array $partes): string => $partes[0])
            ->values()
            ->all();

        $this->assertSame(
            [],
            $firmados,
            'Estos commits posteriores a '.self::CORTE.' llevan la firma de Cursor: '.implode(', ', $firmados).'. Apaga «Commit Attribution» en Cursor (Settings → Git & Pull Requests) y quita la línea `Co-authored-by: Cursor` de esos commits antes de subirlos.'
        );
    }
}
