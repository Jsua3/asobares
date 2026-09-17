# Reporte — Tarea 9: Mi Cuenta sin cartera cargada

**Estado: DONE**
**Commit:** `927f22d` — `fix(mi-cuenta): sin cartera cargada el portal deja de decir que el afiliado está al día`

## Qué se implementó

1. **`app/Http/Controllers/Publico/MiCuentaController.php`**
   - `index()`: `$cartera = $asociado->cartera` sin fallback a `new Cartera([...])`. Ahora la vista recibe `?Cartera` de verdad.
   - `pagarMensualidad()`: separado el `if ($cartera === null || $cartera->estaAlDia())` en dos condicionales; sin cartera se redirige con `session('aviso')` explicando que el estado de cuenta no está cargado, en vez de con `session('exito')` mintiendo que ya está al día.
   - `use App\Models\Cartera;` retirado del controlador (ver nota de Pint abajo).

2. **`resources/views/publico/mi-cuenta/index.blade.php`**
   - Nueva rama `@if ($cartera === null)` antes del `@elseif ($cartera->estaAlDia())`, con tarjeta que usa los ajustes `mi_cuenta_sin_cartera_titulo` y `mi_cuenta_sin_cartera_texto`, y el correo de contacto.
   - `@if ($cartera->actualizado_at)` → `@if ($cartera?->actualizado_at)` para no reventar con cartera nula.

3. **`database/seeders/SettingSeeder.php`**
   - Insertadas las dos claves `mi_cuenta_sin_cartera_titulo` y `mi_cuenta_sin_cartera_texto` justo después de `mi_cuenta_aviso_provisional_texto`, con los mismos textos de respaldo que usa la vista.

4. **`tests/Feature/MiCuentaSinCarteraTest.php`** (nuevo, contenido exacto del brief)
   - `test_sin_cartera_no_dice_que_esta_al_dia_ni_ofrece_pagar`
   - `test_con_cartera_al_dia_sigue_diciendo_que_esta_al_dia`
   - `test_pagar_sin_cartera_no_responde_que_esta_al_dia`

5. **`tests/Feature/FormulariosPublicosTest.php`**
   - `test_el_asociado_al_dia_ve_el_estado_sin_deuda` ahora crea una fila `Cartera` real (saldo 0, sin mora, `actualizado_at` hoy) para el «Bar Al Día», porque sin fila ya no se lee como "al día". `use App\Models\Cartera;` ya estaba importado (se usa más arriba en el archivo).

## RED (Paso 2)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=MiCuentaSinCarteraTest
```

Resultado: `{"result":"failed","tests":3,"passed":1,"failed":2}`

- `test_sin_cartera_no_dice_que_esta_al_dia_ni_ofrece_pagar`: falla en `assertDontSeeText('Estás al día')` — la página sí mostraba "Estás al día" porque el controlador fabricaba una cartera con saldo 0. Esperado.
- `test_pagar_sin_cartera_no_responde_que_esta_al_dia`: falla con "Session is missing expected key [aviso]" — llegaba `exito` ("Tu cuenta ya está al día"). Esperado, coincide con lo que anticipaba el brief ("llega `exito`").
- `test_con_cartera_al_dia_sigue_diciendo_que_esta_al_dia` ya pasaba (comportamiento con cartera real no cambia).

Coincide exactamente con lo esperado en el Paso 2 del brief.

## GREEN (Paso 7)

```
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && /c/Users/Predator/.config/php85/php.exe artisan view:clear && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='MiCuentaSinCarteraTest|FormulariosPublicosTest|FlujoDePagoTest|LimitesDePeticionesTest|AjustesQueSirvenParaAlgoTest'
```

Resultado: `{"result":"passed","tests":110,"passed":110,"assertions":827,"duration_ms":60397}` (el comando de Artisan sale con código 1 pese al JSON en verde — es la rareza conocida del entorno).

Confirmado dos veces: una vez tras implementar, y otra vez al final tras restaurar las tres mutaciones (mismos números: 110/110, 827 aserciones).

`vendor/bin/pint --dirty --format agent` → `{"tool":"pint","result":"passed"}` en ambas corridas (antes y después de quitar a mano el import sin uso).

## Mutaciones (Paso 8)

1. **Devolver `?? new Cartera([...])` al controlador** (y su `use` de vuelta, ya que Pint no lo había retirado — ver nota abajo): `MiCuentaSinCarteraTest` → `{"result":"failed","tests":3,"passed":2,"failed":1}`. Rojo en `test_sin_cartera_no_dice_que_esta_al_dia_ni_ofrece_pagar` (vuelve a ver "Estás al día"). Restaurado exacto.

2. **Quitar el bloque `if ($cartera === null)` de `pagarMensualidad`**: `MiCuentaSinCarteraTest` → `{"result":"failed","tests":3,"passed":2,"failed":1}`. Rojo en `test_pagar_sin_cartera_no_responde_que_esta_al_dia` con 500: `Error: Call to a member function estaAlDia() on null`. "Revienta", tal como anticipaba el brief. Restaurado exacto.

3. **Cambiar `@if ($cartera === null)` por `@if (false)`**: `MiCuentaSinCarteraTest` → `{"result":"failed","tests":3,"passed":2,"failed":1}`. Rojo en `test_sin_cartera_no_dice_que_esta_al_dia_ni_ofrece_pagar` con 500: `Error: Call to a member function estaAlDia() on null` en la vista compilada. "Error sobre `null`", tal como anticipaba el brief. Restaurado exacto.

Tras restaurar las tres, `GIT_OPTIONAL_LOCKS=0 git diff` sobre el controlador y la vista mostró exactamente el estado final deseado (sin residuo de ninguna mutación) antes de comitear.

## Archivos modificados

- `app/Http/Controllers/Publico/MiCuentaController.php`
- `resources/views/publico/mi-cuenta/index.blade.php`
- `database/seeders/SettingSeeder.php`
- `tests/Feature/MiCuentaSinCarteraTest.php` (nuevo)
- `tests/Feature/FormulariosPublicosTest.php`

## Autorrevisión

- `GIT_OPTIONAL_LOCKS=0 git diff` de cada archivo comparado línea a línea contra el texto exacto del brief: coincide.
- `git status` antes del commit: solo los 5 archivos de la tarea quedaron en stage; los PDF/PPTX de `docs/ingenieria/entrega-2026-09-04/` siguieron sin rastrear y no se tocaron.
- No se usó `git add -A` ni `git add .`; rutas explícitas.
- Todas las ediciones de PHP/Blade se hicieron con Read/Edit/Write, nunca con sed/python.

## Concerns

- **Pint no retiró el `use App\Models\Cartera;` sin uso.** El brief y el contexto de la tarea asumían que `vendor/bin/pint --dirty --format agent` lo haría en el Paso 7, pero este repositorio no tiene `pint.json` propio (preset por defecto de Laravel), que no incluye la regla `no_unused_imports` de Symfony. Corrí Pint, confirmé que el import seguía ahí y sin uso en el resto del archivo, y lo quité a mano con la herramienta Edit (un solo `use` line) para dejar el controlador sin un import muerto, que es la intención explícita que el propio brief describe ("Si `use App\Models\Cartera;` queda sin uso, Pint lo retira"). El resto del controlador quedó idéntico a lo especificado. Lo marco aquí por transparencia, no porque haya bloqueado nada: no afecta ningún test ni cambia comportamiento.
- Nada más que reportar: RED, GREEN, las tres mutaciones y el commit se comportaron exactamente como anticipaba el brief.
