# Arreglos de la revisión final — rama `afiliados/alta-real` (base `bbe9b63`, cabeza `398cd5c`)

La revisión final completa está resumida aquí con la decisión del controlador sobre cada hallazgo. **Arregla todos los que dicen «ARREGLAR», en un solo lote.** Los que dicen «NO TOCAR» quedan fuera a propósito.

Material de apoyo:
- Spec: `C:/Users/Predator/AppData/Local/Temp/claude/D--Sua-Files-IdeaProjects-Asobares3/bc09f505-4e1a-4dbc-a814-8505db5067e2/scratchpad/spec-alta-de-afiliados-reales.md`
- Plan (Global Constraints obligatorias): `C:/Users/Predator/AppData/Local/Temp/claude/D--Sua-Files-IdeaProjects-Asobares3/bc09f505-4e1a-4dbc-a814-8505db5067e2/scratchpad/plan-alta-de-afiliados-reales.md`
- Pruebas de sondeo del revisor, que ya reproducen C2, I1, I2, I3 y M1 (fuera del repo, pasan hoy sobre `398cd5c` demostrando los defectos): `C:\Users\Predator\AppData\Local\Temp\claude\D--Sua-Files-IdeaProjects-Asobares3\bc09f505-4e1a-4dbc-a814-8505db5067e2\scratchpad\revision\RevisionAltaDeAfiliadosTest.php`. Úsalas para escribir las pruebas de regresión **dentro** de `tests/` (no copies el archivo tal cual; conviértelo en pruebas que fallen con el defecto y pasen con el arreglo).

## NO TOCAR

- **C1 (spec) — la contraseña genérica permite tomar una cuenta ajena.** Es una decisión de producto de Sua (D1); se le pregunta aparte. No cambies el diseño de la contraseña genérica ni el flujo de la acción de importar más allá de lo que piden I3, M1, M2 y M5.
- **M4 — rotar `remember_token` apaga el «recordarme» del propio dispositivo.** Ruling: se queda; el dueño vuelve a entrar una vez.
- **M7 — la política de contraseñas vive en cinco sitios.** Ruling: trabajo aparte; no extraigas un objeto de política en este lote.

## ARREGLAR

### C2 — una sesión abierta con la contraseña vieja sobrevive al cambio si no hizo una segunda petición (Critical, demostrado)

- **Causa:** `AuthenticateSession::handle` sale temprano para invitados (incluido el POST de entrada), así que nadie guarda `password_hash_{guard}` en la sesión al iniciar sesión. Lo guarda en la siguiente petición de esa sesión con el hash que haya entonces. Un script que entra y no sigue la redirección espera a que el dueño cambie la contraseña y obtiene acceso completo con la marca apagada.
- **Arreglo esperado:** en el listener existente de `Illuminate\Auth\Events\Login` (`App\Providers\AppServiceProvider::registrarBitacoraDeSesiones`), cuando la petición tenga sesión, guardar `password_hash_{guard}` con el mismo formato que usa `AuthenticateSession` (`Auth::guard($evento->guard)->hashPasswordForCookie($usuario->getAuthPassword())`, con el mismo `try/catch BadMethodCallException` de respaldo que usa el middleware). Así cubre todas las puertas de entrada (portal y panel).
- **Prueba de regresión:** entrar por `POST mi-cuenta.entrar.post` **sin** hacer ninguna otra petición con esa sesión, guardar esa sesión, cambiar la contraseña (como el dueño desde otra sesión o actualizando el modelo), `forgetGuards()`, volver con la sesión guardada → tiene que ir a `mi-cuenta.entrar`. Verla ROJA sin el arreglo.

### I1 — `/mi-cuenta/seguridad` acepta la contraseña del demo (Important, demostrado)

- `Asobares2026*` cumple la política y se acepta. La Global Constraint dice que nunca se acepta `CrearUsuarioDelPanel::CLAVE_PUBLICADA`.
- **Arreglo:** en `SeguridadDeLaCuentaController::actualizar`, `Rule::notIn([CrearUsuarioDelPanel::CLAVE_PUBLICADA])` sobre `password`, con mensaje en español (clave `password.not_in`), y un caso nuevo en `cambiosQueNoSirven` de `SeguridadDeLaCuentaTest`. Aplica lo mismo al campo `password` de `app/Filament/Resources/Users/Schemas/UserForm.php` (mismo hueco), con su mensaje y prueba.

### I2 — el archivo subido se queda en el disco si el modal se cierra tras un error de validación o se cancela (Important, demostrado)

- El `finally` de la acción solo corre si la validación pasó. El `.xlsx` y su `.json` se quedan en el temporal de Livewire; Livewire solo purga lo de más de 24 h y solo cuando llega otra subida.
- **Arreglo:** un comando de depuración de subidas temporales (sigue el patrón de nombres de `bolsas:depurar`, `mensajes:depurar`, `inscripciones:depurar`; p. ej. `subidas:depurar`) que borre del disco temporal de Livewire (`Livewire\Features\SupportFileUploads\FileUploadConfiguration::storage()` + `::directory()`) los archivos con más de 60 minutos, **incluidos los `.json`**, programado cada hora en `routes/console.php` junto a las purgas existentes. Pruebas: uno viejo se borra y uno reciente se queda; el calendario lo incluye (ajusta `CalendarioDeTareasTest` si enumera las tareas). Esto cubre también el CSV de `ListCarteras`.

### I3 + M3 + M9 + menores diferidos T3 y T5 — el resumen de la importación (Important)

- **Saltos de línea perdidos:** Filament pinta el cuerpo como HTML saneado; `implode("\n")` colapsa hasta 40 líneas en un párrafo. **Arreglo:** cuerpo como `Illuminate\Support\HtmlString` con cada línea escapada con `e()` y unidas con `<br>`.
- **M3 — texto de la hoja pintado como HTML:** `sanitizeHtml()` deja pasar `<a>` y `style`. El `e()` de arriba lo resuelve; añade una prueba con un nombre de establecimiento que contenga `<a href="https://x.test">` y afirma que sale escapado.
- **Re-importar engaña:** «ya tenía cuenta» entra en `sinCuenta()`. Al re-importar el archivo corregido saldría «0 cuentas creadas · 61 fichas sin cuenta» con 35 cuentas ya creadas, y esas líneas empujan fuera de las 40 las accionables (sin correo, inválido, compartido). **Arreglo:** `ResultadoDeAltaDeCuentas` cuenta aparte las fichas que ya tenían cuenta (p. ej. `contarYaTenia()` / `yaTenianCuenta(): int`), no las mete en `sinCuenta()`, y `resumen()` las nombra en su propio tramo. Ajusta `AltaDeCuentasDeAfiliadosTest` (el caso «ya tenía cuenta» deja de afirmar una línea y afirma el conteo).
- **M9 — copia:** el título sale «Fichas: 61 creados · 1 actualizados.» (género y número mal: el `resumen()` del importador es anterior y lo usa también el comando). **Arreglo:** la acción arma su propio título con concordancia («Fichas: 61 creadas · 1 actualizada.» / «2 actualizadas»), sin cambiar `ResultadoDeCargaDeAsociados::resumen()`.
- **Pruebas que faltaban (diferidos T3/T5):** `resumen()` con cero fichas sin cuenta, con plurales y con el tramo «ya tenían cuenta»; el recorte a 40 líneas con «…y N más.».

### M1 — claves crudas de validación en el modal (Minor, medido)

- `archivo` y `categoria` imprimen `validation.required` / `validation.mimetypes` (y `max` imprimiría su clave). **Arreglo:** `->validationMessages([...])` en español para `required`, el tipo de archivo y `max` en `archivo`, y `required` en `categoria`. Prueba: enviar sin archivo ni categoría y afirmar mensajes en español.

### M2 — el tipo de archivo se valida distinto en producción que en pruebas (Minor)

- Fuera de pruebas `getMimeType()` lee el contenido; una copia re-guardada por otra herramienta podría leerse como `application/zip` y rechazarse en producción sin que ninguna prueba lo vea. **Arreglo:** aceptar también `application/zip` en `acceptedFileTypes`, con comentario del porqué (un `.xlsx` es un zip; si no es una hoja válida, el importador lo reporta como error). Prueba con un `UploadedFile::fake()` de tipo `application/zip`.

### M5 — `report($error)` puede mandar datos personales al registro (Minor)

- Un `QueryException` lleva los valores del SQL (correos, nombres, el hash de la genérica) y en producción el registro sale a stderr. **Arreglo:** en el `catch` de la acción, reportar una excepción nueva sin el mensaje original (clase, código, archivo y línea), no la original. Ajusta la prueba: la excepción simulada lleva un correo en el mensaje y la reportada no lo contiene; `Exceptions::assertReported(...)` sigue pasando.

### M6 — prueba débil (Minor)

- `SeccionesCerradasConContrasenaProvisionalTest::test_sin_la_marca_la_seccion_no_manda_a_seguridad` también pasa con un 500. **Arreglo:** afirmar además que el estado es menor que 500.

### M8 — ayuda del campo contraseña al editar (Minor)

- En `UserForm.php`, la ayuda de edición no dice que escribir una contraseña deja provisional la de un afiliado (spec §4.4). **Arreglo:** añadirlo.

## Reglas del lote

- Todas las Global Constraints del plan siguen vigentes (español, sin dependencias ni permisos nuevos, sin `lang/`, mensajes propios, Pint, rutas explícitas en `git add`, nada de datos reales).
- **Cada arreglo con su prueba vista ROJA antes** (o, si es una prueba que se endurece, con la mutación que la pone roja) y verde después; regla 3.
- Commits pequeños por grupo (C2 · I1 · I2 · I3+M3+M9 · M1+M2+M5 · M6+M8 o como encaje), en español, con tu propio `Co-Authored-By`.
- Al final corre juntas: `ContrasenaProvisionalTest|ImportacionDeAsociadosTest|AltaDeCuentasDeAfiliadosTest|ImportacionDeLaBaseDelGremioTest|ImportarBaseDelGremioTest|SeguridadDeLaCuentaTest|SeccionesCerradasConContrasenaProvisionalTest|AvisoDeContrasenaProvisionalTest|MiCuentaSinCarteraTest|ContrasenaProvisionalDesdeElPanelTest|CrearUsuarioDelPanelTest|InvalidacionDeSesionTest|LimitesDePeticionesTest|LoginDeAsociadoTest|LoginDelPanelTest|AccionesDelPanelTest|CalendarioDeTareasTest|AjustesQueSirvenParaAlgoTest|FormulariosPublicosTest`.
