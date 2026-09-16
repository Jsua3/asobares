<?php

namespace Tests\Feature;

use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Tests\TestCase;

/**
 * El panel edita el cuerpo de la noticia en un Textarea de texto plano, y la
 * ficha no puede pintarlo como HTML a secas: los párrafos que la oficina
 * separa con una línea en blanco saldrían como un solo bloque.
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

        [$xpath, $bloque] = $this->bloqueDelCuerpo($noticia);

        // Lo que se vigila es que la línea en blanco parta el texto en dos
        // párrafos y que cada frase se quede en el suyo. Se cuenta sobre el
        // árbol: cuántos `<p>` cuelgan del bloque no cambia porque la etiqueta
        // gane un atributo, y contar la cadena `<p>` sí se iría a cero.
        $this->assertSame(2, $xpath->query('.//p', $bloque)->length, $this->pintado($bloque));
        $this->assertSame(
            'Primer párrafo del editor.',
            $xpath->evaluate('normalize-space((.//p)[1])', $bloque),
            $this->pintado($bloque),
        );
        $this->assertSame(
            'Segundo párrafo del editor.',
            $xpath->evaluate('normalize-space((.//p)[2])', $bloque),
            $this->pintado($bloque),
        );
    }

    public function test_un_salto_simple_se_conserva_y_el_texto_plano_se_escapa(): void
    {
        $noticia = Noticia::factory()->visible()->create([
            'contenido' => "Inscripciones: <inscripciones@gremio.test>\r\nCupos & horarios en la sede.",
        ]);

        [$xpath, $bloque] = $this->bloqueDelCuerpo($noticia);

        // El salto simple no abre párrafo: se conserva como un `<br>` dentro
        // del mismo. Se mide por el árbol porque cómo escribe el saneado esa
        // etiqueta —y si deja o no un espacio a los lados— es cosa suya.
        $this->assertSame(1, $xpath->query('.//p', $bloque)->length, $this->pintado($bloque));
        $this->assertSame(1, $xpath->query('.//p/br', $bloque)->length, $this->pintado($bloque));

        // El árbol devuelve el texto ya descodificado, así que la afirmación no
        // depende de con qué entidad se escriba la arroba ni el ampersand. Si
        // los ángulos llegaran sin escapar, el marcador sería una etiqueta y el
        // saneado se habría comido todo lo que viene detrás.
        $this->assertSame(
            'Inscripciones: <inscripciones@gremio.test>',
            $xpath->evaluate('normalize-space((.//p/br)[1]/preceding-sibling::text()[1])', $bloque),
            $this->pintado($bloque),
        );
        $this->assertSame(
            'Cupos & horarios en la sede.',
            $xpath->evaluate('normalize-space((.//p/br)[1]/following-sibling::text()[1])', $bloque),
            $this->pintado($bloque),
        );
    }

    public function test_el_contenido_con_html_se_pinta_igual_que_antes_y_saneado(): void
    {
        $contenido = "<p>Uno del sembrador.</p>\n\n<p>Dos con <strong>énfasis</strong>.</p>"
            .'<script>alert(1)</script><a href="javascript:alert(2)">enlace</a>';

        $noticia = Noticia::factory()->visible()->create(['contenido' => $contenido]);

        $cuerpo = $this->cuerpoServidoDeLaFicha($noticia);

        $saneadoComoAntes = (new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->forceHttpsUrls()
        ))->sanitize($contenido);

        // Esta prueba compara bytes a propósito: afirma que el contenido que ya
        // trae etiquetas sale del saneado sin que la conversión a párrafos lo
        // toque. El esperado lo calcula la misma librería, así que el árbol no
        // añadiría nada y sí escondería una diferencia de serialización.
        $this->assertSame(trim($saneadoComoAntes), $cuerpo);
        $this->assertSame(2, substr_count($cuerpo, '<p>'));
        $this->assertStringNotContainsString('&lt;p&gt;', $cuerpo);
        // Las dos negativas se miden sobre los bytes servidos: lo que se vigila
        // es que el navegador no reciba el script ni el esquema, y un árbol ya
        // parseado los habría normalizado.
        $this->assertStringNotContainsString('<script', $cuerpo);
        $this->assertStringNotContainsString('javascript:', $cuerpo);
    }

    /**
     * Un marcador entre ángulos es texto, no una etiqueta. Tomado por HTML, el
     * saneado se comería todo lo que viene detrás, párrafos incluidos.
     */
    public function test_un_marcador_entre_angulos_no_se_come_el_resto_del_texto(): void
    {
        $noticia = Noticia::factory()->visible()->create([
            'contenido' => "Horario <de 8 a 12> en la sede.\n\nEscribe a <nombre del contacto> o <a lo sumo cinco> personas.",
        ]);

        [$xpath, $bloque] = $this->bloqueDelCuerpo($noticia);

        // El texto de cada párrafo se pide al árbol: así la afirmación es que
        // los ángulos llegaron como texto —el nodo los devuelve descodificados—
        // y no que estén escritos con una entidad concreta.
        $this->assertSame(2, $xpath->query('.//p', $bloque)->length, $this->pintado($bloque));
        $this->assertSame(
            'Horario <de 8 a 12> en la sede.',
            $xpath->evaluate('normalize-space((.//p)[1])', $bloque),
            $this->pintado($bloque),
        );
        $this->assertSame(
            'Escribe a <nombre del contacto> o <a lo sumo cinco> personas.',
            $xpath->evaluate('normalize-space((.//p)[2])', $bloque),
            $this->pintado($bloque),
        );
    }

    /** Un byte UTF-8 roto no puede dejar la noticia vacía. */
    public function test_un_byte_utf8_roto_no_deja_la_noticia_vacia(): void
    {
        $noticia = new Noticia(['contenido' => "Primer párrafo \xC3\x28 roto.\n\nSegundo párrafo."]);

        $cuerpo = $noticia->contenidoSaneado();

        // Los dos párrafos se cuentan sobre el árbol por lo mismo que en la
        // ficha: el número de `<p>` es estructura, no texto.
        $this->assertSame(2, $this->xpathDe($cuerpo)->query('//p')->length, "Cuerpo pintado: {$cuerpo}");
        $this->assertStringContainsString('Segundo párrafo.', $cuerpo);
    }

    /**
     * El bloque `prose-asobares` de la ficha, con el consultor del árbol.
     *
     * Se localiza por XPath y no buscando la clase dentro del HTML servido:
     * lo que las pruebas vigilan es qué cuelga de ese bloque, y eso no cambia
     * porque el `<div>` gane un atributo nuevo delante de `class`.
     *
     * @return array{\DOMXPath, \DOMElement}
     */
    private function bloqueDelCuerpo(Noticia $noticia): array
    {
        $html = $this->get(route('boletin.show', $noticia))->assertSuccessful()->getContent();

        $xpath = $this->xpathDe($html);
        $bloque = $xpath
            ->query('//div[contains(concat(" ", normalize-space(@class), " "), " prose-asobares ")]')
            ->item(0);

        $this->assertInstanceOf(
            \DOMElement::class,
            $bloque,
            'La ficha tiene que pintar el cuerpo de la noticia dentro del bloque `prose-asobares`.',
        );

        return [$xpath, $bloque];
    }

    /**
     * Los bytes que la ficha pinta dentro del bloque `prose-asobares`, para la
     * única prueba que compara la serialización tal cual.
     */
    private function cuerpoServidoDeLaFicha(Noticia $noticia): string
    {
        $html = $this->get(route('boletin.show', $noticia))->assertSuccessful()->getContent();

        $clase = strpos($html, 'prose-asobares');
        $this->assertNotFalse($clase, 'La ficha tiene que pintar el cuerpo de la noticia.');

        $apertura = strpos($html, '>', $clase) + 1;
        $cierre = strpos($html, '</div>', $apertura);

        return trim(substr($html, $apertura, $cierre - $apertura));
    }

    /** El cuerpo tal y como quedó pintado, para el mensaje de fallo. */
    private function pintado(\DOMElement $bloque): string
    {
        $documento = $bloque->ownerDocument;
        $html = '';

        foreach ($bloque->childNodes as $hijo) {
            $html .= $documento->saveHTML($hijo);
        }

        return 'Cuerpo pintado: '.trim($html);
    }

    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }
}
