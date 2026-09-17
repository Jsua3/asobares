# Informe — Lote de arreglos de la revisión final (`afiliados/alta-real`, base `398cd5c`)

Estado: **DONE_WITH_CONCERNS** (todos los «ARREGLAR» hechos y probados; las preocupaciones son de alcance y de redacción, ninguna bloquea).

Todos los comandos, desde la herramienta Bash en `D:\Sua_Files\IdeaProjects\Asobares3`, con `PHP=/c/Users/Predator/.config/php85/php.exe`. Las corridas se juzgan por el JSON: `artisan test` sale con código 1 aunque diga `"result":"passed"` (rareza conocida, se repitió en todas). Nada de C1, M4 ni M7 se tocó.

## Commits

| SHA | Grupo | Mensaje |
|---|---|---|
| `0bda700` | C2 | fix(sesion): la sesión guarda al entrar el hash de la contraseña y no sobrevive a un cambio |
| `f1262a2` | I1 | fix(contrasenas): ni Mi Cuenta ni Usuarios aceptan la contraseña publicada del demo |
| `dbd8901` | I2 | feat(panel): las subidas que ninguna acción procesó se borran pasada una hora |
| `f231c5c` | I3 + M3 + M9 + T3/T5 | fix(panel): el aviso de la importación se lee línea por línea y dice la verdad al reimportar |
| `c8f6505` | M1 + M2 + M5 | fix(panel): el modal de la importación habla en español y el registro no guarda datos de la hoja |
| `dfdcd50` | M6 + M8 | fix(panel): la ayuda de la contraseña al editar avisa que la de un afiliado queda provisional |

Todos con `git add` de rutas explícitas y trailer `Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>`. `docs/ingenieria/entrega-2026-09-04/*` sin tocar.

---

## C2 — la sesión que solo hizo el POST de entrada sobrevivía al cambio

**Cambio**
- `app/Providers/AppServiceProvider.php:124` registra y `:231-249` define `registrarHashDeLaContrasenaAlEntrar()`: oyente de `Illuminate\Auth\Events\Login` que, si `request()->hasSession()` (`:236`), guarda `password_hash_{$evento->guard}` (`:247`) con `Auth::guard($evento->guard)->hashPasswordForCookie(...)` y el mismo `try/catch BadMethodCallException` de respaldo que el middleware (`:244`).
- Comentarios que el arreglo volvía falsos: docblock de `SeguridadDeLaCuentaController.php:27-33` y de los ayudantes `entrar()` de `SeguridadDeLaCuentaTest` e `InvalidacionDeSesionTest` (decían que el hash solo lo guardaba el middleware).

**Pruebas** (`tests/Feature/SeguridadDeLaCuentaTest.php`)
- `test_una_sesion_que_solo_hizo_el_post_de_entrada_cae_cuando_el_dueno_la_cambia` (`:284`): POST de entrada sin seguir la redirección → se guarda esa sesión → otra sesión: el dueño entra y cambia la contraseña por `/mi-cuenta/seguridad` → `forgetGuards()` → la sesión guardada pide `mi-cuenta.aspirantes.index` → tiene que ir a `mi-cuenta.entrar` y quedar invitado.
- Contraprueba `test_una_sesion_que_solo_hizo_el_post_de_entrada_sigue_dentro_si_nadie_cambia_la_contrasena` (`:308`): mismas condiciones sin el cambio → la sesión sigue dentro (la sección la manda a seguridad, no a la puerta).

**ROJO** (antes del arreglo)
```
$PHP artisan test --compact --filter='SeguridadDeLaCuentaTest::test_una_sesion_que_solo_hizo_el_post'
{"result":"failed","tests":2,"passed":1,"failures":[{"test":"...test_una_sesion_que_solo_hizo_el_post_de_entrada_cae_cuando_el_dueno_la_cambia","message":"Expected response status code [201, 301, 302, 303, 307, 308] but received 200."}]}
```
(la sesión del atacante recibía 200 en el banco de talento; la contraprueba ya pasaba)

**VERDE**: mismo comando → `{"result":"passed","tests":2,"passed":2,"assertions":20}`

**Mutaciones** (cada una restaurada)
1. Guardar `'x'` en vez del hash → las dos rojas (`Expected response status code [200] but received 302`: hasta la entrada del dueño muere en su primer GET). Fija el formato.
2. Clave `password_hash_x{guard}` → solo la de ataque roja, con la firma exacta del defecto (`...but received 200.`); la contraprueba verde.
3. Quitar `hasSession()` → `LoginDelPanelTest` con 2 errores `Session store not set on request.` (las pruebas de Livewire corren sin middleware y sin sesión en la petición). La guarda queda fijada por esa clase.

**Vecindad**: `SeguridadDeLaCuentaTest|InvalidacionDeSesionTest|LoginDeAsociadoTest|LoginDelPanelTest|SeccionesCerradasConContrasenaProvisionalTest` → 68/68 (255 aserciones); `SolicitudAfiliacionTest|LimitesDePeticionesTest` (las otras que entran por la puerta real) → 63/63.

**Desvíos**
- El oyente es un método propio en el mismo proveedor y sobre el mismo evento, no una línea más dentro de `registrarBitacoraDeSesiones()`: esa función se llama así y su docblock es RF-39; meter ahí la invalidación de sesiones la haría mentir. Comportamiento idéntico.
- Quité una guarda `! $hash` que había escrito al principio: `users.password` no es nula, la rama era inalcanzable.
- La puerta del panel no tiene prueba propia: `Livewire::test(Login::class)` corre sin middleware (`RequestBroker::temporarilyDisableExceptionHandlingAndMiddleware`), así que la petición no lleva sesión y la guarda `hasSession()` —que el requisito pide— hace que el oyente no actúe ahí. En producción sí actúa: la ruta de update de Livewire lleva el grupo `web` (`HandleRequests.php:102`) y `Filament\Auth\Pages\Login::authenticate()` entra con `attemptWhen()`, que dispara `Login`. Si se quiere probar esa puerta, habría que usar la sesión del guard en vez de la de la petición (cambio del requisito) o montar una petición HTTP real al endpoint de Livewire.

---

## I1 — `/mi-cuenta/seguridad` y Usuarios aceptaban la contraseña del demo

**Cambio**
- `app/Http/Controllers/Publico/SeguridadDeLaCuentaController.php:54` `Rule::notIn([CrearUsuarioDelPanel::CLAVE_PUBLICADA])`; mensaje `password.not_in` en `:63`: «La contraseña nueva no puede ser esa: es pública y cualquiera la conoce.»
- `app/Filament/Resources/Users/Schemas/UserForm.php:57` `->notIn([...])` y `:58-60` `validationMessages(['not_in' => 'Esa es la contraseña del demo, publicada en el repositorio. Elige otra.'])` (misma redacción que la acción de importar).

**Pruebas**
- Caso nuevo `'la publicada del demo'` en `cambiosQueNoSirven` (`SeguridadDeLaCuentaTest.php:146`).
- `PoliticaDeContrasenasTest::test_la_contrasena_publicada_del_demo_no_se_acepta_al_crear` (`:87`) y `..._al_cambiarla` (`:99`, editando a un afiliado: el camino con el que la oficina repone contraseñas). Van en esa clase y no en la lista final porque es la de RF-40; se corrió aparte (ver final).

**ROJO**
```
$PHP artisan test --compact --filter='SeguridadDeLaCuentaTest::test_un_cambio_que_no_sirve|PoliticaDeContrasenasTest::test_la_contrasena_publicada'
{"result":"failed","tests":10,"passed":7,"failures":[
 {"test":"PoliticaDeContrasenasTest::test_la_contrasena_publicada_del_demo_no_se_acepta_al_crear","message":"Component has no errors."},
 {"test":"PoliticaDeContrasenasTest::test_la_contrasena_publicada_del_demo_no_se_acepta_al_cambiarla","message":"Component has no errors."},
 {"test":"SeguridadDeLaCuentaTest::test_un_cambio_que_no_sirve_se_explica_en_espanol_y_no_cambia_nada with data set \"la publicada del demo\"","message":"-'http://localhost:8000/mi-cuenta/seguridad'\n+'http://localhost:8000/mi-cuenta'"}]}
```

**VERDE**: mismo comando → `{"result":"passed","tests":10,"passed":10,"assertions":62}`

**Mutaciones**: claves `password.not_inx` y `not_inx` → las 3 rojas; el portal muestra la clave cruda `validation.not_in`.

**Vecindad**: `SeguridadDeLaCuentaTest|PoliticaDeContrasenasTest|ContrasenaProvisionalDesdeElPanelTest|AccionesDelPanelTest|LimitesDePeticionesTest` → 75/75 (700 aserciones).

**Incidencia honesta**: al restaurar las mutaciones lancé las dos ediciones en el mismo bloque que Pint; chocaron, las ediciones no se aplicaron y una corrida salió 72/75 con esas 3 rojas. Releí, restauré y volví a correr: 75/75, y el `git diff` del commit no lleva ninguna mutación. Desde ahí, ediciones y corridas en pasos separados.

**Desvío**: la redacción de los dos mensajes es mía (el requisito no la fijaba); en el portal evité «demo» y «repositorio», que un afiliado no tiene por qué entender.

---

## I2 — la subida se quedaba en el disco si el modal no llegaba a la acción

**Cambio**
- `app/Console/Commands/DepurarSubidas.php` (nuevo, generado con `make:command` y ajustado a la convención de sus hermanos: `$signature`/`$description` como propiedades): `subidas:depurar {--pretend}` borra de `FileUploadConfiguration::storage()` + `::directory()` (`:38`) lo que tenga `lastModified` de hace más de 60 minutos (`:24`, `:33`), `.json` incluidos, con `exists()` antes de leer la fecha como hace la limpieza de Livewire.
- `routes/console.php:17` `Schedule::command('subidas:depurar')->hourly();` junto a las purgas.

**Pruebas**
- `tests/Feature/DepuracionDeSubidasTest.php` (nueva, `make:test --phpunit`): `test_borra_la_subida_de_hace_mas_de_una_hora_con_su_json` (`:47`), `test_conserva_la_subida_de_menos_de_una_hora` (`:59`, con una vieja al lado para que la pasada sí borre), `test_el_simulacro_no_borra_nada` (`:71`), `test_no_toca_nada_fuera_del_temporal_de_livewire` (`:86`: el temporal vive en el disco privado junto a `formatos/`).
- `CalendarioDeTareasTest`: `subidas:depurar` en `purgas()` (`:42`) y `test_las_subidas_temporales_se_depuran_cada_hora` (`:82`, afirma el campo hora `*`). Docblock de la clase ajustado («las tres purgas» dejó de ser cierto).
- Extremo a extremo con los artefactos reales de Livewire: `ImportarBaseDelGremioTest::test_lo_que_deja_un_error_de_validacion_lo_recoge_la_purga_de_subidas` (`:600`): error de validación → quedan archivos → `travel(61)->minutes()` → `subidas:depurar` → temporal vacío.

**ROJO**
```
$PHP artisan test --compact --filter='DepuracionDeSubidasTest'
{"result":"failed","tests":4,"passed":0,"errors":4,"error_details":[... "message":"The command \"subidas:depurar\" does not exist." ×4]}
$PHP artisan test --compact --filter='CalendarioDeTareasTest'
{"result":"failed","tests":9,"passed":6,"failed":3,"failures":[
 "...esta_programada with data set \"subidas temporales del panel\"": "La purga «subidas:depurar» no está en el calendario de tareas...",
 "...corre_todos_los_dias with data set \"subidas temporales del panel\"": "La purga «subidas:depurar» no está programada.",
 "test_las_subidas_temporales_se_depuran_cada_hora": "La purga «subidas:depurar» no está programada."]}
```
La sonda del revisor (`test_el_archivo_se_queda_si_la_validacion_falla`) sigue mostrando los restos justo después del error —es lo esperado: el arreglo es la purga, no un borrado inmediato—: `Restos en livewire-tmp: [".../o70dR7v...xlsx", ".../o70dR7v...xlsx.json"]`.

**VERDE**: `DepuracionDeSubidasTest` → 4/4 (11 aserciones); `CalendarioDeTareasTest|DepuracionDeSubidasTest` → 13/13; la de extremo a extremo → 1/1 (10 aserciones).

**Mutaciones**
1. Umbral de 24 h (el de Livewire) → `test_borra...` rojo: `Se quedó livewire-tmp/vieja-metaYmFzZS54bHN4-.xlsx.`
2. Saltarse los `.json` → `test_borra...` rojo: `Se quedó ...xlsx.json.`; y la de extremo a extremo roja con el `.json` real de Livewire: `livewire-tmp/Pj30qB5k1wHtzSftswl8fW2Rl3nXLXS2LAbSjMrq.xlsx.json`.
3. `allFiles()` del disco entero → `test_no_toca_nada_fuera...` rojo.
4. Ignorar la fecha → `test_conserva...` rojo: `Se borró livewire-tmp/reciente-...xlsx.`
5. Ignorar `--pretend` → `test_el_simulacro...` rojo.
6. `->daily()` → `test_las_subidas_temporales_se_depuran_cada_hora` rojo: `ya no corre cada hora: «0 0 * * *».`

**Vecindad**: `CalendarioDeTareasTest|DepuracionDeSubidasTest|ImportarBaseDelGremioTest|DepuracionDeMensajesTest|DepuracionDeBolsasTest|DepuracionDeInscripcionesTest` → 62/62.

**Desvíos**
- Sin `activity(...)`: las purgas hermanas escriben la bitácora porque borran registros con plazo publicado; esto borra archivos de paso cada hora, y en `Bitacora` saldría «El sistema eliminó un registro» con un tipo nuevo sin traducir.
- `--pretend` sí, por convención con las hermanas (y probado).

---

## I3 + M3 + M9 + diferidos T3/T5 — el aviso de la importación

**Cambio**
- `app/Services/ResultadoDeAltaDeCuentas.php`: contador aparte `contarYaTenia()` (`:28`) / `yaTenianCuenta(): int` (`:43`); `resumen()` (`:54`) arma tramos «N cuentas creadas · N fichas ya tenían cuenta · N fichas sin cuenta», con singular/plural y omitiendo los tramos en cero.
- `app/Services/AltaDeCuentasDeAfiliados.php:82`: la ficha con cuenta llama `contarYaTenia()` y ya no entra en `sinCuenta()`. **Se retiró la constante `YA_TENIA_CUENTA`**, que quedaba sin uso (`grep` en `app/`, `tests/`, `database/`, `routes/`, `resources/`: solo la usaban el servicio y su prueba).
- `app/Filament/Resources/Asociados/Pages/ListAsociados.php:210` título con `resumenDeFichas()` (`:245`): «Fichas: 61 creadas · 1 actualizada[ · N con problemas].»; `ResultadoDeCargaDeAsociados::resumen()` intacto. `:237` cuerpo `implode('<br>', array_map(e(...), $visibles))`.

**Pruebas**
- `AltaDeCuentasDeAfiliadosTest`: `test_una_ficha_que_ya_tiene_cuenta_no_recibe_otra_y_se_cuenta_aparte` (`:117`, antes `..._no_recibe_otra`; afirma `yaTenianCuenta() === 1` y `sinCuenta() === []`) y `test_el_resumen_nombra_cada_tramo_con_su_numero` (`:212`, proveedor de 5: todas creadas, una creada, ninguna creada y varias sin cuenta, una que ya tenía cuenta, la reimportación 1/35/2).
- `ImportarBaseDelGremioTest` (leen la notificación reconstruida desde la sesión y su HTML pintado —`toHtml()`, ya saneado— sobre el árbol DOM, no sobre cadenas):
  - `test_el_resumen_dice_cada_ficha_sin_cuenta_con_su_motivo` (`:213`, título nuevo y líneas del cuerpo);
  - `test_cada_ficha_sin_cuenta_sale_en_su_propia_linea` (`:244`, I3);
  - `test_el_html_que_trae_la_hoja_sale_como_texto` (`:272`, M3: nombre `Bar <a href="https://x.test">Uno</a>` en una fila rechazada; cero `<a>` en el cuerpo y el texto literal en la línea);
  - `test_el_aviso_muestra_cuarenta_lineas_y_cuenta_las_demas` (`:294`, T5: 41 filas rechazadas → 40 líneas + «…y 1 más.»);
  - `test_reimportar_cuenta_aparte_las_fichas_que_ya_tenian_cuenta` (`:320`, I3: segunda carga → `success` y «Fichas: 0 creadas · 1 actualizada. Cuentas: 0 cuentas creadas · 1 ficha ya tenía cuenta.»);
  - `test_el_titulo_concuerda_con_las_fichas` (`:369`, M9, proveedor: «1 creada · 0 actualizadas», «0 creadas · 2 actualizadas», «0 creadas · 1 actualizada · 1 con problemas»).

**ROJO**
```
$PHP artisan test --compact --filter='AltaDeCuentasDeAfiliadosTest'
{"result":"failed","tests":18,"passed":15,"errors":3,"error_details":[
 "...no_recibe_otra_y_se_cuenta_aparte": "Call to undefined method App\\Services\\ResultadoDeAltaDeCuentas::yaTenianCuenta()",
 "...resumen_nombra_cada_tramo... \"una que ya tenía cuenta\"": "Call to undefined method ...::contarYaTenia()",
 "...\"la reimportación del archivo corregido\"": "Call to undefined method ...::contarYaTenia()"]}

$PHP artisan test --compact --filter='ImportarBaseDelGremioTest'
{"result":"failed","tests":21,"passed":13,"failed":8,"failures":[
 "test_el_resumen_dice_cada_ficha_sin_cuenta_con_su_motivo": "-'Fichas: 2 creadas · 0 actualizadas. ...' +'Fichas: 2 creados · 0 actualizados. ...'",
 "test_cada_ficha_sin_cuenta_sale_en_su_propia_linea": "-0 => '«Bar Dos»: sin correo', 1 => '«Bar Tres»: correo inválido' +0 => '«Bar Dos»: sin correo\\n«Bar Tres»: correo inválido'",
 "test_el_html_que_trae_la_hoja_sale_como_texto": "Failed asserting that 1 is identical to 0.",
 "test_el_aviso_muestra_cuarenta_lineas_y_cuenta_las_demas": "Failed asserting that actual size 1 matches expected size 41.",
 "test_reimportar_cuenta_aparte_las_fichas_que_ya_tenian_cuenta": "-'Fichas: 0 creadas · 1 actualizada. ...' +'Fichas: 0 creados · 1 actualizados. ...'",
 "test_el_titulo_concuerda_con_las_fichas" ×3: "+'Fichas: 1 creados · 0 actualizados.'", "+'Fichas: 0 creados · 2 actualizados.'", "+'Fichas: 0 creados · 1 actualizados · 1 con problemas.'"]}
```
(El ROJO del panel se tomó con el cambio del servicio ya hecho y sin comitear, por eso la de reimportar solo cae por el título; la mutación 5 de abajo la pone roja por el aviso engañoso.) Los casos T3 «todas creadas», «una creada» y «ninguna creada y varias sin cuenta» ya pasaban antes: son cobertura que faltaba, no defecto; sus mutaciones están abajo.

**VERDE**: `AltaDeCuentasDeAfiliadosTest` → 18/18 (42 aserciones); `ImportarBaseDelGremioTest` → 21/21 (174 aserciones).

**Mutaciones**
1. `resumen()` siempre añade el tramo sin cuenta → 3 rojos (`'2 cuentas creadas · 0 fichas sin cuenta.'`…).
2. Singulares nunca aplican → 4 rojos (`'1 cuentas creadas'`, `'1 fichas ya tenían cuenta'`…).
3. Cuerpo con `<br>` pero sin `e()` → solo la de M3 roja (`1 is identical to 0`).
4. «…y N más.» nunca se añade (condición `> 99`) → T5 rojo (`actual size 40 matches expected size 41`); `LINEAS_DEL_RESUMEN = 41` → T5 rojo (la última línea es «Fila 47: «Bar 41»…» en vez de «…y 1 más.»).
5. Servicio de vuelta a `agregarSinCuenta(..., 'ya tenía cuenta')` → rojos `...se_cuenta_aparte` (`0 is identical to 1`) y el de reimportar del panel (`-'success' +'warning'`).
6. Título sin el tramo «con problemas» → rojo el caso «una actualizada y una fila con problemas».

**Vecindad**: `ImportarBaseDelGremioTest|AltaDeCuentasDeAfiliadosTest|ImportacionDeLaBaseDelGremioTest|ImportacionDeAsociadosTest` → 63/63 (274 aserciones).

**Desvío (importante de leer)**: el cuerpo **no** va como `Illuminate\Support\HtmlString`. En Filament 5.8.2 `Filament\Notifications\Concerns\HasBody::body()` declara `string | Closure | null` (no `Htmlable`), y el cuerpo siempre se pinta con `str($body)->sanitizeHtml()` (`Notification.php:409`); la documentación 5.x lo confirma («The body text can contain basic, safe HTML elements»). Un `HtmlString` solo entraría por coerción en modo débil y se volvería cadena igual. Se pasa la cadena HTML ya escapada y unida con `<br>`, que es exactamente la intención del requisito; el saneado conserva `<br>` (`allowSafeElements()`).

---

## M1 + M2 + M5 — el modal y el reporte

**Cambio** (`app/Filament/Resources/Asociados/Pages/ListAsociados.php`)
- M1: `archivo` con `validationMessages` `:92-94` (`required` «Sube el archivo de la base del gremio.», `mimetypes` «El archivo tiene que ser una hoja de Excel (.xlsx).», `max` «El archivo pesa más de 4 MB.»); `categoria` `:100` (`required` «Elige la categoría para las filas que no traen una.»). Filament pasa esos mensajes también al validador anidado de los archivos (`BaseFileUpload.php:757-763`), por eso `mimetypes` y `max` funcionan con la clave corta.
- M2: `application/zip` en `acceptedFileTypes` (`:85`) con el comentario del porqué (`:78-81`).
- M5: `report(self::sinDatosDeLaHoja($error))` (`:151`); `sinDatosDeLaHoja()` (`:187`) devuelve un `RuntimeException` nuevo con clase, código, archivo y línea, **sin** el mensaje original y **sin** encadenar la original como `previous` (el registro la imprimiría entera).

**Pruebas** (`tests/Feature/Panel/ImportarBaseDelGremioTest.php`)
- M5: `test_si_la_importacion_revienta_lo_dice_borra_el_archivo_y_reporta_sin_datos_de_la_hoja` (`:471`, antes `test_si_la_importacion_revienta_lo_dice_y_borra_el_archivo`): la falla simulada lleva `duena@merlin.test` y código 23505; se afirma `Exceptions::assertReportedCount(1)` y `Exceptions::assertReported(fn (RuntimeException $reportada) => ...)`: sin el correo, sin `previous`, con la clase, el código y `archivo:línea` de la original.
- M1: `test_sin_archivo_ni_categoria_el_modal_lo_dice_en_espanol` (`:509`) y `test_un_archivo_que_no_sirve_se_explica_en_espanol` (`:535`, proveedor: PDF → tipo; `.xlsx` de 4097 KB → tamaño).
- M2: `test_una_hoja_que_se_lee_como_zip_tambien_entra` (`:555`, la hoja real reportada como `application/zip` → entra la ficha) y `test_un_zip_que_no_es_una_hoja_lo_reporta_el_importador` (`:570`, un zip de verdad que no es hoja → pasa el tipo, aviso `warning` que empieza por «No se pudo leer el archivo», cero fichas: comprueba la frase del comentario).

**ROJO**
```
$PHP artisan test --compact --filter='ImportarBaseDelGremioTest::(test_si_la_importacion_revienta|test_sin_archivo_ni_categoria|test_un_archivo_que_no_sirve|test_una_hoja_que_se_lee_como_zip|test_un_zip_que_no_es_una_hoja)'
{"result":"failed","tests":6,"passed":0,"failed":6,"failures":[
 "...reporta_sin_datos_de_la_hoja": "The expected [RuntimeException] exception was not reported.",
 "test_sin_archivo_ni_categoria_el_modal_lo_dice_en_espanol": "Failed asserting that an array contains 'Sube el archivo de la base del gremio.'.",
 "test_un_archivo_que_no_sirve... \"no es una hoja\"": "...contains 'El archivo tiene que ser una hoja de Excel (.xlsx).'.",
 "test_un_archivo_que_no_sirve... \"pesa más de 4 MB\"": "...contains 'El archivo pesa más de 4 MB.'.",
 "test_una_hoja_que_se_lee_como_zip_tambien_entra": "Component has errors: \"mountedActions.0.data.archivo\" => [\"validation.mimetypes\"]",
 "test_un_zip_que_no_es_una_hoja_lo_reporta_el_importador": "Component has errors: \"mountedActions.0.data.archivo\" => [\"validation.mimetypes\"]"]}
```
Las claves crudas de M1, medidas antes del arreglo con una sonda fuera del repositorio (`scratchpad/revision/SondaMensajesDelModalTest.php`):
```
SIN NADA: {"mountedActions.0.data.archivo":["validation.required"],"mountedActions.0.data.categoria":["validation.required"]}
PDF: {"mountedActions.0.data.archivo":["validation.mimetypes"]}
GRANDE: {"mountedActions.0.data.archivo":["validation.max.file"]}
```

**VERDE**: `ImportarBaseDelGremioTest` → 26/26 (219 aserciones).

**Mutaciones**: `mimetypesx` → rojo «no es una hoja»; `maxx` → rojo «pesa más de 4 MB»; `requiredx` en `categoria` → rojo (la aserción del archivo pasa y cae la de categoría); `requiredx` en `archivo` → rojo; `new RuntimeException(..., 0, $error)` → rojo (`not reported`); reportar la nueva **y** la original → rojo `The total number of exceptions reported was 2 instead of 1.`

**Vecindad**: `ImportarBaseDelGremioTest|AccionesDelPanelTest` → 40/40.

**Desvío**: redacción de los mensajes, mía. `subida()` de la prueba ahora declara `Illuminate\Http\Testing\File` (lo que de verdad devuelve) para poder llamar `->mimeType()`.

---

## M6 — la prueba de secciones sin la marca pasaba con un 500

**Cambio**: `tests/Feature/SeccionesCerradasConContrasenaProvisionalTest.php:102` `assertLessThan(500, ...)` con mensaje que nombra la ruta, y docblock del porqué.

**Mutación que la pone roja**: en `app/Http/Middleware/ExigirContrasenaPropia.php`, `return $siguiente($request);` → `abort(500);`.
- Con la prueba **vieja**: `$PHP artisan test --compact --filter='SeccionesCerradasConContrasenaProvisionalTest::test_sin_la_marca'` → `{"result":"passed","tests":12,"passed":12,"assertions":12}` (la debilidad, demostrada).
- Con la prueba **endurecida**: → `{"result":"failed","tests":12,"passed":0,"failed":12,...,"message":"«mi-cuenta.proveedores.index» respondió 500.\nFailed asserting that 500 is less than 500."}` (y así las 12).
- Restaurado (`git diff` del middleware vacío) → `SeccionesCerradasConContrasenaProvisionalTest` 28/28.

## M8 — ayuda del campo contraseña al editar

**Cambio**: `app/Filament/Resources/Users/Schemas/UserForm.php:64`: «Déjala en blanco para no cambiarla. Si escribes una para un afiliado, queda provisional: Mi Cuenta le pide cambiarla y le cierra las secciones con datos de terceros hasta que lo haga.»

**Prueba**: `tests/Feature/Panel/ContrasenaProvisionalDesdeElPanelTest.php:136` `test_la_ayuda_de_la_contrasena_avisa_que_la_de_un_afiliado_queda_provisional` (proveedor crear/editar). Lee la ayuda por la API del esquema, no por el HTML: en Filament 5 `helperText()` se guarda como un `Text` en el esquema hijo `Field::BELOW_CONTENT_SCHEMA_KEY`.

**ROJO**: el primer intento cayó por el motivo equivocado (`Method ...TextInput::getHelperText does not exist.`, API de Filament 3); se reescribió la lectura y entonces:
```
$PHP artisan test --compact --filter='ContrasenaProvisionalDesdeElPanelTest::test_la_ayuda'
{"result":"failed","tests":2,"passed":1,"failed":1,"failures":[{"test":"... with data set \"al editar\"","message":"Failed asserting that 'Déjala en blanco para no cambiarla.' [UTF-8](length: 36) contains \"Déjala en blanco para no cambiarla. Si escribes una para un afiliado, queda provisional\""}]}
```
**VERDE**: 2/2 (8 aserciones). **Mutación**: invertir la condición `$operation === 'create'` de la ayuda → las 2 rojas, cada una leyendo el texto de la otra página.

**Vecindad M6+M8**: `ContrasenaProvisionalDesdeElPanelTest|PoliticaDeContrasenasTest|AccionesDelPanelTest|SeccionesCerradasConContrasenaProvisionalTest` → 59/59.

---

## Preocupaciones y cosas para el controlador

1. **C2, puerta del panel sin prueba propia** (ver desvíos de C2): cubierta en producción por el mismo oyente, no observable con `Livewire::test` mientras la guarda sea `request()->hasSession()`.
2. **`ListCarteras::importar` tiene los mismos dos defectos que I3/M3** y quedó fuera por alcance: cuerpo `implode("\n", ...)` con nombres del CSV (saltos perdidos y HTML del archivo pintado) y no borra el temporal tras importar (ahora lo recoge `subidas:depurar` en menos de dos horas).
3. **Expediente**: `docs/ingenieria/runbook-despliegue.md:154-161` dice «`routes/console.php` programa tres purgas diarias» y `material/encargo.md:211` lista la retención: falta `subidas:depurar` cada hora. No lo toqué (es documentación del cierre, Tarea 12). La purga depende del scheduler de producción, que el spec §3.2 midió encendido el 16 sep.
4. **Temporal local con restos reales del defecto**: `storage/app/private/livewire-tmp/` tiene un CSV de cartera del 18 ago (`...-metaY2FydGVyYS1hZ29zdG8uY3N2-.csv`). No lo toqué ni corrí la purga fuera de pruebas; el primer `schedule:run` local se lo llevaría.
5. **Cifras**: `scratchpad/revision/pruebas-enfocadas.txt` (272 pruebas) no es comparable con la corrida de abajo: no sé con qué filtro se tomó. Con el filtro de la lista, hoy: 269; el conteo por clase cuadra (19 clases, suma 269) y las clases que amplié crecieron exactamente en lo añadido (ImportarBaseDelGremioTest 26, AltaDeCuentas 18, Seguridad 20, Calendario 9, ContrasenaProvisionalDesdeElPanel 8; coherente con los informes de tarea). **No corrí la suite completa.**
6. Sondas del revisor sobre la cabeza nueva: fallan las de C2 (`Failed asserting that an array does not have the key 'password_hash_web'.`) e I1 (`La contraseña nueva no puede ser esa...`), o sea, los defectos ya no se reproducen; la de I2 sigue viendo restos justo tras el error (esperado, la purga los recoge); la de C1 sigue pasando (NO TOCAR); la de control pasa.

---

## Corrida final combinada (clases de la lista del requisito)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='ContrasenaProvisionalTest|ImportacionDeAsociadosTest|AltaDeCuentasDeAfiliadosTest|ImportacionDeLaBaseDelGremioTest|ImportarBaseDelGremioTest|SeguridadDeLaCuentaTest|SeccionesCerradasConContrasenaProvisionalTest|AvisoDeContrasenaProvisionalTest|MiCuentaSinCarteraTest|ContrasenaProvisionalDesdeElPanelTest|CrearUsuarioDelPanelTest|InvalidacionDeSesionTest|LimitesDePeticionesTest|LoginDeAsociadoTest|LoginDelPanelTest|AccionesDelPanelTest|CalendarioDeTareasTest|AjustesQueSirvenParaAlgoTest|FormulariosPublicosTest'
```
```json
{"tool":"phpunit","result":"passed","tests":269,"passed":269,"assertions":1404,"duration_ms":163752}
```
(código de salida 1, la rareza conocida)

Conteo por clase de ese filtro (`vendor/bin/phpunit --list-tests`): AccionesDelPanel 14 · AjustesQueSirvenParaAlgo 6 · AltaDeCuentasDeAfiliados 18 · AvisoDeContrasenaProvisional 6 · CalendarioDeTareas 9 · ContrasenaProvisional 5 · CrearUsuarioDelPanel 17 · FormulariosPublicos 38 · ImportacionDeAsociados 19 · ImportacionDeLaBaseDelGremio 5 · InvalidacionDeSesion 2 · LimitesDePeticiones 26 · LoginDeAsociado 8 · LoginDelPanel 11 · MiCuentaSinCartera 3 · Panel\ContrasenaProvisionalDesdeElPanel 8 · Panel\ImportarBaseDelGremio 26 · SeccionesCerradasConContrasenaProvisional 28 · SeguridadDeLaCuenta 20.

Clases tocadas fuera de la lista y vecinas que montan las mismas páginas o comandos:
```
/c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='PoliticaDeContrasenasTest|DepuracionDeSubidasTest|AutorizacionDeBorradoTest|CabecerasDeSeguridadTest|ConfiguracionDeDespliegueTest|ColaDePendientesTest|SolicitudAfiliacionTest|DepuracionDeMensajesTest|DepuracionDeBolsasTest|DepuracionDeInscripcionesTest|PanelCompletoTest|PanelAdminTest|BitacoraTest'
```
```json
{"tool":"phpunit","result":"passed","tests":258,"passed":258,"assertions":673,"duration_ms":169455}
```

Barrido final: `git diff 398cd5c..HEAD` sin `dd(`, `dump(`, `var_dump`, `FUGA`, `ray(` ni restos de mutación (`requiredx`, `not_inx`, `maxx`, `mimetypesx`, `abort(500)`, `if (false)`, `=== -1`, `password_hash_x`): sin coincidencias. 17 archivos, +898 / −55.

## `GIT_OPTIONAL_LOCKS=0 git status --short`

```
?? "docs/ingenieria/entrega-2026-09-04/Socializacion final - ASOBARES Quindio - Juan Jose Sua (11 sep 2026) - opcion A clara.pdf"
?? "docs/ingenieria/entrega-2026-09-04/Socializacion final - ASOBARES Quindio - Juan Jose Sua (11 sep 2026) - opcion B oscura.pdf"
?? "docs/ingenieria/entrega-2026-09-04/Socializacion final - ASOBARES Quindio - Juan Jose Sua (11 sep 2026) - opcion B oscura.pptx"
?? "docs/ingenieria/entrega-2026-09-04/Socializacion final - ASOBARES Quindio - Juan Jose Sua (11 sep 2026).pdf"
?? "docs/ingenieria/entrega-2026-09-04/Socializacion final - ASOBARES Quindio - JuanSua.pptx"
```
