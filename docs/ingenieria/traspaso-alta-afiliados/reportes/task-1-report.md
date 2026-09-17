# Reporte — Tarea 1: La marca de contraseña provisional

## Qué se implementó

1. **Migración** `database/migrations/2026_09_16_171033_anade_contrasena_provisional_a_users.php`
   Añade `users.contrasena_provisional` (`boolean`, `default(false)`), con `down()` que la elimina. Contenido idéntico al del brief (Paso 3), incluido el PHPDoc.

2. **Modelo** `app/Models/User.php`
   - En `casts()`, se añadió `'contrasena_provisional' => 'boolean',` justo después de `'has_email_authentication' => 'boolean',`.
   - Después de `esAsociado()`, se añadió el método `marcarContrasenaProvisionalSiEsAfiliado(): void`, con el PHPDoc del brief, que:
     - descarta la relación `roles` en memoria (`unsetRelation('roles')`) para forzar una consulta fresca del rol vigente;
     - si el usuario no tiene el rol `asociado`, retorna sin hacer nada;
     - si lo tiene, enciende `contrasena_provisional` y guarda.

3. **Prueba** `tests/Feature/ContrasenaProvisionalTest.php`
   Creada con `php artisan make:test --phpunit ContrasenaProvisionalTest --no-interaction` y sobrescrita con el contenido exacto del brief (5 casos: nace sin marca, se marca si es afiliado, no se marca para los dos roles del equipo vía `DataProvider`, y la marca mira el rol vigente y no la relación en memoria).

Ningún archivo fuera de esta lista fue modificado por mí. `.claude/launch.json` (modificado) y los cinco archivos de `docs/ingenieria/entrega-2026-09-04/` (sin versionar) ya estaban así al empezar; no los toqué ni los añadí al commit.

## Evidencia RED (Paso 2, antes de migración y modelo)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=ContrasenaProvisionalTest
```

```json
{"tool":"phpunit","result":"failed","tests":5,"passed":0,"assertions":1,"duration_ms":2233,"failed":1,
"failures":[{"test":"...test_una_cuenta_nace_sin_la_marca","line":28,"message":"Failed asserting that null is false."}],
"errors":4,"error_details":[
 {"test":"...test_marcar_una_cuenta_de_afiliado_la_deja_provisional","line":35,"message":"Call to undefined method App\\Models\\User::marcarContrasenaProvisionalSiEsAfiliado()"},
 {"test":"...test_una_cuenta_del_equipo_no_se_marca with data set \"dirección\"","line":55,"message":"Call to undefined method ...marcarContrasenaProvisionalSiEsAfiliado()"},
 {"test":"...test_una_cuenta_del_equipo_no_se_marca with data set \"secretaría\"","line":55,"message":"Call to undefined method ...marcarContrasenaProvisionalSiEsAfiliado()"},
 {"test":"...test_la_marca_mira_el_rol_vigente_y_no_la_relacion_en_memoria","line":70,"message":"Call to undefined method ...marcarContrasenaProvisionalSiEsAfiliado()"}
]}
```

Por qué se esperaba: la columna `contrasena_provisional` no existía (de ahí `null` en vez de `false`) y el método `marcarContrasenaProvisionalSiEsAfiliado()` no existía todavía. Coincide exactamente con lo previsto en el Paso 2 del brief.

## Evidencia GREEN (Paso 5, después de migración y modelo)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=ContrasenaProvisionalTest
```

```json
{"tool":"phpunit","result":"passed","tests":5,"passed":5,"assertions":5,"duration_ms":2234}
```

5 casos, todos en verde, como pedía el Paso 5.

## Mutaciones (Paso 6) — cada una aplicada, probada y restaurada

**Mutación 1 — quitar `'contrasena_provisional' => 'boolean',` de `casts()`.**
Predicción del brief: rojo solo en `test_marcar_una_cuenta_de_afiliado_la_deja_provisional` ("llega `1`, no `true`").
Resultado real: **rojo en las 5 pruebas**, no solo una:

```json
{"tool":"phpunit","result":"failed","tests":5,"passed":0,"assertions":5,"duration_ms":2215,"failed":5,"failures":[
 {"test":"...test_una_cuenta_nace_sin_la_marca","message":"Failed asserting that 0 is false."},
 {"test":"...test_marcar_una_cuenta_de_afiliado_la_deja_provisional","message":"Failed asserting that 1 is true."},
 {"test":"...test_una_cuenta_del_equipo_no_se_marca with data set \"dirección\"","message":"Failed asserting that 0 is false."},
 {"test":"...test_una_cuenta_del_equipo_no_se_marca with data set \"secretaría\"","message":"Failed asserting that 0 is false."},
 {"test":"...test_la_marca_mira_el_rol_vigente_y_no_la_relacion_en_memoria","message":"Failed asserting that 1 is true."}
]}
```

Razón de la discrepancia: `assertTrue`/`assertFalse` de PHPUnit son estrictos (`===`), así que sin el cast booleano cualquier comparación contra `0`/`1` crudos de SQLite falla, no solo la que el brief señaló. Es un resultado más amplio de lo anunciado, no un contradicho: la línea sigue siendo necesaria y el caso que el brief pedía sí está entre los rojos. Restaurada la línea; verifiqué que `git diff` quedó limpio tras restaurar (ver Autorevisión).

**Mutación 2 — quitar el `if (! $this->hasRole(...)) { return; }`.**
Predicción: rojo en los dos casos de `test_una_cuenta_del_equipo_no_se_marca`.
Resultado real, exacto:

```json
{"tool":"phpunit","result":"failed","tests":5,"passed":3,"assertions":5,"duration_ms":2274,"failed":2,"failures":[
 {"test":"...test_una_cuenta_del_equipo_no_se_marca with data set \"dirección\"","message":"Failed asserting that true is false."},
 {"test":"...test_una_cuenta_del_equipo_no_se_marca with data set \"secretaría\"","message":"Failed asserting that true is false."}
]}
```

Restaurado el guard completo.

**Mutación 3 — quitar `$this->unsetRelation('roles');`.**
Predicción: rojo en `test_la_marca_mira_el_rol_vigente_y_no_la_relacion_en_memoria`.
Resultado real, exacto:

```json
{"tool":"phpunit","result":"failed","tests":5,"passed":4,"assertions":5,"duration_ms":2191,"failed":1,"failures":[
 {"test":"...test_la_marca_mira_el_rol_vigente_y_no_la_relacion_en_memoria","message":"Failed asserting that false is true."}
]}
```

Restaurada la línea.

Después de restaurar las tres mutaciones se corrió la prueba una vez más para confirmar verde: `{"tool":"phpunit","result":"passed","tests":5,"passed":5,"assertions":5,"duration_ms":2192}`.

## Formato y commit

`vendor/bin/pint --dirty --format agent` → `{"tool":"pint","result":"passed"}` (sin cambios que aplicar).

Antes de añadir al índice, revisé `git status` y `git diff app/Models/User.php`: el diff de `User.php` contiene únicamente la línea de cast y el método nuevo, sin restos de ninguna mutación. `.claude/launch.json` seguía modificado (ajeno) y los cinco archivos de `docs/ingenieria/entrega-2026-09-04/` seguían sin versionar (ajenos); no se tocaron.

Se hizo `git add` explícito de los tres archivos (`app/Models/User.php`, la migración, la prueba) y se creó el commit:

```
cb8895f feat(afiliados): la cuenta de un afiliado puede quedar con contraseña provisional
```

`git status` después del commit vuelve a mostrar solo `.claude/launch.json` modificado y los cinco PDF/PPTX sin versionar — igual que al empezar.

## Archivos modificados/creados

- `app/Models/User.php` (modificado)
- `database/migrations/2026_09_16_171033_anade_contrasena_provisional_a_users.php` (nuevo)
- `tests/Feature/ContrasenaProvisionalTest.php` (nuevo)

## Autorrevisión del diff

- `git diff --stat` antes de `add` mostraba solo `.claude/launch.json` (ajeno, 7 líneas) y `app/Models/User.php` (24 inserciones, mías). Coincide con lo esperado.
- El contenido de los tres archivos se comparó línea a línea contra el texto literal del brief: idéntico (incluidos los PHPDoc, el orden de los `use`, y el nombre de cada test).
- `file` y `grep -c $'\r'` confirman que los tres archivos quedaron en LF puro, sin retorno de carro, igual que una migración existente usada como referencia — no hubo normalización accidental de finales de línea.
- El commit quedó con exactamente 3 archivos (`git show --stat HEAD`), sin arrastrar `.claude/launch.json` ni los archivos de `docs/ingenieria/entrega-2026-09-04/`.
- No se ejecutó la suite completa (`php artisan test --compact` sin filtro): la Tarea 1 solo pide correr y filtrar `ContrasenaProvisionalTest`, y la regla de "mínimo número de pruebas" del proyecto respalda no ampliar el alcance. Lo dejo anotado como sugerencia para quien cierre el plan completo (Tarea 12 u otra), no como algo pendiente de esta tarea.

## Preocupaciones

1. **Línea de atribución del commit.** El brief pide literalmente `Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>`. El recordatorio de atribución vigente en esta sesión dice que reemplaza "una copia anterior de este recordatorio" y que solo instrucciones propias del usuario (CLAUDE.md o una regla de memoria) tienen precedencia sobre él — el brief no es ninguna de esas dos cosas, así que usé `Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>` en su lugar. Si el controlador prefiere el texto literal del brief para mantener consistencia entre los commits del plan completo, este commit tendría que enmendarse (no lo hice unilateralmente porque no se puede reescribir historia sin indicación explícita).
2. **Mutación 1 más amplia de lo documentado.** Como se detalla arriba, el brief subestima el alcance de la mutación del cast (predice 1 prueba roja, salieron 5). No afecta el resultado de la tarea, pero si el plan reutiliza esa cifra en el expediente («§4», bitácora, etc.) convendría corregirla ahí porque la medición real no coincide con la anunciada.

Ninguna de las dos preocupaciones bloqueó el trabajo; ambas son informativas para quien retome el plan.
