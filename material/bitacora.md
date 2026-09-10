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

## 34. D-23 CERRADA: EL CORREO CAÍDO YA NO TUMBA LA PQR NI LA POSTULACIÓN (1 sep 2026)

**Tercera sesión del día, sobre `main` en `a475211`, en la rama `arregla-d23`.** Sua pidió tres cosas: arreglar D-23 ahora, subir `main`, y dejar anotado «bien grande» el arreglo del correo saliente para cuando se tenga delante la cuenta de Google del gremio. Lo primero está en `707e21e`; lo segundo cierra esta entrada; lo tercero es el bloque que ahora encabeza el estado.

### 34.1 Qué se hizo, con su confirmación

| Commit | Qué |
|---|---|
| `707e21e` | `ContactoController::store` y `EmpleoController` (`avisarAlEstablecimiento`, `confirmarAlPostulante`): los tres envíos van en `rescue()`. La PQR queda radicada y su aviso dice «No pudimos enviarte el acuse por correo: guarda este número para hacer seguimiento» cuando el acuse no salió; la postulación queda guardada con su aviso de siempre, que nunca prometió el acuse. El fallo se reporta al registro. `tests/Feature/CorreoSalienteCaidoTest.php`, tres pruebas |
| (este) | `estado.md` reescrito con el bloque grande del correo saliente arriba; `encargo.md` §9 y §13 con la regla «ningún correo tumba la petición»; runbook §6.3 con la advertencia del DNS; esta entrada. Luego `main` avanza con `--ff-only`, la rama se borra y **`main` se sube a `origin`** |

**Cómo se probó sin simular nada.** La suite corre con `MAIL_MAILER=array`, que nunca falla. La prueba apunta el transporte SMTP de verdad a `127.0.0.1:1` —puerto reservado en el que nadie escucha— y Symfony Mailer lanza `TransportException` igual que en Cloud sin proveedor. **Rojo primero**: sin el arreglo, las dos peticiones devolvieron 500. **Y el reporte se vio rojo aparte**: con `report: false` puesto a propósito en los dos controladores, la PQR falló con «TransportException no reportada» y la postulación con «1 en vez de 2». Restaurado, verde: 3 pruebas, 16 aserciones; las vecinas (`FormulariosPublicos`, `BolsaDeEmpleo`, `CorreosDeBolsa`) sin regresión, 59 pruebas y 157 aserciones en total.

### 34.2 Lo que se midió

Sobre `707e21e`: 278 confirmaciones (274 de Sua, 4 de Ingrid), **80 archivos de prueba**. Lo demás del árbol igual que en el §33.2. **Suite completa sobre `707e21e`: 976 casos · 964 pasan · 11 omitidas · 1 fallo · 3.584 aserciones.** El fallo es ajeno al cambio: `DatosInternosDelAsociadoTest::test_la_base_de_datos_del_gremio_no_vive_en_el_repositorio` encontró `Asobares Quindio - Base de datos.xlsx` y `Base de datos Cap. Quindio.xlsx` en `material/nuevomaterial/`, copiados a las 11:00 p. m. mientras corría la sesión: no estaban en el listado de la primera sesión del día, y la suite de la segunda pasó entera. Están ignorados por git y ningún `.xlsx` está rastreado, así que el `push` no los lleva; pero la guardia mira el disco a propósito y pide sacarlos del árbol. Las otras 975 pruebas, incluidas las tres nuevas, pasaron.

### 34.3 Lo que entró y salió del estado

- **D-23 respondida y cerrada** (sale): el correo caído no tumba la petición; la regla queda en `encargo.md` §9 y §13 para todo envío nuevo desde una petición de usuario.
- **Entra D-24**: la misma red para las tres acciones del panel que envían correo tras aprobar o devolver (`AccionesDeAprobacion`, líneas 134, 169 y 221). Hoy, con el transporte caído, el administrador ve el error de Livewire con el estado ya cambiado: no se pierde nada, pero desconcierta y el correo no sale. Se hace con `rescue()` y con su prueba en rojo contra el puerto cerrado, como aquí. No entró en esta ronda para no ampliar en silencio lo que se pidió.
- **Entra el bloque «Arreglo pendiente: correo saliente»** arriba del estado, con los pasos exactos para el día que se tenga la cuenta de Google del gremio, y la advertencia equivalente en el runbook §6.3, que hasta hoy prescribía Resend sin más.
- **Sale del «lo siguiente»** subir `main` a `origin`: se hace al cerrar esta entrada.

### 34.4 Trampas de esta sesión

- **`Exceptions::assertReported()` compara la clase exacta, en las dos formas.** Con una cadena hace `assertContains` sobre `get_class` de lo reportado; con un cierre exige además que el tipo del primer parámetro sea `===` a `get_class` **antes** de llamar al cierre. Ni una interfaz (`TransportExceptionInterface`) ni `Throwable` sirven jamás, y un `dump` dentro del cierre no se ejecuta, porque el `&&` corta antes. Para saber qué clase se reportó, `assertNothingReported()` falla listándolas: `Symfony\Component\Mailer\Exception\TransportException`.
- **`rescue()` es la red del framework para esto**: reporta con `report()` y devuelve el valor de rescate. No hacía falta un ayudante propio; el proyecto no lo usaba en ninguna parte y ahora lo usa en tres.
- **La salida de la suite en modo agente se traga `STDERR`**: una sonda con `fwrite(STDERR, …)` no aparece. Escribir a un archivo del scratchpad, o usar el mensaje de una aserción, como arriba.
- **La base del gremio dentro del árbol pone la suite en rojo aunque esté ignorada.** `DatosInternosDelAsociadoTest` busca por nombre (`/base.?de.?datos.*\.xls[xm]?$/i`) en todo el árbol menos `vendor`, `node_modules`, `.git` y `storage`, sin mirar el `.gitignore`. `material/nuevomaterial/` sirve para las fotos y los documentos del gremio, no para esa base: esa se guarda fuera del repositorio y se carga con `asociados:importar`.

## 35. D-24 CERRADA, Y LO QUE LLEGÓ DEL DRIVE (1 sep 2026)

**Cuarta sesión del día, sobre `main` en `0b5c2c2`, en la rama `arregla-d24`.** Sua confirmó que lo que apareció en `material/nuevomaterial/` a las 11:00 p. m. es el contenido del Drive del gremio, pidió revisarlo para ver qué se integra, planteó que cifras como las de la franja de la portada dependan del Excel de la contadora con actualización quincenal, y pidió arreglar D-24.

### 35.1 Qué se hizo, con su confirmación

| Commit | Qué |
|---|---|
| `07a3033` | `AccionesDeAprobacion`: dos ayudantes, `enviar()` —el envío en `rescue()`, devuelve si salió, reporta el fallo— y `avisarResultado()` —el aviso del panel en amarillo y persistente, «…, pero el correo no salió», en vez de fingir el éxito—. `publicarVacante`, `devolverConMotivo` y `publicarFicha` devuelven si el correo salió; `enLote` mapea los efectos y cuenta los `false`: «2 registros publicados, pero 2 correos no salieron». `CorreoSalienteCaidoTest` recibe la sección del panel: aprobar vacante, devolver con motivo, aprobar ficha de artista y el lote de vacantes |
| (este) | `estado.md` reescrito; `encargo.md` §9 y §13 sin la excepción del panel; runbook §6.3; esta entrada. Luego `main` avanza con `--ff-only` y la rama se borra |

**Rojos vistos.** Sin el arreglo, las cuatro acciones escapaban con la `TransportException` del puerto cerrado, el mismo error que vería la secretaría con la vacante ya publicada por debajo. Con el reporte apagado en `enviar()` y `$correoSalio` forzado a `true` en `avisarResultado()`: tres «A notification was not sent» y «0 instead of 2» en el lote. Restaurado, verde: 7 pruebas y 51 aserciones en el archivo; 28 y 145 junto con `ModeracionDeBolsasTest`.

### 35.2 Lo que se midió

Sobre `07a3033`: 280 confirmaciones, 80 archivos de prueba (la sección del panel vive en el archivo de D-23). **Suite completa sobre `07a3033`: 980 casos · 968 pasan · 11 omitidas · 1 fallo · 3.619 aserciones.** El único fallo sigue siendo la guardia de `DatosInternosDelAsociadoTest` por los dos `.xlsx` de la base del gremio dentro del árbol (§34.4): ajeno al cambio, y las 979 restantes —las cuatro nuevas incluidas— en verde.

### 35.3 Lo que llegó del Drive, mirado pieza por pieza

Todo con fecha 1 sep, 11:00 p. m., en `material/nuevomaterial/` (ignorado por git). Se miraron estructuras, no datos: de los Excel solo hojas, encabezados y conteos.

- **Video**: `Asobares Quidío VIDEO ANATO .mp4`, 48 s, 55,7 MB. Sirve para el hero, pero no así: hay que recortarlo a un bucle de 10–15 s sin audio, recomprimirlo a 2–5 MB en 720p o 1080p y sacarle un póster, y servirlo desde el bucket (D-22). No hay `ffmpeg` en esta máquina. Uso autorizado desde D-03; ya está en local.
- **Fotos**: las mismas 19 de `Apoyos visuales/`, autorizadas. Sin pies de foto (D-03).
- **`Asobares Quindio - Base de datos.xlsx`** (hoja «Base de Datos 2025», 48 filas) y **`Base de datos Cap. Quindio.xlsx`** (41 filas): la **base de afiliados** —establecimiento, dueño, descripción, NIT, dirección, municipio, teléfono, correo, horario, género musical, servicios, Instagram, menciones—, es decir, las versiones del 26 y del 23 de agosto que el estado ya conocía. Las lee `asociados:importar`. **No son el Excel de la contadora.** Deben salir del árbol (§34.4): la suite sigue en rojo por ellas.
- **`Registro Establecimiento.xlsx`**: el **formulario oficial de registro** maquetado en Excel (83 filas, 26 columnas; casillas «Formulario N°», «Afiliación / Actualización», «Fecha»), no una tabla. Es el insumo de la opción A de D-18: formulario descargable en `/afiliate`.
- **PDF**: `BENEFICIOS AFILIADOS PDF.pdf` (fuente de los cinco beneficios ya sembrados), `Ley laboral.pdf` (fuente del boletín laboral pendiente) y el manual de marca. **No hay ningún formato oficial de trámite**, ni el de Bomberos: los PDF que se habilitaron el 1 sep siguen sin llegar.
- **DOCX**: `REQUISITOS APERTURA - ARMENIA.docx` (ya sembrado), `REQUERIMIENTOS BASICOS GENERALES - ESTABLECIMIENTO NOCTURNO.docx` y `Certificado de afiliacion SALSABOR.docx` (el molde del certificado; D-18 lo tiene como ampliación).
- **Lo que no llegó**: el Excel de cartera de la contadora (D-11) y los formatos oficiales por entidad.

### 35.4 Las cifras quincenales son una decisión de producto, no un arreglo

Sua planteó que cifras como las de la franja «La noche en cifras» cambien según el Excel de la contadora, actualizado cada 15 días. Hoy esa franja son cuatro ajustes editables desde el panel —`cifra_empleo`, `cifra_ingreso`, `cifra_informalidad`, `cifra_jovenes`, cada uno con su detalle— sembrados con el estudio del Observatorio Económico de la Nacional (marzo 2026): son cifras del sector en Armenia, no del gremio, y no salen de ningún archivo. Lo que sí sale de un archivo de la contadora es la cartera (`ImportadorDeCartera`: CSV con `establecimiento`, `saldo_pendiente`, `meses_mora` y `ultimo_pago` opcional), que alimenta `/mi-cuenta` y las gráficas del Observatorio interno del panel. Que la portada muestre cifras del gremio derivadas de un archivo quincenal es funcionalidad nueva —qué cifras, de qué archivo y con qué formato, quién lo sube, cada cuánto, y si sustituye o acompaña a la franja del Observatorio— y el alcance está congelado: primero se escribe (constancia), se decide, y después se codifica. Queda como D-25 en el estado, con las preguntas abiertas.

## 36. LOS «FORMATOS» QUE NO ERAN, Y EL CÓMO DE D-25 (1 sep 2026)

**Quinta sesión del día, sobre `main` en `18bdeea`.** Sua eligió la opción a de D-25 (franja nueva del gremio, la del Observatorio se queda), pidió subir `main` al terminar, y señaló que «los formatos de Bomberos y Policía están en `storage/app/private/formatos/`». No se escribió código: el cómo de D-25 sigue sin decidir y el alcance está congelado.

### 36.1 Los formatos que no eran

`storage/app/private/formatos/` tiene `formato-solicitud-visita-bomberos.pdf` (2,6 KB) y `formato-registro-policia.pdf` (2,2 KB), fechados el 3 de agosto. Leídos: «Formato de ejemplo · ASOBARES Capítulo Quindío … Documento de ejemplo generado para el prototipo. Verifique siempre con la entidad competente». Son los PDF que `GeneradorPdf` fabricaba para la demostración y que `RequisitoAperturaSeeder` dejó de enlazar («Sin `adjunto`. Los PDF que se generaban decían “Formato de ejemplo”»); quedaron huérfanos en el disco privado. **No son los formatos oficiales** y no entran en producción (regla 5). Los reales siguen sin llegar: ni en el Drive (§35.3) ni aquí. D-15 (`GeneradorPdf`: borrar o conservar) sigue abierta; borrar también estos dos archivos huérfanos cuando se decida.

### 36.2 El cómo de D-25: tres vías, y por qué se recomienda la primera

Hoy la franja «La noche en cifras» son ocho ajustes del grupo `cifras` (`cifra_*` y `cifra_*_detalle`, más `cifra_afiliados`) que `AjustesDelSitio` agrupa solo y `inicio.blade.php` pinta. La franja nueva del gremio puede alimentarse de tres maneras:

1. **Ajustes editables desde el panel** (recomendada para llegar al 22 de septiembre). Un grupo nuevo de ajustes —título de la franja y cuatro pares valor/detalle—, una sección más en la portada que no se pinta mientras esté vacía, y «Actualizado el …» sacado del `updated_at` del grupo, sin campo nuevo. El Excel de la contadora es la fuente de la que la oficina copia cuatro números cada quince días; el sistema no lo lee. Coste: una sesión, sin formato que acordar, sin dependencia nueva, cero riesgo de archivo mal formado. Y las cifras cambian sin tocar código, como todo lo demás de la portada.
2. **Importar un archivo pequeño cada quince días**: un CSV de tres columnas (`cifra`, `valor`, `detalle`) subido desde el panel, con historial y fecha. Es lo que Sua imagina («que la página se acople al formato»), pero el formato de la contadora no existe todavía —no llegó ningún Excel de cartera (D-11)— y para cuatro números por quincena la carga cuesta más que teclearlos: un importador, sus errores por fila, su prueba, y otra cosa que enseñar en la capacitación. Tiene sentido si las cifras pasan de cuatro a cuarenta, o si la contadora no va a entrar al panel.
3. **Derivarlas de la cartera** que ya se importa: afiliados al día, en mora, cartera pendiente. No pide archivo nuevo, pero publica la salud financiera del gremio en la portada —una decisión de la dirección, no técnica— y depende de que la cartera se cargue de verdad, cosa que todavía no ha pasado ni una vez en producción.

Con la vía elegida se escribe la constancia (Acta 04 sigue sin firmar: se le añade la fila, o se emite un Acta 05 con `constancias.mjs`) y se fijan las cuatro cifras con Natalia. Mientras tanto la franja no se toca.

### 36.3 Trampa

- **Un PDF de 2 KB con fecha del primer día del proyecto no es un documento oficial.** Antes de dar por recibido un insumo, abrirlo: el pie de página lo dice.

## 37. LA FRANJA «EL GREMIO EN CIFRAS» (1–2 sep 2026)

**Sexta sesión del día 1, cerrada ya el 2 de madrugada, sobre `main` en `f052501`, en la rama `cifras-del-gremio`.** Sua eligió la vía 1 de D-25 —ajustes editables desde el panel— y pidió hacerla; dijo que le gustaría que algún día se actualizara sola desde el Excel de la contadora, y que el 2 de septiembre pedirá los formatos oficiales de Bomberos y Policía y ese Excel.

### 37.1 Primero el escrito: Acta 05

La regla 1 exige registrar la ampliación por escrito antes de codificarla. `5447bbe` emite el **Acta 05 – Ampliación de alcance: cifras del gremio en la portada** con la herramienta del expediente (`constancias.mjs`), como formato para diligenciar y firmar: objeto, regla, la fila D-25 con sus tres casillas, lo que queda fuera —la lectura automática del archivo, Fase II— y un cuadro para que la dirección escriba las cuatro cifras y su fuente. `constancias.mjs` acepta ahora un argumento (`node … constancias.mjs "Acta 05"`) para imprimir un solo PDF sin regenerar los otros cuatro, que cambian de bytes aunque no cambien de contenido.

### 37.2 Qué se hizo, con su confirmación

| Commit | Qué |
|---|---|
| `5447bbe` | Acta 05 y el filtro de `constancias.mjs` |
| `a408afc` | `App\Support\CifrasDelGremio` (cuatro ranuras, `vigentes()`, `actualizadasEl()`); nueve ajustes nuevos en `SettingSeeder` (`portada_gremio_titulo` en `inicio`; ocho `gremio_cifra_N` / `_detalle` en el grupo `gremio`, vacíos); el grupo «El gremio en cifras» en `AjustesDelSitio`; la sección en `inicio.blade.php` tras la del Observatorio, que solo se pinta con cifras; `InicioController` le pasa las cifras y la fecha. `CifrasDelGremioTest`, seis pruebas |
| (este) | `estado.md`, `encargo.md` §7 y §13, esta entrada. Luego `main` avanza con `--ff-only`, la rama se borra y `main` se sube a `origin` |

**Dos arreglos que la franja obligó.** (1) `AjustesDelSitio::guardar()` hacía `update()` masivo de todas las claves en cada guardado, y una actualización masiva sella `updated_at` aunque el valor no cambie: «Actualizado el» habría dicho la fecha del último guardado de cualquier cosa. Ahora escribe solo lo que cambió, por el modelo. (2) `SettingSeeder` hacía `updateOrCreate` de todo: resembrar para añadir un texto devolvía las cifras a vacío y la franja desaparecía sin aviso. El grupo `gremio` se crea si falta y no se vuelve a pisar; D-14 sigue abierta para el resto.

**Rojos vistos.** Primera corrida, sin implementar: «El título de la franja del gremio no está sembrado», el panel no guardaba la clave (`''` en vez de `'4.831'`), la fecha no aparecía, y tres pruebas reventaban antes de su aserción porque las claves no existían. Esas tres se vieron rojas aparte con el código saboteado: `updateOrCreate` en el grupo («resembrar» falló con `''`), sin el filtro de ranuras («sin cifras» y «ranura sin número» fallaron), y con el `update()` masivo de vuelta («la fecha es la de la cifra» falló). Restaurado, verde: 6 pruebas y 31 aserciones; 72 y 206 junto con `PortadaEditable`, `AccionesDelPanel` y `ConfiguracionDeDespliegue`.

**Mirado en el navegador.** `php artisan serve` en 8123 con las nueve claves creadas en la base local sin pisar nada y cuatro cifras de muestra, borradas después: la sección mide 1265×228, cuatro columnas, cifras a 30 px Poppins 700 en el color de acento, título y «Actualizado el 01 de septiembre de 2026», fondo alternado con la franja del Observatorio, sin errores de consola. Medido por JS, porque las capturas del panel del navegador salen negras (no compone fotogramas). `php artisan view:clear && npm run build` corridos; ninguna clase nueva de Tailwind.

### 37.3 Lo que se midió

Sobre `a408afc`: 285 confirmaciones, **81 archivos de prueba**. **Suite completa: 986 casos · 974 pasan · 11 omitidas · 1 fallo · 3.650 aserciones.** El único fallo sigue siendo la guardia de `DatosInternosDelAsociadoTest` por los dos `.xlsx` de la base del gremio dentro del árbol (§34.4); las 985 restantes, con las seis nuevas, en verde.

### 37.4 Lo que entró y salió del estado

- **D-25 cerrada** (sale): la decisión entra en `encargo.md` §13 y el producto en §7. Lo que queda no es código: **firmar el Acta 05**, **fijar las cuatro cifras** con Natalia y **teclearlas** en «Ajustes del sitio → El gremio en cifras». Hasta entonces la franja no existe para quien visita el sitio.
- **Entra la Fase II** en el §13 del encargo: lectura automática del archivo de la contadora cuando exista con formato acordado.
- **D-14 con un matiz**: el grupo `gremio` ya no se sobrescribe al resembrar; el resto sí.
- **Entra en «lo siguiente»**: el 2 de septiembre Sua pide los formatos oficiales de Bomberos y Policía y el Excel de la contadora.

### 37.5 Trampas de esta sesión

- **Un mensaje de commit inline con «a:» dentro bloquea la herramienta de PowerShell** («Remove-Item on system path 'a:' is blocked»): el envoltorio lo lee como una ruta de unidad y no ejecuta nada. Todos los mensajes de commit por archivo, con `-F`.
- **`ajuste()` devuelve `''` para una clave inexistente**, así que `assertDontSee(ajuste('…'))` sobre una clave sin sembrar «falla» por la razón equivocada: la cadena vacía está en todo. Afirmar primero que el ajuste está sembrado y no vacío.
- **Una prueba que revienta antes de su aserción no ha visto roja esa aserción.** Las tres que fallaban en `teclear()` por la clave inexistente se sabotearon aparte, cada una con su rotura, antes de darlas por buenas.
- **El panel del navegador sigue sin componer**: capturas negras en claro y en oscuro. La inspección por JS (`getBoundingClientRect`, `getComputedStyle`) es la prueba visual que sí sirve.

---

## 38. LA CAPA VISUAL ENTRA, LAS BOLSAS SE CIERRAN Y EL VIDEO LLEGA A PRODUCCIÓN (3 sep 2026)

Sesión pedida como análisis del estado y de GitHub; terminó siendo la fusión de todo el trabajo de la Persona 2 y el cierre de OBS3-02.

### 38.1 Lo que el estado no sabía

`origin` tenía **dos ramas nuevas** empujadas esa misma tarde por Ingrid, y el estado decía «Rama de trabajo: ninguna». `p2-redisenio-visual` (la capa visual, 39 archivos) y `p2/acceso-asociados`, que la contiene y además mueve los contactos de proveedores y el banco de talento detrás de la sesión del afiliado. Las dos ya traían `main` fusionado, así que entrar era avance rápido limpio.

También apareció que el repositorio no tiene **ni un solo PR en toda su historia ni ningún workflow de CI**. Nada verifica una rama antes de que entre. La propia nota de Ingrid pedía revisión por PR.

### 38.2 Las dos autorizaciones del dueño

Sua autorizó las dos cosas que la rama traía y que el estado declaraba prohibidas o sin decidir:

1. **El afiliado puede consultar aspirantes** (OBS3-09, la mitad que estaba prohibida hasta releer el §9).
2. **Los contactos de proveedores pasan detrás de la sesión** — que es responder de hecho la DPV-02, abierta desde el 5 de agosto y a nombre de Natalia y el directivo.

Las dos quedan hechas. Lo que **no** quedó hecho es el registro: el alcance está congelado y toda ampliación se escribe **antes** de codificarse, en `constancias/`. Aquí el código se escribió primero y lo que hay es un `.md` en una carpeta nueva, `docs/ingenieria/decisiones/`. De ahí sale **D-26**, y la sesión no emitió el acta por su cuenta: una constancia va con los dos nombres al gremio.

### 38.3 Lo que la rama rompía, y que solo se vio al ejecutarla

La nota de Ingrid lo decía sin rodeos: «el codigo se escribio desde Cowork, que no tiene PHP ni Composer: **nada de esto se ejecuto**». Al fusionar, la suite dio **dos fallos reales**:

- **`hero_frase_corta` y `hero_video_rotulo` estaban sembradas y exigidas por `PortadaEditableTest`, y ninguna vista las pintaba.** La tarjeta del video del hero salía muda: `titulo` y `detalle` se calculaban en el bloque `@php` y no se imprimían en ningún sitio. Se pintan; el rótulo lleva franja de contraste propia (`.video-velo`, 72 % de negro abajo) porque el velo suave de la tarjeta es del 28 % y no sostiene texto blanco sobre los fotogramas claros del bucle.
- **`VerificacionDeProveedoresTest` abría la sesión del afiliado antes de sembrar las fichas.** `FlujoDeAprobacionObserver` degrada a `pendiente_aprobacion` toda alta hecha por quien no puede publicar (RF-37), así que las fichas nacían sin publicar, el directorio salía vacío y la prueba acusaba a la vista de algo que hacía ella misma. El observer estaba bien; la prueba estaba mal montada. Ahora se siembra sin sesión y se abre solo para mirar.

### 38.4 El video: un defecto que solo existía del lado del despliegue

La portada servía el video con `file_exists(public_path('videos/…'))` mientras `.gitignore` traía `/public/videos/`. **En local funcionaba.** Cloud despliega desde git: allí el archivo no viajaba, la condición era falsa siempre y el hero salía mudo **sin error, sin log y sin que ninguna prueba se enterara**.

Se resolvió versionando el medio en vez de esperar al bucket (D-13, que sigue sin crearse):

- Bucle de **10 s** recortado del original de 48,1 s, **sin audio**, 1280×768, **1.550.175 B**, con fundido a negro de 0,5 s en los dos extremos para que el reinicio no se note. El tramo se eligió mirando una hoja de contacto, no a ciegas: los segundos 17 a 27 son barra y coctelería, y el corte evita el plato blanco que hacía fogonazo al reiniciar.
- Póster propio de **27.188 B**, primer fotograma del bucle. Antes el póster salía de las fotos de asociados destacados, que **en producción no existen** porque no hay ninguna ficha publicada.
- El original de 58.451.107 B se queda en `nuevomaterial/`, que no se versiona.

`ffmpeg` **sí estaba instalado**: D-22 afirmaba lo contrario y llevaba desde el 1 de septiembre bloqueando esto por una premisa falsa.

`VideoDelHeroTest` mira el **índice de git**, no el disco: `git ls-files --error-unmatch`. Que el archivo esté en la máquina de quien programa no demuestra nada. Se vio roja con los archivos sin registrar y verde después de `git add`.

### 38.5 Lo que el navegador vio y la suite no

Con la página servida en `localhost:8123`, la inspección por JS dio: `paused: true`, `readyState: 4`, sin la clase `--visible`. **El video se descargaba entero y se quedaba parado detrás del póster.** La causa era propia, no de la rama: el `Alpine.data('videoHero')` recién escrito llamaba a `load()` antes de `play()`, y la carga en curso rechaza esa promesa. Se comprobó llamando a `play()` a mano en la consola de la página: resolvió y el video avanzó. Sin `load()`, funciona; tras el arreglo, `paused: false`, `currentTime` 2,36 s y clase `--visible` puesta.

La misma pasada destapó que `hero_frase_corta` **no está sembrada en la base** —ninguna de las 17 claves nuevas lo está— y el párrafo recién añadido se pintaba vacío, con su margen, encima del titular. No lleva texto de respaldo a propósito: en producción solo entra contenido de documento oficial del gremio. Vacía no se pinta.

Es la regla 7 del prompt maestro cobrándose otra: el sitio abierto en el navegador dice cosas que la suite no ve.

### 38.6 Los `.xlsx` que llevaban dos días poniendo la suite en rojo

Las dos copias de la base del gremio dentro del árbol eran **duplicados byte a byte** (md5 idéntico) de las que ya vivían en `D:/Sua_Files/material-asobares/`. Se borraron sin pérdida. `Registro Establecimiento.xlsx` se queda: es el formulario oficial maquetado, no una base, y la guardia no lo señala. `DatosInternosDelAsociadoTest` vuelve a verde.

### 38.7 Lo que se midió

Sobre `f83c9ea`: **1.010 casos · 999 pasan · 11 omitidas · 0 fallos · 3.800 aserciones**, 435 s. 292 confirmaciones (283 de Sua, 9 de Ingrid), 84 archivos de prueba, 70 vistas Blade, 88 rutas GET propias, 17 controladores públicos, **126 ajustes** en el sembrador contra los 109 que hay en producción. Portada de producción: **200 en 2,46 s** en frío, sirviendo todavía `6f24ff4`.

### 38.8 Lo que entra y sale del estado

- **Salen**: D-22 (el video, resuelto), los `.xlsx` del árbol, OBS3-02 y la mitad de OBS3-07, la mitad prohibida de OBS3-09.
- **Entran**: **D-26** (Acta 06 de la ampliación), **D-27** (política de tratamiento y los 7 perfiles que aceptaron con otra versión), **D-28** (alta de credenciales de afiliados: sin ellas nadie ve lo que se acaba de construir), **D-29** (dónde va la frase corta, y qué se hace con la banda de tres videos que promete piezas inexistentes).
- **Cambia de naturaleza D-17**: la DPV-02 quedó respondida de hecho y hay que ratificarla o revertirla, porque condiciona 8 RF ya codificados y la matriz de trazabilidad del documento que se entrega esta semana.
- **Entra en deuda**: el correo de ficha de bolsa publicada enlaza a `/proveedores`, que ya no nombra al proveedor. Se arregla cuando haya SMTP, que hoy no manda nada.

### 38.9 Trampas de esta sesión

- **No edites archivos mientras corre la suite.** Blade recompila a media carrera y el informe mezcla estados: una corrida entera de cinco minutos tirada y, peor, un fallo que parecía real y era de la edición.
- **Un reemplazo por la primera ocurrencia pega en el comentario, no en el código.** Al romper `preload="none"` a propósito, el reemplazo acertó en la frase del comentario que lo mencionaba y dejó el atributo intacto: la rotura no rompió nada, y de haberlo dado por bueno la prueba habría quedado «verificada» por engaño.
- **Una prueba que afirma sobre el disco no prueba lo que se despliega.** Si el archivo puede estar ignorado, hay que preguntarle a git y no a `file_exists`.
- **`load()` antes de `play()` aborta la reproducción** y deja el elemento con `readyState` 4 y `paused` true: parece que no cargó cuando había cargado entero.
- El panel del navegador **sigue sin componer fotogramas**: la captura salía negra con la página correcta detrás. La inspección por JS es la que vale.

---

## 39. LA OPCIÓN B: LA BARRA DE ESCRITORIO EN TRES ESTADOS, CONSTRUIDA EN UNA RAMA (3–5 sep 2026)

Sesión larga con Sua, en dos noches, cortada una vez por el límite de uso. Entró como «analicemos la barra de Ingrid» y salió como una rama publicada con veinte commits, una spec, un plan y una barra alternativa lista para que la dirección elija. **Nada de esto está en `main`** y este apunte vive, por ahora, solo en la rama `p1-navbar-alternativa`.

### 39.1 De la opinión a la decisión

La barra de la Persona 2 (opción A, en producción desde el 3 sep) se grabó en tres tamaños con Playwright antes de opinar. La grabación del iPad Pro 11 en horizontal es la que vale: a 1024 px o más con dedo **no hay forma de abrir el menú** —la regla que encoge la píldora solo mira el ancho y la que la expande exige ratón; la hamburguesa es `lg:hidden`— y «Afíliate» queda oculto en reposo. Sua decidió no tocar producción y construir su propia barra en una rama para llevar las dos a la reunión.

### 39.2 Diseño primero, con seis lectores y ocho decisiones

Antes de escribir código, seis lectores en paralelo mapearon roles, tema, movimiento, idiomas, pruebas y marca (bitácora de la sesión, no del repositorio). Salió lo que no se sabía: el DOM no se puede duplicar (siete pruebas cuentan controles y `aria-current`), el proyecto no tiene ninguna curva de rebote, la opción «Sistema» la prohibía una prueba a propósito (OBS3-03), y solo existe isotipo rojo. Sua decidió ocho cosas en la sesión (spec §2): idiomas fuera —chip visible con inglés «próximamente», acta aparte—, un toque abre con dedo, móvil intacto, vuelta al inicial al tope, resortes en CSS con `linear()`, isotipo en scroll, 520 ms con token propio, banderas Colombia y Estados Unidos.

La spec (`docs/ingenieria/navbar-tres-estados-diseno.md`) y el plan (`…-plan.md`, doce tareas con la prueba antes y la rotura que la pone roja) se confirmaron antes de la primera línea. Un error mío en la spec se cazó al bajar al plan: **el respaldo de `linear()` no puede ser una segunda declaración** —`var()` es inválido al computar y la propiedad cae a `ease`, no a la anterior—; va por `@supports`.

### 39.3 Doce tareas, doce revisiones, y lo que las revisiones cazaron

Cada tarea la hizo un agente fresco con su brief, y otro la revisó contra la spec. Las revisiones no fueron decorativas:

- **T5:** el sol sin `x-cloak` destellaba en oscuro antes de que arrancara Alpine. El icono pasó a decidirlo CSS por la clase `dark` del `<html>`, que el `<head>` pone antes del primer pintado: sin destello y sin depender de JS.
- **T6:** `assertStringContainsString('disabled', …)` lo satisfacía `aria-disabled`: quitar el atributo real dejaba la prueba verde.
- **T9 (Critical, del plan):** `overflow: hidden` en `.control-plegable` cayó sobre la raíz `position: relative` del grupo «El gremio», bloque contenedor de su panel absoluto: **el desplegable no se veía en ningún estado**. La geometría del plegado se movió al enlace y al botón; la raíz solo se esconde. Verificado por `elementFromPoint`, porque el rectángulo del panel medía lo mismo recortado que sin recortar.
- **T11:** la fila de los popovers solo vigilaba `control-tema`; las filas idénticas de `control-idioma` quedaban sin guardia.
- **T12, pasada de mutaciones:** 12 de 13 roturas rojas. La 13.ª —borrar el método `alternarAtencion`— dejaba la suite verde porque la prueba afirmaba la *llamada*, que sobrevive en `x-on:click`. Se afirman las definiciones.
- **Revisión final (opus, en Chromium real):** lista para la demo; para fusionar, cuatro bloqueos: las guardias probaban que la máquina estaba *construida* pero no *conectada* (borrar `x-on:scroll.window` mataba la barra con 1.026 verdes); elegir tema con teclado tiraba el foco al `<body>`; el `min-height: 44px` que la spec §6.2 pedía nunca se construyó (39,7 px; indicador 32×40); y el chip `ES` fallaba «Label in Name». Más un menor que rompía la decisión de móvil intacto: la cabecera móvil había crecido a 58 px por un borde base. Una ola de arreglos y una re-revisión: los nueve cerrados, cabecera móvil de vuelta a 56.

### 39.4 Lo que se midió

Sobre `84b6798`: **1.028 casos · 1.017 pasan · 11 omitidas · 0 fallos · 3.993 aserciones**, 272 s. En Chromium (playwright-cli, no el panel): módulo principal 44 px en scroll, indicador 44×44, botón de tema 44×44, chip 50×44, filas 45,7, cabecera móvil 56, `.bandeja` 1280 px a 1440 de ancho (`min(1408, 1280)`, no los 1248 que el plan decía), «El gremio» 224×155 y alcanzable en los tres estados, foco de vuelta al disparador tras elegir tema. Seis vídeos en el scratchpad de la sesión (tres de la A, tres de la B). **Sin verificar:** `prefers-reduced-transparency`, que Playwright acepta y no aplica.

### 39.5 Lo que entra y sale del estado

- **Entra:** la rama como opción B (§0, §2.1, §2.3); **D-30** (elegir A o B), **D-31** (transparencia reducida en equipo real), **D-32** (idiomas como subsistema con acta); la deuda de la rama anotada por su revisión final (§4); dos defectos preexistentes en `main` vistos de paso (§4); las cifras de la rama (§5).
- **Cambia:** el documento de práctica pasa a «vencido, sin confirmación de envío»; OBS3-03 con el matiz de «Sistema».
- **No cambia:** `main`, producción, ni las decisiones D-01 a D-29.
- Este estado vive en la rama; el de `main` sigue en `f83c9ea`. La sesión que cierre D-30 lo reescribe donde toque.

### 39.6 Trampas de esta sesión

- **El panel del navegador congela las transiciones**, no solo las capturas: `document.hidden === true` mata `requestAnimationFrame`, y una `width` o un `x-show` se quedan en su valor inicial aunque la regla ya no aplique. Medir valores finales con `*{transition:none!important}` inyectado; el comportamiento, con playwright-cli.
- **Un rectángulo no ve un recorte.** `getBoundingClientRect()` de un panel recortado por `overflow: hidden` mide exactamente lo mismo que sano; `document.elementFromPoint` en el centro de un enlace sí lo ve.
- **Mutar por prueba no basta.** La pasada «una rotura por docblock» dio 12/13 y pareció rigurosa; los tres cableados de eventos sin guardia se quedaron fuera porque ningún docblock los nombraba. La lista que hay que mutar es la de «atributos sin los cuales la función está muerta», escrita al escribir el marcado.
- **`git checkout -- archivo` para restaurar una mutación se lleva también lo que ya habías arreglado en ese archivo.** Pasó tres veces en la ola final; lo cazó `git status`. Restaurar desde copia del archivo ya arreglado, no desde HEAD.
- **Una revisión en opus de una rama entera puede morir por límite de uso a mitad de lectura** sin dejar informe; la segunda arrancó de cero. Dividir en pases y escribir el informe antes de agotar el margen.

### 39.7 La primera mirada de Sua, nueve commits y una revisión adversaria (5 sep, mañana)

Sua abrió la rama en su navegador y volvió con cinco cosas: una raya roja bajo la barra, el resorte «sutilmente más lento», el módulo principal descentrado al comprimirse, que «Bolsas» y «El gremio» abrieran al pasar el cursor como el tema, y que los popovers de tema e idioma se pisaban. Todo se midió en Chromium antes de tocar nada (`f1-medir-geometria.js`, en el scratchpad de la sesión): el módulo principal caía 56 px a la izquierda del centro en inicial y 120 en scroll y atención, igual a 1440, 1280 y 1024; la raya era el `::before` de `.cromo-apoyado`, herencia de la banda de `main`; a los 120 ms de pasar del sol al chip los dos popovers estaban visibles (tema en x 1104–1280, idioma en 1130–1338).

Seis commits, cada uno con su guardia vista roja antes y una pasada de ocho mutaciones por comportamiento, todas rojas: `3c8d155` apaga la raya solo en escritorio; `175a089` estrena `--duracion-estado: 620ms` para la geometría del cambio y deja `--duracion-rebote` a los popovers; `205e912` hace de la `<nav>` una rejilla `1fr auto 1fr`; `97fb666` extrae `Alpine.data('desplegable')` —lo que la revisión final había dejado como deuda— y lo usan los cuatro desplegables, que ahora abren al pasar, ceden al instante cuando otro abre, y la cuenta gana la salida por `focusout`; `c66c849` y `41bcf20` son lo que la verificación cazó (§39.8).

Después, con Ultracode, una revisión adversaria de esos seis commits: tres lectores (Alpine, CSS, guardias) y dos refutadores por hallazgo, quince agentes. De once hallazgos se verificaron seis: cinco confirmados, uno refutado (Escape con el foco fuera; se arregló igual porque era una línea y estrictamente mejor) y cinco huecos de guardia que no hizo falta verificar para cerrarlos. Tres commits más, con la misma disciplina y ocho mutaciones más, todas rojas: `179e79a` da al módulo del logo su mínimo real (`min-width: max-content`), porque en rejilla `shrink-0` es inerte y entre 1024 y 1190 px el logotipo se aplastaba hasta 24 px de ancho y en atención a 1024 desaparecía; `117e33c` pasa los cuatro desplegables de `mouseenter` a `pointerenter`/`pointerleave` con `pointerType === 'mouse'`, porque en un equipo híbrido (ratón y pantalla táctil) el toque llegaba como `mouseenter` sintético y abría y cerraba en el mismo gesto; `d2f374f` hace que Escape devuelva el foco al disparador solo si estaba dentro del componente, y afirma en las guardias los cuerpos enteros de `ceder`, `alternar`, `asomar`, `retirar` y `cerrarYVolverAlFoco`, la gracia de 280 ms, el tramo del componente anclado en código con sus dos límites, y que `app.css` no use `var(--duracion-rebote)` en ninguna regla.

Lo que se midió al final (`f1-verificar.js`, `f1-hibrido.js`, `f1-hibrido2.js`): módulo principal en el centro exacto en los seis casos de 1440 y 1280 salvo atención a 1280 (8 px); logotipo de 175,4 × 32 e isotipo de 46,2 × 32 en los tres estados a 1024, 1150 y 1280; `.cromo::before` computa `content: none` en escritorio y `""` con opacidad 1 en móvil, que sigue en 56 px; `transition-duration` de la separación y de la caída, 0,62 s; «Bolsas» abre al pasar y sigue abierto sobre su panel; de Bolsas a El gremio, a los 120 ms el primero ya cedió; al salir, a los 150 ms sigue y a los 650 ya no; del sol al chip, a los 120 ms solo el idioma está abierto y a los 620 solo él se ve; «El gremio» abre y es alcanzable en atención; Enter abre, Escape cierra y devuelve el foco, tabular fuera cierra; en el híbrido (consulta de puntero fino forzada a verdadera y toques por CDP) el toque abre «Bolsas» con la secuencia `pointerenter:touch, mouseenter, click:touch`, el toque fuera cierra, el sol y el chip abren al toque y se excluyen, y el ratón sigue asomando por `pointerenter:mouse`; Escape con el foco en el campo «Nombre» y «Bolsas» abierto por hover cierra el panel y deja el foco en el campo, y con el foco dentro del panel lo devuelve al disparador.

### 39.8 Trampas de la mañana

- **`this.$el` dentro de un método de `Alpine.data` es el elemento de la directiva que lo llamó, no la raíz.** Por hover (`mouseenter` en la raíz) era la raíz; por clic o Enter era el botón. El aviso de exclusión salía con el botón como identidad, el propio componente lo tomaba por ajeno y cerraba lo que acababa de abrir: **Enter no abría nada** y ninguna guardia lo vio, porque leen archivos. Identidad por `$root`. Lo cazó la verificación en Chromium, que por eso no es opcional.
- **`1fr auto 1fr` centra de verdad solo si los lados caben.** A 1280 en atención la celda de la cuenta quedaba en 305 px y «Mi cuenta» partía en dos líneas: la rejilla prefiere envolver a desbordar. `whitespace-nowrap` en el módulo declara el mínimo real y la rejilla lo respeta; el centro cede 8 px, que es mejor que 15 px de barra de más.
- **En rejilla `shrink-0` no protege nada, y un `<img>` con `max-width: 100%` aporta casi cero al min-content de su celda.** La pista 1fr del logo bajó a 50 px a 1024 y el logotipo se pintó a 24 × 32 deformado, cosa que el manual de marca prohíbe; en atención el isotipo midió 0,1 px. Lo vio la revisión, no la verificación, que solo midió a 1440 y 1280. Regla: cuando un flex pasa a rejilla, cada `shrink-0` que protegía algo necesita su `min-width` explícito, y la verificación tiene que incluir el ancho mínimo del diseño.
- **`mouseenter` no distingue el dedo del ratón.** En un híbrido (Windows táctil, iPad con trackpad) la consulta de puntero fino es verdadera y Chromium sintetiza `mouseenter` antes del `click` de cada toque: abrir por hover y alternar por clic se anulan en el mismo gesto. Asomar por `pointerenter` y mirar `pointerType`. Y ojo con la emulación: `Emulation.setTouchEmulationEnabled` apaga `pointer: fine` en la página, así que para probar un híbrido hay que forzar la consulta desde `addInitScript`.
- **Medir destapa lo que no se pidió:** entre 1024 y ~1130 px la píldora no da para los tres módulos en inicial ni en atención (D-33). Estaba así antes de esta mañana; se anota y no se «arregla de paso», porque decidir qué cede es de Sua.
- **El `console.log` de un `run-code` de playwright-cli no vuelve por la salida;** el resultado se devuelve con `return`.
- **Una revisión adversaria por lentes vale lo que cuesta:** los tres lectores encontraron en veinte minutos lo que dos pasadas de mutaciones y dos verificaciones en Chromium no vieron, porque miraron anchos y dispositivos que yo no había puesto en la lista. La lista de qué medir la escribe alguien que no hizo el cambio.

### 39.9 La versión nueva de Ingrid y la fusión (5 sep, mediodía)

Sua dio la barra por buena («te luciste») y pidió aplicarla a «la versión nueva que Ingrid subió antier». Primero se miró qué era eso: `origin/main` no se había movido de `6c1b8b7`; lo nuevo eran dos commits del 4 sep en `origin/p2/acceso-asociados` (`1d6a0f9` «Rediseño visual de portada pública» y `239eda0`, una fusión de `main` con la resolución del hero), que traían la portada como video a pantalla completa con el titular encima y el header **fijo** (`cromo-fijo`) flotando sobre él, con 7rem de aire para la primera sección de las demás páginas. Tocaban cinco archivos; con la rama chocaban `navbar.blade.php` (una línea: la clase del header) y `app.css` (sus retoques a la bandeja del cromo, que la rama ya había reemplazado).

Se fusionó su rama en la nuestra (`dc4f6aa`): el header pasa a `cromo cromo-fijo z-40` conservando la máquina de tres estados, entran sus dos reglas de `.cromo-fijo` y se descartan los retoques a la bandeja vieja. Su rama traía `PortadaEditableTest` en rojo —el rótulo del video (`hero_video_rotulo`, `_titulo`, `_detalle`) había dejado de pintarse y ella lo dejó anotado como pendiente—: se pinta al pie del texto del hero, como pie de foto del video, a confirmar con ella. Y al medir la portada fusionada salieron dos contrastes rotos de su diseño (el «Afíliate» de contorno era tinta sobre negro y la píldora de afiliados rojo oscuro sobre negro) y una regresión nuestra que nadie había visto: **el móvil de la rama B no tenía vidrio** —`main` lo tenía a todo lo ancho y la rama lo movió a la píldora de escritorio dejando el móvil transparente—, que con el header fijo dejaba la hamburguesa perdida sobre el video. Cuatro commits más con guardia roja antes: `c650f3a` devuelve el vidrio por debajo de 64rem; `84d59d7`, `0c3fb15` y `8e53813` estrenan la variante `contorno-claro` del botón, la `etiqueta-clara` y el filete del pie del video, con el blanco del fondo oscuro en portadores CSS y no en utilidades, como hizo la Persona 2 con el `<h1>`: la suite completa cazó dos veces lo que la focal no vio (un comentario que nombraba la clase vieja del cromo, y `border-white`/`bg-white`, que la guardia de tema prohíbe con razón).

Medido en Chromium sobre `8e53813`: portada a 1440 con header fijo de 70 px sobre un hero de 100svh, titular y rótulo en blanco, video reproduciéndose, módulo principal centrado en los tres estados y cero desplazamiento horizontal; «Afíliate» blanco con borde blanco al 40 %; en /contacto, /directorio y /eventos la primera sección se aparta 112 px y ningún titular queda bajo el header; móvil a 390 con header fijo de 56 px, vidrio (velo al 72 % y desenfoque) sobre /contacto y sobre la portada, y el menú abriendo debajo.

**Sua pidió fusionar y desplegar** («fusiónala a main y despliega, dale»). Con la suite completa en verde sobre `8e53813`, `main` avanza por avance rápido hasta la rama y se empuja a `origin`; Cloud despliega desde git. Lo que pasó con producción está en §39.11. El commit `239eda0` de Ingrid entra en `main` con su `Co-Authored-By: Claude Opus 5`: quitarlo exigía reescribir su rama publicada y no se hizo sin preguntar. `ContenidoOficialSeeder` no se corrió en producción: toca datos y no estaba en el pedido.

### 39.10 Trampas del mediodía

- **`git merge-tree` con tres argumentos no marca los conflictos como yo esperaba:** contó cero y la fusión real tuvo dos. Ensayar con `git merge --no-commit` sobre un árbol limpio y leer `git diff --name-only --diff-filter=U`.
- **Medir la altura no es medir la barra.** Dos revisiones y dos verificaciones dieron el móvil por «intacto» porque la cabecera medía 56 px como en `main`; el fondo era transparente desde el primer commit de la rama. Lo destapó una captura, no una cifra: para «intacto» hay que comparar la lista de propiedades computadas contra la referencia, no una.
- **Una portada oscura invalida todo lo que era para fondo claro:** botón de contorno, píldora, `text-acento-fuerte`. Al adoptar un hero oscuro, recorrer cada elemento que hereda colores de la paleta clara. Y el arreglo no son utilidades `bg-white`/`border-white` (la guardia de tema las prohíbe porque no siguen al tema): el blanco de un fondo oscuro a propósito va en CSS, como portador, que es donde la Persona 2 puso el del `<h1>`.
- **Un encadenado con `;` al final comete el commit aunque el paso anterior fallara.** El «VERDE» no se imprimió y el commit se hizo igual; se corrigió en el siguiente. La cadena tiene que ser `&&` de punta a punta, con `|| true` solo en lo que no es puerta.
- **La suite focal no sustituye a la completa antes de un commit que toca vistas:** la guardia de tema y la del vidrio viven en archivos que la focal no corría. Antes de un commit, correr al menos los archivos de guardia que leen los archivos tocados.

### 39.11 Producción tras el push (5 sep, mediodía)

Se empujó `main` a las 12:07 (hora local) como `76b6620`. Una sonda consultó producción cada 30 s: a los 68 s del push la portada ya servía `data-estado="inicial"` y `cromo-fijo` y había dejado de servir `cromo-compacto`; el video del hero respondió 200 con 1.550.175 bytes y /contacto 200 en 1,2 s; una consulta posterior vio también `contorno-claro` y `pie-de-video`. **La barra B con la portada de la Persona 2 está en producción.** No se corrió `ContenidoOficialSeeder`: toca datos de producción y no estaba en el pedido; hasta entonces el rótulo del video usa sus textos de respaldo y la frase corta no se pinta. La consola de Cloud no se miró desde aquí. Este cierre entra en `main` como un commit más, y Cloud volverá a desplegar el mismo código con la documentación al día.

## 40. «AFÍLIATE» SE ESCONDE CON SESIÓN, Y LA BARRA MÓVIL 2.1 SE DISEÑA ANTES DE ESCRIBIRSE (5–6 sep 2026)

### 40.1 Dos encargos en un mensaje

La noche del 5 sep Sua vio en escritorio la pastilla «Afíliate» junto a «Admin Natalia Gutié…» y pidió esconderla a quien ya tiene sesión, sea socio, secretaría o dirección: «para él no es de interés». En el mismo mensaje abrió la fase siguiente, la **navBar 2.1**: el móvil en dos módulos, el superior con el logo, el tema y «Afíliate» sin sesión o el nombre y el rango con sesión; el inferior, que es el principal, con Directorio, Abre tu negocio y «los desplegables de bolsas y empleo» que abren al pulsar; los estados de escritorio menos atención; y un `scroll` que solo compacta al bajar y vuelve al tamaño inicial al subir. Dos cosas del brief se leyeron y no se resolvieron en silencio: «empleo» casi seguro es «El gremio» (Empleo es la primera fila de Bolsas), y Eventos no está en la lista.

### 40.2 «Afíliate» con sesión: una guardia vista roja dos veces (`09e8c17`)

La spec de la Parte I §6.3 ya decía que solo el anónimo ve «Mi cuenta» y «Afíliate»; el código nunca lo cumplió. El enlace pasa dentro del `@guest` del módulo de cuenta y, por coherencia, el bloque de invitado del panel móvil se envuelve entero; el pie lo conserva para todos. La guardia (`NavbarTresEstadosTest::test_afiliate_solo_se_ofrece_a_quien_no_tiene_sesion`) cuenta el `href` en el `<header>`: dos sin sesión, cero con sesión de cada uno de los tres roles, y el pie sigue enlazándolo. Roja antes del cambio (el header con sesión de asociado seguía ofreciéndolo) y roja otra vez al devolverle el enlace al panel móvil. Las siete clases de la barra en verde: 118 casos, 1.258 aserciones, 24 s.

### 40.3 El taller de dieciocho agentes

El móvil no se empezó por el código: el proyecto registra por escrito antes de codificar, y el dueño «eligió, no delegó» las ocho decisiones de la Parte I. Con Ultracode encendido se lanzó un taller sobre `09e8c17`: seis lectores en paralelo (guardias, CSS y layout, Alpine y estado, cuenta y roles, el móvil real, marca e iconos); tres diseñadores con ángulos distintos (A: un solo DOM y el mínimo cambio; B: la interacción táctil primero; C: el oficio del movimiento); tres jueces con pesos distintos (A ganó dos de tres, B uno); una síntesis a partir del ganador con las ideas de los otros; cuatro críticos adversarios por lente (guardias y DOM, iOS y Android reales, accesibilidad, fidelidad y oficio: 34 hallazgos verificados) y un revisor final que incorporó los 34 y rechazó ocho alternativas con su razón. El resultado es la **Parte II de `docs/ingenieria/navbar-tres-estados-diseno.md`** (`83e7390`), con dieciocho decisiones para Sua, cada una con recomendación (§2), guardias que cambian con su porqué (§8.1), la guardia nueva `NavbarMovilTest` con la rotura de cada prueba (§8.2), y la verificación en navegador (§8.4).

El taller se cortó dos veces por el límite de uso de la sesión (los tres diseñadores a las 9 pm, los cuatro críticos y el revisor a las 2 am) y se reanudó con `resumeFromRunId`: los agentes ya terminados vuelven de caché. La primera caída además tumbó el guion: un diseñador que muere devuelve `null`, y envuelto en `.then(d => ({ clave, diseno: d }))` pasa el `filter(Boolean)`; el filtro tiene que mirar el campo interior. Ultracode se apagó a mitad de la sesión; reanudar el mismo taller es continuar lo que Sua ya había pedido, no lanzar uno nuevo.

### 40.4 Lo que se midió (punto de partida)

Antes de diseñar, la barra móvil de hoy en Chromium real: cabecera de 56 px a 390, 360, 768 y 844 de ancho; logo de 153×40 (h-7 más `py-1.5`); hamburguesa de 44×44; barra lateral de tema a 16 px del borde inferior, justo donde iría un módulo inferior; rótulo del video a 134 px del borde inferior a 390×844 y a 112 a 360×800; en apaisado el hero mide 764 px sobre 390 de alto; la primera sección de /contacto lleva 112 px de apartado; `.cromo` computa `transform: matrix(1, 0, 0, 1, 0, 0)` y `.bandeja` `backdrop-filter: blur(20px) saturate(1.8)`. Guion en `f4/movil-base.js` del scratchpad de la sesión.

### 40.5 Lo que el taller destapó

- **`.cromo` es bloque contenedor de todo `position: fixed` descendiente**: lleva `transform: translateY(0)` con transición, que solo existían para `.cromo-oculto`, clase que ninguna vista usa. Un módulo inferior fijo dentro del header se pegaría bajo la cabecera de 56 px y no al fondo de la pantalla. Se retiran los tres.
- **Un vidrio no es ancestro de otro vidrio** (raíz de fondo, Filter Effects 2): con el `backdrop-filter` en la `.bandeja`, las hojas que cuelgan de ella no tienen página que desenfocar. El vidrio de cada módulo va a un `::before`. De paso: los popovers de escritorio en `inicial` cuelgan de una bandeja con vidrio, hallazgo de la Parte I que se mide aparte. Y **`view-transition-name` también forma raíz de fondo** (View Transitions 1 §2.1.1): la síntesis lo había puesto en los dos módulos y un crítico lo cazó.
- **Las guardias cuentan nodos dentro del `<header>`**: una sola `<nav>`, tres hijos `modulo`, el primer `div` con `gap-1`, dos `aria-current`, el `href` de afiliación. El módulo inferior tiene que vivir en el header y ser un segundo `<nav>` que se llame «Navegación principal», porque bajo 64rem la primera solo contiene logo y cuenta.
- **El apartado de 7rem colgaba del orden del layout**: `.cromo-fijo + .tema-lateral + main` deja de casar si la barra lateral se retira, y todas las páginas salvo la portada se meterían bajo el header sin que nada se pusiera rojo. Pasa a `~` con guardia.
- **11 px sobre el velo del 72 % no llegan a 4,5:1** (unos 3,0:1 en claro y 1,9:1 en oscuro sobre fotos): el velo móvil sube al 88/85 % por token, con la misma guardia calculada que el velo del hero (D-M18).
- **La histéresis de dirección se mide desde el extremo del recorrido**, no desde el último punto de decisión, o volver cuesta entre 12 y 36 px; los extremos del documento son zona muerta (rebote elástico), un salto de más de 200 px no es gesto, y bajo movimiento reducido no hay estado `scroll` (WCAG 2.3.3).
- **El inset de la zona segura no puede entrar en una altura transicionada**: en iOS salta de 0 a 34 px a mitad de gesto y se animaría 620 ms con sobreimpulso. Y `viewport-fit=cover` mete todo el documento bajo la muesca, no solo los módulos.
- **Cerrar una hoja por scroll no puede robar el foco**: con el foco dentro, una flecha abajo desplaza el documento y cerrar tiraría el foco al `<body>`, el defecto que el 5 sep se corrigió para Escape.

### 40.6 Trampas de esta sesión

- **Un agente de taller que muere devuelve `null`**, y envuelto en un `.then` que lo mete en un objeto sobrevive al `filter(Boolean)`: el guion revienta más abajo. Filtrar por el campo interior y fallar con mensaje si no queda ninguno.
- **El límite de uso mata a los agentes en vuelo y no a los terminados**: `resumeFromRunId` reproduce el prefijo intacto desde caché, y editar el posprocesado del guion no lo invalida.
- **En `run-code` de playwright-cli, un ayudante definido fuera de `page.evaluate` no existe dentro**: `ReferenceError: rect is not defined`. Los ayudantes se definen dentro de la función que se evalúa.
- **Las guardias que leen archivos crudos leen también los comentarios**: tres aseveraciones nuevas del diseño se habrían puesto rojas con los propios comentarios que el diseño escribía («solo servían a .cromo-oculto», «no innerWidth», «nunca filter: drop-shadow»). Nombrar sin pegar vale también para el CSS.
- **Una revisión por lentes vale lo que cuesta, otra vez**: los cuatro críticos cazaron 34 cosas en una síntesis que tres jueces habían dado por buena, y la más grave (el `view-transition-name`) contradecía en silencio la restricción central del propio diseño.

### 40.7 Lo que entra y sale del estado

Entra **D-34**, las dieciocho decisiones de la Parte II, dueño Sua. «Afíliate» con sesión pasa a hecho (`09e8c17`). `main` queda tres commits por delante de `origin/main` y nada de hoy está desplegado hasta empujar. La suite entera no se corrió: solo las siete clases de la barra sobre `09e8c17`. No se abrió rama: la barra móvil no se escribe hasta que Sua responda.


## 41. LA BARRA MÓVIL 2.1: DOS MÓDULOS, CONSTRUIDA CONTRA LA SPEC Y REVISADA CONTRA SÍ MISMA (6–7 sep 2026)

### 41.1 Las dieciocho decisiones, respondidas antes del código

La Parte II de la spec llegó con dieciocho decisiones abiertas y una recomendación en cada una. Sua respondió en una línea: «desde D-M1 hasta la D-M18 apruebo y apruebo todas las recomendaciones que propones». Con eso se abrió `p1-navbar-movil` y se derivó el plan por tareas. Lo que quedó decidido: «bolsas y empleo» son **Bolsas** y **El gremio**; Eventos como quinta pestaña; «Abre tu negocio» a dos líneas; la entrada del anónimo como fila de pie de la hoja de El gremio; el chip de idioma oculto bajo 64rem; la barra lateral de tema retirada; isotipo en scroll y con sesión; el chip con nombre y rango abre la hoja de cuenta; un segundo `<nav>` con su propio landmark; `viewport-fit=cover`; `MenuMovilTest` renombrada y reescrita; cierre por desplazamiento también en escritorio; rótulos plegados en scroll; apaisado compacto de nacimiento; el usuario demo de secretaría como persona; y el velo del móvil al 88 / 85 % para que los rótulos de 11 px lleguen a 4,5:1 sobre fotos.

### 41.2 Once tareas, cada guardia vista roja

Se construyó en once tareas con la regla de siempre: la prueba primero, la rotura deliberada después y la guardia vista roja antes de escribir el código que la pone verde. Veintisiete mutaciones en las nueve primeras tareas y cuatro más en la revisión. El resultado es un solo `<header>` con dos `<nav>`: la bandeja de arriba (logo doble con cruce a isotipo, chip de cuenta, tema) y el módulo inferior fijo con cinco pestañas y dos hojas que abren por toque. La máquina de estados vive en el `x-data` del header y decide por **dirección**: el ancla sigue al extremo del recorrido, compactar cuesta 24 px y volver 12, los dos extremos del documento son zona muerta y un salto de más de 200 px no es un gesto.

### 41.3 Lo que el navegador corrigió sobre la marcha

Cinco cosas que la spec daba por buenas y el navegador desmintió: el apaisado reasignaba tokens sobre `:root` desde `@layer components` y **una regla sin capa gana siempre**, así que los tokens se reasignan sobre `.cromo`; la pastilla «Afíliate» mide 33,7 px por la escala tipográfica y con 4 px por lado daba 42 de área, no 44, así que pasó a `-inset-y-1.5`; el segundo bloque de CSS móvil tenía que ir después de `.hoja-flotante`; a 320×180 sacar del fijo solo al módulo inferior no lo devolvía al flujo, porque sigue siendo hijo del header fijo, y dejaba una cabecera de 96 px sobre 180; y la raya roja del módulo superior se pintaba debajo del vidrio de la bandeja, que es `relative` con `z-index: 2`.

### 41.4 La revisión adversaria encontró lo que ninguna guardia veía

Seis lectores independientes sobre el diff (uno murió por el límite de sesión). Diez hallazgos, ocho arreglados. El grave: **el velo base de `:root` había subido de 72 % a 88 %** en el primer WIP, así que la barra de **escritorio** en claro cambió de material sin decisión mientras el oscuro seguía en 62 %; cuatro lectores lo señalaron por separado y ninguna prueba lo miraba, porque la del móvil lee el bloque de la media. También: el pie ofrecía «Entrar a mi cuenta» a quien ya tiene sesión, cuando la fila equivalente de la hoja es del anónimo; el módulo inferior declaraba un `translate: 0 0` que lo volvía bloque contenedor de los fijos, la misma trampa que se le había quitado al header el 4 sep; la aritmética de contraste WCAG estaba copiada por cuarta vez en vez de usar el rasgo `MideContraste`; y la fila de pie de la hoja no tenía objetivo táctil medido.

### 41.5 Lo medido en Chromium

Inicial: bandeja 56, fila de pestañas 68, módulo inferior en 776..844 a 390×844. Scroll: 48 y 48 con los rótulos plegados y el isotipo visible. Objetivos de 44 en las cuatro esquinas del cuadrado para pestañas, logo (153,5×44), chip (212×47,8) y «Afíliate» (45,7). El cambio de estado cae entre 30 y 40 px bajando; subir 10 no devuelve y subir 13 sí. Sin desbordar a 360, 320 y 768; a 320 el logo cruza al isotipo. Las hojas abren por toque con `pointer: coarse` verdadero, cierran por toque fuera, por desplazamiento de 30 px y por Escape devolviendo el foco. Con el video de la portada corriendo, 180 fotogramas durante un desplazamiento guiado: 6,1 ms de mediana, 6,3 el percentil 95 y 6,5 el máximo, **en este equipo y no en el teléfono**.

### 41.6 Fusionada y desplegada (7 sep)

Sua respondió D-35 en tres palabras: «fusiona la rama y empuja». `p1-navbar-movil` entró en `main` por avance rápido, así que el árbol desplegado es exactamente el que se probó, y `main` se empujó con los cuatro commits que esperaban desde el 5 sep. El push despliega: producción pasa a servir la barra móvil 2.1, el «Afíliate» escondido con sesión y el velo de escritorio devuelto al 72 %. No cambian datos, porque la rama no trae migraciones y los sembradores se corren a mano.

### 41.7 Lo que queda abierto

El teléfono real del directivo (Safari de iOS, la barra de direcciones, el rebote elástico, el teclado y la transparencia reducida) sigue siendo lo único que puede cerrar la barra. Dos cosas más quedan anotadas en la §13.3 de la spec: el foco se pierde al cruzar 64rem con una hoja abierta, y el móvil paga dos `backdrop-filter` permanentes sobre el video cuando el velo al 88 % deja al desenfoque un 12 % del píxel.


## 42. EL PANEL DE INGRID ENTRA DETRÁS DE LA BARRA MÓVIL (7 sep 2026)

Con `main` ya al día, Sua pidió traer `p2/acceso-asociados`, la rama donde Ingrid rehízo el panel de administración: identidad visual y tablero, páginas y tablas operativas unificadas, flujos de vacantes y gestión de imágenes, un conmutador de tema en la barra superior y la salida del panel hacia la portada. Cuatro commits del 6 sep sobre 31 archivos, todos del panel. No comparte un solo archivo con la barra móvil 2.1, así que la fusión no tuvo conflictos y el árbol resultante se probó entero antes de empujar.

**Llegó con dos guardias en rojo, y no era cosa de la fusión: ya fallaban en su rama por separado.** La primera, `TipografiaTest`: el tema del panel había cambiado el tracking de los titulares de `-0.02em` a cero, y el plano es decisión registrada, porque allí la interfaz es densa y no hay titulares de 60 px. Volvió al bloque base, y las reglas por elemento que Ingrid añadió siguen mandando donde las hay. La segunda, `TemaClaroOscuroTest`: la bandeja de moderación de fotos usaba `text-gray-950`, `text-gray-600` y `text-gray-400`, grises de fábrica que no siguen el tema; pasan a `text-fuerte` y `text-tenue`, los tokens de las demás vistas. Los dos arreglos van en un commit aparte del de la fusión, con el porqué escrito, para que se le pueda enseñar a Ingrid sin que parezca que se le tocó el diseño sin avisar. Se vieron rojos antes, verdes después y rojos otra vez al deshacerlos.

La suite sobre la fusión: 1.069 casos, 1.058 pasan, 11 omitidas, 0 fallos, 4.721 aserciones, 322 s. Lo que ninguna prueba cubre es el aspecto: la entrada al panel exige el segundo factor con la app TOTP, así que la sesión no pudo verlo con ojos y eso queda para Sua e Ingrid.


## 43. EL TABLERO RECUPERA SU FILA Y LA BARRA LATERAL SE DISEÑA ANTES DE ESCRIBIRSE (7 sep 2026)

### 43.1 El widget que se encogía

Con el panel de Ingrid ya desplegado, Sua abrió el tablero y encontró la banda de pendientes a un sexto de fila, con el rótulo truncado hasta desaparecer y el botón «Revisar» montado sobre el texto. Eran **dos causas y ninguna estaba donde parecía**. La primera: la vista propia del widget no usaba `x-filament-widgets::widget`, que es el envoltorio que llama a `gridColumn()` y coloca el widget en la rejilla; el marcador de carga sí traía el tramo, así que el widget se encogía justo al hidratarse, que es por qué parecía un problema de datos y no de maqueta. La segunda, invisible todavía: un `columnSpan` de `'full'` a secas Filament lo guarda como `['lg' => …]`, y la regla base de la rejilla solo lee `--col-span-default`, así que por debajo de 1.024 px los tres widgets de ancho completo caían a una sola pista. En tableta el tablero se habría visto a media fila sin que nadie lo hubiera notado hasta la demo.

### 43.2 Cinco miradas para una barra lateral

Sua pidió rehacer el menú de la izquierda, hoy una franja burdeos idéntica en los dos temas, «muy semejante a la navBar de escritorio pero vertical», con resorte al desplazarse y **un cristal con detalles luminiscentes en rojo claro para el modo claro y en rojo oscuro para el oscuro**. El diseño se hizo antes de escribir una línea, con cinco miradas independientes sobre el mismo encargo —movimiento, material, estados, encaje con Filament y accesibilidad—, cada una criticada por un adversario que verificó contra el repositorio, y una síntesis que resolvió las cinco contradicciones entre ellas. Cuarenta y dos decisiones fundidas en **dieciocho (D-L1 a D-L18), que Sua aprobó en bloque**.

**Tres hechos comprobados a mano cambiaron el encargo tal como se imaginó.** En escritorio la barra es `lg:sticky` y va en flujo, así que detrás de ella no pasa contenido y el `blur(14px)` que lleva hoy no desenfoca nada: el cristal de escritorio se hace con velo, luz y sombra, y el desenfoque de verdad queda para el cajón del teléfono. Su cabecera con el logotipo es `lg:hidden` cuando hay topbar, así que las reglas que le dedicábamos eran CSS muerto. Y una guardia verde ya prohibía `.fi-body::before`, que era el sitio natural del campo ambiental que una de las miradas proponía pintar detrás. De ahí salió la decisión más útil de todas: **la barra deja de tener paleta privada** y se deriva de los tokens, así que el día que se mueva la paleta se mueve con ella.

El rojo se partió en dos oficios. El que **alumbra**, el filo y el halo, sigue la palabra de Sua: claro en claro y oscuro en oscuro. El que **informa**, el rótulo del ítem activo, va en dirección contraria porque la aritmética manda y el rojo de marca no llega a 4,5:1 en ningún tema.

### 43.3 Lo que midió Sua, y lo que falta medir

El segundo factor impide que una sesión automatizada abra el panel, así que las cifras de partida las tomó Sua en su propio navegador con un guion pegado en la consola. Dieron tres cosas duras: los **24 ítems miden 43,5 px** y ninguno pasa la comprobación de las cuatro esquinas del cuadrado de 44; la lista tiene **1.651 px de contenido en 913 de hueco**, es decir que **se corta el 45 %**, que es justo lo que justifica el aviso de borde; y el fondo computa transparente porque la franja burdeos la pinta un `background-image`. La primera medición se tomó a 201 px de ancho, así que fue el cajón; Sua repitió con la ventana maximizada, a 1.084 x 1.083, y ahí la barra computó **`position: sticky`**. Eso convierte en hecho medido lo que hasta entonces era una lectura del vendor: en escritorio la barra va en flujo y pegada, detrás de ella no pasa contenido, y el `blur(14px)` que lleva hoy es coste sin imagen. Con la ventana grande la lista se corta el 38 % en vez del 45 %, porque el recorte depende del alto y no del ancho: está cortada siempre.

## 44. LA BARRA LATERAL DEL PANEL: CRISTAL, LUZ Y UN CAMPO DE PUNTOS (7-8 sep 2026)

### 44.1 Dieciocho decisiones y un taller de cinco miradas

Sua pidió rehacer el menú de la izquierda, «muy semejante a la navBar de escritorio pero vertical», con resorte al desplazarse y cristal con detalles luminiscentes en rojo. El diseño se hizo antes de escribir una línea: cinco miradas independientes sobre el mismo encargo, cada una criticada por un adversario que verificó contra el repositorio, y una síntesis que resolvió las cinco contradicciones entre ellas. Cuarenta y dos decisiones fundidas en dieciocho, aprobadas en bloque.

Tres hechos comprobados a mano cambiaron el encargo tal como se imaginó: en escritorio la barra es `lg:sticky` y detrás no pasa contenido, así que el `blur(14px)` que llevaba no desenfocaba nada; su cabecera con el logotipo es `lg:hidden`; y una guardia verde ya prohibía el sitio donde una de las miradas quería pintar el campo ambiental.

### 44.2 Lo que costó llegar: siete correcciones y dos regresiones

La construcción no fue en línea recta y conviene que quede escrito. **Los módulos con vidrio permanente sobre un fondo liso se leen como cajas dentro de cajas**: la fila se quedaba en 210 px útiles de 244 y Sua lo llamó «apeñuscado». Se aplanó todo, y entonces faltaban los módulos que el encargo pedía. Volvieron apagados, encendiendo por estado, y por fin permanentes cuando hubo un campo de puntos detrás que refractar. **La cuenta del usuario pasó por tres sitios el mismo día.** Y **el límite pasó por tres formas** —línea, filo y franja difusa— hasta que quedó claro que ninguna servía: con el fondo continuo, nada separa la barra del contenido.

Dos regresiones visuales se entregaron sin verlas, y esa es la lección del día. La segunda rompió la barra entera: el lienzo del campo perdía su posición absoluta porque `.fi-sidebar > *` empata en especificidad con su regla y va después, caía al flujo con alto completo y empujaba la lista fuera de la vista.

### 44.3 La maqueta, que debió existir desde el principio

El panel exige segundo factor, así que ninguna sesión automatizada lo abre. Tras la segunda regresión se construyó una maqueta que reproduce el marcado de la barra con el tema compilado y se sirve por HTTP. Se ganó el sueldo dos veces: la primera vez que se abrió reprodujo el aviso que la spec ya tenía anotado (sin `fi-sidebar-open`, Filament deja la barra fuera de pantalla), y después cazó un falso verde propio: la guardia afirmaba que la barra tenía sombra y el navegador computaba `rgba(0,0,0,0) 0 0 0 0`, porque Filament le aplica `lg:shadow-none` desde una capa que gana. Estar escrita no es aplicarse.

### 44.4 Cómo quedó

La barra no tiene fondo propio: el fondo es un campo de puntos que huyen del cursor, dibujado en un lienzo fijo detrás de toda la interfaz, con su color en tokens (invierte con el tema) y la repulsión apagada bajo movimiento reducido. Cada apartado es una lámina de cristal. La zona del panel la marca un resplandor rojo, claro en el tema claro y oscuro en el oscuro. La fila mide 48 px, contra los 43,5 medidos al empezar, que no pasaban el mínimo táctil en ninguno de los 24 destinos. El foco salió de la media de puntero, donde estaba atrapado. La cuenta vive arriba con nombre y rango, la campana se retiró y el control de tema tiene ya las tres preferencias del sitio público.

### 44.5 Los dos cortes que no eran el mismo corte (8 sep)

Sua miró la barra en oscuro y dijo dos veces que los módulos se veían cortados. Eran dos cosas distintas y ninguna era la que parecía.

El primero sí era un recorte: la máscara de desvanecido que avisa de que hay más lista medía 1,5 rem y la lista solo tenía 0,5 rem de aire vertical, así que en reposo el canto de la primera y de la última lámina nacía dentro del desvanecido. Con el vidrio suelto no se notaba; desde que el módulo tiene borde, un borde a medio pintar se lee como una caja cortada. El aviso baja a 0,9 rem y el relleno pasa a `calc(aviso + 0,35 rem)`.

El segundo no era un recorte en absoluto, y por eso conviene que quede escrito: **el módulo acababa en 239,2 px y lo único que recorta cortaba en 244**. Lo que había era estrechez. El relleno de la lista era asimétrico —0,75 rem a la izquierda, 0,3 a la derecha— desde que Sua pidió agrandar los módulos hacia la derecha: se le quitó al aire de ese canto en vez de al ancho de la barra. Con 16 px de radio y 4,8 px de aire, la curva del canto derecho no tenía fondo contra el que leerse. La barra sube de 15,25 a 15,75 rem, el relleno vuelve a ser simétrico y el módulo queda en 228 px, un poco más ancho de los 227,2 que tenía.

Dos lecciones. Una: **medir antes de arreglar**, porque el arreglo obvio —ensanchar el recorte— no habría tocado la causa. Otra: **la maqueta solo reprodujo el segundo defecto cuando se le puso el marcado real del grupo**, con su botón de plegado; la maqueta aproximada dio verde sobre algo que en el panel se veía mal. Una maqueta vale lo que se parece.

Con eso Sua dijo «empuja». Suite completa como portón: 1.091 casos, 1.080 pasan, 11 omitidas, 0 fallos, 4.920 aserciones en 605 s. Veinte commits de una vez, y comprobado por contenido servido y no por hash: el CSS del panel en producción trae `--asb-admin-sidebar-ancho:15.75rem` y el relleno simétrico. Sale D-37.

### 44.6 Las cuatro tareas que faltaban, y los cuatro defectos que destaparon (8 sep 2026)

Con la barra ya desplegada y dos peticiones más de Sua atendidas —el cristal de los apartados deja ver el campo, y el resplandor cubre todo el lado en vez de apagarse a media altura— se cerraron las cuatro tareas que le quedaban al plan. Lo interesante no son las tareas: son los cuatro defectos que ninguna de ellas iba buscando.

**Las cuatro señales del sistema (D-L17)** llegan por fin a la barra, en cuatro bloques fuera de `@layer components` a propósito: reasignan tokens, y el `:root` de ese archivo también vive fuera de capa, así que dentro de la capa la reasignación perdería. La guardia afirma las tres cosas —que los bloques existen, que cada uno reasigna lo suyo, y que **ninguno cae dentro de una capa**— y se vio roja con las tres mutaciones. Al ponerla se destapó el primero: **el desenfoque del cromo superior estaba escrito a mano en cuatro declaraciones**, así que `prefers-reduced-transparency` no podía apagarlo. Es exactamente el defecto que D-L17 había anotado para la barra, vivo en el sitio de al lado.

**La guardia de contrato con el vendor** afirma once cadenas de Filament con su porqué en el mensaje, y el hecho del panel real. Es la única prueba de la clase que se rompe sola, sin que nadie toque nuestro código: cuando Filament suba de versión.

**La maqueta pasó a ser un comando**, `php artisan maqueta:barra`, porque llevaba dos días generándose con guiones de un solo uso. Con ella se midió la barra construida: 252 px de ancho, las 22 filas a 48 px pasando las cuatro esquinas del cuadrado táctil, ningún rótulo recortado, la sombra del módulo aplicándose de verdad y los rótulos computando exactamente los colores que la guardia de contraste supone. Y enseñó dos lecturas que **parecen defectos y no lo son**: `elementFromPoint` devuelve `null` fuera del viewport, así que medir el objetivo táctil sin desplazar la lista inventa catorce fallos; y leer `box-shadow` justo tras cambiar de estado devuelve el valor interpolado en t = 0, que se lee igual que una sombra anulada.

**La revisión adversaria** sacó los dos últimos. **Cinco tokens declarados sin ningún consumidor, con dos guardias verdes encima de uno de ellos**: `--asb-admin-barra-filo` existía para volverse línea bajo más contraste y hacía dos días que nadie lo pintaba, desde que Sua rechazó el filo rojo. La guardia que había vigilaba un token concreto; la nueva generaliza a los cuarenta y dos y nació roja señalando los cinco. Y **el comando de la maqueta escribía una página servible dentro de `public/`**: en producción eso es publicar el marcado del panel sin que nadie lo pida, así que ahora se niega, y se niega antes de tocar el disco.

La lección común a los cuatro: **ninguno lo destapó mirar la pantalla**. Los destapó escribir la guardia que faltaba y verla roja. Es lo contrario del día anterior, donde lo que faltaba era mirar.

## 45. EL PANEL EN EL TELÉFONO: UN RIEL DE ICONOS Y NINGÚN SUELO (8 sep 2026)

_Esta entrada se escribió después de los hechos, la tarde del 8 de septiembre. La sesión que hizo el trabajo empujó y desplegó seis commits —`f7a9171`, `2165244`, `5fdbedc`, `4cc25d2`, `3b4d60a` y `0594058`— sin reescribir `estado.md` ni anexar aquí, así que se reconstruyó leyendo los seis y la spec, que sí quedó al día antes del código. La lección de expediente va en el §45.6._

### 45.1 El defecto que no era de diseño, y que también le costaba el `sticky` al escritorio

Sua mandó una captura del panel en un teléfono: «la barra del panel en el móvil está terrible. Quiero que se vean los iconos a la izquierda y que al desplegarlo aparezcan los nombres correspondientes». Lo primero que salió no era de diseño.

**El tema declaraba `position: relative` en `.fi-sidebar`.** Filament la declara `fixed` y solo la vuelve `lg:sticky` en escritorio, y nuestra regla va después en el archivo compilado —que no lleva capas: `lightningcss` las aplana y manda el orden—, así que ganaba en todas las anchuras. En el teléfono, el cajón cerrado dejaba de estar fuera de pantalla y ocupaba sus 252 px **en el flujo**: la franja vacía de la captura, con el contenido aplastado contra el canto derecho. Y en escritorio `lg:sticky` también perdía, así que **la barra llevaba dos días yéndose con el desplazamiento de la página** y nadie lo había dicho. La regla existía para darle bloque contenedor al resplandor; desde que lo pinta `.fi-body::before` no hace falta ninguna. Fuera, con guardia que afirma que `.fi-sidebar` no declara `position` y dice por qué en el mensaje.

### 45.2 El riel (D-L29)

Por debajo de 64 rem la barra deja de irse: se estrecha. Cerrada, solo los iconos; abierta, icono y nombre superpuestos al contenido. Cuatro reglas que no se negocian, cada una con su mutación vista roja:

1. **El nombre no se borra, se esconde.** Recorte visual sobre un cuadro de 1 px, nunca `display: none`: un riel de iconos sin nombre accesible es una lista de enlaces sin texto.
2. **La fila sigue midiendo 44 px** como mínimo.
3. **La transición es del ancho, declarada a mano.** Filament pone `transition-all`, que la Parte III ya había prohibido para el cajón.
4. **`translate: none`, no `translate: 0 0`.** Deshacer el `-translate-x-full` de Filament con un cero deja un `translate` computado distinto de `none`, y eso convierte la barra en bloque contenedor de todo `fixed` que cuelgue dentro. Es el mismo pisotón que este proyecto ya pagó dos veces.

Tres cosas aparecieron solo al medir. **El ancho del riel se pone moviendo el token de Filament, no la propiedad**: su regla de ancho tiene mucha más especificidad que cualquier `width` declarado aquí y el riel seguía saliendo de 252 px; como esa regla dice `width: var(--sidebar-width)`, basta con darle otro valor al token dentro de la media. **La reasignación del lavado salió de `@layer components`**, porque las reglas sí viven dentro de la capa y los tokens no pueden: el `:root` sin capa del mismo archivo les gana. Y **la maqueta mintió tres veces**, que es por lo que el defecto del `position` llevaba dos días invisible: declaraba `position: sticky` sobre `.fi-sidebar` —tapando justo lo que fallaba—, ponía su `<style>` después de la hoja compilada —tapando el `display: none` del chevron— y no reproducía el `opacity: 1` que el blade le da al contenido, así que medía un contenido invisible.

**Y el riel se quedó sin suelo el mismo día.** Sua lo vio construido: «deja solo los módulos y quita la barra blanca de fondo que los agrupa para que así se les vea libertad, y a los módulos entrégales un poco de transparencia». Medido en la maqueta antes de tocar nada, ese blanco **no era de la barra** —que computa `rgba(0,0,0,0)`— sino de `.fi-sidebar::before`, que pintaba el velo del **cajón** al 94 % también con la barra cerrada. El velo del cajón tiene su razón (D-L18: debajo pasa contenido variable y hay que taparlo) y esa razón no existe en el riel, que no tapa nada: solo está a un lado. El suelo se ató al estado, y el cristal de los apartados bajó del 76 al 66 % por debajo de 64 rem, recalculado con `MideContraste`: rótulo de grupo 11,08:1 en claro y 7,74:1 en oscuro, ítem activo 6,23:1 y 5,23:1.

### 45.3 D-L30: cromo centrado, perfil anclado, aire y un resorte ligado al gesto

Sua pidió cuatro cosas más viendo el teléfono, y una quinta que resolvió al preguntarle: **el cambio es solo del teléfono**; en escritorio no se toca nada, porque lo que hay está aprobado y desplegado.

El **cromo** deja la hamburguesa donde estaba, centra el logotipo por rejilla `1fr auto 1fr` —no por `justify-content`, que con dos costados de anchura distinta descentra a ojo— y manda el control de tema a la derecha. El **perfil** baja al pie de la barra y **flota sobre la lista**: si fuera su último hermano, la lista terminaría encima y no habría nada pasando por debajo, que es lo que se pidió. La lista reserva su alto o el último destino quedaría inalcanzable. La cuenta se pinta en **dos ganchos**, con dos cuidados: el identificador de la hoja se compone según dónde se pinte —dos copias con el mismo id dejan un `aria-controls` apuntando a dos sitios— y la copia que no toca se apaga con `display: none`, que es lo único que la saca del orden de tabulación. El **riel sube de 3,5 a 4 rem** porque el aire sale de algún sitio: con 3,5 y 0,5 a cada lado el módulo caería a 40 px y se rompería el mínimo táctil; con 4 quedan 48.

El **resorte** es lo único verdaderamente nuevo. Al desplazar el riel, cada icono se retrasa respecto al dedo y llega con muelle, tanto más cuanto más rápido el gesto. No es una transición: una curva CSS no sabe a qué velocidad va la mano. Es una integración con tensión y amortiguación, y por eso responde. Cuatro reglas afirmadas por separado en la guardia: solo `translate` —cualquier otra propiedad mide la página por fotograma—, el bucle se para solo, lo que se mueve no recibe el dedo mientras se mueve —un destino que huye del pulgar es peor que uno quieto— y solo por debajo de 64 rem.

**La guardia cazó de entrada lo que este proyecto ya pagó una vez:** el módulo no estaba cableado ni en `vite.config.js` ni en los activos del panel, así que no habría llegado nunca al navegador y las demás afirmaciones habrían vigilado un archivo muerto.

Dos cosas salieron de medir y no de suponer. **La primera reacción se pinta en el mismo gesto** y no en el fotograma siguiente: se escribió así al descubrir que no había forma de verlo —`requestAnimationFrame` no corre con el panel del navegador oculto— y resultó ser además lo correcto, porque la respuesta sale con la mano y no detrás de ella; el bucle se queda con el regreso. Y **`ARRASTRE` se calibró midiendo**: con 0,55 un desplazamiento de 24 px por fotograma —un pase normal del pulgar— ya saturaba el tope y todos los iconos se quedaban en 14, justo donde importa; con **0,35** el rango útil cubre de 5 a 40 px por fotograma.

**Y la lección que costó dos vueltas y quedó escrita en el código: en el CSS compilado de este proyecto el orden no es nuestro.** `lightningcss` aplana las capas, agrupa las medias y mueve reglas, así que dos reglas de la misma especificidad **no** se resuelven como están escritas. Pasó dos veces seguidas: el `display: none` de escritorio salía después del de móvil y lo anulaba, y `position: relative` de `.asb-barra-cuenta` le ganaba a `position: absolute` de `.asb-cuenta-al-pie`. Se arregló sin depender del orden: el apagado de escritorio vive en su propia media de `min-width`, y las reglas del teléfono llevan `.fi-sidebar` delante para ganar por especificidad.

### 45.4 Las siete correcciones de Sua

Vio la primera versión en el teléfono y nombró siete cosas. Tres merecen quedar escritas.

**«Tablero» no tiene grupo.** Los módulos seguían pegados al canto, y era cierto para uno solo: Filament pinta los destinos sin grupo **sueltos en la lista**, fuera de todo `.fi-sidebar-group`, así que no recibía ni cristal ni aire mientras los demás flotaban. Ahora es un módulo más.

**El resorte movía lo de dentro y no los módulos.** Sua lo diagnosticó con precisión: el indicador rojo del apartado activo se quedaba quieto mientras su fila se desplazaba, **porque el indicador lo pinta el módulo y la fila iba por su cuenta**. Lo que se mueve pasa a ser el módulo.

**Había un corte entre el cromo y el cajón**, y eran dos alturas para lo mismo: una media de 40 rem dejaba el cromo en 3,45 rem mientras la barra empieza en `--asb-admin-topbar-alto` (3,75). Cinco píxeles por los que se veía colarse el contenido. El alto del cromo pasa a ser uno solo, el del token.

Las otras cuatro: el logotipo sube de 6,5 a 10 rem —su tope venía de cuando compartía fila con la cuenta, y en el centro del cromo hay sitio de sobra—; el menú de la cuenta abre **hacia arriba y hacia dentro**, porque colgar hacia abajo y a la izquierda es correcto en el cromo y absurdo al pie de una barra de 64 px, así que se abría fuera de la pantalla; el `bg-white` que Filament le da a la barra por debajo de `lg` se apaga con un selector que gana **por especificidad, no por orden**; y el cajón, que va aparte.

### 45.5 El cajón tampoco tiene suelo

Sua lo rechazó dos veces y la segunda tenía razón. El primer intento lo volvió una lámina de cristal —separada del borde, con radio, canto y el velo bajado de 94 a 84 %— y **seguía siendo una barra detrás de los módulos**. La corrección definitiva es que no hay suelo ninguno: `content: none` en su `::before`, igual que en el riel, y entre los módulos se ve la página atenuada.

Eso mueve la carga del contraste. Sin suelo detrás, lo que sostiene la lectura es el cristal de cada módulo, y ese sí sube **dentro del cajón**, del 66 % del riel al **84 %**. La diferencia tiene una razón física: en el riel, detrás del módulo hay campo de puntos sobre la superficie del panel, que es un color conocido; dentro del cajón hay **página**, y la página puede ser cualquier cosa. Medido sobre los dos extremos, con el velo de cierre de Filament en medio: al 66 %, sobre página negra, el rótulo del ítem activo da **3,03:1 y no pasa**; al 84 % da 4,61 sobre negra y 5,49 sobre blanca, y en oscuro 5,30 y 4,78. **Ese 84 es el suelo medido, no una preferencia.**

Y una nota sobre las guardias que conviene no perder: **la del riel afirmaba que el velo del cajón colgaba del estado abierto**, que era verdad cuando se escribió. Al quedarse el cajón sin suelo dejó de colgar de ningún estado, así que la guardia se reescribió para afirmar lo que hoy es cierto —que la barra no pinta suelo ni cerrada ni abierta— y el velo del cajón sigue existiendo solo para la cuenta anclada, que sí tapa porque por debajo pasan los iconos. Por eso `Panel/BarraLateralTest` baja de 302 aserciones a 298 con un caso más: no se perdió cobertura, se dejó de afirmar algo que había dejado de ser verdad.

### 45.6 Lo que quedó, y una lección de expediente

Suite completa la tarde del 8 sep, sobre `0594058` y ya desplegado: **1.105 casos, 1.094 pasan, 11 omitidas, 0 fallos, 5.048 aserciones en 317 s**. Producción comprobada por contenido servido y no por hash: el CSS del panel trae `--asb-admin-barra-riel:4rem` y, dentro de `@media (width<=63.999rem)`, `.fi-sidebar.fi-sidebar-open:before{content:none}`, que es el último commit. Lo único abierto es **el tacto del resorte en un teléfono de verdad**, que no se mide aquí; lo gobiernan `ARRASTRE` y `AMORTIGUACION` y se ajustan en una línea cada una.

Y la lección que no es técnica. **Los seis commits se empujaron y se desplegaron sin reescribir `estado.md` ni anexar esta entrada.** El estado se quedó seis commits atrás apuntando a un árbol que ya no era el desplegado, y el §3 del prompt maestro existe exactamente para eso: la sesión siguiente lo detectó comparando el encabezado con `HEAD`, y recuperar el día costó leer los seis commits, la spec y volver a medirlo todo. Lo que **no** se perdió fue el diseño, porque D-L29 y D-L30 sí se escribieron antes del código. Se perdió la foto. Barato de arreglar esta vez; caro el día que la sesión que llegue no se dé cuenta.

## 46. EL PLAN DE TRABAJO DE INGRID, MEDIDO CONTRA EL CÓDIGO (8 sep 2026)

### 46.1 La mitad del encargo ya estaba hecha

Sua trajo un documento de reparto que Ingrid escribió el 8 de septiembre y que le asigna cinco frentes: bolsas exclusivas, beneficios por territorio, WhatsApp y orden alfabético, analítica, y el QA de sus módulos. Antes de escribir una línea se midieron los cinco contra el repositorio, con cuatro reconocimientos en paralelo. **Tres de los cinco estaban total o casi totalmente construidos.**

Las **bolsas** se hicieron el 4 de septiembre en `f2092c5`: registro público naciendo pendiente, aprobación y devolución con motivo en el panel, `/mi-cuenta/proveedores` y `/mi-cuenta/aspirantes` detrás de la sesión. El **orden alfabético** de la portada existe desde antes, con `Collator('es_CO')` porque SQLite ordena por bytes, y con cuatro pruebas que lo vigilan. Del **WhatsApp** existían el ajuste, el ayudante que normaliza el número y cero números escritos a mano en Blade: el RNF-09 ya se cumplía y lo único que faltaba era el botón.

Y una parte del documento **pedía revertir una decisión escrita**: «en Empleo, el contenido interno de la bolsa queda restringido a usuarios asociados». La bolsa de empleo no se cierra, lo decidió Ingrid misma el 4 de septiembre —«quien busca trabajo tiene que poder ver la vacante para postularse»—, está en `encargo.md` §13 y tiene prueba que lo afirma. Sua resolvió dejarla pública.

**La lección de reparto, que es la que vale para la próxima:** medir el encargo contra el código antes de aceptarlo. De nueve tareas, dos estaban hechas, una casi, dos eran ampliación de alcance sin acta y una revertía una decisión registrada. Aceptarlo entero habría significado rehacer trabajo y romper lo que ya funcionaba.

### 46.2 Lo que sí faltaba, y el documento no nombraba

Dos huecos reales, ninguno mencionado como tal en el plan.

**Las fichas públicas de artistas seguían dando WhatsApp e Instagram** mientras las de proveedores ya no daban nada. O el criterio del 3 de septiembre vale para las tres bolsas o no vale. Se movió el contacto detrás de la sesión **sin vaciar la ficha**: nombre, foto, género y video siguen públicos, porque el escaparate es lo que el artista viene a buscar. Es el patrón de los convenios: página pública, una línea que dice que el detalle es de los afiliados, y el dato real dentro de `/mi-cuenta`.

**El banco de talento no tenía puerta.** Quien dejaba su perfil en `/empleo` quedaba visible en el mismo segundo para todos los establecimientos afiliados: nombre, teléfono y correo de un tercero, a un público cerrado, sin que nadie los mirara. Ahora hay `aprobado_el`, y nace en null para todos —incluidos los siete de la D-27, que aceptaron con otra política—. Sua eligió esa opción sabiendo el costo: el banco se ve vacío hasta que la secretaría apruebe uno por uno.

### 46.3 Dos ampliaciones, y el acta antes del código

Beneficios por territorio y analítica no figuran en ningún RF de la ERS v3, así que son ampliación. Se emitió el **Acta 07** —numerada 07 y no 06 porque el 06 sigue reservado para la ampliación de las bolsas (D-26)— con el costo medido y no estimado a ojo, y con una contrapropuesta que es la parte que importa: **analítica anónima sin visitantes únicos**, porque distinguir personas exige IP, cookie o sesión, que es justo lo que el diseño evita para no entrar en la Ley 1581. Sua aprobó todo lo relativo a firmas y se construyeron las dos.

En **beneficios**, la decisión que gobierna el resto es que `alcance` nace nullable y sin valor por defecto: los cinco sembrados salen del catálogo oficial y ese documento no dice de quién es cada uno, así que clasificarlos de oficio habría sido publicar una afirmación que nadie hizo. Hay guardia contra eso. En **analítica**, la vuelta de tuerca es que no hay una fila por visita sino un contador por ruta y día: la tabla crece con el calendario y no con el tráfico, y desaparece la hora exacta de cada visita, que era la última traza que quedaba.

### 46.4 Tres falsos verdes cazados al romper las guardias, y uno que no era nuestro

**Cuarenta y dos casos nuevos** —la suite pasó de 1.105 a 1.147 en la sesión—, todos vistos rojos antes del código y rotos después uno por uno. Tres pasaban por el motivo equivocado y solo se supo al mutarlos.

1. **«El panel no cuenta como visita del sitio» pasaba porque Filament no usa el grupo `web`**, no porque el filtro funcionara: el contador no llega ahí. Se conserva la prueba —fija el resultado— y se añade la del portal del afiliado, que sí pasa por `web` y ejerce el filtro de verdad.
2. **«Una respuesta que no es 200 no cuenta» pasaba por la rama de "ruta sin nombre"**, no por la comprobación del código. Se partió en dos, y la segunda usa una ficha de artista en borrador: ruta con nombre que responde 404.
3. **Dos pruebas del sello de alcance afirmaban sobre texto que la página ya traía por otro lado**: el sitio entero se llama «ASOBARES Quindío» y `/afiliate` lista todos los municipios en su formulario, así que un `assertSee` pasaba aunque el sello dijera cualquier cosa. Ahora se afirma sobre el contenido del sello con una expresión regular.

Y una trampa que no era de nadie de la casa: **`dia` con casteo a fecha se guarda como «2026-09-08 00:00:00» en SQLite y como «2026-09-08» en PostgreSQL**, la misma fila con dos formas según el motor y las comparaciones de la ventana dependiendo de eso. Cuando una columna es la clave de un cubo y no un instante, se trata como la cadena que es.

**Y una lectura del navegador que parecía un defecto y no lo era:** midiendo el botón flotante, las cuatro esquinas de su caja devolvían «tapada» con `elementFromPoint`. El botón es un círculo: las esquinas del rectángulo caen fuera de él. Los cinco puntos sobre el círculo devuelven el botón. Es la tercera vez que este proyecto anota una lectura de `elementFromPoint` que hay que interpretar antes de creer.

### 46.5 Cómo quedó

Cinco commits en la rama `p1-cierre-bolsas`, ninguno empujado. Suite completa sobre `fc2142f`: **1.147 casos, 1.136 pasan, 11 omitidas, 0 fallos, 5.204 aserciones en 335 s**. El botón de WhatsApp y el sello de alcance se vieron en el navegador a 375 y a 1.280 px; la analítica se comprobó contra el servidor de desarrollo y no solo en pruebas.

Queda **un solo bloque del plan sin tocar: el QA**, y está bloqueado por lo mismo desde el principio: el documento dice que la auditoría funcional dio 44 PASS, 2 FAIL, 1 BLOCKED y 3 NOT TESTED, **y no dice cuáles**. Los 2 FAIL son lo más accionable de todo el plan y no están descritos en ninguna parte.

---

## §47 — La auditoría de extremo a extremo, y las nueve cosas que no hacían su trabajo (9 de septiembre de 2026)

Sua pidió un análisis del programa entero: no errores de código, sino **cosas que sirven para algo y no lo cumplen porque les falta la pieza de al lado**. Y contrastarlo contra los documentos que dicen qué debía tener la plataforma. Salieron nueve, más una petición nueva de la dirección. Todo se arregló el mismo día, en la rama `p1-auditoria-y-metricas`.

### 47.1 Lo que enseñó la auditoría, que es lo que conviene no olvidar

**El expediente de este proyecto describe con precisión lo que el código hace, y no describe lo que el código NO hace.** Los nueve hallazgos son de la segunda clase, y ninguno se veía leyendo el estado: hay que ir a mirar el código y contrastarlo contra lo que otro documento promete.

Los tres patrones que se repitieron:

1. **Una mitad construida y la otra no.** Las purgas escritas, configuradas, probadas y sin nadie que las llame en producción. El JSON-LD `JobPosting` de la vacante sin la URL en el sitemap. El ajuste `contacto_correo_destino` en el panel sin una línea que lo lea.
2. **Un contrato escrito que el código contradice.** `bootstrap/app.php` dice que la analítica «cuenta páginas servidas, no descargas», y contaba descargas.
3. **Una decisión que se aplicó a medias.** La campana del panel se apagó el 7 de septiembre y nadie retiró a quien escribía en ella.

Y la lección de método: **las cifras del expediente hay que medirlas también contra el navegador**. `estado.md` afirmaba «el sitio dice 60» sobre la cifra de afiliados. El sitio no decía nada: `cifra_afiliados` estaba sembrada y ninguna vista la pintaba. D-18 llevaba dos semanas persiguiendo un número que no se publicaba en ninguna parte.

### 47.2 Lo que se descartó midiendo, y por qué importa

Dos sospechas se cayeron al comprobarlas, y las dos habrían costado una tarde de trabajo inútil:

- **`TRUSTED_PROXIES` se lee con `env()` fuera de `config/`**, y con `config:cache` —que el despliegue ejecuta— eso suele devolver `null`. El runbook lo llama «bloqueante». Se midió contra producción en vez de razonarlo: el sitemap servido sale en `https` y las cookies van `secure`. Cloud inyecta las variables como variables de entorno reales. No hay problema.
- **Las cuatro cifras de la portada** (12,65 %, $2.104.124, 72,82 %, 35,28 %) parecían contradecir el «franja vacía de fábrica» del estado. Son **dos franjas distintas**: la visible es la del Observatorio de la Nacional, con fuente en `NoticiaSeeder`; la del Acta 05 es `gremio_cifra_*` y sigue vacía y oculta, como debe.

### 47.3 El hallazgo más caro: las purgas sin quien las dispare

Tres comandos de depuración de datos personales, programados a diario en `routes/console.php`, con su configuración, sus seis variables declaradas en `.env.staging.example` y **tres archivos de prueba** que verifican que borran bien. Ninguna prueba miraba si alguien los **llama**.

Y el runbook de despliegue —quince secciones— **no menciona el planificador ni una vez**. En Laravel Cloud el Scheduler es un recurso que se añade al entorno y no viene de fábrica. Se buscó «scheduler», «schedule:run», «cron» y «tarea programada» en todo `docs/`, `material/` y los dos `.env`: cero resultados fuera de un plan de agosto.

Lo que cuelga de eso son dos promesas escritas:

- `/politica-de-datos`, línea 143, al titular: «Pasado cada plazo, el borrado es automático».
- El manual de usuario, al gremio: «se borran solos… el sistema la cumple sin que nadie tenga que acordarse».

Se cerró por los dos lados. `CalendarioDeTareasTest` (6 casos) falla si alguien quita una tarea o le cambia la frecuencia —comprobado en rojo dos veces: comentando las tres y bajando una a semanal—, y el runbook gana un **§5.1** con el paso, el comando de verificación y el aviso de que el silencio de la bitácora no prueba nada mientras su presencia sí.

**Lo que sigue abierto, y solo lo cierra una persona: activar el Scheduler en el panel de Cloud.** Una prueba no puede saber si el entorno remoto tiene quien llame a sus tareas.

### 47.4 El agujero de seguridad del banco de talento

`Aspirante::updateOrCreate(['correo' => …], [...$datos, 'aprobado_el' => null])`. La clave es un correo **que teclea un anónimo en un formulario público**, sin verificación de titularidad ni de correo.

Con eso, quien conociera el correo de alguien del banco podía reescribirle el perfil entero —cambiar el teléfono desvía a los establecimientos hacia otro número— y, con la misma llamada, **sacarlo del banco**, porque cada envío ponía `aprobado_el` en nulo. A seis envíos por minuto, vaciar el banco era cuestión de rato.

Lo llamativo es que **había una prueba afirmando ese comportamiento**: `test_volver_a_dejar_el_perfil_lo_devuelve_a_revision`. Su intención era buena —aprobar una vez no puede ser una llave— y el mecanismo era el agujero. Se reescribió, no se borró, y el docblock cuenta por qué.

La regla nueva cumple la misma intención más estricto: sin aprobar se actualiza (un perfil pendiente no lo ve nadie más que la oficina), aprobado no se toca. Y el aviso es **idéntico** se conozca o no el correo, con su prueba: uno distinto para cada caso convertiría el formulario en un buscador de quién está inscrito en el banco.

### 47.5 Las guardias nuevas encontraron tres defectos que la auditoría no vio

Esto es lo que más rendimiento dio, y conviene repetirlo: **una guardia bien escrita encuentra lo que el lector no**.

1. **`Panel\BitacoraTest`** —la página Bitácora no tenía ni una prueba— exige que todo modelo que escribe actividad esté traducido. Destapó que **`iniciativa` nunca lo estuvo**: publicar «Vibrarte» se leía como «Natalia actualizó **un registro** Vibrarte». Defecto desde que el módulo existe.
2. **`Panel\TableroTest`**, la guardia del `columnSpan` desglosado que se escribió el 7 de septiembre, atrapó el mismo error en el widget nuevo antes de que llegara a ninguna pantalla.
3. Y el más gordo: al construir el aviso de PQR se iba a mandar una notificación de base de datos, copiando lo que hacía `FlujoDeAprobacionObserver`. Al compilar salió que **la campana del panel está apagada desde el 7 de septiembre** (D-L22, `databaseNotifications()` comentado) y que **nadie había retirado a quien escribía en ella**: consultas a todos los usuarios y filas nuevas en cada guardado de contenido, sin una sola pantalla que las leyera. Con **cuatro aserciones de `FlujoDeAprobacionTest` en verde** encima.

Ese tercero es el **falso verde número trece** del proyecto, y el primero que no lo escribió el autor de un plan sino que lo dejó una decisión aplicada a medias. Se retiró el envío, las cuatro aserciones pasan a afirmar sobre la **cola de pendientes** —que es lo que D-L22 dijo que lo sustituía y lo que de verdad se pinta— y `Panel\AvisosQueSeVenTest` vigila la pareja: o hay campana y hay quien escriba, o no hay ninguna de las dos.

### 47.6 La indexación no estaba pendiente: estaba pasando

D-08 llevaba semanas anotada como «decidir `noindex` antes del lanzamiento». Leerla así escondía lo importante. El sitio servía `Allow: /`, publicaba su sitemap y —lo que de verdad hace daño— clavaba `<link rel="canonical">` resolviendo al host temporal de Cloud. Todos los días le decía a Google que la versión autorizada de cada página del gremio vive en una dirección desechable, a una semana de que llegue el dominio propio.

Se gobierna con `SITIO_INDEXABLE`, **cerrada por defecto**: salir del índice cuesta semanas y entrar cuesta un despliegue.

Efecto colateral que hubo que atender: dos pruebas antiguas —`CalendarioDeEventosTest` y `VigenciaDeLaGuiaTest`— medían la regla `noindex` **por página** y se cayeron porque ahora el sitio entero nace cerrado. Se aislaron abriendo la llave dentro de la prueba, que es lo que de verdad querían medir.

### 47.7 Lo que pidió la dirección: «flujo de personas que entran a la página»

Chocaba de frente con el A-02 del Acta 07, aprobado el día anterior: analítica anónima y **sin visitantes únicos**, porque contar personas exige IP, cookie o sesión y la política de tratamiento sigue sin publicarse (D-19). Esa decisión no se revierte sin Sua, así que se le puso delante: entradas anónimas ampliando el Acta 07, visitantes únicos de verdad revirtiéndola, o solo desplegar lo ya construido. Eligió la primera.

**Acta 08 emitida antes de la primera línea de código**, como manda la regla 1, con A-03 (flujo de entradas) y A-04 (aviso de PQR).

La entrada se reconoce por el `Referer`: si no viene de nuestro host, la página es la primera de una visita. El encabezado **se mira y no se guarda**, que es el trato que ya recibía el navegador para descartar rastreadores. Tres piezas en el tablero: los números con la comparación contra la semana anterior —y sin inventarse un porcentaje contra cero, que es lo que pasa siempre la primera semana de una métrica nueva—, la curva de treinta días con las dos series juntas, y por dónde entra la gente al lado de qué mira una vez dentro.

Cada pieza dice que **no son personas distintas**, y hay una prueba que lo exige. Una cifra de tráfico sin esa frase se lee como visitantes únicos, que es exactamente lo que no es.

Se acepta a sabiendas que un navegador que borre la procedencia sobrecuenta un poco. La alternativa es una cookie, y la cookie es la línea que este módulo no cruza; el sesgo va hacia arriba, es pequeño y es estable, así que la comparación entre semanas —que es para lo que sirve la cifra— se sostiene igual.

### 47.8 Lo que no se pudo ver con ojos, otra vez

Se entró al panel de verdad: contraseña, código del segundo factor leído de `storage/logs/laravel.log` —el correo local va al registro— y sesión abierta en el tablero. **Y ahí se acabó.** El navegador de esta máquina no compone fotogramas con la ventana detrás, así que `IntersectionObserver` no dispara nunca y los nueve widgets diferidos se quedaron en «Cargando…». Forzar la carga a mano tampoco: sin composición, Alpine no llega a inicializar.

El sustituto honesto son pruebas de **renderizado completo** de los tres widgets, que `getData()` no da: un widget con los números correctos y un error de plantilla pasa la prueba de datos y revienta en el tablero.

Lo que sí se comprobó contra un servidor corriendo, porque el sitio público se pinta en el servidor: `noindex` en la portada servida, `Disallow: /` en el `robots.txt` servido, cinco vacantes en el sitemap, y cero cuentas para `sitemap`, `robots` y `guia.formato` en la tabla de visitas.

**Sigue abierto que Sua e Ingrid miren el panel con ojos.** Es la misma deuda del 7 y el 8 de septiembre, y ahora hay tres widgets más que nadie ha visto.

### 47.9 Cómo quedó

Cinco commits en `p1-auditoria-y-metricas`, ninguno empujado. Suite completa: **1.209 casos, 1.198 pasan, 11 omitidas, 0 fallos, 5.394 aserciones en 353 s** — de 1.147 a 1.209, sesenta y dos casos nuevos, todos vistos rojos antes y mutados después.

El manual sube a 1.3 con lo que faltaba contar, y con un aviso en rojo arriba del todo: **sus once capturas son del 18 de agosto y el panel se rehizo el 7 y el 8 de septiembre**. Capacitar en la semana 8 sobre ese manual garantizaba que la secretaría no supiera que hay que aprobar los perfiles del banco, y que buscara en pantalla cosas que ya no están.

### 47.10 Post scriptum: el Scheduler estaba apagado, y ahora se sabe con un número

Unas horas después del cierre, Sua preguntó dónde se activaba. La ruta que esta misma sesión había escrito en el runbook —«Environment → Resources»— **estaba mal**: esa pestaña no existe en Laravel Cloud. Se salió de la duda con el CLI en vez de con más memoria:

```
cloud instance:list --json  →  "usesScheduler": false
```

**No es un recurso del entorno: es una propiedad de la instancia**, la tarjeta *App cluster* del diagrama. Y ese `false` convierte el hallazgo de la mañana en un hecho medido: las tres purgas de datos personales **no habían corrido una sola vez** desde el primer despliegue del 28 de agosto, mientras `/politica-de-datos` le prometía al titular que el borrado era automático.

Se encendió con permiso de Sua, y en el orden que el propio runbook pedía: **primero los tres simulacros** —`0`, `0` y `0`, porque el sitio lleva menos de un mes y ningún plazo ha vencido—, después `instance:update App --uses-scheduler=true`, y por último `schedule:list` contra producción, que devolvió las tres tareas con su `Next Due`.

Dos lecciones, y la segunda es la que vale:

1. **`cloud command:run` quiere el comando en `--cmd`, no como argumento suelto.** La forma que el runbook llevaba escrita responde `{"error":true,"message":"cmd is required"}`. Se descubrió usándolo, que es la única manera de descubrir eso.
2. **Un runbook que nadie ha ejecutado es una hipótesis.** Las dos correcciones de hoy —la ruta del panel y la firma del comando— llevaban ahí desde que se escribieron, con toda la confianza del mundo y sin que nadie las hubiera pasado por una terminal. La regla del proyecto de no citar cifras sin medirlas el mismo día vale igual para los procedimientos: **un paso que no se ha corrido no está verificado, por bien redactado que esté.**

### 47.11 Desplegado: dieciocho commits de una vez, y la analítica midiéndose a sí misma

Sua dijo «despliega la rama», y la rama arrastraba más de lo que sonaba: `origin/main` llevaba desde el 8 de septiembre en `0594058`, así que empujar no publicaba ocho commits sino **dieciocho** —`p1-cierre-bolsas` entera, que se había publicado *para que Ingrid la revisara*, más la auditoría del 9—. Ochenta archivos, cuatro migraciones. Eso se dijo antes de empujar, no después.

Suite completa como última puerta —1.209 casos, 1.198 pasan, 0 fallos— y `git push origin main`. El despliegue tardó **1 min 13 s** y las cuatro migraciones entraron en el lote 2.

**La verificación fue por contenido servido, no por el mensaje de éxito**, que es la regla de la casa desde §29: `Disallow: /` en el `robots.txt` real, `noindex, nofollow` en la portada real, siete rutas públicas en 200.

Y entonces la analítica se midió a sí misma, que es la parte que vale la pena contar. Primer intento: `visitas=0` después de siete peticiones. No era un fallo — **el agente de usuario de `curl` está en la lista de rastreadores del propio middleware**, así que se descartó solo. Repetido con un agente de navegador y tres peticiones deliberadas, producción devolvió:

```
3 páginas servidas / 2 entradas
```

La portada sin procedencia contó llegada. La guía con `Referer` de Google contó llegada. `/empleo` con procedencia nuestra contó página **y no** llegada. `sitemap.xml` y `robots.txt` no contaron nada. Es la definición entera del módulo, comprobada en el sitio de verdad con tráfico de verdad, unas horas después de escribirla.

**Lo que queda dicho y no hecho:** el sembrador de contenido oficial no corre en el despliegue, así que las dos claves jubiladas hoy —`hero_subtitulo` y `cifra_afiliados`— **le siguen apareciendo a la oficina en el panel**. La limpieza existe y vive en `SettingSeeder`; hace falta correrlo una vez, y eso toca datos, así que pide visto bueno aparte.

## §48 — La capa visual: gesto, vidrio y sitio para las fotos (9 de septiembre de 2026)

Sua pidió potenciar el diseño «a un 300 %», y con una condición que cambia el trabajo entero: **no cambiar el estilo, sino potenciar el que ya hay.** Lo que había que darle a los botones, barras y paneles era *vida* — el `liquidglass` de iOS 26, donde lo que predomina no es la apariencia sino la sensación de que el elemento reacciona al movimiento, «que el botón asemeja el movimiento de una gota de agua sobre un cristal». Más el encargo de imaginar dónde irán las fotos del gremio cuando lleguen, dejando el hueco marcado. Y el móvil primero, porque la mayoría va a conocer el sitio por ahí.

Salieron siete commits en la rama `diseno/movimiento`, sobre `cierre/sua`: `0eacd68`, `30dec30`, `b52d292`, `42fc01a`, `ee01f64`, `311fdf2` y `86dcdb9`, más `b074c44` de cierre.

### 48.1 Qué se construyó

**Un motor de resortes propio** (`resources/js/movimiento.js`, 302 líneas, **sin una sola dependencia nueva**). Amortiguador con la parametrización de Apple —respuesta y razón de amortiguación en vez de masa, rigidez y rozamiento—, proyección de momento, goma en los bordes y un solo bucle de `requestAnimationFrame` que **se apaga cuando no queda nada en vuelo**, que es lo que separa un motor de un consumo de batería. Integra a paso fijo de 4 ms con tope de 50 ms por fotograma, para que una pestaña que vuelve del fondo no dispare el resorte al infinito. Se verifica con un script de Node que importa el módulo de verdad y comprueba 25 propiedades numéricas; si Node no está, la prueba se omite en vez de mentir.

**La hoja del teléfono se cierra con el dedo.** Arrastre 1:1 con histéresis de 10 px, captura del puntero, y la proyección decide si se cierra o vuelve.

**El botón se vidria al pulsarlo.** Sua corrigió la propuesta original —yo había planteado un destello— por algo mejor: que al presionarlo se vuelva transparente y deje ver lo que hay detrás. Dos capas, `backdrop-filter` de 0 a 14 px, 90 ms de ida y 280 de vuelta.

**La pestaña activa lleva una gota que viaja** entre pestañas con una transición de vista nombrada.

**Los huecos de fotografía.** Mientras no hay foto pintan el marcador de marca; cuando la haya, basta con guardar la ruta en un ajuste. Abiertos en Guía, Empleo, Artistas y Proveedores; el Directorio se dejó fuera por ser de Ingrid.

**Y dos incumplimientos de RNF-12 que aparecieron midiendo, no buscando:** un rótulo tenue en oscuro daba 4,32:1 y el botón de acción con texto blanco daba 3,86:1.

Medido sobre la rama: **1.324 casos · 1.310 pasan · 14 omitidas · 0 fallos · 6.035 aserciones**, Pint limpio, **veintidós mutaciones comprobadas en rojo**.

### 48.2 Las cuatro lecciones, que son más caras que el código

**1. El minificador pliega `color-mix()` cuando el porcentaje lleva `calc()` dentro.** El fuente mezclaba dos colores con un porcentaje calculado; en `public/build/assets/app-*.css` salía el primer color a secas, sin mezcla. Efecto: el botón pintado siempre con la tinta del estado pulsado, rojo oscuro sobre rojo, **1,89:1**. Ni Vite ni Tailwind avisan, y **la prueba que leía el fuente pasaba en verde**. La salida no fue pelearse con el minificador sino no necesitar interpolar colores: dos capas y un `background-color` que transiciona entre dos valores literales. Desde aquí, **el CSS que se comprueba es el construido**.

**2. La constante de Apple es para el scroll, no para una hoja de 155 px.** La proyección de momento con `0.998` convertía un arrastre suave de 40 px (unos 143 px/s) en 71 px proyectados, y la hoja se cerraba sola. Con `0.99` el mismo gesto proyecta 14 px y se queda abierta, mientras que un golpe real de 45 px en 34 ms —1.351 px/s medidos— proyecta 134 y cierra. La fórmula era correcta; el parámetro estaba copiado de un contexto que mide miles de píxeles.

**3. El decimocuarto falso verde del proyecto, y otra vez mío.** Una guardia que buscaba una llamada dentro de un método con una expresión regular se escapaba del método y la encontraba en otro sitio: pasaba en verde con el código mutado. Se sustituyó por un ayudante que cuenta llaves y acota el cuerpo de verdad. **Ninguna expresión regular delimita un bloque de código.**

**4. Prohibir la palabra no es prohibir la sintaxis — tres veces seguidas.** Tres guardias que vetaban un identificador saltaban al encontrarlo **en un comentario que explicaba por qué no se usa**. Las tres se reescribieron para afirmar sobre la forma sintáctica en vez de sobre el texto.

### 48.3 Lo que quedó abierto

**Ni el arrastre ni el vidriado se han visto con dedo**: solo con puntero sintético y medidos por geometría, porque el navegador de esta máquina no compone fotogramas con la ventana detrás. Los números que se ajustan cuando alguien lo toque son la deceleración de la hoja y las dos duraciones del vidriado. Quedan además **D-46** —Ingrid tiene que aprobar los dos tokens compartidos, que cambian cómo se ven cuatro módulos suyos— y **D-47**: el §9 del encargo exige registrar por escrito toda ampliación **antes** de codificarla, y esta se codificó el mismo día que se pidió. El Acta 09 está debida.

## §49 — El material del gremio empieza a entrar al sitio (10 de septiembre de 2026)

La sesión anterior tradujo la segunda entrega del gremio a instrucciones (`encargo.md` §17). Esta ejecuta la parte que no dependía de nadie, y se estrella con la que sí.

### 49.1 Cuatro textos que dejaron de ser nuestros

**El presidente tenía los apellidos al revés.** El sitio decía «Jorge Iván Botero Ángel». La invitación a los ponentes del foro nocturno de noviembre de 2025 **la firma él mismo**: «Jorge Iván Ángel Botero · Presidente Asobares Quindío». Era el error de contenido más visible de la plataforma —el nombre de una persona real en una página pública con el nombre del gremio encima— y llevaba semanas ahí.

**El lema.** `sitio_eslogan` decía «La noche construye territorio», de cosecha propia, y se ve en el pie de todas las páginas, en el título de la portada y sobre el hero de «Quiénes somos»: era el texto inventado más repetido del sitio. El del gremio cierra la última lámina de la presentación institucional: **«Construyendo un Quindío nocturno»**. Un detalle lo confirma: esa frase **ya estaba en el sitio**, sembrada como firma del cierre del manifiesto. El lema real llevaba semanas conviviendo con el inventado sin que nadie los cruzara.

**La propuesta de valor**, de la lámina 2, sustituye a la misión que redactó este equipo. Es el subtítulo del hero de «Quiénes somos»: lo primero que lee quien entra a saber qué es esto.

**Y el respaldo nacional deja de ser una afirmación sin tamaño.** El bloque decía «Somos el capítulo regional de Asobares Colombia» sin enseñar de qué tamaño es ese respaldo; la lámina 3 trae **17 capítulos y 2.500 afiliados**. Entran con la forma de la franja de la portada —cifra y rótulo por separado, editables, y la que se deje en blanco no se pinta—. Los rótulos dicen «en el país» **a propósito**: `cifra_afiliados` se jubiló el 9 de septiembre justo por publicar un número de afiliados que ningún documento sostenía (D-18), y sin esa palabra el mismo defecto volvía por la puerta de al lado con una cifra cuatro veces mayor. Hay una guardia que lo comprueba.

Cuatro guardias nuevas en `SemillaInstitucionalTest`, cuatro claves nuevas en la de «Quiénes somos» y una que vigila que una cifra vacía no deje su rótulo flotando solo. **Ocho mutaciones, ocho rojas.** Contraste medido en los dos temas: los números 6,52:1 en claro y 6,62 en oscuro (exigido 3), los rótulos de 11 px 11,37 y 7,62 (exigido 4,5).

### 49.2 Los 18 aliados del Quindío no entran, y el motivo no es el que parecía

`encargo.md` §17.1 dice que la franja de aliados no tiene un solo aliado del Quindío y que las láminas 15–16 traen 18 departamentales. Es el hallazgo de contenido más gordo de la entrega: un gremio departamental que solo enseña aliados nacionales se lee como sucursal. Y aun así no se sembró ninguno, por dos motivos independientes:

1. **No pude leer los nombres.** En esas dos láminas la capa de texto solo trae el título: los 18 nombres son **logos**. Esta máquina tiene `pdftotext` pero no con qué rasterizar el PDF; el visor del navegador lo incrusta en un marco que no compone; el visor de PDF de las herramientas no tiene directorios permitidos; y de los diez JPEG que sí se pueden extraer del archivo a mano, ninguno es la lámina de logos. La lista existe escrita en el §17.1, pero **escrita por otra sesión, y copiarla sería citar un resumen como si fuera el documento**.
2. **Y aunque los tuviera, faltaría lo esencial.** El tipo comercial exige `detalle_convenio` —lo que el afiliado ve cuando inicia sesión— y hay una guardia que lo comprueba desde agosto. La presentación trae logos, **no condiciones**. Sembrarlos sin convenio rompe la guardia; inventarles el convenio es exactamente lo que el §10 prohíbe.

Lo que sí cabría hoy son las tres entidades públicas (Alcaldía de Armenia, Comfenalco Quindío, EDEQ) como **institucionales**, que no llevan convenio. Se dejan sin sembrar por el motivo 1: son entidades públicas, y afirmar una alianza que no he podido verificar en el documento es peor que no afirmarla.

**Lo que hace falta para desbloquearlo** cabe en una frase: que alguien abra esas dos láminas y escriba los 18 nombres, y que el gremio diga qué le da cada uno al afiliado. Sin lo segundo, los quince comerciales no pueden entrar por diseño del propio esquema.

### 49.3 La lección

**Verificar contra el documento, no contra el resumen del documento.** Los cuatro textos que entraron se leyeron del PDF y del `.docx` originales, palabra por palabra; el que no se pudo leer, no entró. La diferencia entre las dos mitades de esta sesión es exactamente esa, y es la regla del §4.2 del prompt maestro —ninguna cifra sale de una suma— aplicada a texto en vez de a números.

## §50 — La gota vista en un teléfono de verdad (10 de septiembre de 2026)

Primera pasada de la S7 sobre la capa visual: Sua abrió el sitio en un Android contra el servidor de la LAN y mandó la captura. La barra se pinta bien y el estado activo se entiende. Salió **una** cosa, y es de las que ninguna medición iba a dar.

**La gota llevaba un contorno de 1 px**, `rgb(238 65 55 / 0.3)`, encima de un relleno al 14 %. En el navegador de escritorio y en la maqueta eso pasaba por una píldora discreta. En la mano no: se lee como **un aro rojo dibujado alrededor del icono**, que es exactamente lo contrario de lo que se pidió — «que el botón asemeje el movimiento de una gota de agua sobre un cristal». Una gota sobre un cristal no tiene línea: tiene un borde que se apaga.

Se le quitó el trazo. El relleno sube del 14 al 20 % para no perder el «estás aquí» que daba el contorno, y el salto al fondo de la barra lo amortigua un halo de 5 px al 6 %: dos escalones de opacidad en vez de una línea. Medido después en los dos temas, con la caja intacta —40×28, el icono dentro con 2 px de holgura arriba y 8 a los lados— así que la corrección del 9 de septiembre sigue en pie.

**La guardia tiene truco, y por cuarta vez el mismo.** Prohibir la palabra «border» en la regla la habría disparado el propio comentario de `app.css`, que cita el contorno viejo para explicar por qué se fue. La guardia mira la **declaración** —`border`, `border-width`, `border-style`… seguido de dos puntos— y deja pasar `border-radius`, que sí hace falta. Comprobada con tres mutaciones: devolver el aro, roja; quitar el halo, roja; `border-radius` intacto, verde.

**La lección, que es de método:** el catálogo de trampas de este proyecto se llenó de cosas que el navegador miente. Esta es la otra mitad — **una decisión de diseño que ninguna medición podía tomar**. El contraste pasaba, la geometría pasaba, las mutaciones pasaban, y aun así estaba mal. Lo único que lo dijo fue una pantalla de seis pulgadas en una mano. Quedan dos superficies en esa misma situación y **más peligrosas, porque son gesto y no dibujo**: la hoja arrastrable y el vidriado del botón, que a día de hoy no ha tocado nadie.

## §51 — Las dos tareas «que no dependían de nadie» no eran tareas (10 de septiembre de 2026)

Quedaban dos filas del §17 marcadas como trabajo puro de código, sin bloqueos: sustituir el logo y sustituir el video del hero. De una de ellas el propio §17 decía que era «la única tarea de toda la entrega que no depende de nadie». **Las dos estaban equivocadas**, y la forma de averiguarlo fue la misma en los dos casos: abrir el archivo en vez de leer su nombre.

### 51.1 El video: no se sustituye, y no es discutible

`Video de Asobares Capítulo Quindio.mp4` sale de la carpeta de proyectos y dura 24,8 s, así que sobre el papel encajaba. Extraídos seis fotogramas, es **una persona hablando a cámara mientras camina por un centro comercial**: contenido de redes sociales. Y `ffprobe` lo remata — `rotation=-90`: es **vertical**, guardado como 1280×720.

El hero no es un reproductor: es un fondo **mudo y en bucle** detrás del titular, forzado a `muted` por el propio JavaScript. Meter ahí ese archivo es poner a alguien moviendo la boca en silencio detrás de la portada del gremio. Y además expone a una persona identificable, cuando D-03 —autorizaciones de imagen— lleva desde el 26 de agosto sin respuesta.

Los otros dos videos de la entrega caen por lo mismo: `Asobares Gestión 2025.mp4` es vertical y pesa 79 MB, y `video.mp4` es un tutorial **de la Nacional**, con su marca de agua, sobre la biblioteca virtual.

El bucle que hay hoy, en cambio, es exactamente lo que un fondo debe ser: cócteles sirviéndose, cocteleras, brindis, con la marca del capítulo. Y está bien montado — medida la luminancia media, **funde a negro por los dos extremos** (16→39 al entrar, 42→18 al salir), así que el bucle no parpadea. Fui a comprobar un defecto que sospechaba y no existía.

### 51.2 El logo: ya estaba puesto, y el PNG es una decisión, no una dejadez

El §17 decía que el sitio servía «`material/logo asobares.svg`, un archivo suelto». No lo sirve: sirve `public/img/logo-asobares.png`. Comparado con el del kit oficial a la misma escala, **es el mismo dibujo** —mismo isotipo, mismo logotipo, misma línea «CAPÍTULO QUINDÍO»—; lo que cambia es la nitidez, porque el del sitio son 592×108 y el oficial 3128×572.

Y el PNG no es una degradación: `logo.blade.php` ya deja escrito que aquel `.svg` **nunca fue un vector** —era este mismo PNG en base64 dentro de un `<svg><image>`, un 34 % más de bytes— y que se cambió midiendo, porque el logo tiene que estar en el primer pintado y antes se veía desaparecer y volver en cada navegación. Medido hoy: la instancia mayor se pinta a 219 px, así que a 3x pide 658 y hay 592. Un 11 % de estiramiento en un solo sitio: no justifica tocar algo que se afinó midiendo.

### 51.3 Lo que sí faltaba de esa fila, y no lo decía

La fila del logo listaba cuatro destinos: cabecera, pie, favicon y **imagen al compartir**. Los tres primeros estaban. El cuarto **no existía**: `ogImagen` nacía en `null` y solo lo pasaban cuatro vistas —artista, noticia, ficha de asociado y evento—, así que la portada, «Quiénes somos», la guía, el directorio y contacto se compartían **sin miniatura**.

Para este gremio eso no es posicionamiento: su canal es WhatsApp, y un enlace sin imagen es el primer contacto de mucha gente con el sitio. Ya hay tarjeta —logotipo blanco del kit, sin recolorear, sobre el fondo del sitio, 1200×630, 22 KB— y se declara siempre. **Las medidas solo se juran sobre la tarjeta nuestra**: hacerlo sobre la foto de un artista es peor que callarse, porque el desplegador recorta contra un tamaño que no existe.

El defecto no lo veía nadie porque **el `<meta>` existía y estaba bien escrito**: lo que casi nunca se cumplía era su `@if`. Una prueba que mirase la plantilla lo habría dado por bueno; la guardia nueva recorre cinco páginas servidas y lee el HTML. Y una de sus tres aserciones se puso roja sola en la primera pasada, por no haber añadido el archivo al índice de git — que es **exactamente** la avería que enseñó el video del hero, cazada esta vez antes de producción.

### 51.4 La lección

**Una tabla de instrucciones escrita desde un listado de archivos no es lo mismo que una escrita desde los archivos.** El §17 es un trabajo bueno y útil, y aun así dos de sus filas mandaban hacer algo incorrecto: una porque no miró dentro del video, otra porque no comprobó qué archivo sirve el sitio. Es la misma regla del §49.3 —verificar contra el documento y no contra el resumen— aplicada al propio expediente. El expediente también es un resumen.

## §52 — La consolidación y el despliegue de los 45 commits (10 de septiembre de 2026)

Ingrid pidió parar el desarrollo y hacer una revisión manual completa sobre una rama consolidada, `cierre/ingrid @ 46efdc0`. De ahí salieron tres cosas: lo que la revisión encontró, lo que la revisión **no** pudo encontrar, y el despliegue.

### 52.1 Lo primero que apareció no era una pantalla: era una cifra

La consolidación se validó con «425 tests, 0 fallos». El árbol tiene **1.062 métodos de prueba en 106 archivos**, y PHPUnit reporta **1.350 casos** contando proveedores de datos. Los 425 cubren menos de un tercio. La fusión estaba sana —la corrí entera: 1.350 casos, 0 fallos—, pero la cifra con la que se dio por buena no era la suite, y este proyecto tiene una regla escrita sobre eso.

### 52.2 El hallazgo que solo aparece tropezando

`/admin/solicitudes-afiliacion` devolvió **403** siendo `super_admin`. Del lado del servidor: `shouldRegisterNavigation` decía **sí** y `canViewAny` decía **no**. El módulo se anuncia en el menú y no deja entrar.

La policy estaba bien escrita. Lo que faltaba eran los **ocho permisos nuevos** que la rama añade —tres de solicitud de afiliación y cinco de publicidad—, que solo nacen en `RolYPermisoSeeder`. La base local tenía 80; al correr el sembrador pasó a 88 y las dos pantallas abrieron sin tocar una línea.

**Y ese sembrador no corre en el despliegue.** Vive dentro de `ContenidoOficialSeeder`, que ningún guion invoca. Es exactamente el patrón que destapó la auditoría del 9 de septiembre —una mitad construida y la pieza de al lado sin conectar—, esta vez con dos módulos enteros del panel detrás.

### 52.3 Lo que la revisión no pudo encontrar, y por qué importa decirlo

**Ningún hallazgo de contraste sobrevivió a la verificación, y no reporté ninguno.** Este entorno no compone fotogramas: el `IntersectionObserver` no dispara —nueve secciones de la portada se quedan en `opacity: 0`— y alternar el tema por clase sin recargar da lecturas fantasma. Con esas dos trampas llegué a *confirmarle a Sua* que las tarjetas del Directorio incumplían RNF-12 con 2,91:1. Al recargar de verdad en oscuro, el mismo elemento da **6,62:1**. Me retracté en el momento.

Dos veces en la misma sesión este entorno fabricó un defecto que no existía; la otra fue un «500» del observatorio que venía del búfer viejo de la consola. La lección no es nueva pero se pagó otra vez: **una medición que no sobrevive a una recarga real no es una medición.**

Lo que sí quedó, medido y repetible: la barra de escritorio **se superpone 151 px a 1024** (171 con sesión) y deja «El gremio» y «Mi cuenta» ilegibles uno encima del otro —es D-33, que el expediente describía como desbordamiento cuando es solape—; «Ver ficha» y «WhatsApp» de las tarjetas nuevas se quedan en 22 y 33 px de área táctil; la hamburguesa del panel móvil en 36; y el `h1` de la portada sigue siendo la frase que inventó este equipo mientras el pie ya lleva la del gremio.

### 52.4 La fusión, y lo que no hubo que arbitrar

`cierre/ingrid` ya contenía casi todo. Faltaban tres commits de `diseno/movimiento` —la imagen al compartir, las correcciones del §17 y las cifras remedidas—. El ensayo en seco y la fusión real dieron **cero conflictos**: ninguna línea en disputa entre las dos mitades.

Sobre la fusión: **1.353 casos · 1.339 pasan · 14 omitidas · 0 fallos · 6.208 aserciones**. Pint limpio, `git diff --check` limpio.

### 52.5 El despliegue

`main` avanzó de `adfcd97` a `5a4958b` por avance rápido: **45 commits y tres migraciones**. Producción sirvió el build nuevo unos **60 segundos** después del push. Comprobado por contenido servido: ocho rutas públicas en 200 entre 0,86 y 1,25 s, la tarjeta de compartir en 200 con sus 22.357 bytes, `Disallow: /` intacto y el formulario de afiliación con el cargo del solicitante.

**Y con el sembrador sin correr, que es lo que hay que saber:** el presidente sigue con los apellidos al revés, el respaldo nacional no se pinta, el título de la portada sigue diciendo «La noche construye territorio», y los dos módulos nuevos del panel devuelven «Forbidden». **El código está desplegado; el contenido, no.** Correrlo tiene un coste que se decide antes y no después: `SettingSeeder` usa `updateOrCreate` y sobrescribe cualquier ajuste que la oficina haya editado desde el 3 de septiembre, sin forma de saber cuáles. Es la D-14 cobrando por primera vez.
