<?php

namespace Tests\Feature;

use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Tests\TestCase;

/**
 * SEG-04: el panel edita el cuerpo de la noticia en un Textarea de texto
 * plano, y la ficha lo pintaba como HTML. Los párrafos que la oficina separa
 * con una línea en blanco salían como un solo bloque.
 */
class ParrafosDelBoletinTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function saltosDeLinea(): array
    {
        return [
            'LF' => ["\n"],
            'CRLF, como envía un Textarea' => ["\r\n"],
        ];
    }

    #[DataProvider('saltosDeLinea')]
    public function test_el_texto_plano_escrito_en_parrafos_se_publica_en_parrafos(string $salto): void
    {
        $noticia = Noticia::factory()->visible()->create([
            'contenido' => "Primer párrafo del editor.{$salto}{$salto}Segundo párrafo del editor.",
        ]);

        $cuerpo = $this->cuerpoDeLaFicha($noticia);

        $this->assertSame(2, substr_count($cuerpo, '<p>'), "Cuerpo pintado: {$cuerpo}");
        $this->assertStringContainsString('<p>Primer párrafo del editor.</p>', $cuerpo);
        $this->assertStringContainsString('<p>Segundo párrafo del editor.</p>', $cuerpo);
    }

    public function test_un_salto_simple_se_conserva_y_el_texto_plano_se_escapa(): void
    {
        $noticia = Noticia::factory()->visible()->create([
            'contenido' => "Inscripciones: <inscripciones@gremio.test>\r\nCupos & horarios en la sede.",
        ]);

        $cuerpo = $this->cuerpoDeLaFicha($noticia);

        $this->assertSame(1, substr_count($cuerpo, '<p>'), "Cuerpo pintado: {$cuerpo}");
        // El saneado codifica la arroba como `&#64;`; los ángulos tienen que
        // llegar escapados, no convertidos en una etiqueta que se descarta.
        $this->assertMatchesRegularExpression(
            '/Inscripciones: &lt;inscripciones(?:@|&#64;)gremio\.test&gt;<br\s*\/?>Cupos &amp; horarios/',
            $cuerpo,
        );
    }

    public function test_el_contenido_con_html_se_pinta_igual_que_antes_y_saneado(): void
    {
        $contenido = "<p>Uno del sembrador.</p>\n\n<p>Dos con <strong>énfasis</strong>.</p>"
            .'<script>alert(1)</script><a href="javascript:alert(2)">enlace</a>';

        $noticia = Noticia::factory()->visible()->create(['contenido' => $contenido]);

        $cuerpo = $this->cuerpoDeLaFicha($noticia);

        $saneadoComoAntes = (new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->forceHttpsUrls()
        ))->sanitize($contenido);

        $this->assertSame(trim($saneadoComoAntes), $cuerpo);
        $this->assertSame(2, substr_count($cuerpo, '<p>'));
        $this->assertStringNotContainsString('&lt;p&gt;', $cuerpo);
        $this->assertStringNotContainsString('<script', $cuerpo);
        $this->assertStringNotContainsString('javascript:', $cuerpo);
    }

    /**
     * Un marcador entre ángulos es texto, no una etiqueta. Tomado por HTML, el
     * saneado se comía todo lo que venía detrás, párrafos incluidos.
     */
    public function test_un_marcador_entre_angulos_no_se_come_el_resto_del_texto(): void
    {
        $noticia = Noticia::factory()->visible()->create([
            'contenido' => "Horario <de 8 a 12> en la sede.\n\nEscribe a <nombre del contacto> o <a lo sumo cinco> personas.",
        ]);

        $cuerpo = $this->cuerpoDeLaFicha($noticia);

        $this->assertSame(2, substr_count($cuerpo, '<p>'), "Cuerpo pintado: {$cuerpo}");
        $this->assertStringContainsString('<p>Horario &lt;de 8 a 12&gt; en la sede.</p>', $cuerpo);
        $this->assertStringContainsString('Escribe a &lt;nombre del contacto&gt; o &lt;a lo sumo cinco&gt; personas.', $cuerpo);
    }

    /** Un byte UTF-8 roto no puede dejar la noticia vacía. */
    public function test_un_byte_utf8_roto_no_deja_la_noticia_vacia(): void
    {
        $noticia = new Noticia(['contenido' => "Primer párrafo \xC3\x28 roto.\n\nSegundo párrafo."]);

        $cuerpo = $noticia->contenidoSaneado();

        $this->assertSame(2, substr_count($cuerpo, '<p>'), "Cuerpo pintado: {$cuerpo}");
        $this->assertStringContainsString('Segundo párrafo.', $cuerpo);
    }

    /** Lo que la ficha pinta dentro del bloque `prose-asobares`. */
    private function cuerpoDeLaFicha(Noticia $noticia): string
    {
        $html = $this->get(route('boletin.show', $noticia))->assertSuccessful()->getContent();

        $clase = strpos($html, 'prose-asobares');
        $this->assertNotFalse($clase, 'La ficha tiene que pintar el cuerpo de la noticia.');

        $apertura = strpos($html, '>', $clase) + 1;
        $cierre = strpos($html, '</div>', $apertura);

        return trim(substr($html, $apertura, $cierre - $apertura));
    }
}
