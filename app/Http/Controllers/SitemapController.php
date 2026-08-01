<?php

namespace App\Http\Controllers;

use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Municipio;
use App\Models\Noticia;
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
            ['afiliate', Url::CHANGE_FREQUENCY_MONTHLY, 0.8],
            ['contacto', Url::CHANGE_FREQUENCY_YEARLY, 0.5],
            ['politica-de-datos', Url::CHANGE_FREQUENCY_YEARLY, 0.3],
        ];

        foreach ($fijas as [$ruta, $frecuencia, $prioridad]) {
            $mapa->add(Url::create(route($ruta))->setChangeFrequency($frecuencia)->setPriority($prioridad));
        }

        // La guía por municipio son URLs distintas y de mucho valor para SEO.
        Municipio::whereHas('requisitos', fn ($q) => $q->publicado())
            ->get()
            ->each(fn (Municipio $municipio) => $mapa->add(
                Url::create(route('guia.index', ['municipio' => $municipio->slug]))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.8)
            ));
    }

    private function fichas(Sitemap $mapa): void
    {
        Asociado::publicado()->get()->each(fn (Asociado $a) => $mapa->add(
            Url::create(route('directorio.show', $a))
                ->setLastModificationDate($a->updated_at)
                ->setPriority(0.7)
        ));

        Evento::publicado()->get()->each(fn (Evento $e) => $mapa->add(
            Url::create(route('eventos.show', $e))->setLastModificationDate($e->updated_at)->setPriority(0.6)
        ));

        Noticia::visible()->get()->each(fn (Noticia $n) => $mapa->add(
            Url::create(route('boletin.show', $n))->setLastModificationDate($n->updated_at)->setPriority(0.5)
        ));

        Artista::publicado()->get()->each(fn (Artista $a) => $mapa->add(
            Url::create(route('artistas.show', $a))->setLastModificationDate($a->updated_at)->setPriority(0.5)
        ));
    }
}
