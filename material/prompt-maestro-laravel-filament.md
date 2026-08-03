# PROMPT MAESTRO v3 — Plataforma Web ASOBARES Capítulo Quindío (Laravel 13 + Filament 4)

> Copia TODO el contenido desde "## 1. Tu misión" hasta el final y pégalo en Claude Code, dentro de una carpeta vacía. Requisitos previos en tu máquina: PHP ≥ 8.3 con Composer, Node ≥ 20 y Git. Si algo falta, Claude Code puede instalarlo primero.
>
> **v2 (1 ago 2026):** incorpora el alcance ampliado de la Reunión 2 con la directiva — bolsa de empleo, directorio de artistas, bolsa de proveedores, estado de cartera del asociado, preferencia PSE y guía normativa reforzada. Los módulos nuevos se construyen en versión mínima demostrable; el alcance definitivo lo fija el documento de requisitos v2 firmado.
>
> **v3 (3 ago 2026) — PROMPT YA EJECUTADO:** el prototipo existe en `asobares-web/` (repo git propio) y está completo — fases 0–5 terminadas, suite de pruebas en verde (191 pruebas: 184 pasan, 7 omitidas, 0 fallos) y correcciones posteriores aplicadas (identidad del manual de marca, contenido del TED gremial, login MFA por correo, imágenes y 403 de `/mi-cuenta`). **Stack real: Laravel 13.23 + Filament 4.12 + Livewire 3.8 sobre PHP 8.5.9** — el prompt pedía Laravel 12, pero el instalador oficial ya solo entrega Laravel 13; el starter kit de Livewire se descartó porque trae Livewire 4 y Filament 4 exige `livewire/livewire ^3.5`, así que el proyecto se creó con `laravel new --no-authentication` y el login de `/mi-cuenta` se escribió a mano. Filament 5 (publicado el 31 jul 2026) se descartó por demasiado nuevo para la entrega del 22 sept. Las menciones de versión de abajo ya están corregidas; este documento queda como registro del encargo y receta de relanzamiento.

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

**Rendimiento (RNF-02):** imágenes servidas en webp con thumbnails, `loading="lazy"`, sin librerías JS pesadas, CSS de Vite build. Objetivo: home < 2,5 s en móvil 4G.

## 8. Pagos (Bold, demostrable sin credenciales)

- Crea una interfaz `PaymentGateway` con dos implementaciones:
  - `BoldGateway`: estructura real — crea un **link de pago por API** (endpoint y llaves desde `.env`: `BOLD_API_KEY`, `BOLD_SECRET`, `BOLD_SANDBOX=true`) y recibe confirmación en `POST /webhooks/bold` **verificando la firma**. Déjalo implementado según la documentación pública de developers.bold.co, pero sin credenciales.
  - `FakeGateway` (por defecto en el demo, `PAYMENT_DRIVER=fake`): genera una página interna `/pago-simulado/{transaccion}` con la marca, selector decorativo de método (**PSE / Tarjeta** — el gremio prefiere PSE; la cuenta real es Itaú) y botones "Pagar" y "Rechazar" que disparan el mismo flujo del webhook. Así los flujos completos **inscripción → pago → confirmación** y **mi-cuenta → pagar mensualidad → cartera al día** se pueden demostrar en vivo.
- Toda transacción queda registrada (referencia, concepto, monto, método, estado, payload) y visible en el panel (RF-34). Nunca marques una inscripción como confirmada ni una cartera como saldada sin transacción aprobada.

## 9. Seguridad y Habeas Data (no negociable)

- Validación estricta del lado servidor en TODOS los formularios públicos + rate limiting por IP + honeypot antispam simple (sin captcha de terceros en el demo).
- Checkbox de **autorización de tratamiento de datos obligatorio** en cada formulario que capture datos (inscripciones, aspirantes, afiliación, contacto, proveedor), con enlace a `/politica-de-datos` y `consentimiento_at` guardado con timestamp (Ley 1581 de 2012).
- Contraseñas hasheadas (por defecto de Laravel), MFA en el panel, sesiones seguras, CSRF en todo, escape de salida (nada de `{!! !!}` sobre input de usuarios; el rich text de noticias se sanea). El `video_url` de artistas se valida como URL de YouTube y se embebe solo con el ID extraído.
- Archivos subidos: validar tipo/tamaño; imágenes reprocesadas (nunca servir el binario original de un upload). Los adjuntos de requisitos (formatos oficiales) se sirven con nombre limpio.
- La importación CSV de cartera valida columnas, tipos y asociado existente; muestra errores por fila sin abortar todo.
- `.env.example` completo y documentado; ningún secreto en el código ni en el repo.

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
- [ ] Accesibilidad base: contraste AA sobre fondo oscuro, alt en imágenes, labels en formularios, foco visible.
- [ ] Código en inglés, UI en español; PSR-12 (`laravel/pint`).
- [ ] Git: repositorio inicializado, commits pequeños y descriptivos por fase.

## 12. Orden de trabajo (síguelo)

1. **Fase 0 — Setup:** **fetch y sigue https://laravel.com/for/agents** (verificar `php`/`composer`/`laravel`; instalar con php.new lo que falte — ⚠️ en Windows el build de php.new no trae `intl` ni `gd`, que Filament exige: usar el build oficial de windows.php.net); crear el proyecto con SQLite + npm + **Boost**, **sin starter kit**, y cargar sus guías; instalar Filament y paquetes; configurar Tailwind, fuentes, colores; commit.
2. **Fase 1 — Datos:** migraciones + modelos + factories + seeders completos (incluidas las entidades nuevas); `php artisan migrate:fresh --seed` sin errores; commit.
3. **Fase 2 — Panel:** recursos Filament (incluidos Vacantes, Aspirantes, Artistas, Proveedores y Cartera con importador CSV), roles y policies, flujo de aprobación con notificaciones, bitácora, MFA, dashboard; commit.
4. **Fase 3 — Sitio público núcleo:** layout + páginas 1–4 y 8–14 (inicio, quiénes somos, directorio, abre-tu-negocio, eventos, boletín, afíliate, contacto, política, extras), filtros del directorio, mapa, formularios con habeas data y radicados; commit.
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
