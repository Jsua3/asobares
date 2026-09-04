# Estado vigente — Plataforma Web ASOBARES Capítulo Quindío

_La foto del proyecto hoy. **Se reescribe entero** al cerrar toda sesión que cambie algo: sin tachones, sin «superado», sin «esta línea decía». Lo que se cierra sale de aquí y queda contado en `bitacora.md`; lo que se decide sale de aquí y entra en «Decisiones que rigen» de `encargo.md`. Si el commit del encabezado está atrás de `main`, lee las entradas de bitácora posteriores a él y actualiza esto **antes** de tocar nada. `tests/Feature/GuardiaDelEstadoTest.php` comprueba que el commit del encabezado exista de verdad._

---

## ⚠️ ARREGLO PENDIENTE — CORREO SALIENTE (SMTP). SE HACE EL DÍA QUE SE TENGA DELANTE LA CUENTA DE GOOGLE DEL GREMIO

> **Qué está roto hoy.** La aplicación no tiene por dónde mandar correo: nunca se contrató el SMTP (D-07). No sale el código del segundo factor por correo (al panel se entra con la app TOTP), ni los acuses de PQR y de postulación, ni el aviso al establecimiento, ni los correos de vacante aprobada, vacante devuelta y ficha publicada. Desde `707e21e` y `07a3033` **nada se rompe** por eso: los formularios públicos guardan y avisan que el acuse no salió, y el panel publica y avisa en amarillo que el correo no salió; cada fallo queda reportado en el registro.
>
> **Por qué no sirve lo que decía el expediente (Resend).** El DNS de `asobares.org`, medido el 1 sep 2026, tiene el correo en **Google Workspace**, SPF solo para Google y Mailgun, DKIM de Google y de Brevo, y **DMARC `p=reject`**. Un remitente `@asobares.org` que salga por Resend, Postmark o una cuenta nueva de Brevo **lo rechazan los receptores** —no va a spam, no llega— mientras la Nacional, que administra ese DNS, no añada los registros del proveedor. El capítulo no administra ese DNS. `.env.staging.example` todavía trae `smtp.resend.com`: no copiarlo.
>
> **Qué se hace, con la cuenta `asobaresquindio@asobares.org` abierta (Natalia + Sua, media hora):**
>
> 1. En la cuenta de Google: activar la **verificación en dos pasos** y crear una **contraseña de aplicación** (Seguridad → Verificación en dos pasos → Contraseñas de aplicaciones). Si la opción no aparece, el administrador del Workspace —la Nacional— la tiene bloqueada: pasar a la opción B.
> 2. En Laravel Cloud, variables del entorno: `MAIL_MAILER=smtp` (ya está), `MAIL_SCHEME=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_USERNAME=asobaresquindio@asobares.org`, `MAIL_PASSWORD=<la contraseña de aplicación, sin espacios>`, `MAIL_FROM_ADDRESS=asobaresquindio@asobares.org`, `MAIL_FROM_NAME` con el nombre del gremio. El remitente tiene que ser el mismo buzón que autentica: Google reescribe cualquier otro.
> 3. **Redesplegar.** Una variable nueva no llega al proceso vivo hasta el siguiente despliegue.
> 4. Probar, en este orden: radicar una PQR de prueba y comprobar que el aviso dice «Te enviamos el acuse» y que el acuse llega; entrar al panel con el código por correo; postularse a una vacante de prueba y ver los dos correos; aprobar una vacante de prueba y ver el aviso verde «Vacante publicada» sin el «pero». Mirar `cloud environment:logs` buscando `TransportException`.
> 5. Actualizar `.env.staging.example` y el runbook §6.3; pasar D-07 a «Decisiones que rigen» del encargo; **borrar este bloque** del estado.
>
> **Opción B**, si Google no deja crear contraseñas de aplicación: credenciales SMTP de la cuenta de Brevo o de Mailgun de la Nacional, que ya firman o están en el SPF del dominio. **Opción C**: la Nacional añade los registros de Resend. Las dos dependen de la Nacional; la A no.
>
> El límite de Workspace por SMTP es del orden de 2.000 correos al día por buzón; el sitio manda un puñado.

---

## 0. Medición

| | |
|---|---|
| Fecha | Noche del jueves 3 de septiembre de 2026 (Bogotá) |
| `main` | `f83c9ea` · 292 confirmaciones · este archivo entra en el commit siguiente. **`origin/main` al día** |
| Rama de trabajo | Ninguna. Las dos ramas de la Persona 2 entraron a `main` por avance rápido; `origin/p2-redisenio-visual`, `origin/p2/acceso-asociados` y `origin/p2-directorio` siguen publicadas y ya no aportan nada |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5). **La suite sí se ejecutó** (§5) y el video **sí se miró en el navegador** con la página servida |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud` responde **200** en 2,46 s en frío (3 sep). **Está sirviendo `6f24ff4`**: no tiene ni el rediseño, ni las bolsas detrás de la sesión, ni el video |

## 1. Qué se exige y cuándo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **HOY jue 3 – vie 4 sep, 11:59:59 pm** | Documento de práctica corregido según la revisión CG del 31 ago, en `.docx` con sus anexos, en el hilo «Entrega de documento de practicas». **Ya está corregido y puesto en el repositorio** (`docs/ingenieria/Semana 7 - Documento - Juan Jose Sua - correccion.docx`); falta enviarlo, y falta que describa el sistema que de verdad hay (§2.5) | Docente asesor. Tarde = 0.0 |
| **Entre el 4 y el 11 de sep** | **Segunda demostración con la capa visual levantada**, sobre la URL pública, en el teléfono del directivo. Fecha exacta sin fijar (pedir jue 10 o vie 11) | Directivo del capítulo (`R24 04:52`) |
| 7 – 11 sep | Pruebas en dispositivos reales y corrección de lo que salga | Cronograma firmado (S7) |
| 14 – 18 sep | Dominio, SSL, capacitación y Acta 02 firmada; manual actualizado | Cronograma firmado (S8) |
| **22 sep** | **Entrega dura al gremio** | Cronograma firmado |
| Por confirmar | Fecha de cierre del corte 3 y del PDF final a `proyectosing@cue.edu.co` | Docente asesor |

## 2. Inventario por frente

### 2.1 Producto — los catorce señalamientos del gremio (acta 3, 28 ago)

| Ref. | Estado | Qué falta, y de quién depende |
|---|---|---|
| OBS3-01, 02, 04, 05, 06, 08, 12, 13, 14 | ✅ Cerrados con commit (31 ago, §30.1) | — |
| OBS3-03 | ✅ Cerrado por decisión (1 sep): el sitio arranca en el tema del dispositivo, `system` | — |
| OBS3-02 (medio del hero) | ✅ **Cerrado el 3 sep** (`f83c9ea`): la ranura tiene el video institucional. Bucle de 10 s sin audio, 1280×768, 1,48 MB, fundido a negro en los dos extremos, póster propio de 27 KB, y **versionado**, que es lo único que llega a producción | Medir contraste del rótulo en los dos temas con el video corriendo |
| OBS3-07 (fotos y video del gremio) | ⚠️ **Mitad cerrada**: el video ya está en el sitio. Las 19 fotografías siguen sin usarse | Pies de foto (D-03) y colocarlas en la franja visual |
| OBS3-09 (bolsa de empleo) | ✅ **Cerrado el 3 sep**: Sua autorizó que el afiliado consulte aspirantes. `/mi-cuenta/aspirantes` muestra nombre, cargo, experiencia, **teléfono con enlace de WhatsApp y correo** a todo establecimiento afiliado con cuenta. **Deja abierto un frente legal: §2.4** | — |
| OBS3-10 | ⚠️ Código puesto | Las **7 URL de trámite** de Armenia. Insumo del gremio (D-04) |
| OBS3-11 | ⚠️ Código puesto (todo editable) | El **texto propio** de «Quiénes somos» y nombres/cargos confirmados (D-05) |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar** (D-01) |

De catorce, **doce cerrados y dos vivos** (10 y 11); ninguno de los dos se cierra escribiendo código.

**Lo que entró el 3 de septiembre desde las ramas de la Persona 2** (`p2-redisenio-visual` + `p2/acceso-asociados`, fusionadas a `main` por avance rápido):

- **Capa visual**: hero editorial con tarjeta audiovisual, escena fotográfica, barra de tema lateral, banda de videos en la portada, 484 líneas nuevas de `app.css` y tokens de movimiento. Es la franja de diseño que llevaba semanas sin hacerse.
- **Las bolsas dejan de ser públicas**: los contactos de proveedores pasan a `/mi-cuenta/proveedores` y el banco de talento a `/mi-cuenta/aspirantes`, los dos detrás de `['auth','rol.asociado']`. `/proveedores` sigue pública e indexable pero sin un solo contacto: explica la bolsa y cuenta cuántos hay por categoría. `/empleo` no se toca, a propósito: quien busca trabajo tiene que poder ver la vacante.
- **Se escribió sin PHP delante y no se ejecutó nada.** Al fusionarla salieron dos fallos reales, arreglados en `f83c9ea`: `hero_frase_corta` y `hero_video_rotulo` estaban sembradas y exigidas por la prueba sin que ninguna vista las pintara —la tarjeta del video salía muda—, y `VerificacionDeProveedoresTest` abría la sesión del afiliado antes de sembrar, así que el observer degradaba las fichas a `pendiente_aprobacion` (RF-37) y el directorio salía vacío.

### 2.2 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| **Los 126 ajustes sembrados** | ⚠️ En producción hay **109**. Los **17 nuevos** de la capa visual (`hero_frase_corta`, `hero_resumen_corto`, los tres `hero_video_*` y los doce `portada_video*`) **no existen todavía en la base** | Correr `ContenidoOficialSeeder` una vez tras el despliegue. Dieciséis traen texto de respaldo en la vista y se ven igual; `hero_frase_corta` no lo lleva a propósito —inventarle un valor sería contenido no oficial— y **no se pinta hasta que alguien la teclee** |
| **Franja «El gremio en cifras»** (D-25, Acta 05) | ✅ Código en `a408afc`, vacía de fábrica, no se pinta sin cifras | **Firmar el Acta 05**; fijar las cuatro cifras y su fuente con Natalia; teclearlas |
| **Banda de videos de la portada** | ⚠️ Tres huecos de video con títulos de fábrica («La noche se mueve», «Rutas del gremio», «Agenda viva») y pósters tomados de fotos de asociados. **Hay un solo video y los tres `src` están en `null`** | Decidir si la banda se queda con un hueco real o se recorta a lo que existe. Contradice «promesas ajustadas a lo que existe», que era la regla de la portada |
| Guía normativa | ⚠️ **1 municipio de 12** (Armenia) | Los otros 11: orden y fuente (D-21). **Formatos oficiales por entidad: sin llegar.** Los dos PDF de `storage/app/private/formatos/` son los de ejemplo del prototipo y no van a producción |
| Portada | ✅ Todo texto editable | Las 19 fotos autorizadas siguen sin colocarse |
| Aliados | ✅ 23 sembrados del catálogo oficial | Logos en buena resolución (D-06); cuáles aplican al Quindío (D-18) |
| Beneficios e iniciativas | ✅ 5 y 5, de documento oficial | — |
| «Quiénes somos» | ⚠️ Editable entero; texto provisional | Texto propio, nombres y cargos (D-05, D-18) |
| Directorio | ⚠️ **0 fichas publicadas** en producción (correcto sin autorizaciones). En local, 41 en borrador | Importar la base de 48 con `asociados:importar` **desde `D:/Sua_Files/material-asobares/`**, que es donde vive; autorización de cada titular |
| Boletín laboral (Ley 2466 de 2025) | ❌ Sin publicar | Redactarlo; revisión del aliado jurídico (D-18) |
| Formulario oficial de registro | ❌ No está en `/afiliate` | Descargable (opción A) o en línea por pasos (B, Fase II) (D-18) |
| Certificado de afiliación | ❌ Molde en `nuevomaterial/` | Es ampliación (D-18) |
| Cifra pública de afiliados | ⚠️ El sitio dice 60; la base 48; el directivo 60 | La fija Natalia (D-18) |

### 2.3 Infraestructura

| Qué | Estado |
|---|---|
| Sitio | ✅ **200** sobre PostgreSQL 17.11, 39 migraciones aplicadas. **Sirviendo `6f24ff4`: le faltan cinco commits** |
| Despliegue pendiente | ❌ **`main` no está subido.** Sin `push` no hay despliegue, y sin despliegue no hay ni rediseño, ni bolsas cerradas, ni video |
| Video del hero en producción | ✅ Resuelto por diseño: `public/videos/` **se versiona** y viaja con el código. No depende del bucket. `VideoDelHeroTest` vigila que `.gitignore` no vuelva a taparlo |
| Cuenta de Laravel Cloud | ✅ Existe, con medio de pago del gremio. ⚠️ La organización se llama `juan-sua`: comprobar la facturación y añadir a Natalia (D-12) |
| Correo saliente (SMTP) | ❌ **Sin contratar: ver el bloque de arriba.** Con el transporte caído ya no se rompe nada |
| Bucket | ❌ Sin crear. Ya **no** condiciona el video; sigue condicionando las fotos sin moderar y los formatos oficiales (D-13) |
| Dominio propio | ❌ Semana 8; sin nombre (D-09) |
| Indexación | ⚠️ `robots.txt` responde `Allow: /`. Decidir `noindex` hasta el lanzamiento (D-08). **Más urgente ahora**: `/proveedores` sigue indexable a propósito, y es la única bolsa que queda abierta |
| Rendimiento contra la URL | ⚠️ Solo la portada: **2,46 s en frío** (3 sep, `curl`, scale-to-zero). Sin medición completa |
| Dispositivos reales (RNF-01, RNF-07) | ❌ Sin hacer (S7) |
| Repositorio | ⚠️ `Jsua3/asobares`, público. **Cero PR en toda la historia y cero CI** (`.github/workflows/` no existe): nada verifica una rama antes de que entre. Colaboradores: `Jsua3` admin, `INGRIDMONWARTSKI` **write** — sigue sin segundo administrador (D-12) |
| `docs/ingenieria/decisiones/` | ⚠️ Carpeta **nueva** creada por la Persona 2 para dejar por escrito el cierre de las bolsas. Las ampliaciones van en `constancias/`; decidir si se convierte en Acta 06 o se retira (D-26) |

### 2.4 Datos personales

| Qué | Estado |
|---|---|
| **Banco de talento visible para los afiliados** | ⚠️ **Frente abierto, y es el más serio de la sesión.** Desde el 3 sep los perfiles de aspirantes —nombre, cargo, experiencia, teléfono y correo— los ve cualquier establecimiento afiliado con cuenta; antes solo el panel del gremio. **Los 7 perfiles ya registrados aceptaron con una versión anterior de la política**, que no contemplaba este uso. El formulario nuevo sí lo avisa y `consentimiento_politica` guarda con qué versión aceptó cada persona. Falta: versionar la política y decidir qué se hace con esos 7 (D-27) |
| Fichas de asociados | ✅ Nacen en borrador; cero publicadas en producción |
| Fotos del propietario pendientes o rechazadas | ⚠️ Viven en el disco público, servidas por URL no enumerable (ULID). Decidir (D-13) |
| 19 fotografías del gremio y el video | ✅ Uso autorizado por el gremio el 1 sep. El video ya está en el sitio; el original de 58 MB se queda en `nuevomaterial/`, que no se versiona. Pies de foto pendientes (D-03) |
| Base de establecimientos del gremio (`.xlsx`) | ✅ **Fuera del árbol desde el 3 sep.** Las dos copias que había dentro eran duplicados byte a byte de `D:/Sua_Files/material-asobares/`; se borraron y no se perdió nada. `DatosInternosDelAsociadoTest` en verde. `Registro Establecimiento.xlsx` se queda: es el formulario oficial, no una base, y la guardia no lo señala |
| Política de tratamiento de datos | ❌ Texto legal definitivo (P-15), encargados, canal de supresión, revisión legal (D-19). **Ahora bloquea al banco de talento** |
| `material/nuevomaterial/` | ✅ En `.gitignore`; no se versiona |
| Retención automática | ✅ Tres purgas diarias con `subMonthsNoOverflow()` |

### 2.5 Académico

| Qué | Estado |
|---|---|
| Corte 1 | ✅ 5.0 |
| Corte 2 | ✅ Entregado a tiempo el 21 ago (no revisado por César por uso evidente de IA) |
| Corte 3 (60 %) | ⚠️ **Documento corregido y puesto en el repositorio** el 3 sep: `docs/ingenieria/Semana 7 - Documento - Juan Jose Sua - correccion.docx`. Está en `docs/ingenieria/`, no en `entrega-2026-09-04/` junto a los anexos. **Falta enviarlo hoy o mañana** |
| **Lo que el documento ya no describe bien** | ⚠️ La matriz de trazabilidad (Anexo E) y el capítulo de requisitos describen los **RF de proveedores como públicos**, y desde el 3 sep no lo son. El **banco de talento** (`/mi-cuenta/aspirantes`) es funcionalidad nueva que no aparece. Las cifras del §5 cambiaron |
| Constancias | ✅ Acta 01 y Formato 03 firmados el 25 ago; planeador firmado. ❌ Acta 02 (S8), **Acta 04 y Acta 05 sin firmar**, y **Acta 06 sin emitir** (D-26) |
| Menores | Encuesta de Santiago (19 ago) sin confirmar; el documento de Ingrid es aparte e individual |

## 3. Registro único de decisiones pendientes

Cada decisión con su dueño y la fecha en que se pidió. Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md` (el 3 sep salieron **D-22** —el video— y las dos autorizaciones que cerraron OBS3-09 y el cierre de las bolsas). **Las D-01, D-04 a D-12 y D-20 caben en una sola reunión con Natalia con esta tabla impresa.**

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| **D-26** | **Acta 06: ampliación de alcance por las bolsas.** El alcance está congelado y toda ampliación se registra por escrito **antes** de codificarse; aquí el código se escribió primero y el registro que hay es un `.md` en una carpeta nueva, no una constancia. Emitirla con `constancias.mjs`, decir sin adornos que el registro llegó después, y retirar o reubicar `docs/ingenieria/decisiones/` | Sua + Ingrid | 3 sep | — |
| **D-27** | **Política de tratamiento y los 7 perfiles ya registrados.** El banco de talento expone contactos de terceros a un público nuevo. Versionar la política, decidir si a esos 7 se les vuelve a pedir consentimiento o se les excluye del listado mientras tanto, y quién responde ante una solicitud de supresión | Natalia + aliado jurídico | 3 sep | — |
| **D-28** | **Alta de credenciales de los afiliados.** No hay registro público de cuentas: las crea el panel. Sin resolver el alta para los establecimientos de la base, **nadie ve lo que se acaba de construir**: ni el directorio de proveedores ni el banco de talento | Natalia + Sua | 3 sep | — |
| **D-29** | **Dónde va `hero_frase_corta`.** Se puso de antetítulo encima del titular porque la clave estaba sembrada y exigida por la prueba sin que nada la pintara; la intención original no está escrita en ningún sitio. Y la **banda de tres videos** promete piezas que no existen: decidir si se recorta | Ingrid | 3 sep | — |
| D-01 | **Firma del Acta 04** y del **Acta 05**, con las cuatro cifras de su punto 5 | Natalia + directivo | 30 ago / 1 sep | — |
| D-03 | **Pies de foto** de las 19 fotografías y del video (evento, fecha, lugar, quiénes) | Natalia | 26 ago | Autorización: 1 sep ✅ · pies: — |
| D-04 | **Las 7 URL de trámite** de Armenia, o el contacto en la Alcaldía (OBS3-10) | Natalia / Alcaldía | 28 ago | — |
| D-05 | **Texto propio de «Quiénes somos»**; nombre del presidente; cargo con el que firma Natalia; datos del capítulo en el sitio de la Nacional | Natalia + Nacional | 5 ago / 28 ago | — |
| D-06 | **Logos institucionales** y de los aliados comerciales en buena resolución | Natalia | 31 ago | — |
| D-07 | **SMTP con el correo del gremio.** Pasos exactos en el bloque de arriba | Natalia + Sua (A) · Nacional (B, C) | 15 ago / 30 ago / 1 sep | — |
| D-08 | **Indexación antes del lanzamiento**: `noindex` hasta el lanzamiento o dejar indexar. `/proveedores` es ahora la única bolsa abierta y sigue indexable | Natalia + equipo | 30 ago | — |
| D-09 | **Dominio propio**: nombre, compra, titularidad y autorización de marca | Natalia | 5 ago / 28 ago | — |
| D-10 | **Pasarela**: «solo Bold» por escrito; medio PSE o QR y cuenta receptora; documentos de producción de Bold | Natalia + contadora | 28 ago | — |
| D-11 | **Cartera**: Excel con el formato real de la contadora —no vino con el Drive—; Drive vinculado o carga manual; periodicidad | Luisa + Natalia | 28 ago | — |
| D-12 | **Titularidad de la infraestructura**: facturación de Cloud, Natalia como miembro, **segundo administrador en GitHub** (Ingrid tiene `write`, no `admin`), fecha de traspaso | Sua + Natalia | 30 ago | — |
| D-13 | **Bucket y fotos pendientes o rechazadas**: disco privado servido por controlador, o riesgo aceptado por escrito. Ya no condiciona el video | Sua (técnica) | 31 ago | — |
| D-14 | **Marca de procedencia en el contenido sembrado**: sin ella, resembrar pisa lo que la oficina corrigió. El grupo `gremio` ya no se sobrescribe; el resto sí | Sua | 1 sep | — |
| D-15 | `GeneradorPdf`: borrar o conservar como molde; y borrar los dos PDF de ejemplo huérfanos | Sua | 1 sep | — |
| D-16 | **Reparto Persona 1 / Persona 2**: reescribirlo como quedó de hecho o declarar su suspensión | Sua + Ingrid | 31 ago | — |
| D-17 | **ERS v3 sin firma** y sus DPV abiertas. **DPV-02 —qué se ve con sesión y qué sin ella— quedó respondida de hecho el 3 sep** por el cierre de las bolsas: hay que ratificarlo o revertirlo, porque condiciona 8 RF ya codificados y la matriz de trazabilidad | Natalia + directivo | 5 ago | DPV-02: de hecho, 3 sep ⚠️ |
| D-18 | **Las confirmaciones del plan del material (26 ago)** que siguen sin respuesta | Natalia | 26 ago | — |
| D-19 | **Política de tratamiento de datos**: texto legal, encargados, canal de supresión, revisión legal. **Ver D-27** | Natalia / aliado jurídico | 5 ago | — |
| D-20 | **Fecha de la segunda demostración** (entre el 4 y el 11; pedir jue 10 o vie 11) | Directivo + Natalia | 28 ago | — |
| D-21 | **Municipios 2 a 12 de la guía**: orden, fuente por alcaldía, y qué se declara Fase II | Natalia | 1 sep | — |

## 4. Deuda diferida a propósito

No se «arregla de paso»:

- **El correo de ficha de bolsa publicada enlaza a `/proveedores`**, que ya no nombra al proveedor ni muestra su contacto: `ProveedorsTable` pasa `route('proveedores.index')` a `AccionesDeAprobacion`. El proveedor recibe «ya estás publicado» y llega a una página donde no aparece. Comprobado el 3 sep. **Se arregla cuando haya SMTP**, que hoy no manda nada.
- Los chips de filtro repetidos y los `leading-*`/`tracking-*` sueltos: la capa visual rehízo parte, queda revisar el resto **después del 11 de septiembre**.
- `@alpinejs/collapse` importado sin consumidor (retirarlo toca `package.json`, que exige aprobación).
- El consecutivo de PQR bajo concurrencia en PostgreSQL falla cerrado (error, no duplicado).
- No existe `lang/`: las reglas sin mensaje propio salen en inglés. Pendiente `php artisan lang:publish` + `lang/es/validation.php`.
- Salento y Filandia sin guía tras retirar lo inventado: se resuelve con D-21 y con decirlo en la página.
- El filtro de municipios del **directorio** lista todos los de la tabla tengan o no fichas publicadas; la guía ya filtra bien.
- Cuatro `index.lock.huerfano*` y `.git/huerfanos-cowork-2026-09-01/` siguen en `.git/`: **los borra Sua a mano**. `hs_err_pid48556.log` y los `~$*.docx` están ignorados por git.

## 5. Cifras medidas del árbol

Sobre `f83c9ea`, 3 de septiembre de 2026 (noche). Cada cifra con el comando que la produjo; **vuelve a medirlas antes de citarlas** en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | 292 (283 de Sua, 9 de Ingrid) | `git rev-list --count HEAD` · `git shortlog -sn HEAD` |
| Migraciones | 39 | `ls database/migrations \| wc -l` |
| Modelos | 21 | `ls app/Models/*.php \| wc -l` |
| Sembradores | 21 (+ `Support/`) | `ls database/seeders/*.php \| wc -l` |
| Archivos de prueba | 84 | `find tests -name '*Test.php' \| wc -l` |
| Vistas Blade | 70 | `find resources/views -name '*.blade.php' \| wc -l` |
| Panel | 19 recursos · 6 páginas · 20 policies | `ls app/Filament/Resources app/Filament/Pages app/Policies` |
| Comandos de Artisan propios | 5 | `ls app/Console/Commands` |
| Enums | 16 | `ls app/Enums` |
| Controladores públicos | 17 | `ls app/Http/Controllers/Publico/*.php \| wc -l` |
| Rutas GET propias | 88 | `php artisan route:list --method=GET --except-vendor --json` |
| Ajustes que siembra `SettingSeeder` | **126** (109 anteriores + 17 de la capa visual); en producción hay 109 | reflexión sobre `SettingSeeder::ajustes()` |
| **Suite** | **1.010 casos · 999 pasan · 11 omitidas · 0 fallos · 3.800 aserciones** · 435 s | `php artisan test --compact` |
| Video del hero | 10,0 s · 1280×768 · sin audio · **1.550.175 B** · póster 27.188 B (original: 48,1 s, 1680×1008, 58.451.107 B) | `ffprobe` · `ls -la public/videos` |
| Producción (3 sep) | Portada **200** en 2,46 s en frío | `curl -o /dev/null -w` |
| Producción (1 sep, mañana) | 23 aliados · 8 requisitos · 100 ajustes · 8 municipios · 6 categorías · 5 beneficios · 5 iniciativas · 3 roles · 80 permisos · 3 usuarios · **0** asociados, PQR, transacciones, noticias, eventos, vacantes y artistas | consola de Cloud / `tinker` |

## 6. Lo siguiente, en orden

1. **Enviar el documento de práctica** (Sua, hoy o mañana antes de las 11:59:59 pm). Antes de enviarlo, corregir lo que ya no es cierto: los RF de proveedores **no son públicos** desde hoy, el banco de talento es nuevo, y las cifras del §5 cambiaron.
2. **`git push`** (Sua, un minuto): `main` está cinco commits por delante de `origin`. Sin esto no hay despliegue y el gremio sigue viendo el sitio del 2 de septiembre.
3. **Desplegar y sembrar** (Sua): comprobar en la consola de Cloud que el despliegue arrancó; **correr `ContenidoOficialSeeder` una vez** para que existan las 17 claves nuevas (no pisa el grupo `gremio`; el resto sí, D-14). Después, abrir la URL pública y mirar con los ojos: que el video del hero **corra** —no solo que cargue—, que el rótulo se lea en los dos temas, y que la banda de tres videos no deje huecos.
4. **Emitir el Acta 06** (D-26) y llevarla a la reunión junto con la 04 y la 05.
5. **Una sola reunión con Natalia** con la tabla del §3 impresa: D-01, D-04 a D-12, D-20, y las tres nuevas D-27, D-28 y D-29. Si está la cuenta de Google del gremio, se hace ahí mismo el SMTP (D-07).
6. **Resolver D-28 antes de la demo**: sin credenciales de afiliado no hay a quién enseñarle el directorio de proveedores ni el banco de talento, que es la mitad de lo que se acaba de construir.
7. **Fijar la demo 2** (D-20) para el jueves 10 o viernes 11; guion de siete pantallas; sitio despierto media hora antes.
8. **Franja visual, lo que queda** (Ingrid): colocar las 19 fotos, pies de foto, decidir la banda de videos y la frase corta (D-29), medir contraste, dirección de arte escrita.
9. **Backend tras el SMTP**: arreglar el enlace del correo de ficha publicada (§4); bucket con política `publico/*` (D-13); disco privado para fotos pendientes; procedencia de semillas (D-14); `noindex` (D-08); filtro de municipios del directorio; `lang/es`; medición de rendimiento completa; importar la base de 48 filas desde `D:/Sua_Files/material-asobares/`.
10. Semana 8: dominio y SSL, manual actualizado y en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
