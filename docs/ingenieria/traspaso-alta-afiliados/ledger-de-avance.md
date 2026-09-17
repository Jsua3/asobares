# SDD ledger — plan: C:/Users/Predator/AppData/Local/Temp/claude/D--Sua-Files-IdeaProjects-Asobares3/bc09f505-4e1a-4dbc-a814-8505db5067e2/scratchpad/plan-alta-de-afiliados-reales.md

Spec: C:/Users/Predator/AppData/Local/Temp/claude/D--Sua-Files-IdeaProjects-Asobares3/bc09f505-4e1a-4dbc-a814-8505db5067e2/scratchpad/spec-alta-de-afiliados-reales.md
Workspace: D:/Sua_Files/IdeaProjects/Asobares3/.superpowers/sdd/plan-alta-de-afiliados-reales/
Reparto acordado con Sua: tareas 0, 11 y 12 las ejecuta el controlador; 1 a 10 van por subagente.
Acta: Sua registra la ampliación DESPUÉS del código. El expediente (Tarea 12) tiene que decirlo con todas las letras, como con el Acta 06.
Sin herramienta de todos en esta sesión: este ledger es el único registro de avance.

## Pre-flight scan

### Pares de tareas que comparten archivo o interfaz

| Tareas | Produce → consume | Hallazgo |
|---|---|---|
| T1 → T3 | `users.contrasena_provisional` + `#[Fillable]` → `forceFill([... 'contrasena_provisional' => true])` | Coherente: T3 usa forceFill, no create |
| T1 → T6 | columna + cast → asignación suelta en el controlador y `@if ($usuario->contrasena_provisional)` en la vista | Coherente |
| T1 → T7 | columna + cast → `$usuario->contrasena_provisional` en `ExigirContrasenaPropia` | Coherente |
| T1 → T8 | columna + cast → `auth()->user()?->contrasena_provisional` en el componente | Coherente |
| T1 → T10 | `marcarContrasenaProvisionalSiEsAfiliado(): void` → CreateUser, EditUser, comando | Coherente; el método hace `unsetRelation('roles')`, necesario porque EditUser guarda roles antes del gancho |
| T2 → T4 | `fichasTocadas(): list<int>` → `Asociado::query()->whereKey(...)` | Coherente |
| T3 → T4 | `crear(Collection, string): ResultadoDeAltaDeCuentas` → llamada y subclase anónima de la prueba | Coherente: el atributo `#[SensitiveParameter]` no forma parte de la firma |
| T4 → T5 | `importar(string, string, ?string): array{carga, cuentas}` → acción y subclase anónima con `__construct() {}` | Coherente: la subclase no necesita llamar al constructor padre |
| T3/T2 → T5 | `resumen()`, `errores()`, `sinCuenta()` → `notificarResultado()` | Coherente: los cinco métodos existen o se crean en T2/T3 |
| T6 → T7 | ruta `mi-cuenta.seguridad` → destino de la redirección y caso «seguridad» en `seccionesAbiertas` | Coherente: T7 depende de T6, el orden del plan lo respeta |
| T6 → T7 (`routes/web.php`) | T6 inserta rutas de seguridad tras `fotos.destroy`; T7 reemplaza el bloque desde «// Beneficios detrás de la sesión» | Coherente: las rutas de T6 quedan antes del ancla de T7 y fuera del grupo cerrado |
| T6 → T8 → T9 (`SettingSeeder`) | T6 ancla tras `mi_cuenta_pago_ayuda`; T8 tras `mi_cuenta_seguridad_provisional_texto`; T9 tras `mi_cuenta_aviso_provisional_texto` | Coherente: cada ancla la crea la tarea anterior |
| T8 ↔ T9 (`index.blade.php`) | T8 edita header/nav/flash; T9 edita `@if ($cartera->estaAlDia())` y `@if ($cartera->actualizado_at)` | Coherente: anclas distintas |
| T8 → T9 | T8 pinta `session('aviso')` en index; T9 manda `aviso` desde `pagarMensualidad` | Coherente: la prueba de T9 mira la sesión, no el pintado |
| T9 ↔ T7 | T7 espera `pagar` con marca → redirect a `mi-cuenta.index`; T9 cambia la rama de cartera nula | Coherente: sigue redirigiendo a index |
| T9 ↔ LimitesDePeticionesTest | pagar sin cartera → redirect index | Coherente con `test_gestionar_seis_postulaciones…` y `test_el_limite_de_pagar…` |
| T6 ↔ LimitesDePeticionesTest | entrada nueva `PUT mi-cuenta.seguridad.actualizar` con `duenio()` sin marca | Coherente: sin marca no pasa por `contrasena.propia`, y la ruta está fuera del grupo |
| T10 ↔ AccionesDelPanelTest / InvalidacionDeSesionTest | ganchos de CreateUser/EditUser | Coherente: crear subadmin y editar sin contraseña no marcan; InvalidacionDeSesionTest actualiza el modelo directo |
| T4 → T11 | `ImportacionDeLaBaseDelGremio::importar` → ensayo por tinker | Ver hallazgo H1 |

### Cada tarea contra sí misma

| Tarea | Pruebas ↔ código | Hallazgo |
|---|---|---|
| T0 | pasos de entorno | `npm install` puede tocar `package-lock.json`: el plan ya dice revertirlo |
| T1 | 5 casos ↔ migración, cast y método | Coherente; la mutación del cast se ve roja porque `assertTrue` es estricto |
| T2 | 4 casos ↔ `anotarFicha`/`fichasTocadas` + 3 reemplazos en `procesarFila` | Coherente |
| T3 | 14 casos ↔ tabla de motivos en orden | Coherente; el orden de comprobaciones deja cada caso alcanzable |
| T4 | 5 casos ↔ orquestador | Coherente |
| T5 | 11 casos ↔ acción | Riesgos de API a confirmar al implementar: `TextInput::notIn()` y la forma del valor del FileUpload en pruebas; el plan ya da cómo diagnosticar la ruta del error |
| T6 | 16 casos ↔ controlador, vista, rutas, limitador, ajustes | Coherente; nombres de campo = `$dontFlash` |
| T7 | 12+12+3+1 casos ↔ middleware, alias, grupo | Coherente; parámetros reales para no depender del orden de middleware |
| T8 | 6 casos ↔ componente e inserciones | Coherente |
| T9 | 3 casos + ajuste de FormulariosPublicosTest ↔ controlador y vista | Coherente |
| T10 | 6 + 5 casos ↔ páginas, tabla, ayuda, comando | Coherente; paso 7 condicionado a verlo rojo |
| T11 | cifras esperadas ↔ simulación | Ver H1 |
| T12 | expediente | Coherente |

### Hallazgos y rulings

- H1: T11 sobrescribe `DB_DATABASE` por variable de entorno. Si existe `bootstrap/cache/config.php`, la configuración cacheada ignora la variable y el ensayo escribiría datos reales en la base de desarrollo. Ruling: antes del paso 1 de T11 el controlador comprueba que no haya configuración cacheada y que `config('database.connections.sqlite.database')` apunte a `ensayo.sqlite`; si no, `config:clear` y vuelve a comprobar — la alternativa es mezclar 61 fichas reales con la demostración local — si el ruling fuera innecesario, cuesta un comando.
- H2: T4, T5, T6 y T8 duplican ayudantes de prueba (`xpathDe`, el escritor del .xlsx). Ruling: se quedan duplicados — es la convención del proyecto (`xpathDe` existe en 17 clases y `tests/TestCase.php` está vacío a propósito); extraer un trait tocaría decenas de archivos fuera del plan — si fuera un error, son cuatro copias más que consolidar después.
- H3: modelos. Ruling: implementadores y revisores por tarea en `sonnet`, revisión final en `opus` — las tareas traen el código completo, pero las mutaciones (romper, ver rojo, restaurar) y el entorno Windows (PHP fuera del PATH, Bash vs PowerShell) castigan a un modelo pequeño con turnos extra y con mutaciones sin restaurar — si fuera excesivo, cuesta tokens, no corrección.

## Avance

- Tooling: `scripts/task-brief` busca «Task N» y el plan dice «Tarea N»; los briefs salen de `brief.sh` (en este workspace), que antepone las Global Constraints a la sección de la tarea.
- Tarea 0: `origin/main` avanzó a `a57f044` (arreglo de /empleo empujado por Sua a las 10:42; no toca archivos del plan). Rama `afiliados/alta-real` creada sobre `a57f044`. `composer install` → Filament v5.8.2, Livewire v4.4.5, sin cambios versionados. `npm run build` ✓, `package-lock.json` intacto. Sin `bootstrap/cache/config.php`. Línea base vecina: 120 pruebas, 818 aserciones, 0 fallos (50,5 s).
- Task 0: complete (controlador, sin commits)
- Task 1: base a57f044
- Task 1: Ruling: el commit lleva `Co-Authored-By: Claude Sonnet 5` en vez del `Claude Opus 5` literal del plan — la atribución debe nombrar al modelo que escribió el commit (el subagente es Sonnet); se instruye igual a los siguientes — si fuera un error, es cosmético y no se reescribe historia.
- Task 1: minor (deferred): no hay prueba de idempotencia de `marcarContrasenaProvisionalSiEsAfiliado()` con la marca ya en true.
- Task 1: minor (deferred): la predicción del plan para la mutación del cast se queda corta (se ponen rojas las 5 pruebas, no 1).
- Task 1: complete (commits a57f044..cb8895f, review clean)
- Task 2: base cb8895f
- Task 2: complete (commits cb8895f..c6a577c, review clean)
- Task 3: base c6a577c
- Task 3: minor (deferred): el paso 5 del plan dice «14 casos»; la clase tiene 13 (8 métodos + proveedor de 3 + proveedor de 2). Error de conteo del plan, no del código.
- Task 3: minor (deferred): la mutación de `Str::lower` pone rojas 4 pruebas, no 1 (normalizar alimenta la detección de repetidos y la búsqueda del equipo).
- Task 3: Ruling: la redacción de los motivos se queda como está en el código del plan («el correo es de una cuenta del equipo del gremio», «el correo ya tiene una cuenta», «el mismo correo está también en «…»») aunque la tabla del spec §4.2 los parafrasea más corto — el plan se escribió después con las constantes exactas y las pruebas las fijan; el sentido es el mismo — si fuera un error, son unas palabras en la notificación del panel.
- Task 3: minor (deferred): `ResultadoDeAltaDeCuentas::resumen()` no tiene prueba para cero fichas sin cuenta ni para los plurales (solo 1/1).
- Task 3: complete (commits c6a577c..4d53609, review clean)
- Task 4: base 4d53609
- Task 4: nota: el implementador tardó 86 min de reloj pero el commit es de las 17:50 y las corridas duran ~2,5 s — tiempo muerto de la máquina, no un cuelgue.
- Task 4: minor (deferred): `test_una_fila_rechazada_no_recibe_cuenta` no afirma que `cuentas` sea un resultado con `creadas() === 0` (sí afirma cero usuarios).
- Task 4: complete (commits 4d53609..04e8d0c, review clean)
- OTRA SESIÓN ACTIVA (18:28): desde el worktree `ingrid02` se integró `integracion/lenguaje-unico` y se empujó `origin/main` hasta `bbe9b63` (12 commits, 80 archivos; producción sirve 6197c92). Esa sesión también retiró la entrada `asobares-ingrid02` de `.claude/launch.json` en la raíz (ya no aparece modificado). Solapes con el plan: `database/seeders/SettingSeeder.php` (T6/T8/T9) y `material/estado.md` (T12); su bitácora ocupó la §57 (la nuestra será la siguiente). Sin cambios en composer.lock ni package.json.
- Rebase hecho sin conflictos. Mapa de hashes: T1 cb8895f→90876b5 · T2 c6a577c→a416b4f · T3 4d53609→f33a7f8 · T4 04e8d0c→295876d. `npm run build` ✓. Verde sobre la base nueva: 147 pruebas, 877 aserciones (120 vecinas + 27 de T1–T4). Nota: `main` trae la migración `2026_09_16_172442_actualiza_la_entradilla…`, posterior por nombre a la nuestra (`2026_09_16_171033_…`); en producción la suya ya corrió y la nuestra entra como pendiente igual.
- Task 5: base 295876d
- Task 5: implementador ab532f9ae1b829f30 → commit 6f98d1f. Desvío justificado: limpia también el `.json` de metadatos que Livewire 4 escribe junto a cada subida (`delete()` no lo borra; guardaría el nombre original del archivo del gremio).
- Task 5: minor (deferred): el plan dice «11 casos»; son 12 (7 métodos + proveedor de 5).
- Task 5: minor (deferred): la limpieza del `.json` usa `is_file`/`unlink` sobre la ruta absoluta y no el disco de Livewire; correcto mientras el temporal sea `local` (D-13 lo tendrá que mover).
- Task 5: Ruling: los dos Important «plan-mandated» del revisor entran al loop — (1) prueba de la rama de aviso de `notificarResultado()` con el motivo de una ficha sin cuenta en el cuerpo, (2) aserción de que `report()` corre ante una excepción — porque el spec §4.2 exige que la notificación diga cada ficha sin cuenta con su motivo y el §5 exige `report()`, y ninguna prueba lo vigilaba; el recorte a 40 líneas («…y N más.») queda sin probar (menor) — si fuera un error, son dos pruebas de más.
- Task 5: minor (deferred): el recorte del resumen a 40 líneas («…y N más.») no tiene prueba.
- Task 5: fix round 1/5 (2 addressed, 0 open — rama de aviso con motivo probada; `report()` afirmado con `Exceptions::fake()`; commits 6f98d1f..30fc0fc)
- Task 5: complete (commits 295876d..30fc0fc, review clean after 1 fix round)
- Task 6: base 30fc0fc (anclas verificadas en el árbol rebasado: import de ProveedorController, ruta fotos.destroy, limitador mi-cuenta-contrasena, entrada «definir la contraseña», línea mi_cuenta_pago_ayuda)
- Task 6: nota para la revisión final y la Tarea 12: `artisan test` sale con código 1 aunque el JSON diga `"result":"passed"`; el implementador lo reprodujo con una prueba no tocada (preexistente). En la suite completa hay que juzgar por el JSON y averiguar la causa antes de citar la cifra.
- Task 6: minor (deferred): la prueba del invitado solo cubre el GET de seguridad, no el PUT.
- Task 6: complete (commits 30fc0fc..358a991, review clean)
- Task 7: base 358a991
- Task 7: complete (commits 358a991..0512314, review clean)
- Task 8: base 0512314
- Task 8: minor (deferred): `test_mi_cuenta_pinta_el_aviso_que_llega_en_la_sesion` solo busca el texto; no afirma que salga con `x-publico.alerta tipo="aviso"`.
- Task 8: minor (deferred): el `min-h-11` del aviso no entró al catálogo `cadenasMedidas()` de `ObjetivoTactilTest`; lo vigila solo la prueba de la tarea.
- Task 8: complete (commits 0512314..c441099, review clean)
- Task 9: base c441099
- Task 9: nota: el Pint de este repositorio no retira `use` sin uso (el plan lo suponía); el implementador quitó `use App\Models\Cartera;` a mano.
- Task 9: minor (deferred): `test_pagar_sin_cartera_no_responde_que_esta_al_dia` afirma que hay `aviso` y no hay `exito`, no el texto del aviso.
- Task 9: complete (commits c441099..927f22d, review clean)
- Task 10: base 927f22d
- Task 10: el paso 7 SÍ hacía falta: la prueba nueva vio `validation.password.symbols` en la salida de `asobares:crear-usuario` antes del arreglo (defecto latente real del comando, corregido a `clave.password.*`).
- Task 10: minor (deferred): `CreateUser::afterCreate()` y `EditUser::afterSave()` repiten las dos líneas que llaman a `marcarContrasenaProvisionalSiEsAfiliado()`.
- Task 10: minor (deferred): el `TernaryFilter` ofrece una opción «en blanco» que nunca coincide (columna booleana no nula); es el código del plan.
- Task 10: complete (commits 927f22d..398cd5c, review clean)
- Tareas 1–10 completas. Siguiente: revisión final de la rama (opus), luego Tarea 11 (ensayo) y Tarea 12 (cierre) en el controlador.
- Revisión final (opus, bbe9b63..398cd5c): «With fixes». Critical C1 (spec: con la genérica y el correo ajeno se toma la cuenta — cambia la contraseña, apaga la marca, deja fuera al dueño, ve datos de terceros) y C2 (sesión abierta sin segunda petición sobrevive al cambio; demostrado). Important I1 (seguridad acepta la del demo), I2 (subida que queda en disco si se cierra el modal tras error), I3 (resumen ilegible y engañoso al re-importar). Minor M1–M9. Sondas del revisor en scratchpad/revision/.
- Final review: Ruling: C1 NO entra al lote de arreglos — es seguridad y revierte la decisión explícita D1 de Sua (contraseña genérica); se le pregunta con opciones — si se hubiera arreglado sin preguntar, se habría rehecho el diseño que Sua eligió.
- Final review: Ruling: M4 (rotar remember_token apaga el «recordarme» del propio dispositivo) queda aparcado — el dueño vuelve a entrar una vez — si fuera un error, es una molestia de un inicio de sesión.
- Final review: Ruling: M7 (política de contraseñas en cinco sitios) queda aparcado como trabajo aparte — extraer un objeto de política ahora toca el comando, el panel y el portal fuera del alcance del arreglo — si fuera un error, la deriva que causó I1 puede repetirse hasta que se haga.
- Final review: Ruling: entran al lote C2, I1 (también en UserForm), I2 (comando `subidas:depurar` cada hora), I3+M3+M9 (+ diferidos T3 resumen y T5 recorte), M1, M2 (aceptar application/zip), M5 (reportar sin el mensaje original), M6, M8 — todos valen elija lo que elija Sua en C1 — si C1 cambia el flujo de la acción, parte de I3/M1 se retoca.
- Final review: minor (deferred) T10 «opción en blanco del TernaryFilter» retirado: en Filament 5.8.2 la opción en blanco significa «sin filtro», no es defecto.
- Lote de arreglos de la revisión final: base 398cd5c — instrucciones en final-review-fixes.md
- DECISIÓN DE SUA sobre C1 (16 sep, por AskUserQuestion): «Una por afiliado» — contraseña provisional aleatoria por cuenta y descarga de accesos por la dirección. Revierte D1 (genérica). Diseño corto presentado en el chat (importar sin campos de contraseña; acción «Descargar accesos provisionales» que regenera y descarga CSV; 12 caracteres sin ambiguos; bitácora sin secretos); APROBACIÓN PENDIENTE — no se codifica hasta el sí.
- Lote de arreglos: commits 0bda700 (C2) · f1262a2 (I1) · dbd8901 (I2, `subidas:depurar` cada hora) · f231c5c (I3+M3+M9+T3/T5) · c8f6505 (M1+M2+M5) · dfdcd50 (M6+M8). 269/269 en las clases listadas. Desvíos declarados: el cuerpo de la notificación va como cadena con `<br>` y líneas escapadas (Filament 5.8.2 `body()` no toma HtmlString); el hash al entrar va en un método aparte sobre el evento `Login`; el login del panel no tiene prueba de C2 (Livewire en pruebas corre sin sesión); se retiró la constante `YA_TENIA_CUENTA`.
- Final review: minor (deferred): `ListCarteras` tiene el mismo cuerpo de notificación con saltos perdidos y HTML de un archivo, y no borra su temporal (ya lo cubre `subidas:depurar`).
- Tarea 12 (expediente): el runbook dice «tres purgas diarias» y `encargo.md` no nombra `subidas:depurar`; hay que ponerlos al día.
- Ruling (previo al rebase, conservado): rebase de `afiliados/alta-real` sobre `origin/main` (`bbe9b63`) antes de la Tarea 5 — las tareas 6 a 9 editan archivos que la integración cambió y sus pruebas tienen que correr contra lo que de verdad se despliega; la rama es local y sin empujar, así que el rebase es reversible por reflog y no toca historia compartida — si fuera un error, cuesta rehacer los hashes del ledger (queda el mapa abajo) y un conflicto que resolver.
