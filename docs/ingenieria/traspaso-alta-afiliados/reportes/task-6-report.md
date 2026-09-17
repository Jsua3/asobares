# Task 6 — La pantalla `/mi-cuenta/seguridad` — Reporte

## Estado

DONE.

## Qué se implementó

- **Controlador** `app/Http/Controllers/Publico/SeguridadDeLaCuentaController.php` (nuevo, sin `extends Controller`, igual que sus vecinos de `Publico`): `editar()` pinta la vista con el usuario autenticado; `actualizar()` valida `current_password`/`password`/`password_confirmation`, asigna `password`, `remember_token` y `contrasena_provisional = false` sueltos (nunca por `update()`, porque `#[Fillable]` descartaría la marca en silencio), regenera la sesión, registra `activity('sesion')->log('cambió su contraseña')` y redirige a `mi-cuenta.index` con `exito`.
- **Vista** `resources/views/publico/mi-cuenta/seguridad.blade.php` (nueva): formulario PUT con los tres campos de contraseña, mensaje condicional según `contrasena_provisional`, y aviso de sesión.
- **Rutas** (`routes/web.php`): import de `SeguridadDeLaCuentaController` en orden alfabético tras `ProveedorController`; `GET /mi-cuenta/seguridad` → `mi-cuenta.seguridad`; `PUT /mi-cuenta/seguridad` (con `throttle:mi-cuenta-seguridad`) → `mi-cuenta.seguridad.actualizar`; ambas dentro del grupo `['auth', 'rol.asociado']`, justo después de `mi-cuenta.fotos.destroy`.
- **Limitador** (`app/Providers/AppServiceProvider.php`): `'mi-cuenta-seguridad' => 5` en `LIMITES_POR_MINUTO`, tras `'mi-cuenta-contrasena'`.
- **`tests/Feature/LimitesDePeticionesTest.php`**: nueva fila en `rutasLimitadas()`: `'cambiar la contraseña con sesión' => ['PUT', 'mi-cuenta.seguridad.actualizar', [], 5, true]`.
- **Ajustes** (`database/seeders/SettingSeeder.php`): `mi_cuenta_seguridad_titulo`, `mi_cuenta_seguridad_texto`, `mi_cuenta_seguridad_provisional_texto`, tras `mi_cuenta_pago_ayuda`.
- **Prueba** `tests/Feature/SeguridadDeLaCuentaTest.php` (nueva, 17 casos incluyendo el `DataProvider` de 7 combinaciones): generada con `php artisan make:test --phpunit SeguridadDeLaCuentaTest --no-interaction` y sobrescrita con el contenido exacto del brief.

Todo el contenido de cada archivo se copió literalmente del brief (identificadores, textos y mensajes verbatim); no se necesitó desviarse de lo especificado en ningún punto.

## RED (Paso 2)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=SeguridadDeLaCuentaTest
```

Resultado: 17 pruebas, 0 pasadas, 17 errores, todas con `"Route [mi-cuenta.seguridad] not defined."` (las dos últimas con `mi-cuenta.seguridad.actualizar`). Exactamente lo esperado por el Paso 2 del brief, antes de crear el controlador y las rutas.

## GREEN (Paso 8)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='SeguridadDeLaCuentaTest|LimitesDePeticionesTest|AjustesQueSirvenParaAlgoTest|InvalidacionDeSesionTest'
```

Resultado:
```
{"tool":"phpunit","result":"passed","tests":51,"passed":51,"assertions":618,"duration_ms":40225}
```

51/51 pruebas, 618 aserciones, verde. Repetido una segunda vez tras cerrar el ciclo de mutaciones (Paso 9) con el mismo resultado exacto (51/51, 618 aserciones), confirmando que no quedó nada roto.

Nota sobre el código de salida: el comando `artisan test` devuelve código de salida 1 aunque el JSON reporte `"result":"passed"`. Verificado que es un comportamiento preexistente del entorno y no algo introducido por esta tarea: corriendo solo `InvalidacionDeSesionTest` (archivo que esta tarea no toca) en aislamiento se obtiene el mismo patrón —JSON en verde, código de salida 1—. La evidencia de verde/rojo de esta tarea se basa en el JSON de resumen, tal como indican las instrucciones del entorno.

## Mutaciones (Paso 9)

Las siete, en orden, cada una: mutada → confirmado un único test rojo con el mensaje esperado → restaurada exactamente → confirmado que el diff vuelve a estar limpio.

1. **`'password.password.symbols'` → `'password.symbols.x'`.** Rojo: `test_un_cambio_que_no_sirve_se_explica_en_espanol_y_no_cambia_nada` con data set "sin símbolo". Mensaje: `Failed asserting that an array contains 'La contraseña nueva necesita al menos un símbolo.'` con la clave cruda `validation.password.symbols` filtrándose en el error real. Solo ese caso del data provider cayó (16/17 pasaron).
2. **`nombre="password"` → `nombre="contrasena"` en la vista.** Rojo: `test_la_pantalla_pide_la_actual_la_nueva_y_su_confirmacion` — `Falta el campo password. Failed asserting that 0 is identical to 1.`
3. **Quitar `$usuario->contrasena_provisional = false;`.** Rojo: `test_cambiarla_guarda_la_nueva_y_apaga_la_marca` — `Failed asserting that true is false.`
4. **Quitar `$usuario->password = $datos['password'];`.** Rojos (2, como predice el brief): `test_cambiarla_guarda_la_nueva_y_apaga_la_marca` (`Failed asserting that false is true.`) y `test_una_sesion_abierta_con_la_contrasena_vieja_se_cierra` (`Expected response status code [...] but received 200.`).
5. **Quitar el bloque `activity('sesion')…`.** Rojo: `test_la_bitacora_anota_el_cambio_sin_la_contrasena` — `Failed asserting that null is not null.`
6. **Quitar `'different:current_password'`.** Rojo: `test_un_cambio_que_no_sirve_se_explica_en_espanol_y_no_cambia_nada` con data set "igual a la actual" — el cambio con la misma contraseña ahora tiene éxito y redirige a `/mi-cuenta` en vez de quedarse en `/mi-cuenta/seguridad` con el error.
7. **Quitar `->middleware('throttle:mi-cuenta-seguridad')` de la ruta PUT.** Rojo: `LimitesDePeticionesTest::test_cada_ruta_limitada_conserva_su_propio_maximo` con data set "cambiar la contraseña con sesión" — `La petición 6 ... debía rebotar con 429. Failed asserting that 302 is identical to 429.`

Cada mutación produjo exactamente el rojo puntual que predice el brief (ningún otro test se vio afectado en ninguna de las siete), y cada restauración se verificó re-leyendo el archivo completo o con `git diff` antes de seguir.

## Pint

```
/c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent
```
Resultado: `{"tool":"pint","result":"passed"}` — sin cambios de formato necesarios.

## Archivos modificados

- `app/Http/Controllers/Publico/SeguridadDeLaCuentaController.php` (nuevo)
- `resources/views/publico/mi-cuenta/seguridad.blade.php` (nuevo)
- `tests/Feature/SeguridadDeLaCuentaTest.php` (nuevo)
- `routes/web.php` (modificado)
- `app/Providers/AppServiceProvider.php` (modificado)
- `database/seeders/SettingSeeder.php` (modificado)
- `tests/Feature/LimitesDePeticionesTest.php` (modificado)

Ningún otro archivo tocado. Los PDFs/PPTX sin versionar de `docs/ingenieria/entrega-2026-09-04/` no se tocaron ni se incluyeron en el `git add` (se usaron rutas explícitas, nunca `-A` ni `.`).

## Commit

```
358a991 feat(mi-cuenta): el afiliado cambia su contraseña y las sesiones viejas se cierran
```
7 files changed, 383 insertions(+).

## Autorrevisión

- Verifiqué antes de empezar (lectura de `routes/web.php`, `AppServiceProvider.php`, `SettingSeeder.php`, `LimitesDePeticionesTest.php`, `User.php`, `InvalidacionDeSesionTest.php`, los controladores vecinos de `Publico`, los componentes `x-publico.campo/boton/alerta/flecha`, `x-layouts.publico`, `AsegurarRolAsociado`, `bootstrap/app.php` y `AjustesQueSirvenParaAlgoTest`) que cada supuesto del brief coincidía con el código real: convención "sin `extends Controller`" en `Publico`, comportamiento de `AuthenticateSession`/`redirectGuestsTo`, forma exacta de las claves de mensaje de la regla `Password` (confirmé leyendo `vendor/laravel/framework/.../Rules/Password.php` y `FormatsMessages::getFromLocalArray` que `password.password.symbols` y `password.symbols` son ambas válidas, tal como dice el brief), props de `x-publico.campo` (`tipo="password"` cae en el `<input>` genérico, no hay caso especial), y que `Asociado::factory()->publicado()`, `RolYPermisoSeeder` y `Spatie\Activitylog\Models\Activity` existen tal como se referencian.
- Cada archivo se comparó línea por línea contra el texto del brief después de escribirlo (releído completo tras el ciclo de mutaciones).
- `git diff` final revisado antes del commit: solo los 7 archivos esperados, sin restos de ninguna mutación.
- No se generó ninguna cuenta real, no se tocó ningún dato de `material/nuevomaterial/`, no se usaron correos/nombres reales (el test usa `duena@merlin.test`, dominio `.test`).
- No se ejecutó `composer`, `npm` ni ningún comando de `cloud`. La vista no añade ninguna entrada nueva a Vite (usa los mismos `resources/css/app.css`/`resources/js/app.js` que ya sirve el layout), así que no hubo problema de manifiesto.
- No se tocó ningún archivo fuera del alcance de la Tarea 6; no se rebasó ni se hizo pull; no se cambió de rama.

## Preocupaciones

- El código de salida 1 de `artisan test` con JSON en verde es un comportamiento preexistente del entorno (reproducido con un test que esta tarea no modifica), documentado aquí por si en una tarea futura alguien lo confunde con un fallo real: el JSON `"result":"passed"` es la fuente de verdad, no el código de salida del proceso.
- No se ejecutó la suite completa del proyecto (solo los cuatro archivos que pide el Paso 8): el brief no lo pide para esta tarea y las instrucciones de la tarea piden "el mínimo de pruebas necesario". Si se quiere esa confirmación adicional antes de fusionar (Tarea 12), queda pendiente.
