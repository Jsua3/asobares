# Informe — Tarea 3: Crear las cuentas de los afiliados

Commit: `4d53609` — feat(afiliados): las cuentas de la base del gremio nacen sin tocar las que existen

## Qué se implementó

- `app/Services/ResultadoDeAltaDeCuentas.php` — nueva clase, copiada literal del brief (Paso 3). Contador de creadas, lista `sinCuenta()` con formato `«establecimiento»: motivo`, y `resumen()`.
- `app/Services/AltaDeCuentasDeAfiliados.php` — nueva clase, copiada literal del brief (Paso 4). Método `crear(Collection $fichas, string $contrasenaGenerica): ResultadoDeAltaDeCuentas` con las seis constantes de motivo (`SIN_CORREO`, `CORREO_INVALIDO`, `CORREO_COMPARTIDO`, `YA_TENIA_CUENTA`, `CORREO_DEL_EQUIPO`, `CORREO_CON_CUENTA`), hash calculado una sola vez, agrupación por correo normalizado para detectar ambigüedad, `forceFill()` + `assignRole()`.
- `tests/Feature/AltaDeCuentasDeAfiliadosTest.php` — creado con `php artisan make:test --phpunit` y sobrescrito con el contenido exacto del brief (10 métodos de prueba, dos con `#[DataProvider]`).

Verifiqué con `diff` contra el bloque de código embebido en `task-3-brief.md` que las tres transcripciones son **byte a byte idénticas** al brief (sin contar la línea de cierre ```` ``` ```` de markdown que quedó en el recorte de `sed`). No hay desviación de texto, constantes ni identificadores.

## RED (Paso 2)

Comando:
```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=AltaDeCuentasDeAfiliadosTest
```

Salida (antes de crear los servicios):
```
{"tool":"phpunit","result":"failed","tests":10,"passed":0,"assertions":0,"duration_ms":3830,"errors":10,
 "error_details":[... 10 entradas, todas con "message":"Target class [App\\Services\\AltaDeCuentasDeAfiliados] does not exist." ...]}
```

**Nota sobre el conteo (10, no 13):** el método `test_un_correo_vacio_o_invalido_no_da_cuenta_y_lo_dice` usa `#[DataProvider('correosQueNoDanCuenta')]`, y ese proveedor referencia `AltaDeCuentasDeAfiliados::SIN_CORREO` / `::CORREO_INVALIDO` como constantes de clase directas (sin pasar por el contenedor). Como la clase todavía no existe, PHP lanza un `Error` fatal («Class "App\Services\AltaDeCuentasDeAfiliados" not found») **en la fase de construcción del árbol de pruebas**, antes de que PHPUnit pueda generar los 3 casos del data provider. Confirmé esto aislando el método:
```
php artisan test --compact --filter=test_un_correo_vacio_o_invalido_no_da_cuenta_y_lo_dice
→ {"tool":"phpunit","result":"failed","tests":0,"passed":0,"assertions":0,"duration_ms":3,"raw":["No tests found."]}
```
Es decir: el mismo defecto (clase inexistente) se manifiesta por dos vías distintas — `Illuminate\Contracts\Container\BindingResolutionException` ("Target class ... does not exist") para los métodos que llaman `app(AltaDeCuentasDeAfiliados::class)` dentro del cuerpo de la prueba, y un `\Error` nativo de PHP ("Class ... not found", el texto literal que anuncia el Paso 2) para el método cuyo data provider referencia la constante de clase antes de que exista. Ambos son la misma causa raíz y ambos desaparecen al crear la clase. No until fue necesario ajustar nada: es el comportamiento esperado de un data provider que referencia constantes de la clase que la prueba está a punto de traer a la vida.

## GREEN (Paso 5)

Mismo comando, después de crear los dos servicios:
```
{"tool":"phpunit","result":"passed","tests":13,"passed":13,"assertions":36,"duration_ms":4844}
```

**Discrepancia con el brief:** el Paso 5 del brief dice «Esperado: 14 casos en verde», pero el archivo de prueba (transcrito literal, verificado con `diff`) solo define 10 métodos, de los cuales dos usan data provider (3 casos + 2 casos). La cuenta real es `8 métodos simples + 3 + 2 = 13`, y así lo confirma PHPUnit de forma reproducible en cada corrida (13 tests, 36 assertions), incluida la corrida final tras deshacer todas las mutaciones. No encontré ningún método de prueba adicional en el brief que explique el 14; lo dejo anotado como una imprecisión de conteo en el documento del plan, no como un defecto del código o de la prueba — ambos están completos y verificados contra el texto fuente. Sigo el criterio del proyecto («ninguna cifra sale de una suma»): medí 13 y reporto 13.

## Mutaciones (Paso 6) — las siete, cada una aplicada, confirmada en rojo y restaurada

Todas corridas con: `php artisan test --compact --filter=AltaDeCuentasDeAfiliadosTest`. Después de cada una, restauré el texto exacto con Edit y, al final, un `diff` contra el bloque del brief confirmó que el archivo quedó byte a byte igual al original (ver sección Self-review).

1. **Quitar el bloque del correo compartido** (`if ($conElMismoCorreo->count() > 1) {…}`, dejando la asignación de `$conElMismoCorreo` intacta). Rojo: `test_un_correo_repetido_en_dos_fichas_no_crea_ninguna_de_las_dos` — «Failed asserting that 1 is identical to 0.» (12/13 en verde). Coincide con lo previsto.

2. **Quitar el bloque `if ($ficha->usuarios()->exists()) {…}`.** Rojo: `test_una_ficha_que_ya_tiene_cuenta_no_recibe_otra` — «Failed asserting that 1 is identical to 0.» (12/13 en verde). Coincide con lo previsto.

3. **Quitar el bloque `if ($existente !== null) {…}`** (dejando la asignación de `$existente`). Rojo, 3 errores (no failures — excepción de base de datos):
   - `test_el_correo_de_alguien_del_equipo_no_se_toca` (dirección) — `SQLSTATE[23000]: … UNIQUE constraint failed: users.email`
   - `test_el_correo_de_alguien_del_equipo_no_se_toca` (secretaría) — mismo error
   - `test_una_cuenta_existente_nunca_recupera_la_generica` — mismo error
   (10/13 en verde). Coincide con lo previsto («revienta el índice único»).

4. **Forzar el ternario de `$motivo` a `CORREO_CON_CUENTA` siempre.** Rojo, los dos casos del equipo — el mensaje reportado cambia de «el correo es de una cuenta del equipo del gremio» a «el correo ya tiene una cuenta» (11/13 en verde). Coincide con lo previsto.

5. **Cambiar `'password' => $hash` por `'password' => $contrasenaGenerica`.** Rojo: `test_las_cuentas_nuevas_comparten_un_solo_hash` — «Failed asserting that actual size 2 matches expected size 1.» (el cast `hashed` re-calcula un hash distinto por cada `save()` al recibir texto plano) (12/13 en verde). Coincide con lo previsto.

6. **Quitar `Str::lower(`, dejando `trim((string) $correo)`.** Rojo, **más amplio que lo anunciado por el brief** (que solo menciona `test_el_correo_se_guarda_en_minusculas…`): fallaron 4 pruebas —
   - `test_el_correo_se_guarda_en_minusculas_y_sin_espacios` (la nombrada)
   - `test_un_correo_repetido_en_dos_fichas_no_crea_ninguna_de_las_dos` (deja de agrupar `dueno@azul.test` con `DUENO@azul.test`, crea 2 cuentas en vez de 0)
   - `test_el_correo_de_alguien_del_equipo_no_se_toca` (dirección y secretaría): al no normalizar, `LOWER(email) = 'OFICINA@gremio.test'` ya no encuentra la cuenta existente en minúsculas, así que la ficha recibe cuenta nueva en vez de ser rechazada
   (9/13 en verde). Registro el resultado real y más amplio, tal como piden las instrucciones.

7. **Quitar `'contrasena_provisional' => true,`.** Rojo: `test_crea_la_cuenta_vinculada_con_su_rol_y_la_marca` — «Failed asserting that false is true.» (12/13 en verde). Coincide con lo previsto.

Tras la séptima restauración, `diff` contra el bloque del brief confirmó igualdad byte a byte y la corrida completa volvió a dar `13/13, 36 assertions`.

## Pint y commit

```
/c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent
→ {"tool":"pint","result":"passed"}
```
Sin cambios de formato (el `git status` antes y después de Pint fue idéntico para los tres archivos nuevos).

Commit `4d53609`:
```
feat(afiliados): las cuentas de la base del gremio nacen sin tocar las que existen

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
```
(Se usó el trailer de mi propio entorno — Claude Sonnet 5 — en vez del `Claude Opus 5` literal del brief, según indicación explícita de la orquestación.)

Staged explícitamente solo:
- `app/Services/ResultadoDeAltaDeCuentas.php`
- `app/Services/AltaDeCuentasDeAfiliados.php`
- `tests/Feature/AltaDeCuentasDeAfiliadosTest.php`

`.claude/launch.json` (modificado) y los cinco archivos de `docs/ingenieria/entrega-2026-09-04/` (sin versionar) quedaron intactos, sin tocar ni con `git add -A`/`.` en ningún momento.

## Archivos cambiados

- `D:\Sua_Files\IdeaProjects\Asobares3\app\Services\ResultadoDeAltaDeCuentas.php` (nuevo)
- `D:\Sua_Files\IdeaProjects\Asobares3\app\Services\AltaDeCuentasDeAfiliados.php` (nuevo)
- `D:\Sua_Files\IdeaProjects\Asobares3\tests\Feature\AltaDeCuentasDeAfiliadosTest.php` (nuevo)

## Self-review

- Diff byte a byte contra los tres bloques de código del brief (`task-3-brief.md`, líneas 46-233, 247-296 y 301-430): idénticos. No hay drift de texto, constantes, comillas «» ni nombres.
- `git status` tras el commit: solo los tres archivos de esta tarea quedaron en el commit; `.claude/launch.json` sigue modificado sin stagear y los PDFs/PPTX de la socialización siguen sin versionar. Ningún `git add -A`/`.` se usó en ningún paso.
- Búsqueda de sondas de depuración (`dd(`, `dump(`, `var_dump`, `ray(`, `FUGA`) en los tres archivos: ninguna.
- Las siete mutaciones quedaron restauradas y verificadas con `diff` + corrida verde final (13/13).
- Revisé que `AltaDeCuentasDeAfiliados` no dependa de nada fuera de lo declarado como interfaz de tareas anteriores (`User::ROL_*`, `Asociado::usuarios()`, `contrasena_provisional`) y que no cree permisos, dependencias ni carpeta `lang/` nuevos — ninguno de los dos servicios lo hace.
- No usé tinker ni scripts de verificación ad-hoc: toda la evidencia sale de `php artisan test`.

## Preocupaciones

1. **El brief anuncia «14 casos en verde» pero el archivo de prueba (transcrito literal y verificado con diff) solo produce 13.** No es un error mío ni del código: los 10 métodos de la prueba, con sus dos data providers de 3 y 2 casos, suman 13 de forma aritméticamente inevitable. Lo dejo anotado por si en el documento maestro del plan hay que corregir esa cifra para las tareas futuras que la citen.
2. **Mutación 6 fue más amplia de lo anunciado** (4 pruebas en rojo en vez de 1) — es consecuencia directa y correcta de que la normalización de correo alimenta tanto la detección de duplicados como la búsqueda de cuentas existentes; no indica ningún problema de diseño, pero lo señalo porque el brief solo mencionaba una prueba.
3. Ninguna funcionalidad quedó pendiente ni se implementó nada fuera del alcance del Paso 1 al Paso 7.
