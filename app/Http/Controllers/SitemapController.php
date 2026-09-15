<?php

namespace App\Http\Controllers;

use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Municipio;
use App\Models\Noticia;
use App\Models\Vacante;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Se genera al vuelo: el gremio publica poco y así el mapa del sitio
 * nunca queda desactualizado ni hay que acordarse de regenerarlo.
 */
class SitemapController
{
    public function __invoke(): Response
    {
        $mapa = Sitemap::create();

        $this->paginasFijas($mapa);
        $this->fichas($mapa);

        return response($mapa->render(), 200, ['Content-Type' => 'application/xml']);
    }

    private function paginasFijas(Sitemap $mapa): void
    {
        $fijas = [
            ['inicio', Url::CHANGE_FREQUENCY_WEEKLY, 1.0],
            ['directorio.index', Url::CHANGE_FREQUENCY_WEEKLY, 0.9],
            ['guia.index', Url::CHANGE_FREQUENCY_MONTHLY, 0.9],
            ['empleo.index', Url::CHANGE_FREQUENCY_DAILY, 0.8],
            ['artistas.index', Url::CHANGE_FREQUENCY_WEEKLY, 0.7],
            ['proveedores.index', Url::CHANGE_FREQUENCY_WEEKLY, 0.7],
            ['eventos.index', Url::CHANGE_FREQUENCY_WEEKLY, 0.7],
            ['boletin.index', Url::CHANGE_FREQUENCY_MONTHLY, 0.6],
            ['quienes-somos', Url::CHANGE_FREQUENCY_YEARLY, 0.6],
            ['aliados.index', Url::CHANGE_FREQUENCY_MONTHLY, 0.6],
            ['afiliate', Url::CHANGE_FREQUENCY_MONTHLY, 0.8],
            ['contacto', Url::CHANGE_FREQUENCY_YEARLY, 0.5],
            ['politica-de-datos', Url::CHANGE_FREQUENCY_YEARLY, 0.3],
        ];

        foreach ($fijas as [$ruta, $frecuencia, $prioridad]) {
            $mapa->add(Url::create(route($ruta))->setChangeFrequency($frecuencia)->setPriority($prioridad));
        }

        /*
         * El calendario, y sólo el mes en curso. Enumerar meses sería una lista
         * infinita —los enlaces de anterior y siguiente no tienen tope—, y los
         * meses sin datos ya se marcan `noindex` en la propia página.
         *
         * Va aparte del bucle de `$fijas` porque ese llama a `route($ruta)` sin
         * parámetros, y va la URL FECHADA y no `eventos.calendario.hoy`: un
         * sitemap no debe listar una redirección.
         */
        $mapa->add(
            Url::create(route('eventos.calendario', [now()->year, now()->format('m')]))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.6)
        );

        // La guía por municipio son URLs distintas y de mucho valor para SEO.
        // Con `vigente()`, porque anunciarle a Google una guía vacía es peor
        // que no anunciarla.
        Municipio::activos()
            ->whereHas('requisitos', fn (Builder $requisitos): Builder => $requisitos->publicado()->vigente())
            ->ordenados()
            ->get()
            ->each(fn (Municipio $municipio): Sitemap => $mapa->add(
                Url::create(route('guia.index', ['municipio' => $municipio->slug]))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.8)
            ));
    }

    private function fichas(Sitemap $mapa): void
    {
        Asociado::publicado()->get()->each(fn (Asociado $asociado): Sitemap => $mapa->add(
            Url::create(route('directorio.show', $asociado))
                ->setLastModificationDate($asociado->updated_at)
                ->setPriority(0.7)
        ));

        Evento::publicado()->get()->each(fn (Evento $evento): Sitemap => $mapa->add(
            Url::create(route('eventos.show', $evento))->setLastModificationDate($evento->updated_at)->setPriority(0.6)
        ));

        Noticia::visible()->get()->each(fn (Noticia $noticia): Sitemap => $mapa->add(
            Url::create(route('boletin.show', $noticia))->setLastModificationDate($noticia->updated_at)->setPriority(0.5)
        ));

        Artista::publicado()->get()->each(fn (Artista $artista): Sitemap => $mapa->add(
            Url::create(route('artistas.show', $artista))->setLastModificationDate($artista->updated_at)->setPriority(0.5)
        ));

        /*
         * Las vacantes van con `vigente()` además de `publicado()`, por el mismo
         * motivo que la guía normativa: anunciarle a Google una oferta cerrada o
         * vencida manda al visitante a una vacante muerta.
         *
         * Prioridad alta y frecuencia diaria porque es el módulo que más rota:
         * una oferta vive semanas, no años. La ficha trae JSON-LD `JobPosting`, y
         * ese marcado --el que mete una oferta en Google Jobs-- apenas sirve sin
         * la URL en el mapa.
         */
        Vacante::publicado()->vigente()->get()->each(fn (Vacante $vacante): Sitemap => $mapa->add(
            Url::create(route('empleo.show', $vacante))
                ->setLastModificationDate($vacante->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.7)
        ));
    }
}
