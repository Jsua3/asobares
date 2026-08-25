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
use App\Http\Controllers\Publico\MisVacantesController;
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
// Es lectura, no un formulario, y cada visita con ?municipio= inserta una fila
// para el observatorio: sin límite, un bucle sobre los 12 municipios del
// Quindío envenena esa cifra. 6,1 —el límite de los formularios de escritura—
// cortaría a la mitad a alguien comparando municipios de verdad; 30,1 iguala
// el límite que ya usan las otras rutas de lectura del sitio (retorno y
// estado de pago) y sigue muy lejos de permitir un bucle serio.
Route::get('/abre-tu-negocio', [GuiaController::class, 'index'])
    ->middleware('throttle:30,1')
    ->name('guia.index');
// Descargar un formato también escribe en consultas_guia (ver el
// controlador), pero es una acción más deliberada y menos repetitiva que
// elegir municipio: nadie baja 30 formatos por minuto de verdad, y cada guía
// solo trae dos o tres. 10,1 iguala el límite que ya usan las otras
// escrituras ocasionales del sitio (resolver el pago simulado) y sobra para
// bajar todos los formatos de una guía real sin rebotar a nadie.
Route::get('/abre-tu-negocio/formato/{requisito}', [GuiaController::class, 'descargarFormato'])
    ->middleware('throttle:10,1')
    ->name('guia.formato');

// Bolsa de empleo.
Route::get('/empleo', [EmpleoController::class, 'index'])->name('empleo.index');
Route::post('/empleo/perfil', [EmpleoController::class, 'registrarAspirante'])
    ->middleware('throttle:6,1')
    ->name('empleo.aspirante');
Route::get('/empleo/{vacante}', [EmpleoController::class, 'show'])->name('empleo.show');
Route::post('/empleo/{vacante}/postular', [EmpleoController::class, 'postular'])
    ->middleware('throttle:6,1')
    ->name('empleo.postular');

// Artistas y proveedores.
Route::get('/artistas', [ArtistaController::class, 'index'])->name('artistas.index');
// Antes que la ruta con slug: si no, «inscripcion» se leería como un artista.
Route::get('/artistas/inscripcion', [ArtistaController::class, 'inscripcion'])->name('artistas.inscripcion');
Route::post('/artistas/inscripcion', [ArtistaController::class, 'guardarInscripcion'])
    ->middleware('throttle:6,1')
    ->name('artistas.inscripcion.store');
Route::get('/artistas/{artista:slug}', [ArtistaController::class, 'show'])->name('artistas.show');

Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
Route::get('/proveedores/inscripcion', [ProveedorController::class, 'inscripcion'])->name('proveedores.inscripcion');
Route::post('/proveedores/inscripcion', [ProveedorController::class, 'guardarInscripcion'])
    ->middleware('throttle:6,1')
    ->name('proveedores.inscripcion.store');

// Eventos del gremio.
Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
// Antes que la ruta con slug, por el mismo motivo y con el mismo remedio que
// `artistas.inscripcion` unas líneas más arriba: `/eventos/calendario` tiene
// dos segmentos igual que `/eventos/{evento:slug}`, así que registrada después
// no se alcanzaría NUNCA y Laravel devolvería 404 buscando un evento con slug
// «calendario». No avisa nada: es una URL que simplemente deja de existir.
//
// Y son dos rutas y no una con parámetros opcionales, para que cada mes tenga
// una URL canónica única y `/eventos/calendario` a secas redirija al mes en
// curso en vez de servir el mismo contenido bajo dos direcciones.
Route::get('/eventos/calendario', [EventoController::class, 'calendarioDeHoy'])
    ->name('eventos.calendario.hoy');
Route::get('/eventos/calendario/{anio}/{mes}', [EventoController::class, 'calendario'])
    ->where(['anio' => '[0-9]{4}', 'mes' => '0[1-9]|1[0-2]'])
    ->name('eventos.calendario');
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

    // Cada llamada crea una transacción y, con Bold, un enlace de pago real.
    Route::post('/mi-cuenta/pagar', [MiCuentaController::class, 'pagarMensualidad'])
        ->middleware('throttle:5,1')
        ->name('mi-cuenta.pagar');

    // Bolsa de empleo: el establecimiento publica y corrige lo suyo.
    Route::get('/mi-cuenta/vacantes', [MisVacantesController::class, 'index'])->name('mi-cuenta.vacantes.index');
    Route::get('/mi-cuenta/vacantes/crear', [MisVacantesController::class, 'crear'])->name('mi-cuenta.vacantes.crear');
    Route::post('/mi-cuenta/vacantes', [MisVacantesController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('mi-cuenta.vacantes.store');
    Route::get('/mi-cuenta/vacantes/{vacante}/editar', [MisVacantesController::class, 'editar'])->name('mi-cuenta.vacantes.editar');
    Route::put('/mi-cuenta/vacantes/{vacante}', [MisVacantesController::class, 'update'])
        ->middleware('throttle:20,1')
        ->name('mi-cuenta.vacantes.update');
    Route::post('/mi-cuenta/vacantes/{vacante}/cerrar', [MisVacantesController::class, 'cerrar'])->name('mi-cuenta.vacantes.cerrar');
    Route::post('/mi-cuenta/vacantes/{vacante}/reabrir', [MisVacantesController::class, 'reabrir'])->name('mi-cuenta.vacantes.reabrir');
    Route::get('/mi-cuenta/vacantes/{vacante}', [MisVacantesController::class, 'show'])->name('mi-cuenta.vacantes.show');
    Route::patch('/mi-cuenta/postulaciones/{postulacion}', [MisVacantesController::class, 'gestionarPostulacion'])
        ->middleware('throttle:60,1')
        ->name('mi-cuenta.postulaciones.gestionar');
});

/*
|--------------------------------------------------------------------------
| Pagos
|--------------------------------------------------------------------------
*/

// La pasarela simulada aprueba pagos con solo pedírselo. Sus rutas no deben
// existir fuera de la máquina de desarrollo: no basta con que respondan 404
// desde el controlador, es que no tienen por qué estar registradas.
if (app()->environment('local', 'testing')) {
    Route::get('/pago-simulado/{transaccion:referencia}', [PagoController::class, 'simulado'])->name('pago.simulado');
    Route::post('/pago-simulado/{transaccion:referencia}', [PagoController::class, 'resolverSimulado'])
        ->middleware('throttle:10,1')
        ->name('pago.simulado.resolver');
}

// Punto de vuelta desde la pasarela. Existe aparte porque Bold puede añadir
// sus propios parámetros a la URL de retorno, y eso rompería una firma: aquí
// se ignoran y se manda a la página firmada.
Route::get('/pago/{transaccion:referencia}/retorno', [PagoController::class, 'retorno'])
    ->middleware('throttle:30,1')
    ->name('pago.retorno');

// Muestra el detalle de un cobro, así que va firmada y caduca.
Route::get('/pago/{transaccion:referencia}/estado', [PagoController::class, 'estado'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('pago.estado');

// La firma del webhook reemplaza al token CSRF: la petición viene de Bold.
Route::post('/webhooks/bold', WebhookBoldController::class)
    ->withoutMiddleware([PreventRequestForgery::class])
    ->middleware('throttle:120,1')
    ->name('webhooks.bold');
