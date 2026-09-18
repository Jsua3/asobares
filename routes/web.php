<?php

use App\Http\Controllers\PagoController;
use App\Http\Controllers\Publico\AfiliacionController;
use App\Http\Controllers\Publico\ArtistaController;
use App\Http\Controllers\Publico\ContactoController;
use App\Http\Controllers\Publico\ContrasenaAsociadoController;
use App\Http\Controllers\Publico\DirectorioController;
use App\Http\Controllers\Publico\EmpleoController;
use App\Http\Controllers\Publico\EventoComunitarioController;
use App\Http\Controllers\Publico\EventoController;
use App\Http\Controllers\Publico\GuiaController;
use App\Http\Controllers\Publico\InicioController;
use App\Http\Controllers\Publico\MiCuentaController;
use App\Http\Controllers\Publico\MisArtistasController;
use App\Http\Controllers\Publico\MisAspirantesController;
use App\Http\Controllers\Publico\MisFotosController;
use App\Http\Controllers\Publico\MisProveedoresController;
use App\Http\Controllers\Publico\MisVacantesController;
use App\Http\Controllers\Publico\NoticiaController;
use App\Http\Controllers\Publico\PaginaController;
use App\Http\Controllers\Publico\ProveedorController;
use App\Http\Controllers\Publico\SeguridadDeLaCuentaController;
use App\Http\Controllers\Publico\SesionAsociadoController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WebhookBoldController;
use App\Http\Controllers\WebhookBoldPruebasController;
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
        // Mientras no haya dominio propio, el sitio no se deja indexar:
        // lo que se indexe hoy queda apuntando al host temporal de Cloud.
        // `config/sitio.php` explica por qué el valor por defecto es cerrado.
        config('sitio.indexable') ? 'Allow: /' : 'Disallow: /',
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
Route::get('/aliados', [PaginaController::class, 'aliados'])->name('aliados.index');
Route::get('/politica-de-datos', [PaginaController::class, 'politicaDeDatos'])->name('politica-de-datos');

// Directorio de establecimientos.
Route::get('/directorio', [DirectorioController::class, 'index'])->name('directorio.index');
// URL propia por municipio: la canónica de /directorio?municipio= siempre
// colapsa en /directorio (url()->current() descarta la query), así que esa
// forma nunca puede posicionar "bares en Salento" aparte de "bares en
// Armenia". Va antes de /{asociado:slug} solo por orden de lectura: al tener
// un segmento más, Laravel no los confunde en ningún orden.
Route::get('/directorio/municipio/{municipio:slug}', [DirectorioController::class, 'porMunicipio'])->name('directorio.municipio');
Route::get('/directorio/{asociado:slug}', [DirectorioController::class, 'show'])->name('directorio.show');

// Guía normativa: el producto insignia.
// Es lectura, no un formulario, y cada visita con ?municipio= inserta una fila
// para el observatorio: sin límite, un bucle sobre los 12 municipios del
// Quindío envenena esa cifra. Seis por minuto —el límite de los formularios de
// escritura— cortaría a la mitad a alguien comparando municipios de verdad;
// treinta iguala el límite que ya usan las otras rutas de lectura del sitio
// (retorno y estado de pago) y sigue muy lejos de permitir un bucle serio.
// Cada máximo vive en AppServiceProvider::LIMITES_POR_MINUTO, uno por ruta.
Route::get('/abre-tu-negocio', [GuiaController::class, 'index'])
    ->middleware('throttle:guia')
    ->name('guia.index');
// Misma URL propia por municipio, mismo motivo y mismo límite que arriba:
// llegar aquí en bucle sobre los doce municipios es el mismo abuso que la
// nota de arriba explica, solo que por ruta en vez de por query string.
Route::get('/abre-tu-negocio/{municipio:slug}', [GuiaController::class, 'porMunicipio'])
    ->middleware('throttle:guia')
    ->name('guia.municipio');
// Descargar un formato también escribe en consultas_guia (ver el
// controlador), pero es una acción más deliberada y menos repetitiva que
// elegir municipio: nadie baja 30 formatos por minuto de verdad, y cada guía
// solo trae dos o tres. Diez por minuto, lo mismo que la otra escritura
// ocasional del sitio (resolver el pago simulado), sobra para bajar todos los
// formatos de una guía real sin rebotar a nadie.
Route::get('/abre-tu-negocio/formato/{requisito}', [GuiaController::class, 'descargarFormato'])
    ->middleware('throttle:guia-formato')
    ->name('guia.formato');

// Bolsa de empleo.
Route::get('/empleo', [EmpleoController::class, 'index'])->name('empleo.index');
Route::post('/empleo/perfil', [EmpleoController::class, 'registrarAspirante'])
    ->middleware('throttle:empleo-perfil')
    ->name('empleo.aspirante');
Route::get('/empleo/{vacante}', [EmpleoController::class, 'show'])->name('empleo.show');
Route::post('/empleo/{vacante}/postular', [EmpleoController::class, 'postular'])
    ->middleware('throttle:empleo-postular')
    ->name('empleo.postular');

// Artistas y proveedores.
Route::get('/artistas', [ArtistaController::class, 'index'])->name('artistas.index');
// Antes que la ruta con slug: si no, «inscripcion» se leería como un artista.
Route::get('/artistas/inscripcion', [ArtistaController::class, 'inscripcion'])->name('artistas.inscripcion');
Route::post('/artistas/inscripcion', [ArtistaController::class, 'guardarInscripcion'])
    ->middleware('throttle:artistas-inscripcion')
    ->name('artistas.inscripcion.store');
Route::get('/artistas/{artista:slug}', [ArtistaController::class, 'show'])->name('artistas.show');

Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
Route::get('/proveedores/inscripcion', [ProveedorController::class, 'inscripcion'])->name('proveedores.inscripcion');
Route::post('/proveedores/inscripcion', [ProveedorController::class, 'guardarInscripcion'])
    ->middleware('throttle:proveedores-inscripcion')
    ->name('proveedores.inscripcion.store');

// Eventos del gremio.
Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
Route::post('/eventos/calendario/comunidad', [EventoComunitarioController::class, 'store'])
    ->middleware('throttle:eventos-comunidad')
    ->name('eventos.comunidad.store');
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
    ->middleware('throttle:eventos-inscripcion')
    ->name('eventos.inscribir');

// Boletín.
Route::get('/boletin', [NoticiaController::class, 'index'])->name('boletin.index');
Route::get('/boletin/{noticia:slug}', [NoticiaController::class, 'show'])->name('boletin.show');

// Afiliación y contacto.
Route::get('/afiliate', [AfiliacionController::class, 'index'])->name('afiliate');
Route::post('/afiliate', [AfiliacionController::class, 'store'])->middleware('throttle:afiliate')->name('afiliate.store');
Route::get('/contacto', [ContactoController::class, 'index'])->name('contacto');
Route::post('/contacto', [ContactoController::class, 'store'])->middleware('throttle:contacto')->name('contacto.store');

/*
|--------------------------------------------------------------------------
| Mi cuenta (rol asociado)
|--------------------------------------------------------------------------
*/

Route::get('/mi-cuenta/entrar', [SesionAsociadoController::class, 'mostrarFormulario'])->name('mi-cuenta.entrar');
Route::post('/mi-cuenta/entrar', [SesionAsociadoController::class, 'entrar'])
    ->middleware('throttle:mi-cuenta-entrar')
    ->name('mi-cuenta.entrar.post');
Route::get('/mi-cuenta/contrasena/{token}', [ContrasenaAsociadoController::class, 'editar'])
    ->middleware('guest')
    ->name('mi-cuenta.password.reset');
Route::post('/mi-cuenta/contrasena', [ContrasenaAsociadoController::class, 'actualizar'])
    ->middleware(['guest', 'throttle:mi-cuenta-contrasena'])
    ->name('mi-cuenta.password.update');
Route::post('/mi-cuenta/salir', [SesionAsociadoController::class, 'salir'])->name('mi-cuenta.salir');

Route::middleware(['auth', 'rol.asociado'])->group(function (): void {
    Route::get('/mi-cuenta', [MiCuentaController::class, 'index'])->name('mi-cuenta.index');

    // Cada llamada crea una transacción y, con Bold, un enlace de pago real.
    Route::post('/mi-cuenta/pagar', [MiCuentaController::class, 'pagarMensualidad'])
        ->middleware('throttle:mi-cuenta-pagar')
        ->name('mi-cuenta.pagar');

    // Fotos del establecimiento: las sube el dueño y las aprueba el gremio.
    Route::get('/mi-cuenta/fotos', [MisFotosController::class, 'index'])->name('mi-cuenta.fotos.index');
    Route::post('/mi-cuenta/fotos', [MisFotosController::class, 'store'])
        // Treinta por minuto, por encima del tope de doce fotos por ficha a
        // propósito: con un límite menor, un afiliado que suba sus doce fotos
        // de una sentada chocaría contra un 429 a mitad de camino.
        ->middleware('throttle:mi-cuenta-fotos-subir')
        ->name('mi-cuenta.fotos.store');
    Route::delete('/mi-cuenta/fotos/{media}', [MisFotosController::class, 'destroy'])
        ->middleware('throttle:mi-cuenta-fotos-borrar')
        ->name('mi-cuenta.fotos.destroy');

    // Seguridad de la cuenta: el titular cambia su contraseña. Es la única
    // puerta que apaga la marca de provisional, así que queda fuera de las
    // secciones que esa marca cierra.
    Route::get('/mi-cuenta/seguridad', [SeguridadDeLaCuentaController::class, 'editar'])->name('mi-cuenta.seguridad');
    Route::put('/mi-cuenta/seguridad', [SeguridadDeLaCuentaController::class, 'actualizar'])
        ->middleware('throttle:mi-cuenta-seguridad')
        ->name('mi-cuenta.seguridad.actualizar');

    // Las secciones con datos de otras personas: contactos de proveedores y
    // artistas, el banco de talento y las postulaciones de cada vacante.
    // Mientras la contraseña sea provisional, `contrasena.propia` las manda a
    // /mi-cuenta/seguridad (ver ExigirContrasenaPropia). La bolsa entera va
    // dentro porque las postulaciones se ven en cada vacante y cerrar una
    // vacante no pasa por moderación.
    Route::middleware('contrasena.propia')->group(function (): void {
        // Beneficios detrás de la sesión: los datos de contacto de proveedores,
        // artistas y aspirantes son la contraprestación de la cuota. La cara
        // pública de /proveedores existe, pero sin un solo contacto.
        //
        // Los artistas, con una salvedad: su ficha pública NO se vacía. El
        // escaparate --nombre, foto, género, video-- es lo que el artista busca al
        // inscribirse; lo que se muda aquí es solo el contacto.
        Route::get('/mi-cuenta/proveedores', [MisProveedoresController::class, 'index'])->name('mi-cuenta.proveedores.index');
        Route::get('/mi-cuenta/artistas', [MisArtistasController::class, 'index'])->name('mi-cuenta.artistas.index');
        Route::get('/mi-cuenta/aspirantes', [MisAspirantesController::class, 'index'])->name('mi-cuenta.aspirantes.index');

        // Bolsa de empleo: el establecimiento publica y corrige lo suyo.
        Route::get('/mi-cuenta/vacantes', [MisVacantesController::class, 'index'])->name('mi-cuenta.vacantes.index');
        Route::get('/mi-cuenta/vacantes/crear', [MisVacantesController::class, 'crear'])->name('mi-cuenta.vacantes.crear');
        Route::post('/mi-cuenta/vacantes', [MisVacantesController::class, 'store'])
            ->middleware('throttle:mi-cuenta-vacantes-crear')
            ->name('mi-cuenta.vacantes.store');
        Route::get('/mi-cuenta/vacantes/{vacante}/editar', [MisVacantesController::class, 'editar'])->name('mi-cuenta.vacantes.editar');
        Route::put('/mi-cuenta/vacantes/{vacante}', [MisVacantesController::class, 'update'])
            ->middleware('throttle:mi-cuenta-vacantes-editar')
            ->name('mi-cuenta.vacantes.update');
        Route::post('/mi-cuenta/vacantes/{vacante}/cerrar', [MisVacantesController::class, 'cerrar'])->name('mi-cuenta.vacantes.cerrar');
        Route::post('/mi-cuenta/vacantes/{vacante}/reabrir', [MisVacantesController::class, 'reabrir'])->name('mi-cuenta.vacantes.reabrir');
        Route::get('/mi-cuenta/vacantes/{vacante}', [MisVacantesController::class, 'show'])->name('mi-cuenta.vacantes.show');
        Route::patch('/mi-cuenta/postulaciones/{postulacion}', [MisVacantesController::class, 'gestionarPostulacion'])
            ->middleware('throttle:mi-cuenta-postulaciones')
            ->name('mi-cuenta.postulaciones.gestionar');
    });
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
        ->middleware('throttle:pago-simulado')
        ->name('pago.simulado.resolver');
}

// Punto de vuelta desde la pasarela. Existe aparte porque Bold puede añadir
// sus propios parámetros a la URL de retorno, y eso rompería una firma: aquí
// se ignoran y se manda a la página firmada.
Route::get('/pago/{transaccion:referencia}/retorno', [PagoController::class, 'retorno'])
    ->middleware('throttle:pago-retorno')
    ->name('pago.retorno');

// Muestra el detalle de un cobro, así que va firmada y caduca.
Route::get('/pago/{transaccion:referencia}/estado', [PagoController::class, 'estado'])
    ->middleware(['signed', 'throttle:pago-estado'])
    ->name('pago.estado');

// La firma del webhook reemplaza al token CSRF: la petición viene de Bold.
Route::post('/webhooks/bold', WebhookBoldController::class)
    ->withoutMiddleware([PreventRequestForgery::class])
    ->middleware('throttle:webhook-bold')
    ->name('webhooks.bold');

Route::post('/webhooks/bold/pruebas', WebhookBoldPruebasController::class)
    ->withoutMiddleware([PreventRequestForgery::class])
    ->middleware('throttle:webhook-bold')
    ->name('webhooks.bold.pruebas');
