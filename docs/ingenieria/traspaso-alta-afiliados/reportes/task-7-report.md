# Reporte — Tarea 7: Las secciones con datos de terceros se cierran

## Qué se implementó

1. **Middleware `App\Http\Middleware\ExigirContrasenaPropia`** (`app/Http/Middleware/ExigirContrasenaPropia.php`, nuevo): mientras `request()->user()->contrasena_provisional` sea verdadero, redirige a `mi-cuenta.seguridad` con `session('aviso', 'Esa sección se abre cuando cambies tu contraseña provisional por una tuya.')`. Si no, deja pasar la petición. Contenido copiado literal del brief.
2. **Alias `contrasena.propia`** registrado en `bootstrap/app.php`: `use App\Http\Middleware\ExigirContrasenaPropia;` añadido al bloque de `use` (Pint lo reordenó alfabéticamente al formatear, ver más abajo) y la entrada `'contrasena.propia' => ExigirContrasenaPropia::class,` añadida al array de `$middleware->alias([...])`, junto a `'rol.asociado'`.
3. **Grupo cerrado en `routes/web.php`**: el bloque de rutas de proveedores, artistas, aspirantes y toda la bolsa de empleo (vacantes + postulaciones) quedó envuelto en `Route::middleware('contrasena.propia')->group(function (): void { ... });`, anidado dentro del grupo existente `['auth', 'rol.asociado']`. Las rutas de `mi-cuenta.seguridad` (Tarea 6) quedaron **fuera** del grupo nuevo, tal como exige el brief.
4. **Prueba `Tests\Feature\SeccionesCerradasConContrasenaProvisionalTest`** (nueva, generada con `php artisan make:test --phpunit` y sobrescrita con el contenido exacto del brief): 12 rutas cerradas × 2 escenarios (provisional / no provisional) + 3 secciones abiertas + `mi-cuenta.pagar` = 28 casos.

## RED (Paso 2)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=SeccionesCerradasConContrasenaProvisionalTest
```

Resultado (antes de crear el middleware/alias/grupo):

```json
{"tool":"phpunit","result":"failed","tests":28,"passed":16,"assertions":34,"duration_ms":19116,"failed":12,"failures":[ ... ]}
```

Los 12 fallos son exactamente los 12 data sets de `test_con_la_contrasena_provisional_la_seccion_manda_a_seguridad` (proveedores, artistas, banco de talento, mis vacantes, crear vacante, guardar vacante, editar vacante, actualizar vacante, cerrar vacante, reabrir vacante, ver vacante y sus postulaciones, gestionar una postulación). Es lo esperado: sin middleware todavía, cada sección responde 200 (o redirige a otro lado) en vez de mandar a `/mi-cuenta/seguridad`. Los otros 16 casos (la variante "sin la marca", las secciones abiertas y pagar) ya pasaban porque no dependen del cierre.

## GREEN (Paso 6, con la vecindad)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='SeccionesCerradasConContrasenaProvisionalTest|MisVacantesTest|LimitesDePeticionesTest|AccesoDeAsociadosTest'
```

Resultado (después del middleware + alias + grupo):

```json
{"tool":"phpunit","result":"passed","tests":96,"passed":96,"assertions":625,"duration_ms":70188}
```

(El proceso salió con código 1 pese al JSON `"result":"passed"` — es la rareza conocida documentada en el encargo de la tarea; se juzga por el JSON, no por el exit code.)

Se repitió esta misma suite una vez más tras restaurar las tres mutaciones y otra vez después de Pint, con idéntico resultado (`passed`, 96/96, 625 assertions), para confirmar que ninguna mutación quedó a medio revertir y que el reformateo de Pint no cambió comportamiento.

## Mutaciones (Paso 7)

Las tres se aplicaron, se corrió `--filter=SeccionesCerradasConContrasenaProvisionalTest`, se confirmó el rojo esperado, y se restauró el archivo antes de seguir.

1. **Sacar `mi-cuenta.aspirantes.index` del grupo** (la dejé justo encima de `Route::middleware('contrasena.propia')->group(...)`, dentro del grupo `['auth','rol.asociado']` pero fuera del cerrado).
   Resultado: `{"result":"failed","tests":28,"passed":27,"failed":1,...}` — el único fallo es `test_con_la_contrasena_provisional_la_seccion_manda_a_seguridad with data set "banco de talento"` (`Expected response status code [...] but received 200`). Exactamente el caso esperado, ningún otro se movió. Restaurado y confirmado con `git diff` limpio.

2. **Quitar `'contrasena.propia' => ExigirContrasenaPropia::class,` del alias.**
   Resultado: `{"result":"failed","tests":28,"passed":4,"errors":24,...}` — 24 errores, todos `Target class [contrasena.propia] does not exist.`: los 12 data sets de `test_con_la_contrasena_provisional_la_seccion_manda_a_seguridad` y los 12 de `test_sin_la_marca_la_seccion_no_manda_a_seguridad` (ambos actúan sobre rutas ahora sin alias resoluble). Solo sobreviven los 4 casos que no tocan el grupo cerrado (3 secciones abiertas + pagar). Coincide con "rojo toda la prueba (alias inexistente)" del brief. Restaurado.

3. **Cambiar la condición del middleware por `if (false) { ... }`.**
   Resultado: `{"result":"failed","tests":28,"passed":16,"failed":12,...}` — mismos 12 fallos, con los mismos mensajes, que el RED original del Paso 2 (nunca redirige, así que cada sección cerrada responde con su código normal en vez de redirigir a seguridad). Restaurado a `if ($usuario instanceof User && $usuario->contrasena_provisional) {`.

Tras cada restauración se comprobó `GIT_OPTIONAL_LOCKS=0 git diff` contra el estado objetivo; el diff final (antes de Pint) coincidía exactamente con el bloque del Paso 5 y el alias del Paso 4, sin residuo de ninguna mutación.

## Pint

```
/c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent
```

```json
{"tool":"pint","result":"fixed","files":[{"path":"bootstrap\\app.php","fixers":["ordered_imports"]}]}
```

Pint reordenó alfabéticamente el bloque de `use` en `bootstrap/app.php` (el brief pedía insertar `ExigirContrasenaPropia` justo después de `CabecerasDeSeguridad`, es decir antes de `ContarVisitaDelSitio`; Pint lo movió después, que es el orden alfabético correcto y el que exige el estilo del proyecto). No tocó ningún otro archivo. Se volvió a correr la suite del Paso 6 después de Pint: mismo resultado, 96/96 `passed`.

## Archivos modificados

- `app/Http/Middleware/ExigirContrasenaPropia.php` (nuevo)
- `bootstrap/app.php` (modificado: import + alias)
- `routes/web.php` (modificado: grupo cerrado)
- `tests/Feature/SeccionesCerradasConContrasenaProvisionalTest.php` (nuevo)

Commit: `0512314` — `feat(mi-cuenta): con la contraseña provisional se cierran las secciones con datos de terceros`. Solo esos cuatro archivos quedaron en el commit (`git show --stat HEAD`); los PDF/PPTX de la socialización y cualquier otro archivo sin versionar siguieron intactos y sin tocar (confirmado con `git status --porcelain` antes y después).

## Autorrevisión del diff

- Confirmé que la línea siguiente al `});` que cierra el nuevo grupo `contrasena.propia` sigue siendo el `});` que cierra `['auth', 'rol.asociado']` (routes/web.php línea 229 tras el cambio).
- Confirmé que `mi-cuenta.seguridad` y `mi-cuenta.seguridad.actualizar` (Tarea 6) quedaron fuera del grupo nuevo, arriba de él, sin tocar.
- `php -l` sin errores en los cuatro archivos PHP tocados.
- Grep sobre el diff completo del commit en busca de `dd(`, `dump(`, `var_dump`, `print_r`, `Log::debug`, `ray(`, `FUGA`: sin coincidencias.
- El alias nuevo se añadió junto al existente (`rol.asociado`), sin tocar la línea de `rol.asociado`.
- El comentario nuevo sobre el grupo (Paso 5) y el comentario original de "Beneficios detrás de la sesión..." quedaron ambos, el nuevo por fuera explicando el cierre y el original re-indentado dentro del `group()`, palabra por palabra como pide el brief.
- No se creó ninguna carpeta `lang/`, no se añadió ninguna dependencia, no se tocó `composer.json`/`package.json`.
- No se usó `git add -A` ni `git add .`; se listaron las cuatro rutas explícitas.

## Preocupaciones

Ninguna. El brief no dejó ambigüedad y el único punto de decisión (el orden final de los `use` en `bootstrap/app.php` tras la inserción "después de CabecerasDeSeguridad") lo resolvió Pint automáticamente en el Paso 8, como cabía esperar.
