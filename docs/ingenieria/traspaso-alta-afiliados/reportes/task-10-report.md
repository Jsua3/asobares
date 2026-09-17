# Reporte — Tarea 10: Toda contraseña puesta por otro nace provisional

Estado: **DONE**. Commit `398cd5c` en `afiliados/alta-real`.

## Qué se implementó

Todo lo descrito en el brief, en el orden de sus pasos:

1. **`tests/Feature/Panel/ContrasenaProvisionalDesdeElPanelTest.php`** (nuevo): 6 pruebas — crear afiliado desde el panel lo deja provisional, crear alguien del equipo no, escribirle contraseña a un afiliado existente lo vuelve provisional, editar sin escribir contraseña no lo marca, pasar a afiliado y escribir contraseña en el mismo guardado lo marca, y el filtro de la tabla.
2. **`tests/Feature/CrearUsuarioDelPanelTest.php`**: 5 pruebas añadidas al final de la clase — cuenta de afiliado nace provisional, cuenta del equipo no, y 3 casos con `#[DataProvider('reglasConSuMensaje')]` para "sin símbolos", "sin mayúsculas", "sin números".
3. **`app/Filament/Resources/Users/Pages/CreateUser.php`**: añadido `afterCreate()` que llama a `$usuario->marcarContrasenaProvisionalSiEsAfiliado()`.
4. **`app/Filament/Resources/Users/Pages/EditUser.php`**: añadida propiedad `$seEscribioUnaContrasena`, `mutateFormDataBeforeSave()` que la fija, y `afterSave()` que marca solo si se escribió contraseña.
5. **`app/Filament/Resources/Users/Tables/UsersTable.php`**: columna `IconColumn::make('contrasena_provisional')` después de `asociado.nombre`, y `TernaryFilter::make('contrasena_provisional')` después del filtro de roles.
6. **`app/Filament/Resources/Users/Schemas/UserForm.php`**: texto de ayuda de la contraseña en creación actualizado para mencionar que la cuenta de un afiliado queda provisional.
7. **`app/Console/Commands/CrearUsuarioDelPanel.php`**: `marcarContrasenaProvisionalSiEsAfiliado()` añadido después de `syncRoles()`.

### Paso 7 (mensajes del comando): SÍ fue necesario

El Paso 3 (ver las pruebas nuevas en rojo) probó la hipótesis del brief: **rojo**, no verde. El caso "sin símbolos" de `test_cada_regla_incumplida_se_explica_en_espanol` falló con:

```
Output does not contain "al menos un símbolo".
```

Confirmé la causa raíz leyendo el vendor, no solo infiriéndola:
- `vendor/laravel/framework/src/Illuminate/Validation/Rules/Password.php:367` llama `$validator->addFailure($attribute, 'password.symbols')` (y `password.mixed`, `password.letters`, `password.numbers` en las líneas vecinas).
- `Validator::addFailure()` (vendor `Validator.php:1005`) resuelve el mensaje buscando primero `"{$attribute}.{$rule}"` en los mensajes propios — es decir `clave.password.symbols`, no `clave.symbols`.
- Sin `lang/` y sin coincidencia, el traductor devuelve la clave cruda `validation.password.symbols`, que es justo lo que la prueba `doesntExpectOutputToContain('validation.')` detecta.

Por tanto apliqué el Paso 7 tal cual el brief: las cuatro claves de mensaje pasaron de `clave.mixed/letters/numbers/symbols` a `clave.password.mixed/letters/numbers/symbols`, con el comentario explicativo del brief.

## Evidencia RED (Paso 3)

```json
{"tool":"phpunit","result":"failed","tests":23,"passed":15,"assertions":84,"duration_ms":13978,"failed":8,
"failures":[
 "CrearUsuarioDelPanelTest::test_una_cuenta_de_afiliado_nace_con_la_contrasena_provisional" — Failed asserting that false is true.,
 "test_cada_regla_incumplida_se_explica_en_espanol \"sin símbolos\"" — Output does not contain \"al menos un símbolo\".,
 "test_cada_regla_incumplida_se_explica_en_espanol \"sin mayúsculas\"" — Output does not contain \"una mayúscula y una minúscula\".,
 "test_cada_regla_incumplida_se_explica_en_espanol \"sin números\"" — Output does not contain \"al menos un número\".,
 "ContrasenaProvisionalDesdeElPanelTest::test_crear_un_afiliado_desde_el_panel_lo_deja_provisional" — Failed asserting that false is true.,
 "test_escribirle_una_contrasena_a_un_afiliado_lo_vuelve_provisional" — Failed asserting that false is true.,
 "test_pasar_a_afiliado_y_escribir_contrasena_en_el_mismo_guardado_lo_marca" — Failed asserting that false is true.,
 "test_la_tabla_filtra_a_quien_le_falta_cambiarla" — Failed asserting that a table filter with name [contrasena_provisional] exists ... null is an instance of BaseFilter.
]}
```

Exactamente lo esperado por el brief: rojo en los casos que marcan, en el filtro (no existe) y en `test_una_cuenta_de_afiliado_nace…`; y rojo también (no verde) en el caso de mensajes, confirmando que el Paso 7 hacía falta.

Las pruebas que NO debían cambiar de comportamiento (`test_crear_a_alguien_del_equipo_no_lo_deja_provisional`, `test_editar_un_afiliado_sin_escribir_contrasena_no_lo_marca`, `test_una_cuenta_del_equipo_no_queda_provisional`) ya pasaban en este punto, como se espera de aserciones sobre `false` sin lógica de marcado todavía.

## Evidencia GREEN (Paso 8, con la vecindad)

```json
{"tool":"phpunit","result":"passed","tests":66,"passed":66,"assertions":181,"duration_ms":49439}
```

Filtro: `ContrasenaProvisionalDesdeElPanelTest|CrearUsuarioDelPanelTest|AccionesDelPanelTest|InvalidacionDeSesionTest|PanelAdminTest`. 66/66 verde.

Vuelto a correr después de deshacer las 5 mutaciones (mismo filtro): de nuevo `66/66 passed`.

## Mutaciones (Paso 9)

Cada una se aplicó, se corrió el filtro relevante, se confirmó el rojo exacto (o más amplio, anotado), y se restauró literalmente (confirmado con `git diff` después de las cinco).

1. **Vaciar el cuerpo de `afterCreate()`** → `ContrasenaProvisionalDesdeElPanelTest` (6 pruebas): 1 falla, exactamente `test_crear_un_afiliado_desde_el_panel_lo_deja_provisional` ("Failed asserting that false is true."). Las otras 5 siguieron verdes.
2. **Quitar el `if (! $this->seEscribioUnaContrasena)` en `afterSave()`** → mismo filtro: 1 falla, exactamente `test_editar_un_afiliado_sin_escribir_contrasena_no_lo_marca` ("Failed asserting that true is false."). Las otras 5 siguieron verdes.
3. **Quitar `IconColumn` (columna) y `TernaryFilter` (filtro)** → mismo filtro: 1 falla, exactamente `test_la_tabla_filtra_a_quien_le_falta_cambiarla` ("Failed asserting that a table filter with name [contrasena_provisional] exists ... null is an instance of BaseFilter."). Las otras 5 siguieron verdes.
4. **Quitar la llamada del comando** (`$usuario->marcarContrasenaProvisionalSiEsAfiliado();` en `CrearUsuarioDelPanel::handle()`) → `CrearUsuarioDelPanelTest` (17 pruebas): 1 falla, exactamente `test_una_cuenta_de_afiliado_nace_con_la_contrasena_provisional` ("Failed asserting that false is true."). Las otras 16 siguieron verdes.
5. **Devolver `'clave.password.symbols'` a `'clave.symbols'`** → `test_cada_regla_incumplida_se_explica_en_espanol` (3 casos): 1 falla, exactamente el caso "sin símbolos" ("Output does not contain \"al menos un símbolo\"."). Los casos "sin mayúsculas" y "sin números" (que no dependen de esa clave) siguieron verdes — confirma que la mutación afecta solo la regla `symbols`, como se esperaba.

Ninguna mutación produjo un rojo más amplio de lo previsto; todas coincidieron exactamente con lo anotado en el brief.

Después de restaurar las cinco, `git diff` sobre los cuatro archivos mutados mostró únicamente el diff final esperado (el mismo que quedó comprometido), sin rastro de ninguna mutación.

## Verificación final antes de comprometer

- `php artisan test --compact` con el filtro de la vecindad: `66/66 passed` (repetido tras restaurar mutaciones).
- `vendor/bin/pint --dirty --format agent`: `{"tool":"pint","result":"passed"}` — sin cambios de formato necesarios.
- `git status --short` antes de `git add`: solo los 7 archivos del brief más modificados/nuevo; los PDF/PPTX de `docs/ingenieria/entrega-2026-09-04/` (sin versionar, ajenos a esta tarea) siguen intactos y sin tocar.
- `git add` con rutas explícitas (sin `-A` ni `.`), commit con `git commit -F -` vía heredoc.

## Archivos modificados

- `app/Filament/Resources/Users/Pages/CreateUser.php` (modificado)
- `app/Filament/Resources/Users/Pages/EditUser.php` (modificado)
- `app/Filament/Resources/Users/Tables/UsersTable.php` (modificado)
- `app/Filament/Resources/Users/Schemas/UserForm.php` (modificado)
- `app/Console/Commands/CrearUsuarioDelPanel.php` (modificado)
- `tests/Feature/Panel/ContrasenaProvisionalDesdeElPanelTest.php` (nuevo)
- `tests/Feature/CrearUsuarioDelPanelTest.php` (modificado)

## Autorevisión del diff

- Confirmé versión real instalada: `filament/filament v5.8.2` (vía `vendor/composer/installed.json`), coincide con lo dicho en el encargo de la tarea aunque `CLAUDE.md` (bloque de Boost) diga v4 — es una guía desactualizada, no algo que corregir aquí.
- Verifiqué en `vendor/filament/tables/src/Columns/IconColumn.php` y `vendor/filament/tables/src/Filters/TernaryFilter.php` que todos los métodos usados (`boolean()`, `trueIcon()`, `falseIcon()`, `trueColor()`, `falseColor()`, `sortable()`, y el propio `TernaryFilter::make()`) existen tal cual en Filament 5.8; no hizo falta ningún reemplazo de API. Confirmado también contra `search-docs` (paquete `filament/filament`), que devolvió los mismos ejemplos para la versión 5.x.
- Verifiqué en `vendor/filament/tables/src/Testing/TestsFilters.php` que `filterTable('contrasena_provisional', true)` arma `['value' => true]` para un `TernaryFilter`, coincidiendo con `->boolean()` (activado por defecto en `TernaryFilter::setUp()`) usado por la columna del formulario del filtro.
- No añadí ni quité permisos, dependencias, ni carpeta `lang/`.
- No toqué `docs/ingenieria/entrega-2026-09-04/*` ni `.claude/launch.json`.
- Los imports de `App\Models\User` en `CreateUser.php` y `EditUser.php` se usan en las anotaciones `/** @var User $usuario */`; no quedan imports sin usar en ningún archivo tocado (revisé cada uno).
- El comentario nuevo en `CrearUsuarioDelPanel.php` explica una decisión no obvia (por qué `clave.password.symbols` y no `clave.symbols`), consistente con "Prefer PHPDoc/comentarios solo para lógica no obvia" y con la política de comentarios del proyecto (el porqué en presente, sin bitácora de sesión).
- No creé ningún archivo de documentación no solicitado.
- Mensaje de commit en español, con el trailer `Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>` (el que indica este entorno) en vez del `Claude Opus 5` literal del brief; mantuve el párrafo del cuerpo del brief porque el Paso 7 sí se aplicó.

## Preocupaciones

Ninguna. El único punto que merece mención (no es un problema) es que `CLAUDE.md` declara Filament v4 en su bloque de Boost mientras el proyecto corre v5.8.2 — ya lo tenía anotado la propia sesión en `material/` según la memoria del usuario ("Stack Laravel 13 + Filament 5"), así que no es una sorpresa ni algo que esta tarea deba corregir.
