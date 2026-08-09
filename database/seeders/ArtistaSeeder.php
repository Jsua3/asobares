<?php

namespace Database\Seeders;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use App\Models\Artista;
use App\Models\Municipio;
use Database\Seeders\Support\GeneradorImagen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * "El del bar a las 12 de la noche buscando un DJ": género, tarifa desde,
 * contacto directo y un video para escucharlo antes de llamar.
 */
class ArtistaSeeder extends Seeder
{
    public function run(GeneradorImagen $imagenes): void
    {
        $municipios = Municipio::pluck('id', 'slug');

        $artistas = [
            [
                'nombre' => 'DJ Marea',
                'tipo' => TipoArtista::Dj,
                'genero_musical' => 'House / Deep house',
                'descripcion' => 'Sets de house melódico para terrazas y rooftops. Trae controladora propia y cabina básica. Ha tocado en Armenia, Pereira y Manizales.',
                'tarifa_desde' => 450000,
                'video_url' => 'https://www.youtube.com/watch?v=5qap5aO4i9A',
                'whatsapp' => '3145511220', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'DJ Kandela',
                'tipo' => TipoArtista::Dj,
                'genero_musical' => 'Salsa / Tropical',
                'descripcion' => 'Salsa brava, boogaloo y tropical de colección. Especialista en noches de salsa con público que sabe bailar.',
                'tarifa_desde' => 380000,
                'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
                'whatsapp' => '3106678890', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'DJ Nocturna',
                'tipo' => TipoArtista::Dj,
                'genero_musical' => 'Techno / Minimal',
                'descripcion' => 'Techno de closing para las últimas tres horas de la noche. Trabaja con sonido calibrado y pide prueba de sonido previa.',
                'tarifa_desde' => 520000,
                'video_url' => null,
                'whatsapp' => '3122245567', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'DJ Guadua',
                'tipo' => TipoArtista::Dj,
                'genero_musical' => 'Crossover / Reguetón',
                'descripcion' => 'Crossover para público amplio: reguetón, popular y clásicos. La opción segura para un sábado lleno.',
                'tarifa_desde' => 320000,
                'video_url' => null,
                'whatsapp' => '3178812204', 'municipio' => 'la-tebaida',
            ],
            [
                'nombre' => 'Los Cafeteros del Ritmo',
                'tipo' => TipoArtista::Banda,
                'genero_musical' => 'Música tropical en vivo',
                'descripcion' => 'Orquesta de ocho músicos con repertorio de salsa, merengue y cumbia. Requieren tarima mínima de 4x3 metros y toma de 110V.',
                'tarifa_desde' => 1800000,
                'video_url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                'whatsapp' => '3160033218', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'Bahareque Rock',
                'tipo' => TipoArtista::Banda,
                'genero_musical' => 'Rock en español',
                'descripcion' => 'Cuarteto de rock en español con covers de los noventa y material propio. Formato acústico o eléctrico según el espacio.',
                'tarifa_desde' => 900000,
                'video_url' => null,
                'whatsapp' => '3134489911', 'municipio' => 'calarca',
            ],
            [
                'nombre' => 'Valentina Cardona',
                'tipo' => TipoArtista::Solista,
                'genero_musical' => 'Bolero / Jazz vocal',
                'descripcion' => 'Voz y guitarra para noches tranquilas de gastrobar. Repertorio de bolero, bossa y jazz vocal en español y portugués.',
                'tarifa_desde' => 400000,
                'video_url' => null,
                'whatsapp' => '3197745503', 'municipio' => 'filandia',
            ],
            [
                'nombre' => 'Tomás Arbeláez',
                'tipo' => TipoArtista::Solista,
                'genero_musical' => 'Música andina colombiana',
                'descripcion' => 'Tiple y voz, repertorio de música andina colombiana y pasillos. Muy solicitado para eventos institucionales y aperturas.',
                'tarifa_desde' => 350000,
                'video_url' => null,
                'whatsapp' => '3183356674', 'municipio' => 'salento',
            ],
            [
                'nombre' => 'DJ Amanecer',
                'tipo' => TipoArtista::Dj,
                'genero_musical' => 'Afrobeat / World',
                'descripcion' => 'Sets de cierre para el after: afrobeat, world music y downtempo. Ficha recién inscrita, todavía sin revisar.',
                'tarifa_desde' => 300000,
                'video_url' => null,
                'whatsapp' => '3145511221', 'municipio' => 'armenia',
                // Pendiente a propósito, con antigüedad corta: junto con el
                // proveedor y la vacante pendientes, la banda de pendientes
                // del tablero muestra más de un renglón con edades distintas.
                'estado' => EstadoPublicacion::PendienteAprobacion,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ];

        foreach ($artistas as $artista) {
            $slug = $artista['municipio'];
            unset($artista['municipio']);

            $artista['slug'] = Str::slug($artista['nombre']);
            $artista['municipio_id'] = $municipios[$slug];
            $artista['instagram_url'] = 'https://instagram.com/'.Str::slug($artista['nombre'], '');
            $artista['foto'] = $imagenes->generar("artista-{$artista['nombre']}", 'artistas', 800, 800);
            $artista['estado'] ??= EstadoPublicacion::Publicado;

            Artista::updateOrCreate(['slug' => $artista['slug']], $artista);
        }
    }
}
