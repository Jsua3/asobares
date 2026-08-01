<?php

use App\Http\Controllers\PagoController;
use App\Http\Controllers\Publico\AfiliacionController;
use App\Http\Controllers\Publico\ArtistaController;
use App\Http\Controllers\Publico\ContactoController;
use App\Http\Controllers\Publico\DirectorioController;
use App\Http\Controllers\Publico\EmpleoController;
use App\Http\Controllers\Publico\EventoController;
use App\Http\Controllers\Publico\GuiaController;
use App\Http\Controllers\Publico\InicioController;
use App\Http\Controllers\Publico\MiCuentaController;
use App\Http\Controllers\Publico\NoticiaController;
use App\Http\Controllers\Publico\PaginaController;
use App\Http\Controllers\Publico\ProveedorController;
use App\Http\Controllers\Publico\SesionAsociadoController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WebhookBoldController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitio público
|--------------------------------------------------------------------------
*/

Route::get('/', InicioController::class)->name('inicio');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Se sirve por ruta, no como archivo estático, para que la URL del sitemap
// siempre sea absoluta y correcta en cualquier dominio.
Route::get('/robots.txt', function (): Response {
    $lineas = [
        'User-agent: *',
        'Allow: /',
        '',
        '# Zonas privadas: panel del gremio, cuenta del afiliado y pasarela de pago.',
        'Disallow: /admin',
        'Disallow: /mi-cuenta',
        'Disallow: /pago-simulado',
        'Disallow: /pago/',
        'Disallow: /webhooks/',
        '',
        'Sitemap: '.route('sitemap'),
    ];

    return response(implode("\n", $lineas)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

Route::get('/quienes-somos', [PaginaController::class, 'quienesSomos'])->name('quienes-somos');
Route::get('/politica-de-datos', [PaginaController::class, 'politicaDeDatos'])->name('politica-de-datos');

// Directorio de establecimientos.
Route::get('/directorio', [DirectorioController::class, 'index'])->name('directorio.index');
Route::get('/directorio/{asociado:slug}', [DirectorioController::class, 'show'])->name('directorio.show');

// Guía normativa: el producto insignia.
Route::get('/abre-tu-negocio', [GuiaController::class, 'index'])->name('guia.index');
Route::get('/abre-tu-negocio/formato/{requisito}', [GuiaController::class, 'descargarFormato'])->name('guia.formato');

// Bolsa de empleo.
Route::get('/empleo', [EmpleoController::class, 'index'])->name('empleo.index');
Route::post('/empleo/perfil', [EmpleoController::class, 'registrarAspirante'])
    ->middleware('throttle:6,1')
    ->name('empleo.aspirante');

// Artistas y proveedores.
Route::get('/artistas', [ArtistaController::class, 'index'])->name('artistas.index');
Route::get('/artistas/{artista:slug}', [ArtistaController::class, 'show'])->name('artistas.show');
Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');

// Eventos del gremio.
Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{evento:slug}', [EventoController::class, 'show'])->name('eventos.show');
Route::post('/eventos/{evento:slug}/inscripcion', [EventoController::class, 'inscribir'])
    ->middleware('throttle:6,1')
    ->name('eventos.inscribir');

// Boletín.
Route::get('/boletin', [NoticiaController::class, 'index'])->name('boletin.index');
Route::get('/boletin/{noticia:slug}', [NoticiaController::class, 'show'])->name('boletin.show');

// Afiliación y contacto.
Route::get('/afiliate', [AfiliacionController::class, 'index'])->name('afiliate');
Route::post('/afiliate', [AfiliacionController::class, 'store'])->middleware('throttle:6,1')->name('afiliate.store');
Route::get('/contacto', [ContactoController::class, 'index'])->name('contacto');
Route::post('/contacto', [ContactoController::class, 'store'])->middleware('throttle:6,1')->name('contacto.store');

/*
|--------------------------------------------------------------------------
| Mi cuenta (rol asociado)
|--------------------------------------------------------------------------
*/

Route::get('/mi-cuenta/entrar', [SesionAsociadoController::class, 'mostrarFormulario'])->name('mi-cuenta.entrar');
Route::post('/mi-cuenta/entrar', [SesionAsociadoController::class, 'entrar'])
    ->middleware('throttle:5,1')
    ->name('mi-cuenta.entrar.post');
Route::post('/mi-cuenta/salir', [SesionAsociadoController::class, 'salir'])->name('mi-cuenta.salir');

Route::middleware(['auth', 'rol.asociado'])->group(function (): void {
    Route::get('/mi-cuenta', [MiCuentaController::class, 'index'])->name('mi-cuenta.index');
    Route::post('/mi-cuenta/pagar', [MiCuentaController::class, 'pagarMensualidad'])->name('mi-cuenta.pagar');
});

/*
|--------------------------------------------------------------------------
| Pagos
|--------------------------------------------------------------------------
*/

Route::get('/pago-simulado/{transaccion:referencia}', [PagoController::class, 'simulado'])->name('pago.simulado');
Route::post('/pago-simulado/{transaccion:referencia}', [PagoController::class, 'resolverSimulado'])->name('pago.simulado.resolver');
Route::get('/pago/{transaccion:referencia}/estado', [PagoController::class, 'estado'])->name('pago.estado');

// La firma del webhook reemplaza al token CSRF: la petición viene de Bold.
Route::post('/webhooks/bold', WebhookBoldController::class)
    ->withoutMiddleware([PreventRequestForgery::class])
    ->name('webhooks.bold');
