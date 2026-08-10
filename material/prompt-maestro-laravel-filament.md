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

## 18. ESTADO ACTUAL Y TRABAJO EN CURSO (9 ago 2026)

**Léelo antes de tocar nada.** Esta sección existe para que una sesión nueva sepa en qué punto está el proyecto sin tener que reconstruirlo del historial. Se escribió con trabajo a medias en el árbol; si retomas, empieza por «Lo que está a medias».

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

### 18.2 Lo que está a medias — Observatorio del gremio

**Rama `observatorio`, 25 commits sobre `main`.** Plan en `docs/superpowers/plans/2026-08-09-observatorio.md`; el registro de ejecución, con todos los hallazgos y decisiones, en `.superpowers/sdd/2026-08-09-observatorio/progress.md` (git-ignorado, pero es el mapa de recuperación).

**Las ocho tareas del plan están cerradas y revisadas**, y encima se aplicó una **ola de arreglos** tras la revisión de toda la rama, que encontró dos Críticos. Las pruebas propias del módulo están en verde: **115/115** en `tests/Feature/Panel/`.

**Lo que falta para poder fusionar — está desarrollado como encargo en la sección 19, que es lo primero que hay que hacer:**
1. La **re-revisión acotada de la ola de arreglos** (siete commits, de `168ce93` a `7778ce3`). Es el último control del método y no se ha hecho.
2. **Correr la suite completa sobre un árbol limpio.** Hoy no se puede — ver 18.8.
3. Fusionar y recuperar el stash de 18.5.

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

Está en **`stash@{0}`** de la rama `observatorio`, por decisión del dueño, para no mezclar dos trabajos sin relación. **Merece commit propio.** Recupéralo con `git stash pop` cuando el observatorio esté cerrado.

### 18.6 Deuda conocida que quedó anotada

De la revisión final de F1–F3, ninguna bloqueante, todas con su sitio:

- **`<x-panel.kpi>` no tenía consumidor en producción** hasta que el observatorio estrenó su banda de KPIs. Si el observatorio se descarta, el componente vuelve a quedar huérfano.
- **La convención de `ticks`/`grid` vacíos en los `ChartWidget` es load-bearing y frágil**: el plugin de tema solo escribe donde ya hay clave. Hay pruebas que lo vigilan **por eje** en los widgets del observatorio, pero no en los del tablero.
- **Duplicación entre los dos guardianes de tema** (`TemaClaroOscuroTest`, sitio y panel): ~28 líneas literales.
- **`RequisitoAperturaFactory`** usa el string crudo `borrador` donde su hermana usa el caso del enum.
- **El singleton de `ColaDePendientes` habilita staleness intra-petición** si el rol de un usuario cambiara entre `canView()` y el render. No explotable hoy; documentado en `AppServiceProvider`.
- **`AsociadoSeeder`** lanzaría `ValueError` si algún día `CarteraSeeder::EN_MORA` ganara un slug con mora ≥ 22 meses.
- **`Bitacora` y `AjustesDelSitio`** arrastran un `abort_unless` redundante con un comentario que dice que es el que cierra la puerta; el guardián real es el trait `CanAuthorizeAccess` de Filament.
- **La corrida contra PostgreSQL que pide el spec §7 sigue sin hacerse.** `RecaudoMensual` y `MetricasDelObservatorio` tienen expresiones SQL por motor de las que **solo se ejecuta la de SQLite**; las de `pgsql` y `mysql` se estrenarán el día del despliegue.

### 18.7 Cómo se está trabajando, por si retomas

Método: **diseño conversado y aprobado antes de tocar código** (`docs/superpowers/specs/`), **plan con el código y las pruebas escritos de antemano** (`docs/superpowers/plans/`), y ejecución tarea por tarea con un implementador fresco y un **revisor independiente por tarea**, más una revisión de toda la rama al final.

Lo que hace que funcione no es la revisión en sí, es **la mutación**: el revisor rompe el código a propósito y comprueba si la prueba se entera. Cinco de los fallos más caros de estas dos fases eran pruebas que pasaban con el bug reintroducido, y ninguna se detectó leyendo.

⚠️ **El panel del navegador de esta sesión no compone fotogramas** —las capturas fallan con «the page is not compositing frames»—, así que **nadie ha visto Chart.js pintar de verdad**. Lo verificado es estructural: el JSON de opciones que llega al cliente, los tokens computados, el DOM. Si tu sesión sí puede componer, **mira las gráficas del observatorio y del tablero en los dos temas**: es la única verificación que falta en todo este trabajo.

### 18.8 ⚠️ HAY OTRA SESIÓN TRABAJANDO EN ESTE MISMO DIRECTORIO

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

**Consecuencia práctica, y es la que importa:** con ese trabajo en el árbol, **`php artisan test` da 52 fallos** — todos en `PanelCompletoTest`, todos `302` porque la MFA obligatoria cambia el flujo de login que esas pruebas asumen. **No son del Observatorio.** Las pruebas propias del módulo (`tests/Feature/Panel/`) están en **115/115 verde**.

Si retomas y ves la suite en rojo: **primero mira `git status`**. Si esos archivos siguen sin commitear, el rojo probablemente no es tuyo. Verifica tu trabajo corriendo solo tus archivos, y no intentes «arreglar» `PanelCompletoTest` — le toca a quien esté haciendo la MFA obligatoria, que además tendrá que actualizar esas pruebas para que afirmen la regla nueva.

**No stashees ese trabajo sin hablarlo.** Son 31 archivos de otra persona a medio camino.

---

## 19. LO PRIMERO QUE TIENES QUE HACER EN LA PRÓXIMA SESIÓN

**Esta sección es el encargo, no el contexto.** El Observatorio está construido y sus pruebas están verdes, pero **no está cerrado**: le faltan los tres pasos de abajo, en este orden. Hazlos antes de empezar nada nuevo.

### Paso 1 — La re-revisión de la ola de arreglos (pendiente, es el control que falta)

Tras cerrar las ocho tareas, una revisión de toda la rama encontró dos Críticos y se aplicó una **ola de arreglos de siete commits**. Esa ola **nunca se re-revisó**, y el método dice que toda ola de arreglos termina con una re-revisión acotada. Es el último control y está sin hacer.

El rango exacto es `168ce93..7778ce3`. Genera el paquete así:

```
bash <ruta-a-superpowers>/skills/subagent-driven-development/scripts/review-package \
  docs/superpowers/plans/2026-08-09-observatorio.md 168ce93 7778ce3
```

Los siete commits, y lo que cada uno debe haber cerrado:

| Commit | Qué arregla | Qué debe verificar la re-revisión |
|---|---|---|
| `2625326` | Sube la regla del umbral a una clase base de la que heredan las seis gráficas | Que **ninguna** gráfica puede olvidarse de la regla. Añade una séptima de mentira sin declarar nada y comprueba que hereda el comportamiento |
| `f3f09d2` | Saca las gráficas del observatorio del descubrimiento del tablero | Que el tablero **no** contiene ninguna. Revierte el arreglo y comprueba que la prueba nueva se pone roja |
| `2731819` | El umbral se exige al conjunto más flaco, no a la suma | Que una serie con varios conjuntos y uno flojo **no** alcanza muestra, y que sí la alcanza cuando todos la tienen |
| `999c7fc` | Reescribe la prueba de las «sólidas» para que mida lo que dice | Que ahora afirma canvas presente y estado vacío ausente, y que **deriva del umbral en vivo** en vez de una lista escrita a mano |
| `06785cf` | Distingue base vacía de muestra insuficiente | Que con base vacía se ve texto legible y no un lienzo en blanco |
| `f887104` | Una sola fuente para la frase `que`, y color visible para «Otros» | Que no quedan frases duplicadas entre widget e informe, y que «Otros» se ve en los dos temas |
| `7778ce3` | Arregla dos pruebas que el arreglo del umbral dejó en rojo | Que las arregló **afirmando la regla nueva**, no excluyendo el caso |

**Exígele mutación, no lectura.** En estas dos fases aparecieron **seis pruebas en falso verde**, todas escritas por el autor del plan, todas de la forma «afirmo que una cadena aparece en algún sitio». Ninguna se detectó leyendo. La instrucción al re-revisor tiene que ser: *rompe el código de cada arreglo y comprueba que la prueba se entera*.

### Paso 2 — La suite completa sobre un árbol limpio

Hoy **no se puede correr** y el motivo está en 18.8: otra sesión tiene 31 archivos sin commitear (MFA obligatoria, cabeceras, retención, cupos) que dejan 52 pruebas de `PanelCompletoTest` en rojo por un `302` de login. **Ese rojo no es del Observatorio.**

Antes de fusionar hace falta una corrida limpia. Dos caminos, y **hay que hablarlo con el dueño**, no decidirlo solo:

- **Si ese trabajo ya está commiteado** cuando retomes: corre la suite y punto.
- **Si sigue sin commitear**: no lo stashees por tu cuenta. Pregunta. Son 31 archivos de otra persona a medio camino, y ya hubo que guardar uno antes (18.5).

Mientras tanto, lo que sí puedes afirmar: `php artisan test --compact tests/Feature/Panel/` da **115/115**, y ésas son las pruebas propias del módulo.

### Paso 3 — Fusionar, y recuperar el stash

Con la re-revisión limpia y la suite verde:

```
git checkout main
git merge observatorio
php artisan test --compact          # sobre el resultado fusionado, no solo sobre la rama
git branch -d observatorio
git stash pop                       # el arreglo de seguridad de la galeria, ver 18.5
```

El `stash@{0}` **merece commit propio**, no ir mezclado: es el hallazgo G6 de la sección 15 en el campo de galería, que se le escapó a la auditoría de agosto. Con él, `SubidaDeImagenesTest` pasa 11/11.

### Y una decisión que el dueño tiene pendiente, no técnica

Tras la ola de arreglos, **de las seis gráficas del observatorio solo dibuja una** (salud financiera). Las otras cinco muestran «Aún sin muestra suficiente», y es correcto: sus muestras no sostienen lo que dibujarían.

Es honesto, y es exactamente lo que el módulo prometía. **También es un observatorio que enseña cinco paneles vacíos el día de la presentación ante la directiva.** Las dos salidas honestas:

- **Enseñarlo así**, explicando que la herramienta ya mide y lo que falta es que el sector alimente el dato. Es defendible y distingue esto de un tablero de vanidad.
- **Sembrar una bolsa de empleo con volumen realista** —del orden de 60–80 vacantes repartidas en 18 meses y por área, más aspirantes proporcionales— para que las series tengan sustancia. Son datos ficticios sobre un mercado laboral que no existe todavía, y eso hay que tenerlo claro antes de elegirlo.

**No lo decidas tú.** Pregúntaselo al dueño antes del 22 de septiembre.
