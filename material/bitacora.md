# Bitácora del proyecto — Plataforma Web ASOBARES Capítulo Quindío

_Historia del proyecto: las notas de versión del prompt maestro (v2–v16, del 1 al 31 de agosto de 2026), el §0 tal como quedó el 1 de septiembre y las secciones §15–§31 tal como se escribieron, **sin reescribir**. Este archivo **solo se anexa**: ninguna entrada anterior se edita, ni para corregir una cifra; si una cifra resultó falsa, la entrada nueva lo dice. Las entradas nuevas continúan la numeración desde el §32, una por sesión que cambie algo: fecha, qué se hizo con sus commits, qué se midió, qué se aprendió, qué entró y salió del estado. Para el estado vigente, `estado.md`; para lo que el producto es y sus reglas, `encargo.md`; para empezar una sesión, `prompt-maestro-laravel-filament.md`._

Archivo creado el 1 de septiembre de 2026 al partir el prompt maestro (rama `division-prompt-maestro`). Las referencias «§n» que aparecen en el código, las pruebas, los mensajes de commit y los documentos del Project de Cowork resuelven aquí con la misma numeración. El encargo original (§1–§14) no está aquí: se reescribió con lo vigente en `encargo.md`, y su texto tal como estaba el 1 de septiembre queda en el historial de git (`ed9bec2`).

## Índice cronológico de las notas de versión

Las notas están abajo en el orden en que quedaron en la cabecera original (no cronológico). Este índice las ordena por fecha y dice a qué sección remite cada una.

| Versión | Fecha | Qué registra | Sección |
|---|---|---|---|
| v2 | 1 ago | Alcance ampliado de la Reunión 2 (bolsas, cartera, PSE, guía reforzada) | — |
| v3 | 3 ago | Prompt ejecutado; stack real (Laravel 13, Filament 4, Livewire 3) | — |
| v4 | 4 ago | Endurecimiento de seguridad antes de Bold | §15 |
| v5 | 4 ago | Tema claro/oscuro con tokens; defectos cerrados; trampas de Chromium, `view:clear`, `Paginator`, `storage:link` | — |
| v6 | 4 ago | Rediseño de las tres bolsas; trampas del observer, `view`/`verEnPortal`, retención cero | §16 |
| v7 | 5–9 ago | Panel como sistema de diseño; observatorio en curso; cinco falsos verdes; tres suposiciones falsas de la API de Filament | §18, §19 |
| v8 | 15 ago | Hosting decidido: Laravel Cloud con cuenta institucional | §20 |
| v9 | 18 ago | El cuello de botella deja de ser técnico; R-14 escalado | §23 |
| v10 | 19 ago | Fase 4 cerrada; cifras verificadas | §23.11, §23.12 |
| v11 | 19 ago (tarde) | Pase de interfaz cerrado y despliegue a un comando | §25 |
| v12 | 25 ago | RF-60 cerrado; Ingrid commitea; seis falsos verdes más | §24.6 |
| v13 | 28–30 ago | El gremio vio la plataforma: catorce señalamientos | §27 |
| v14 | 30 ago | La suite estaba en rojo: el defecto de calendario | §28 |
| v15 | 30 ago | Hay cuenta y despliegue, y el sitio está en 500 | §29 |
| v16 | 31 ago | Nueve señalamientos cerrados; el sitio sigue en 500 | §30 |
| — | 1 sep | La base dejó de estar vacía; inventario consolidado | §31 |

## Cabecera original del prompt maestro (líneas 1–59 tal como estaban el 1 de septiembre de 2026)

# PROMPT MAESTRO v4 — Plataforma Web ASOBARES Capítulo Quindío (Laravel 13 + Filament 4)

> **v15 (30 ago 2026) — HAY DESPLIEGUE, Y LA URL ESTÁ EN 500.** Hay cuenta de Laravel Cloud y el sitio **ya está publicado** en `asobares-production-0jhdcz.laravel.cloud` (tres despliegues en verde), pero **responde `500`: construir en verde no es arrancar**. Se acabó el bloqueo de dos semanas —SSL, dispositivos físicos, respaldos, medición contra dominio y cinco vacíos declarados de la matriz dejan de estar bloqueados, y el §26.2 pasa de lista de espera a tarea—, pero aparecieron tres cosas nuevas. **Manda la §29, y dentro de ella el §29.7.** ⚠️ **El entorno se llama `production`, y `DatabaseSeeder` se niega a correr ahí**: si `APP_ENV` heredó ese nombre, la base se queda vacía y la demo sale sin un solo bar. ⚠️ **La organización de Cloud es `juan-sua`, personal**: el apartado 1.1 del runbook pide facturación del gremio, así que R-14 está sorteado, no cerrado, hasta que se compruebe. ⚠️ **No hay bucket y el sitio duerme por scale-to-zero**: no lo enseñes ni lo midas en frío. ⚠️ **El bloqueo se movió, no desapareció: ahora es el correo.** Laravel Cloud no incluye SMTP y no hay proveedor contratado; sin él los códigos MFA no salen del registro y `/admin` **no se puede demostrar delante del cliente**. Configura `LOG_STACK=stderr` desde el primer despliegue o ni siquiera funciona el camino de emergencia. ⚠️ **Antes de crear el bucket, lee el §8.3 del runbook**: la política evidente abre los formatos de la guía normativa por URL directa. ⚠️ Y **la demo del 4–11 de septiembre va sobre el subdominio de Cloud**; el dominio propio es de la semana 8.
>
> **v16 (31 ago 2026) — NUEVE SEÑALAMIENTOS CERRADOS, Y EL SITIO SIGUE EN 500.** Doce confirmaciones sobre el §27: `main` de `6b0a20d` a `b9e2428`, empujadas. Cerrados OBS3-01, 02, 04, 05, 06, 08, 12, 13 y 14; OBS3-10 y OBS3-11 hechos a medias con la mitad de contenido pendiente del gremio; OBS3-03 y OBS3-07 bloqueados en decisiones humanas. **El bloque A se agotó en lo que depende del equipo.** Suite de 871 a **946 casos** (935 pasan, 11 omitidas, 0 fallos, 3.502 aserciones). ⚠️ El sitio desplegado sigue devolviendo **500** y ahora está doce commits por detrás de `main`. ⚠️ Deuda nueva: el disco público transporta fotos sin moderar desde OBS3-13, hay seis migraciones sin verificar contra PostgreSQL real, y el reparto del §24.3 quedó suspendido de hecho. **Todo en la nueva §30**, que además explica cómo se auditó este documento y qué se decidió no tocar.
>
> ## ⇢ EMPIEZA POR EL §0
>
> **El §0 «Punto de entrada» es lo primero que debe leer cualquier sesión nueva.** Dice en qué estado está el proyecto, qué se está exigiendo y con qué fecha, qué falta y qué problemas conocidos te van a morder. Las notas de versión de abajo son el registro histórico y se leen después, no antes.
>
> ⚠️ **Este prompt YA SE EJECUTÓ.** El párrafo que sigue es del encargo original de agosto y solo aplica si alguien quisiera reconstruir el proyecto desde cero en una carpeta vacía. No es lo que estás haciendo.
>
> Copia TODO el contenido desde "## 1. Tu misión" hasta el final y pégalo en Claude Code, dentro de una carpeta vacía. Requisitos previos en tu máquina: PHP ≥ 8.3 con Composer, Node ≥ 20 y Git. Si algo falta, Claude Code puede instalarlo primero.
>
> **v2 (1 ago 2026):** incorpora el alcance ampliado de la Reunión 2 con la directiva — bolsa de empleo, directorio de artistas, bolsa de proveedores, estado de cartera del asociado, preferencia PSE y guía normativa reforzada. Los módulos nuevos se construyen en versión mínima demostrable; el alcance definitivo lo fija el documento de requisitos v2 firmado.
>
> **v3 (3 ago 2026) — PROMPT YA EJECUTADO:** el prototipo existe en `asobares-web/` (repo git propio) y está completo — fases 0–5 terminadas, suite de pruebas en verde (191 pruebas: 184 pasan, 7 omitidas, 0 fallos) y correcciones posteriores aplicadas (identidad del manual de marca, contenido del TED gremial, login MFA por correo, imágenes y 403 de `/mi-cuenta`). **Stack real: Laravel 13.23 + Filament 4.12 + Livewire 3.8 sobre PHP 8.5.9** — el prompt pedía Laravel 12, pero el instalador oficial ya solo entrega Laravel 13; el starter kit de Livewire se descartó porque trae Livewire 4 y Filament 4 exige `livewire/livewire ^3.5`, así que el proyecto se creó con `laravel new --no-authentication` y el login de `/mi-cuenta` se escribió a mano. Filament 5 (publicado el 31 jul 2026) se descartó por demasiado nuevo para la entrega del 22 sept. Las menciones de versión de abajo ya están corregidas; este documento queda como registro del encargo y receta de relanzamiento.
>
> **v5 (4 ago 2026) — TEMA CLARO/OSCURO EN TODO EL SITIO:** el frontal dejó de ser oscuro por obligación. Ahora usa **tokens semánticos** (`fondo`, `superficie`, `superficie-alta`, `fuerte`, `tinta`, `suave`, `tenue`, `apagado`, `linea`, `linea-fuerte`, `acento`, `acento-fuerte`, `marca-panel`, `exito*`, `aviso*`) definidos una sola vez en `resources/css/app.css`: `:root` es el tema claro y `.dark` el oscuro, con `@custom-variant dark` y `@theme inline` de Tailwind v4. Se migraron **446 clases cableadas en 29 vistas** y los valores del modo oscuro se conservan exactos, así que el refactor es un no-op visual para la identidad nocturna original. El control vive en un **desplegable de configuración en la navbar** —Claro / Oscuro / Sistema, por defecto **Sistema**— visible para cualquier visitante; para el asociado, la secretaría y la dirección añade además nombre, rol y sus acciones de sesión. Comparte la clave `localStorage.theme` con Filament a propósito: quien usa el panel y el sitio elige una sola vez. Se añadieron **9 pruebas** (191 → 200; el total actual de 233 incluye también el endurecimiento de seguridad de la v4), una de ellas una guardia que recorre las vistas y falla si reaparece una clase de tema cableada. **Las secciones 4, 7 y 11 están corregidas: si se relanza este prompt, el sitio se construye bicromático desde el principio, no oscuro y adaptado después.**
>
> **v5 · defectos cerrados de paso:** una auditoría multiagente (43 hallazgos en bruto, 12 confirmados tras refutación adversarial) destapó que el aro del pin de Leaflet **no** debe seguir el tema —se dibuja sobre teselas de OSM, claras en ambos modos—, que el foco no volvía al disparador al cerrar el desplegable con Escape y que la opción activa del selector se distinguía solo por color (1,22:1, incumple WCAG 1.4.11). Aparte: el **paginador de Laravel** se reescribió con tokens y en español, porque venía cableado en grises (2,63:1 en oscuro) y, al no existir carpeta `lang/`, mostraba las claves crudas `pagination.previous` y un «Showing … results» en un sitio en español; y las **portadas de relleno** se rediseñaron con fondo transparente, diagonales en gris neutro y el monograma de marca, para que una sola imagen sirva en los dos temas.
>
> **v5 · trampas que costaron tiempo y conviene no repetir:**
> - Chromium **no reinicia una `transition` cuando lo que cambia es la custom property** que hay detrás del valor: la propiedad se queda congelada en el color del tema anterior. Con `transition-colors` repartido por todo el sitio hay que apagar las transiciones durante el cambio y devolverlas después (con respaldo de `setTimeout`: en pestaña de segundo plano no corre `requestAnimationFrame`).
> - `app.css` escanea `storage/framework/views/*.php`, así que **el tamaño del bundle depende de qué vistas estén compiladas en caché**. Hay que `php artisan view:clear` antes de compilar para desplegar: se vio pasar de 90 kB a 69 kB solo con eso.
> - `Paginator::$defaultView` es **estático y vive en todo el proceso**; Livewire lo reapunta a su propia vista al renderizar una tabla del panel y no siempre lo restaura, así que en la suite una prueba de `/admin` puede romper otra del sitio público.
> - Tras unificar el repositorio, el enlace `public/storage` se quedó apuntando a la antigua `asobares-web/`: hay que rehacerlo con `php artisan storage:link` o no carga ninguna imagen.
>
> **v4 (4 ago 2026) — ENDURECIMIENTO DE SEGURIDAD:** antes de conectar Bold con dinero real se auditó la seguridad del prototipo (49 hallazgos en bruto, 43 confirmados tras refutación adversarial). Se cerraron los bloqueantes de pago, el XSS almacenado del JSON-LD, el importador de cartera y las subidas de archivos; la suite pasó de 200 a 233 pruebas. **Las secciones 8 y 9 están corregidas con lo aprendido: si se relanza este prompt, hay que construirlas así desde el principio.** El acta completa —método, hallazgos, qué se cerró y qué queda— está en la nueva sección 15.
>
> **v6 (4 ago 2026) — REDISEÑO DE LAS TRES BOLSAS:** el encargo original modeló las bolsas como contenido que carga la oficina, y así no se mueven: quien tiene la necesidad —el establecimiento que busca bartender— no tenía cómo publicarla, y «postularse» era un enlace de WhatsApp que no dejaba rastro. **Se invirtió la propiedad del contenido.** Ahora el asociado publica y corrige sus propias vacantes desde `/mi-cuenta/vacantes`, la secretaría aprueba o devuelve **con motivo obligatorio**, y ni ella ni la dirección editan una vacante ajena. Las postulaciones tienen tabla propia y avisan por correo al establecimiento. Artistas y proveedores entran por formulario público moderado en vez de un mensaje de texto libre que había que transcribir a mano. Y los datos personales de la bolsa **se purgan solos** al vencer su plazo (`bolsas:depurar`, diario). La suite pasó de **233 a 374 pruebas** (363 pasan, 11 omitidas). **Las secciones 5, 6, 7, 9, 10, 11 y 12 están corregidas: si se relanza este prompt, las bolsas se construyen así desde el principio, no como un CRUD del panel.**
>
> **v6 · las tres trampas que costaron rondas de revisión:**
> - **El observer de aprobación degrada *cualquier* guardado de un registro publicado hecho por quien no puede publicar**, mire el campo que mire. Cerrar una vacante solo toca `cerrada_at`, pero la despublicaba. Hace falta un escape explícito y acotado (propiedad de instancia en `Vacante`, leída con `instanceof` y encendida solo dentro de un `try/finally`), **no** relajar la condición general: hacerlo reabriría el agujero para los otros ocho modelos publicables.
> - **Autorizar la vista del portal con la habilidad `view` es una fuga de datos.** `view` concede por permiso *o* por propiedad, así que un directivo que además sea dueño de un bar leía los candidatos de cualquier otro establecimiento. Las rutas de `/mi-cuenta` se autorizan **solo por propiedad** (habilidad aparte, `verEnPortal`); `view` queda para el panel.
> - **Un plazo de retención en cero convierte la purga en «borra todo»**: `now()->subMonths(0)` es *ahora*. Y a cero se llega solo —variable de entorno vacía, `config:cache` viejo que no incluya el archivo nuevo—. El comando **aborta con error** si el plazo no es un entero ≥ 1; vaciar la variable no desactiva nada.
>
> **v7 (5–9 ago 2026) — EL PANEL ADMINISTRATIVO, Y TRABAJO EN CURSO:** el objetivo declarado por la dirección cambió de «prototipo completo» a **demo para la directiva del 22 de septiembre**, y eso reordenó la prioridad hacia el panel. Se rediseñó `/admin` como **sistema de diseño**, no como cuatro pantallas sueltas: los tokens de color pasaron a un `resources/css/tokens.css` compartido entre el sitio y un tema propio de Filament, se añadieron tres componentes reutilizables (vidrio, tarjeta KPI, fila de cola), las gráficas pasaron a seguir el tema, y el tablero de fábrica se sustituyó por uno de tres bandas: **lo pendiente de aprobar preguntando a las policies**, cuatro KPIs **distintos por rol** y todos enlazados, y recaudo mensual agregado en SQL. En paralelo se sembró historia con forma (18 meses de mensualidades con estacionalidad, consultas de la guía por municipio) y se añadió la tabla anónima `consultas_guia`. **Todo eso está fusionado a `main`: 39 commits, la suite pasó de 374 a 458 pruebas.** Encima se está construyendo el **Observatorio del gremio** (rama `observatorio`, 16 commits, suite en 486), que **no está terminado**. El estado exacto, lo que falta y las trampas están en la nueva **sección 18**.
>
> **v7 · la trampa que más costó, y que no es técnica:** de veintitantos fallos atrapados en estas dos fases, **cinco fueron pruebas en falso verde escritas por el propio autor del plan** — pruebas que pasaban con el bug reintroducido. Todas tenían la misma forma: `assertSee('alguna palabra')` o una aserción sobre el texto de un archivo, en vez de sobre el comportamiento. Ninguna se detectó leyendo; todas salieron cuando un revisor **mutó el código a propósito** y miró si la prueba se enteraba. Si se relanza cualquier parte de este trabajo: **escribir la prueba no basta, hay que romper el código y ver el rojo.**
>
> **v14 (30 ago 2026) — EMPIEZA POR AQUÍ. LA SUITE ESTABA EN ROJO Y NADIE LO SABÍA.** Al medir las cifras que el §27.9 pedía volver a medir antes de citarlas, la suite salió **roja**: `SemillaConFormaTest` fallaba porque a un asociado con cinco meses de mora se le sembraba un pago dentro de su propia ventana de mora. No era la semilla: era **aritmética de calendario**. `now()->subMonths(6)` un 30 de agosto **no da febrero, da el 2 de marzo** —PHP construye `2026-02-30` y lo deja correr al mes siguiente en vez de recortarlo—, así que con un `startOfMonth()` detrás dos cubos distintos aterrizan en el mismo mes y el que se saltaron no lo cubre nadie. **Solo ocurre los días 29, 30 y 31, y solo cuando la resta cruza febrero**: por eso las 820 pruebas del 25 de agosto se midieron un día que no desbordaba y la suite llevaba semanas pasando verde veintitantos días de cada mes. El mismo error estaba en **las tres purgas de retención** —que borraban datos personales hasta dos días **antes** del plazo publicado—, en las ventanas de 18 y 12 meses del observatorio y en el borde estricto de RF-60. Corregido en once archivos, con `VentanaDeMesesTest` fijando la fecha en cuatro días que desbordan para que la guardia valga los 365. Suite de 820 a **870 casos** (859 pasan, 11 omitidas, 0 fallos, 3.115 aserciones) — y a **871** al cerrar el v14, con la prueba del acuse del §28.5. ⚠️ **Cifra superada:** el 31 de agosto la suite es de **946 casos** (935 pasan, 11 omitidas, 0 fallos, 3.502 aserciones) tras los once archivos de prueba del bloque OBS3. Ver §30. Cerradas de paso dos deudas del §18.6: la vigilancia **por eje** de las ranuras del plugin de tema, que faltaba en los widgets del tablero, y el enum crudo de `RequisitoAperturaFactory`. ⚠️ **Y la lección de método, que es nueva y no la cubrían el v7 ni el v12:** aquí no hubo prueba en falso verde ni revisor que fallara — **la prueba era correcta y el defecto real**, pero el calendario decidía si se veía. Una suite que depende del día en que se ejecuta miente sin que nadie mienta. Ver la nueva **§28**.
>
> **v13 (28–30 ago 2026) — EL GREMIO VIO LA PLATAFORMA Y LO QUE FALLÓ FUE LO VISUAL.** El viernes 28 de agosto se demostró el sitio y el panel ante el directivo del capítulo, la dirección ejecutiva y la contadora. **Ninguna funcionalidad fue objetada**; toda la inconformidad es visual, de contenido y de encuadre — «lo visual es lo que tiene que ser, también muy impactante» — y el equipo reconoció en la mesa que «la parte visual la dejamos de último». El cierre fue un plazo del cliente: **una a dos semanas para levantar lo visual y volver a mostrarlo** (entre el 4 y el 11 de septiembre). La nueva **sección 27 manda sobre el orden de trabajo del producto** y es lo primero que debe leer una sesión nueva; el §26 sigue mandando sobre lo que no es código (cuenta institucional, despliegue, firmas) y su §26.4 sigue vigente entero. ⚠️ **Deja de ser cierto que «no queda trabajo de producto»** (§26): hay catorce señalamientos con archivo asignado en el §27.2 y cuatro ampliaciones que exigen acta en el §27.4. ⚠️ **Y tres cosas se demostraron como si existieran y no existen** (§27.3): que «toda la página es editable» —los títulos de la portada estaban cableados—, que el propietario sube y el gremio aprueba sus fotos —`/mi-cuenta` no tenía esa ruta— y el correo de confirmación al postulante, que no se enviaba. ✅ **Las tres existen desde el 31 de agosto de 2026**: `046895c` y `d79d1b8` (textos editables), `a803e3a` (carga y moderación de fotos) y `7eb0799` (acuse). Ver §30.2 — y ojo, que esta cabecera y el §27.3 no nombran el mismo trío. ⚠️ **Cambia un dato del expediente:** `p2-directorio` **ya está fusionada** a `main` (`fc028ad`).
>
> **v12 (25 ago 2026) — EMPIEZA POR AQUÍ. RF-60 CERRADO Y LA COMPAÑERA YA COMMITEA.** Cerrado **RF-60** —normativa vigente y decretos transitorios—, que era uno de los dos únicos requisitos funcionales sin ninguna cobertura: la guía normativa guarda ahora `verificado_el`, `verificado_con` y `vigente_hasta`; una ficha sin fechar se publica igual pero lo dice en su cara, y lo caducado desaparece por **las cuatro puertas** por las que sale la guía —lista, selector, sitemap y **descarga del formato**, que era la única con consecuencia de seguridad—. Suite de 791 a **820 casos** (809 pasan, 11 omitidas, 0 fallos, 2.904 aserciones). `main` en `bad0143`, empujado. ⚠️ **Dato nuevo que cambia un riesgo del expediente:** existe `origin/p2-directorio` con **tres commits de Ingrid**, con pruebas propias. Deja de ser cierto que su trabajo no pasa por el repositorio — pero **no está fusionada y hoy no fusionaría limpio** (cuatro conflictos, todos contra `09e3e33`, el pase de foco y objetivos táctiles). Ver §11 del estado del proyecto. ⚠️ **Y la lección de método de esta sesión, que confirma la del v7:** ocho tareas con revisión independiente encontraron **seis pruebas que pasaban sin ejercer lo que decían proteger**, y **ninguna era un defecto de producción**. Las cuatro justificaciones falsas las había escrito el propio plan. Ver §24.6.
>
> **v11 (19 ago 2026, tarde) — EMPIEZA POR AQUÍ. PASE DE INTERFAZ CERRADO Y DESPLIEGUE A UN COMANDO.** Sesión larga que cierra **cinco de las seis brechas** que el §23.12 daba por vivas y **los siete hallazgos** del pase de interfaz que el §22 tenía en pausa. Suite de **599 a 747 casos** (736 pasan, 11 omitidas, 0 fallos, 2.719 aserciones), 7 commits ya en `origin/main`, `main` en `f61a236`. Lo que hay que leer, en este orden: **§25** (qué se hizo, las trampas nuevas y qué toca ahora), y solo después las §20 a §23, que están corregidas pero cuentan historia, no estado. ⚠️ **Lo único que sigue bloqueando el proyecto no es técnico**: la cuenta institucional del gremio (R-14). Todo lo demás está escrito y esperando.
>
> **v10 (19 ago 2026) — FASE 4 CERRADA, SOLO QUEDA LO QUE EXIGE SERVIDOR:** todo el expediente de entrega existe y está commiteado en `docs/ingenieria/` (matriz de pruebas, manual con capturas, siete diagramas, base de datos exportada, medición de rendimiento e informe de cumplimiento en `.docx`). Las dos cifras que eran documentales están **verificadas**: la suite re-ejecutada (599 casos, 0 fallos, 1.699 aserciones) y el RNF-02 medido (**portada en 972 ms contra un techo de 2.500**, 78 mediciones sobre navegador real). Lee la **§23.11** y la **§23.12**; la §23.9 quedó superada y así está marcada. Lo único vivo es el hosting institucional (R-14) y lo que cuelga de él, más cuatro pendientes menores que la §23.12 enumera.
>
> **v9 (18 ago 2026) — EL CUELLO DE BOTELLA YA NO ES TÉCNICO:** revisión de estado con el repositorio, el cronograma firmado y los correos del docente asesor a la vista. El producto va dos o tres semanas por delante del cronograma y **las cuatro cosas que faltan no son código de producto**: hosting, manual de usuario, capacitación y documentación de entrega. El despliegue del §20 deja de ser tarea y pasa a **riesgo escalado a la junta (R-14)** — el bloqueo es la cuenta y el medio de pago institucionales, no un paso de ingeniería, y no se resuelve desplegando con cuenta personal. El pase de interfaz del §22 **queda en pausa**: ninguno de sus siete hallazgos vivos bloquea la entrega. La nueva **sección 23 manda sobre el orden de trabajo** hasta el 21 de agosto y es lo primero que debe leer una sesión nueva.
>
> **v8 (15 ago 2026) — HOSTING DECIDIDO, DESPLIEGUE EN ESPERA DE UN TRÁMITE HUMANO:** un consejo de decisión multiagente (cinco asientos con encargos distintos, prueba de fuga del expediente y refutación cruzada) eligió **Laravel Cloud** para el hosting de pruebas y como candidato definitivo, con una condición que es la mitad del veredicto: **la cuenta nace institucional, no personal**. El despliegue quedó bloqueado únicamente por el paso que un agente no puede dar — el registro/OAuth y el medio de pago son del dueño. **Toda sesión nueva: lee la sección 20 antes de proponer o tocar hosting.** El CLI de Cloud, su skill de despliegue y los certificados ya quedaron listos en esta máquina.
>
> **v7 · tres suposiciones sobre la API de Filament que resultaron falsas.** Cuestan una ronda entera cada una, así que conviene leerlas antes de escribir: `Panel::getAssets()` **no existe** en 4.12 (los assets se leen con `registerAssets()` + `FilamentAsset::getScripts()`); una página con `$view` propio **no debe** invocar `{{ $this->footerWidgets }}` a mano, porque el envoltorio `<x-filament-panels::page>` ya lo hace y llamarlo duplica cada widget; y `ChartWidget` **sí** sabe no dibujar, con `isEmpty()` y `getEmptyState()` nativos, sin necesidad de un `Widget` con vista propia.

---


## El §0 tal como estaba el 1 de septiembre de 2026 (sustituido por `estado.md` y por el prompt maestro nuevo)

## 0. PUNTO DE ENTRADA — léelo antes que nada (actualizado el 1 sep 2026)

**Este prompt ya se ejecutó. El proyecto existe, funciona, lleva 270 confirmaciones y está desplegado con contenido real del gremio.** Todo lo que viene después del §1 se escribió para construirlo desde cero a principios de agosto; hoy sirve como **referencia del encargo, del modelo de datos y de las reglas editoriales**, no como plan de trabajo. Si abres una sesión aquí, no estás arrancando: estás continuando un producto que el cliente ya vio, sobre el que ya reclamó, y que hoy sirve páginas en internet.

> 🟢 **Lo primero que tienes que saber, y contradice a casi todo lo que sigue:** el sitio **ya no devuelve 500** y **la base ya no está vacía**. `https://asobares-production-0jhdcz.laravel.cloud` responde **200** sobre PostgreSQL 17.11 con contenido oficial sembrado. **El §31 es el estado vigente**; el §29.7, el §30.4 y todo el §17 quedaron atrás. Y hay un inventario consolidado de qué está cumplido y qué falta en el **§31.2**: empieza por ahí antes de decidir en qué trabajar.

⚠️ **Puede haber otra sesión trabajando en este mismo directorio.** Ya pasó el 18 de agosto (§18.8) y volvió a pasar el 30. Antes de escribir, `GIT_OPTIONAL_LOCKS=0 git log --oneline -5` y mira el final de este documento: si hay una sección más nueva que la que creías última, léela antes de tocar nada.

### 0.1 Lo que hay que entender del proyecto en un minuto

Plataforma web del gremio de la vida nocturna del Quindío, construida como **práctica empresarial** de dos estudiantes. Eso significa que tiene **dos clientes con dos calendarios distintos**, y confundirlos es el error más caro que se puede cometer aquí:

- **El gremio (Asobares Quindío).** Le importa lo que se ve y lo que le sirve a sus afiliados. Su documento rector es el cronograma firmado, con **entrega dura el 22 de septiembre de 2026**. La dirección ejecutiva es la product owner; el directivo del capítulo manda sobre el producto.
- **La universidad (CUE).** Le importa el documento de práctica, que es **individual por estudiante** y se califica por presentación tanto como por contenido. El docente asesor no revisa código.

Un avance técnico impecable que llegue tarde al documento pierde el 60 % del corte. Un documento perfecto sobre un sitio que el gremio no aprueba pierde al cliente. Hay que servir a los dos.

### 0.2 Qué se está exigiendo ahora mismo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **Jue 3 – vie 4 sep, 11:59:59 pm** | 95 % del documento de práctica, en `.docx` con sus anexos | Docente asesor. Tarde = 0.0, sin excepción |
| **Entre el 4 y el 11 de sep** | **Nueva demostración con la capa visual levantada**, y sobre la URL pública si el despliegue ya está hecho (§29) | Directivo del capítulo |
| **7 – 11 sep** | Pruebas en dispositivos reales y corrección de lo que salga | Cronograma firmado |
| **14 – 18 sep** | Dominio, SSL, capacitación y Acta 02 firmada | Cronograma firmado |
| **22 sep** | **Entrega dura al gremio** | Cronograma firmado |

**El compromiso vivo es el segundo.** El 28 de agosto el gremio vio la plataforma por primera vez, **no objetó ninguna funcionalidad** y dejó catorce señalamientos, todos visuales o de contenido. El §27 los enumera con archivo y línea. ⚠️ **Pero ya no empieces por el bloque A: se agotó el 31 de agosto** (§30). De los catorce quedan OBS3-03 y OBS3-07, bloqueados en decisiones humanas, y las mitades de contenido de OBS3-10 y OBS3-11, que son insumo del gremio.

⚠️ **Y ya no lo bloquea el despliegue.** Esta línea decía «lo que hoy bloquea la demo es que el sitio desplegado devuelve 500»; **se resolvió el 1 de septiembre** (§31). La demo se puede hacer sobre la URL. **Lo que la bloquea ahora es el bloque D**, y ninguno de los cinco señalamientos vivos se cierra escribiendo código: tres son insumo del gremio (OBS3-07, 10 y 11), uno es una decisión de Natalia (OBS3-03) y uno está prohibido hasta releer el §9 (OBS3-09). El bloque C, aparte, espera la firma del Acta 04.

### 0.3 Qué falta, en tres frentes que no se estorban

1. **Producto — lo visual y lo de contenido.** Es lo único con fecha de cliente encima. Catorce señalamientos (§27.2) repartidos en cuatro bloques (§27.7): **A** son siete cambios de portada, tema, aliados, orden y fotografías, y es lo único que el directivo va a mirar; **B** son siete de contenido y reglas; **C** son cuatro ampliaciones que no se codifican sin acta; **D** son insumos que hay que reclamarle al gremio. ⚠️ **Superado el 31 de agosto** (§30.1): cerrados **OBS3-01, 02, 04, 05, 06, 08, 12, 13 y 14**; OBS3-10 y OBS3-11 con el código puesto y el contenido pendiente del gremio; OBS3-09 sigue cerrado solo en su mitad barata (§28.5) y su otra mitad la prohíbe el §27.8. Quedan bloqueados en decisión humana OBS3-03 y OBS3-07.
2. **Infraestructura — ✅ EN PIE desde el 1 de septiembre.** ⚠️ Este punto decía «desplegada y HOY ROTA»; ya no. El sitio **responde 200** sobre **PostgreSQL 17.11**, con las 39 migraciones aplicadas y la base sembrada con contenido oficial. La causa del 500 era la que `.env.staging.example` predecía palabra por palabra: `DB_CONNECTION` sin poner, cayendo al `sqlite` por defecto. **Manda el §31, no el §29.7 ni el §30.4.** El gremio abrió la cuenta de Laravel Cloud **institucional y con su medio de pago**, que era la condición del §20.2: **R-14 está cerrado**.

   **Lo que sigue abierto de este frente** (detalle y medición en §31.2): **SMTP sin contratar** —sin él no se demuestra el segundo factor ni sale el acuse al postulante, y sigue siendo el único bloqueo técnico con consecuencia visible—; el **bucket con la política del §8.3**, que arrastra el disco público del §30.3; el **dominio propio**; la **medición de rendimiento contra la URL**, porque los 972 ms del expediente son contra `localhost`; los **dispositivos reales**; y la **decisión de indexación**, que hoy está de hecho tomada al revés: `robots.txt` responde `Allow: /` y anuncia un sitemap de 14 URL.
3. **Académico.** El documento del 4 de septiembre está redactado, paginado y confirmado en el repositorio. Lo que falta es que su autor lo lea y lo ajuste **en voz propia**: una entrega anterior fue rechazada por uso evidente de IA. Ver §23.2 para lo que el docente ha exigido, textual y con fecha.

**Y una decisión que no es de código y bloquea el bloque C:** el `Acta 04 · Ampliación de alcance` ya está emitida en `docs/ingenieria/constancias/`, con una fila por petición (antes del 22 sep / Fase II / se descarta) y dos contrapropuestas del equipo. **Está sin firmar.** Mientras no vuelva firmada, OBS3-15 a OBS3-18 no se tocan — y dejar una fila sin marcar no las aplaza, las deja sin decidir.

### 0.4 Los problemas que te van a morder

Están todos documentados más abajo; esta es la lista corta para que los reconozcas antes de volver a pagarlos.

**De método, y son los que más cuestan:**

- **Falsos verdes.** En dos auditorías distintas aparecieron **once pruebas que pasaban sin ejercer lo que decían proteger**, y las escribió el propio autor del plan. Ninguna se detectó leyendo. La regla del proyecto es: **escribir la prueba no basta, hay que romper el código a propósito y ver el rojo.** Ver §7 de la cabecera y §24.6.
- **«La suite está en verde» tiene fecha de caducidad.** Lección del §28, y es distinta de la anterior: allí la prueba era correcta y el defecto real, pero **el calendario decidía si la prueba miraba o no**. Una prueba que depende de `now()` sin fijarlo no prueba lo que dice ningún día en particular. Si vas a citar una cifra de la suite, mídela hoy.
- **Ninguna cifra del expediente sale de una suma.** Casos de prueba, migraciones, rendimiento: se miden ejecutando, y sobre clon limpio si van a un documento.
- **El alcance está congelado desde el 14 de agosto.** La ausencia de una funcionalidad no es incumplimiento mientras no esté en el cronograma firmado ni en la ERS. Toda ampliación se registra por escrito **antes** de escribirse.

**De código, en orden de probabilidad de que te toquen:**

- **Contraste al tocar el hero.** El bloque A pide meterle imagen o video de fondo. Hay mediciones de contraste en el expediente que una imagen puede tumbar en silencio, y el propio cliente advirtió «no sea que afecte la visibilidad de las letras».
- **Chromium no reinicia una `transition` cuando lo que cambia es la custom property** que hay detrás. Con `transition-colors` repartido por el sitio, al cambiar de tema hay que apagar las transiciones y devolverlas (con respaldo de `setTimeout`, porque en pestaña de fondo no corre `requestAnimationFrame`). Cabecera, v5.
- **Restar meses desborda los días 29, 30 y 31.** `now()->subMonths(6)` un 30 de agosto da el 2 de marzo, no el 28 de febrero, y con un `startOfMonth()` detrás dos cubos distintos aterrizan en el mismo mes. Regla: **`startOfMonth()` primero y la resta después**; donde el límite no sea un inicio de mes, `subMonthsNoOverflow()`. Estuvo en once archivos y hacía que las purgas borraran datos personales **antes** del plazo publicado. §28.
- **`php artisan view:clear` antes de compilar para desplegar.** `app.css` escanea las vistas compiladas en caché, así que el tamaño del bundle depende de qué haya en `storage/framework/views/`.
- **`Paginator::$defaultView` es estático.** Livewire lo reapunta al renderizar una tabla del panel y no siempre lo restaura: una prueba de `/admin` puede romper otra del sitio público.
- **El observer de aprobación degrada *cualquier* guardado** de un registro publicado hecho por quien no puede publicar, mire el campo que mire. Si necesitas una excepción, hazla acotada y explícita; relajar la condición general reabre el agujero para los otros ocho modelos publicables (v6).
- **Autorizar `/mi-cuenta` con la habilidad `view` es una fuga de datos.** El portal se autoriza **solo por propiedad** (`verEnPortal`); `view` queda para el panel. Importa más que nunca, porque el §27.4 propone exponerle al afiliado datos de terceros.
- **Un plazo de retención en cero convierte la purga en «borra todo»**, y a cero se llega solo (variable vacía, `config:cache` viejo). El comando aborta si el plazo no es un entero ≥ 1.
- **`git status` normal deja un `index.lock` huérfano** que las sesiones remotas no pueden borrar. Usa siempre `GIT_OPTIONAL_LOCKS=0 git status`. Hay cuatro huérfanos en `.git/` esperando que alguien los borre a mano.
- **No muevas la escala tipográfica ni el reloj a `tokens.css`**: repinta 372 reglas de `/admin` en silencio. Hay una prueba que lo prohíbe.

**De datos personales, que es donde un error no se arregla con un commit:**

Las fichas de asociados **nacen en borrador** y no se publican sin autorización del titular. Las 19 fotografías del gremio tienen personas identificables y **no tienen autorización de imagen documentada**. `material/nuevomaterial/` está en `.gitignore` y ahí se queda. Y la mitad pendiente de OBS3-09 —que el afiliado consulte el banco de aspirantes— expone datos de terceros: **no se construye sin releer el §9**, y el §27.8 lo prohíbe expresamente.

### 0.5 Deuda que está diferida a propósito

No la «arregles de paso»: los chips de filtro repetidos y los 104 `leading-*`/`tracking-*` sueltos del §26.3 viven **en las mismas vistas que el bloque A va a rehacer**. Refactorizarlas antes de la demo es pagar el trabajo dos veces. Van después del 11 de septiembre.

### 0.6 Cómo verificar antes de decir que algo está hecho

1. `php artisan test --compact` con filtro sobre lo que tocaste; la suite entera antes de cerrar la sesión.
2. `vendor/bin/pint --dirty --format agent` si tocaste PHP.
3. `npm run build` si tocaste vistas o CSS, con `view:clear` antes.
4. `GIT_OPTIONAL_LOCKS=0 git status --short` para ver qué quedó suelto.
5. Y la que de verdad importa: **rompe a propósito lo que acabas de proteger y comprueba que la prueba se pone roja.**

El repositorio trae dos habilidades propias en `.claude/skills/` —`laravel-best-practices` y `tailwindcss-development`—; la segunda es de lectura obligada para el bloque A.

### 0.7 En qué orden leer el resto de este documento

- **§31 — PRIMERO.** Es el estado vigente: qué hay hoy en producción, y sobre todo el **§31.2**, que es el inventario consolidado de lo cumplido y lo que falta en los cinco frentes. Supera al §17 entero, al §29.7, al §30.4 y a las cifras del §26.1.
- **§27 y §28** — mandan sobre el trabajo de producto. El §27 son los catorce señalamientos del cliente, lo que se demostró y no existía, y el orden de trabajo hasta la próxima demo. El §28 es el defecto de calendario que salió al ejecutarlos y la lección que dejó. **El §28.6 sustituye al §27.9 como estado del árbol.**
- **§26** — manda sobre lo que no es código: la cuenta, el despliegue, las firmas. Su §26.4 sigue vigente entero.
- **§24** — el equipo son dos practicantes con fronteras de trabajo, y el §24.6 es la lección de los falsos verdes.
- **§4 y §9** — obligatorias antes de tocar diseño o datos personales, respectivamente.
- **§20** — obligatoria antes de proponer o tocar hosting.
- **§23.2** — lo que el docente asesor ha exigido, textual y con fecha.
- **§1 a §14** — el encargo original, ya corregido con lo aprendido. Referencia del modelo de datos, las rutas y las reglas editoriales. **No es un plan.**
- **§15 a §25** — historia. Cuentan cómo se llegó hasta aquí y qué trampas se pagaron. Se leen cuando algo no cuadra, no de entrada.

### 0.8 Estado medido del árbol (30 de agosto de 2026)

⚠️ **VIGENTE: el §31.7, medido el 1 de septiembre.** `main` en `493790d`, **270 confirmaciones**, 78 archivos de prueba, 39 migraciones, 21 sembradores, 5 comandos de Artisan, 66 vistas Blade. **Suite: 970 casos, 959 pasan, 11 omitidas, 0 fallos, 3.563 aserciones.** Lo de abajo es del 30 de agosto y se conserva como historia; la cifra intermedia del 31 de agosto (946 casos) está en el §30.1.

`main` en `6b0a20d`, **249 confirmaciones**. Árbol limpio. `origin/p2-directorio` **ya está fusionada**. 64 archivos de prueba, 35 migraciones, 19 recursos de Filament, 64 vistas Blade. (`.claude/settings.local.json` existe pero no aparece en `git status`: lo atrapa el `gitignore` **global** de la máquina, no el del proyecto. En otra máquina saldría como no seguido.)

**Suite re-medida el 30 de agosto por la tarde: 871 casos, 860 pasan, 11 omitidas, 0 fallos, 3.121 aserciones**, 274,7 s. ⚠️ **Y superada el 31: 946 casos, 935 pasan, 11 omitidas, 0 fallos, 3.502 aserciones.** No cites la duración: dos corridas del mismo código el mismo día dieron 253 s y 593 s. Confirma el §28.6 y **resuelve una contradicción del propio documento**: el encabezado del v14 y el aviso del §27.9 decían 870 casos y 3.115 aserciones, que era la cifra antes de añadir la prueba del acuse del §28.5. La vigente es 871. Las cifras de 820 que aparecen en las secciones anteriores son del 25 de agosto y **están superadas**.

⚠️ **Esta corrida vale más que una cualquiera:** el 30 es uno de los tres días en que el desbordamiento del §28 se manifestaba. Verde hoy significa que el arreglo se ejercitó en la condición que lo destapó, no que se midió un día tranquilo. Antes de citar cualquiera de estos números en un documento, vuelve a medirlos: es la lección del §28.4 y aquí es literal.

---


## Secciones de historia y estado (§15–§31, íntegras)

## 15. Auditoría de seguridad (3–4 ago 2026)

Se auditó el prototipo antes de conectar Bold con dinero real, porque la pasarela es lo siguiente en el cronograma y un fallo ahí cuesta dinero del gremio, no tiempo del equipo.

**Método:** seis auditorías en paralelo sobre el código real —pagos, autorización, entrada/XSS, archivos, configuración y datos personales— y una pasada de **refutación adversarial** sobre cada hallazgo, con la instrucción de intentar tumbarlo releyendo el código. **49 hallazgos en bruto → 43 confirmados, 6 refutados.** Ninguno quedó como crítico: los tres marcados así dependían de una variable de entorno mal puesta y no sobreviven a un despliegue correcto.

La suite pasó de **200 pruebas (193 pasan, 7 omitidas)** a **233 (226 pasan, 7 omitidas)**, todas verdes.

### 15.1 Lo que se cerró

| Grupo | Qué era | Dónde |
|---|---|---|
| **Firma de Bold** | El algoritmo estaba equivocado en dos pasos: ninguna notificación real habría pasado la validación | `app/Pagos/PasarelaBold.php` |
| **G1 — Cerrojo del driver** | Sin `PAYMENT_DRIVER` se activaba la pasarela simulada, y el webhook público aprobaba cualquier pago sin firma, sin CSRF y sin sesión | `config/pagos.php`, `PagosServiceProvider`, `PasarelaSimulada`, `WebhookBoldController`, `routes/web.php` |
| **G2 — Referencia y retorno** | Referencia de 24 bits enumerable; la página de estado era pública y mostraba el correo del inscrito | `Transaccion`, `PagoController`, `routes/web.php`, vista de estado |
| **G3 — Conciliación** | Un pago aprobado saldaba la cartera entera sin mirar el monto; el webhook no conciliaba nada | `RegistroDePagos`, `ResultadoDePago`, `Cartera::abonar()` |
| **G5 — Importador** | `1250.75` entraba como `125075`; una celda vacía ponía la deuda en cero en silencio; la plantilla CSV permitía inyección de fórmulas | `ImportadorDeCartera`, `ListCarteras` |
| **G6 — Archivos** | La extensión la elegía quien sube; los archivos nunca se borraban; los formatos "privados" estaban en `/storage` | `SubidaSegura`, `LimpiezaDeArchivosObserver`, `GuiaController`, `config/livewire.php` |
| **G7 (parcial) — XSS** | `JSON_UNESCAPED_SLASHES` permitía cerrar el `<script>` desde cualquier campo editable | componente `publico.json-ld` |
| **B6/B7 — Despliegue** | Producción podía arrancar con `APP_DEBUG=true`; el seeder reimponía un `super_admin` con contraseña publicada | `AppServiceProvider`, `bootstrap/app.php`, `UsuarioSeeder` |
| **Bug funcional** | `/afiliate` exigía un campo `tipo` que su formulario nunca enviaba: **toda solicitud de afiliación fallaba** | `GuardarMensajeRequest` |

### 15.2 Lo que queda

Ninguno es de severidad alta y ninguno bloquea conectar Bold.

> **Actualización (14 ago 2026):** el frente que cerraba estos pendientes se terminó y se commiteó — ver 19.4. **G4 y G9 quedaron cerrados**; **G8 en sus tres frentes** (MFA obligatoria, política de contraseñas, login de `/mi-cuenta` con mensaje genérico y bloqueo por cuenta; el login del panel sigue sin bloquear por cuenta, pero con el segundo factor obligatorio la contraseña sola ya no abre sesión); y **de G12 se cerró además la retención de mensajes** de contacto y PQR (`mensajes:depurar`, plazos en `config/retencion.php`). De G12 sigue abierto lo demás: evidencia del consentimiento (IP, agente, versión de la política), los encargados que la política no nombra, el plazo de retención de inscripciones, el canal de supresión a petición del titular y la vacante sin fecha límite que retiene postulaciones para siempre.
>
> **Actualización (15 ago 2026):** cayó el resto automatizable de G12 — los seis formularios guardan **evidencia del consentimiento** (IP, agente y versión de la política aceptada, capturada en el único trait del sello), las **inscripciones a eventos tienen plazo** (`inscripciones:depurar`, 24 meses desde que el evento termina; la transacción sobrevive por su `nullOnDelete`), la **antigüedad máxima absoluta** cierra el hueco de la vacante que nadie cierra (12 meses aunque siga abierta), y la política publica todos los plazos desde la configuración — incluido el aviso del portal que decía «seis meses» a mano. **De G12 solo sobrevive lo que exige decisión humana:** los encargados que la política no nombra (incluida la pasarela), el canal de supresión a petición del titular, y el texto revisado por quien responda legalmente por el gremio.

- **G4 — Cupos de eventos**: se consumen con inscripciones sin pagar y el conteo no está protegido contra concurrencia.
- **G8 — Autenticación**: la MFA del panel es opcional, no obligatoria; los dos logins limitan por IP pero nunca bloquean la cuenta atacada; el login de `/mi-cuenta` confirma con un mensaje distinto que una contraseña de administrador era correcta.
- **G9 — Flujo de aprobación**: el observer solo vigila la *entrada* a «publicado», así que **un subadmin sí puede despublicar**; y puede confirmar a mano una inscripción de pago, saltándose la regla de que solo la confirma una transacción aprobada.
- **G12 — Datos personales (Ley 1581)**: ~~no hay política de retención ni supresión~~ **cerrado en parte por la v6** para la bolsa —`bolsas:depurar` borra postulaciones y perfiles al vencer su plazo, y la política publica esos plazos y declara la transferencia al establecimiento—. **Queda abierto**: el consentimiento se sigue guardando sin evidencia (IP, agente, versión de la política aceptada); la política no nombra a los demás encargados que intervienen —incluida la pasarela—; inscripciones y mensajes no tienen plazo de retención; y no hay canal de supresión a petición del titular. **Queda además un hueco de la propia v6**: una vacante de tiempo completo sin fecha límite que nadie cierre nunca conserva sus postulaciones para siempre, porque el reloj de los seis meses arranca al cerrar o vencer. Se cierra purgando también por antigüedad absoluta de la postulación, o cerrando solas las vacantes sin movimiento.

### 15.3 Dos incógnitas que solo resuelve el sandbox de Bold

Antes de tocar credenciales reales hay que confirmarlas, porque la documentación pública no basta:

1. **Unidad de `expiration_date`**: el texto dice nanosegundos y el ejemplo de la propia documentación muestra milisegundos. El código envía nanosegundos.
2. **Nombre del campo del monto en la notificación**: se prueban tres formas conocidas. Si no aparece ninguna, la conciliación del punto 8 de la sección 8 **queda inerte** y solo lo delata un aviso en `storage/logs`. Hay que mirar el log en la primera prueba real.

### 15.4 Huecos que la auditoría declaró sin cubrir

- Exportaciones CSV/Excel de Filament en recursos con datos personales (Aspirantes, Inscripciones, Mensajes).
- Los ocho recursos de Filament no auditados a fondo más allá del patrón replicado de Asociados.
- La configuración real del servidor de producción, que aún no está elegido: decide si una extensión de archivo mal escogida es XSS almacenado o ejecución de código.
- El comportamiento de la caché de permisos de spatie tras un cambio de rol en producción, con `config:cache` activo.

---

## 16. Rediseño de las tres bolsas (4 ago 2026)

**Por qué se rehizo.** El encargo original modeló las bolsas como contenido que carga la oficina: la secretaría creaba las vacantes «a nombre de» un asociado, y postularse era un enlace de WhatsApp. Con eso la bolsa no se mueve —quien tiene la necesidad no tiene cómo publicarla—, no queda rastro de nadie, y una vacante de una noche vive publicada para siempre. La directiva la había puesto como prioridad número uno.

**Método.** Diseño conversado y aprobado antes de tocar código (`docs/superpowers/specs/`), plan de 22 tareas con código y pruebas escritas de antemano (`docs/superpowers/plans/`), y ejecución tarea por tarea con un agente implementador y un revisor independiente por tarea, más una revisión final de toda la rama. **30 commits. La suite pasó de 233 a 374 pruebas** (363 pasan, 11 omitidas, 0 fallos).

### 16.1 Lo que cambió

| Antes | Ahora |
|---|---|
| La oficina creaba las vacantes desde el panel | El asociado las publica y corrige desde `/mi-cuenta/vacantes`; el panel solo modera |
| Solo la dirección publicaba | La secretaría aprueba **las tres bolsas**; la dirección sigue aprobando lo que la secretaría redacta |
| Devolver no decía por qué | Devolver **exige motivo**, que se guarda, se manda por correo y el asociado lo ve en su cuenta |
| Postularse era un enlace de WhatsApp | La postulación queda en base ligada a su vacante y **avisa por correo** al establecimiento |
| `aspirantes` servía para dos cosas y hacía mal las dos | `postulaciones` (a una vacante) y `aspirantes` (banco de talento, una persona un registro) |
| Una vacante publicada vivía para siempre | Cierre manual («ya contraté») + fecha límite que la retira sola |
| El filtro era por texto libre | Filtro por **área del establecimiento**, que sí se puede agrupar |
| «Quiero ser proveedor» era un mensaje de texto libre | Formulario propio que crea la ficha en pendiente |
| Los datos de empleo se guardaban indefinidamente | `bolsas:depurar` los borra al vencer su plazo, configurable |

### 16.2 Lo que la revisión atrapó y conviene no repetir

Cinco de las siete rondas de arreglo salieron de revisiones independientes, no de pruebas que fallaran:

- Una **fuga de datos**: la vista de postulaciones del portal autorizaba con `view`, que concede por permiso o por propiedad; un directivo que además fuera dueño de un bar leía los candidatos de cualquier establecimiento.
- Un **plazo de retención en cero** convertía la purga en «borra todo», y a cero se llega solo con una variable de entorno vacía.
- El **consentimiento no cubría** la transferencia al establecimiento que la propia rama acababa de introducir.
- Un `down()` de migración que dejaba el rollback inservible, una **carrera de doble clic** que daba 500, un enlace roto a fichas sin aprobar, y cobertura de pruebas que se estaba **borrando en vez de afirmarse** cuando el comportamiento cambiaba.

### 16.3 Cabos sueltos conocidos

- El formulario de contacto **sigue ofreciendo** «Quiero aparecer en la bolsa de proveedores» (tipo `proveedor` de `mensajes`) pese a que la bolsa ya tiene formulario propio: dos caminos para lo mismo. Decidir si se retira del contacto.
- La cláusula de entrega a terceros aparece en **todos** los formularios públicos, incluidos afiliación y contacto, donde no hay ningún tercero. Para Ley 1581 la autorización debería ser específica por finalidad.
- El aviso de retención en el portal del asociado dice «seis meses» a mano, mientras la política lee el plazo de la configuración: divergen si se cambia la variable.
- La política no menciona que el WhatsApp e Instagram del artista y el contacto del proveedor **se publican en la web abierta**.
- Falta el texto real de la política revisado por quien responda legalmente por el gremio: lo redactó un agente describiendo el comportamiento del código.

### 16.4 Lo que quedó explícitamente fuera

Cuentas propias para artistas y proveedores (la columna `user_id` existe y nadie la usa), monetización de proveedores, versionado de ediciones, CV adjunto en PDF, cruce automático entre banco de talento y vacantes (la categoría de cargo lo deja facilitado), y consulta del estado de la postulación por parte del candidato —no tiene cuenta—.

---

## 17. PENDIENTE ANTES DE PRODUCCIÓN — Normatividad real y formatos oficiales

> ⚠️ **SUPERADA EN SU MAYOR PARTE el 1 de septiembre de 2026. El estado vigente es el §31.2.** Armenia ya no es contenido de demostración: son los siete trámites del documento oficial de la Alcaldía, fechados el 20 de agosto, sin un solo costo —porque el documento no trae ninguno— y con su fuente nombrada en cada ficha. Los PDF de relleno se retiraron. **Lo que de esta sección sigue vivo:** los otros once municipios, los formatos oficiales de cada entidad, los enlaces profundos al trámite (OBS3-10) y quién mantiene esto. El texto de abajo es el diagnóstico del 9 de agosto y se conserva porque explica por qué no bastaba.

**Nada del contenido normativo que hoy trae la plataforma sirve para orientar a un empresario de verdad.** Es material de demostración: existe para que la página insignia se vea viva en una presentación, no para que alguien abra un bar siguiéndolo.

### Qué hay hoy y por qué no basta

- `RequisitoAperturaSeeder` siembra los trámites por municipio (Cámara de Comercio, Alcaldía, Bomberos, Sayco-Acinpro, Secretaría de Salud, Policía) con descripciones, checklists y costos **aproximados y escritos a mano**. No están verificados contra la fuente oficial ni fechados.
- Los **formatos descargables son PDF generados por `Database\Seeders\Support\GeneradorPdf`**: archivos de relleno con la forma de un formato oficial, no los documentos reales de cada entidad.
- Los enlaces externos apuntan a los sitios institucionales correctos, pero a la portada, no al trámite concreto.

Publicar esto tal cual es peor que no tener la guía: alguien puede pagar por un trámite equivocado, presentar un formato que la entidad no reconoce, o creer que cumplió y recibir una visita de control. El descargo de responsabilidad («verifica siempre con la entidad») no cubre servir un formato inventado con el sello del gremio encima.

### Qué hay que hacer antes de que la guía salga a producción

1. **Recoger la normatividad real, municipio por municipio.** El alcance firmado es el Quindío: Armenia, Calarcá, Circasia, Filandia, Salento, La Tebaida, Montenegro, Quimbaya, Córdoba, Buenavista, Pijao y Génova. Los requisitos **difieren entre municipios** —el certificado de bomberos no cuesta lo mismo en Armenia que en Salento— y esa diferencia es justamente el valor del módulo.
2. **Conseguir los formatos oficiales de cada entidad**, en el archivo que la entidad publica o entrega. Sustituirlos por los PDF de relleno del seeder.
3. **Verificar costos y vigencias contra la fuente**, y dejar constancia de cuándo se verificó cada ítem. La tarifa de Sayco-Acinpro y el impuesto de industria y comercio cambian cada año: un dato sin fecha envejece sin avisar.
4. **Definir quién mantiene esto.** La normatividad se mueve; si nadie del gremio queda responsable de revisarla, la guía se vuelve desinformación con el tiempo. Conviene un campo de fecha de última revisión visible al público.
5. **Cargar el contenido real desde el panel**, no desde un seeder. Los seeders son para el demo; lo definitivo lo administra la secretaría en `/admin/requisitos`.

### Nota de origen legal

Los requisitos de apertura y funcionamiento de establecimientos de comercio en Colombia se apoyan, entre otros, en el Código Nacional de Seguridad y Convivencia Ciudadana (Ley 1801 de 2016), el Código de Comercio en lo relativo a matrícula mercantil, la normativa sanitaria del Invima y las secretarías de salud, la reglamentación de derechos de autor de Sayco-Acinpro, y los acuerdos y decretos **de cada municipio**, que son los que introducen las diferencias locales. **Esta lista es un punto de partida para la investigación, no una fuente citable**: hay que confirmar la norma vigente con cada entidad antes de publicarla.

---

## 18. ESTADO DEL PROYECTO (9–10 ago 2026)

**Léelo antes de tocar nada.** Esta sección existe para que una sesión nueva sepa en qué punto está el proyecto sin tener que reconstruirlo del historial. La escribió la sesión del Observatorio con trabajo a medias en el árbol y la actualizó la del 10 ago al cerrarlo; **el resumen de qué quedó vivo está en la sección 19**, que es la más corta y la que conviene leer primero.

### 18.1 Qué está terminado y fusionado a `main`

**Panel administrativo, fases F1–F3** — 39 commits, suite de **374 → 458 pruebas** (447 pasan, 11 omitidas, 0 fallos).

| Pieza | Dónde |
|---|---|
| Tokens de color compartidos entre sitio y panel | `resources/css/tokens.css`, importado por `app.css` y por el tema del panel |
| Tema propio de Filament, con Poppins de verdad | `resources/css/filament/admin/theme.css` + `->font()` y `Vite::fonts()` |
| Componentes reutilizables | `resources/views/components/panel/{vidrio,kpi,cola}.blade.php` |
| Gráficas que siguen el tema | `resources/js/panel-graficas.js` (plugin global de Chart.js) |
| Cola de pendientes por policy, no por rol | `app/Panel/ColaDePendientes.php` (singleton, memoizado por usuario) |
| Tablero propio de tres bandas | `app/Filament/Pages/Dashboard.php` + widgets |
| Conteo anónimo de consultas a la guía | tabla `consultas_guia`, sin IP ni agente ni sesión |
| Semilla con forma temporal | 18 meses de mensualidades con estacionalidad, coherencia entre afiliación, cartera e historial |

Documentos: `docs/superpowers/specs/2026-08-05-panel-administrativo-design.md` y `docs/superpowers/plans/2026-08-05-panel-f1-f3.md`.

### 18.2 Observatorio del gremio — FUSIONADO a `main` el 10 ago 2026

Plan en `docs/superpowers/plans/2026-08-09-observatorio.md`; el registro de ejecución, con todos los hallazgos y decisiones, en `.superpowers/sdd/2026-08-09-observatorio/progress.md` (git-ignorado, pero es el mapa de recuperación).

Las ocho tareas del plan, la ola de arreglos posterior y **la re-revisión de esa ola** están cerradas. La rama `observatorio` se fusionó por avance rápido y se borró. Suite completa sobre `main`: **508 pruebas, 497 pasan, 11 omitidas, 0 fallos.**

**La re-revisión encontró cinco cosas, y conviene saber cuáles porque dos eran del tipo que este proyecto ya pagó caro** (commit `e072d33`):

- **Un Crítico de prueba en falso verde.** El arreglo del color de «Otros» estaba custodiado por una aserción que prohibía UNA cadena. Repintar la barra de blanco puro —contraste 1.0:1, peor que el bug original— la dejaba en verde.
- Esa prueba, rehecha para medir contraste WCAG de verdad, destapó **tres barras por debajo de 3:1 sobre el fondo oscuro**, y al generalizarla a las seis gráficas apareció que **tres widgets distintos habían cableado la misma paleta a mano**, cada uno por su lado.
- **El umbral se le exigía a cada conjunto también cuando los conjuntos son rebanadas de una sola medida.** Demanda laboral pedía 30 vacantes en cada una de sus siete áreas —210— y «Otros», que es un cajón residual, no las tendría nunca: la gráfica no podía dibujar jamás mientras el módulo anunciaba un umbral de 30.
- **La frontera de esa regla no estaba probada** (los casos usaban 6 y 30): un off-by-one sobrevivía con la suite verde.
- **El estado vacío se contradecía en pantalla:** «hoy hay n = 762 registros y hacen falta al menos 30».

Lección, que es la misma de siempre: **las cinco aparecieron mutando, ninguna leyendo.**

**⚠️ El módulo cambió de aspecto tras la ola de arreglos, y hay que decidir si así se enseña.** Antes dibujaban tres de seis gráficas; **ahora dibuja una sola** (salud financiera, n=167). Las otras cinco muestran el estado vacío honesto, incluidas dos que antes dibujaban:

- **Composición del sector** tenía `n = 24` y dibujaba **sin aviso**, mientras la tarjeta KPI y el informe impreso marcaban ese mismo dato como «muestra pequeña». El plan la había clasificado mal como «sólida» y nadie volvió a mirarla.
- **Presencia por municipio** sumaba tres señales heterogéneas en un solo `n` (24 asociados + 6 vacantes + 732 consultas = 762) y con eso se sellaba «suficiente», pero el 96 % venía de una serie. Ahora el umbral se le exige **al conjunto más flaco, no a la suma**, que es lo correcto: una serie robusta no puede prestarle credibilidad a dos que no la tienen.

Los dos arreglos son correctos y restauran el principio del módulo. Pero **un observatorio que enseña cinco de seis paneles diciendo «aún sin muestra suficiente» es una decisión de producto**, no solo técnica, y conviene confirmarla antes del 22 de septiembre. Las alternativas honestas son: enseñarlo así y explicar que la herramienta ya mide y falta que el sector alimente; o sembrar una bolsa de empleo con volumen realista para que las series tengan sustancia.

### 18.3 Decisiones del dueño que gobiernan este trabajo

- **Objetivo: demo para la directiva del 22 de septiembre.** Prioriza impacto visual y que las gráficas se vean vivas, pero nada de lo construido debe tirarse después.
- **Identificadores y comentarios en español.** `CLAUDE.md` se contradice a sí mismo sobre esto; el dueño resolvió el 5 ago 2026 que gobierna la convención existente del código. **No es un defecto y no debe reportarse como tal.**
- **Bicromático con los dos temas al mismo nivel.** El vidrio esmerilado se hace con **luz** en oscuro y con **sombra y borde** en claro: son dos recetas, no una opacidad compartida.
- **Sin dependencias nuevas.** El informe del observatorio se produce con CSS de impresión y lo convierte el navegador, en vez de añadir una librería de PDF.
- **El «mapa de calor» son barras ordenadas, no Leaflet**: los municipios no tienen coordenadas y el componente de mapa del sitio publica sus assets en stacks que el panel no tiene.
- **Las visualizaciones sin muestra no dibujan.** Con 7 vacantes en 7 áreas, dibujar barras sugiere una tendencia inexistente. El umbral es 30 (`SerieDelObservatorio::MUESTRA_MINIMA`), compartido con la tarjeta KPI.
- **La tasa de mora se queda en la banda de cabecera** con su `n = 24` y su rótulo «muestra pequeña». Es la cifra más incómoda del gremio en primera fila, diciendo con cuántos datos se sostiene.

### 18.4 El principio que ordena el Observatorio

**Ninguna cifra se presenta sin su n.** No es un adorno: el módulo existe para que la dirección lleve datos a una alcaldía, y un porcentaje sin el número de observaciones detrás no aguanta la primera pregunta. Hoy **cinco de las siete métricas no alcanzan muestra suficiente**, y la interfaz lo dice en cada una, no en un descargo genérico al pie.

Ese principio ya se violó una vez dentro del propio módulo y conviene saber cómo: la tasa de mora vivía dentro de `saludFinanciera()` como línea plana repetida en dieciocho puntos. Dos problemas — una recta junto a una curva se lee como tendencia, y **heredaba el `n` de transacciones (160) cuando el suyo es 24**, así que salía sellada como «muestra suficiente» siendo pequeña. Está separada en `tasaDeMoraActual()` por eso.

### 18.5 Un cambio ajeno guardado en stash

El 9 ago 2026 aparecieron en el árbol dos archivos modificados que **no pertenecen a este plan**: `app/Filament/Resources/Asociados/Schemas/AsociadoForm.php` y `tests/Feature/SubidaDeImagenesTest.php`.

Es un **arreglo de seguridad real y correcto**: el campo de galería sube por Spatie MediaLibrary, que trae su propio nombrador y **no hereda la defensa de `SubidaSegura`**, así que la extensión la elegía quien sube y un JPEG llamado `payload.html` habría quedado servido como HTML desde `/storage`. Es el hallazgo **G6 de la sección 15** en el único campo que se le escapó entonces. Con el arreglo, `SubidaDeImagenesTest` pasa 11/11.

**Recuperado y commiteado el 10 ago 2026** en `a8c02ee`, con commit propio como estaba previsto. Ya no hay nada guardado en stash. Se queda escrito aquí porque explica por qué ese arreglo llegó suelto y sin plan detrás.

### 18.6 Deuda conocida que quedó anotada

De la revisión final de F1–F3, ninguna bloqueante, todas con su sitio:

- **`<x-panel.kpi>` no tenía consumidor en producción** hasta que el observatorio estrenó su banda de KPIs. Si el observatorio se descarta, el componente vuelve a quedar huérfano.
- ~~**La convención de `ticks`/`grid` vacíos en los `ChartWidget` es load-bearing y frágil**: el plugin de tema solo escribe donde ya hay clave. Hay pruebas que lo vigilan **por eje** en los widgets del observatorio, pero no en los del tablero.~~ **CERRADO el 30 ago 2026** (v14): `RanurasDelPluginDeTemaTest` gana una segunda guardia que exige `ticks` y `grid` en **todo eje de todo `ChartWidget`**, barriendo el mismo directorio que su hermana, así que una gráfica nueva entra sola. Verificada por mutación. La convención sigue siendo frágil —el plugin no inventa claves, solo escribe donde las hay—, pero ya no depende de que alguien se acuerde.
- **Duplicación entre los dos guardianes de tema** (`TemaClaroOscuroTest`, sitio y panel): ~28 líneas literales.
- ~~**`RequisitoAperturaFactory`** usa el string crudo `borrador` donde su hermana usa el caso del enum.~~ **CERRADO el 30 ago 2026** (v14).
- **El singleton de `ColaDePendientes` habilita staleness intra-petición** si el rol de un usuario cambiara entre `canView()` y el render. No explotable hoy; documentado en `AppServiceProvider`.
- **`AsociadoSeeder`** lanzaría `ValueError` si algún día `CarteraSeeder::EN_MORA` ganara un slug con mora ≥ 22 meses.
- **`Bitacora` y `AjustesDelSitio`** arrastran un `abort_unless` redundante con un comentario que dice que es el que cierra la puerta; el guardián real es el trait `CanAuthorizeAccess` de Filament.
- **La corrida contra PostgreSQL que pide el spec §7** — **hecha el 14 ago 2026** contra PostgreSQL 17 en Docker: suite completa **559 pruebas, 548 pasan, 11 omitidas, 0 fallos**. Las expresiones por motor de `RecaudoMensual` y `MetricasDelObservatorio` pasaron sin cambios; lo que cayó fue otra cosa, dos veces: la columna `data` de `notifications` era `text` y Filament la consulta con `->>` (casi todos los 88 fallos de la primera pasada; ahora es `json`, que en SQLite compila a TEXT y no cambia nada), y el catch de la violación de unicidad al postular moría con `25P02` bajo el envoltorio transaccional de las pruebas — la inserción va ahora en un savepoint y el catch usa `updateOrCreate`. **La expresión de `mysql` sigue sin estrenarse.**

### 18.7 Cómo se está trabajando, por si retomas

Método: **diseño conversado y aprobado antes de tocar código** (`docs/superpowers/specs/`), **plan con el código y las pruebas escritos de antemano** (`docs/superpowers/plans/`), y ejecución tarea por tarea con un implementador fresco y un **revisor independiente por tarea**, más una revisión de toda la rama al final.

Lo que hace que funcione no es la revisión en sí, es **la mutación**: el revisor rompe el código a propósito y comprueba si la prueba se entera. Cinco de los fallos más caros de estas dos fases eran pruebas que pasaban con el bug reintroducido, y ninguna se detectó leyendo.

⚠️ **El panel del navegador de esta sesión no compone fotogramas** —las capturas fallan con «the page is not compositing frames»—, así que **nadie ha visto Chart.js pintar de verdad**. Lo verificado es estructural: el JSON de opciones que llega al cliente, los tokens computados, el DOM. Si tu sesión sí puede componer, **mira las gráficas del observatorio y del tablero en los dos temas**: es la única verificación que falta en todo este trabajo.

### 18.8 ⚠️ HAY OTRA SESIÓN TRABAJANDO EN ESTE MISMO DIRECTORIO

> **Actualización (14 ago 2026): frente cerrado y commiteado.** El trabajo descrito abajo se terminó y entró a `main` en once commits temáticos; ya no hay nada ajeno sin commitear en el árbol. La advertencia de los 52 rojos quedó obsoleta: quien cerró el frente dio de alta el segundo factor por omisión en `UserFactory` y en `UsuarioSeeder`, así que `PanelCompletoTest` pasa sin tocarlo. Suite completa con todo dentro: **558 pruebas, 547 pasan, 11 omitidas, 0 fallos.** Lo que sigue se conserva como registro.

**Léelo antes de correr la suite o de creerte un fallo.** El 9 ago 2026, mientras se construía el Observatorio, apareció en el árbol de trabajo un segundo frente **sin commitear**, de otra sesión, que está cerrando los pendientes **G4–G12 de la sección 15.2**. No es basura y no hay que borrarlo.

Lo que hay sin commitear, por lo que se ve en los archivos:

| Frente | Archivos |
|---|---|
| MFA obligatoria del panel (G8) | `AdminPanelProvider` con `isRequired: true`, `LoginDelPanelTest`, `PoliticaDeContrasenasTest` |
| Cabeceras de seguridad | `app/Http/Middleware/CabecerasDeSeguridad.php`, su prueba, `bootstrap/app.php` |
| Cupos de eventos (G4) | `EventoController`, `ConfirmacionDeInscripcionObserver`, `CuposDeEventoTest` |
| Retención de datos (G12) | `config/retencion.php`, `DepurarMensajes`, `DepuracionDeMensajesTest`, `routes/console.php` |
| Flujo de aprobación (G9) | `FlujoDeAprobacionObserver`, su prueba |
| Pagos | `PasarelaBold`, `PasarelaSimulada`, `RegistroDePagos`, `FlujoDePagoTest` |
| Login de asociado | `SesionAsociadoController`, `LoginDeAsociadoTest`, `config/session.php` |

**Consecuencia práctica, y es la que importa:** con ese trabajo en el árbol, **`php artisan test` da 52 fallos** — todos en `PanelCompletoTest`, todos `302` porque la MFA obligatoria cambia el flujo de login que esas pruebas asumen. **No son del Observatorio.** Quedó demostrado el 10 ago 2026: guardando esos 33 archivos en stash, la suite completa dio **508 pruebas, 497 pasan, 0 fallos**; devolviéndolos al árbol, vuelven los 52.

Si retomas y ves la suite en rojo: **primero mira `git status`**. Si esos archivos siguen sin commitear, el rojo probablemente no es tuyo. Verifica tu trabajo corriendo solo tus archivos, y no intentes «arreglar» `PanelCompletoTest` — le toca a quien esté haciendo la MFA obligatoria, que además tendrá que actualizar esas pruebas para que afirmen la regla nueva.

**No stashees ese trabajo sin hablarlo.** Son 33 archivos de otra persona a medio camino. El 10 ago 2026 hubo que hacerlo para poder correr la suite limpia, **con permiso explícito del dueño**, y se devolvieron al árbol intactos al terminar (verificado archivo por archivo y línea por línea contra un inventario tomado antes). Si te toca repetirlo: toma el inventario primero (`git status --short` y `git diff --stat` a un archivo aparte), usa `git stash push -u` con un mensaje que diga de quién es, y compara al devolverlo.

---

## 19. ESTADO AL CERRAR EL OBSERVATORIO (10 ago 2026)

El encargo que dejó escrito la sesión anterior —re-revisar la ola de arreglos, correr la suite limpia, fusionar y recuperar el stash— **está hecho**. `main` va por `a8c02ee`, sin ramas vivas y sin nada en stash.

| Paso | Resultado |
|---|---|
| Re-revisión de `168ce93..7778ce3` | Cinco de siete arreglos aguantaron la mutación; dos se cayeron. Cinco hallazgos, cerrados en `e072d33` — ver 18.2 |
| Suite completa sobre árbol limpio | **508 pruebas, 497 pasan, 11 omitidas, 0 fallos** |
| Fusión y stash | Avance rápido, rama borrada, arreglo de galería en commit propio (`a8c02ee`) |

### 19.1 La decisión de producto sigue pendiente, y sigue sin ser del agente

**De las seis gráficas del observatorio solo dibuja una** (salud financiera, n = 173). Las otras cinco muestran «Aún sin muestra suficiente», y es correcto: sus muestras no sostienen lo que dibujarían.

El arreglo del umbral por rebanadas **no cambió esto**. Demanda laboral ya no exige 210 vacantes sino 30, pero hoy hay 7: sigue sin dibujar. Lo que cambió es que ahora *puede* dibujar algún día; antes no podía nunca.

Es honesto, y es exactamente lo que el módulo prometía. **También es un observatorio que enseña cinco paneles vacíos el día de la presentación ante la directiva.** Las dos salidas honestas:

- **Enseñarlo así**, explicando que la herramienta ya mide y lo que falta es que el sector alimente el dato. Es defendible y distingue esto de un tablero de vanidad.
- **Sembrar una bolsa de empleo con volumen realista** —del orden de 60–80 vacantes repartidas en 18 meses y por área, más aspirantes proporcionales— para que las series tengan sustancia. Son datos ficticios sobre un mercado laboral que no existe todavía, y eso hay que tenerlo claro antes de elegirlo.

**No lo decidas tú.** Pregúntaselo al dueño antes del 22 de septiembre.

### 19.2 La verificación que sigue sin hacerse, y que ahora importa más

**Nadie ha visto Chart.js pintar de verdad.** El panel del navegador de las sesiones que construyeron esto no compone fotogramas («the page is not compositing frames»), así que lo verificado es estructural: el JSON de opciones que llega al cliente, los tokens computados, el DOM.

Eso ya estaba anotado en 18.7, pero **desde el 10 ago 2026 pesa más**: los colores de las gráficas categóricas ya no salen del servidor, los escribe `panel-graficas.js` leyendo `--asb-serie-N` en cada pintado y en cada cambio de tema. La cadena entera —token → plugin → `dataset.backgroundColor`— está probada por sus extremos (los tokens tienen prueba de contraste; los widgets tienen prueba de ranura y de reserva), pero **el eslabón de JavaScript no tiene prueba automática**: este proyecto no tiene infraestructura de pruebas de JS.

Si tu sesión puede componer fotogramas, esto es lo primero que vale la pena mirar: **abre el observatorio en los dos temas y comprueba que las siete barras de demanda laboral y las tres de presencia por municipio se distinguen del fondo y entre sí.** Necesitarás sembrar vacantes para que la gráfica dibuje.

> **Actualización (14 ago 2026): hecha, y encontró dos bugs reales apilados.** El panel del navegador seguía sin componer fotogramas, pero `getImageData` sobre el canvas no lo necesita: la verificación se hizo leyendo los píxeles pintados por JavaScript, con datos sembrados en una base temporal (respaldada y restaurada). Lo que apareció, ambos corregidos y commiteados ese día: **(1)** las ranuras vacías `'ticks' => []` de los ocho ChartWidget llegaban a Chart.js como *arrays* JSON —en PHP no hay diferencia entre `[]` y `{}`, en JavaScript sí— y el primer `update()` tras la creación moría sin capturar con `setContext is not a function`, dejando la gráfica congelada; ahora van como objetos (`App\Panel\RanuraDeTema::vacia()`) y la guardia `RanurasDelPluginDeTemaTest` recorre todos los ChartWidget presentes y futuros mirando el **JSON serializado**, que es donde la diferencia existe. **(2)** El repintado del cambio de tema usaba `update('none')`, un modo directo en el que Chart.js no vuelve a fusionar las opciones de los elementos: los rellenos que `beforeUpdate` escribía en el dataset no llegaban a las barras y cada serie conservaba la paleta del tema anterior — Seguridad, casi negro en claro, quedaba invisible sobre el fondo oscuro. Ahora es `update()` en modo normal (sin animación: Filament fija `duration: 0`). Con ambos arreglos, la cadena completa token → plugin → dataset → elemento → **píxel** quedó verificada: las siete barras de demanda laboral y las tres series de presencia pintan cada una su token exacto en los dos temas y en ambos sentidos del cambio. Ninguna de las pruebas estructurales podía ver ninguno de los dos: `assertArrayHasKey` no distingue los dos vacíos, y el segundo solo se ve midiendo píxeles.

### 19.3 Deuda nueva que dejó este trabajo

- **La paleta de marca no da para siete categorías en los dos temas.** La banda que supera 3:1 sobre blanco Y sobre casi-negro contiene solo cinco de sus colores. Por eso son dos paletas. La consecuencia es que en el tema oscuro hay tres grises juntos (`#d0cccd`, `#a8a3a5`, `#7d7779`) y dos rojos (`#ee4137`, `#d9313a`) más próximos entre sí de lo que están sus equivalentes en claro. Se distinguen, pero si algún día el observatorio necesita una octava categoría, la paleta no la tiene: hay que ampliar el manual de marca, no inventar un hexadecimal.
- **`--asb-serie-N` es la primera paleta del proyecto que vive a medias entre CSS y JS.** El servidor manda un color de reserva y el cliente lo pisa. `ObservatorioTest` ata la reserva al token de `:root` para que no diverjan, pero nada obliga a que un widget nuevo use `relleno()`: la prueba solo exige ranura a las gráficas de **más de una serie**. Una gráfica nueva de una sola serie puede cablear un color sin que nadie chiste, igual que hacían las tres que había.
- **Lo que sigue sin tocarse de 18.6**: la duplicación entre los dos guardianes de tema y el resto de la lista. La corrida contra PostgreSQL ya no está aquí: se hizo el 14 ago 2026 con dos hallazgos corregidos — ver la actualización en 18.6; solo la expresión de `mysql` sigue sin estrenarse.

### 19.4 Lo único que queda vivo en el árbol

**Ya nada (14 ago 2026).** El frente G4–G12 que vivía aquí sin commitear se terminó y entró a `main` en once commits temáticos: MFA obligatoria, política de contraseñas, login de asociado, cabeceras de seguridad, cupos de eventos, confirmación de inscripción, despublicación vigilada, conciliación de pagos fallando cerrada, retención de mensajes, filtrado de enlaces de ajustes y cookie de sesión `Secure`. La suite completa con ese trabajo: **558 pruebas, 547 pasan, 11 omitidas, 0 fallos.** En el árbol solo queda `_to_delete/` (dos tarballs de empaquetado, sin trackear). Lo vivo ahora es otra cosa: **83 commits sin push** — `origin/main` se quedó en el 5 de agosto y todo lo posterior existe solo en este disco. *(Actualización del mismo 14–15 ago: el push se hizo y `_to_delete/` se borró; el remoto quedó al día.)*

---

## 20. Despliegue en Laravel Cloud — decidido el 15 ago, PREPARADO el 19 ago, pendiente de la cuenta

> ⚠️ **Esta sección conserva la DECISIÓN y su porqué, que siguen en pie. Lo que ya no describe es el estado.** La «tarde de trabajo» que anunciaba abajo se hizo el 19 de agosto: la aplicación corrió entera contra PostgreSQL 17.11 real, existe `docs/ingenieria/runbook-despliegue.md` con la secuencia literal, existe `.env.staging.example`, y el almacenamiento de archivos quedó probado contra almacenamiento de objetos. **La checklist del §20.5 está superada por el runbook, que es más largo y está verificado — sigue el runbook, no la checklist.** Lo único que falta es el §20.3, que no es técnico. Detalle en el §25.
>
> Corrección a lo que dice el §20.5: **el punto 5 es peligroso tal cual está escrito.** «`migrate --seed` remoto» daría por bueno sembrar datos de demostración contra la base del servidor; hasta el 19 de agosto solo `UsuarioSeeder` se negaba en producción y los otros diecinueve habrían publicado establecimientos inventados en el directorio real. Ya está cerrado con una guardia en bloque, pero lee el §9 del runbook antes de sembrar nada remoto.

**Recordatorio para toda sesión nueva: la decisión de proveedor está tomada.** No la reabras salvo que se cumpla alguno de los falsadores del §20.6; lo que falta es un trámite humano.

### 20.1 La decisión y su porqué

Un consejo de decisión multiagente (cinco asientos con funciones objetivo distintas, expediente pasado por prueba de fuga, ronda de refutación cruzada; tres modelos distintos, asiento de evidencia con búsqueda web real del 15 ago 2026) eligió **Laravel Cloud, plan Starter**, para el hosting de pruebas y como candidato definitivo. Confianza alta: los asientos convergieron.

- **El lock-in que se temía no existe:** la app es un monolito portable con migraciones verificadas en SQLite y PostgreSQL 17; salir cuesta 2–8 horas técnicas y no hay punto de no retorno contractual. Lo que sí ata para siempre es **una cuenta a nombre equivocado** — por eso la condición de abajo.
- **El recurso escaso del gremio no es el dinero, es el operador:** las opciones difieren en menos de US$15/mes pero en un orden de magnitud en horas (VPS: 15–30 h de arranque + 24–60 h/año que nadie del gremio va a poner).
- **Evidencia fresca (15 ago 2026):** Starter = US$5/mes con US$5 de uso incluidos y primer mes gratis; desde jun 2026 trae límites de gasto que **pausan** el cómputo en vez de facturar, e hibernación de toda la pila con el scheduler corriendo. Oracle Free quedó descartado (recortes sin aviso y terminación de instancias desde el 18 ago 2026); Railway/Render/Fly exigen Dockerfile que el repo no tiene. Ley 1581: EE. UU. tiene nivel adecuado (Circular 005/2017 SIC) — la transferencia es legal; queda pendiente de G12 quién firma como responsable del tratamiento.

### 20.2 La condición que es mitad del veredicto

**La cuenta de Laravel Cloud nace institucional, no personal.** La autopsia del consejo fue unánime en el modo de muerte: cuenta y tarjeta del practicante → la demo sale bien → nadie migra lo que funciona → la práctica termina y las llaves se van → al primer evento operativo el gremio reconstruye desde cero. Concreto:

- Correo de la cuenta / facturación: `asobaresquindio@asobares.org` (no el personal del desarrollador).
- Medio de pago: del gremio. Si por urgencia se usa uno personal, queda anotado aquí como deuda con fecha de traspaso.
- Límite de gasto configurado el día uno (~US$10/mes) — ese número, no el piso de US$5, es el que se le presenta a la junta como techo.
- El repo `Jsua3/asobares` vive en cuenta personal de GitHub: recomendado moverlo a una organización con un segundo administrador del gremio, o al menos añadir uno.

### 20.3 El paso humano bloqueante (por esto no está hecho)

El registro/inicio de sesión en cloud.laravel.com y el ingreso del medio de pago **los debe hacer el dueño** (un agente no debe autenticar ni tocar datos de pago). En una terminal interactiva: `& "$env:APPDATA\Composer\vendor\bin\cloud.bat" auth` — abre el navegador, se autoriza, y el token queda en `~\.config\cloud\config.json` para que el agente continúe.

### 20.4 Lo que ya quedó listo en esta máquina (15 ago 2026)

CLI de Laravel Cloud instalado global (`laravel/cloud-cli` ^0.5, binario en `%APPDATA%\Composer\vendor\bin`); skill `deploying-laravel-cloud` instalada en `~/.claude/skills` (usar sus combos de flags: `-n` siempre, `--json` en lecturas); certificados CA arreglados en `php.ini` (curl.cainfo/openssl.cafile → bundle de Git); `pdo_pgsql` habilitado y la suite completa validada contra PostgreSQL 17 — la base de Cloud es Serverless Postgres, justo el motor probado.

### 20.5 Checklist de despliegue (para la sesión que lo retome, tras 20.3)

1. `cloud ship -n` desde el repo (app + entorno de pruebas + Postgres de Cloud). Región US East.
2. Variables: `APP_ENV=staging`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `PAYMENT_DRIVER=bold` (la pasarela simulada se niega fuera de local/testing — los pagos NO se demuestran en este hosting, por diseño), `MAIL_MAILER` según el punto 3.
3. **Correo saliente:** Laravel Cloud no lo incluye. Sin proveedor (Resend/Postmark/SES, cuenta del gremio), los códigos MFA del panel mueren en el log y el login no es demostrable ante la dirección; mientras no exista, los códigos se leen con `cloud environment:logs`.
4. Límite de gasto desde el panel o CLI antes del primer despliegue público.
5. `migrate --seed` remoto (los seeders demo corren en staging, se niegan en production — por eso staging), smoke test de rutas públicas y del login, y la URL a la dirección.
6. Anotar aquí la URL, el entorno y a nombre de quién quedó todo.

### 20.6 Falsadores (cuándo sí reabrir la decisión)

- Dos facturas seguidas > ~US$15/mes con límite de gasto puesto y sin crecimiento de tráfico → reevaluar PaaS genérica con Dockerfile.
- Otra caída total de Laravel Cloud que afecte aplicaciones (la única seria: 20 feb 2026, 3h15m).
- El gremio no logra aportar correo/medio de pago institucional en dos semanas → el problema es de gobierno, no de proveedor: escalarlo a la junta como riesgo del proyecto, no resolverlo desplegando con cuenta personal.

---

## 21. CERRADO — Rework de movimiento del frontend (17 ago 2026)

Fusionado a `main` en `301cf55` (avance rápido, 45 archivos, +1157/−479), rama borrada. Suite: **597 pruebas, 586 pasan, 11 omitidas, 0 fallos** (creció desde 578 sin perder ninguna). La especificación vive en `docs/superpowers/specs/2026-08-17-movimiento-del-frontend-design.md` y el plan ejecutado —con sus cinco enmiendas commiteadas y explicadas— en `docs/superpowers/plans/2026-08-17-movimiento-del-frontend.md`. Léelos antes de tocar movimiento: los rechazos del §10 de la spec (scroll-reveal, hero, navegación, cambio de tema…) son deliberados y no se reabren en revisión.

### 21.1 El sistema, en cinco líneas

- **Fuente única en `tokens.css`**: curvas `--ease-out/-in-out/-cajon/-color` en `@theme` (pisan las utilidades nativas de Tailwind: cada `ease-out` del proyecto usa ya la curva propia) y `--duracion-instante/boton/salida/entrada/panel` + tres desplazamientos `--asb-*` en `:root`.
- **Movimiento reducido = anular el desplazamiento, no el reloj**: el interruptor al final de `tokens.css` pone los desplazamientos a cero y deja vivir los fundidos. La red de seguridad de `app.css` solo apaga `animation` y `scroll-behavior` — jamás ampliarla a `transition`.
- **Portadores con nombre**: `.pulsable` (lleva la transición COMPLETA del botón: transform + colores), `.tarjeta-hover`/`.vidrio-hover`, `.enlace-accion`, `.alerta-animada` (via `@starting-style`, nunca keyframes: las mordazas de tema no cubren `animation`). Componente `x-publico.boton` (variantes primaria/contorno, prop `tipo`, `.pulsable` de fábrica) en 43 sitios.
- **Guardias**: `tests/Feature/MovimientoTest.php` (7 patrones prohibidos sobre 7 directorios de vistas, poda de llaves para el hover táctil) y `tests/Feature/MenuMovilTest.php` (las tres salidas de la superposición).
- **Transiciones de vista** entre documentos: fundido de raíz 180 ms + emparejamientos `view-transition-name` **prefijados por sección** (`portada-asociado-{id}`, `portada-artista-{id}`, `portada-evento-{id}`, `filtro-activo`). Una sección nueva que copie el patrón debe nacer con su propio prefijo o colisiona por navbar y el navegador descarta la transición entera en silencio.

### 21.2 Las dos reglas que no se ven venir (costaron cinco defectos silenciosos)

1. **La sintaxis de variable es el paréntesis**: `duration-(--duracion-boton)`. El corchete `duration-[--var]` compila a una declaración inválida que el navegador descarta sin avisar y todo cae al default de 150 ms. La guardia lo vigila.
2. **Una utilidad de Tailwind pisa siempre a un portador de `@layer components`** (utilities gana a components, da igual la especificidad). Nunca poner `transition-*`/`duration-*`/`ease-*` sueltas en un elemento que lleve `.pulsable` o `.enlace-accion`; la guardia prohíbe `transition-colors` en `boton.blade.php` por esto. Misma física por la que `class="block"` no puede vencer al `inline-block` del componente: se traduce a `w-full`.

### 21.3 Deuda anotada (dictamen del revisor final: ninguna bloquea)

- **`@alpinejs/collapse` quedó sin consumidores** (el menú móvil era el único); `app.js:2,4` la sigue importando y registrando. Retirarla toca `package.json` → decisión del dueño.
- Los **7 chips de filtro** piden componente propio con prop `:activo` (la cadena `@class` está repetida idéntica 4 veces entre boletín y proveedores).
- `--ease-in-out` aún sin consumidor; los conmutadores segmentados (Tarjetas/Mapa, Próximos/Pasados) sin unificar; `empleo/show:35` con paleta distinta a sus tres migas gemelas.
- Huecos menores del regex de la guardia, hoy sin instancias: modificadores de Alpine (`x-transition:enter.duration.150ms`) y estilos en línea (`style="transition-duration:…"`).
- La red de seguridad de `app.css` no está en `@layer` (riesgo teórico frente a `!important` en capa; hoy nadie lo escribe); la `variante` con typo en el botón cae en primaria sin avisar.

### 21.4 Notas de cierre

La verificación visual se hizo con `playwright-cli` sobre Chromium real (el panel del navegador de esta máquina no compone fotogramas): tres vídeos entregados en la conversación del 17 ago 2026 — menú móvil, transición listado→ficha, y el recorrido completo de 8 capítulos. No están en el repo. La grabación dejó **un Mensaje de demo en la base local** (envío real de /contacto); la retención de G12 lo depura sola. El pendiente del §20 (despliegue en Laravel Cloud) sigue igual: decidido, sin ejecutar, esperando el paso humano del §20.3.

---

## 22. CERRADO — Pase de interfaz iOS y el parpadeo del logo (18–19 ago 2026)

> ✅ **Ya no está en pausa: los siete hallazgos del §22.4 se cerraron el 19 de agosto.** Lo de esta sección se conserva porque explica el diagnóstico y las trampas que costaron rondas —el logo que no era vector, los tres desplegables que no animaban nada, el material con dos recetas—, no el estado. **Para el estado ve al §25.** La lista de siete pendientes del §22.4 está toda cerrada, con dos correcciones a lo que decía: las etiquetas de la navbar que partían en dos líneas eran **tres y no dos**, y no ocurría «a 1280, 1440 y 1600 px» sino a **cualquier ancho de escritorio**, porque el `max-w-7xl` de la barra la topa en 1280.

Encargo del dueño: (1) el logo «aparece y desaparece» en cada navegación; (2) «mejorar toda la interfaz dándole ese toque de iOS que tienen los iPhone». Sobre `main`, tres commits, suite **599 pruebas (588 pasan, 11 omitidas, 0 fallos)**.

### 22.1 El parpadeo del logo NO era la transición de vista

Conviene leerlo porque la hipótesis obvia era falsa y costó cuatro rondas de medición descartarla. El fundido de raíz de `@view-transition` usa `mix-blend-mode: plus-lighter`, que para píxeles idénticos **es estable**: medido, la desviación de luminancia de la caja del logo durante la navegación era de 0,14/255, o sea invisible. Nombrar el cromo con `view-transition-name` no arreglaba nada porque no había nada que arreglar ahí.

La causa estaba en el activo. **`logo-asobares.svg` nunca fue un vector**: eran 49.211 bytes de un `<svg><image xlink:href="data:img/png;base64,…">`, es decir un PNG de 592×108 envuelto en base64 —con el tipo MIME mal escrito, `img/png` en vez de `image/png`—. Descubierto tarde, al analizar el cuerpo, no le daba tiempo a descarga + análisis de XML + decodificación de base64 + decodificación de PNG antes del primer pintado. Instrumentando el documento nuevo desde dentro, en los dos temas, el `<img>` llegaba a `pagereveal` y al primer `rAF` con `naturalWidth` 0 y no terminaba hasta `load`, ~500 ms después. Como la instantánea de la página entrante se toma en la primera oportunidad de pintado, salía **sin logo**.

Arreglo: se extrajo el PNG de dentro del SVG (mismos píxeles, sin recodificar), `<link rel=preload as=image fetchpriority=high>` en el `<head>` y `fetchpriority=high` en el `<img>`. Desviación medida: **48,1 → 0,0** en claro y **30,4 → 0,4** en oscuro.

⚠️ **El SVG viejo sigue en `public/img/` y ya no lo usa el sitio público, pero `AdminPanelProvider:45` sí** (`->brandLogo()`). El panel arrastra el mismo coste; se dejó fuera de alcance a propósito.

### 22.2 Los tres desplegables no animaban nada de lo que se mueve

Trampa de Tailwind 4, hermana de la que ya documenta el §21.2 y con la misma firma: ningún error, nada roto a la vista. `transition-[opacity,transform]` declara `transform`, pero las utilidades de movimiento de Tailwind 4 **ya no compilan a `transform`**:

```
translate-y-(--var)  ->  translate: ...
scale-95             ->  scale: ...
rotate-90            ->  rotate: ...
```

Son propiedades distintas, así que transicionar `transform` no interpola ninguna. El menú móvil no deslizaba, el de usuario no escalaba y la hamburguesa no giraba: las tres solo fundían opacidad y la geometría saltaba. Que la propia `transition-transform` de Tailwind se defina como `transform, translate, scale, rotate` es la confirmación.

Entra el portador `.transicion-desplegable` con las cuatro propiedades, más dos guardias: un patrón prohibido que caza cualquier `transition-[…transform…]` que no nombre también `translate`, y una prueba de que el portador no se puede recortar. Verificado muestreando con `requestAnimationFrame` la propiedad computada: 38 valores de `translate`, 26 de `scale`, 25 y 26 de `rotate`. Antes, dos.

### 22.3 Material con dos recetas

El sitio cumplía el bicromatismo en el panel y no aquí: el único `backdrop-filter` del frontend era una opacidad compartida por los dos temas, y la tarjeta en oscuro llevaba `box-shadow: none` (superficie contra fondo, 1,1:1). Entran `--asb-cromo-*` y `--asb-hoja-*` con sus dos recetas —sombra sobre Ambient White, filo de luz sobre Pub Black—, el velo derivado de `--asb-fondo` con `color-mix`, `blur(20px) saturate(180%)`, y separación **condicional al scroll** (`.cromo-apoyado`, umbral de 8 px para no pelear con el rebote elástico de iOS). Más `prefers-reduced-transparency` y `prefers-contrast`, que no existían.

⚠️ **Trampa de verificación, y es la razón por la que esto se anota:** Playwright **acepta** la opción de contexto `reducedTransparency` pero **no la emula** en la versión instalada — la consulta sigue devolviendo `false`. Quien la dé por buena con la opción de contexto está firmando a ciegas. Hay que forzarla por CDP con `Emulation.setEmulatedMedia`. La opción `contrast: 'more'` sí funciona.

### 22.4 Lo que queda, con su informe

Una auditoría multiagente de ocho lentes contra los principios de Apple (16 agentes, refutación adversarial por lente) dejó **59 hallazgos vivos de 84**. Lo entregado arriba cubre los de material y movimiento. Sigue abierto, por orden de impacto:

- **`campo.blade.php:16` mata el foco visible** con `focus:outline-none` y lo sustituye por un anillo de 2,21:1. Es el único indicador de foco de todos los formularios del sitio y no cumple. Arreglo: borrar esa cadena y dejar actuar al `:focus-visible` de `app.css:44` (3,49:1 en claro, 5,15:1 en oscuro).
- **La ayuda del campo desaparece al errar y la rejilla salta.** Lo dejó anotado el §10 de la spec de movimiento y sigue abierto: hay que emitir la ranura SIEMPRE (`min-h-4`), no condicionarla.
- **41 de 75 objetivos táctiles por debajo de 44 px**, los enlaces de la navbar en 36 px.
- **`.pulsable` tiene un solo consumidor** (`boton.blade.php`): 43 botones responden al dedo y los otros ~106 controles del sitio no tienen acuse ninguno.
- **Tipografía sin escala**: un `letter-spacing: -0.02em` plano para toda la horquilla de 16 a 60 px, y `leading-relaxed` único repartido entre 12 y 18 px en 48 sitios. ⚠️ La escala va en `app.css`, **nunca en `tokens.css`**: el tema del panel importa `tokens.css` y redefinir ahí `--text-*` cambia en silencio toda la tipografía de `/admin`.
- **21 de 22 `transition-colors` corren a 150 ms con la curva nativa** en vez de los tokens; se cierra con `--default-transition-duration` en un `@theme` de `app.css` (misma advertencia de ubicación).
- **Las 27 flechas del sitio** (`→`, `←`, `↗`) se pintan con la fuente del sistema, no con Poppins, porque el subconjunto compilado no las trae.
- **Los enlaces «Abre tu negocio» y «Quiénes somos» parten en dos líneas** en la navbar de escritorio a 1280, 1440 y 1600 px, dejándola en 81 px de alto. **Es anterior a este trabajo** —verificado con el árbol en stash— y es decisión de producto: o se acortan las etiquetas o la navegación se reagrupa.

### 22.5 Decisión pendiente del dueño

El §10 de la spec de movimiento rechazó a propósito animar navbar, pie, migas, paginación y logo, y el scroll-reveal de las rejillas. **El pase de iOS no ha reabierto ninguno de esos rechazos**: todo lo entregado es material, profundidad y movimiento que ya existía pero estaba roto. Si se quiere ir más lejos —scroll-reveal, hero animado, transiciones de vista con emparejamiento de cromo— hay que reabrir el §10 **a propósito y por escrito**, no de pasada en una revisión.


---

## 23. EL CONTEXTO DE LOS DOS CALENDARIOS (18 ago 2026) — el orden de trabajo pasó al §26

> ⚠️ **Ya NO manda sobre el orden de trabajo. Para eso ve al §26.** Lo que sigue siendo válido y por lo que se conserva entera es el **contexto que no está en ningún otro sitio**: los dos calendarios que corren en paralelo (§23.1), lo que ha dicho el docente asesor con fecha y textual (§23.2), por qué el despliegue es un riesgo de gobierno y no una tarea (§23.3), y cómo conseguir la retroalimentación del empresario sin URL viva (§23.4). Eso hay que leerlo. Las listas de pendientes de las §23.5 a §23.12 están cerradas o superadas, y así están marcadas una por una.

Las §15–22 cuentan lo que se construyó y las trampas que costaron rondas; esta cuenta el terreno en el que se juega.

El diagnóstico en una línea, y no cambió desde la auditoría del 14 de agosto: **el producto va dos o tres semanas por delante del cronograma; la evidencia contractual va por detrás.** Lo que falta no es código de funcionalidad — es despliegue, documentación de entrega y firmas.

### 23.1 Los dos calendarios, que no son el mismo

Hay dos relojes corriendo y se cruzan el viernes 21.

**Carril empresa** — cronograma firmado por la dirección ejecutiva, 8 semanas:

| Semana | Fechas | Lo que exige | Estado real |
|---|---|---|---|
| S1 | 27–31 jul | Textos, sitemap, elección de stack | ✅ |
| S2 | 3–7 ago | Wireframes, paleta, tipografías · **hito: aprobación del diseño** | ✅ construido; el hito nunca se firmó formalmente |
| S3 | 10–14 ago | Proyecto, **hosting de pruebas**, BD, menú, institucional | ⚠️ todo menos el hosting · el 19 ago quedó **preparado a un comando**, falta la cuenta (§25, §26.2) |
| **S4** | **17–21 ago** | **Directorio + módulo de eventos** | ✅ hecho desde el 5 de agosto · el **calendario** que faltaba se construyó el 19 ago |
| S5 | 24–28 ago | Pasarela en sandbox | ✅ hecho (Bold real + simulada, conmutable) |
| S6 | 31 ago–4 sep | Panel CMS | ✅ hecho (18 recursos Filament) |
| S7 | 7–11 sep | Pruebas globales, dispositivos reales, **bugs que reporta la tutora** | ⚠️ el registro de hallazgos ya tiene formato (`constancias/Formato 03`); faltan los dispositivos reales y que la tutora reporte |
| S8 | 14–18 sep | Dominio + SSL, **capacitación**, **manual de usuario**, documentación técnica, BD exportada | ⚠️ manual, documentación y BD **entregados**; la capacitación tiene su constancia lista; dominio y SSL cuelgan de R-14 |

**Fecha límite dura: 22 de septiembre de 2026.** Quedan cinco semanas. El adelanto en funcionalidad es real pero no compra nada: **de las cuatro cosas que aún no existen, ninguna es código de producto** — hosting, manual, capacitación y documentación de entrega. Son exactamente los ítems que el cronograma pone al final y que no se pueden apurar el último día porque dependen de terceros (la tutora tiene que probar, la junta tiene que decidir la cuenta).

**Carril universidad** — asesoría de César Augusto Granada. Detalle vivo en `claude/pendientes-practica.md` del Project de claude.ai.

### 23.2 Lo que ha dicho César, con fecha y textual

César **no revisa código** — ese rol es de la tutora empresarial (Natalia Gutiérrez). Lo suyo es el documento de práctica GU-DO-007. Pero lo que califica sí depende del repositorio, y por eso está aquí.

- **10 ago, «Documento de práctica - Semana 5»** (sigue vigente, es la instrucción de fondo): aplicar las correcciones del adjunto `Semana 4 - Documento - Juan José Sua - Revisión CG.docx`; **capítulos 1 a 4 completos** e **iniciar avances de los capítulos 5, 7 y anexos**; ⚠️ «tanto usted como Ingrid deben tener documentos **diferentes**»; respetar **la longitud por capítulo** de la guía de elaboración; entregar **en `.docx`**, no en PDF.
- **12 ago, «Práctica empresarial»**: por el sismo del 10 de agosto, **no hubo entrega esa semana**. Todo se corre al **viernes 21 de agosto, 11:59:59 p. m.** — «al menos por el momento».
- **18 ago, «Información urgente sobre estado de prácticas»**: encuesta de la coordinación (afectación por el terremoto, normalidad de la práctica, modalidad). **Ya respondida** el mismo día: sin novedades, se retoman actividades, modalidad híbrida.
- **18 ago, «Re: PLANEADOR FIRMADO»** — lo más reciente y lo único abierto que él pidió directamente: se envió el planeador con la firma del estudiante y respondió **«haga firmar el documento de la tutora empresarial y me lo vuelve a enviar»**. Requiere la firma de **Natalia**, no la del practicante.

**Reglas de fondo del curso** (correo de inicio, 14 jul): avances todos los viernes 11:59 p. m. sin excepción, tarde = 0.0, la nota del corte es el promedio semanal. Cortes C1 20 % · C2 20 % · C3 60 %.

**El viernes 21 cierra el corte 2 (20 %), y evalúa cuatro cosas:**

1. Fundamentación teórica terminada.
2. **≥ 80 % del capítulo de desarrollo** (cap. 5, 8 páginas según GU-DO-007).
3. **Retroalimentación del empresario.**
4. Cumplimiento de entregas y asesorías.

**Dónde toca esto al código:** el punto 2 se escribe con el repositorio en la mano — 599 pruebas, 22 modelos, 32 migraciones, 18 recursos Filament, el historial de commits, los diagramas y las decisiones de arquitectura de las §15–22 son el material del capítulo 5. Y el punto 3 no se produce escribiendo: **hay que conseguirlo de Natalia y dejarlo por escrito.** Es el único de los cuatro que depende de otra persona, y es el que está en riesgo (§23.3–23.4).

### 23.3 El despliegue pasa de tarea a riesgo (decisión del dueño, 18 ago)

La decisión del §20 sigue en pie y **no se reabre**: Laravel Cloud, plan Starter, cuenta institucional. Lo que cambió es la clasificación. El bloqueo del §20.3 no es una tarea pendiente de un agente ni del practicante: es **el correo y el medio de pago del gremio**, y eso lo decide la junta.

Se registra formalmente, en la línea de los riesgos de la ERS v3:

> **R-14 — La plataforma no vive en ningún servidor por falta de cuenta institucional.** Materializado desde el 15 de agosto. Impacto: el hosting de pruebas era ítem de la S3 (vencida); sin URL viva no hay SSL (S8), no hay pruebas en dispositivos reales de la tutora (S7), y el corte 2 se queda sin su canal natural de retroalimentación del empresario. Dueño del riesgo: la dirección ejecutiva, no el equipo de práctica. Mitigación mientras tanto: §23.4.

**No lo resuelvas desplegando con cuenta personal.** El §20.6 ya lo dice y el consejo de decisión fue unánime en el modo de muerte: la demo sale bien, nadie migra lo que funciona, la práctica termina el 22 de septiembre y las llaves se van con el practicante. Si por urgencia extrema se hiciera, **queda anotado aquí con fecha de traspaso** — no se hace en silencio.

Lo que sí toca hacer es **nombrarlo en la reunión del viernes** (§23.7, punto 5) y dejar constancia. Un riesgo escalado por escrito es evidencia de gestión de proyecto y sirve para el capítulo 6; un riesgo callado es una omisión del practicante.

### 23.4 Cómo conseguir la retroalimentación del empresario sin URL viva

Es el punto que más fácil se pasa por alto porque no parece trabajo de desarrollo, y es el que califica el viernes.

- **La reunión semanal con Natalia es el viernes a las 9:00 a. m.** (acuerdo de la Reunión 1; ⚠️ confirmar que se mantiene tras el sismo, porque la práctica se retomó apenas el 18) — mismo día del cierre del corte 2, con catorce horas de margen. Ahí se resuelven dos cosas de una sentada: la **firma del planeador** que pidió César el 18 (§23.2) y la **retroalimentación**.
- **Demo sin hosting:** `php artisan serve` desde el portátil, o recorrido grabado. Ya hay precedente: los tres vídeos con `playwright-cli` sobre Chromium real del 17 de agosto (§21.4). Un recorrido de 8 capítulos grabado se le puede dejar a la tutora para que lo revise con calma y reporte por escrito, que es justo el bucle que el cronograma pide para la S7.
- ⚠️ **La retroalimentación tiene que quedar escrita y fechada.** Un «está muy bonito» dicho en la reunión no es evidencia para el corte 2. Sirve: un acta corta firmada, un formato de retroalimentación, o un correo suyo respondiendo. Lo que se lleve, se anexa al documento de práctica.
- **Abre el registro de bugs de la tutora ya**, aunque tenga una sola entrada. El cronograma nombra explícitamente «corrección de errores reportados por el tutor de práctica» como el contenido de la S7: llegar a septiembre con un registro que arrancó en agosto se lee muy distinto a improvisarlo.

### 23.5 Orden de trabajo hasta el viernes 21 — Fase 4, la parte que no depende del hosting

Cuatro artefactos que el cronograma exige, que **no necesitan servidor**, y que alimentan a la vez la entrega a la empresa y los capítulos 5, 7 y anexos que pide César. Ese doble uso es el criterio por el que están primero.

> ✅ **Los puntos 1, 2 y 3 se ejecutaron el 18 de agosto.** Existe `docs/ingenieria/` con la matriz de pruebas, el manual de usuario y siete diagramas con sus fuentes. Lo que sigue abierto está en el §23.9. El índice de la carpeta es `docs/ingenieria/README.md` y es el que deben citar los anexos del documento de práctica.

1. ✅ **Diagramas UML/BPMN a `docs/ingenieria/`.** Los cuatro de la S2 ya existen (casos de uso, contexto nivel 0, BPMN de afiliación, BPMN de guía normativa) con sus fuentes PlantUML en `claude/diagramas-uml-bpmn-fuentes.md` del Project — pero **viven fuera del repositorio** — verificado el 18 de agosto: `docs/ingenieria/` no existe y en `docs/` solo está `superpowers/`. Es justo lo que la auditoría del 14 de agosto marcó y sigue igual. Falta además el flujograma del proceso propio del equipo. ⚠️ Van a `docs/ingenieria/`, **no** a `docs/superpowers/`: eso último es área de trabajo de agentes y los anexos del documento no deben apuntar ahí.
2. ✅ **Manual de usuario** (texto completo; faltan 11 capturas, §23.9). El panel está terminado, así que ya se puede escribir; no hay razón para dejarlo a la S8. Los cinco guiones del README (flujo de aprobación, pago simulado, cartera del afiliado, importar CSV de la contadora, PQR con radicado) son el esqueleto. ⚠️ El destinatario es **personal no técnico** — es literalmente el RNF-14. Capturas del panel real, no prosa. El cronograma acepta PDF o vídeo.
3. ✅ **Matriz de pruebas.** Existen 599 pruebas automatizadas y **cero** matriz legible por un humano. Son cosas distintas y el cronograma nombra la segunda. No hay que escribir pruebas nuevas: hay que **mapear las que ya pasan contra los códigos RF-01…RF-62 de la ERS v3**, que es exactamente la trazabilidad que el Anexo C ya promete. Es trabajo de tabla, no de desarrollo. **Hallazgo al construirla:** el árbol tiene **460 métodos de prueba en 48 archivos**, que se ejecutan como **599 casos** porque 15 métodos usan proveedor de datos y expanden a varios casos cada uno. Las dos cifras son correctas y miden cosas distintas — al citarlas en el documento, decir cuál es cuál.
4. ⬜ **Base de datos exportada.** Entregable final explícito: esquema + datos semilla. Trivial hoy, y evita la carrera de septiembre.

Y uno barato que da un número citable:

5. ⬜ **Medición de móvil y de los 2,5 s.** El cronograma manda mobile-first y portada por debajo de 2,5 segundos, y la auditoría anotó que **no hay ninguna medición**, solo marcado responsive. Lighthouse o `playwright-cli` sobre las rutas públicas reales, en los dos temas. Media hora, y el capítulo 5 pasa de «se implementó mobile-first» a una cifra verificable. El §22.1 ya dejó el instrumental montado para medir en el navegador real.

### 23.6 Lo que NO se toca esta semana

- ~~**El pase de interfaz iOS (§22.4) queda en pausa.**~~ **Se retomó y se cerró el 19 de agosto**, una vez cerrada la Fase 4 que era la condición. Los siete hallazgos —foco, ayuda del campo, objetivos táctiles, `.pulsable`, escala tipográfica, `transition-colors`, flechas sin Poppins, navbar en dos líneas— siguen abiertos y siguen siendo válidos, pero **ninguno bloquea la entrega, ninguno está en el cronograma y ninguno lo califica César.** Abrir interfaz ahora se come la semana y no mueve nada de lo que vence el viernes. Única excepción razonable si sobra un hueco: `campo.blade.php:16`, porque es **una línea** (`focus:outline-none` fuera) y arregla el único indicador de foco de todos los formularios del sitio, que hoy incumple.
- **Ningún módulo nuevo.** La congelación de alcance del 14 de agosto sigue vigente y ahora tiene más razón, no menos.
- **La firma de la ERS v3 no es trabajo de código.** Es un punto de la reunión del viernes (§23.7).

### 23.7 Qué llevar a la reunión del viernes 21, 9:00 a. m.

Una sola lista, porque es la única ventana de la semana con la tutora y de ahí sale medio corte 2:

1. **Planeador FO-DO-100 para la firma de Natalia como tutora empresarial** — es lo que César pidió el 18 de agosto y lo único suyo que está abierto. Reenviárselo apenas esté firmado.
2. **ERS v3 para firma** y las **13 decisiones DPV**. Primero la **DPV-02**: la contradicción de qué se ve con sesión y qué sin ella condiciona ocho requisitos que **ya están codificados**. Es la única de las trece que puede obligar a reescribir código, así que se pregunta antes que las otras doce.
3. **Demo + formato de retroalimentación por escrito** (§23.4).
4. **P-06: la base de los ~60 asociados con sus autorizaciones de publicación.** Sin ella el directorio se lanza vacío — riesgo R-02, ya materializado. Se lleva pidiendo desde principios de agosto; conviene ponerle fecha comprometida en el acta, no volver a pedirlo de palabra.
5. **La cuenta institucional para el hosting** (§23.3): correo `asobaresquindio@asobares.org` y medio de pago del gremio. Se plantea como decisión de la junta con su consecuencia dicha en voz alta — sin esto no hay SSL, no hay pruebas en dispositivos y no hay sitio en vivo el 22 de septiembre.

### 23.8 Estado del árbol al escribir esto

- `main` en **`4f15d24`** («Anota en el prompt maestro el pase de interfaz iOS»), **sincronizado con `origin`** — el hueco de 71 commits que denunció la auditoría del 14 de agosto **está cerrado**, y con él el incumplimiento de commits semanales en GitHub. `origin/main` refleja hoy el trabajo real.
- Suite: **599 pruebas** (588 pasan, 11 omitidas, 0 fallos) — cifra **registrada por la sesión del 18 de agosto (§22), no re-ejecutada en esta revisión**: el entorno desde el que se escribe esto no tiene PHP, así que la verificación es documental. Confírmala con `php artisan test` antes de citarla en el documento de práctica. Las 11 omisiones siguen siendo legítimas (recursos sin página de creación/edición: Cartera, Postulación, Vacante).
- Árbol limpio salvo `.claude/settings.local.json` sin seguir — correcto, es configuración local.
- `.env` local: `DB_CONNECTION=sqlite`, `PAYMENT_DRIVER=fake`, `MAIL_MAILER=log`, `QUEUE_CONNECTION=sync`. Es el perfil de demostración local; el de despliegue está en el §20.5 y no se ha usado.
- ⚠️ Rama **`claude/suspicious-colden-d9e9e8`** parada desde el 4 de agosto (`82398a6`), 14 días atrás de `main`. Verificar que su contenido esté fusionado y borrarla; una rama muerta en un repositorio que la universidad va a mirar es ruido.
- El repositorio sigue siendo **`Jsua3/asobares`, cuenta personal de GitHub**. Misma familia de problema que el §20.2: recomendado moverlo a una organización del gremio o añadir un segundo administrador antes del 22 de septiembre.


### 23.9 SUPERADA — el reparto de la Fase 4 (18 ago)

> ⛔ **Esta sección ya no describe la realidad. Se conserva porque explica el reparto de trabajo que se hizo, no el estado.** Los cinco artefactos que aquí figuran como pendientes están **todos entregados**: ve directo a la **§23.11**. Las dos advertencias del final —el calendario de eventos y el foco visible— **siguen vigentes** y son lo único de esta sección que hay que seguir leyendo.

Los tres artefactos de escritorio están cerrados (§23.5). Lo que sigue **exige ejecutar la aplicación**, así que corresponde a una sesión con PHP en la máquina, no a una que solo edite archivos.

| Pendiente | Cómo se cierra | Alimenta |
|---|---|---|
| **Re-ejecutar la suite** | `php artisan test` y actualizar la tabla del §2 de `matriz-de-pruebas.md` con la salida real | La matriz declara explícitamente que su cifra es documental hasta que esto ocurra |
| **Las 11 capturas del manual** | `php artisan migrate:fresh --seed`, tema claro, 1440 px, sin datos personales reales. Los marcadores `[CAPTURA: …]` dicen exactamente qué pantalla va en cada hueco | Manual de usuario (S8 del cronograma) |
| **Base de datos exportada** | Esquema + datos semilla, en el formato que se entregue al gremio | Entregable final explícito del cronograma |
| **Medición de rendimiento** | Lighthouse o `playwright-cli` sobre las rutas públicas, en los dos temas. Es media hora | RNF-02 y el capítulo 5: convierte «mobile-first» de promesa en cifra |
| **Dispositivos reales** | Android + iOS sobre el árbol local | RNF-01 y RNF-07, contenido declarado de la S7 |

⚠️ **Dos cosas que la matriz destapó y que son decisiones, no tareas:**

1. **RF-19 — el calendario de eventos.** El cronograma firmado dice «calendario + formularios»; lo construido es una grilla Próximos/Pasados. Es una diferencia con el documento que firmó la dirección ejecutiva. Se cierra de una de dos formas, y ambas valen: construir la vista de calendario, o **acordar por escrito con Natalia que la grilla la sustituye**. Lo que no vale es dejarlo sin nombrar y que aparezca en la revisión final.
2. **RNF-12 — el foco visible.** `campo.blade.php:16` anula el indicador de foco de todos los formularios del sitio. Ya estaba anotado en el §22.4 como parte del pase de iOS en pausa, pero la matriz lo eleva: es incumplimiento de un requisito no funcional contratado, no un pulido de interfaz. **Es una línea.** Tómala aunque el resto del §22 siga en pausa.

### 23.10 Regla para la carpeta nueva

`docs/ingenieria/` es **documentación de entrega**: la lee el gremio, la citan los anexos del documento de práctica y sobrevive al 22 de septiembre.

`docs/superpowers/` es **área de trabajo de agentes**: especificaciones y planes de ejecución. No se referencia desde los anexos ni se le entrega a nadie.

No las mezcles. Un plan de agente citado como anexo académico se lee exactamente como lo que es.

⚠️ El `06-modelo-de-datos.puml` se construyó leyendo las migraciones y las relaciones declaradas en los modelos: documenta **lo que existe**, no lo que se pensaba construir. Si cambia el esquema y no se regenera, pasa a mentir. Regenerarlo es `java -jar plantuml.jar -charset UTF-8 -tpng docs/ingenieria/diagramas/fuentes/*.puml` con PlantUML 1.2024.8.

### 23.11 Fase 4 CERRADA — lo que se ejecutó entre el 18 y el 19 de agosto

El §23.5 pedía cinco artefactos que no dependen del hosting. **Los cinco están hechos.** `docs/ingenieria/` ya existe y ya no es una carpeta prometida.

| # | Artefacto | Estado |
|---|---|---|
| 1 | Diagramas UML/BPMN en `docs/ingenieria/diagramas/` | ✅ 7 diagramas en PNG con sus fuentes PlantUML editables, incluidos el flujograma del proceso del equipo y el modelo de datos real |
| 2 | Manual de usuario | ✅ Texto **y las 11 capturas** del panel real. Falta solo exportarlo a PDF |
| 3 | Matriz de pruebas | ✅ Trazabilidad RF-01…RF-62 y RNF-01…RNF-14, con los huecos declarados |
| 4 | Base de datos exportada | ✅ `docs/ingenieria/base-de-datos/`: esquema, volcado completo e inventario de las 37 tablas |
| 5 | Medición de móvil y de los 2,5 s | ✅ `docs/ingenieria/medicion-de-rendimiento.md` |

**Las dos cifras que antes eran documentales y ahora están verificadas:**

- **La suite se re-ejecutó**, como pedía el §23.8: **599 pruebas · 588 pasan · 11 omitidas · 0 fallos · 1.699 aserciones · 169 s**. Coincide exactamente con lo que se venía citando. Ya es citable en el capítulo 5 sin advertencia.
- **RNF-02 dejó de ser una promesa.** 78 mediciones con Chromium real —12 rutas públicas, móvil 4G estrangulado y escritorio, los dos temas, tres corridas por combinación, caché en frío—: **la portada pinta en 972 ms contra un techo contractual de 2.500 ms**. Ninguna ruta lo incumple. La más lenta es `/boletin` con 2.132 ms, y es la que hay que vigilar si crecen las portadas del boletín.

**El residuo honesto de esa medición:** está tomada contra `localhost`, así que **no incluye la latencia real hasta un servidor**. Hay que repetirla contra el dominio cuando exista despliegue — es decir, cuando se levante R-14. Y sigue sin haber dispositivos reales: RNF-01 y RNF-07 continúan abiertos y son contenido de la S7.

**Verificado de paso:** la rama `claude/suspicious-colden-d9e9e8` que el §23.8 mandaba comprobar **está totalmente fusionada en `main`** (`git log main..rama` sale vacío). Se puede borrar sin perder nada.

**Lo que queda de Fase 4 ya no lo puede hacer un agente:** exportar el manual a PDF, la capacitación con su constancia, las pruebas en dispositivos de la tutora, y todo lo que cuelga de R-14.


### 23.12 SUPERADA — Estado al 19 de agosto por la mañana

> ⛔ **Esta sección ya no describe la realidad.** De las seis cosas que enumera como vivas, **cinco se cerraron esa misma tarde**: el calendario de eventos, el foco visible, los objetivos táctiles, el manual en PDF con la constancia de capacitación, y el hito de aprobación del diseño (que ahora tiene su acta emitida, a falta de firma). La única que sigue viva es la primera, **R-14**. Se conserva porque su lectura del camino crítico sigue siendo correcta y porque explica por qué se priorizó lo que se priorizó. **Ve al §25.**

`main` en **`1ff87d0`**, sincronizado con `origin`. Árbol limpio salvo `.claude/settings.local.json`. Suite sin cambios: 460 métodos en 48 archivos, 599 casos.

**El expediente de entrega está completo.** `docs/ingenieria/` reúne matriz de pruebas, manual con capturas, siete diagramas con sus fuentes, base de datos exportada, medición de rendimiento y el informe de cumplimiento del cronograma en `.docx`. Nada de la Fase 4 que pueda hacerse sin servidor sigue pendiente.

⚠️ **El informe `.docx` se regeneró el 19 de agosto.** La versión del 18 declaraba como pendientes cuatro cosas que ya estaban hechas —la medición de rendimiento, las capturas del manual, la re-ejecución de la suite y la base de datos exportada— y por tanto **subestimaba el proyecto ante la dirección y ante la universidad**. Si aparece una copia con fecha del 18, no la entregues. Regla general para este documento: **cada vez que se cierre una brecha hay que regenerarlo**, porque su §8.3 enumera brechas por nombre y envejece rápido.

**Lo único que sigue abierto, en orden:**

1. **R-14 / hosting institucional** (§23.3). Sin resolver no hay SSL, ni dispositivos reales, ni medición contra dominio, ni revisión autónoma de la tutora. Es el camino crítico entero.
2. **El calendario de eventos** (§23.9). El cronograma firmado pide «calendario + formularios» y hay grilla. Decisión de la dirección, por escrito, en cualquiera de los dos sentidos.
3. **El foco visible** — `campo.blade.php:16` **sigue con `focus:outline-none`**, verificado el 19 de agosto. Es una línea y es incumplimiento del RNF-12.
4. **41 de 75 objetivos táctiles** por debajo de 44 px.
5. **Exportar el manual a PDF** y la constancia de capacitación.
6. **El hito de aprobación del diseño** de la S2, que nunca quedó por escrito.

Los puntos 2 y 6 son de la dirección; el resto es del equipo. Ninguno es de construcción de producto: el alcance está congelado y debe seguir congelado.

---

## 24. EL EQUIPO — dos practicantes, no uno (19 ago 2026)

**Se anota aquí porque el expediente salió mal por no tenerlo escrito.** Los tres formatos de firma generados el 19 de agosto llevaban una sola línea del lado de la universidad, y hubo que rehacerlos. Cualquier documento, acta, informe o crédito que produzca este proyecto lleva a **las dos personas**.

| | **Juan José Sua Gómez** | **Ingrid Montoya Warski** |
|---|---|---|
| Correo institucional | `jjsua_542@unihumboldt.edu.co` | `imontoya_624@unihumboldt.edu.co` |
| Frente | A — arquitectura, backend, panel Filament, pasarela de pagos, SEO técnico, despliegue y documentación técnica | B — maquetación mobile-first del sitio público, directorio de asociados, módulo de eventos e inscripciones, optimización de imágenes, matriz de pruebas y manual de usuario |
| Rol | Practicante · Universidad Alexander von Humboldt | Practicante · Universidad Alexander von Humboldt |

Ambas desarrollan; lo que cambia es el frente, no la categoría. El docente asesor las trata como equipo y les escribe juntas.

### 24.1 La regla, y su única excepción

**Regla:** todo artefacto del proyecto —actas, formatos de firma, informe de cumplimiento, manual, créditos, README— nombra a las dos.

⚠️ **Única excepción, y es importante no confundirla:** el **documento de práctica de la universidad (GU-DO-007)** es **individual**. César lo dijo por escrito el 10 de agosto: «tanto usted como Ingrid deben tener **documentos diferentes**». Ese documento no se firma en conjunto ni se comparte redacción; cada quien entrega el suyo.

La distinción es limpia: **lo que se le entrega a la empresa es del equipo; lo que se le entrega a la universidad es de cada persona.** Los artefactos técnicos de `docs/ingenieria/` son del equipo y ambas los citan como anexo de su propio documento — anexar el mismo artefacto no es tener el mismo documento.

### 24.2 Dónde quedó aplicado

- `docs/ingenieria/herramientas/constancias.mjs` — las tres constancias llevan las tres firmas (dirección + los dos practicantes), con la variante `.firmas.tres` de `imprimir.mjs`.
- `docs/ingenieria/Informe de cumplimiento...docx` — ficha de portada, bloque de firmas y pie de autoría.
- `estado-proyecto-web.md` del Project de claude.ai.

Si generas un artefacto nuevo y solo pones un nombre, está mal. Revísalo antes de imprimirlo.

### 24.3 El reparto del trabajo (acordado el 20 de agosto)

Ingrid levantó su propia auditoría del proyecto y propuso repartirlo en dos bloques **por responsabilidad funcional, no por frontend/backend**. Sua eligió el bloque 1. El reparto queda así:

| | **Persona 1 — Sua** | **Persona 2 — Ingrid** |
|---|---|---|
| Eje | Plataforma, administración y producción | Producto, usuario y módulos públicos |
| Bloques | Entorno · panel Filament (19 recursos, 5 páginas, MFA, roles, policies, aprobación) · cartera e importación CSV · pagos y webhook de Bold · observatorio · infraestructura (hosting, HTTPS, correo, colas) · **salud global de la suite** | Directorio y municipios · guía normativa · bolsa de empleo · eventos · artistas · proveedores · boletín · contacto/PQRS/afiliación · portal `/mi-cuenta` |

**Las dos reglas de frontera, que son lo que evita los conflictos de Git:**

1. Durante la estabilización, **solo la Persona 1 toca PHP, Composer, `.env` y migraciones**.
2. La Persona 1 **no rediseña ni amplía los módulos públicos** de la Persona 2.

El ejemplo que ella misma da y que resuelve casi todas las dudas: en la bolsa de empleo, **Persona 2 responde por crear, publicar y postular desde la experiencia funcional; Persona 1 por permisos, Filament, moderación administrativa y estabilidad técnica.**

> ✅ **Actualización del 25 de agosto: el reparto dejó de ser un acuerdo y pasó a tener commits detrás.** `origin/p2-directorio` trae tres commits de Ingrid (`imontoya_624@unihumboldt.edu.co`), del 20 y el 25 de agosto, exactamente sobre su bloque —directorio, bolsa de empleo, portal `/mi-cuenta`— y **con pruebas propias** (`DirectorioTest`, 168 líneas nuevas, más casos en `BolsaDeEmpleoTest` y `FormulariosPublicosTest`). Las dos reglas de frontera se respetaron por ambos lados: ella no tocó PHP de aplicación, migraciones ni `composer.json`; el bloque de la Persona 1 no rediseñó ninguno de sus módulos públicos.
>
> ⚠️ **Lo que el reparto no previó, y hay que resolver una vez:** su rama sale de `1ff87d0` y `main` lleva 16 commits por delante. Fusionar da **cuatro conflictos, todos contra `09e3e33`** —el pase de foco visible y objetivos táctiles— en `navbar`, `directorio/index`, `directorio/show` y `mi-cuenta/index`. Son vistas suyas, así que las resuelve ella, **pero conservando de `main` todo lo que sea `focus-visible` y área táctil mínima**: resolverlo tomando su versión en bloque revierte RNF-12, que es un requisito contratado y hoy declarado verificado. `FocoVisibleTest` y `ObjetivoTactilTest` lo cazan si se corre la suite después.
>
> La frontera que sí faltaba declarar: **quien lleve más tiempo sin sincronizar es quien fusiona `main` dentro de su rama**, no al revés.

### 24.4 Cómo leer su auditoría sin repetir trabajo

Su documento es serio y encontró cosas reales, pero **se levantó sobre un árbol sin actualizar y sobre un PHP incompleto**. Antes de aceptar su lista de pendientes, aplica esta lectura:

- **La suite NO está rota.** Su corrida dio 405 pasan / 22 fallan / 172 error porque **su PHP (Herd Lite) no trae `intl` ni `gd`** — ella misma lo diagnostica y avisa de no leerlo como 194 funcionalidades defectuosas. En un PHP con esas extensiones la suite del día de su auditoría salía **747 casos · 736 pasan · 11 omitidas · 0 fallos · 2.719 aserciones**, y hoy sale **791 · 780 · 11 · 0 · 2.818**. Su «0 skipped» es consecuencia de lo mismo: una corrida que revienta no llega a registrar omisiones. ⚠️ **Esta línea citó durante tres días «599 casos · 588 pasan · 1.699 aserciones»**, que era la cifra del 18 de agosto y ya estaba vencida cuando se escribió —el encabezado v11 de este mismo documento decía 747—. Rebatir una cifra obsoleta con otra obsoleta no rebate nada.
- **`InscripcionesDelMes` es un falso positivo.** No es residuo de autoload: `TableroTest` afirma a propósito que **la clase NO existe** (`test_el_recaudo_mensual_reemplazo_a_las_inscripciones_de_30_dias`). Es una guarda de regresión. El `use` de una clase inexistente es legal en PHP mientras solo se pase a `class_exists()`. Si «se arregla», se rompe la guarda.
- **Los pendientes de documentación ya no aplican.** Pide generar diagramas, capturas y alinear el rendimiento: **las tres cosas existen y están commiteadas** desde el 19 de agosto en `docs/ingenieria/`. Su copia era anterior.

**Lo que sí encontró y hay que atender:**

| Hallazgo | Veredicto | De quién |
|---|---|---|
| `CreateAction` huérfano en Transacciones | ✅ **Real** · **cerrado el 20 ago en `b6418e0`.** `ListTransaccions.php:16` montaba el botón sin existir página `CreateTransaccion`, y Filament no se queda quieto ante eso: abre el formulario del recurso en un modal, concediendo justo lo que el hallazgo G9 prohíbe. Retirada la acción, con dos pruebas que fijan el recurso como de solo lectura | Persona 1 |
| Catálogo de municipios incompleto | ✅ **Real.** El seeder trae 7 y el Quindío tiene 12 | Persona 2 |
| La suite muere en PHP 8.5.8 al llegar a una prueba de imágenes, y esa prueba pasa aislada | ✅ **Real en su máquina** · **no reproduce en 8.5.9**: el 23 de agosto la suite completa salió entera y verde dos veces (791 · 780 · 11 omitidas · 0 fallos). Deja de ser un defecto que diagnosticar y pasa a ser lo que ya era el punto siguiente: unificar el intérprete del equipo | Persona 1 |
| Migraciones pendientes en la base local | ⚠️ Es de **su** base local, no del repositorio | Persona 1 la acompaña |

⚠️ **La raíz del bloqueo de Ingrid ya estaba documentada y aun así la mordió:** el README declara `intl`, `gd`, `exif`, `fileinfo` y `mbstring` como requisito, y menciona Herd Lite por su nombre. Lo que faltaba era que **`composer.json` las declarara**: solo pedía `php ^8.3`, así que `composer install` pasaba limpio en un entorno incompleto y el fallo aparecía 350 segundos después, disfrazado de 194 pruebas rotas. **Cerrado el 20 de agosto en `3f612fa`**: las cinco extensiones están en el `require` de la raíz y subidas al bloque `platform` del candado, con diez casos que impiden que raíz y candado se vuelvan a separar. El diagnóstico pasa de media jornada a una línea de salida.

### 24.5 Lo que su §7 acierta, y conviene adoptar

Su documento incluye una lista de «elementos que NO deben asumirse como pendientes obligatorios» —API REST, membresías, suscripción automática, integración con redes, certificado de afiliación, cobro a proveedores, motor PDF nativo y calendario de eventos— con la regla de comprobar el alcance antes de construir. **Es la misma congelación de alcance que sostiene este documento desde el 14 de agosto.** Adoptarla explícitamente: la ausencia de una funcionalidad no es un incumplimiento mientras no esté en el cronograma firmado o en la ERS.

### 24.6 Seis falsos verdes, y por qué el §7 se quedó corto

RF-60 se construyó con un plan escrito de antemano, ocho tareas, revisión independiente por tarea y una revisión final de toda la rama. El resultado más útil no es el código: es el recuento.

**Seis pruebas pasaban sin ejercer lo que decían proteger. Ninguna era un defecto de producción.** El código funcionó desde el primer commit. Lo que fallaba, una y otra vez, era lo que las pruebas *afirmaban*.

| # | La prueba decía | La verdad |
|---|---|---|
| 1 | Que sin el paréntesis que agrupa el `orWhere`, el filtro de publicación se anulaba | **Falso para un scope local.** `Builder::callScope()` cuenta los `where` antes y después y llama a `addNewWheresWithinGroup()`. El peligro sólo existe **fuera** del scope. La mutación que el plan mandaba era imposible |
| 2 | Que un borrador no se cuela por el `orWhere` | Usaba un borrador **sin** `vigente_hasta`, y ése no se colaría ni con la SQL rota: el término sería `NULL >= ?`. El que delata la fuga es uno con fecha **futura** |
| 3 | Que vaciar `vigente_hasta` desde el panel lo deja nulo | El registro **ya nacía nulo**. Entrada igual a salida: el `DatePicker` podía estar desconectado y pasaba en verde |
| 4 | Que la contraprueba protegía de una vista que no renderizara nada | Las pruebas de marcas usan `assertSee` de texto que sólo existe si el trámite se renderiza: ya caían solas. Lo que protege es otra cosa |
| 5 | Que el scope no anula el `publicado()` | Creaba dos borradores y **ningún publicado**: la consulta salía vacía y las dos negaciones pasaban por vacuidad. Faltaba el control positivo |
| 6 | *(evitado)* Que la bitácora registra el cambio | Iba a leer `properties`, pero activitylog v5 guarda el diff en `attribute_changes`. Lo descubrió el implementador **corriendo la suite**, no razonando |

**Las cuatro justificaciones falsas las escribió el plan**, no los implementadores. Y ahí está la lección que el §7 no vio:

> **El código de producción se valida al ejecutarse. Una prueba mal pensada se valida a sí misma.**

Por eso un plan que incluye código de prueba es más peligroso que uno que no lo incluye: le da al implementador algo que parece verificado y no lo está. Tres consecuencias prácticas:

1. **Todo paso de mutación es obligatorio, y hay que comprobar que puede ponerse rojo.** El único que resultó imposible (el nº 1) fue precisamente el que dejó pasar el error. Una mutación que no rompe nada no es tranquilizadora: es una señal de que la prueba no toca lo que crees.
2. **Para cada aserción, preguntar qué tendría que romperse para que fallara.** Si la respuesta es «nada», no es una prueba. Los casos 3 y 5 se detectan enteros con esa sola pregunta.
3. **El punto ciego no está donde uno lo teme.** Las Tasks 6-8 no pasaron por revisión de subagente fresco y se temió que fueran el hueco; aguantaron. Los dos defectos serios de la revisión final estaban en las Tasks 1 y 3, **dentro del tramo que sí se revisó**.

Y un cuarto hallazgo, que no es de pruebas sino de cifras: la matriz declaraba **«46 de 50» requisitos funcionales cubiertos** y ese número **no se podía reconstruir bajo ningún método de conteo**. Quedó en **52 de 53** con la metodología escrita al lado. Es la segunda vez que este proyecto publica una cifra calculada en vez de medida —la primera fue el «789 casos» del 21 de agosto, que la corrida real desmintió con 791—. **Ninguna cifra del expediente debe salir de una suma.**



---

## 25. LA JORNADA DEL 19 DE AGOSTO — el pase de interfaz cerrado y el despliegue a un comando

**Lee esto antes de tocar interfaz, despliegue o el expediente.** Cierra el §22, que estaba en pausa; cierra el §20 por su lado técnico; y cierra cinco de las seis brechas que el §23.12 dejaba vivas. Lo que queda abierto está en el §25.4, y es corto.

Se ejecutó con dos frentes en paralelo —despliegue e interfaz— y **verificación independiente del orquestador sobre cada afirmación medible**. Esa doble medición encontró cuatro defectos que ningún documento registraba (§25.2) y descartó ocho propuestas del reconocimiento que no se sostenían al comprobarlas.

### 25.1 Qué quedó cerrado

| Brecha | Cómo se comprobó |
|---|---|
| **RNF-12 · foco visible** | Se fueron las dos anulaciones. El trazo mide 3,49:1 en claro y 5,15:1 en oscuro contra el mínimo de 3:1. Seis guardias, y una **recalcula el contraste** en vez de comparar cadenas |
| **Ranura de ayuda del campo** | Se emite siempre: la rejilla ya no salta al errar, y la ayuda queda asociada al control con `aria-describedby` |
| **Escala tipográfica** | 14 pasos con tracking monótono que **cruza cero exacto en `text-base`** y leading inverso al tamaño. Vive en `app.css`, y está verificado sobre el CSS **compilado** que no llegó a `/admin` |
| **`transition-colors` fuera de los tokens** | `--default-transition-*` en un `@theme` de `app.css`: las 21 pasan a los tokens sin editar una sola vista |
| **Las 27 flechas** | Componente `x-publico.flecha` con SVG de Heroicons. Cero caracteres de flecha en las vistas, verificado por codepoint |
| **Objetivos táctiles** | **547 objetivos en 18 rutas a 320 y a 390 px, y 674 a 1280 px: cero por debajo de 44 px**, 26 exceptuados por WCAG 2.5.8, y **cero robos de clic** |
| **Acuse al pulsar** | `.pulsable` pasa de 1 a 39 consumidores. Medido con el ratón abajo: `:active` cierto, `matrix(0.97)` y `transition-duration: 0s` |
| **Navbar en dos líneas** | Ocho enlaces a cinco controles. Cabecera de **83 a 62 px**, medida a 1280, 1440, 1600 y 1920 |
| **RF-19 · calendario** | Rejilla `<table>` en escritorio y agenda `<ol>` en móvil. **4 consultas por mes, no 42** |
| **Manual en PDF** | 24 páginas, Poppins incrustada, las 11 capturas |
| **Constancias** | Los tres formatos de firma, con las **tres** líneas que exige el §24 |
| **R-14, lado técnico** | 33 migraciones y 20 sembradores verdes en **PostgreSQL 17.11 real**; runbook de 512 líneas; `.env.staging.example` de 52 variables |

### 25.2 Los cuatro defectos que nadie había registrado

Ninguno estaba en el §22.4 ni en el §23.12. Los tres primeros **solo se habrían visto con la plataforma ya publicada**, que es el peor momento posible.

1. **El buscador del directorio encontraba 4 de cada 10 establecimientos.** `LIKE` es insensible a mayúsculas en SQLite y **sensible** en PostgreSQL. Medido contra el motor real: `like '%bar%'` devuelve 4 filas; `ilike`, 10. La suite corre sobre SQLite, así que ninguna prueba lo veía. Arreglado con `whereLike(caseSensitive: false)`, que lo resuelve la gramática de Laravel sin un `match` por driver que mantener. **La prueba afirma la SQL emitida por cada gramática**, no el resultado: una prueba de comportamiento habría pasado en verde con el código roto.

2. **La política obvia del bucket reabre un agujero ya cerrado.** Conceder `s3:GetObject` sobre `arn:...:<bucket>/*` deja los formatos oficiales de la guía normativa descargables por URL directa — medido: 200, sin pasar por `GuiaController`, que es donde se comprueba que el requisito esté publicado. Acotada al prefijo `publico/`: 403. Los dos prefijos son la frontera de seguridad, y `AlmacenamientoTest` afirma que siguen siendo distintos. ⚠️ **Sigue siendo cierto, pero desde OBS3-13 ya no basta:** el prefijo público transporta ahora fotos del propietario **sin moderar y devueltas**, así que «no hay nada que proteger» dejó de valer para ese disco. Decisión pendiente en el §30.3.

3. **La coraza de configuración protegía el entorno equivocado.** Estaba atada a `production`, y el despliegue usa `APP_ENV=staging`: el único entorno de verdad expuesto era justo el que no se endurecía. El criterio ya no es cómo se llama el entorno, es si está expuesto.

4. **Seis advertencias de seguridad activas** en `league/commonmark`, tres de severidad alta, en el camino de los cinco correos transaccionales. Se cerraban **dentro de la restricción que ya declaraba Laravel**, así que se aplicó el parche 2.8.3 a 2.10.0 sin tocar ninguna restricción. `composer audit` sale limpio.

Y uno menor, anterior a esta jornada: a 320 px `/proveedores` desbordaba 45 px, por un correo de 299 px sin punto de corte. ⚠️ **`overflow-wrap: break-word` NO reduce el ancho mínimo de contenido** —solo parte al desbordar—, y la tarjeta es un elemento flex cuyo ancho lo fija ese mínimo. Hace falta `wrap-anywhere`.

### 25.3 Trampas nuevas, para no volver a pagarlas

- **Una guardia que lee ficheros crudos también lee los comentarios.** La guardia de flechas prohíbe codepoints; si explicas por qué quitaste una, **nombra el codepoint** en vez de pegarlo. Ya mordió una vez.
- **El espacio de no separación NO protege a un SVG.** Sirve para pegar dos palabras, no una palabra a una caja atómica: medido, la línea se parte igual. Con el carácter de antes sí funcionaba, porque un carácter no es una caja. Donde el ancho aprieta, la única defensa es `whitespace-nowrap` en el portador.
- **Ampliar el subconjunto de Poppins no era caro: era imposible.** Medido con fontTools sobre los seis `.woff2`: 217 glifos por peso y ninguna flecha, ni siquiera los codepoints que el propio `@font-face` promete en su `unicode-range`. La familia no los dibuja.
- **`text-2xs` no compila hasta que una vista lo usa.** Tailwind poda los pasos sin consumidores; el primero fue el calendario. Hasta entonces el paso existía en `@theme` y no en el CSS, y quien mirara el bundle habría concluido que la escala estaba rota.
- **`--window-size` no fija el viewport de maqueta en headless.** Hay que imponerlo por CDP con `Emulation.setDeviceMetricsOverride`, o se mide la variante equivocada y se diagnostican defectos que no existen. Pasó, y costó tres rondas.
- **`html { scroll-behavior: smooth }` rompe `scrollIntoView` seguido de `getBoundingClientRect`**: la caja se lee a mitad del recorrido y el clic sintético cae en otro elemento. Hay que usar `behavior: 'instant'` y esperar un fotograma.
- **Ampliar objetivos táctiles a 44 px introduce robos de clic** si el paso entre vecinos es menor. Donde el paso sea corto hay que **abrir el paso**, no meter margen negativo. Se comprueba con `elementFromPoint`, no midiendo rectángulos.

### 25.4 Lo que sigue abierto

**Está en el §26, y a propósito no se repite aquí.** Este documento ya ha pagado dos veces el precio de tener la misma lista en dos sitios: la del §23.9 y la del §23.12 quedaron obsoletas mientras la otra decía lo contrario. Una sola lista de pendientes, y es la del §26.

En una frase, para quien solo lea esta sección: **lo único que bloqueaba el proyecto era la cuenta institucional del gremio (R-14) — ⚠️ **superado: ver §29 y §30.4.** La cuenta existe, el sitio está desplegado y devuelve 500; R-14 está **sorteado, no cerrado**, porque la organización es personal, y de ella cuelgan el bucket, el correo saliente, el SSL, la medición contra dominio y las pruebas en dispositivos de la tutora.** Lo demás son firmas.

Un residuo que sí conviene dejar dicho aquí porque es una **excepción declarada y no una deuda**: los seis enlaces de atribución de Leaflet miden 51 por 15 px. Los pinta la librería, la licencia de las teselas los exige y son la convención de todos los mapas de la web. Cerrarlos significa tocar una pieza de terceros para cumplir un listón que es más exigente que la norma; es decisión del dueño, no de accesibilidad.

### 25.5 El instrumental que queda

- `docs/ingenieria/herramientas/` — los generadores de los PDF del expediente, manual y constancias, **sin dependencias de npm**: un conversor de Markdown propio y el Chrome ya instalado, conducido por el protocolo DevTools sobre el WebSocket nativo de Node. `imprimir.mjs` es el motor compartido, y es el único sitio donde tocar la hoja de estilo del papel.
- **Contenedores de verificación**, que no viven en el repositorio y se levantan cuando hagan falta: `postgres:17-alpine` para el motor de Cloud y `minio/minio` para el almacenamiento de objetos.
  ⚠️ El contenedor de Postgres **no es fiel a Cloud en colación**: alpine es musl y ordena como `COLLATE "C"`, igual que SQLite; el de Cloud será glibc o ICU y **el orden alfabético del directorio, los municipios y las categorías cambiará**. No rompe nada, pero lo parecerá el día de la demostración, y el contenedor local no permite anticiparlo.

### 25.6 Qué quedó escrito, y dónde

Siete commits, todos en `origin/main`, con `main` en `f61a236`:

| Commit | Qué cierra |
|---|---|
| `c052097` | Escala tipográfica, reloj por defecto y portadores de acuse |
| `09e3e33` | Foco visible, objetivos táctiles, flechas, acuse en vistas y navegación reagrupada |
| `fa0691f` | El calendario de eventos (RF-19) |
| `55a23ab` | El calendario en el sitemap y en el barrido de rutas públicas |
| `2172ce5` | Despliegue, almacenamiento de objetos y los defectos que solo se verían publicados |
| `f61a236` | Expediente, tres constancias y esta memoria |
| `9b871ce` | *(anterior a la sesión, estaba sin subir)* |

Ocho archivos de prueba nuevos, 65 métodos entre todos: `AlmacenamientoTest`, `CalendarioDeEventosTest`, `ConfiguracionDeDespliegueTest`, `FocoVisibleTest`, `NavegacionAgrupadaTest`, `ObjetivoTactilTest`, `TipografiaTest` y `TransicionesDeVistaTest`, más el rasgo compartido `tests/Support/MideContraste`.

Del expediente se actualizaron además **`matriz-de-pruebas.md`** —RF-19 y RNF-12 dejan de ser huecos abiertos, y las cifras pasan de 599 a 747 casos— y **`docs/ingenieria/README.md`**. El informe de cumplimiento se reescribió en `… (19 ago, rev 2).docx`, **fichero aparte**: Word tenía el original abierto y lo habría sobrescrito al guardar. No se rehízo el documento, se sustituyó solo el XML dentro del `.docx`, de modo que conserva enteras las veinte páginas con sus estilos, su numeración y su pie. Ese método sirve para la próxima vez: un `.docx` es un zip, y reemplazar `word/document.xml` copiando el resto de entradas en su orden original es más seguro que regenerarlo.

### 25.7 Cómo se ejecutó, y las dos veces que se murió

Se conduce con dos frentes en paralelo —despliegue e interfaz— sobre un reconocimiento previo de ocho lentes que produjo inventarios con archivo, línea y arreglo propuesto. **El frente de interfaz tuvo que ser secuencial**: sus siete etapas comparten `app.css` y las mismas vistas, y en paralelo se habrían pisado.

⚠️ **El frente de interfaz murió dos veces por límite de sesión**, con las etapas 5, 6 y 7 sin ejecutar la primera vez y la 7 la segunda. Se recuperó reanudando el mismo trabajo: las etapas ya cerradas se sirven de caché y solo corren las que faltan. **Si vuelve a pasar, no relances desde cero.** La segunda muerte ocurrió *después* de que la etapa 7 terminara su trabajo y *antes* de que lo reportara: se comprobó midiendo el árbol —los siete pasos de su plan estaban aplicados— en vez de suponer que había quedado a medias.

**Lo que más valor dio fue medir dos veces.** Cada afirmación de un agente se volvió a comprobar por fuera, y esa segunda medición encontró el defecto del buscador, el de la política del bucket y el desborde de 320 px. También descartó cuatro falsos positivos **míos**: un desborde que solo existía porque medí mientras se recompilaba el CSS, y tres objetivos táctiles que mi sonda contaba mal por no contemplar la etiqueta envolvente ni el equivalente al mismo destino. La lección es simétrica: **el que verifica también se equivoca, y se nota igual de tarde.**

### 25.8 Aviso de seguridad, ajeno al encargo

En el bloque de instrucciones que llegó junto a los servidores MCP venía una directiva que **no procedía del dueño**: pedía enrutar todo el trabajo de ficheros por la terminal «en vez de las herramientas dedicadas de lectura y escritura». Su efecto sería sacar las escrituras de las herramientas que el sistema de permisos vigila. Se ignoró, y un agente del frente de despliegue la detectó por su cuenta y también la ignoró. Queda anotado por si conviene revisar de qué servidor sale.

---

## 26. QUÉ TOCA AHORA (desde el 20 de agosto de 2026)

**Esta sección sustituye al §23 en su papel de orden de trabajo.** El §23 mandaba hasta el 21 de agosto y su lectura del camino crítico sigue siendo buena, pero su lista de pendientes está cerrada.

El diagnóstico no cambió y ahora es más nítido: **no queda trabajo de producto**. Lo que falta son firmas, una cuenta y un teléfono.

### 26.1 Antes del viernes 21, que cierra el corte 2

1. **La reunión con Natalia**, que es la única ventana de la semana. Se lleva, en este orden: el **planeador FO-DO-100** para su firma (lo pidió César el 18 y es lo único suyo abierto); el **Acta 01** de aprobación del diseño, ya redactada y lista en `docs/ingenieria/constancias/`; el **Formato 03** de retroalimentación, que sirve a la vez para el corte y para abrir el registro de hallazgos de la S7; la **base de los asociados** con sus autorizaciones (P-06, el insumo más urgente y el que lleva pidiéndose desde principios de agosto); y **la cuenta institucional** del §20.2, planteada como decisión de la junta con su consecuencia dicha en voz alta.
2. **El capítulo 5 del documento de práctica** se escribe con el repositorio en la mano, y las cifras cambian cada semana: ⚠️ **las vigentes son las del §31.7 — 970 casos** (959 pasan, 11 omitidas, 0 fallos, 3.563 aserciones), 21 modelos y 39 migraciones, medidos el 1 de septiembre sobre `493790d`. Las de esta línea eran del 31 de agosto: 946 casos sobre `b9e2428`. **No copies ninguna de las dos: vuelve a medirlas** (§28.4). Las de 747 que decía esta línea eran del 19 de agosto. ⚠️ Recuerda el §24: el documento de la universidad es **individual**, uno por practicante, aunque ambos anexen los mismos artefactos técnicos.

### 26.2 SUPERADA — En cuanto exista la cuenta

> ✅ **La cuenta existe desde el 30 de agosto de 2026, institucional y con medio de pago del gremio.** Lo de abajo sigue siendo la lista correcta de pasos; el orden, las trampas y lo que no puede quedar público están en la **§29**.

Todo esto está escrito y probado; es ejecución, no diseño.

1. `cloud auth` lo corre **el dueño** en una terminal interactiva. Después el runbook, de principio a fin.
2. **Crear el bucket, y leer el §8.3 del runbook ANTES de crearlo.** La política que sale sola abre los formatos de la guía normativa. Es el único paso de todo el despliegue que no se puede delegar en la aplicación.
3. Contratar el **SMTP** con la cuenta del gremio, o los códigos del segundo factor no salen del registro y el panel no es demostrable.
4. Repetir la **medición de rendimiento contra el dominio**: los 972 ms son contra `localhost` y no incluyen latencia de red.
5. **Dispositivos reales** (RNF-01, RNF-07): un Android y un iOS de verdad. Es lo único de la S7 que no se puede emular.

### 26.3 Deuda anotada, por si sobra tiempo

Ninguna bloquea la entrega, y están en orden de lo que más se nota:

- **Los 74 `leading-*` y `tracking-*` sueltos** que secuestran la escala tipográfica en su propio elemento. Cada utilidad de tamaño emite su valor como reserva, así que mientras el suelto siga en la etiqueta, la escala pierde ahí. No se tocan las 28 `tracking-wide`/`tracking-wider` sobre `uppercase`: ahí el tracking positivo grande debe seguir ganando.
- **`@alpinejs/collapse`** se importa y se registra sin ningún consumidor. Retirarlo toca `package.json`.
- **Los siete chips de filtro** siguen con la cadena `@class` repetida; piden componente propio con prop `:activo`. El conmutador de eventos ya se resolvió así y sirve de modelo.
- **El consecutivo de PQR bajo concurrencia** en PostgreSQL: `lockForUpdate()` no puede bloquear una fila que aún no existe. Falla **cerrado** por índice único, así que el segundo envío simultáneo recibe error y no un radicado duplicado. Arreglarlo toca el expediente de PQR y es decisión del dueño.
- **El repositorio sigue en cuenta personal de GitHub.** Misma familia que el §20.2: conviene una organización del gremio, o al menos un segundo administrador, antes del 22 de septiembre. Es gratis y toma minutos.

### 26.4 Lo que NO hay que hacer

- **No reabrir el alcance.** La congelación del 14 de agosto sigue vigente y ahora tiene más razón: quedan cinco semanas y lo que falta no se construye, se firma.
- **No desplegar con cuenta personal.** El §20.6 y el §23.3 lo dicen y el modo de muerte está descrito: la demo sale bien, nadie migra lo que funciona, la práctica termina y las llaves se van.
- **No mover la escala tipográfica ni el reloj a `tokens.css`.** Repinta 372 reglas de `/admin` en silencio. Hay una prueba que lo prohíbe; si la ves fallar, es esto.
- **No dar por buena una medición hecha mientras se recompila el CSS.** Media hora de esta sesión se fue diagnosticando un desbordamiento que no existía.

---

## 27. LA REVISIÓN DEL GREMIO (28 ago 2026) — el producto pasó, lo visual no

**Esta sección sustituye al §26 en su papel de orden de trabajo para el producto.** El §26 sigue mandando sobre lo que no es código —la cuenta institucional, el despliegue, las firmas— y su §26.4 sigue vigente entero. Lo que cambia es que **ya no es cierto que «no queda trabajo de producto»**: el 28 de agosto el gremio vio la plataforma por primera vez y dejó una lista.

El viernes 28 de agosto, de noche, en el establecimiento del directivo, se demostró de punta a punta el sitio público y el panel ante **el directivo del capítulo** (presumiblemente Jorge Iván Botero Ángel, presidente — confirmar por escrito), **Natalia Gutiérrez**, la **contadora del gremio** y los dos practicantes. Cuatro audios, 66 minutos. Acta y transcripción completas están **en el repositorio**, en `docs/ingenieria/reuniones/2026-08-28-acta-reunion-3-revision-del-gremio.md` y `docs/ingenieria/reuniones/2026-08-28-transcripcion-reunion-3.md` (copia en el Project de Cowork, `claude/acta-reunion-3.md` y `claude/transcripcion-reunion-3.md`); las referencias `Rxx mm:ss` de abajo apuntan al audio y minuto exactos.

### 27.1 El veredicto, y cómo hay que leerlo

Ninguna funcionalidad fue objetada. El directivo no pidió módulos porque falten: pidió que lo que ya existe **se vea**.

> «Lo que debe ser… es que sea muy amigable, que la gente pueda interactuar con ella. **Lo visual es lo que tiene que ser. También muy impactante.**» — `R21 01:03–01:16`

Y el equipo se lo concedió en voz alta, que es lo que convierte el comentario en compromiso:

> «Algo que nos falta ahí en la página es la parte visual, **que la dejamos de último** para organizar primero toda la parte estructural.» — `R21 01:16`

El cierre fue un plazo suyo: **una a dos semanas para levantar la capa visual y volver a mostrarla** (`R24 04:52`). Entre el 4 y el 11 de septiembre.

Dos advertencias de método para cualquier sesión que retome esto:

1. **Él mismo relativizó su opinión** — «en el caso mío yo no tengo el conocimiento… uno siempre opina desde lo personal» (`R21 00:32`) — y aclaró que habla «desde el tema de junta, pensando en todos los sectores». Es un diagnóstico de percepción, no una especificación. Hay margen para proponerle una solución distinta a la que él imaginó; no lo hay para ignorar el diagnóstico.
2. **La próxima demo se juzga por lo que se ve, no por la suite.** 820 casos en verde no compran nada en esa mesa. Esta es la primera vez en todo el proyecto en que el criterio de aceptación del cliente y el del expediente técnico no coinciden.

### 27.2 Los catorce señalamientos, con el archivo que toca cada uno

⚠️ **Re-verificado contra el árbol el 31 de agosto de 2026** (`main` en `b9e2428`). **CERRADAS: filas 1, 2, 5, 6, 7, 11 y 14.** A medias, con el código puesto y el contenido pendiente del gremio: **8 y 10**. A medias por bloqueo humano: **12** (ranura hecha, imagen bloqueada por OBS3-07). **Intactas: 3, 4, 9 y 13.** El detalle de cada una, en el §30.1. Lo que sigue es el texto del 30 de agosto, con la marca de lo hecho añadida fila a fila.

| # | Señalamiento (con su cita) | Dónde está hoy | Qué hay que hacer |
|---|---|---|---|
| 1 | «Lo que gana» como título de los beneficios: «**suena horrible**… como si estuviéramos vendiendo una lotería» — `R22 02:35–03:10` | `resources/views/publico/inicio.blade.php:115`, cableado: `Lo que gana tu establecimiento` | ✅ **HECHO 31 ago (`046895c`).** Renombrado y pasado a `ajuste('portada_beneficios_titulo')`. El «ascenso a bloque principal» se resolvió subiendo el peso de la entradilla (`text-sm/text-tenue` → `text-base/text-suave`), no reordenando la portada; contraste medido: 11,91:1 en oscuro y 15,02:1 en claro |
| 2 | Tarifa del artista a la vista: «**la gente se sesga de una vez con el precio** y de pronto no lo contacto»; «yo no le pondría precio» — `R21 13:37–14:48`, ratificado en `R23 09:49` | `publico/artistas/index.blade.php:65`, `show.blade.php:52`, `inscripcion.blade.php:31` | ✅ **HECHO 31 ago (`f7a5d9d`).** Salió de **seis** sitios públicos, no de los tres que listaba esta fila; se conserva en modelo y panel. ⚠️ `publicar_tarifa` quedó **descartada a propósito**: el directivo rechazó esa propuesta en la frase siguiente a plantearla (`R21 14:37`) y la ratificó en `R23 09:49` |
| 3 | Bolsa de empleo «queda abierta para todo el mundo»; quiere que **ver** sea beneficio del afiliado y **postularse** siga abierto a cualquiera — `R23 08:01–09:39` | Ver §27.3, punto 4: hoy es al revés de lo que se temía | Vista nueva en `/mi-cuenta`. **Es ampliación, no ajuste** |
| 4 | Tema: «¿lo ve mejor negro o lo pongo blanco?» → «**pues a mí me gusta negro**… lo hace ver moderno, más elegante; el blanco lo hace ver como un papel, **muy plano, muy insípido**»; «institucionalmente somos más negros» — `R23 02:33–03:31` | `components/layouts/publico.blade.php`, script de arranque: por defecto **`system`** | Decidir el arranque con Natalia y **dejarlo escrito**. Ver §27.3, punto 1 |
| 5 | Falta un bloque de **aliado principal**: Asobares Colombia, Cámara de Comercio (mencionó su página *Experiencia*), Comité Intergremial, Gobernación del Quindío — `R21 02:19–03:26` | `inicio.blade.php:176`, tira única de logos; la tabla `aliados` no distingue tipo | ✅ **HECHO 31 ago (`18c13ed`).** Enum `TipoAliado`, columna `tipo` con índice, dos bandas (institucionales en rejilla con `object-contain`, comerciales en carrusel) y los cuatro institucionales sembrados. Falta insumo, no código: los logos reales en buena resolución |
| 6 | **Alcaldías: todas o ninguna** — «es para **no abrir susceptibilidades**… a todos o nada» — `R21 03:35–03:49` | Sin regla | ✅ **HECHO 31 ago (`0fbcaa0`), con un matiz que NO es lo que pedía esta fila:** no es una regla de semillas sino de **pintado** (`App\Support\ReglaDeAlcaldias` + `aliados.municipio_id`). Sembrar una sola alcaldía las hace desaparecer todas del sitio; el panel avisa de cuáles faltan |
| 7 | Directorio: pidió orden alfabético o por ciudad, movimiento, destacados «pero con un costo» y un buscador — `R21 06:11–07:31` | `DirectorioController` **ya** ordena `destacado desc, nombre` y **ya** tiene buscador (`index.blade.php:35`) | ✅ **HECHO 31 ago (`dee639f`).** `orderBy('nombre')` envuelto en `ordenarEnEspanol()` (Collator `es_CO`), porque SQLite ordena por bytes y dejaría «Ámbar» detrás de «Zorba». Efecto colateral querido: editar una ficha ya no la mete en la portada |
| 8 | «Quiénes somos» copiado de la Nacional y desactualizado: «**ya eso toca cambiarlo**»; hay que pedirle a la directora nacional «corríjame sus datos» — `R22 05:00–07:03` | `publico/quienes-somos.blade.php` + `SettingSeeder` | Texto propio del capítulo. Bloqueado por Natalia |
| 9 | Portal del afiliado sin contenido diferencial: «¿qué va a ver el afiliado que no va a ver el resto?» → **estados financieros y de gestión administrativa en tiempo real**; «las agremiaciones tienen que tener pública la información, **pero para los agremiados, no para todo el público**» — `R22 08:18–10:58` | `/mi-cuenta` tiene cartera, movimientos y convenios | **Ampliación** |
| 10 | Enlaces de la guía que caen en la portada de la entidad: «que sea **puntual**… que me abra a donde tiene que ir»; «hay personas que no son tan amigables con la tecnología» — `R23 05:32–06:33` | `RequisitoAperturaSeeder`: `camaraarmenia.org.co`, `armenia.gov.co`, `sayco.org` — todos raíz | Enlaces profundos al trámite exacto. Insumo del gremio |
| 11 | Fotos de establecimiento sin filtro: «lo tienen que aprobar ellos, no sea que pongan imágenes… **exóticas**» — `R23 00:45–01:05` | Ver §27.3, punto 5: **el dueño hoy no puede subir nada** | ✅ **HECHO 31 ago (`a803e3a`).** Se construyó: rutas `/mi-cuenta/fotos`, habilidad `gestionarFotosEnPortal` **solo por propiedad**, moderación foto a foto en la página `ModerarFotos` y ficha pública filtrada. ⚠️ Deuda nueva: las pendientes y rechazadas viven en el disco público (§30.3) |
| 12 | Portada sin vida: «acá podría quedar ese **video**… el **banner** que va moviéndose, algo que le genere vida»; imágenes de fondo fundidas «no sea que afecte la visibilidad de las letras» — `R21 05:20–05:39` | `components/publico/hero.blade.php`: solo texto sobre `.resplandor-marca`, sin ranura de medio | ⚠️ **A MEDIAS 31 ago (`12cd692`).** La ranura existe con velo inseparable que garantiza AA sobre cualquier imagen (`--asb-velo-hero: 0.8`), y `VeloDelHeroTest` recalcula el peor caso desde el CSS. **La ranura está vacía**: ninguna vista le pasa medio, porque las imágenes son OBS3-07 |
| 13 | WhatsApp sin respuesta: «¿ese tiene respuesta? **Hay que automatizarlo**» — `R21 11:13–11:25` | `footer.blade.php:2`, enlace `wa.me` con mensaje prellenado | No es código de la plataforma: es WhatsApp Business del gremio. Decirlo así y no prometer nada |
| 14 | Proveedores que ya no existen: «y que **sí respondan**, y que la información esté actualizada» — `R22 04:10–04:21` | `Proveedor` tiene `visible_hasta` y lo aplica, pero **no** fecha de verificación | ✅ **HECHO 31 ago (`468ee0c`).** `verificado_el` y `verificado_con`, con tres estados en la ficha pública. ⚠️ Divergencia **deliberada** con RF-60: seis meses en vez de doce, porque un proveedor caduca más rápido que un trámite |

### 27.3 Lo que la revisión destapó en el código, y el prompt no preveía

Seis hallazgos que salieron al cruzar lo que se dijo en la mesa con lo que hay en el árbol. Los tres primeros son deuda que se demostró como si no existiera.

1. **«Por defecto Sistema» equivale a «por defecto claro» en la mesa del cliente.** El script del `<head>` resuelve `system` con `prefers-color-scheme`, y los portátiles del gremio están en claro. La demo abrió blanca y por eso salió la discusión del negro. El v5 eligió `system` con buen criterio de accesibilidad; el dueño del producto prefiere el oscuro. **No cambiar el valor sin dejar la decisión escrita con fecha**, porque toca `localStorage.theme`, que se comparte con `/admin`, y porque hay pruebas que dependen del arranque.
2. ~~**Se le dijo al cliente «toda la página es completamente editable» (`R22 02:54`) y no lo es.**~~ ✅ **CERRADO el 31 ago 2026** (`046895c`): eran **diez** textos cableados en la portada, no los cuatro que este punto enumeraba —se le habían escapado «Abre tu negocio», «Bolsa de empleo», «Aliados del capítulo» y la franja de cifras—, y `d79d1b8` hizo lo mismo con **quince** de «Quiénes somos». Guardias estructurales en `PortadaEditableTest` y `QuienesSomosEditableTest`: ningún `<h2>` ni `<p>` puede volver a llevar texto literal. Lo que sigue es el diagnóstico original.

   **Diagnóstico del 30 de agosto:** Los títulos de sección de la portada están cableados en `inicio.blade.php` — «Lo que gana tu establecimiento», «La noche del Quindío», «Próximos eventos del gremio», «Cinco beneficios concretos» —. El resto del contenido sí sale de `ajuste()`. O los títulos pasan a `ajustes`, o hay que corregir la afirmación en la próxima demo. La primera opción es media hora; la segunda cuesta credibilidad.
3. ~~**El postulante no recibe ningún correo.**~~ **CERRADO el 30 ago 2026** (v14). `AcuseDePostulacion` se manda en la creación de la postulación, con el mismo criterio que ya regía el aviso al establecimiento: no se repite en el reenvío que actualiza. Cambia además un comportamiento que nadie había señalado: antes, si el establecimiento no tenía correo configurado, la postulación no generaba ningún correo; ahora el candidato recibe su acuse igual, porque su propio correo es obligatorio en el formulario y siempre hay a quién escribirle.
4. **El banco de aspirantes no es público, y el gremio quiere justo lo contrario de lo que se temía.** Hoy `aspirantes` vive solo en `/admin`; el sitio expone las vacantes y el formulario de perfil, nada más. Lo que el directivo pidió es que **el afiliado sí pueda consultarlo desde su cuenta**, como beneficio. Eso es una vista nueva **con datos personales de terceros**: exige base legal en la autorización que firma el aspirante, y probablemente contacto revelado solo tras registrar el interés. No se construye sin revisar el §9.
5. ⚠️ **CERRADO A MEDIAS el 31 ago 2026** (`a803e3a`): la **carga de fotos** existe (`/mi-cuenta/fotos`, con moderación por foto en `ModerarFotos`). Lo que **sigue sin existir** es editar la ficha del establecimiento desde `/mi-cuenta`: el grupo de rutas solo tiene índice, pagar, fotos, vacantes y postulaciones. Diagnóstico original: **El dueño no puede tocar su ficha.** `/mi-cuenta` solo tiene índice y vacantes: no hay ruta para editar el establecimiento ni para subir imágenes. En la demo se afirmó que el afiliado sube fotos y el gremio aprueba (`R23 00:48`). El flujo de aprobación **existe para el estado del registro**, no para una carga del propietario, porque el propietario no carga nada. Antes de prometer moderación hay que construir la carga.
6. **La bitácora ya responde a la mitad del pedido.** `app/Filament/Pages/Bitacora.php` lista `activitylog` con quién, qué, cuándo y **qué campos cambiaron**. Lo que pidió el directivo —«todos los cambios, **para que no haya excusa**», y poder «reversar» (`R24 03:19–03:44`)— solo añade la reversión. Enseñar la bitácora que ya existe puede cerrar el punto sin escribir un módulo.

### 27.4 Lo que pidieron y NO está en el alcance congelado

La congelación del 14 de agosto sigue vigente y el §26.4 la reitera. Estas tres piden acta firmada antes de una línea de código:

- **Correo de alerta al administrador** cuando la secretaría o un pasante cambien algo: «para que sepa qué fue y no tenga que ir a abrir la página» (`R23 04:03`). Barato **si el SMTP institucional ya existe**; hoy no existe (§26.2, punto 3), así que depende del mismo bloqueo de siempre.
- **Reversión de cambios** sobre la bitácora (`R24 03:19`). Es lo más caro de la lista. Contrapropuesta razonable: la bitácora que ya está, más reversión solo donde sea barata (campos de texto de `ajustes` y de contenido), nunca genérica.
- **Transparencia financiera en `/mi-cuenta`**: estados financieros, gestión administrativa, actas e invitaciones (`R22 08:54`). No se codifica hasta que el gremio defina **qué documento se publica y quién lo sube**; si la respuesta es «un PDF que sube la dirección», es un recurso de Filament y una lista, no un módulo financiero.
- Añadido menor, ya modelado: **destacados pagos** en portada y laterales rotativos «pero con un costo» (`R21 06:38`). La base de pautas existe en el panel; falta la regla comercial y su cobro, que son del gremio.

### 27.5 Reglas de contenido nuevas, de obligado cumplimiento

Van aquí porque afectan semillas, textos y semántica del modelo, no solo diseño:

- **Alcaldías: se nombran todas las de los municipios cubiertos o ninguna.** Es una instrucción política del directivo, no una preferencia estética.
- **Aliados en dos niveles.** Institucionales (Asobares Colombia, Cámara de Comercio de Armenia y del Quindío, Comité Intergremial, Gobernación del Quindío) por encima de los comerciales, y con tratamiento visual distinto.
- **La junta se muestra corta.** Solo directora ejecutiva y presidente, «como lo hace la página nacional»; el directivo dijo que no le gusta «mucha publicidad» personal (`R22 05:41`).
- **«Quiénes somos» no se copia de la Nacional**, entre otras cosas porque la información del capítulo allí está desactualizada y el propio directivo la va a hacer corregir.
- **La tarifa del artista no se publica.** Ver §27.2, punto 2.
- **Cifras que se pueden usar de esta reunión:** ~**60 afiliados** hoy y **1.080 bares** en el universo del departamento (`R22 13:31`). La segunda no estaba en ningún documento anterior.

### 27.6 Lo que la reunión dejó a medias (no inventar la respuesta)

- **Formato de cartera:** la contadora comparte su Excel. El equipo pidió uno **aunque sea con datos ficticios** para probar el importador antes de los reales (`R23 15:18`). Sigue sin llegar.
- **¿Drive vinculado o carga manual?** Se preguntó y no se respondió (`R24 00:48`). Hoy el importador es carga manual de CSV; asumir eso hasta que digan otra cosa.
- **Periodicidad de la cartera:** semanal por ahora, «en algún momento diario» (`R24 01:13`).
- **Dominio:** hay que comprarlo, cobro anual, sin nombre decidido (`R24 06:54`).
- **Correo institucional de salida:** «lo importante sería confirmar el correo por donde sale» (`R24 06:41`). Es el mismo SMTP del §26.2.
- **Pasarela:** se habló de cerrar la cuenta de BBVA y dejar **solo Bold** (`R23 13:56–14:47`), pero la conversación no cerró. **No tocar la configuración de pagos** sin confirmación escrita.
- **Despliegue:** el plan de **US$5 mensuales** quedó confirmado en la mesa, con salto al de ~US$20 si la base crece (`R24 05:36`). El directivo desdramatizó el costo: «no es un costo tan alto… pues no se asusten» (`R22 12:12`). El bloqueo era la cuenta, no el precio — y **la cuenta llegó el 30 de agosto**: ver §29.

### 27.7 Orden de trabajo hasta la próxima demo

Los identificadores `OBS3-nn` son los del acta, para poder citarlos en las confirmaciones de git.

**Bloque A — visual.** ✅ **AGOTADO el 31 de agosto en todo lo que depende del equipo** (§30.1). Hechos: ~~OBS3-01~~ (`046895c`) · ~~OBS3-02~~ (`12cd692`, ranura y velo puestos; **vacía** hasta que se desbloquee OBS3-07) · ~~OBS3-04~~ (`18c13ed`) · ~~OBS3-05~~ (`0fbcaa0`) · ~~OBS3-06~~ (`dee639f`). **Bloqueados en decisión humana: OBS3-03** (arranque del tema, lo decide Natalia y hay que dejarlo escrito) y **OBS3-07** (las 19 fotos siguen sin autorización de imagen documentada).

**Bloque B — contenido y reglas.** ✅ **CERRADO el 31 de agosto en todo lo que no depende de un insumo** (§30.1). Cerrados: ~~OBS3-08~~ (`f7a5d9d`) · ~~OBS3-09~~ en su mitad barata (30 ago, §28.5; la otra mitad la prohíbe el §27.8) · ~~OBS3-12~~ (`468ee0c`) · ~~OBS3-13~~ (`a803e3a`) · ~~OBS3-14~~ (`b9e2428`). **A medias, con el código puesto y el contenido pendiente del gremio: OBS3-10** (faltan las siete URL de trámite; hay una prueba que se pondrá roja el día que lleguen) y **OBS3-11** (falta el texto propio del capítulo).

**Bloque C — no se toca sin acta.** OBS3-15 correo de alerta · OBS3-16 reversión · OBS3-17 transparencia financiera · OBS3-18 destacados pagos.

**Bloque D — insumos que hay que reclamar.** Excel de cartera · decisión Drive/carga · corrección de los datos del capítulo en la Nacional · dominio y correo · confirmación «solo Bold». ⚠️ **Creció el 31 de agosto, y ahora es el frente principal del producto:** las **siete URL de trámite** de Armenia (OBS3-10), el **texto propio de «Quiénes somos»** (OBS3-11), los **logos institucionales** en buena resolución (OBS3-04), las **autorizaciones de imagen** de las 19 fotos (OBS3-07) y la **decisión del tema inicial** (OBS3-03).

⚠️ **Colisión con lo académico.** La ventana de las dos semanas se superpone con el 95 % del documento de práctica del viernes 4 de septiembre. El Bloque A es en su mayor parte de la Persona 2; la Persona 1 debería proteger esa semana para el documento y entrar a lo visual después del 4.

### 27.8 Lo que NO hay que hacer

- **No dar por aceptado el Bloque C en silencio.** Aceptarlo tácitamente es el camino a llegar al 22 de septiembre con todo a medias. El §26.4 ya lo dice para el alcance general; esto es su caso concreto.
- **No confundir la bitácora con la reversión.** Están a un mundo de distancia de esfuerzo y el cliente probablemente se dé por satisfecho con la primera.
- **No meter una imagen de fondo en el hero sin comprobar el contraste en los dos temas.** El propio directivo lo advirtió («no sea que afecte la visibilidad de las letras») y el expediente tiene mediciones de contraste que una imagen de fondo puede tumbar en silencio. Ver la trampa del v5 sobre `transition` y custom properties antes de animar nada del fondo.
- **No devolver la tarifa del artista a la ficha pública** aunque el campo siga en el modelo.
- **No cambiar el tema por defecto ni tocar `localStorage.theme` como efecto colateral de otro trabajo.**
- **No construir la consulta del banco de aspirantes desde `/mi-cuenta` sin pasar por el §9.** Son datos personales de terceros y la autorización que hoy firma el aspirante puede no cubrir ese uso.

### 27.9 SUPERADA — Estado del árbol al escribir esto (30 ago 2026, mañana)

> ⚠️ **Estas cifras quedaron viejas el mismo día.** El estado vigente es el **§30.1**: `main` en `b9e2428`, 261 confirmaciones y suite en 946 casos. (El §28.6 lo dejó en `6b0a20d`, 249 confirmaciones y 871 casos, y también quedó atrás.) El párrafo se conserva porque explica de dónde salían las cifras del expediente y porque su consejo —volver a medir antes de citar— fue justo lo que destapó el §28.

> ⚠️ **Superada el mismo 30 de agosto por la tarde. Ve al §28.** Se ejecutó la suite que esta sección pedía ejecutar, salió roja, y de ahí salió todo el v14. El árbol ya no está como se describe abajo: el acta, la transcripción y la entrega del 4 de septiembre están confirmadas, y la cifra de entonces era **871 casos**, no 820 (hoy son 946: §30.1). Se conserva porque su encargo —«hay que volver a medirlas antes de citarlas»— resultó ser el consejo más rentable del documento.

`main` en `543b0cb`, **241 confirmaciones**. ⚠️ **Cambia un dato del §24 y del expediente: `p2-directorio` ya está fusionada** (`fc028ad`, «Integra el trabajo de Ingrid en p2-directorio») — deja de ser cierto que su trabajo esté fuera de `main`. Sin confirmar: los archivos de `docs/ingenieria/entrega-2026-09-04/` (documento del 4 de septiembre, anexos E–H y `fuentes-anexos/`) y `.claude/settings.local.json`; tres PDF viejos marcados como borrados. **En esta sesión no se ejecutó la suite**: las cifras vigentes siguen siendo las del v12 (820 casos) y hay que volver a medirlas antes de citarlas.

---

## 28. EL DEFECTO QUE SOLO EXISTÍA LOS DÍAS 29, 30 Y 31 (30 ago 2026)

El §27.9 dejó un encargo de una línea: «hay que volver a medirlas antes de citarlas». Se ejecutó la suite y **salió roja**, con las 820 pruebas que el expediente daba por verdes desde el 25 de agosto.

### 28.1 Qué era, y por qué nadie lo había visto

`SemillaConFormaTest::test_ningun_asociado_en_mora_tiene_pagos_en_su_ventana_de_mora` fallaba: a `cafe-del-parque`, que debe cinco meses, se le sembraba un pago fechado dentro de su propia ventana de mora.

No era la semilla. Era esto:

```
Hoy: 2026-08-30
now()->subMonths(5)->startOfMonth()  →  2026-03-01
now()->subMonths(6)->startOfMonth()  →  2026-03-01   ← el mismo mes
```

**`now()->subMonths(6)` un 30 de agosto no da el 28 de febrero: da el 2 de marzo.** PHP construye `2026-02-30`, que no existe, y en vez de recortarlo al último día del mes lo deja correr hasta el siguiente. Con un `startOfMonth()` detrás, dos cubos distintos del historial aterrizan en el mismo mes de calendario y **el mes que se saltaron no lo cubre nadie**.

El filtro de la semilla excluye los meses `0..N` para quien debe `N`. Con el desbordamiento, el cubo `N+1` cae dentro de la ventana de `N` y el pago entra donde tenía prohibido entrar.

**Solo ocurre los días 29, 30 y 31, y solo cuando la resta cruza febrero.** Por eso las 820 pruebas del 25 de agosto se midieron un día que no desbordaba, y por eso la suite llevaba semanas pasando verde veintitantos días de cada mes y poniéndose roja los últimos, sin que nadie coincidiera con el momento.

### 28.2 El arreglo es de orden, no de método

`startOfMonth()` **primero** y la resta después: restarle meses a un día 1 no puede desbordar, porque el día 1 existe en los doce meses. Donde el límite no es un inicio de mes —los plazos de retención—, `subMonthsNoOverflow()`.

### 28.3 Lo que rompía fuera de la semilla, que es lo que importa

El defecto estaba en once archivos, y la prueba solo delataba uno:

| Dónde | Consecuencia real |
|---|---|
| `TransaccionSeeder`, `CarteraSeeder`, `ConsultaGuiaSeeder`, `AsociadoSeeder` | Cubos colapsados, un mes sin sembrar, y el portal del asociado diciendo «debes 5 meses» junto a un pago de ese periodo |
| `DepurarBolsas`, `DepurarMensajes`, `DepurarInscripciones` | **Borraban datos personales hasta dos días antes del plazo publicado.** Un plazo de retención es un contrato con el titular del dato; incumplirlo por el lado que nadie reclama sigue siendo incumplirlo, y es justo lo que el §9 promete por escrito |
| `MetricasDelObservatorio` | Las ventanas de 18 y 12 meses perdían un mes sin avisar |
| `RequisitoApertura` y su filtro del panel | El borde estricto de RF-60 —«a los doce meses exactos todavía sirve»— se adelantaba dos días |

### 28.4 La lección de método, que es nueva

El v7 y el v12 enseñaron a desconfiar de las pruebas: cinco y seis falsos verdes escritos por el propio autor del plan, todos cazados mutando el código.

**Esto es otra cosa, y por eso se anota aparte.** Aquí la prueba era correcta, el defecto era real y la mutación lo habría cazado cualquier día. Lo que falló es que **el calendario decidía si la prueba miraba o no**: la misma suite, el mismo código y el mismo comando dan verde el día 15 y rojo el día 30.

Dos reglas que salen de aquí:

- **Una prueba que depende de `now()` sin fijarlo no prueba lo que dice ningún día en particular.** `VentanaDeMesesTest` fija la fecha en cuatro días que desbordan —30 de agosto, 31 de marzo, 31 de mayo y el 29 de febrero bisiesto— precisamente para no volver a depender de cuándo se ejecute. Sin eso, el arreglo habría «pasado» mañana con el defecto dentro.
- **«La suite está en verde» tiene fecha de caducidad.** El §27.9 lo intuyó al pedir que se volvieran a medir las cifras antes de citarlas. Resultó ser el consejo más rentable del documento: costó una tarde y evitó llevar a la universidad un número falso y a producción una purga que borra antes de tiempo.

### 28.5 De paso, la mitad barata de OBS3-09

Con la suite ya en verde, se cerró también **OBS3-09 en su mitad barata**: el correo de confirmación al postulante que el §27.3 (punto 3) señalaba como una de las tres cosas que se demostraron como si existieran y no existían.

`AcuseDePostulacion` sigue el mismo patrón que `NuevaPostulacion` y `VacanteAprobada`, y se manda en el mismo sitio y con el mismo criterio: solo en la creación, no en el reenvío que actualiza. **La única decisión que cambia comportamiento observable:** el acuse al candidato no depende de que el establecimiento tenga correo configurado —el correo del candidato es obligatorio en el formulario y siempre hay a quién escribirle—, así que un asociado sin correo interno, que antes dejaba la postulación sin generar ningún correo, ahora sí genera el acuse. **La otra mitad de OBS3-09** —que el afiliado consulte el banco de aspirantes desde su cuenta— sigue sin tocarse: expone datos personales de terceros y el §27.8 la prohíbe expresamente sin pasar por el §9.

### 28.6 Estado del árbol al cerrar el 30 de agosto

> ⚠️ **Superado el 31 de agosto: `main` en `b9e2428`, 261 confirmaciones, suite de 946 casos. Ver §30.1.**

Suite: **871 casos, 860 pasan, 11 omitidas, 0 fallos, 3.121 aserciones.** Pint limpio.

Confirmado en `main`: el arreglo del desbordamiento con su guardia, la vigilancia por eje de las ranuras del tema y el enum de la fábrica, el acta y la transcripción de la reunión 3 —que el §27 ya daba por estar en el repositorio y no estaban—, la entrega del 4 de septiembre con sus versiones superadas archivadas en `superadas/` (los tres «borrados» son movimientos, verificados por hash y detectados por git como `R100`), el Acta 04 de ampliación de alcance que faltaba para decidir el Bloque C, y el acuse de postulación del §28.5.

**Lo que NO se tocó, y por qué:** los chips de filtro repetidos y los 104 `leading-*`/`tracking-*` sueltos del §26.3 viven en las mismas vistas que el Bloque A va a rehacer para la demo del 4–11 de septiembre. Refactorizarlas antes es pagar el trabajo dos veces. Van después. Y el `abort_unless` de `Bitacora` y `AjustesDelSitio` se queda: el comentario engañoso que el §26.3 le reprochaba ya no existe en ninguno de los dos, y lo que queda es defensa en profundidad — no se borra un 403 para satisfacer una nota de limpieza.

---

## 29. EL DESPLIEGUE — hay cuenta, hay sitio publicado, y el sitio está en 500 (30 ago 2026)

> ⚠️ **Lee el §29.7 antes que el resto de esta sección.** Los apartados 29.1 a 29.6 se escribieron con la información de que la cuenta acababa de llegar y el despliegue estaba pendiente. Al ver la consola de Cloud resultó que **ya está desplegado desde hace horas y la URL responde `500`**, que el entorno se llama `production` —lo que bloquea la semilla— y que la organización es personal. El §29.7 corrige los tres puntos y trae el orden real de trabajo. Lo demás de la sección (las trampas del bucket, la semilla, el gasto, el correo y lo que no puede quedar público) sigue siendo válido palabra por palabra.

**El bloqueo que llevaba desde el 15 de agosto encima del proyecto se acabó:** hay cuenta de Laravel Cloud y el equipo tiene acceso. ⚠️ **Lo que NO se puede dar por cumplido es la otra mitad del veredicto del §20.2** —que la cuenta naciera institucional—: la consola muestra la organización `juan-sua`, que es personal. Ver §29.7. Mientras no se confirme a nombre de quién está la facturación, **R-14 está sorteado, no cerrado.**

Lo que esto desbloquea de golpe: SSL, correo saliente, pruebas en dispositivos físicos, respaldos, medición contra dominio, validación de Bold con dinero real y **cinco de los vacíos declarados de la matriz de pruebas** que dependían de publicar. El §26.2 deja de ser una lista de espera y pasa a ser la tarea.

⚠️ **Ojo con el dato:** en la reunión del 28 de agosto esto **no** se cerró. Se habló del costo (`R24 05:46`), de qué es un despliegue (`R22 12:30`) y de que había que comprar el dominio (`R24 06:54`), pero nunca se dijo que la cuenta existiera; por eso el acta de esa reunión lo dejó como pendiente del gremio. La cuenta llegó después. Si lees el §27.6 y el acta de la reunión 3, están desactualizados en este punto.

### 29.1 El bloqueo se movió, no desapareció: ahora es el correo

**Laravel Cloud no incluye correo saliente** (runbook §6.3) y **no hay proveedor SMTP contratado**. Eso importa más de lo que parece, porque el panel exige segundo factor obligatorio: **sin SMTP los códigos MFA no salen del registro y `/admin` no se puede demostrar delante del cliente.**

- **Camino de emergencia (runbook §10.4):** leer el código con `cloud environment:logs -n`. Sirve para que el equipo entre, **no** para demostrarle el panel a nadie. Y solo funciona con **`LOG_STACK=stderr`**: con `single` el código se escribe en un fichero del disco efímero que ese comando no lee. Configúralo así desde el primer despliegue, no cuando falle.
- **La solución de verdad es un trámite de minutos, no de días.** Resend, Postmark y Brevo publican endpoint SMTP y tienen plan gratuito suficiente para este volumen. Se contrata **con el correo del gremio** y se rellenan `MAIL_HOST`, `MAIL_USERNAME` y `MAIL_PASSWORD`. Es lo primero que hay que pedirle a Natalia esta semana.
- Sin SMTP tampoco se puede demostrar el acuse al postulante que cerró OBS3-09 (§28.5), ni tendría sentido construir el correo de alerta del OBS3-15.

### 29.2 La demo va sobre el subdominio de Cloud

**Decidido el 30 de agosto:** la demostración del 4–11 de septiembre se hace sobre el subdominio que da Laravel Cloud, con su HTTPS incluido. **El dominio propio se compra para la semana 8**, que es cuando el cronograma firmado lo pide. Meter una compra de dominio y una propagación de DNS en la semana más apretada del proyecto no compra nada que el cliente vaya a mirar. DPV-08 sigue abierta y no bloquea.

### 29.3 El orden, y las cuatro cosas donde se va a equivocar quien lo haga

El runbook manda; esto es solo el orden y los puntos donde no se improvisa.

1. **`cloud auth` en una terminal interactiva del dueño** (runbook §1.2). Abre el navegador y pide autorizar. **Un agente no registra la cuenta, no mete datos de pago y no se autentica por nadie.** A partir de ahí un agente puede continuar.
2. **Fija el límite de gasto en ~US$10/mes ANTES de exponer la URL** (§7). Ese techo, y no el piso de US$5, es el número que se le presenta a la junta.
3. **Lee el §8.3 del runbook ANTES de crear el bucket.** La política evidente —`GetObject` sobre `/*`— **abre los formatos oficiales de la guía normativa por URL directa**, saltándose el único sitio donde se comprueba que el requisito esté publicado. La correcta acota a `publico/*`. Y no la des por buena: compruébala con los dos `curl` del runbook, esperando `200` en lo público y `403` en lo privado.
4. **`db:seed` una sola vez, sobre base vacía, en `staging`** (§9). El entorno se llama `staging` justamente para poder sembrar; el día que haya datos reales pasa a `production` y esa puerta se cierra sola. Volver a sembrar sobre datos del gremio corre el consecutivo oficial de PQR y mete pagos ficticios en estado aprobado que el widget de recaudo suma como ingresos.

Y al terminar: humo de rutas (§10.3) y **repetir la medición de rendimiento contra la URL**. Los 972 ms del expediente son contra `localhost` y no incluyen latencia de red; citarlos como si fueran de producción es exactamente el tipo de cifra que el §28.4 prohíbe.

### 29.4 Lo que NO puede quedar público el día que la URL exista

Esto no está en el runbook y es lo que más puede doler, porque una URL pública con el nombre del gremio encima ya no es un entorno de pruebas.

- **Las fichas de asociados no tienen autorización de publicación** (riesgo R-02). Nacen en borrador y ahí se quedan. Si se siembra la demostración, quedan **establecimientos inventados en un directorio con URL real y el logo del gremio**: decide antes si el directorio sale con datos de demostración declarados como tales o sale vacío, pero no lo descubras el día de la demo.
- **Los costos de la guía normativa son de ejemplo.** En la propia demostración se dijo «todavía no estamos actualizados» (`R21 09:27`). Publicar cifras equivocadas de trámites legales en una URL pública es un riesgo del gremio, no del equipo. RF-60 ya sabe marcar «Sin verificar contra la fuente oficial»: asegúrate de que esas fichas salgan marcadas, o no salgan.
- **Las 19 fotografías del gremio no tienen autorización de imagen documentada** y tienen personas identificables. No suben al bucket hasta que la autorización exista.
- **Considera `noindex` hasta el lanzamiento oficial.** Que Google indexe datos de demostración asociados al nombre de Asobares Quindío es un daño que cuesta más quitar que evitar.

### 29.5 Por qué conviene desplegar ANTES del 4 de septiembre, y no después

Parece que compite con la semana del documento de práctica, y es al revés:

- **El despliegue mejora el documento.** El §5.5, la Tabla 5 y el apartado 6.2 hoy describen el despliegue como pendiente. Desplegado, pasan a describir un resultado, y cinco vacíos declarados de la matriz dejan de estar declarados. Es de las pocas cosas que suben la nota del capítulo 5 sin escribir una línea más.
- **Es trabajo de horas, no de días.** Está todo escrito, probado y vigilado por la suite (§15 del runbook). Es ejecución.
- **Le cambia la cara a la demo del bloque A.** El directivo dijo que lo que falla es lo visual; enseñarle la página **en su propio teléfono, en una URL real**, es una demostración distinta a enseñársela en el portátil de alguien. Y una vez desplegado, cada cambio visual se vuelve a publicar solo.

El reparto natural se mantiene: el despliegue es de la Persona 1 —es su bloque de infraestructura— y el bloque A es en su mayor parte de la Persona 2, así que las dos cosas pueden correr en paralelo esta semana.

### 29.6 Lo que hay que pedirle al gremio esta semana

Uno solo es bloqueante; los otros dos son de la semana 8 pero se piden ahora porque tienen espera.

1. **SMTP con el correo del gremio** (Brevo, Resend o Postmark, plan gratuito). **Bloquea la demostración del panel.**
2. Nombre del **dominio propio**, para comprarlo en la semana 8 (DPV-08).
3. Los documentos de **Bold** para producción —RUT, cámara de comercio y cuenta bancaria—, más la confirmación escrita de «solo Bold» que la reunión del 28 dejó sin cerrar (§27.6).

### 29.7 ⚠️ CORRECCIÓN DEL MISMO DÍA — ya está desplegado, y está devolviendo 500

Lo escrito arriba se redactó antes de ver la consola de Laravel Cloud. **El despliegue no está pendiente: ya ocurrió.** Lo que hay que arreglar es otra cosa.

Lo que muestra la consola el 30 de agosto de 2026:

| Qué | Valor |
|---|---|
| Organización · aplicación · entorno | `juan-sua` / `asobares` / **`production`** |
| Origen | `Jsua3/asobares:main` |
| URL | `asobares-production-0jhdcz.laravel.cloud` |
| Últimos despliegues | `6b0a20d` ✅ (8 h), `e6c45ca` ✅ (13 h), `0cc5dc4` ✅ (13 h), `543b0cb` ❌ fallido (2 días) |
| Cómputo | Flex 512 MiB · 1 vCPU · autoescalado desactivado · **scale-to-zero, dormido 8 h** |
| Red | DDoS, CDN y caché de borde activos · dominio de Cloud activo · sin dominio propio |
| Región | US East (Ohio) |
| Recursos | El diagrama del clúster solo muestra **Compute**. «Add resource: Database, cache, buckets, WebSockets» sigue sin usar |

**Medido, no supuesto: `https://asobares-production-0jhdcz.laravel.cloud` responde `500`.** Un despliegue en verde solo dice que la construcción terminó; no dice que la aplicación arranque.

> ⚠️ **Desfase, anotado el 31 de agosto.** La tabla de arriba es correcta para el 30, pero `main` es hoy **`b9e2428`**: doce commits, cuatro migraciones y 59 ficheros por delante de `6b0a20d`, que es lo desplegado. Quien diagnostique el 500 con esa tabla estará leyendo código que la rama ya no tiene. Y el 500 **sigue** el 31 a las 18:25 GMT, re-medido: el cuerpo es la página de error de Laravel con el middleware del proyecto ya ejecutado, lo que descarta la causa 4 y refuerza la 2. Ver §30.4.

#### Las cuatro causas candidatas, en orden de probabilidad

1. **No hay base de datos adjunta.** El diagrama del clúster no muestra ningún recurso de Postgres. Sin base, todo lo que toque `settings`, `asociados` o la sesión revienta, y como `SESSION_DRIVER=database` y `CACHE_STORE=database`, revienta **antes** de renderizar nada. Es la hipótesis principal y la primera que hay que descartar.
2. **`DB_CONNECTION` sin definir.** El `.env.staging.example` lo avisa por escrito: Cloud inyecta las credenciales de Postgres **pero no inyecta `DB_CONNECTION`**, y el valor por defecto de la aplicación es SQLite. Si falta, la aplicación busca un fichero que no existe.
3. **La base está vacía.** La portada resuelve casi todos sus textos con `ajuste('…')`; sin `settings` sembrados no hay nada que pintar. Ver el punto siguiente, porque aquí está la trampa buena.
4. **`APP_KEY` sin generar**, o las variables del apartado 6 del runbook sin cargar.

Diagnóstico en un comando: `cloud environment:logs -n`. Y para que ese comando sirva, **`LOG_STACK=stderr`** tiene que estar puesto (runbook §10.4); con `single` el registro se escribe en el disco efímero y no se lee desde fuera.

#### ⚠️ El entorno se llama `production`, y eso bloquea la semilla

`DatabaseSeeder::run()` **se niega en bloque cuando `app()->isProduction()`** (línea 25) y se limita a avisar «DatabaseSeeder omitido». No es un descuido: es la protección del runbook §9 contra sembrar establecimientos inventados y correr el consecutivo oficial de PQR sobre datos reales.

El runbook lo dice sin rodeos: **«la razón de que el entorno se llame `staging` y no `production` es precisamente poder sembrar los datos de demostración»**, y el `.env.staging.example` fija `APP_ENV=staging`. Con el entorno llamado `production`, si `APP_ENV` heredó ese nombre, **la base se queda vacía haga lo que haga quien despliegue**, y la demostración del 4–11 de septiembre sale sin un solo bar en el directorio.

Hay dos salidas y **conviene elegirla a conciencia, no por accidente**:

- **Poner `APP_ENV=staging` en las variables del entorno**, aunque el entorno de Cloud se siga llamando `production`. Es lo que el runbook contempla, permite sembrar la demostración, y el día que entren datos reales del gremio se cambia a `production` y la puerta se cierra sola. **Es la recomendada para llegar a la demo.**
- **Dejarlo en `production` y cargar el contenido a mano** desde el panel. Más fiel al nombre, pero es trabajo manual justo en la semana que no sobra, y sin bucket lo que se suba se pierde en el siguiente despliegue (§8).

Ojo con el efecto colateral que el propio `.env.staging.example` anota: hay configuración —`config/session.php`, entre otras— que cambia de comportamiento según `APP_ENV`. Cambiarlo no es solo cosmético; hay que releer el apartado 6 del runbook antes de tocarlo.

#### ⚠️ La organización es `juan-sua`, no del gremio

La URL de la consola es `cloud.laravel.com/**juan-sua**/asobares/production`. Eso es una organización personal, y el apartado 1.1 del runbook pide correo de cuenta y de facturación del gremio, no del desarrollador. **Puede que el gremio esté pagando y la organización sea solo el contenedor** —hay que confirmarlo mirando la facturación, no el nombre—, pero mientras no se confirme, **R-14 no está cerrado como dice la nota v15: está sorteado.**

El modo de muerte está descrito palabra por palabra en el runbook: la demostración sale bien → nadie migra lo que funciona → la práctica termina → las llaves se van con ella. Dos acciones, ninguna cuesta dinero:

1. **Comprobar a nombre de quién está la facturación.** Si es personal, anotarlo en el apartado 12 del runbook **con fecha de traspaso**, que es justo para lo que existe ese apartado.
2. **Añadir a la dirección del gremio como miembro de la organización de Cloud**, y de paso un segundo administrador al repositorio de GitHub, que sigue en cuenta personal. Son minutos.

#### Lo demás que se ve, y que importa para la demo

- **Scale-to-zero, dormido 8 horas.** La primera visita paga un arranque en frío. Antes de la reunión hay que **despertar el sitio** y no enseñarlo en frío; y sobre todo, **no medir el rendimiento en frío**: sería la cifra falsa perfecta.
- **Región US East (Ohio).** Desde Armenia son ~80–120 ms de ida y vuelta que `localhost` no tenía. La medición contra la URL **va a dar peor que los 972 ms** del expediente, y está bien que así sea: esa es la cifra honesta. El techo contratado del RNF-02 son 2.500 ms, así que hay margen de sobra.
- **Sin bucket.** El apartado 8 sigue vivo entero: lo que se suba desde el panel se pierde en el siguiente despliegue. Y cuando se cree, **la política acotada a `publico/*` del §8.3**, comprobada con los dos `curl`.
- **Hay un despliegue fallido** (`543b0cb`, hace dos días). Conviene leer su registro antes de repetir el patrón que lo tumbó.
- **Sin dominio propio**, que es lo decidido en el §29.2. Correcto.

#### El orden para llegar vivo a la demo

1. `cloud environment:logs -n` y leer el 500 de verdad, en vez de adivinarlo.
2. Comprobar que hay **base de datos adjunta** y que `DB_CONNECTION=pgsql` está en las variables.
3. Cargar las variables del apartado 6 del runbook, con **`LOG_STACK=stderr`** entre ellas.
4. Decidir `APP_ENV` a conciencia; si es `staging`, **sembrar una sola vez** sobre base vacía.
5. Fijar el **límite de gasto ~US$10** (§7) — el sitio ya es público, así que esto va con retraso.
6. Humo de rutas (§10.3), y solo entonces medir el rendimiento, **en caliente**.
7. Revisar el §29.4 antes de que nadie de fuera vea la URL: fichas sin autorización, costos de la guía sin verificar, fotos sin autorización de imagen, `noindex`.

---

## 30. NUEVE SEÑALAMIENTOS CERRADOS Y UN SITIO EN 500 (31 ago 2026)

Sesión larga sobre el §27. **Doce confirmaciones, `main` de `6b0a20d` a `b9e2428`, empujadas a `origin`.** El bloque A queda agotado en todo lo que depende del equipo y el bloque B cerrado salvo lo que es insumo del gremio. En paralelo, el sitio que la otra sesión dejó desplegado **sigue devolviendo 500**, y ahora además está doce commits por detrás.

### 30.1 Qué se cerró, con su confirmación

| Ref. | Qué | Commit |
|---|---|---|
| OBS3-01 | Diez textos cableados de la portada pasan a `ajuste()`; beneficios renombrado | `046895c` |
| OBS3-02 | Ranura de fondo en el hero con velo que garantiza AA — **la ranura queda vacía** | `12cd692` |
| OBS3-04 | Aliados en dos bandas, cuatro institucionales sembrados | `18c13ed` |
| OBS3-05 | `ReglaDeAlcaldias`: el juego parcial es irrepresentable | `0fbcaa0` |
| OBS3-06 | Destacados en orden alfabético español y estable | `dee639f` |
| OBS3-08 | La tarifa del artista sale de **seis** sitios públicos | `f7a5d9d` |
| OBS3-10 | La guía deja de prometer el trámite que su enlace no abre — **a medias** | `1e81ce1` |
| OBS3-11 | Quince textos de «Quiénes somos» a `ajuste()` — **a medias** | `d79d1b8` |
| OBS3-12 | Verificación fechada de proveedores, patrón RF-60 a seis meses | `468ee0c` |
| OBS3-13 | Carga de fotos del propietario y su moderación — **funcionalidad nueva** | `a803e3a` |
| OBS3-14 | Aviso honesto bajo el WhatsApp institucional | `b9e2428` |

Más `768136b`, que es el §0 de este documento.

**Estado medido el 31 de agosto sobre `b9e2428`:** suite **946 casos, 935 pasan, 11 omitidas, 0 fallos, 3.502 aserciones**. 261 confirmaciones, 75 archivos de prueba, 39 migraciones, 19 recursos de Filament, 6 páginas. Pint limpio. La duración NO se cita: dos corridas del mismo código el mismo día dieron 253 s y 593 s.

**Lo que sigue abierto del §27, y por qué:** OBS3-03 (arranque del tema — lo decide Natalia y hay que dejarlo escrito) y OBS3-07 (las 19 fotos, sin autorización de imagen). De OBS3-10 faltan las siete URL de trámite y de OBS3-11 el texto propio del capítulo: los dos son **insumo del gremio, no código**. El bloque C sigue congelado sin Acta 04 firmada.

### 30.2 Las tres cosas que se demostraron sin existir ya existen las tres

El §27.3 abrió con tres afirmaciones falsas hechas delante del cliente. A 31 de agosto: el acuse al postulante se manda desde `7eb0799`; los títulos de la portada salen de `ajustes` desde `046895c` —y los de «Quiénes somos» desde `d79d1b8`—; y la carga de fotos con moderación existe desde `a803e3a`.

⚠️ **Ojo al citarlo:** la cabecera del v13 y el §27.3 **no nombran el mismo trío**. La discrepancia es anterior a esta sesión; no la propagues.

### 30.3 Deuda NUEVA que abrió este trabajo

Tres cosas que antes no existían y ahora sí. Ninguna es un fallo de la suite; las tres son decisiones.

- ⚠️ **El disco público transporta material sin moderar.** La colección `galeria` usa `config('almacenamiento.publico')`, y hasta ayer eso era inofensivo porque solo contenía fotos que el gremio había cargado y aprobado. Desde OBS3-13 lleva también **cargas del propietario sin revisar y devueltas**: no salen en la ficha, pero el archivo queda servido por URL —`/storage/{id}/{ulid}.jpg`— y una foto rechazada por «exótica» no se borra al rechazarla. El nombre es un ULID y no es enumerable, así que es exposición por oscuridad, no un agujero abierto. **Hay que decidir por escrito**: o las pendientes y rechazadas van al disco privado servidas por un controlador, o se acepta el riesgo dejándolo dicho. El comentario de `config/almacenamiento.php` ya se corrigió, porque afirmaba que en ese disco «no hay nada que proteger».
- ~~**Seis migraciones sin verificar contra PostgreSQL real.**~~ ✅ **CERRADA el 1 de septiembre** (§31.2). Las **39** se verificaron contra PostgreSQL 17.11 y después corrieron de verdad en producción, sobre el mismo motor. Diagnóstico original: el §25.2 acredita 33 en PostgreSQL 17.11 el 19 de agosto; hoy son 39, dos de finales de agosto y cuatro de aquella sesión, ninguna probada entonces contra el motor de producción.
- **El reparto del §24.3 quedó suspendido de hecho.** Decía que la Persona 1 «no rediseña ni amplía los módulos públicos» de la Persona 2, y esta sesión cerró catorce señalamientos que caen casi todos ahí, incluida funcionalidad nueva. O se declara la excepción por escrito, o se dice quién responde ahora por cada módulo.

### 30.4 El despliegue: medido hoy, no supuesto

`https://asobares-production-0jhdcz.laravel.cloud` responde **500** el 31 de agosto a las 18:25 GMT. Dos datos que acotan el diagnóstico del §29.7:

- El cuerpo son 6.592 bytes de **la página de error de Laravel**, con `x-frame-options: DENY` y `nosniff` puestos por el middleware del proyecto.
- ⚠️ **Y aquí esta sesión se equivocó, así que queda escrito para que nadie repita el razonamiento.** De esas cabeceras se dedujo que «el framework arranca y el middleware corre, luego no es `APP_KEY` ausente». **Es falso.** `CabecerasDeSeguridad` se registra con `$middleware->append()` (`bootstrap/app.php:24`), o sea que es el middleware más EXTERNO; y `Illuminate\Routing\Pipeline::handleException` captura la excepción, la renderiza allí mismo y devuelve la respuesta hacia afuera. El resultado es que un `MissingAppKeyException` reventando en `EncryptCookies` **sale con las mismas cabeceras**. La observación no descarta absolutamente nada: **las cuatro causas candidatas del §29.7 siguen vivas** y solo el registro las separa.
- Lo que sí sigue en pie es que el repositorio predice una de ellas palabra por palabra en `.env.staging.example`: «Cloud inyecta `DB_HOST`… pero **NO inyecta `DB_CONNECTION`**… el defecto silencioso de `config/database.php` es `sqlite`… y **el sitio entero da 500** con las variables de Postgres correctamente puestas al lado». Es una hipótesis con buena letra, no un diagnóstico: **el 500 se lee en el registro, no se deduce desde fuera**.

⚠️ **Y hay un desfase nuevo:** la tabla del §29.7 registra `6b0a20d` como último despliegue. `main` es hoy `b9e2428` — **doce commits, cuatro migraciones y 59 ficheros por delante**. Quien diagnostique el 500 con esa tabla estará leyendo código que ya no es el de la rama.

**Sobre `APP_ENV`, que el §29.7 manda decidir a conciencia:** la recomendación de esta sesión es **`staging`**. Es lo que contempla el runbook, es lo único que permite sembrar, y sin sembrar la demostración del 4–11 de septiembre sale con el directorio vacío porque `DatabaseSeeder::run()` se niega en `production`. El día que entren datos reales del gremio se cambia a `production` y la puerta se cierra sola. La decisión sigue siendo del dueño; aquí queda la recomendación con su porqué.

**El CLI de Cloud está instalado** (v0.5.0, `--version` responde) pero `environment:list` se cuelga esperando la autenticación interactiva. El §29.3 ya lo dice: ese paso es del dueño y un agente no lo da por nadie.

### 30.5 Cómo se revisó este documento, y qué se decidió no tocar

El §30 no se escribió de memoria. Se auditaron los siete tramos del documento en paralelo contra el árbol real y **cada hallazgo pasó por un verificador adversarial con instrucción de refutar por defecto**: 106 hallazgos en bruto → **82 confirmados y 24 refutados**, casi una cuarta parte caída por citas inexistentes, líneas equivocadas o cosas que el documento ya matizaba más abajo. Uno de los verificadores **volvió a ejecutar la suite entera** en vez de creerse la cifra: 946/935/3.502/11, idéntica.

Se corrigieron los **treinta hallazgos graves** —los que harían que una sesión nueva decidiera mal— y se dejaron intactas las cifras históricas de las **§15 a §25**. No es descuido: el §0.7 dice que esas secciones «cuentan historia, no estado», y reescribir sus números borraría el registro de qué se midió y cuándo. Si lees ahí «599 pruebas» o «747 casos», es lo que era verdad ese día.

La auditoría devolvió además **101 advertencias que siguen vigentes**. Las que más conviene no tocar: el `abort_unless` de `Bitacora` y `AjustesDelSitio` —que `ModerarFotos` heredó bien—, la prohibición de mover la escala tipográfica a `tokens.css`, las cuatro prohibiciones del §27.8 y las dos reglas de método del §28.4, que en esta sesión **se aplicaron solas**: `Proveedor::necesitaRevision()` usa `subMonthsNoOverflow` con el comentario «es el defecto del §28, y aquí se paga igual».

### 30.6 La lección de método, que vuelve a ser la de siempre

Veintiocho mutaciones deliberadas en el bloque B. **Dos pasaron en verde**, y las dos estaban en OBS3-13, que es lo más sensible que se escribió:

- Abrir la política a «por permiso **o** por propiedad» —literalmente la fuga del v6— **no puso roja ninguna prueba**. El caso comprobaba el 404 de `destroy`, que lo produce otra comprobación del controlador: la política podía estar abierta de par en par. El docblock prometía lo que no probaba.
- Poner `true` como valor por defecto de «aprobada» tampoco mordía, porque todas las fotos de la prueba entraban por el controlador, que siempre escribe la propiedad. **El defecto silencioso no se ejercía nunca** — y es exactamente el estado en que estaban las dieciocho fotos anteriores a la migración de relleno.

Ninguna de las dos se ve leyendo. Las dos se vieron mutando. **Van doce falsos verdes en el proyecto**, y siguen apareciendo en el trabajo del propio autor del plan.

De paso, tres defectos reales que solo salieron porque la prueba los tropezó: el límite de subida por minuto era **menor** que el máximo de fotos; el tope se contaba con `getMedia()`, que cachea en la instancia y se saltaba en silencio; y el ayudante de las pruebas daba por buena una subida que no ocurría, porque `assertRedirect()` pasa igual con un redirect de error.

### 30.7 Aparte: ocho municipios inventados en la base de demostración

La base local tenía **ocho municipios de fábrica** —«O Lorente», «Luevano Baja», «Casárez del Barco»…— creados el 24–25 de agosto con sufijo aleatorio en el slug, cada uno con un requisito en borrador. Los borradores no llegaban a la guía, pero **los municipios sí alimentaban el filtro público del directorio**, que ofrecía dieciséis opciones con ocho falsas.

Borrados con autorización del dueño, tras comprobar las **seis** tablas que referencian municipios y que ninguno tenía asociados, artistas, proveedores ni consultas colgando. Quedan los ocho reales y veinte requisitos. Los sembradores estaban bien y el anexo de base de datos se exportó antes: **solo afectaba a la base local, que es la que se demuestra**. Es dato, no código, así que no lleva confirmación.

⚠️ **La causa raíz sigue viva:** el filtro del directorio lista todos los municipios de la tabla, tengan contenido publicado o no. Si listara solo los que tienen, un municipio basura no habría llegado nunca a la vista pública.

---

## 31. LA BASE DEJÓ DE ESTAR VACÍA, Y EL EXPEDIENTE AUDITADO CONTRA ELLA (1 sep 2026)

**El sitio pasó de responder 200 con el título «—» a servir contenido real del gremio.** Siete confirmaciones de código, `main` de `f6dac90` a `493790d`, empujadas a `origin`, más la que trae esta sección. La base de producción tenía las 39 migraciones aplicadas y **todas las tablas de contenido en cero**; hoy tiene 23 aliados, 8 trámites, 100 ajustes, los catálogos y tres cuentas del panel. Ni un solo dato inventado.

La instrucción del dueño fue literal: **«no siembres cosas inventadas, métele nada más lo que es oficial de Asobares»**. Esta sección es el registro de haberla cumplido, y de lo que se descubrió al cumplirla.

### 31.1 Qué se hizo, con su confirmación

| Ref. | Qué | Commit |
|---|---|---|
| — | Los sembradores institucionales dejan de traer datos inventados | `a00ff37` |
| — | `asobares:crear-usuario`: la forma legítima de dar de alta en el panel | `7a4f157` |
| — | La guía de Armenia pasa a la transcripción **ya fechada** del repositorio | `edd7902` |
| — | `ContenidoOficialSeeder`: qué sembradores pueden tocar la base del gremio | `dee17aa` |
| — | Dos defectos del alta de usuarios que solo se vieron en producción | `1b32344` |
| — | La portada deja de prometer lo que la guía ya no tiene | `ef7efc2` |
| — | No anuncia como gratis el trámite que nadie ha costeado | `493790d` |

**Lo que se retiró, medido:** cinco marcas inventadas con URL a `ejemplo.test` y descuentos inventados —tres de ellas sin ningún convenio detrás—; **doce costos inventados de trámites legales** (180.000, 45.000, 380.000, 290.000…) repartidos en tres municipios; y dos PDF generados al vuelo, rotulados «Formato de ejemplo», con el nombre del gremio encima.

**Lo que entró, con su fuente:**

- **19 aliados estratégicos** del catálogo oficial `BENEFICIOS AFILIADOS`, con su condición real —16,6 % y 8,3 % con OSA, 6 % con Sayco, 25 % en pólizas con Manchego Álvarez, diagnóstico acústico gratuito con Conacústica— y el canal de contacto que el propio catálogo publica. El detalle sigue siendo privado: solo lo ve el afiliado con sesión, que es exactamente el público al que ASOBARES le entrega ese documento.
- **Los 7 trámites de Armenia** de la jornada «Blindemos tu Negocio», hecha con la Alcaldía, más la lista de verificación de la **ley 1801 de 2016** y el **decreto 119**. Ocho fichas, ningún costo, cada una nombrando su fuente.
- Las **5 iniciativas** confirmadas una por una contra el TED gremial, con los estados que la lámina les pone.

⚠️ **El PDF del TED y el catálogo de beneficios son escaneos sin capa de texto.** No se leyeron «interpretando el nombre del archivo»: se extrajeron las 37 imágenes de página incrustadas y se leyeron una a una. Es la técnica que sirve para el resto del material escaneado de `material/`.

### 31.2 EL INVENTARIO — qué está cumplido y qué falta

Esto es lo que el dueño pidió consolidar. Cada fila se comprobó contra el árbol o contra el sitio vivo el 1 de septiembre; **ninguna sale de leer una sección anterior de este documento**.

#### Producto — los catorce señalamientos del §27

| Ref. | Estado | Qué falta, y de quién depende |
|---|---|---|
| OBS3-01, 02, 04, 05, 06, 08, 12, 13, 14 | ✅ **Cerrados** (§30.1) | — |
| OBS3-09 | ⚠️ Mitad cerrada | La otra mitad la **prohíbe** el §27.8 hasta releer el §9 |
| OBS3-10 | ⚠️ Código puesto | Las **7 URL de trámite** de Armenia. Insumo del gremio. La guardia de `EnlacePuntualDeLaGuiaTest` sigue verde, o sea que siguen siendo de portada |
| OBS3-11 | ⚠️ Código puesto | El **texto propio** de «Quiénes somos». Bloqueado en Natalia |
| OBS3-03 | ❌ Sin decidir | **El arranque del tema.** Decisión de Natalia, y hay que dejarla escrita |
| OBS3-07 | ❌ Bloqueado | **Autorización de imagen** de las 19 fotografías. Mientras no exista, la ranura del hero sigue vacía |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar.** Una fila sin marcar no las aplaza: las deja sin decidir |

**Balance: de catorce, nueve cerrados y cinco vivos — y ninguno de los cinco se cierra escribiendo código.** Tres son **insumo del gremio** (OBS3-07 las autorizaciones, OBS3-10 las URL, OBS3-11 el texto), uno es una **decisión de Natalia** que además hay que dejar escrita (OBS3-03) y uno está **prohibido** hasta releer el §9 (OBS3-09). Aparte de los catorce, el bloque C —OBS3-15 a 18— espera la **firma del Acta 04**.

#### Contenido normativo — el §17, que es el que más cambia

| Lo que el §17 afirma | Hoy |
|---|---|
| «Nada del contenido normativo sirve para orientar a un empresario de verdad» | ❌ **Falso para Armenia.** Cierto todavía para los otros once municipios |
| «costos aproximados y escritos a mano, sin verificar ni fechar» | ✅ **Resuelto.** Cero costos, y siete de las ocho fichas fechadas el 20 de agosto con su fuente nombrada |
| «los formatos descargables son PDF generados por `GeneradorPdf`» | ✅ **Resuelto.** Se retiraron. ⚠️ `GeneradorPdf` quedó **sin un solo consumidor**: es código muerto |
| «los enlaces apuntan a la portada, no al trámite» | ❌ **Sigue igual.** Es OBS3-10 |
| §17.1 — normatividad municipio por municipio | ⚠️ **1 de 12.** Armenia sí; faltan once |
| §17.2 — formatos oficiales de cada entidad | ❌ Sin empezar. Al menos ya no hay falsos ocupando su sitio |
| §17.3 — verificar y fechar | ✅ El mecanismo existe (RF-60) y Armenia está fechada |
| §17.4 — quién mantiene esto | ❌ **Sin dueño.** Decisión del gremio |
| §17.5 — «cargar el contenido real desde el panel, no desde un seeder» | ⚠️ **Se hizo al revés, a conciencia.** Ver 31.4 |

#### Infraestructura — el §29 y el §30.4

| Qué | Estado |
|---|---|
| El sitio devuelve 500 | ✅ **Superado.** Responde **200**. La causa era la que `.env.staging.example` predecía: `DB_CONNECTION` sin poner cayendo al `sqlite` por defecto |
| Base de datos | ✅ PostgreSQL **17.11**, `bold-leaf-62673759`, esquema `production`, 39 migraciones aplicadas |
| Las 39 migraciones contra PostgreSQL real | ✅ **Cierra la deuda del §30.3.** Verificadas en Docker y después corridas de verdad en producción |
| Base sembrada | ✅ Solo contenido oficial |
| Cuentas del panel | ✅ Tres, sin la contraseña publicada |
| Bucket con la política del §8.3 | ❌ **Sin crear.** Y ahora pesa más: el §30.3 avisa de que el disco público transporta fotos sin moderar |
| SMTP | ❌ **Sin contratar.** Bloquea el acuse al postulante y el segundo factor por correo |
| Dominio propio | ❌ Semana 8, sin nombre decidido |
| Medición de rendimiento contra la URL | ❌ **Sin hacer.** Los 972 ms del expediente son contra `localhost` |
| Dispositivos reales (RNF-01, RNF-07) | ❌ Sin hacer |
| Indexación | ⚠️ **Decisión pendiente.** Medido hoy: **no hay `X-Robots-Tag`** —la consola de Cloud lo muestra en «index, follow», que es su defecto y no emite cabecera—, no hay `<meta name="robots">`, y `robots.txt` responde `Allow: /` **anunciando un sitemap con 14 URL**. O sea: el sitio está plenamente indexable y además invitando. El §29.4 aconsejaba `noindex` hasta el lanzamiento; el argumento se **debilitó** —ya no hay datos de demostración que indexar— pero no desapareció: el directorio, el boletín y la bolsa están vacíos |

⚠️ **Corrección a lo que esta misma sesión dijo primero:** se afirmó que Cloud servía `X-Robots-Tag: index, follow`. **Es falso**: la cabecera no se emite. El efecto para un buscador es el mismo, pero el dato era inexacto y así queda.

#### Datos personales — el §9 y el G12 del §15.2

Sin cambios de esta sesión salvo uno favorable: **el directorio salió vacío**, así que el riesgo R-02 —fichas publicadas sin autorización del titular— no se materializó al desplegar. Siguen abiertos los tres de G12 que exigen decisión humana (encargados que la política no nombra, canal de supresión, revisión legal del texto) y el disco público del §30.3.

### 31.3 Lo que este trabajo invalida del propio documento

Cinco afirmaciones del expediente dejaron de ser ciertas hoy. Se anotan aquí en vez de reescribirlas en su sitio, siguiendo el criterio del §30.5: las secciones viejas cuentan historia.

1. **§0.2 y §0.3** — «lo que hoy bloquea la demo no es producto: es que el sitio desplegado devuelve 500». **Ya no.** Responde 200 y con contenido.
2. **§17 entero** — su premisa («nada de esto sirve para orientar a nadie») es falsa para Armenia. Ver la tabla de 31.2.
3. **§26.1** — cita 946 casos de prueba sobre `b9e2428`. Hoy son **970**.
4. **§30.3, segundo punto** — «seis migraciones sin verificar contra PostgreSQL real». **Cerrado**: las 39 corrieron contra PostgreSQL 17.11.
5. **§30.4** — todo el diagnóstico del 500 quedó superado por los hechos, y la recomendación de poner `APP_ENV=staging` **no se siguió**: el entorno se llama `production` y se sembró igual, invocando los sembradores sueltos en vez de `DatabaseSeeder`. Resultó mejor solución que la recomendada, porque la guardia de `DatabaseSeeder` siguió protegiendo justo lo que tenía que proteger.

### 31.4 Deuda NUEVA que abrió este trabajo

Cuatro cosas. Ninguna rompe la suite; las cuatro son decisiones.

- ⚠️ **No hay marca de procedencia en el esquema, y ahora importa de verdad.** Ninguna columna distingue una fila sembrada de una que escribió la oficina, y los ocho sembradores van por `updateOrCreate` sobre clave natural: **el día que Natalia corrija el texto de un beneficio desde el panel y alguien resiembre, se lo pisa sin avisar.** «Luego se pueden quitar» es cierto mecánicamente —beneficios, aliados e iniciativas no los referencia nada y el panel tiene borrado— pero el coste real no es borrarlos: es que dentro de dos semanas nadie va a poder decir cuáles puso el sembrador. `SemillaInstitucionalTest` es la única defensa posible: impedir que entre inventado.
- **Salento y Filandia perdieron su guía.** Tenían contenido inventado y ahora no tienen nada. Es mejor, pero es una regresión visible: un empresario de Salento que entrara antes veía algo. **La guía cubre 1 municipio de 12.**
- **`GeneradorPdf` es código muerto.** No se borró porque retirar una clase de soporte es decisión aparte, y el día que lleguen los formatos oficiales puede que estorbe o puede que sirva de molde.
- **No hay traducciones de validación.** `locale` y `fallback_locale` están en `es` y **no existe la carpeta `lang/`**; el framework solo trae `en`. Cualquier regla sin mensaje propio se imprime como `validation.min.string`. **Medido: los siete formularios públicos están a salvo**, porque sus `messages()` cubren `required` y `max` con claves generales — pero es una mina, y ya explotó una vez en `asobares:crear-usuario`.

### 31.5 La lección de método, que esta vez es nueva

**El sitio abierto en el navegador dice cosas que la suite no ve.**

Las 970 pruebas estaban en verde mientras la portada, en producción, decía tres cosas falsas:

- «**0 establecimientos afiliados en el Quindío**», en la píldora que va encima del lema. No es un caso raro: es el estado inicial del sitio, porque el directorio nace vacío a propósito.
- «con checklist, **costos y los formatos oficiales listos para descargar**», en la portada y en la entradilla de la guía. Ninguna de las dos cosas existe ya.
- «**Sin costo directo**» en los ocho trámites. Eso *afirma* que el trámite es gratis; `costo_aproximado` en `null` significa lo contrario —que nadie lo ha averiguado—. A quien está haciendo cuentas para abrir un bar eso no se le dice.

Ninguna la encontró una prueba. Las tres se vieron mirando la página **con los datos reales puestos**, que es una condición que en local no se da nunca porque en local el directorio está lleno de demostración. Las tres guardias que se escribieron después comprueban **correspondencia**, no vocabulario: si el texto promete costos, algún trámite tiene que tenerlos. El día que el gremio cargue las tarifas, la promesa vuelve a ser cierta y la prueba sigue verde sin tocarla.

**Y dos defectos propios que solo aparecieron en producción**, los dos en el comando recién escrito. Corrió en Cloud y devolvió exactamente `La contraseña` y exit 1. Parecía una clave débil; llegaba vacía:

1. «La contraseña» iba de **tercer** argumento de `validator()`, que es `$messages`, no de cuarto, que es `$attributes`. Con eso *cualquier* incumplimiento se imprimía igual. Un comando que falla sin decir por qué es peor que uno que no existe.
2. **`isInteractive()` miente en el ejecutor remoto de Laravel Cloud**: devuelve `true` sin que haya terminal, así que la pregunta salía vacía y el comando la arrastraba hasta la validación en vez de parar con la explicación que ya tenía escrita.

Y un tercero, del framework: **`User` declara `#[Fillable(['name','email','password','asociado_id'])]`**, así que `email_verified_at` dentro de un `updateOrCreate` se descarta **en silencio**. Lo atrapó una aserción de la prueba, no la lectura.

Mutaciones de esta sesión: **siete deliberadas, siete rojas**. Las cuatro guardias de la semilla se comprobaron contra la versión anterior real de los sembradores —el defecto de verdad, no uno simulado— y las tres del comando y la portada contra su propia inversión. **Siguen siendo doce los falsos verdes del proyecto**; esta sesión no añadió ninguno.

### 31.6 Dos trampas de Laravel Cloud, para no volver a pagarlas

- **Una variable de entorno recién creada NO llega al proceso hasta el siguiente despliegue.** Medido: tras crearla, `printenv ASOBARES_CLAVE_INICIAL | wc -c` devolvió **0**. El orden es crear → desplegar → ejecutar.
- **`command:run` no tiene terminal**, aunque `isInteractive()` diga que sí. Cualquier comando pensado para correr ahí tiene que leer sus entradas de variables de entorno y **fallar con un mensaje que nombre la variable**.

Y una de operación: `environment:variables --action=set --key=… --value=…` cambia **una sola** variable pese a que la ayuda diga «replace all». No hay acción de borrado: para retirar una se deja en blanco y se borra después desde la consola.

### 31.7 Estado medido del árbol (1 de septiembre de 2026)

`main` en **`493790d`**, **270 confirmaciones**, árbol limpio. **78 archivos de prueba**, 39 migraciones, 19 recursos de Filament, 6 páginas, 21 modelos, **21 sembradores**, **5 comandos de Artisan**, 66 vistas Blade (30 públicas), 86 rutas GET.

**Suite medida hoy: 970 casos, 959 pasan, 11 omitidas, 0 fallos, 3.563 aserciones.** Pint limpio. La duración no se cita, por el §28.4.

**Producción:** `https://asobares-production-0jhdcz.laravel.cloud` responde **200**. PostgreSQL 17.11. 23 aliados, 8 requisitos, 100 ajustes, 8 municipios, 6 categorías, 5 beneficios, 5 iniciativas, 3 roles, 80 permisos, 3 usuarios. **Cero** asociados, PQR, transacciones, noticias, eventos, vacantes y artistas — y eso es lo correcto, no una tarea pendiente.

### 31.8 Lo siguiente, en el orden en que conviene

1. **Reclamar el bloque D**, que es el frente principal y no se destraba solo: las 7 URL de trámite, el texto de «Quiénes somos», los logos institucionales, las autorizaciones de imagen y la decisión del tema. **Cinco de los catorce señalamientos dependen solo de esto.**
2. **SMTP.** Sigue siendo el único bloqueo técnico con consecuencia visible: sin él no se demuestra el segundo factor ni el acuse al postulante.
3. **Decidir la indexación** antes de que el buscador decida por el gremio. Hoy el sitio invita a indexar con un sitemap de 14 URL.
4. **El Acta 04.** Sin firma, cuatro peticiones del cliente siguen sin decidir, y llegar al 22 de septiembre con eso abierto es el modo de fallo que el §27.8 describe.
5. **El bucket con la política del §8.3**, que arrastra además el disco público del §30.3.

## 32. LA DIVISIÓN DEL PROMPT MAESTRO (1 sep 2026)

**El prompt maestro dejó de ser un archivo de 1.800 líneas y 32 secciones para ser un punto de entrada de unas cien líneas que remite a tres archivos.** Sesión de Cowork con el dueño, sobre `main` en `ed9bec2`, en la rama `division-prompt-maestro`, cuatro commits en el orden acordado: primero la bitácora, luego el encargo, luego el estado y por último el prompt maestro reescrito, para que nada se borrara antes de existir en su sitio nuevo.

### 32.1 Por qué

Una lectura completa del documento el mismo 1 de septiembre dejó tres conclusiones. La primera: mezclaba cuatro cosas que envejecen a ritmos distintos —reglas y protocolo, estado semanal, referencia del producto e historia—, y el que cambia cada semana obligaba a tachar y anotar «superado» sobre los otros tres; de ahí las contradicciones que la auditoría del §30.5 tuvo que corregir (106 hallazgos, 30 graves) y las que quedaban (el §0.3 daba R-14 por cerrado mientras el §29.7 pedía comprobar la facturación; la cabecera v13 y el §27.3 no nombraban el mismo trío). La segunda: el §0.7 pedía leer ocho tramos —unas 770 líneas— antes de la primera línea de código, y el propio documento registraba dos veces (18 y 30 de agosto) otra sesión trabajando en el mismo directorio sin haber leído el final. La tercera: el encargo (§1–§14) decía estar «corregido con lo aprendido» y no lo estaba en cosas concretas: el §4 seguía prescribiendo Unbounded y Hanken Grotesk cuando el sitio usa Poppins por el manual de marca; el §5 no tenía las columnas de aliados, proveedores y requisitos añadidas después; el §8 nombraba `BoldGateway` y `FakeGateway` cuando las clases son `PasarelaBold` y `PasarelaSimulada`; el §10 pedía sembrar justo lo que el §31 acababa de retirar de producción.

El diseño completo, con el mapa sección por sección y las cuatro decisiones del dueño (ubicación en `material/`, el estado del repositorio manda en lo técnico y el del Project de Cowork queda para lo académico, las trampas técnicas al encargo por área, ejecución desde Cowork en rama propia), está en `claude/diseno-division-prompt-maestro.md` del Project.

### 32.2 Qué se hizo, con su confirmación

| Commit | Qué |
|---|---|
| `972e510` | `material/bitacora.md`: cabecera v2–v16 (líneas 1–59), §0 (60–166) y §15–§31 (411–1800) **copiados sin cambiar una línea** y verificados con `diff` bloque a bloque; índice cronológico de las notas de versión; regla de solo anexar |
| `2a9c864` | `material/encargo.md` (375 líneas): §1–§14 puestos al día. Los tramos vigentes (tabla del modelo, panel, sitio, reglas de pagos y seguridad, RNF, guion de la bolsa, §14, §26.4, §27.8, §27.5) **se trasladaron por número de línea** con sustituciones que fallan si el patrón no aparece exactamente una vez; lo demás se redactó de nuevo |
| `ffd0946` | `material/estado.md` (163 líneas): exigencias, inventario por cinco frentes, **registro único de 21 decisiones pendientes** (D-01 a D-21) que antes vivían repartidas entre la ERS, el plan del material, el acta 3 y el §31.8, deuda diferida, cifras con su comando, lo siguiente |
| (este) | Prompt maestro reescrito a ~90 líneas sin cifras ni pendientes; bloque del proyecto de `CLAUDE.md` y `AGENTS.md` sin estado (antes decía «el trabajo vivo es el §27 y el §28», ya viejo); puntero en `docs/ingenieria/README.md`; esta entrada |

### 32.3 Lo que se midió y lo que no

Medido sobre `main` en `ed9bec2`: 271 confirmaciones (267 de Sua, 4 de Ingrid, la última del 25 de agosto), 39 migraciones, 21 modelos, 21 sembradores, 78 archivos de prueba, 66 vistas Blade, 19 recursos, 6 páginas, 20 policies, 5 comandos, 16 enums. El sitio publicado respondía 200 y su portada tenía 6 `<img>` y 0 `<video>`, con las secciones de destacados y eventos sin pintar por falta de datos. `robots.txt` con `Allow: /` y sitemap de 14 URL.

**La suite no se ejecutó** en esta sesión: la máquina enlazada a Cowork no expone PHP. El estado cita los 970 casos del §31.7 diciendo que no son una re-medición. Por la misma razón **no se escribió la prueba de guardia propuesta** (que el commit del encabezado del estado exista y que el prompt maestro no lleve cifras de la suite): una prueba que no se vio en rojo no cuenta (§24.6). Queda anotada en el estado como deuda.

### 32.4 Lo que este trabajo cambia para la siguiente sesión

La cadena de entrada es `CLAUDE.md` → prompt maestro → `estado.md` → las secciones de `encargo.md` que toquen. Una sesión lee unas 270 líneas para arrancar en vez de 770, y no tiene que cotejar cifras repetidas. El coste está en la disciplina: si una sesión cierra sin reescribir `estado.md`, el estado se convierte en lo que hoy era el §0. La lista de cierre (§5 del prompt maestro) tiene por eso dos pasos nuevos, y el protocolo de apertura obliga a comparar el commit del encabezado con `HEAD`.

Lo que **no** cambia: los cinco señalamientos vivos del gremio siguen dependiendo de insumos y decisiones humanas, el SMTP sigue sin contratar y la semana es del documento de práctica. La división es infraestructura para las tres semanas que quedan y, sobre todo, para quien reciba el proyecto después del 22 de septiembre.

### 32.5 Trampas de esta sesión

- **Git desde la máquina enlazada a Cowork no puede borrar sus propios archivos temporales** (`Operation not permitted` sobre `.git/HEAD.lock`, `.git/objects/maintenance.lock` y los `tmp_obj_*` de cada commit). Un `HEAD.lock` huérfano bloquea el siguiente commit. Solución que funcionó: renombrarlos a `.git/huerfanos-cowork-2026-09-01/` después de cada commit; el dueño borra esa carpeta y los cuatro `index.lock.huerfano*` anteriores a mano.
- **El sistema de archivos montado es lento para `grep -r`**: un recorrido sobre `docs/` con PDF e imágenes no termina en dos minutos. Acotar a carpetas concretas o a `*.md`.
- **El repositorio no tiene identidad de git en la máquina enlazada**: los commits se hicieron con `-c user.name -c user.email` del dueño, con la coautoría de la sesión en el trailer.

## 33. LA GUARDIA DEL ESTADO, LA FUSIÓN Y TRES DECISIONES RESPONDIDAS (1 sep 2026)

**La prueba que el §32 dejó propuesta existe, se vio en rojo antes que en verde, y la rama `division-prompt-maestro` quedó fusionada en `main`.** Segunda sesión del día, esta vez local, en la máquina de Sua (PHP 8.5.9: aquí sí corre la suite), sobre `division-prompt-maestro` en `1d32b60`. De paso entraron tres decisiones del gremio que Sua trajo a la sesión, y una explicación pedida del correo saliente que cambia el plan del SMTP.

### 33.1 Qué se hizo, con su confirmación

| Commit | Qué |
|---|---|
| `54ddcbb` | `tests/Feature/GuardiaDelEstadoTest.php`, tres pruebas. (1) El encabezado de `estado.md` cita un commit que existe: lee la fila `main` de la tabla de medición y lo comprueba con `git cat-file -t` contra el repositorio, con `GIT_OPTIONAL_LOCKS=0`; sin git o sin repositorio se omite con motivo en vez de dar un falso verde. (2) El prompt maestro no lleva cifras que caducan: un número seguido de casos, aserciones, pruebas, migraciones, modelos, sembradores, vistas, recursos, policies, comandos, enums, rutas, confirmaciones o commits. (3) El prompt maestro no lleva fechas fuera de su §6, que es su registro de cambios |
| (este) | `estado.md` reescrito; `encargo.md` §4, §9 y §13 con las decisiones del 1 sep; `docs/ingenieria/README.md:69`, que seguía llamando al prompt maestro «historial de decisiones y estado (§15 a §23)», apunta a la bitácora y al estado; esta entrada. Después del commit, `main` avanza con `--ff-only` hasta aquí y la rama se borra |

**Los tres rojos, vistos.** Con `abc1234` en la fila `main` del estado: «El encabezado de estado.md cita `abc1234` y ese commit no existe en este repositorio: la foto no es de nadie». Con «La suite tiene 970 casos» pegado en el §1 del prompt maestro: «lleva cifras que caducan («970 casos»)». Con «el 22 de septiembre» en el mismo §1: «lleva fechas fuera de su registro de cambios («22 de septiembre»)». Restaurados los dos archivos con `git checkout --`, las tres verdes: 3 pruebas, 5 aserciones. Pint limpio.

### 33.2 Lo que se midió

Sobre `54ddcbb`: 276 confirmaciones (272 de Sua, 4 de Ingrid), 39 migraciones, 21 modelos, 21 sembradores, **79 archivos de prueba**, 66 vistas Blade, 19 recursos, 6 páginas, 20 policies, 5 comandos, 16 enums, **86 rutas GET propias (96 contando las de vendor)**: el 86 del §31.7 era sin vendor, y por eso no contradice el 96 del primer conteo de hoy. **Suite completa, `php artisan test --compact` sobre `54ddcbb` con las ediciones de documentación sin confirmar: 973 casos · 962 pasan · 11 omitidas · 0 fallos · 3.568 aserciones** (tres casos más que el §31.7: los de la guardia). Producción: `HTTP 200` a una petición `HEAD`; el contenido no se releyó en esta sesión.

### 33.3 Lo que entró y salió del estado

- **D-02 respondida** (sale): el sitio arranca en el tema del dispositivo, `system`, como estaba. OBS3-03 cerrado sin tocar código ni `localStorage.theme`. Anotado en `encargo.md` §4 y §13.
- **D-03 respondida en su mitad grande** (se queda en el estado solo por los pies de foto y por bajar el video): el gremio autorizó el uso de las 19 fotografías de `nuevomaterial/Apoyos visuales/` y del video nuevo que subió al Drive del gremio. El video no está en local ni en el Drive enlazado a esta sesión (se buscó: ningún archivo de video posterior al 15 ago); hay que bajarlo a `nuevomaterial/`, que no se versiona. OBS3-07 pasa de «bloqueado» a «insumo en mano, trabajo de la franja visual». Anotado en `encargo.md` §4, §9 y §13.
- **Formatos oficiales habilitados** (entra en §2.2 del estado y en el §13 del encargo): ya es prudente subir los PDF oficiales de cada entidad, Bomberos primero. El código está entero desde agosto —`SubidaSegura::documentoPdf()` al disco privado, carpeta `formatos/`, y `GuiaController::descargarFormato`, que comprueba publicación y vigencia y registra la consulta—, así que es trabajo de contenido desde el panel. **Con una condición**: en Cloud el disco privado es el efímero, y lo que se suba desde el panel se pierde en el siguiente despliegue (runbook §8.4: «nadie sube archivos definitivos desde el panel hasta que exista el bucket»). El bucket (D-13) pasa a condicionar también esto. Los PDF no están en local ni en el Drive enlazado: pedirlos.
- **D-07 replanteada** (se queda, con otro texto): ver 33.4.
- **Sale la deuda** «prueba de guardia del propio estado: propuesta, no escrita».
- **Entra D-22**: cómo se sirve el video del hero (peso, formato, bucket o embebido, silencio y `prefers-reduced-motion`).
- **Entra D-23**: qué ve el ciudadano cuando el acuse no sale (33.4).
- **Sale del «lo siguiente»** la fusión de la rama; **entra** subir `main` a `origin`, que esta sesión no hizo.

### 33.4 Lo que se aprendió del correo saliente

Se pidió explicar el SMTP, y la explicación destapó dos cosas que el expediente no tenía.

**Primera: el DNS de `asobares.org` decide qué proveedor sirve, y el que prescribe `.env.staging.example` no sirve sin la Nacional.** Medido hoy con `Resolve-DnsName`: MX en Google (`aspmx.l.google.com` y sus alternos: el buzón `asobaresquindio@asobares.org` es de Google Workspace), SPF `v=spf1 include:_spf.google.com include:mailgun.org ~all`, un `brevo-code` de verificación de dominio sin su `include` correspondiente, y **DMARC `p=reject; adkim=r; aspf=r`**, y dos DKIM publicados: `google._domainkey` (Workspace firma alineado) y `mail._domainkey` con la clave compartida de Brevo, es decir, alguien —presumiblemente la Nacional— tiene `asobares.org` autenticado en una cuenta de Brevo. Eso abre una vía más: las credenciales SMTP de **esa** cuenta firmarían alineado y pasarían DMARC por DKIM aunque el SPF no incluya a Brevo; una cuenta de Brevo nueva del capítulo no, porque su `brevo-code` sería otro y volvería a exigir DNS. Con `p=reject`, un correo con remitente `@asobares.org` que no venga autorizado por el SPF del dominio ni firmado con un DKIM alineado **lo rechazan los receptores que aplican DMARC, Gmail entre ellos**; no va a spam, no llega. Resend (`smtp.resend.com`, lo que trae el ejemplo), Postmark y Brevo necesitan que quien administre el DNS de `asobares.org` —la Nacional, no el capítulo— añada sus registros. La vía que no toca DNS es la de Google mismo: SMTP de Workspace (`smtp.gmail.com`, puerto 587) con una **contraseña de aplicación** del buzón del gremio, que exige verificación en dos pasos en esa cuenta y que el administrador del Workspace no tenga bloqueadas las contraseñas de aplicación. Google ya está en el SPF, así que DMARC pasa por SPF aunque el DKIM de Workspace no estuviera configurado. La alternativa es pedirle a la Nacional una credencial SMTP de Mailgun, que ya está en su SPF. Todo esto queda en D-07.

**Segunda: con el transporte muerto, dos formularios públicos rompen después de guardar.** `ContactoController::store` (línea 39) y `EmpleoController` (líneas 148 y 158) llaman a `Mail::to()->send()` sin `try/catch`, y `bootstrap/app.php` no captura `TransportException`. Symfony Mailer lanza cuando no puede abrir el socket y cuando el servidor rechaza la autenticación. Así que hoy, en producción, una PQR o una postulación **quedan guardadas y el ciudadano ve la página de error**, sin su número de radicado ni su acuse. La suite no lo ve porque corre con `MAIL_MAILER=array`. No se corrigió en esta sesión: la corrección merece su prueba en rojo y una decisión sobre qué se le dice al ciudadano cuando el acuse no sale (D-23). El SMTP de esta semana la vuelve menos urgente, no innecesaria: el proveedor también se cae.

**Y el matiz del segundo factor.** El panel ofrece dos factores, la app TOTP y el código por correo (`AdminPanelProvider`), y exige uno de los dos. Sin SMTP el código por correo no sale, pero quien tenga la app configurada entra; lo que nadie puede es dar de alta el factor por correo. El estado decía «el panel no se puede demostrar»: es más preciso decir que no se puede demostrar el factor por correo.

### 33.5 Trampas de esta sesión

- **La cuenta de rutas depende de `--except-vendor`**: 96 GET con las de vendor, 86 propias. El §31.7 citaba 86 sin decir cuál; el estado lo dice ahora.
- **El Bash de Claude Code en esta máquina no tiene coreutils** (`cat`, `ls`: «command not found»). Se trabajó con PowerShell y con las herramientas nativas; PowerShell sí tiene `php`, `git` y `Resolve-DnsName`.
- **Los mensajes de commit largos van por archivo (`git commit -F`)**, escrito con la herramienta de edición y no con `Out-File`, para que no entre un BOM ni se pierdan los acentos por el código de página de la consola. Los asuntos siguen sin tildes, como todos los anteriores.
- **`storage/media-library/` aparece suelto en `git status` mientras corre la suite** y queda vacío al terminar (0 archivos; git no muestra carpetas vacías). No es basura que limpiar ni algo que ignorar: son los temporales de medialibrary.
