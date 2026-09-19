# Estado vigente — Plataforma Web ASOBARES Capítulo Quindío

_La foto del proyecto hoy. **Se reescribe entero** al cerrar toda sesión que cambie algo: sin tachones, sin «superado», sin «esta línea decía». Lo que se cierra sale de aquí y queda contado en `bitacora.md`; lo que se decide sale de aquí y entra en «Decisiones que rigen» de `encargo.md`. Si el commit del encabezado está atrás de `main`, lee las entradas de bitácora posteriores a él y actualiza esto **antes** de tocar nada. `tests/Feature/GuardiaDelEstadoTest.php` comprueba que el commit del encabezado exista de verdad._

---

## ✅ EL SITIO VIVE EN SU DOMINIO: `https://asobaresquindio.com` (18 sep)

> **Una sola línea otra vez.** Todo lo que estaba en ramas está en `main` y en producción: el alta de afiliados reales, el calendario comunitario de Ingrid, la corrección de los webhooks de Bold, las páginas por municipio y el dominio. Producción sirve `659fc61`.
>
> **Comprobado sobre la URL pública el 18 sep, por contenido servido:**
>
> - `asobaresquindio.com` responde **200** en portada, directorio, `robots.txt`, `sitemap.xml` y `/mi-cuenta/entrar`; `www.` y `http://` redirigen con 301 al dominio sin www (lo hace Cloud).
> - El host de Cloud (`asobares-production-0jhdcz.laravel.cloud`) **redirige con 301** al dominio conservando ruta y consulta; `/up` sigue en 200 y un POST no se redirige (el webhook contesta allí mismo).
> - **`APP_URL` de producción = `https://asobaresquindio.com`** (`config:show app.url`). Hasta hoy seguía siendo el host de Cloud: los enlaces generados fuera de una petición apuntaban a la copia.
> - `SITIO_INDEXABLE` abierto: `robots.txt` sirve `Allow: /` y el sitemap con **40 URL**, todas del dominio. La portada lleva canónica al dominio y **JSON-LD de la organización y del sitio**.
> - **Google Search Console verificado** para el dominio; prueba en vivo de la portada «URL is available to Google»; **sitemap enviado** (Google leyó 33 páginas el 18 sep; hoy lista 40).
>
> ⚠️ **La redirección depende de `APP_URL`.** `RedirigirAlDominioPropio` no hace nada si `SITIO_INDEXABLE` es falsa o si `APP_URL` vuelve a ser un host `*.laravel.cloud`.

---

## ⚠️ ACCESOS DE LOS AFILIADOS: EL DOCUMENTO DEL 17 SEP YA NO SIRVE

> **El alta real está en producción.** La base tiene **61 fichas** y **39 usuarios** (conteo de filas del 18 sep, `db:show --counts`; sin PII). El documento de entrega para Natalia (Word y PDF, fuera del repositorio: lista de control, un mensaje para copiar y pegar por afiliado y tarjetas para recortar) salió del CSV del **17 sep, 15:41**.
>
> - ⚠️ **Ese documento no se envía.** Los registros de Cloud muestran **dos descargas más el 18 sep, a las 11:53 y a las 17:51** (Bogotá): `password_hash()` dentro de `DescargarAccesosProvisionales`. Cada descarga cambia las contraseñas de todas las cuentas que siguen en provisional.
> - **Plan (Sua, 18 sep):** el **domingo 20 sep** se hace una sola descarga nueva y con ese CSV se rehace el documento. Hasta entonces, nadie descarga.
> - Desde `5ba8bb1` (rama `sua/fase-a-rendimiento`) el aviso de la descarga dice cuántas cuentas cambian, que los archivos anteriores dejan de funcionar y que hay que esperar.
> - Antes de enviar, Natalia confirma tres casos del archivo: un responsable con dos establecimientos y dos correos; cuatro cuentas con el nombre del negocio en vez de una persona; correos universitarios o que no coinciden con el titular.
> - **10 fichas publicadas** en el directorio (las que lista el sitemap). La regla es que ninguna se publica sin autorización del titular: **que esa autorización exista por escrito no está registrado aquí**.
> - El **acta del alta** (se registra después del código, como el Acta 06) **sigue sin emitir**.

---

## 🔀 REPARTO DE CIERRE CON INGRID (18 sep)

> Ingrid repartió el cierre en dos bloques para no tocar los mismos archivos. **La navbar pública no la toca nadie.** Todo cambio se revisa en escritorio, tablet y móvil, claro y oscuro. Cada bloque se entrega con commit, archivos, pruebas y pendientes.
>
> - **Sua:** rendimiento y estabilidad → panel (logo al sitio, volver al listado, buscador general, campana que se cierra) → flujos (artistas, eventos por dentro, ubicación de eventos en datos) → datos institucionales (Observatorio, iniciativas, boletín, convenios, guía, proveedores) → datos definitivos (asociados, bolsa de empleo, SEO). Bold queda **congelado** hasta la ronda conjunta.
> - **Ingrid:** sistema visual público, Directorio, Aliados, Abre tu negocio, Quiénes somos, Eventos por fuera (calendario, mapa), Mi Cuenta y la presentación de convenios.
> - Se coordina antes de tocar: Eventos, Mi Cuenta, Aliados/Convenios, CSS público global, layouts y componentes compartidos.
>
> **Fase A (rendimiento) — en la rama `sua/fase-a-rendimiento`, sin integrar a `main`:**
>
> | Problema | Causa medida | Commit |
> |---|---|---|
> | «Se bloqueó dos veces» | Las dos descargas de accesos: 11,3 y 10,1 s cifrando 35 contraseñas (bcrypt 12), sin explicación en pantalla | `5ba8bb1` (aviso; el hash no se toca) |
> | Páginas públicas lentas (p95 1,3 s en el servidor) | Cada `ajuste()` era una consulta a la tabla `cache`: la portada hacía 96, 81 de ellas la misma | `faea52e`: `Cache::memo()`; portada **96 → 15** consultas |
> | Panel cargado | Los widgets de Filament consultaban cada 5 s por defecto: **1.254 de 2.418** peticiones de personas en 23 h (52 %) | `d079048`: sin sondeo en 7 widgets y 6 gráficas |
> | Primera visita tras inactividad | Hibernación de la app y de Postgres *serverless*; instancia única `flex-512mb` | Sin cambio: medir de nuevo tras integrar; subir de instancia es costo del gremio (D-12) |
> | **Pantalla negra** | **No reproducida.** Candidatos: `@view-transition` del sitio público (`resources/css/app.css`, zona de Ingrid) o la navegación SPA del panel | Falta saber dónde se vio: sitio o panel, navegador, dispositivo, modo |
>
> La cola es `sync`: cada correo sale dentro de la petición que lo dispara. En 23 h ninguna petición fue lenta por eso; el aviso de artista nuevo del bloque C suma uno más.

---

## 0. Medición

| | |
|---|---|
| Fecha | **Viernes 18 de septiembre de 2026** (Bogotá). Suite medida sobre `659fc61` el mismo día; producción comprobada tras el despliegue de 17:42 – 17:44 (hora de Bogotá) |
| `main` | `659fc61` · lo que sirve producción. `origin/main` en el mismo commit (`git ls-remote`). Desde la foto anterior (`446901e`, 17 sep) entraron 14 commits: ocho de Ingrid (`97be48c`, `46483fe`, `49b6d88`, `f04fc59`, `c8a7929`, `3884a5b` y las fusiones `735e1d8` y `341fa1f`) y seis de Sua (`781d6c4`, `66eca79`, `0aea90b`, `4f1d34b`, `7656af6`, `659fc61`). ⚠️ El hash va **primero y entre acentos graves**: `GuardiaDelEstadoTest` lee esta fila |
| Ramas | Una sola línea viva: `main`. `seo/dominio-canonico` quedó contenida en `main`. `origin/ingrid/cierre-alta-real` y `origin/cierre/visual03-directorio-login` ya no llevan nada que no esté en `main` por contenido |
| **Suite completa (18 sep, `659fc61`)** | **1.972 casos · 1.971 pasan · 1 fallo · 13.662 aserciones · 1.150 s**. El fallo es `DatosInternosDelAsociadoTest::test_la_base_de_datos_del_gremio_no_vive_en_el_repositorio`: detecta el Excel del gremio en `material/sep15material/`, carpeta **ignorada por git** (`.gitignore:67`) y fuera del repositorio público. Es del disco de esa máquina, no del código; **no se midió en clon limpio**. El corte `Premature end of PHP process` del 17 sep **no se repitió** |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5): suite, producción por `curl` y `cloud command:run` (solo lectura), registros de Cloud y Search Console por capturas de Sua |
| Producción | `https://asobaresquindio.com` · sirve `659fc61` · **53 migraciones** · PostgreSQL 17. Correo: `mail.default=smtp`, `smtp.gmail.com`, remitente `asobarespaginaweb@gmail.com` (configurado; **la entrega de un correo no se comprobó** en esta sesión). Pagos: `pagos.driver=bold` con variables de sandbox |
| **Expediente** | Esta foto y la **§59** de `bitacora.md`. `encargo.md` §13 recoge hoy el dominio (D-09), la barra en scroll (D-50) y el calendario comunitario (D-51). ⚠️ La **matriz de trazabilidad** sigue con las cifras del 8 sep. ⚠️ **Acta 09** (D-47) y **acta del alta real** sin emitir |

## 1. Qué se exige y cuándo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **Martes 22 sep — quedan 4 días** | **Entrega dura al gremio** | Cronograma firmado |
| **Hoy, vie 18 sep, 11:59:59 pm** | ⚠️ Es viernes de entrega. El expediente **no registra** qué pide la universidad esta semana: confirmarlo con el docente asesor | Docente asesor. Tarde = 0.0 |
| **Vencido: vie 11 sep** | ⚠️ **Confirmar que se envió** la presentación de la socialización final. Los archivos siguen **sin versionar** en `docs/ingenieria/entrega-2026-09-04/` | Docente asesor |
| **Vencido: vie 4 sep** | ⚠️ **Confirmar que se envió** el documento de práctica corregido. Sigue describiendo los RF de proveedores como públicos, sin banco de talento, **la barra A** y «solo eventos del gremio» | Docente asesor |
| Vencida sin fijar | **Segunda demostración** en el teléfono del directivo (D-20). Ya se puede hacer **sobre el dominio** | Directivo del capítulo |
| 14 – 18 sep (S8) | ✅ Dominio y SSL. ❌ Capacitación y **Acta 02 firmada**; manual actualizado | Cronograma firmado |
| Por confirmar | Cierre del corte 3 y PDF final a `proyectosing@cue.edu.co` | Docente asesor |

## 2. Inventario por frente

### 2.1 Producto

| Ref. | Estado | Qué falta, y de quién depende |
|---|---|---|
| OBS3-01 a 06, 08, 09, 12, 13, 14 | ✅ Cerrados | — |
| OBS3-07 (fotos y video) | ⚠️ Video en el sitio; imágenes generadas permitidas (§13, 16 sep). Fotos reales de siete establecimientos en `material/sep15material/` (ignorada) | Pies de foto y **autorización de imagen (D-03)** |
| OBS3-10 (enlaces al trámite) | ⚠️ De veinticinco, **dieciocho abren la portada de la alcaldía** | Las **7 URL de Armenia** (D-04) |
| OBS3-11 | ⚠️ Código puesto | Texto de «Quiénes somos» y cargos (D-05) |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar** (D-01) |

**Calendario comunitario (Ingrid, `49b6d88`, en producción).** Formulario público en `/eventos/calendario` que **publica al instante**, sin cuenta y sin revisión previa, con imagen opcional de hasta 5 MB; la moderación es posterior, desde el panel (pestañas por origen) y con aviso por correo al gremio. No pide datos personales del remitente. Hoy no hay ningún evento en producción. **Aprobado así por Natalia el 18 sep** (D-51, encargo §13): publicar primero y moderar después. Falta el acta escrita.

**Páginas por municipio (`7656af6`).** `/directorio/municipio/{municipio}` y `/abre-tu-negocio/{municipio}`, con título, descripción y canónica propios; un municipio sin negocios publicados muestra un estado honesto y se marca `noindex`.

**Barra de escritorio en scroll (`66eca79`).** Sin velo y sin banda: el velo pintaba una franja clara de canto a canto sobre la foto del hero. D-50 cerrada (encargo §13).

**Panel.** Tablas operativas con mejor presentación (`f04fc59`). El ajuste del riel comprimido en el teléfono (`0aea90b`) **se revirtió** (`4f1d34b`) porque en producción dejaba el último módulo vacío: el desfase del riel frente al cajón sigue como estaba. La columna de códigos de recuperación de la app de autenticación pasó de `json` a texto (`781d6c4`): Postgres rechazaba el blob cifrado al registrar la app.

**Contenido.** La Misión de «Quiénes somos» vuelve a la redacción del equipo (`0aea90b`, encargo §13, 17 sep). ⚠️ **Falta reflejarlo en producción**, que conserva el texto sembrado hasta el 15 sep.

### 2.2 Pagos (Bold)

| Qué | Estado |
|---|---|
| Correlación de webhooks del API Link | ✅ `46483fe` (Ingrid): la transacción guarda el link de Bold (migración `2026_09_17_000000`) |
| Webhook de pruebas aislado | ✅ `3884a5b`: `/webhooks/bold/pruebas`, no toca transacciones ni aplica pagos; solo acepta la referencia de `BOLD_SANDBOX_WEBHOOK_LINK` |
| Validación en sandbox (18 sep) | ✅ Pago simulado aprobado de $10.000. **Un** `POST /webhooks/bold/pruebas` a las **13:34:42** (Bogotá), `bold-webhook/1.0`, **200** en 144 ms; ninguna excepción en la ventana 12:40 – 13:45. Entre 12:44 y 13:34 no llegó ninguna otra petición: comparar con la hora del pago en el panel de Bold |
| Salida a producción real | ❌ Sigue en sandbox. Depende de D-10 y D-44 |

### 2.3 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| **Guía normativa** | ✅ 12 municipios y 151 fichas en producción, ahora con página propia por municipio. Cero costos | Confirmación por escrito de la dirección (D-21) y las 7 URL de Armenia (D-04) |
| **Directorio** | ⚠️ **61 fichas** en la base de producción; **10 publicadas** | Autorización escrita de cada titular publicado; filas 57, 70, 71 y 72 del archivo del gremio con municipio fuera del catálogo |
| Ajustes | ✅ 200 en producción | `hero_frase_corta` de cosecha propia: **D-29** |
| Franja «El gremio en cifras» (Acta 05) | ✅ Código en producción, vacía | Firmar el Acta 05 y teclear las cuatro cifras |
| Aliados | ⚠️ 23 del catálogo nacional, ninguno del Quindío | D-18 |
| Beneficios e iniciativas | ✅ 5 y 5, de documento oficial | D-39 |
| «Quiénes somos» | ⚠️ Misión del equipo en código, no en producción | Historia, visión, cargos y junta (D-05) |
| Cifra pública de afiliados | ⚠️ El sitio dice 60; la base de producción tiene 61 fichas | Natalia (D-18) |

### 2.4 Infraestructura

| Qué | Estado |
|---|---|
| Dominio y SSL | ✅ `asobaresquindio.com` con https; `APP_URL` al dominio; host de Cloud en 301 |
| Indexación | ✅ Abierta; Search Console verificado; sitemap enviado |
| Posicionamiento | ⚠️ Lo que falta es del gremio: **Perfil de Empresa en Google** con el correo de la organización, enlace desde `asobares.org` y desde Instagram. Pedir indexación de la portada y las páginas clave en Search Console |
| Despliegues del 18 sep | ✅ `659fc61` (17:42:45 → 17:44:12, Bogotá) y antes `3884a5b`, `341fa1f`, `7656af6`. **El push a `main` despliega solo** y **no siembra nada** |
| Correo saliente | ⚠️ Configurado con Gmail (`asobarespaginaweb@gmail.com`). **No comprobado que salga**: radicar una PQR de prueba y ver el acuse; buscar `TransportException` en los registros |
| Bucket | ❌ Sin crear (D-13) |
| Scheduler | ✅ Tres purgas diarias y `subidas:depurar` cada hora (entró con el alta) |
| Dispositivos reales | ⚠️ Sin ver iOS/Safari ni el panel bajo Filament 5 en un teléfono |
| Registros de Cloud | `cloud environment:logs` devuelve **máximo 100 líneas** por consulta: para una ventana larga, pedir tramos cortos |
| Repositorio | ⚠️ `Jsua3/asobares`, público. Cero PR y cero CI. Sin segundo administrador (D-12) |

### 2.5 Datos personales

| Qué | Estado |
|---|---|
| **Accesos provisionales** | ⚠️ CSV con contraseñas en texto plano y documento de entrega en la máquina de Sua, **fuera del repositorio**. Se borran al terminar el reparto |
| **Calendario comunitario** | ⚠️ No recoge datos del remitente y **publica imágenes sin revisión previa** (aprobado por Natalia, D-51). La moderación tiene que retirar pronto cualquier foto con personas identificables, porque sale sin autorización de imagen (§9) |
| Imágenes generadas por IA | ✅ Permitidas; no se presentan como fotografía de un local real |
| Fotos de siete establecimientos reales | ⚠️ `material/sep15material/` (ignorada); requieren D-03 |
| Fichas de asociados | ⚠️ 10 publicadas en producción: confirmar la autorización de cada titular |
| Base de establecimientos (`.xlsx`) | ✅ Fuera del repositorio (ignorada); su presencia en el disco es la que hace fallar la guardia de la suite |
| Política de tratamiento de datos | ❌ D-19; bloquea al banco de talento |
| Retención automática | ✅ Tres purgas diarias y la horaria de subidas |

### 2.6 Académico

| Qué | Estado |
|---|---|
| Corte 1 | ✅ 5.0 |
| Corte 2 | ✅ Entregado a tiempo el 21 ago |
| Corte 3 (60 %) | ⚠️ Sin confirmación de envío. El documento no recoge el alta real, el calendario comunitario, Bold en sandbox ni el dominio |
| Constancias | ✅ Actas 01, 04, 05, 07 y 08 emitidas; Formato 03 y planeador. ❌ Acta 02 sin firmar (S8), **Actas 04 y 05 sin firmar**, **Acta 06** (D-26), **Acta 09** (D-47) y **acta del alta real** y **acta del calendario comunitario** (aprobado por Natalia el 18 sep) sin emitir |

## 3. Registro único de decisiones pendientes

Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md`. **El 18 sep entraron cuatro:** el dominio propio en producción (**cierra D-09**), la barra de escritorio en scroll sin velo ni banda (**cierra D-50**), el calendario comunitario que publica primero y se modera después (**cierra D-51**) y el bloque C del reparto —artistas que publican al instante y eventos con ubicación—, los dos últimos aprobados por Natalia. Contadas en la bitácora §59. **D-28 no sale:** las cuentas existen en producción, pero los accesos no se han repartido.

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| **D-28** | **Alta de credenciales de afiliado.** Código, importación y cuentas **en producción** (61 fichas, 39 usuarios). Faltan: la descarga única del **domingo 20 sep** (el documento del 17 sep quedó sin validez por las dos descargas del 18 sep), **repartir los accesos** y el acta de la ampliación | Natalia + Sua + Ingrid | 3 sep | Cuentas en producción; reparto: — |
| **D-47** | **Acta 09**: capa visual, salto de versión y eventos de aliados | Sua | 9 sep | — |
| **D-21** | **Municipios 2 a 12 de la guía**: la dirección confirma por escrito que es la versión vigente | Natalia | 1 sep | Materia prima el 15 sep; confirmación: — |
| **D-46** | **Los dos tokens compartidos de la capa visual** (`--asb-apagado`, `--asb-accion`) | Ingrid | 9 sep | — |
| **D-44** | **¿Cuál es la cuota vigente?** La cartera implica $70.000/mes; el formulario oficial, $30.000 dos meses y $50.000 después. Nada se cobra de verdad hasta que Natalia lo diga por escrito | Natalia | 9 sep | — |
| **D-45** | **Los 41 nombres de la cartera contra la base de asociados** | Natalia + Sua | 9 sep | — |
| **D-38** | **La lista real de la auditoría funcional** | Ingrid | 8 sep | — |
| **D-39** | **Clasificar los cinco beneficios por alcance** | Natalia | 8 sep | — |
| **D-40** | **Aprobar los perfiles del banco de talento** | Natalia + secretaría | 8 sep | — |
| **D-41** | **El reparto que propone el plan de trabajo invierte el registrado** | Sua + Ingrid | 8 sep | — |
| **D-31** | **`prefers-reduced-transparency` en un equipo real** | Sua | 5 sep | — |
| **D-32** | **Idiomas como subsistema propio**: acta antes de codificar | Natalia + Sua | 3 sep | — |
| D-26 | **Acta 06** de la ampliación de las bolsas | Sua + Ingrid | 3 sep | — |
| D-27 | **Política de tratamiento y los 7 perfiles** que aceptaron otra versión | Natalia + aliado jurídico | 3 sep | — |
| D-29 | **`hero_frase_corta`** y la banda de tres videos | Ingrid | 3 sep | — |
| D-01 | Firma del **Acta 04** y del **Acta 05** con sus cuatro cifras | Natalia + directivo | 30 ago / 1 sep | — |
| D-03 | **Pies de foto y autorización de imagen** | Natalia | 26 ago | 19 del gremio, 1 sep ✅ · el resto: — |
| D-04 | **Las 7 URL de trámite** de Armenia | Natalia / Alcaldía | 28 ago | — |
| D-05 | **Texto propio de «Quiénes somos»**; nombres y cargos | Natalia + Nacional | 5 ago / 28 ago | Falta aprobación |
| D-06 | **Logos** de aliados y cuáles aplican en el Quindío | Natalia | 31 ago | Institucional resuelto el 9 sep |
| D-07 | **Correo saliente** | Natalia + Sua | 15 ago | Configurado con Gmail; falta comprobar que llega |
| D-10 | **Pasarela**: «solo Bold» por escrito; PSE o QR; ¿se cobra alguna vez a nombre de un aliado? | Natalia + contadora | 28 ago | Sandbox validado el 18 sep; decisión: — |
| D-11 | **Cartera**: cómo se actualiza el archivo de la contadora | Luisa + Natalia | 28 ago | Archivo recibido el 9 sep |
| D-12 | **Titularidad de la infraestructura**: facturación, Natalia miembro, segundo admin en GitHub; **Search Console y el futuro Perfil de Empresa, a nombre del gremio** | Sua + Natalia | 30 ago | — |
| D-13 | **Bucket y fotos pendientes** | Sua | 31 ago | — |
| D-14 | **Marca de procedencia en el contenido sembrado** | Sua | 1 sep | — |
| D-18 | **Confirmaciones del plan del material**: condiciones de los 18 aliados departamentales y la cifra pública de afiliados | Natalia | 26 ago | Aliados de hecho 9 sep ⚠️ · condiciones: — |
| D-19 | **Política de tratamiento de datos** | Natalia / aliado jurídico | 5 ago | — |
| D-20 | **Fecha de la segunda demostración** | Directivo + Natalia | 28 ago | — |
| D-43 | **¿Vuelve la campana del panel?** | Sua + Natalia | 9 sep | — |

## 4. Deuda diferida a propósito

No se «arregla de paso»:

- **El riel comprimido del panel en el teléfono** sigue desfasado frente al cajón desplegado; el arreglo del 17 sep se revirtió porque dejaba el último módulo vacío en producción.
- **La guardia de la base del gremio falla en la máquina de Sua** mientras el Excel viva en `material/sep15material/`. El archivo está bien donde está (ignorado); la prueba no distingue carpeta ignorada de repositorio.
- **Los tokens de radio compartidos** (`--asb-radio-celda`, `--asb-radio-pieza`…) no se crearon.
- **El conmutador de `/eventos` en el teléfono** parte «Calendario» a una segunda línea dentro de la píldora.
- **La cabecera de la guía en claro** funde una foto nocturna sobre crema; el gusto lo confirman Sua e Ingrid.
- **Ninguna prueba ve una imagen**: procedencia (C2PA) y peso se miran a mano.
- **Guardias sobre `.js`** acotadas con `[\s\S]*`: el patrón puede repetirse.
- **`hamcrest` 3.0.0** entra por Mockery.
- **De la barra pública B:** `$rol`/`$prefijoRol` en `menu-usuario.blade.php` son dos `match` que recalculan lo mismo.
- **Preexistente:** tabular hacia el header estando desplazado devuelve la página al tope (Chromium).
- **El correo de ficha de bolsa publicada enlaza a `/proveedores`**, que no nombra al proveedor.
- El consecutivo de PQR bajo concurrencia falla cerrado. No existe `lang/` (D-32).
- **`ListCarteras::importar`**: cuerpo de notificación y temporal fuera del lote del alta; el temporal lo recoge `subidas:depurar`.
- Cuatro `index.lock.huerfano*` y `.git/huerfanos-cowork-2026-09-01/` en `.git/`: **los borra Sua a mano**.
- **`.env.staging.example`** todavía nombra `smtp.resend.com`; el correo real es Gmail.

## 5. Cifras medidas del árbol

Medidas el **18 de septiembre de 2026 sobre `659fc61`**, que es lo desplegado. Vuelve a medirlas antes de citarlas en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | **692** | `git rev-list --count main` |
| Migraciones | **53** | `ls database/migrations/*.php` (producción: 53 filas en `migrations`) |
| Modelos | **24** | `ls app/Models/*.php` |
| Sembradores | **21** | `ls database/seeders/*.php` |
| Fábricas | **19** | `ls database/factories/*.php` |
| Archivos de prueba | **164** | `find tests -name '*Test.php'` |
| Métodos de prueba | **1.517** | `grep -rhE '^\s*public function test_' tests` |
| Vistas Blade | **110** | `find resources/views -name '*.blade.php'` |
| Componentes públicos | **30** | `ls resources/views/components/publico/*.blade.php` |
| Hojas editoriales | **8** | `ls resources/css/*-editorial.css` |
| Panel | **21** recursos · **6** páginas · **22** policies · **16** archivos de widgets | `find app/Filament/Resources -maxdepth 1 -mindepth 1 -type d`, `ls app/Filament/Pages/*.php`, `ls app/Policies/*.php`, `find app/Filament -path '*Widgets*' -name '*.php'` |
| Comandos de Artisan propios | **7** | `ls app/Console/Commands/*.php` |
| Enums | **22** | `ls app/Enums/*.php` |
| Controladores públicos | **21** | `ls app/Http/Controllers/Publico/*.php` |
| Middleware propio | **5** | `ls app/Http/Middleware/*.php` |
| Archivos de configuración | **18** | `ls config/*.php` |
| Rutas GET propias | **99** | `php artisan route:list --method=GET --except-vendor --json` |
| **Suite completa (18 sep)** | **1.972 casos · 1.971 pasan · 1 fallo (del disco, ver §0) · 13.662 aserciones · 1.150 s** | `php artisan test --compact` |
| URL del sitemap | **40** | `curl https://asobaresquindio.com/sitemap.xml` |
| Producción: filas | **61** fichas · **39** usuarios · **0** eventos · **0** transacciones · **200** ajustes · **151** fichas de la guía | `php artisan db:show --counts` por `cloud command:run` |
| Despliegue del 18 sep | `659fc61`: 17:42:45 → 17:44:12 (Bogotá), **87 s** | `cloud deployment:list` |

## 6. Lo siguiente, en orden

1. **Domingo 20 sep: una sola descarga nueva de los accesos** y, con ese CSV, rehacer el documento de Natalia; después, repartir con los tres casos dudosos del archivo confirmados. **Nadie descarga antes.**
2. **Integrar la Fase A** (`sua/fase-a-rendimiento`: `faea52e`, `d079048`, `5ba8bb1`) cuando Ingrid la revise, medir de nuevo en producción y seguir con la **Fase B** (panel). Averiguar dónde se vio la pantalla negra.
3. **Search Console:** pedir indexación de la portada y de 4 o 5 páginas clave; revisar **Indexing → Pages** en una semana.
4. **Perfil de Empresa en Google** con Natalia, con la guía paso a paso del 18 sep (fuera del repositorio) y la cuenta del gremio; Sua como administrador.
5. **Comprobar el correo**: una PQR de prueba y su acuse; registros sin `TransportException`.
6. **Emitir por escrito las ampliaciones aprobadas o construidas:** el Acta 09 (D-47), el acta del alta real, la del calendario comunitario y la del bloque C (artistas al instante y ubicación de eventos).
7. **Confirmar las autorizaciones** de las 10 fichas publicadas.
8. **Reflejar la Misión del equipo en producción** (`quienes_mision`).
9. **Confirmar los envíos a la universidad** (4 y 11 sep) y qué pedía el viernes 18.
10. **Fijar la demo 2** (D-20) sobre el dominio.
11. **Una sola reunión con Natalia** con la tabla del §3 impresa: cuota (D-44), pasarela (D-10), cifra de afiliados (D-18), titularidad de las cuentas de Google (D-12).
12. **Entrega del 22 sep:** capacitación y Acta 02, manual en PDF, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
13. **Rehacer la matriz de trazabilidad** y el documento de práctica con lo de esta semana.
