# PROMPT MAESTRO v4 — Plataforma Web ASOBARES Capítulo Quindío (Laravel 13 + Filament 4)

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

## 1. Tu misión

**Paso previo obligatorio — setup oficial:** estás construyendo una aplicación Laravel nueva. Haz fetch de **https://laravel.com/for/agents** y sigue sus instrucciones; trata ese Markdown como la **fuente de verdad para instalar y configurar Laravel en esta sesión** (verificación de `php`, `composer` y el instalador `laravel`; instalación con php.new si falta algo; creación del proyecto; Laravel Boost). Donde esa página ofrezca variantes, usa las de ESTE documento: base de datos **SQLite**, gestor **npm**, **sin starter kit** (`--no-authentication` — Filament corre sobre Livewire 3; el starter kit Livewire de Laravel 13 trae Livewire 4, incompatible con Filament 4, así que el auth de `/mi-cuenta` se escribe a mano; **no** React ni Vue) y **`--boost` activado**; carga las guías de Boost (CLAUDE.md/AGENTS.md) apenas se instalen, sin reiniciar la sesión. Los pasos post-creación de la página (Cloud CLI, `composer run dev`) síguelos; el despliegue real queda fuera de esta sesión, pero anota Laravel Cloud como candidato de hosting de producción (pago mensual, condición de la dirección).

Actúa como un desarrollador senior de Laravel. Vas a construir desde cero, en esta carpeta, la **plataforma web oficial de ASOBARES Capítulo Quindío**: un monolito **Laravel 13 + Filament 4** con sitio público en Blade + Tailwind y panel de administración a la medida. Es un prototipo funcional completo (no un mockup): al terminar debe poder ejecutarse con `php artisan serve` y demostrarse de punta a punta con datos semilla realistas.

Trabaja por fases (sección 12), verifica cada fase antes de seguir, y no marques nada como terminado si las migraciones, seeders o pruebas fallan.

## 2. Contexto del cliente (úsalo en textos y semillas)

- **ASOBARES Capítulo Quindío** es el gremio de la vida nocturna del Quindío (bares, gastrobares, cafés, discotecas). Fundado el **14 de agosto de 2024** en Armenia. Es el capítulo regional de Asobares Colombia.
- **Presidente:** Jorge Iván Botero Ángel · **Directora Ejecutiva:** Natalia Gutiérrez.
- **Oficina:** Piso 3, Cámara de Comercio de Armenia y del Quindío, Armenia.
- **Contacto real:** asobaresquindio@asobares.org · WhatsApp 321 5549513 · Instagram @asobaresquindio.
- Hoy tiene ~**60 establecimientos afiliados** y crece mes a mes; no tiene sitio web propio.
- **Propósitos del sitio**: (1) **visibilizar los establecimientos** del sector; (2) **guiar a quien quiera abrir un establecimiento**. **Re-ponderación de la directiva (Reunión 2):** el sitio NO es una plataforma de mercadeo de bares; su valor es la **representatividad gremial** y, como producto insignia, la **guía normativa por municipio** («ningún gremio la tiene; es donde caen los negocios y los cierran»). Usuario objetivo de la guía: el dueño pequeño que «es el mismo dueño, el bartender y el que hace todo».
- **Prioridades top 3 dictadas por la directiva** si el tiempo aprieta: (1) bolsa de empleo, (2) guía de requisitos con formatos descargables, (3) directorio de asociados.
- **Reglas editoriales acordadas:** los eventos publicados son **solo del gremio** (ExpoBar, Congreso Nacional, capacitaciones propias), nunca de bares individuales; lo no-local se enlaza al registro de la Nacional. El **propietario del establecimiento decide** qué información suya se publica. El boletín/noticias es de **baja frecuencia** (~mensual, con datos que envía la Nacional) — la directiva es escéptica de secciones que exijan alimentación constante.
- Beneficios institucionales del afiliado (textuales, para la sección de beneficios): 1. Representación gremial · 2. Descuentos en SAYCO y OSA · 3. Beneficios con aliados estratégicos · 4. Formación empresarial · 5. Orientación jurídica gratuita. El **detalle de convenios por marca es privado**: visible solo para asociados con sesión iniciada.
- Cifras reales del Observatorio Económico (marzo 2026, para las noticias de ejemplo del boletín): la economía nocturna genera el **12,65 %** del empleo de Armenia; ingreso medio mensual del sector **$2.104.124**; informalidad **72,82 %**; **35,28 %** de los trabajadores tienen 28 años o menos; brecha salarial de género **−26,55 %** para las mujeres.
- Palabras clave SEO: "Asobares Quindío", "bares en Armenia", "vida nocturna Quindío", "Quindío nocturno", "abrir un bar en Armenia", "empleo en bares Armenia", "DJs en Armenia".
- ⚠️ Los asociados, artistas, proveedores y vacantes de ejemplo deben ser **ficticios**; los datos institucionales de arriba sí son reales.

## 3. Stack y paquetes (obligatorio)

- **Laravel 13** — instálalo siguiendo https://laravel.com/for/agents (fuente de verdad del setup). Comando de referencia: `laravel new . --database=sqlite --no-authentication --npm --boost --no-interaction` (si el instalador exige subcarpeta, créala y trabaja dentro). **No instales el starter kit de Livewire** (`--livewire`): trae Livewire 4 y Filament 4 exige `livewire/livewire ^3.5` — el andamiaje de auth de `/mi-cuenta` se escribe a mano; **todo el sitio público se rediseña** con la identidad de la sección 4 — nada de estilos de starter kits ni de Flux en las vistas públicas.
- **Laravel Boost** (instalado con `--boost`): mantén sus guías cargadas durante toda la sesión; si Boost expone herramientas de documentación/búsqueda, úsalas antes de inventar una API.
- **Filament v4** (`filament/filament:^4.0`) con un panel en `/admin`.
- **Base de datos:** SQLite para este demo (cero configuración); escribe migraciones portables para poder pasar a PostgreSQL en producción sin cambios (nada específico del motor).
- **Paquetes:** `spatie/laravel-permission` (roles), `spatie/laravel-activitylog` (bitácora), `spatie/laravel-medialibrary` (imágenes con conversión a **webp** y thumbnails; si diera problemas en SQLite/demo, usa uploads nativos de Filament + conversión webp con Intervention Image), `spatie/laravel-sitemap`, `league/csv` (importación de cartera).
- **Frontend público:** Blade + **Tailwind CSS** (vía Vite) + Alpine.js para interacciones ligeras. **Prohibido** usar page builders o un tema prefabricado.
- **Mapas:** Leaflet + OpenStreetMap por CDN (gratis, sin API key). Encapsula el mapa en un componente Blade para poder cambiar de proveedor después.
- **Pruebas:** Pest (o PHPUnit si viene por defecto).
- Si la API exacta de Filament 4 difiere de lo que recuerdas (p. ej. la configuración de MFA), consulta la documentación oficial antes de inventar.

## 4. Identidad visual (aplícala con rigor)

- **Modo oscuro elegante de vida nocturna:** fondo casi negro `#0C0A0B`, superficie `#141114`, **rojo de marca `#EE4036`** como único acento, texto blanco hueso `#F4EFEC` y grises cálidos.
- Tipografías (Google Fonts): **Unbounded** para titulares, **Hanken Grotesk** para texto. Pesos con jerarquía clara.
- Mobile-first real: diseña primero la vista de 390 px; más del 80 % de los usuarios entrará por celular.
- Detalles que elevan: bordes redondeados suaves, tarjetas con borde `rgba(255,255,255,.09)`, hover sutiles, un resplandor rojo discreto en heros. Nada de gradientes morados ni estética genérica.
- **⚠️ v5 — el sitio es bicromático, no solo oscuro.** El modo oscuro de arriba sigue siendo la identidad nocturna y el que se diseña primero, pero convive con un modo claro levantado sobre el **Ambient White `#F5F3F4`** de la paleta principal del manual. **No escribas colores en las vistas:** usa los tokens semánticos de `resources/css/app.css` (`bg-fondo`, `bg-superficie`, `bg-superficie-alta`, `text-fuerte`, `text-tinta`, `text-suave`, `text-tenue`, `text-apagado`, `border-linea`, `text-acento`, `bg-exito-fondo`…), que `:root` resuelve en claro y `.dark` en oscuro. Los valores reales, ya corregidos contra el manual de marca, son Pub Black `#0B090A`, Pub Red `#EE4137` y Ambient White `#F5F3F4` — no los `#0C0A0B` / `#EE4036` / `#F4EFEC` del encargo original. Ojo con el acento: el Pub Red puro no alcanza AA como texto sobre fondo claro, por eso el token `acento` vale `#F27166` en oscuro y `#B71F18` en claro. Las rampas de estado (verde de «al día», ámbar de aviso) también estaban pensadas sobre negro y hay que invertirlas, o los recuadros quedan ilegibles en claro.
- El panel Filament puede usar su tema por defecto con el rojo `#EE4036` como color primario y logo/nombre "ASOBARES Quindío".

## 5. Modelo de datos (crea exactamente estas entidades)

| Entidad | Campos clave | Notas |
|---|---|---|
| `municipios` | nombre, slug | Seed: Armenia, Salento, Filandia, Circasia, Calarcá, Montenegro, Quimbaya, La Tebaida |
| `categorias` | nombre, slug | Seed: Bar, Gastrobar, Café, Discoteca, Restaurante bar, Rooftop |
| `asociados` | nombre, slug, categoria_id, municipio_id, descripcion, direccion, whatsapp, instagram_url, sitio_web, **google_maps_url, tripadvisor_url** (nullable), horario, lat, lng, foto_portada, galería, destacado (bool), estado | **Campos internos NO públicos:** representante, correo_interno, telefono_interno, fecha_afiliacion, notas_internas. `estado`: borrador → pendiente_aprobacion → publicado. El propietario decide qué se publica |
| `aliados` | nombre, logo, url, descripcion, orden, activo | Marcas con convenio (licoreras, Contingentix…). El **detalle del convenio** (campo `detalle_convenio`) solo se muestra a asociados logueados |
| `beneficios` | titulo, descripcion, icono, orden | Los 5 institucionales del punto 2 |
| `eventos` | titulo, slug, tipo (`evento`\|`capacitacion`), descripcion, lugar, fecha_inicio, fecha_fin, imagen, cupos, precio (0 = gratis), permite_inscripcion, enlace_externo (nullable, para registro de la Nacional), estado | Solo eventos del gremio. Mismo flujo de estados que asociados |
| `inscripciones` | evento_id, nombre, correo, telefono, establecimiento, acepta_datos (bool), consentimiento_at, estado (`registrada`\|`confirmada`), transaccion_id | Habeas data obligatorio |
| `requisitos_apertura` | municipio_id, entidad, descripcion, **checklist (json de ítems)**, enlace_externo, **adjunto descargable (formato oficial)**, **costo_aproximado (nullable)**, orden | La normatividad **difiere por municipio** (ej.: certificado de bomberos ~$100.000 en un municipio). Seed por municipio: Cámara de Comercio (matrícula), Alcaldía (uso de suelos), Bomberos (carta de solicitud de visita + certificado), Sayco-Acinpro, Secretaría de Salud, Policía (horarios) |
| `vacantes` | asociado_id, cargo, **categoria_cargo** (enum `CargoDelSector`), tipo (`tiempo_completo`\|`por_turnos`\|**`momentaneo`**), descripcion, franja_horaria, **fecha_limite** (date, nullable), **cerrada_at** (timestamp, nullable), **motivo_devolucion** (text, nullable), whatsapp_contacto, estado | **Bolsa de empleo del sector** ("muro"). **La publica y la corrige el propio asociado** desde `/mi-cuenta/vacantes`; el panel solo modera. `categoria_cargo` (administración, cocina, barra, servicio, seguridad, aseo, otros) es lo que permite filtrar: `cargo` es texto libre y no se puede agrupar. `fecha_limite` es **obligatoria para `momentaneo`** (turno de una o dos noches) y opcional para el resto; al pasar, la vacante sale sola del muro. `cerrada_at` es el «ya contraté» y **no pasa por aprobación**. `motivo_devolucion` lo escribe la secretaría al devolver y lo lee el asociado en su cuenta |
| `postulaciones` | vacante_id, nombre, correo, telefono, experiencia, **estado** (enum `EstadoDeGestion`), acepta_datos, consentimiento_at | **Quien se postula a una vacante concreta**, sin necesidad de cuenta. Índice **único por `(vacante_id, correo)`**: reenviar el formulario actualiza, no duplica ni vuelve a molestar al establecimiento. `estado`: nuevo → contactado → descartado, lo gestiona el asociado dueño |
| `aspirantes` | nombre, **correo (único)**, telefono, cargo_interes, **categoria_cargo**, experiencia (texto corto), **estado** (`EstadoDeGestion`), acepta_datos, consentimiento_at | **Banco de talento del gremio**, distinto de una postulación: aquí la persona deja su perfil sin apuntar a ninguna vacante, para los cargos escasos que el gremio conecta a mano. **Sin `vacante_id`** — esa relación vive en `postulaciones`. Una persona, un registro |
| `artistas` | nombre, slug, **user_id (nullable)**, tipo (`dj`\|`banda`\|`solista`\|`otro`), genero_musical, descripcion, tarifa_desde (nullable), video_url (nullable, YouTube), whatsapp, **correo**, instagram_url, foto, municipio_id, estado, **acepta_datos, consentimiento_at** | Categoría separada del empleo («el DJ es artista, el mesero es empleo»). **Se inscribe desde el sitio** y la secretaría aprueba; el `correo` es donde se le avisa que su ficha quedó publicada. `user_id` queda preparado —y sin usar— para cuando esta bolsa tenga cuenta propia |
| `proveedores` | nombre, slug, **user_id (nullable)**, categoria_proveedor (`hielo`\|`licores`\|`alimentos`\|`aseo`\|`seguridad`\|`mantenimiento`\|`otros`), descripcion, whatsapp, correo, municipio_id, visible_hasta (date, nullable), estado, **acepta_datos, consentimiento_at** | Bolsa de proveedores, también por inscripción pública moderada. La **monetización futura** (cobrar por estar en la base) se modela con `visible_hasta`: solo se listan los vigentes. En el demo, todos vigentes |
| `carteras` | asociado_id (único), saldo_pendiente, meses_mora, ultimo_pago_at, actualizado_at | **Estado de cuenta del afiliado**. Se **importa por CSV** desde el panel (archivo de la contadora); el asociado lo ve en `/mi-cuenta`, solo lectura |
| `noticias` | titulo, slug, extracto, contenido (rich text), imagen, categoria (`noticia`\|`observatorio`\|`proyecto`), publicado_at, estado | Boletín de **baja frecuencia** (~mensual); categoría observatorio para cifras de la Nacional |
| `mensajes` | tipo (`contacto`\|`afiliacion`\|`pqr`\|`aliado`\|`proveedor`), nombre, correo, telefono, mensaje, acepta_datos, consentimiento_at, radicado, estado (`nuevo`\|`en_tramite`\|`respondido`) | `radicado` consecutivo tipo `PQR-2026-0001` solo para PQR. ⚠️ **v6:** el tipo `proveedor` quedó redundante — la bolsa tiene formulario propio desde `/proveedores/inscripcion`, pero el formulario de contacto **sigue ofreciendo** «Quiero aparecer en la bolsa de proveedores». Son dos caminos para lo mismo; hay que decidir si se retira esa opción del contacto o se deja como puerta alterna |
| `transacciones` | referencia única, concepto (`afiliacion`\|`evento`\|`mensualidad`), inscripcion_id (nullable), asociado_id (nullable), monto, moneda (COP), estado (`pendiente`\|`aprobada`\|`rechazada`), metodo (`pse`\|`tarjeta`\|`otro`), payload (json) | Consultable en el panel, solo lectura |
| `settings` | par clave/valor tipado o tabla dedicada | Textos institucionales, misión, contactos, redes, WhatsApp, correo destino, cifras del home. **Nada de contenido quemado en las vistas** |
| `users` | + rol (spatie), + MFA de Filament, + `asociado_id` (nullable) | Roles abajo; el rol `asociado` enlaza a su establecimiento para `/mi-cuenta` |

Relaciones obvias con claves foráneas e índices (slug únicos, filtros por municipio/categoría/estado).

## 6. Panel Filament `/admin` (el corazón del proyecto)

**Recursos:** Asociados, Eventos, Noticias, Requisitos de apertura, **Vacantes (solo moderación: sin crear ni editar)**, **Postulaciones** (bandeja), **Aspirantes** (bandeja), **Artistas**, **Proveedores**, Aliados, Beneficios, Municipios, Categorías, Mensajes (bandeja), Inscripciones, Transacciones (solo lectura), **Cartera** (listado + **importador CSV** con validación y resumen de filas), Usuarios, Ajustes del sitio (página de settings), Bitácora (página que lista activitylog con filtros por usuario/modelo/fecha).

**⚠️ El recurso de Vacantes no tiene formulario.** Registra solo la página de listado: nadie crea ni edita una vacante desde el panel, porque es contenido de un tercero. Las acciones de fila son aprobar, devolver con motivo y saltar a las postulaciones de esa vacante (filtradas en su propia bandeja, que es donde viven los datos personales de los candidatos).

**Todo el panel en español**, con el vocabulario del gremio (nunca "posts" ni "records": "Asociados", "Eventos y capacitaciones", "Bolsa de empleo", "Boletín"...).

**Roles (spatie/laravel-permission):**
- `super_admin` (la directora): acceso total; publica todo el contenido, gestiona usuarios, ajustes, cartera y ve transacciones. Es el único que **elimina**.
- `subadmin` (secretaria/pasantes): crea y edita Asociados, Eventos, Noticias, Requisitos y Aliados, y gestiona las bandejas de Mensajes, Postulaciones, Aspirantes e Inscripciones. **No puede publicar lo que ella misma redacta** —al guardar queda en `pendiente_aprobacion`—, **pero sí aprueba las tres bolsas** (`publicar_vacante`, `publicar_artista`, `publicar_proveedor`) y ningún otro `publicar_*`.
- `asociado` (dueño de establecimiento): **no entra al panel**; su sesión sirve para `/mi-cuenta` — cartera, beneficios con detalle y **sus vacantes**. Es el único que crea, edita y cierra vacantes de su establecimiento.

**El principio que ordena los permisos:** *nadie aprueba lo que él mismo redactó*. Las bolsas las escriben terceros —el asociado su vacante, el artista y el proveedor su ficha—, así que aprobarlas es trabajo de secretaría. El contenido que redacta la propia secretaría lo sigue aprobando la dirección.

**El otro principio, el que más código toca:** *quien publica es dueño de su contenido y es el único que lo edita*. `VacantePolicy` **no hereda** del mapeo por permisos que usan los demás recursos: gobierna por propiedad. Crear exige rol asociado con establecimiento vinculado; editar y cerrar exigen ser del establecimiento dueño; el gremio modera y lee, pero no reescribe lo ajeno. (Artistas y proveedores son la excepción documentada: mientras no tengan cuenta propia, la secretaría sí edita sus fichas.)

**Flujo de aprobación (requisito crítico RF-37):** cuando alguien guarda contenido que no puede publicar, el estado pasa a `pendiente_aprobacion` y llega una **notificación de base de datos** en el panel. **El aviso va a quien pueda publicar ESE modelo**, no a un rol fijo: se pregunta por la policy, no por el rol. Así las bolsas avisan a secretaría y dirección, el resto del contenido sigue avisando solo a dirección, y quien lo envió no se avisa a sí mismo — todo sin una sola línea de lógica por recurso. Impleméntalo con policies + acciones de Filament, y pruébalo: **la regla vive en el modelo (un observer), no en el formulario**, así que no se puede burlar mandando el estado a mano.

**Devolver exige motivo.** La acción de devolver una vacante abre un modal con un campo obligatorio; el motivo se guarda en la vacante, se le manda por correo al asociado y lo ve en su cuenta. Devolver sin decir por qué obliga al asociado a llamar a la oficina, que es justo lo que la plataforma viene a evitar.

**⚠️ La aprobación en lote tiene que producir exactamente los mismos efectos que la de fila** —limpiar el motivo, mandar el correo, saltarse los ya publicados—, y hay que probarla aparte. Es el camino que usa la secretaría cuando hay volumen, o sea cuando más importa que el aviso salga; y si reimplementa el `update` por su cuenta, se desincroniza en silencio.

**Bitácora (RF-39):** `spatie/laravel-activitylog` en todos los modelos de contenido y en login/logout; página "Bitácora" legible: "Natalia aprobó el asociado X — hace 2 horas".

**Autenticación reforzada (RF-40):** activa la **MFA del núcleo de Filament 4** (app de autenticación y/o código por correo) al menos para `super_admin`; política de contraseñas fuertes; throttle de intentos de login.

**Dashboard:** widgets de resumen — asociados publicados (y por municipio), **asociados en mora y saldo total de cartera**, mensajes nuevos, aspirantes de la semana, inscripciones de la semana, total recaudado del mes (transacciones aprobadas), últimas 5 transacciones, gráfico de inscripciones últimos 30 días.

**Bandejas:** Mensajes con filtros por tipo y estado (al abrir un PQR se ve el radicado; acción "Marcar respondido" con nota). **Postulaciones** filtrables por vacante y por estado de gestión — el gremio **no recibe aviso por cada postulación** (sería ruido), pero conserva la vista para medir si la bolsa está sirviendo y para responder por los datos que custodia. Aspirantes filtrables por área y estado. Las inscripciones muestran su evento y estado de pago. Ninguna bandeja permite crear registros a mano: todas entran por formularios públicos.

## 7. Sitio público (Blade + Tailwind, server-rendered)

Rutas y páginas (todas leen de la BD/settings, **cero texto quemado**):

1. **`/` Inicio** — hero oscuro con la promesa del gremio + botones "Explora la noche" (directorio) y "Afíliate"; franja de cifras (desde settings); asociados destacados (tarjetas con foto y municipio); los 5 beneficios; carrusel de aliados; próximos eventos (3); accesos directos a **Abre tu negocio** y **Bolsa de empleo**; CTA final de afiliación.
2. **`/quienes-somos`** — historia (fundación 2024), misión con énfasis en **representatividad gremial ante instituciones**, qué hace el gremio, junta/dirección, programas (Armenia 24 Horas, Foro Quindío Nocturno), enlace a Asobares Nacional.
3. **`/directorio`** — buscador por nombre + filtros por municipio y categoría (GET server-side, URLs compartibles tipo `/directorio?municipio=salento&categoria=cafe`); grid de tarjetas; **vista mapa** (Leaflet) con pins de los publicados. **`/directorio/{slug}`** — ficha del asociado: galería, "reseñita" (descripción), horario, dirección con mini-mapa, botones WhatsApp e Instagram y **enlaces a Google Maps/Business y TripAdvisor si existen**; **solo campos públicos** (el propietario decide qué se muestra); `schema.org` de negocio local en JSON-LD.
4. **`/abre-tu-negocio`** — la **página insignia**. Selector de municipio → requisitos por entidad con **checklist visible**, descripción, **costo aproximado si aplica**, enlaces y **formatos oficiales descargables** (botón "Descargar formato"); acabado formal e institucional (nada que parezca un Google Docs enlazado); texto de descargo ("verifica siempre con la entidad"); CTA "¿Dudas? Escríbenos".
5. **`/empleo`** — **bolsa de empleo del sector**: muro de vacantes **publicadas y vigentes** (ni cerradas ni pasadas de fecha), con filtro por **área del establecimiento** y municipio, y aviso visible «solo los establecimientos asociados publican vacantes»; más el formulario público «Déjanos tu perfil» para el banco de talento (nombre, contacto, cargo e **área** de interés, experiencia breve, habeas data), que **actualiza el perfil si el correo ya existe** en vez de duplicarlo. **`/empleo/{vacante}`** — página de detalle con el formulario de postulación, `JobPosting` en JSON-LD y otras vacantes del área. El WhatsApp del establecimiento queda como canal secundario, no como única vía.
6. **`/artistas`** — directorio de artistas (DJs, bandas, solistas) con filtro por tipo y **género musical**; tarjeta/ficha con foto, género, tarifa desde, WhatsApp y **video de YouTube embebido** (`iframe` con `loading="lazy"`) cuando exista. **`/artistas/inscripcion`** — formulario público para inscribirse en la bolsa (incluida foto), que crea la ficha en `pendiente_aprobacion`. ⚠️ **Regístrala antes que `/artistas/{artista:slug}`** o el slug se come la palabra «inscripcion».
7. **`/proveedores`** — bolsa de proveedores por categoría (hielo, licores, alimentos, aseo, seguridad, mantenimiento), **paginada**; tarjetas con contacto directo; CTA "¿Quieres aparecer aquí?" → **`/proveedores/inscripcion`**, formulario propio que crea la ficha en `pendiente_aprobacion`. Se acabó el «quiero ser proveedor» como mensaje de texto libre que la secretaría tenía que transcribir a mano. (La vigencia pagada `visible_hasta` sigue filtrando el listado.)
8. **`/eventos`** — próximos y pasados (tabs), tipo evento/capacitación, **solo eventos del gremio**; si el evento tiene `enlace_externo` (registro de la Nacional) el botón lleva allá. **`/eventos/{slug}`** — ficha con fecha, lugar, cupos y **formulario de inscripción** (nombre, correo, teléfono, establecimiento opcional, checkbox habeas data obligatorio). Si el evento tiene precio > 0, tras inscribirse redirige al **flujo de pago** (sección 8) y muestra el estado.
9. **`/mi-cuenta`** — login del rol `asociado`: saludo con su establecimiento, **estado de cartera** ("Estás al día" ✅ o "Debes N meses · $X" con botón **"Pagar ahora"** → flujo de pago concepto `mensualidad`), y los **beneficios con el detalle de convenios** (contenido privado). Al aprobarse el pago simulado, la cartera del demo queda al día.
    - **`/mi-cuenta/vacantes` — «Mis vacantes», el portal donde el asociado gestiona su bolsa.** Listado de las suyas con estado, motivo de devolución si lo hay y conteo de postulaciones; crear, editar, cerrar («ya contraté») y reabrir; y **`/mi-cuenta/vacantes/{vacante}`** con las postulaciones recibidas y su estado de gestión. Se construye en **Blade propio con la estética del sitio**, no como un segundo panel de Filament: el asociado nunca entra a `/admin` y esa frontera se mantiene dura.
    - **Editar una vacante publicada la devuelve a revisión** y la saca del muro hasta que la secretaría apruebe el cambio: lo que está publicado es siempre algo que alguien aprobó. **Cerrar y reabrir no pasan por aprobación** — no cambian el contenido.
    - El establecimiento se toma **de la sesión, nunca del formulario**, y el estado lo fija el servidor: mandar `estado` o `asociado_id` a mano no sirve de nada.
10. **`/boletin`** — listado con categorías (noticias, observatorio, próximos proyectos); **`/boletin/{slug}`** — detalle; las de observatorio con tarjetas de cifras destacadas. Sección deliberadamente sobria: frecuencia ~mensual.
11. **`/afiliate`** — los beneficios en grande, cómo funciona, formulario de afiliación (habeas data) que al enviarse: guarda el mensaje, muestra confirmación y ofrece botón directo a WhatsApp del gremio con mensaje precargado.
12. **`/contacto`** — formulario con tipo (contacto/PQR/quiero ser aliado/quiero ser proveedor); si es PQR genera y muestra **radicado** en pantalla y (mailer `log` en demo) por correo; datos de la oficina con mapa; redes.
13. **`/politica-de-datos`** — política de tratamiento de datos personales (plantilla seria conforme Ley 1581 de 2012, con los datos del gremio como responsable).
14. **Extras técnicos:** layout con navbar sticky + footer completo; página 404 con la marca; `sitemap.xml` (spatie), `robots.txt`, metas y Open Graph por página, favicon con el monograma.
15. **Selector de tema (v5):** desplegable de configuración en la navbar con Claro / Oscuro / Sistema. Lo ve cualquier visitante; con sesión abierta muestra además nombre, rol y las acciones que correspondan —*Mi cuenta* al asociado, *Ir al panel* a la secretaría y a la dirección, y *Cerrar sesión*—. En móvil el mismo bloque va desplegado dentro del menú hamburguesa. La preferencia se guarda en `localStorage.theme`, **la misma clave que usa Filament**, así que el panel y el sitio quedan siempre en el mismo tema. La clase `.dark` se pone en `<html>` desde un **script síncrono en el `<head>`, antes de las hojas de estilo**: si se deja para Alpine, quien tenga el sitio en claro ve un fogonazo negro en cada navegación. Ese mismo script mantiene el `<meta name="theme-color">` y escucha `matchMedia`, `storage` (otras pestañas) y `pageshow` (bfcache). Cerrar sesión no puede depender de Alpine: hace falta un respaldo en `<noscript>`.
16. **Portadas de relleno (v5):** las imágenes de ejemplo del sembrador se generan con **fondo transparente** —lo que se ve por detrás es la superficie de la tarjeta, así que la misma imagen vale en los dos temas—, diagonales en un gris neutro equidistante de ambas superficies y el monograma de marca centrado. Nada de fondos sólidos ni viñetas oscuras quemadas en el PNG: el degradado que hace legible el nombre del establecimiento ya lo pone la tarjeta en HTML y sí es consciente del tema.

**Rendimiento (RNF-02):** imágenes servidas en webp con thumbnails, `loading="lazy"`, sin librerías JS pesadas, CSS de Vite build. Objetivo: home < 2,5 s en móvil 4G.

## 8. Pagos (Bold, demostrable sin credenciales)

- Crea una interfaz `PaymentGateway` con dos implementaciones:
  - `BoldGateway`: estructura real — crea un **link de pago por API** (endpoint y llaves desde `.env`: `BOLD_API_KEY`, `BOLD_SECRET`, `BOLD_SANDBOX`) y recibe confirmación en `POST /webhooks/bold` **verificando la firma**. Déjalo implementado según la documentación pública de developers.bold.co, pero sin credenciales.
  - `FakeGateway` (por defecto en el demo, `PAYMENT_DRIVER=fake`): genera una página interna `/pago-simulado/{transaccion}` con la marca, selector decorativo de método (**PSE / Tarjeta** — el gremio prefiere PSE; la cuenta real es Itaú) y botones "Pagar" y "Rechazar" que disparan el mismo flujo del webhook. Así los flujos completos **inscripción → pago → confirmación** y **mi-cuenta → pagar mensualidad → cartera al día** se pueden demostrar en vivo.
- Toda transacción queda registrada (referencia, concepto, monto, método, estado, payload) y visible en el panel (RF-34). Nunca marques una inscripción como confirmada ni una cartera como saldada sin transacción aprobada.

**Reglas duras de la pasarela (v4 — cada una nació de un fallo real, no las deduzcas de nuevo):**

1. **La firma de Bold no es el HMAC habitual.** El orden es: codificar el cuerpo **crudo en Base64**, aplicarle **HMAC-SHA256** con la llave de identidad, y comparar el resultado en **hexadecimal** (64 caracteres). No es `base64(hmac(cuerpo))`. Equivocarse rechaza con 401 *todas* las notificaciones legítimas: el asociado paga, Bold cobra, y ni la inscripción se confirma ni la cartera se salda.
2. **La prueba de la firma se congela a mano.** Si el test recalcula la firma esperada con la misma fórmula que la implementación, valida el error contra sí mismo. Usa un cuerpo y una firma literales, más una aserción sobre el **formato** (64 caracteres hexadecimales).
3. **En el sandbox de Bold la firma se calcula con llave vacía.** Es el único caso en que se acepta una llave vacía, así que `BOLD_SANDBOX` debe valer `false` por omisión: si valiera `true`, a un despliegue le bastaría con olvidar la variable para firmar en blanco.
4. **`PAYMENT_DRIVER` no lleva valor por defecto** y el contenedor se niega a devolver la pasarela simulada fuera de `local`/`testing`. Un despliegue sin la variable tiene que romper en el arranque, no degradarse a la pasarela que aprueba cualquier pago.
5. **Las rutas `/pago-simulado/*` solo se registran en `local`/`testing`**, y el webhook responde 404 si la pasarela activa no es Bold. `FakeGateway::firmaValida()` devuelve `false` siempre: nadie externo debe confirmar por ahí.
6. **La referencia no es una credencial ni un identificador adivinable**: 8 bytes aleatorios, no 3. La página de estado del pago va **firmada y con caducidad**, y no muestra datos personales de nadie.
7. **La URL de retorno que se le entrega a la pasarela es un salto aparte** (`/pago/{ref}/retorno`), porque la pasarela puede añadir sus propios parámetros y eso invalidaría una firma justo después de pagar.
8. **Concilia el dinero antes de aplicar efectos.** Compara monto y moneda notificados contra la transacción local; si no cuadran, deja la transacción pendiente y regístralo. Y un pago **abona** sobre el saldo, no lo salda entero: si no, un abono de $50.000 borra una deuda de $500.000.

## 9. Seguridad y Habeas Data (no negociable)

- Validación estricta del lado servidor en TODOS los formularios públicos + rate limiting por IP + honeypot antispam simple (sin captcha de terceros en el demo).
- Checkbox de **autorización de tratamiento de datos obligatorio** en cada formulario que capture datos (inscripciones, postulaciones, banco de talento, inscripción de artistas y de proveedores, afiliación, contacto), con enlace a `/politica-de-datos` y `consentimiento_at` guardado con timestamp (Ley 1581 de 2012).
- **Retención (v6): los datos personales de la bolsa se borran solos.** Un comando `bolsas:depurar` programado a diario elimina las postulaciones cuya vacante cerró o venció hace más del plazo, y los perfiles del banco de talento cuyo consentimiento tiene más del plazo. Los plazos viven en `config/bolsas.php` (6 y 12 meses por defecto), nunca cableados. **El reloj cuelga de `consentimiento_at`, no de `updated_at`**: si colgara de la última edición, un simple cambio de estado de gestión desde el panel regalaría doce meses más sin que la persona haya renovado nada. Los registros sin sello caducan por `created_at`, o serían inmortales.
- **La política publicada tiene que describir lo que el sistema hace de verdad.** Si al postularse los datos del candidato se entregan al establecimiento —otro responsable de tratamiento—, eso es una **transferencia a un tercero** y va escrita en la política y en la casilla; un consentimiento «para atender esta solicitud» no la cubre. La lista de datos recolectados incluye la foto del artista y los contactos de artistas y proveedores. Y los plazos que anuncia la política se leen de la configuración, para que no se separen del comportamiento real.
- Contraseñas hasheadas (por defecto de Laravel), MFA en el panel, sesiones seguras, CSRF en todo, escape de salida (nada de `{!! !!}` sobre input de usuarios; el rich text de noticias se sanea). El `video_url` de artistas se valida como URL de YouTube y se embebe solo con el ID extraído.
- Archivos subidos: validar tipo/tamaño; imágenes reprocesadas (nunca servir el binario original de un upload). Los adjuntos de requisitos (formatos oficiales) se sirven con nombre limpio.
- La importación CSV de cartera valida columnas, tipos y asociado existente; muestra errores por fila sin abortar todo.
- `.env.example` completo y documentado; ningún secreto en el código ni en el repo.

**Lo anterior es la intención; esto es lo que hace falta para cumplirla de verdad (v4):**

- **JSON-LD dentro de `<script>`: nunca `JSON_UNESCAPED_SLASHES`.** Esa bandera desactiva el escape de la barra, que es justo lo que impide que un valor de la base cierre la etiqueta con `</script>`. Cualquier campo editable desde el panel —el nombre de un asociado, el título de una noticia— se convierte en XSS almacenado. Usa `JSON_HEX_TAG` y **un solo componente Blade** para los tres bloques: repetir la decisión de codificación en cada vista garantiza que la cuarta la copie mal.
- **La extensión de un archivo subido la decide el servidor, no quien sube.** Filament aleatoriza el nombre pero conserva la extensión: un JPEG legítimo llamado `payload.html` pasa la validación de tipo —su MIME es `image/jpeg`— y queda servido como HTML desde `/storage`. Deriva la extensión del MIME validado con `getUploadedFileNameForStorageUsing`.
- **Lo que se sirve tras un control de acceso no puede vivir en el disco público.** Los formatos de la guía van al disco privado; si están en `/storage`, comprobar el estado de publicación en el controlador es decorativo. Sírvelos con `Content-Type` explícito y `X-Content-Type-Options: nosniff`, y acota la ruta a su carpeta.
- **Los archivos se borran cuando dejan de estar referenciados** (al reemplazar, al vaciar el campo y al eliminar el registro). Con fotos que un propietario pidió retirar, no borrarlas es un problema de datos personales, no solo de disco. Ojo: las semillas comparten archivo entre registros, así que comprueba que nadie más lo use antes de borrar.
- **Los temporales de Livewire van al disco privado.** Con `FILESYSTEM_DISK=public`, el CSV de cartera de la contadora queda bajo `/storage` mientras dura la subida.
- **Parseo de dinero en el CSV: el separador decimal es el que está más a la derecha**, y solo cuenta como decimal si le siguen una o dos cifras. Borrar todos los puntos multiplica por cien cualquier archivo exportado en formato inglés. Una celda vacía es **un error de fila**, nunca un cero: dejar una deuda en cero tiene que ser una decisión escrita.
- **Todo CSV que se genere se escribe con `League\Csv`**, con un formateador que antepone apóstrofo a las celdas que empiezan por `=`, `+`, `-` o `@`. Excel las ejecuta como fórmula, y esos nombres los escribe un tercero.
- **Producción no arranca con la configuración del demo**: la aplicación falla si corre en producción con `APP_DEBUG=true` o con el mailer en `log`, fuerza HTTPS, y los seeders de cuentas de demostración se niegan a ejecutarse. La contraseña del demo está publicada en el README.
- **Los formularios se prueban con los campos que manda el navegador.** Un test que inyecta a mano un campo que el formulario real no envía enmascara el fallo: así estuvo roto todo el envío de `/afiliate`, que exigía un `tipo` que su formulario nunca mandaba.

**Y esto es lo que enseñó el rediseño de las bolsas (v6):**

- **Autorizar por permiso y autorizar por propiedad no son intercambiables.** La habilidad `view` de un recurso concede a quien tenga el permiso *o* a quien sea dueño; usarla para una ruta del portal del asociado deja entrar a cualquiera con permiso de panel. Un directivo que además sea dueño de un bar —caso perfectamente real en un gremio— podía leer los datos de los candidatos de todos los establecimientos. Las rutas del portal usan una habilidad **solo de propiedad**.
- **Un endpoint público que hace «buscar y si no existe crear» revienta con un doble clic.** Dos peticiones pasan el `first()` a la vez y la segunda choca contra el índice único: 500 en la cara del usuario, en un formulario que se envía desde el móvil con conexión lenta. Captura la violación de unicidad, vuelve a buscar y actualiza.
- **Un `<select>` obligatorio sin opción vacía llega preseleccionado con el primer valor.** Quien no toca el desplegable manda «Administración» y el primer municipio alfabético — y envenena justo los filtros que el módulo existe para ofrecer.
- **Los plurales del framework son ingleses.** `Str::plural('postulación')` devuelve «postulacións» en la pantalla que más mira el asociado.
- **Al reconstruir una tabla en una migración, nombra los índices explícitamente.** `Schema::rename()` no renombra los índices, así que quedan con el nombre de la tabla temporal y el `down()` los busca por convención, no los encuentra y revienta: el rollback queda inservible. (Y en SQLite hay que reconstruir, no alterar: no deja soltar una columna atada a una clave foránea.)

## 10. Datos semilla (para que el demo se vea vivo)

- 8 municipios, 6 categorías, **24 asociados ficticios** repartidos en municipios y categorías (nombres creíbles de bares/cafés quindianos inventados; 6 destacados; coordenadas reales aproximadas de cada municipio con jitter; 8 con `google_maps_url`/`tripadvisor_url` de ejemplo), con imágenes placeholder generadas localmente (SVG/PNG de color de marca con las iniciales del negocio — no dependas de URLs externas).
- 6 aliados (2 con `detalle_convenio` privado), los 5 beneficios reales, 6 eventos del gremio (3 futuros: 1 capacitación gratuita con inscripción, 1 evento con precio $30.000 para probar pagos, 1 con `enlace_externo` a la Nacional; 3 pasados), 8 inscripciones de ejemplo.
- **7 vacantes** (bartender, chef, mesero, administrador, portero, auxiliar de cocina — el DJ residente no: ese va en artistas —, más **una momentánea con fecha límite** para demostrar el turno de una noche) en 5 asociados distintos, cada una con su **área**; 1 queda a propósito en `pendiente_aprobacion` para demostrar que lo no aprobado no sale en `/empleo`. Además **4 postulaciones** repartidas sobre las publicadas y **7 perfiles** del banco de talento.
- **8 artistas** (4 DJs con géneros distintos, 2 bandas, 2 solistas; 3 con `video_url` de YouTube válida; tarifas variadas) y **10 proveedores** repartidos en las categorías (todos con `visible_hasta` vigente).
- **Cartera para los 24 asociados**: 16 al día y 8 en mora (1–6 meses, saldos realistas de mensualidad ~$50.000).
- 6 noticias (2 de observatorio con las cifras reales del punto 2, 1 de "próximos proyectos"), **requisitos de apertura completos para Armenia, Salento y Filandia** (6 entidades c/u, con checklist json, costo aproximado donde aplique y 1–2 adjuntos de ejemplo generados localmente como PDF simple), 10 mensajes variados (3 PQR con radicados consecutivos, 2 tipo proveedor), 6 transacciones en distintos estados (incluida 1 `mensualidad` aprobada).
- Usuarios: `direccion@asobaresquindio.test` (super_admin), `oficina@asobaresquindio.test` (subadmin) y `asociado@asobaresquindio.test` (rol asociado, **vinculado a un asociado en mora de 3 meses** para el guion del demo), contraseña `Asobares2026*` los tres — imprime estas credenciales al final del seeder.
- Settings poblados con los datos institucionales reales del punto 2.

## 11. Requisitos no funcionales — checklist

- [ ] Mobile-first verificado en 390 px, 768 px y 1280 px.
- [ ] Todo el contenido editable desde el panel (RNF-09): si un texto aparece en el sitio, vive en la BD o en settings.
- [ ] SEO: title/description únicos por página, OG, slugs limpios, sitemap, JSON-LD en fichas.
- [ ] Imágenes webp + lazy; sin fuentes ni librerías innecesarias.
- [ ] Accesibilidad base: contraste AA **en los dos temas** (no solo sobre el fondo oscuro), alt en imágenes, labels en formularios, foco visible.
- [ ] Tema claro/oscuro: cero clases de color cableadas en las vistas; el modo oscuro conserva exactamente los valores con los que se diseñó; sin parpadeo al navegar; el estado activo del selector se distingue por algo más que el color (WCAG 1.4.1 y 1.4.11).
- [ ] Prueba de guardia que recorra las vistas y falle si reaparece una clase de tema cableada — incluidas las vistas de paquetes publicadas en `resources/views/vendor/`, que es por donde se coló el paginador.
- [ ] Retención de datos personales de la bolsa configurable y automática; la política publicada dice lo que el sistema hace, incluida la transferencia al establecimiento.
- [ ] Código en inglés, UI en español; PSR-12 (`laravel/pint`).
- [ ] Git: repositorio inicializado, commits pequeños y descriptivos por fase.

## 12. Orden de trabajo (síguelo)

1. **Fase 0 — Setup:** **fetch y sigue https://laravel.com/for/agents** (verificar `php`/`composer`/`laravel`; instalar con php.new lo que falte — ⚠️ en Windows el build de php.new no trae `intl` ni `gd`, que Filament exige: usar el build oficial de windows.php.net); crear el proyecto con SQLite + npm + **Boost**, **sin starter kit**, y cargar sus guías; instalar Filament y paquetes; configurar Tailwind, fuentes, colores; commit.
2. **Fase 1 — Datos:** migraciones + modelos + factories + seeders completos (incluidas las entidades nuevas); `php artisan migrate:fresh --seed` sin errores; commit.
3. **Fase 2 — Panel:** recursos Filament (incluidos Vacantes, Aspirantes, Artistas, Proveedores y Cartera con importador CSV), roles y policies, flujo de aprobación con notificaciones, bitácora, MFA, dashboard; commit.
4. **Fase 3 — Sitio público núcleo:** layout + páginas 1–4 y 8–16 (inicio, quiénes somos, directorio, abre-tu-negocio, eventos, boletín, afíliate, contacto, política, extras, **selector de tema y portadas de relleno bicromáticas**), filtros del directorio, mapa, formularios con habeas data y radicados; commit. Monta la capa de tokens semánticos que describe la sección 4 **antes** de escribir la primera vista: migrar 446 clases cableadas después cuesta mucho más que nacer con ellas.
5. **Fase 3b — Módulos Reunión 2:** `/empleo` (muro filtrado + detalle con formulario de postulación), `/artistas` y `/proveedores` (directorio + inscripción pública moderada), y `/mi-cuenta` con login de asociado, vista de cartera y **el portal «Mis vacantes»**; los cuatro correos del gremio (postulación recibida, vacante aprobada, vacante devuelta con motivo, ficha publicada); y el comando de depuración con su tarea programada. Commit.
   - **Orden importante dentro de la fase:** primero el modelo de datos y los permisos, después el portal del asociado, después la cara pública, y **los correos justo antes de la moderación del panel** — las vistas de correo enlazan a rutas que crean el portal y el detalle público, así que escribirlas antes deja tests rojos por rutas inexistentes.
6. **Fase 4 — Pagos:** interfaz + FakeGateway (con concepto `mensualidad`) + esqueleto Bold + transacciones en panel; commit.
7. **Fase 5 — Calidad:** pruebas (mínimo: subadmin no puede publicar lo que redacta pero sí aprueba las bolsas; PQR genera radicado consecutivo; inscripción exige habeas data; webhook actualiza transacción e inscripción; **importar CSV de cartera actualiza saldos**; **/mi-cuenta exige rol asociado**; **una vacante no publicada, cerrada o vencida no aparece en /empleo ni tiene detalle**; **un asociado no ve ni edita nada de otro establecimiento**; **editar una publicada la devuelve a revisión y cerrar no**; **postularse guarda y avisa, y reenviar no duplica ni reenvía**; **devolver exige motivo**; **aprobar en lote produce los mismos efectos que aprobar de a uno**; **la depuración borra lo vencido, respeta lo vigente y aborta con un plazo inválido**; rutas públicas responden 200), pint, revisión de RNF, README.
   - **Prueba la frontera negativa, no solo el camino feliz.** Un test que comprueba que el dueño puede editar no prueba nada si no hay otro que compruebe que el vecino no. Y cuando un cambio deje sin sentido un test viejo, **cámbialo para que afirme la regla nueva** —un 403 esperado— en vez de excluir el caso del barrido: excluirlo borra la cobertura justo donde acaba de cambiar el comportamiento.
8. **Verificación final:** `php artisan migrate:fresh --seed && php artisan test && npm run build`, levantar el server y recorrer con curl las rutas públicas y el login del panel. Arregla todo lo que falle antes de reportar.

## 13. Entregable final de tu sesión

Termina con un **README.md** que incluya: qué es el proyecto, requisitos, pasos exactos para correrlo (5 comandos máximo), credenciales demo (los 3 usuarios), cómo probar el flujo de aprobación (guion de 6 pasos), cómo probar el pago simulado de un evento, **cómo probar el flujo de cartera** (login asociado → ver mora → pagar → al día), **cómo importar el CSV de la contadora**, cómo activar Bold real con `.env`, y la lista de pendientes conocidos. Y un resumen en consola: qué se construyó, qué pruebas pasan, y las 3 cosas que priorizarías después.

**Guion de la bolsa de empleo, de punta a punta** (el que demuestra el rediseño de la v6, y el que conviene recorrer a mano antes de entregar):

1. Entrar a `/mi-cuenta` con la cuenta de asociado y abrir **Mis vacantes**.
2. Publicar una vacante momentánea con fecha límite → queda pendiente y **no** aparece en `/empleo`.
3. Entrar al panel como secretaría: hay notificación de pendiente. Devolverla con motivo.
4. Volver a `/mi-cuenta/vacantes`: se ve el motivo. Corregir y reenviar.
5. Aprobarla desde el panel → aparece en `/empleo` y en su página de detalle.
6. Postularse desde el detalle **sin iniciar sesión** → aparece en «Mis vacantes» del asociado y en `/admin/postulaciones`.
7. Marcar «Ya contraté» → la vacante desaparece del muro y su detalle da 404.
8. Inscribirse como artista desde `/artistas/inscripcion` → la ficha entra pendiente y no es visible; aprobarla desde el panel la publica.
9. `php artisan bolsas:depurar --pretend` informa sin borrar.

## 14. Lo que NO debes hacer

- No usar WordPress, WooCommerce, plantillas compradas, page builders ni CSS genérico de framework sin personalizar.
- No dejar textos "Lorem ipsum": todo el contenido semilla es en español y del contexto del gremio.
- No inventar credenciales de Bold ni llamar APIs externas de pago; el demo usa FakeGateway.
- No usar datos personales reales de establecimientos, artistas, proveedores o personas (salvo los institucionales del punto 2).
- No publicar eventos de bares individuales en las semillas: solo eventos del gremio.
- No saltarte el flujo de aprobación "porque es un demo": es EL requisito que estamos evaluando.
- **No volver a modelar las bolsas como un CRUD del panel.** La vacante la escribe el establecimiento; el gremio modera. Si la oficina tiene que teclear las vacantes, la bolsa no se mueve — ya se probó.
- **No dejar que el gremio edite contenido de un tercero.** Aprobar, devolver con motivo y, en último caso, eliminar. Reescribir lo ajeno, no.
- **No guardar datos de personas que buscan empleo sin plazo de borrado**, ni prometer en la política un plazo que el código no cumpla.
- No terminar con migraciones, seeders o pruebas rotas.

---

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
- **La convención de `ticks`/`grid` vacíos en los `ChartWidget` es load-bearing y frágil**: el plugin de tema solo escribe donde ya hay clave. Hay pruebas que lo vigilan **por eje** en los widgets del observatorio, pero no en los del tablero.
- **Duplicación entre los dos guardianes de tema** (`TemaClaroOscuroTest`, sitio y panel): ~28 líneas literales.
- **`RequisitoAperturaFactory`** usa el string crudo `borrador` donde su hermana usa el caso del enum.
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

2. **La política obvia del bucket reabre un agujero ya cerrado.** Conceder `s3:GetObject` sobre `arn:...:<bucket>/*` deja los formatos oficiales de la guía normativa descargables por URL directa — medido: 200, sin pasar por `GuiaController`, que es donde se comprueba que el requisito esté publicado. Acotada al prefijo `publico/`: 403. Los dos prefijos son la frontera de seguridad, y `AlmacenamientoTest` afirma que siguen siendo distintos.

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

En una frase, para quien solo lea esta sección: **lo único que bloquea el proyecto es la cuenta institucional del gremio (R-14), y de ella cuelgan el bucket, el correo saliente, el SSL, la medición contra dominio y las pruebas en dispositivos de la tutora.** Lo demás son firmas.

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
2. **El capítulo 5 del documento de práctica** se escribe con el repositorio en la mano, y ahora las cifras son otras: **747 casos de prueba**, 22 modelos, 33 migraciones, el calendario de eventos, y `docs/ingenieria/` como anexo. ⚠️ Recuerda el §24: el documento de la universidad es **individual**, uno por practicante, aunque ambos anexen los mismos artefactos técnicos.

### 26.2 En cuanto exista la cuenta

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
