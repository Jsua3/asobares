# Reporte — Tarea 8: El aviso tocable y el botón «Seguridad»

## Qué se implementó

1. **Componente nuevo** `resources/views/components/publico/mi-cuenta/aviso-contrasena.blade.php`: mientras `auth()->user()?->contrasena_provisional` sea verdadero, pinta un `<a href="{{ route('mi-cuenta.seguridad') }}">` cuya superficie ENTERA (icono + título + texto) es el enlace, con `min-h-11` para el objetivo táctil de 44 px. Título y texto salen de `ajuste('mi_cuenta_aviso_provisional_titulo', …)` y `ajuste('mi_cuenta_aviso_provisional_texto', …)`. Usa los tokens semánticos `border-aviso-linea`, `bg-aviso-fondo`, `text-aviso-suave` (mismos que `x-publico.alerta` tipo="aviso"), sin ninguna clase de color cableada.
2. **`resources/views/publico/mi-cuenta/index.blade.php`**: monta `<x-publico.mi-cuenta.aviso-contrasena class="mt-6" />` justo después de `</header>`; añade el botón «Seguridad» (`x-publico.boton variante="contorno" :href="route('mi-cuenta.seguridad')"`) al final del `<nav>`; añade el bloque `@if (session('aviso')) <x-publico.alerta tipo="aviso" …>` después del bloque de `session('error')`.
3. **`resources/views/publico/mi-cuenta/fotos/index.blade.php`**: monta el mismo componente después de `</header>`, antes de `@if (session('exito'))`.
4. **`database/seeders/SettingSeeder.php`**: añadidas las dos claves `mi_cuenta_aviso_provisional_titulo` (texto) y `mi_cuenta_aviso_provisional_texto` (largo), justo después de `mi_cuenta_seguridad_provisional_texto` (ancla de la Tarea 6).
5. **Prueba nueva** `tests/Feature/AvisoDeContrasenaProvisionalTest.php`, contenido exacto del brief: 6 casos (2 con `DataProvider('paginasConAviso')` para "con la marca", 2 para "sin la marca", 1 para el botón de seguridad, 1 para el aviso de sesión).

Ningún archivo fuera de esta lista se tocó. Los PDFs/PPTX sin versionar en `docs/ingenieria/entrega-2026-09-04/` no se tocaron ni se añadieron al índice.

## RED (Paso 2, antes de tocar el componente/vistas/seeder)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=AvisoDeContrasenaProvisionalTest
```

```json
{"tool":"phpunit","result":"failed","tests":6,"passed":2,"assertions":11,"duration_ms":3623,"failed":4,"failures":[
 {"test":"...test_con_la_marca_el_aviso_entero_lleva_a_seguridad with data set \"mi cuenta\"", "message":"Failed asserting that 0 is identical to 1."},
 {"test":"...test_con_la_marca_el_aviso_entero_lleva_a_seguridad with data set \"mis fotos\"", "message":"Failed asserting that 0 is identical to 1."},
 {"test":"...test_mi_cuenta_ofrece_la_seguridad_aunque_no_haya_marca", "message":"Failed asserting that 0 is identical to 1."},
 {"test":"...test_mi_cuenta_pinta_el_aviso_que_llega_en_la_sesion", "message":"Failed asserting that '<!DOCTYPE html>...' contains \"Mensaje de aviso de prueba\""}
]}
```

Coincide exactamente con lo previsto en el brief: fallan los dos casos "con la marca", el botón de seguridad y el aviso de sesión; "sin la marca no hay aviso" sale verde (2 passed) porque es la contraprueba — no hay nada que ver todavía.

## GREEN (Paso 6, tras componente + vistas + seeder)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan view:clear && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='AvisoDeContrasenaProvisionalTest|AjustesQueSirvenParaAlgoTest|FocoVisibleTest|ObjetivoTactilTest|TemaClaroOscuroTest|FormulariosPublicosTest|MisFotosTest'
```

```json
{"tool":"phpunit","result":"passed","tests":123,"passed":123,"assertions":410,"duration_ms":75317}
```

El comando salió con código de proceso 1 (el `artisan test` de este entorno lo hace incluso en verde, según el contexto de la tarea), pero el JSON dice `"result":"passed"` con 123/123. Confirmado explícitamente comprobando `$?` por separado: `EXIT:1` junto al mismo JSON en verde. Ninguna de las guardias de vistas (`FocoVisibleTest`, `ObjetivoTactilTest`, `TemaClaroOscuroTest`) puso objeción al componente nuevo ni a los cambios de vista: no hizo falta tocar nada fuera del brief.

Se repitió la suite completa una vez más tras deshacer las cinco mutaciones (ver abajo), con el mismo resultado: `{"tool":"phpunit","result":"passed","tests":123,"passed":123,"assertions":410,"duration_ms":76862}`.

## Mutaciones (Paso 7) — las cinco del brief, cada una restaurada de inmediato

1. **Quitar el componente de `fotos/index`.** Editado: se quitó la línea `<x-publico.mi-cuenta.aviso-contrasena class="mt-6" />` de `fotos/index.blade.php`.
   Resultado: `{"result":"failed","tests":6,"passed":5,"failed":1,"failures":[{"test":"...test_con_la_marca_el_aviso_entero_lleva_a_seguridad with data set \"mis fotos\"","message":"Failed asserting that 0 is identical to 1."}]}`
   Exactamente el caso "mis fotos" rojo, como predecía el brief. Restaurado.

2. **`@if (auth()->user()?->contrasena_provisional)` → `@if (true)`.**
   Resultado: `{"result":"failed","tests":6,"passed":4,"failed":2,"failures":[{"test":"...test_sin_la_marca_no_hay_aviso with data set \"mi cuenta\"","message":"Failed asserting that 1 is identical to 0."},{"test":"...test_sin_la_marca_no_hay_aviso with data set \"mis fotos\"","message":"Failed asserting that 1 is identical to 0."}]}`
   Los dos casos de la contraprueba en rojo, como predecía el brief. Restaurado.

3. **Quitar `min-h-11` del componente.**
   Resultado: `{"result":"failed","tests":6,"passed":4,"failed":2,"failures":[{"test":"...test_con_la_marca_el_aviso_entero_lleva_a_seguridad with data set \"mi cuenta\"","message":"El aviso necesita 44 px de objetivo táctil.\nFailed asserting that '...' contains \"min-h-11\"."},{"test":"...\"mis fotos\"","message":"(mismo mensaje)"}]}`
   Rojo por el objetivo táctil en los dos casos, como predecía el brief. Restaurado.

4. **Quitar el botón «Seguridad» del `<nav>`.**
   Resultado: `{"result":"failed","tests":6,"passed":5,"failed":1,"failures":[{"test":"...test_mi_cuenta_ofrece_la_seguridad_aunque_no_haya_marca","message":"Failed asserting that 0 is identical to 1."}]}`
   Restaurado.

5. **Quitar el bloque `@if (session('aviso'))` de `index`.**
   Resultado: `{"result":"failed","tests":6,"passed":5,"failed":1,"failures":[{"test":"...test_mi_cuenta_pinta_el_aviso_que_llega_en_la_sesion","message":"Failed asserting that '<!DOCTYPE html>...' contains \"Mensaje de aviso de prueba\""}]}`
   Restaurado.

Las cinco mutaciones dieron rojo exactamente donde el brief anticipaba, ni más ni menos casos de los previstos. Tras restaurar cada una, `GIT_OPTIONAL_LOCKS=0 git diff` (contra HEAD, antes de comitear) mostró solo los cambios de la Tarea 8 —ninguna mutación quedó viva—, y el componente nuevo (archivo sin versionar hasta el commit) se releyó íntegro y coincide con el bloque del Paso 3 del brief carácter por carácter.

## Pint

```
/c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent
```
`{"tool":"pint","result":"passed"}` — sin cambios de formato que aplicar.

## Archivos modificados/creados

- `resources/views/components/publico/mi-cuenta/aviso-contrasena.blade.php` (nuevo)
- `resources/views/publico/mi-cuenta/index.blade.php` (modificado: aviso, botón «Seguridad», alerta de sesión)
- `resources/views/publico/mi-cuenta/fotos/index.blade.php` (modificado: aviso)
- `database/seeders/SettingSeeder.php` (modificado: dos claves nuevas)
- `tests/Feature/AvisoDeContrasenaProvisionalTest.php` (nuevo)

## Commit

```
c441099 feat(mi-cuenta): el aviso de contraseña provisional lleva a seguridad con un toque
```
5 files changed, 129 insertions(+). Sin `docs/ingenieria/entrega-2026-09-04/*` ni ningún otro archivo ajeno en el índice (`git status` tras el commit solo lista esos PDFs/PPTX como untracked, intactos).

## Autorrevisión del diff

- Las tres sustituciones de `index.blade.php` y la de `fotos/index.blade.php` son literales al brief (comparadas carácter por carácter contra el Paso 4).
- El componente es literal al Paso 3 del brief, incluyendo el comentario Blade explicando por qué el aviso entero es el enlace y por qué no lleva `role="status"`.
- Las dos líneas del seeder son literales al Paso 5, insertadas exactamente después del ancla de la Tarea 6 (`mi_cuenta_seguridad_provisional_texto`), antes de `mi_cuenta_convenios_titulo`.
- Verifiqué antes de escribir que los tres anchors (`</header>` + `<nav...`, el botón «Banco de talento» + `</nav>`, el bloque `@if (session('error'))`) existían exactamente una vez en `index.blade.php`, y que `</header>` + `@if (session('exito'))` existía exactamente una vez en `fotos/index.blade.php`. Las ediciones no tuvieron ambigüedad.
- Confirmé que `x-publico.alerta` ya soporta `tipo="aviso"` con los mismos tokens semánticos (`border-aviso-linea bg-aviso-fondo text-aviso-suave`) antes de usarlos en el componente nuevo, así que no inventé clases nuevas.
- Confirmé que `mi-cuenta.index` y `mi-cuenta.fotos.index` NO están detrás del middleware `contrasena.propia` en `routes/web.php` (quedan fuera de ese grupo a propósito), y que `mi-cuenta.seguridad` tampoco lo está — coincide con la descripción del brief de "páginas que siguen abiertas con la marca".
- Confirmé el valor por defecto de `contrasena_provisional` (`false` en la migración) para descartar que las pruebas existentes de `FormulariosPublicosTest`/`MisFotosTest`/`TemaClaroOscuroTest` que no fijan el campo explícitamente se vieran afectadas por el aviso nuevo; ninguna lo hace y la suite completa lo confirmó en verde.
- No fue necesario tocar ninguna guardia (`FocoVisibleTest`, `ObjetivoTactilTest`, `TemaClaroOscuroTest`) ni tocar CSS/Vite: pasaron a la primera con el componente tal como lo especifica el brief.
- `git diff` final antes de comitear solo mostraba las líneas de la Tarea 8; no quedó ninguna mutación a medio restaurar.

## Concerns

Ninguno. El alcance quedó exactamente dentro de lo que pedía la Tarea 8; no hubo ambigüedad ni contradicción entre el brief y el código existente, y ninguna guardia de vistas exigió un cambio de intención en el componente.
