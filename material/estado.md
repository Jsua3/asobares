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
| Fecha | Sábado 5 de septiembre de 2026 (Bogotá), mañana; segunda sesión del día |
| **Dónde vive este archivo** | **En la rama `p1-navbar-alternativa`**, sobre `d2f374f`; este archivo entra en el commit siguiente de la rama. El `estado.md` de `main` sigue siendo el del 3 sep (`f83c9ea`) y **no sabe que esta rama existe**: cuando se decida A o B, la sesión que cierre lo reescribe en `main` |
| `main` | `6c1b8b7` · 295 confirmaciones · `origin/main` al día · **sin cambios desde el 3 sep** |
| Rama de trabajo | **`p1-navbar-alternativa`** · `d2f374f` · 30 commits sobre `main` · **publicada en `origin` el 5 sep, sin PR** · es la **opción B** de la barra de navegación para la decisión de la dirección; **no se fusiona ni se despliega** hasta que la dirección elija. En `origin` siguen `p2-redisenio-visual`, `p2/acceso-asociados` y `p2-directorio`, ya fusionadas y sin aportar nada |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5). **La suite sí se ejecutó** sobre `d2f374f` (§5); la barra **sí se miró en Chromium real** con playwright-cli en escritorio 1440, 1280, 1150 y 1024 de ancho, un híbrido (puntero fino forzado y toques por CDP), iPad Pro 11 horizontal táctil y móvil 390×844 (bitácora §39) |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud` responde **200** en 2,97 s en frío (5 sep). Sirve el árbol de `main` (`6c1b8b7`; se desplegó el 3 sep como `a3805f1`, mismo contenido antes de reescribir la firma): rediseño de Ingrid, bolsas detrás de la sesión, video del hero y **la barra de la opción A** |

## 1. Qué se exige y cuándo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **Vencido: vie 4 sep, 11:59:59 pm** | Documento de práctica corregido según la revisión CG del 31 ago. Está en el repositorio (`docs/ingenieria/Semana 7 - Documento - Juan Jose Sua - correccion.docx`, `1e3b365`). **El repositorio no registra si se envió**: confirmarlo. Y sigue describiendo los RF de proveedores como públicos y sin el banco de talento (§2.5) | Docente asesor. Tarde = 0.0 |
| **Entre el 4 y el 11 de sep** | **Segunda demostración con la capa visual levantada**, sobre la URL pública, en el teléfono del directivo. Fecha exacta sin fijar (pedir jue 10 o vie 11) | Directivo del capítulo (`R24 04:52`) |
| **Reunión con la dirección, fecha sin anotar** | **Elegir entre la barra de navegación A (en producción) y la B (esta rama)**, con los seis vídeos grabados el 3 y el 5 sep (D-30) | Dirección + Sua |
| 7 – 11 sep | Pruebas en dispositivos reales y corrección de lo que salga | Cronograma firmado (S7) |
| 14 – 18 sep | Dominio, SSL, capacitación y Acta 02 firmada; manual actualizado | Cronograma firmado (S8) |
| **22 sep** | **Entrega dura al gremio** | Cronograma firmado |
| Por confirmar | Fecha de cierre del corte 3 y del PDF final a `proyectosing@cue.edu.co` | Docente asesor |

## 2. Inventario por frente

### 2.1 Producto — los catorce señalamientos del gremio (acta 3, 28 ago)

| Ref. | Estado | Qué falta, y de quién depende |
|---|---|---|
| OBS3-01, 02, 04, 05, 06, 08, 12, 13, 14 | ✅ Cerrados con commit (31 ago, §30.1) | — |
| OBS3-03 | ✅ Cerrado por decisión (1 sep): el sitio arranca en el tema del dispositivo, `system`. **La opción B lo matiza sin revertirlo**: su popover ofrece también «Sistema» para volver al del dispositivo tras forzar uno (`encargo.md` §13, 3 sep) | — |
| OBS3-02 (medio del hero) | ✅ Cerrado el 3 sep (`f83c9ea`): video institucional versionado en `public/videos/`, en producción | Medir contraste del rótulo en los dos temas con el video corriendo |
| OBS3-07 (fotos y video del gremio) | ⚠️ Mitad cerrada: el video está en el sitio. Las 19 fotografías siguen sin usarse | Pies de foto (D-03) y colocarlas en la franja visual |
| OBS3-09 (bolsa de empleo) | ✅ Cerrado el 3 sep: el afiliado consulta aspirantes en `/mi-cuenta/aspirantes`. **Frente legal abierto: §2.4** | — |
| OBS3-10 | ⚠️ Código puesto | Las **7 URL de trámite** de Armenia (D-04) |
| OBS3-11 | ⚠️ Código puesto (todo editable) | El **texto propio** de «Quiénes somos» y nombres/cargos (D-05) |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar** (D-01) |

De catorce, **doce cerrados y dos vivos** (10 y 11); ninguno se cierra escribiendo código.

**La barra de navegación tiene dos opciones sobre la mesa (D-30):**

- **Opción A — en producción.** La barra de Ingrid (`p2-redisenio-visual`, en `main` desde el 3 sep): una píldora de 280 px con solo el logo que se expande al pasar el ratón por la franja superior. Grabada el 3 sep en tres tamaños: **a 1024 px o más con dedo (iPad en horizontal, portátil táctil) no hay forma de abrir el menú** —la regla que la encoge solo mira el ancho y la que la expande exige ratón—, y «Afíliate» queda oculto en reposo en escritorio.
- **Opción B — rama `p1-navbar-alternativa`.** Diseñada con Sua el 3 sep (`docs/ingenieria/navbar-tres-estados-diseno.md`) y construida el 5 sep en doce tareas con revisión por tarea y revisión final (`…-plan.md`, bitácora §39). Escritorio ≥ 1024 px con **tres estados** (`inicial` una píldora, `scroll` tres módulos de vidrio separados —isotipo · Directorio, Bolsas, Eventos · cuenta—, `atención` los cinco controles de vuelta) sobre **un solo DOM**; resortes reales como `linear()` con respaldo `@supports` y anulados bajo movimiento reducido; **con dedo, un toque en el módulo o en el indicador `···` abre**; con teclado, el foco dentro; popover de tema **Claro · Oscuro · Sistema** con el icono decidido por CSS (sin destello); chip **ES** con Español activo y **English «próximamente»** deshabilitado (banderas Colombia y Estados Unidos, SVG propios); disparador de cuenta con **`Sec.` / `Admin`**; **móvil intacto** (cabecera de 56 px como en `main`; la barra lateral de tema se queda solo ahí). Medido en Chromium: módulo principal e indicador de 44 px, foco devuelto al disparador al elegir tema, «El gremio» desplegable y alcanzable en los tres estados. **Lo único sin verificar: `prefers-reduced-transparency`**, que Playwright no emula (D-31). Revisión final: lista para la demo; los cuatro bloqueos de fusión ya cerrados y re-revisados. **Ajustes de la mañana del 5 sep tras la primera mirada de Sua** (nueve commits, bitácora §39.7): sin raya roja bajo la barra en escritorio; el cambio de estado dura **620 ms** (`--duracion-estado`; los popovers siguen en 520); el módulo principal se **centra en la pantalla** por rejilla `1fr auto 1fr` en los tres estados (exacto a 1440 y 1280, 8 px a la izquierda solo en atención a 1280); «Bolsas», «El gremio» y la cuenta **abren al pasar el cursor** como el tema; los cuatro desplegables comparten `Alpine.data('desplegable')` y **se excluyen** (nunca dos abiertos: el choque tema/idioma que Sua vio, cerrado); la cuenta se cierra al tabular fuera. Lo que cazó la verificación en Chromium y la revisión adversaria de después (quince agentes): Enter no abría los grupos porque la identidad era `$el` y no `$root`; la rejilla aplastaba el logotipo entre 1024 y 1190 px (mínimo real al módulo del logo); en un equipo híbrido el toque abría y cerraba en el mismo gesto (los desplegables asoman por `pointerenter` de tipo ratón, no por `mouseenter`); y Escape robaba el foco a un campo del formulario si un panel estaba abierto por hover (ahora solo lo devuelve si estaba dentro). Queda **D-33**: entre 1024 y ~1130 px la píldora no da para los tres módulos en inicial ni en atención.

### 2.2 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| **Los 126 ajustes sembrados** | ⚠️ En producción hay **109**. Los **17 nuevos** de la capa visual **no existen todavía en la base de producción** | Correr `ContenidoOficialSeeder` una vez. Dieciséis traen respaldo en la vista; `hero_frase_corta` no se pinta hasta que alguien la teclee (así, a propósito) |
| **Franja «El gremio en cifras»** (D-25, Acta 05) | ✅ Código en producción, vacía de fábrica | **Firmar el Acta 05**; fijar las cuatro cifras; teclearlas |
| **Banda de videos de la portada** | ⚠️ Tres huecos con títulos de fábrica y un solo video real (`src` en `null`) | D-29: recortarla a lo que existe |
| Guía normativa | ⚠️ **1 municipio de 12** (Armenia) | D-21; formatos oficiales por entidad sin llegar |
| Portada | ✅ Todo texto editable | Las 19 fotos autorizadas sin colocar |
| Aliados | ✅ 23 del catálogo oficial | Logos (D-06); cuáles aplican al Quindío (D-18) |
| Beneficios e iniciativas | ✅ 5 y 5, de documento oficial | — |
| «Quiénes somos» | ⚠️ Texto provisional | D-05, D-18 |
| Directorio | ⚠️ **0 fichas publicadas** en producción (correcto). La base del gremio vive en `D:/Sua_Files/material-asobares/` (48 y 41 filas), **fuera del árbol** | Importar con `asociados:importar` desde allí; autorización de cada titular |
| Boletín laboral (Ley 2466 de 2025) | ❌ Sin publicar | D-18 |
| Formulario oficial de registro | ❌ No está en `/afiliate` | D-18 |
| Certificado de afiliación | ❌ Molde en `nuevomaterial/` | D-18 |
| Cifra pública de afiliados | ⚠️ El sitio dice 60; la base 48; el directivo 60 | Natalia (D-18) |

### 2.3 Infraestructura

| Qué | Estado |
|---|---|
| Sitio | ✅ **200** sobre PostgreSQL 17.11, 39 migraciones, sirviendo `main` |
| **Rama `p1-navbar-alternativa`** | ✅ Publicada en `origin` (5 sep), 30 commits, sin PR. **No fusionar sin decisión de la dirección (D-30).** Si se elige A, se archiva; si B, entra por fusión con la suite sobre el resultado y este estado se reescribe en `main` |
| Video del hero en producción | ✅ Versionado en `public/videos/`; `VideoDelHeroTest` vigila el índice de git |
| Cuenta de Laravel Cloud | ✅ Existe, con medio de pago del gremio. ⚠️ Organización `juan-sua`: facturación y Natalia como miembro (D-12) |
| Correo saliente (SMTP) | ❌ **Sin contratar: bloque de arriba** |
| Bucket | ❌ Sin crear; condiciona fotos sin moderar y formatos oficiales (D-13) |
| Dominio propio | ❌ Semana 8 (D-09) |
| Indexación | ⚠️ `Allow: /`; decidir `noindex` (D-08) |
| Rendimiento contra la URL | ⚠️ Solo portada: **2,97 s en frío** (5 sep) |
| Dispositivos reales (RNF-01, RNF-07) | ❌ Sin hacer (S7). **La rama B ya se midió en iPad Pro 11 emulado; falta el aparato de verdad** |
| Repositorio | ⚠️ `Jsua3/asobares`, público. **Cero PR y cero CI.** `INGRIDMONWARTSKI` con `write`, sin segundo administrador (D-12) |
| `docs/ingenieria/decisiones/` | ⚠️ Carpeta nueva de la Persona 2 (3 sep); decidir Acta 06 o retirar (D-26) |

### 2.4 Datos personales

| Qué | Estado |
|---|---|
| **Banco de talento visible para los afiliados** | ⚠️ **Frente abierto (3 sep).** Los 7 perfiles registrados aceptaron con una versión anterior de la política (D-27) |
| Fichas de asociados | ✅ Nacen en borrador; cero publicadas en producción |
| Fotos del propietario pendientes o rechazadas | ⚠️ Disco público, URL no enumerable (D-13) |
| 19 fotografías del gremio y el video | ✅ Uso autorizado el 1 sep; el video ya en el sitio; pies de foto pendientes (D-03) |
| Base de establecimientos (`.xlsx`) | ✅ **Fuera del árbol** desde el 3 sep; `DatosInternosDelAsociadoTest` en verde |
| Política de tratamiento de datos | ❌ D-19; bloquea al banco de talento |
| `material/nuevomaterial/` | ✅ En `.gitignore` |
| Retención automática | ✅ Tres purgas diarias |

### 2.5 Académico

| Qué | Estado |
|---|---|
| Corte 1 | ✅ 5.0 |
| Corte 2 | ✅ Entregado a tiempo el 21 ago |
| Corte 3 (60 %) | ⚠️ Documento corregido en el repositorio (`docs/ingenieria/Semana 7 - …correccion.docx`). **Sin confirmación de envío.** Sigue describiendo los RF de proveedores como públicos y sin el banco de talento; y si se elige la opción B, la barra que describe tampoco será la desplegada |
| Constancias | ✅ Acta 01, Formato 03, planeador. ❌ Acta 02 (S8), **Acta 04 y Acta 05 sin firmar**, **Acta 06 sin emitir** (D-26) |
| Menores | Encuesta de Santiago sin confirmar; el documento de Ingrid es aparte |

## 3. Registro único de decisiones pendientes

Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md` (el 3 sep salieron D-22 y las tres de la rama B; ninguna el 5 sep). **Las D-01, D-04 a D-12 y D-20 caben en una sola reunión con Natalia con esta tabla impresa; D-30 es de la dirección.**

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| **D-30** | **Barra de navegación: opción A (producción, de Ingrid) u opción B (rama `p1-navbar-alternativa`, de Sua).** Insumos: los seis vídeos (`1-`, `2-`, `3-` de la A; `B-1-`, `B-2-`, `B-3-` de la B, 3 y 5 sep), la spec y las medidas de §2.1. Si B: fusión, `ContenidoOficialSeeder`, reescritura del estado en `main`, y el Acta 06 (D-26) incluye el cambio de barra. Si A: cerrar el agujero táctil de la A (una media query) y archivar la rama | Dirección + Sua | 5 sep | — |
| **D-31** | **`prefers-reduced-transparency` en un equipo real** antes de la demo de la B: Playwright acepta la emulación y no la aplica; el CSS usa los tokens que la señal apaga, pero no está medido | Sua | 5 sep | — |
| **D-32** | **Idiomas como subsistema propio**: `lang/`, middleware de locale, traducir vistas y volver multilingüe la tabla de ajustes. **Ampliación de alcance: acta antes de codificar.** El chip de la B es su sitio reservado y no funciona a propósito | Natalia + Sua | 3 sep | — |
| **D-33** | **Qué cede en la barra B entre 1024 y ~1130 px de ancho** (iPad mini y los iPad de 10 pulgadas antiguos, en horizontal): en `inicial` y `atención` los tres módulos suman 1.102 px y la píldora mide 968. Con el logo protegido, a 1024 en inicial la píldora desborda 124 px a la derecha y el documento gana 107 px de desplazamiento horizontal (medido el 5 sep); en scroll cabe (727 px). Opciones: isotipo también en inicial por debajo de ~1150 px, o esconder el texto de «Mi cuenta» en esa franja; ninguna se toma sin Sua. Ya desbordaba antes de la rejilla | Sua | 5 sep | — |
| D-26 | **Acta 06** de la ampliación de las bolsas (3 sep): emitirla con `constancias.mjs`, decir que el registro llegó después del código, y retirar o reubicar `docs/ingenieria/decisiones/` | Sua + Ingrid | 3 sep | — |
| D-27 | **Política de tratamiento y los 7 perfiles** que aceptaron con otra versión antes de que su contacto fuera visible | Natalia + aliado jurídico | 3 sep | — |
| D-28 | **Alta de credenciales de afiliado**: sin ella nadie ve el directorio de proveedores ni el banco de talento | Natalia + Sua | 3 sep | — |
| D-29 | **`hero_frase_corta`** de antetítulo (colocación a confirmar) y la **banda de tres videos** que promete piezas inexistentes | Ingrid | 3 sep | — |
| D-01 | Firma del **Acta 04** y del **Acta 05** con sus cuatro cifras | Natalia + directivo | 30 ago / 1 sep | — |
| D-03 | **Pies de foto** de las 19 fotografías y del video | Natalia | 26 ago | Autorización 1 sep ✅ · pies: — |
| D-04 | **Las 7 URL de trámite** de Armenia (OBS3-10) | Natalia / Alcaldía | 28 ago | — |
| D-05 | **Texto propio de «Quiénes somos»**; nombres y cargos | Natalia + Nacional | 5 ago / 28 ago | — |
| D-06 | **Logos** institucionales y de aliados en buena resolución | Natalia | 31 ago | — |
| D-07 | **SMTP con el correo del gremio** (bloque de arriba) | Natalia + Sua (A) · Nacional (B, C) | 15 ago / 30 ago / 1 sep | — |
| D-08 | **Indexación antes del lanzamiento** | Natalia + equipo | 30 ago | — |
| D-09 | **Dominio propio** | Natalia | 5 ago / 28 ago | — |
| D-10 | **Pasarela**: «solo Bold» por escrito; PSE o QR; documentos de Bold | Natalia + contadora | 28 ago | — |
| D-11 | **Cartera**: Excel real de la contadora; Drive o carga manual | Luisa + Natalia | 28 ago | — |
| D-12 | **Titularidad de la infraestructura**: facturación, Natalia miembro, segundo admin en GitHub | Sua + Natalia | 30 ago | — |
| D-13 | **Bucket y fotos pendientes** | Sua | 31 ago | — |
| D-14 | **Marca de procedencia en el contenido sembrado** | Sua | 1 sep | — |
| D-15 | `GeneradorPdf` y los dos PDF de ejemplo huérfanos | Sua | 1 sep | — |
| D-16 | **Reparto Persona 1 / Persona 2** | Sua + Ingrid | 31 ago | — |
| D-17 | **ERS v3 sin firma**; **DPV-02 respondida de hecho el 3 sep** (bolsas detrás de la sesión), pendiente de ratificar | Natalia + directivo | 5 ago | DPV-02: de hecho ⚠️ |
| D-18 | **Confirmaciones del plan del material (26 ago)** | Natalia | 26 ago | — |
| D-19 | **Política de tratamiento de datos** (ver D-27) | Natalia / aliado jurídico | 5 ago | — |
| D-20 | **Fecha de la segunda demostración** | Directivo + Natalia | 28 ago | — |
| D-21 | **Municipios 2 a 12 de la guía** | Natalia | 1 sep | — |

## 4. Deuda diferida a propósito

No se «arregla de paso»:

- **De la rama B, anotado por su revisión final** (se atiende si se elige B): la transición de `gap` aporta poco y cuesta un reflow por fotograma durante 620 ms; `backdrop-filter` no se transiciona (aparece de golpe, como antes); el brillo de los tres módulos se mueve al unísono (así lo manda la spec; el comentario del marcado dice otra cosa); `$rol`/`$prefijoRol` son dos `match` que recalculan lo mismo.
- **De la rama B, visto el 5 sep al medir la rejilla:** la franja 1024–1130 px en inicial y atención (D-33). No se toca sin decidir qué cede.
- **Preexistente en `main`, visto el 5 sep:** `consultaSistema.addEventListener('change', aplicarTema)` pasa el evento como `preferenciaForzada` (funciona por accidente de la comparación); y tabular hacia el header `sticky` estando desplazado devuelve la página al tope (Chromium).
- **El correo de ficha de bolsa publicada enlaza a `/proveedores`**, que ya no nombra al proveedor. Se arregla cuando haya SMTP.
- Los chips de filtro repetidos y los `leading-*`/`tracking-*` sueltos: **después del 11 de septiembre**.
- `@alpinejs/collapse` importado sin consumidor (toca `package.json`).
- El consecutivo de PQR bajo concurrencia falla cerrado.
- No existe `lang/` (ver D-32).
- Salento y Filandia sin guía tras retirar lo inventado (D-21).
- El filtro de municipios del **directorio** lista todos tengan o no fichas.
- Cuatro `index.lock.huerfano*` y `.git/huerfanos-cowork-2026-09-01/` en `.git/`: **los borra Sua a mano**.

## 5. Cifras medidas del árbol

Sobre **`d2f374f` (rama `p1-navbar-alternativa`)**, 5 de septiembre de 2026, mañana. Las de `main` (`6c1b8b7`) son las del 3 sep salvo donde se indica. **Vuelve a medirlas antes de citarlas** en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | 315 en la rama (306 de Sua, 9 de Ingrid); 295 en `main`; la rama suma 20 | `git rev-list --count HEAD` · `git shortlog -sn HEAD` |
| Migraciones | 39 | `ls database/migrations \| wc -l` |
| Modelos | 21 | `ls app/Models/*.php \| wc -l` |
| Sembradores | 21 (+ `Support/`) | `ls database/seeders/*.php \| wc -l` |
| Archivos de prueba | **85** en la rama (84 en `main`: entra `NavbarTresEstadosTest`) | `find tests -name '*Test.php' \| wc -l` |
| Vistas Blade | **72** en la rama (70 en `main`: entran `control-tema`, `control-idioma`, `bandera`; sale `selector-tema`) | `find resources/views -name '*.blade.php' \| wc -l` |
| Componentes públicos | 20 | `ls resources/views/components/publico/*.blade.php \| wc -l` |
| Panel | 19 recursos · 6 páginas · 20 policies | `ls app/Filament/Resources app/Filament/Pages app/Policies` |
| Comandos de Artisan propios | 5 | `ls app/Console/Commands` |
| Enums | 16 | `ls app/Enums` |
| Controladores públicos | 17 | `ls app/Http/Controllers/Publico/*.php \| wc -l` |
| Rutas GET propias | 88 | `php artisan route:list --method=GET --except-vendor --json` |
| Ajustes que siembra `SettingSeeder` | 126; en producción 109 | reflexión sobre `SettingSeeder::ajustes()` |
| **Suite en la rama** | **1.031 casos · 1.020 pasan · 11 omitidas · 0 fallos · 4.081 aserciones** · 274 s (5 sep, mañana, sobre `d2f374f`) | `php artisan test --compact` |
| Suite en `main` | 1.010 casos · 999 pasan · 11 omitidas · 0 fallos · 3.800 aserciones (3 sep, sobre `f83c9ea`) | `php artisan test --compact` |
| Barra B, medida en Chromium (5 sep) | módulo principal 44 px en scroll (antes 39,7) · indicador 44×44 (antes 32×40) · botón de tema 44×44 · chip de idioma 50×44 · filas de popover 45,7 · cabecera móvil 56 px (igual que `main`) · panel «El gremio» 224×155 alcanzable por `elementFromPoint` en los tres estados | `playwright-cli --raw eval` |
| Video del hero | 10,0 s · 1280×768 · 1.550.175 B · póster 27.188 B | `ffprobe` |
| Producción (5 sep) | Portada **200** en 2,97 s en frío | `curl -o /dev/null -w` |

## 6. Lo siguiente, en orden

1. **Confirmar que el documento de práctica se envió** el 4 sep (Sua). Si no, es 0.0 y hay que hablar con el docente.
2. **La decisión A o B** (D-30) con la dirección, con los seis vídeos y esta tabla. Antes, si se puede, **D-31**: probar la B en un equipo con transparencia reducida activa, y en un iPad de verdad. Y **D-33**: decidir qué cede entre 1024 y 1130 px antes de enseñarla en un iPad mini.
3. **Si B:** fusionar `p1-navbar-alternativa` a `main` con la suite sobre el resultado; correr `ContenidoOficialSeeder` una vez en producción; reescribir este estado en `main`; que el Acta 06 (D-26) recoja también el cambio de barra; corregir el documento de práctica, que describe la A. **Si A:** una media query cierra el agujero táctil de la A (`@media (hover: hover) and (pointer: fine)` alrededor del colapso) y se archiva la rama con su spec.
4. **`git push` ya no falta**: `main` y la rama están al día en `origin`. Comprobar en la consola de Cloud que el último despliegue corrió (`a3805f1`, 3 sep).
5. **Una sola reunión con Natalia** con la tabla del §3 impresa: D-01, D-04 a D-12, D-20, D-27, D-28, D-29. Si está la cuenta de Google del gremio, se hace ahí mismo el SMTP (D-07).
6. **Resolver D-28 antes de la demo**: sin credenciales de afiliado no hay a quién enseñarle el directorio de proveedores ni el banco de talento.
7. **Fijar la demo 2** (D-20) para el jueves 10 o viernes 11; guion de siete pantallas; sitio despierto media hora antes.
8. **Franja visual, lo que queda** (Ingrid): las 19 fotos, pies de foto, D-29.
9. **Backend tras el SMTP**: enlace del correo de ficha publicada; bucket (D-13); disco privado para fotos pendientes; procedencia de semillas (D-14); `noindex` (D-08); filtro de municipios; `lang/es`; medición de rendimiento completa; importar la base de 48 filas desde `D:/Sua_Files/material-asobares/`.
10. Semana 8: dominio y SSL, manual actualizado y en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
