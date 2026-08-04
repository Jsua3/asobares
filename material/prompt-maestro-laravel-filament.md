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
| `vacantes` | asociado_id, cargo, tipo (`tiempo_completo`\|`por_turnos`), descripcion, franja_horaria, whatsapp_contacto, estado | **Bolsa de empleo del sector** ("muro"). **Solo asociados publican** (en el demo se crean desde el panel a nombre del asociado). Mismo flujo de estados |
| `aspirantes` | vacante_id (nullable), nombre, correo, telefono, cargo_interes, experiencia (texto corto), acepta_datos, consentimiento_at | Registro público de quien busca empleo (bartender, chef, mesero, administrador…) |
| `artistas` | nombre, slug, tipo (`dj`\|`banda`\|`solista`\|`otro`), genero_musical, descripcion, tarifa_desde (nullable), video_url (nullable, YouTube), whatsapp, instagram_url, foto, municipio_id, estado | Categoría separada del empleo («el DJ es artista, el mesero es empleo»). Ficha con género, tarifa y video embebido |
| `proveedores` | nombre, slug, categoria_proveedor (`hielo`\|`licores`\|`alimentos`\|`aseo`\|`seguridad`\|`mantenimiento`\|`otros`), descripcion, whatsapp, correo, municipio_id, visible_hasta (date, nullable), estado | Bolsa de proveedores. La **monetización futura** (cobrar por estar en la base) se modela con `visible_hasta`: solo se listan los vigentes. En el demo, todos vigentes |
| `carteras` | asociado_id (único), saldo_pendiente, meses_mora, ultimo_pago_at, actualizado_at | **Estado de cuenta del afiliado**. Se **importa por CSV** desde el panel (archivo de la contadora); el asociado lo ve en `/mi-cuenta`, solo lectura |
| `noticias` | titulo, slug, extracto, contenido (rich text), imagen, categoria (`noticia`\|`observatorio`\|`proyecto`), publicado_at, estado | Boletín de **baja frecuencia** (~mensual); categoría observatorio para cifras de la Nacional |
| `mensajes` | tipo (`contacto`\|`afiliacion`\|`pqr`\|`aliado`\|`proveedor`), nombre, correo, telefono, mensaje, acepta_datos, consentimiento_at, radicado, estado (`nuevo`\|`en_tramite`\|`respondido`) | `radicado` consecutivo tipo `PQR-2026-0001` solo para PQR. Tipo `proveedor` = "quiero aparecer en la bolsa" |
| `transacciones` | referencia única, concepto (`afiliacion`\|`evento`\|`mensualidad`), inscripcion_id (nullable), asociado_id (nullable), monto, moneda (COP), estado (`pendiente`\|`aprobada`\|`rechazada`), metodo (`pse`\|`tarjeta`\|`otro`), payload (json) | Consultable en el panel, solo lectura |
| `settings` | par clave/valor tipado o tabla dedicada | Textos institucionales, misión, contactos, redes, WhatsApp, correo destino, cifras del home. **Nada de contenido quemado en las vistas** |
| `users` | + rol (spatie), + MFA de Filament, + `asociado_id` (nullable) | Roles abajo; el rol `asociado` enlaza a su establecimiento para `/mi-cuenta` |

Relaciones obvias con claves foráneas e índices (slug únicos, filtros por municipio/categoría/estado).

## 6. Panel Filament `/admin` (el corazón del proyecto)

**Recursos:** Asociados, Eventos, Noticias, Requisitos de apertura, **Vacantes**, **Aspirantes** (bandeja), **Artistas**, **Proveedores**, Aliados, Beneficios, Municipios, Categorías, Mensajes (bandeja), Inscripciones, Transacciones (solo lectura), **Cartera** (listado + **importador CSV** con validación y resumen de filas), Usuarios, Ajustes del sitio (página de settings), Bitácora (página que lista activitylog con filtros por usuario/modelo/fecha).

**Todo el panel en español**, con el vocabulario del gremio (nunca "posts" ni "records": "Asociados", "Eventos y capacitaciones", "Bolsa de empleo", "Boletín"...).

**Roles (spatie/laravel-permission):**
- `super_admin` (la directora): acceso total; único que **publica** contenido, gestiona usuarios, ajustes, cartera y ve transacciones.
- `subadmin` (secretaria/pasantes): crea y edita Asociados, Eventos, Noticias, Requisitos, Vacantes, Artistas, Proveedores y Aliados, y gestiona las bandejas de Mensajes, Aspirantes e Inscripciones, **pero no puede publicar**: al guardar, su contenido queda en `pendiente_aprobacion`.
- `asociado` (dueño de establecimiento): **no entra al panel**; su sesión sirve para `/mi-cuenta` (cartera + beneficios con detalle). Preparado para que en fase 2 pueda editar su propia ficha.

**Flujo de aprobación (requisito crítico RF-37):** cuando un subadmin guarda contenido, el estado pasa a `pendiente_aprobacion` y el súper admin recibe una **notificación de base de datos** en el panel; el súper admin ve un badge con el número de pendientes, revisa y usa una acción "Aprobar y publicar" o "Devolver a borrador". Impleméntalo con policies + acciones de Filament, y pruébalo: un subadmin **no debe poder** publicar ni con manipulación del formulario.

**Bitácora (RF-39):** `spatie/laravel-activitylog` en todos los modelos de contenido y en login/logout; página "Bitácora" legible: "Natalia aprobó el asociado X — hace 2 horas".

**Autenticación reforzada (RF-40):** activa la **MFA del núcleo de Filament 4** (app de autenticación y/o código por correo) al menos para `super_admin`; política de contraseñas fuertes; throttle de intentos de login.

**Dashboard:** widgets de resumen — asociados publicados (y por municipio), **asociados en mora y saldo total de cartera**, mensajes nuevos, aspirantes de la semana, inscripciones de la semana, total recaudado del mes (transacciones aprobadas), últimas 5 transacciones, gráfico de inscripciones últimos 30 días.

**Bandejas:** Mensajes con filtros por tipo y estado (al abrir un PQR se ve el radicado; acción "Marcar respondido" con nota). Aspirantes filtrables por cargo. Las inscripciones muestran su evento y estado de pago.

## 7. Sitio público (Blade + Tailwind, server-rendered)

Rutas y páginas (todas leen de la BD/settings, **cero texto quemado**):

1. **`/` Inicio** — hero oscuro con la promesa del gremio + botones "Explora la noche" (directorio) y "Afíliate"; franja de cifras (desde settings); asociados destacados (tarjetas con foto y municipio); los 5 beneficios; carrusel de aliados; próximos eventos (3); accesos directos a **Abre tu negocio** y **Bolsa de empleo**; CTA final de afiliación.
2. **`/quienes-somos`** — historia (fundación 2024), misión con énfasis en **representatividad gremial ante instituciones**, qué hace el gremio, junta/dirección, programas (Armenia 24 Horas, Foro Quindío Nocturno), enlace a Asobares Nacional.
3. **`/directorio`** — buscador por nombre + filtros por municipio y categoría (GET server-side, URLs compartibles tipo `/directorio?municipio=salento&categoria=cafe`); grid de tarjetas; **vista mapa** (Leaflet) con pins de los publicados. **`/directorio/{slug}`** — ficha del asociado: galería, "reseñita" (descripción), horario, dirección con mini-mapa, botones WhatsApp e Instagram y **enlaces a Google Maps/Business y TripAdvisor si existen**; **solo campos públicos** (el propietario decide qué se muestra); `schema.org` de negocio local en JSON-LD.
4. **`/abre-tu-negocio`** — la **página insignia**. Selector de municipio → requisitos por entidad con **checklist visible**, descripción, **costo aproximado si aplica**, enlaces y **formatos oficiales descargables** (botón "Descargar formato"); acabado formal e institucional (nada que parezca un Google Docs enlazado); texto de descargo ("verifica siempre con la entidad"); CTA "¿Dudas? Escríbenos".
5. **`/empleo`** — **bolsa de empleo del sector**: muro de vacantes publicadas (filtro por cargo y municipio; aviso visible: "solo los establecimientos asociados publican vacantes") + formulario público "Déjanos tu perfil" (aspirante: nombre, contacto, cargo de interés, experiencia breve, habeas data).
6. **`/artistas`** — directorio de artistas (DJs, bandas, solistas) con filtro por tipo y **género musical**; tarjeta/ficha con foto, género, tarifa desde, WhatsApp y **video de YouTube embebido** (`iframe` con `loading="lazy"`) cuando exista.
7. **`/proveedores`** — bolsa de proveedores por categoría (hielo, licores, alimentos, aseo, seguridad, mantenimiento); tarjetas con contacto directo; CTA "¿Quieres aparecer aquí? Escríbenos" → formulario tipo `proveedor`. (La vigencia pagada `visible_hasta` filtra el listado.)
8. **`/eventos`** — próximos y pasados (tabs), tipo evento/capacitación, **solo eventos del gremio**; si el evento tiene `enlace_externo` (registro de la Nacional) el botón lleva allá. **`/eventos/{slug}`** — ficha con fecha, lugar, cupos y **formulario de inscripción** (nombre, correo, teléfono, establecimiento opcional, checkbox habeas data obligatorio). Si el evento tiene precio > 0, tras inscribirse redirige al **flujo de pago** (sección 8) y muestra el estado.
9. **`/mi-cuenta`** — login del rol `asociado`: saludo con su establecimiento, **estado de cartera** ("Estás al día" ✅ o "Debes N meses · $X" con botón **"Pagar ahora"** → flujo de pago concepto `mensualidad`), y los **beneficios con el detalle de convenios** (contenido privado). Al aprobarse el pago simulado, la cartera del demo queda al día.
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
- Checkbox de **autorización de tratamiento de datos obligatorio** en cada formulario que capture datos (inscripciones, aspirantes, afiliación, contacto, proveedor), con enlace a `/politica-de-datos` y `consentimiento_at` guardado con timestamp (Ley 1581 de 2012).
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

## 10. Datos semilla (para que el demo se vea vivo)

- 8 municipios, 6 categorías, **24 asociados ficticios** repartidos en municipios y categorías (nombres creíbles de bares/cafés quindianos inventados; 6 destacados; coordenadas reales aproximadas de cada municipio con jitter; 8 con `google_maps_url`/`tripadvisor_url` de ejemplo), con imágenes placeholder generadas localmente (SVG/PNG de color de marca con las iniciales del negocio — no dependas de URLs externas).
- 6 aliados (2 con `detalle_convenio` privado), los 5 beneficios reales, 6 eventos del gremio (3 futuros: 1 capacitación gratuita con inscripción, 1 evento con precio $30.000 para probar pagos, 1 con `enlace_externo` a la Nacional; 3 pasados), 8 inscripciones de ejemplo.
- **6 vacantes** (bartender, chef, mesero, administrador, DJ residente no — ese va en artistas —, portero, auxiliar de cocina) en 5 asociados distintos + **7 aspirantes** de ejemplo.
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
- [ ] Código en inglés, UI en español; PSR-12 (`laravel/pint`).
- [ ] Git: repositorio inicializado, commits pequeños y descriptivos por fase.

## 12. Orden de trabajo (síguelo)

1. **Fase 0 — Setup:** **fetch y sigue https://laravel.com/for/agents** (verificar `php`/`composer`/`laravel`; instalar con php.new lo que falte — ⚠️ en Windows el build de php.new no trae `intl` ni `gd`, que Filament exige: usar el build oficial de windows.php.net); crear el proyecto con SQLite + npm + **Boost**, **sin starter kit**, y cargar sus guías; instalar Filament y paquetes; configurar Tailwind, fuentes, colores; commit.
2. **Fase 1 — Datos:** migraciones + modelos + factories + seeders completos (incluidas las entidades nuevas); `php artisan migrate:fresh --seed` sin errores; commit.
3. **Fase 2 — Panel:** recursos Filament (incluidos Vacantes, Aspirantes, Artistas, Proveedores y Cartera con importador CSV), roles y policies, flujo de aprobación con notificaciones, bitácora, MFA, dashboard; commit.
4. **Fase 3 — Sitio público núcleo:** layout + páginas 1–4 y 8–16 (inicio, quiénes somos, directorio, abre-tu-negocio, eventos, boletín, afíliate, contacto, política, extras, **selector de tema y portadas de relleno bicromáticas**), filtros del directorio, mapa, formularios con habeas data y radicados; commit. Monta la capa de tokens semánticos que describe la sección 4 **antes** de escribir la primera vista: migrar 446 clases cableadas después cuesta mucho más que nacer con ellas.
5. **Fase 3b — Módulos Reunión 2:** `/empleo`, `/artistas`, `/proveedores` y `/mi-cuenta` con login de asociado y vista de cartera; commit.
6. **Fase 4 — Pagos:** interfaz + FakeGateway (con concepto `mensualidad`) + esqueleto Bold + transacciones en panel; commit.
7. **Fase 5 — Calidad:** pruebas Pest (mínimo: subadmin no puede publicar; PQR genera radicado consecutivo; inscripción exige habeas data; webhook actualiza transacción e inscripción; **importar CSV de cartera actualiza saldos**; **/mi-cuenta exige rol asociado**; **una vacante no publicada no aparece en /empleo**; rutas públicas responden 200), pint, revisión de RNF, README.
8. **Verificación final:** `php artisan migrate:fresh --seed && php artisan test && npm run build`, levantar el server y recorrer con curl las rutas públicas y el login del panel. Arregla todo lo que falle antes de reportar.

## 13. Entregable final de tu sesión

Termina con un **README.md** que incluya: qué es el proyecto, requisitos, pasos exactos para correrlo (5 comandos máximo), credenciales demo (los 3 usuarios), cómo probar el flujo de aprobación (guion de 6 pasos), cómo probar el pago simulado de un evento, **cómo probar el flujo de cartera** (login asociado → ver mora → pagar → al día) y **cómo importar el CSV de la contadora**, cómo activar Bold real con `.env`, y la lista de pendientes conocidos. Y un resumen en consola: qué se construyó, qué pruebas pasan, y las 3 cosas que priorizarías después.

## 14. Lo que NO debes hacer

- No usar WordPress, WooCommerce, plantillas compradas, page builders ni CSS genérico de framework sin personalizar.
- No dejar textos "Lorem ipsum": todo el contenido semilla es en español y del contexto del gremio.
- No inventar credenciales de Bold ni llamar APIs externas de pago; el demo usa FakeGateway.
- No usar datos personales reales de establecimientos, artistas, proveedores o personas (salvo los institucionales del punto 2).
- No publicar eventos de bares individuales en las semillas: solo eventos del gremio.
- No saltarte el flujo de aprobación "porque es un demo": es EL requisito que estamos evaluando.
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
- **G12 — Datos personales (Ley 1581)**: no hay política de retención ni supresión, el consentimiento se guarda sin evidencia (IP, agente, versión de la política aceptada), y la política publicada no nombra a los encargados que intervienen —incluida la pasarela—.

### 15.3 Dos incógnitas que solo resuelve el sandbox de Bold

Antes de tocar credenciales reales hay que confirmarlas, porque la documentación pública no basta:

1. **Unidad de `expiration_date`**: el texto dice nanosegundos y el ejemplo de la propia documentación muestra milisegundos. El código envía nanosegundos.
2. **Nombre del campo del monto en la notificación**: se prueban tres formas conocidas. Si no aparece ninguna, la conciliación del punto 8 de la sección 8 **queda inerte** y solo lo delata un aviso en `storage/logs`. Hay que mirar el log en la primera prueba real.

### 15.4 Huecos que la auditoría declaró sin cubrir

- Exportaciones CSV/Excel de Filament en recursos con datos personales (Aspirantes, Inscripciones, Mensajes).
- Los ocho recursos de Filament no auditados a fondo más allá del patrón replicado de Asociados.
- La configuración real del servidor de producción, que aún no está elegido: decide si una extensión de archivo mal escogida es XSS almacenado o ejecución de código.
- El comportamiento de la caché de permisos de spatie tras un cambio de rol en producción, con `config:cache` activo.
