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
| Fecha | Lunes 7 de septiembre de 2026 (Bogotá), madrugada; sesión con Sua que empezó la noche del 5 y construyó la barra móvil el 6 |
| **Dónde vive este archivo** | **En `main`**; medido sobre `6f18908`, con la barra móvil 2.1 y el panel de Ingrid ya dentro, y este archivo entra en el commit siguiente. **`main` se empuja hoy**: con el push quedan desplegados los dos |
| `main` | `6f18908` más este commit de estado · dos fusiones por avance rápido el 7 sep, las dos con el mismo árbol que se probó: `p1-navbar-movil` (la barra móvil 2.1) y `p2-panel-ingrid` (el panel de administración de Ingrid, `p2/acceso-asociados` más dos arreglos de conformidad) |
| Rama de trabajo | Ninguna abierta: `p1-navbar-movil` y `p2-panel-ingrid` quedaron fusionadas en `main` el 7 sep. La de Ingrid nació de `origin/p2/acceso-asociados`, que se conserva como historia igual que `p1-navbar-alternativa` |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5). Suite completa del 7 sep sobre la fusión de las dos ramas: **1.069 casos · 1.058 pasan · 11 omitidas · 0 fallos · 4.721 aserciones · 322 s**. La barra móvil se midió además en Chromium con `playwright-cli` a 320, 360, 390, 768 y 844×390, con toques por CDP |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud`. **Con los push del 7 sep pasa a servir la barra móvil 2.1 y el panel de administración de Ingrid**, además del «Afíliate» escondido con sesión y el velo de escritorio devuelto al 72 %. No cambian datos: ninguna de las dos ramas trae migraciones y los sembradores se corren a mano |

## 1. Qué se exige y cuándo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **Vencido: vie 4 sep, 11:59:59 pm** | Documento de práctica corregido según la revisión CG del 31 ago. Está en el repositorio (`docs/ingenieria/Semana 7 - Documento - Juan Jose Sua - correccion.docx`, `1e3b365`). **El repositorio no registra si se envió**: confirmarlo. Y sigue describiendo los RF de proveedores como públicos y sin el banco de talento (§2.5) | Docente asesor. Tarde = 0.0 |
| **Entre el 4 y el 11 de sep** | **Segunda demostración con la capa visual levantada**, sobre la URL pública, en el teléfono del directivo. Fecha exacta sin fijar (pedir jue 10 o vie 11) | Directivo del capítulo (`R24 04:52`) |
${1}Ya no es elegir A o B: Sua aplicó la B el 5 sep y está desplegada. Enseñar el resultado (los seis vídeos siguen en el scratchpad de la sesión del 5 sep) y cerrar el Acta 06 (D-26) con el cambio de barra${2}
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

**La barra de navegación quedó decidida el 5 sep (D-30, Sua): la B, fusionada en `main` y desplegada.** Las dos opciones, para la historia:

- **Opción A — en producción.** La barra de Ingrid (`p2-redisenio-visual`, en `main` desde el 3 sep): una píldora de 280 px con solo el logo que se expande al pasar el ratón por la franja superior. Grabada el 3 sep en tres tamaños: **a 1024 px o más con dedo (iPad en horizontal, portátil táctil) no hay forma de abrir el menú** —la regla que la encoge solo mira el ancho y la que la expande exige ratón—, y «Afíliate» queda oculto en reposo en escritorio.
- **Opción B — rama `p1-navbar-alternativa`.** Diseñada con Sua el 3 sep (`docs/ingenieria/navbar-tres-estados-diseno.md`) y construida el 5 sep en doce tareas con revisión por tarea y revisión final (`…-plan.md`, bitácora §39). Escritorio ≥ 1024 px con **tres estados** (`inicial` una píldora, `scroll` tres módulos de vidrio separados —isotipo · Directorio, Bolsas, Eventos · cuenta—, `atención` los cinco controles de vuelta) sobre **un solo DOM**; resortes reales como `linear()` con respaldo `@supports` y anulados bajo movimiento reducido; **con dedo, un toque en el módulo o en el indicador `···` abre**; con teclado, el foco dentro; popover de tema **Claro · Oscuro · Sistema** con el icono decidido por CSS (sin destello); chip **ES** con Español activo y **English «próximamente»** deshabilitado (banderas Colombia y Estados Unidos, SVG propios); disparador de cuenta con **`Sec.` / `Admin`**; **móvil intacto** (cabecera de 56 px como en `main`; la barra lateral de tema se queda solo ahí). Medido en Chromium: módulo principal e indicador de 44 px, foco devuelto al disparador al elegir tema, «El gremio» desplegable y alcanzable en los tres estados. **Lo único sin verificar: `prefers-reduced-transparency`**, que Playwright no emula (D-31). Revisión final: lista para la demo; los cuatro bloqueos de fusión ya cerrados y re-revisados. **Ajustes de la mañana del 5 sep tras la primera mirada de Sua** (nueve commits, bitácora §39.7): sin raya roja bajo la barra en escritorio; el cambio de estado dura **620 ms** (`--duracion-estado`; los popovers siguen en 520); el módulo principal se **centra en la pantalla** por rejilla `1fr auto 1fr` en los tres estados (exacto a 1440 y 1280, 8 px a la izquierda solo en atención a 1280); «Bolsas», «El gremio» y la cuenta **abren al pasar el cursor** como el tema; los cuatro desplegables comparten `Alpine.data('desplegable')` y **se excluyen** (nunca dos abiertos: el choque tema/idioma que Sua vio, cerrado); la cuenta se cierra al tabular fuera. Lo que cazó la verificación en Chromium y la revisión adversaria de después (quince agentes): Enter no abría los grupos porque la identidad era `$el` y no `$root`; la rejilla aplastaba el logotipo entre 1024 y 1190 px (mínimo real al módulo del logo); en un equipo híbrido el toque abría y cerraba en el mismo gesto (los desplegables asoman por `pointerenter` de tipo ratón, no por `mouseenter`); y Escape robaba el foco a un campo del formulario si un panel estaba abierto por hover (ahora solo lo devuelve si estaba dentro). Queda **D-33**: entre 1024 y ~1130 px la píldora no da para los tres módulos en inicial ni en atención. **Al mediodía del 5 sep Sua pidió aplicar esta barra a la versión nueva de Ingrid**, y la rama la absorbió (`dc4f6aa`): el hero de la portada es ahora el video a pantalla completa con el titular encima y el header pasa a **fijo** (`cromo-fijo`), flotando sobre él; la barra en tres estados se queda entera. La portada nueva traía o destapaba tres cosas, arregladas con guardia: el rótulo del video sin pintar (`PortadaEditableTest` estaba rojo en la rama de Ingrid; va al pie del hero, **a confirmar con ella**), el «Afíliate» de contorno y la píldora de afiliados invisibles sobre el video (portadores `contorno-claro` y `etiqueta-clara`, con el blanco del fondo oscuro en CSS y no en utilidades, que la guardia de tema prohíbe), y **el vidrio del móvil, que la rama B había perdido** sin que nadie lo midiera y que vuelve por debajo de 64rem. **Y Sua pidió fusionar y desplegar**: hecho por avance rápido con la suite en verde. Queda `ContenidoOficialSeeder` en producción (toca datos: visto bueno aparte) y el Acta 06.

**6 sep (madrugada): «Afíliate» ya no se ofrece con sesión, y la barra móvil 2.1 quedó diseñada antes de escribirse.** Sua vio en escritorio la pastilla roja junto a «Admin Natalia Gutié…» y pidió esconderla a quien ya es del gremio: `09e8c17` la mete en el `@guest` del módulo de cuenta y del panel móvil (el pie la conserva), con guardia vista roja antes y al devolverla. En el mismo mensaje abrió la fase siguiente, la **navBar 2.1**: el móvil en dos módulos, el inferior como principal, con los estados de escritorio menos atención y una compactación al bajar que se deshace al subir. El proyecto registra por escrito antes de codificar y Sua elige, así que `83e7390` es la **Parte II de `docs/ingenieria/navbar-tres-estados-diseno.md`**, salida de un taller de dieciocho agentes (seis lectores, tres diseñadores, tres jueces, síntesis, cuatro críticos adversarios y revisor final; bitácora §40): dos módulos de vidrio, el inferior fijo abajo con las cinco secciones y dos hojas que suben al tocar, `inicial` y `scroll` por dirección del scroll con histéresis, el módulo de cuenta compartido con el escritorio, y **sin código hasta que Sua responda dieciocho decisiones** (D-34). Lo que el taller destapó y el diseño resuelve: el `transform` muerto de `.cromo` hacía del header bloque contenedor de todo `fixed`; un vidrio ancestro, o un `view-transition-name`, deja a sus hojas sin página que desenfocar; el apartado de 7rem cuelga de un selector de hermanos con la barra lateral de tema; y 11 px sobre el velo del 72 % no llegan a 4,5:1.

**6–7 sep: la barra móvil 2.1 está construida, medida y revisada.** Sua aprobó las dieciocho decisiones en una línea («desde D-M1 hasta la D-M18 apruebo y apruebo todas las recomendaciones que propones») y la rama `p1-navbar-movil` las ejecutó en once tareas, con cada guardia vista roja antes del código y treinta y una mutaciones. El teléfono ya no tiene hamburguesa ni panel: arriba la marca, el tema y «Afíliate» (o el nombre y el rango de quien tiene sesión), y abajo, fijo y principal, cinco pestañas con Directorio, Abre tu negocio, Eventos y las hojas de Bolsas y El gremio, que abren al tocarlas. La revisión adversaria (seis lectores independientes sobre el diff) devolvió diez hallazgos y se arreglaron ocho; el grave era que el velo base de `:root` había subido de 72 a 88 % y cambiaba la barra de **escritorio** en claro sin decisión ni guardia. Lo que quedó abierto está en la §13.3 de la spec. **Lo único que puede cerrar la barra es un teléfono real** (§4).

**7 sep: entra también el panel de administración de Ingrid.** `p2/acceso-asociados` trae identidad visual y tablero, páginas y tablas operativas unificadas, flujos de vacantes y gestión de imágenes, un conmutador de tema en la barra superior y la salida del panel hacia la portada. No comparte un solo archivo con la barra móvil, así que la fusión no tuvo conflictos. **Llegó con dos guardias en rojo, y ya lo estaban en su rama antes de fusionarla**: el tema había cambiado el tracking de los titulares de `-0.02em` a cero, que es decisión escrita del panel, y la bandeja de moderación de fotos usaba los grises de fábrica de Tailwind en vez de los tokens. Las dos quedaron ajustadas en un commit aparte, vistas rojas antes y verdes después. **Falta que Sua y Ingrid miren el panel con ojos**, porque la entrada exige el segundo factor y la sesión no pudo verlo.

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
| **Rama `p1-navbar-alternativa`** | ✅ **Fusionada en `main` el 5 sep (mediodía) por avance rápido, con la suite completa en verde sobre el árbol final**; 38 commits, dos de Ingrid (su portada a pantalla completa). Se conserva en `origin` como historia |
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

Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md` (el 3 sep salieron D-22 y las tres de la rama B; el 5 sep sale D-30; el 6 sep sale D-34, las dieciocho de la barra móvil; el 7 sep sale D-35, fusionar y empujar). **Las D-01, D-04 a D-12 y D-20 caben en una sola reunión con Natalia con esta tabla impresa; D-30 la resolvió Sua el 5 sep, la B fusionada y desplegada, y sale de aquí.**

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| **D-31** | **`prefers-reduced-transparency` en un equipo real** antes de la demo: Playwright acepta la emulación y no la aplica. | Sua | 5 sep | — |
| **D-32** | **Idiomas como subsistema propio**: `lang/`, middleware de locale, traducir vistas y volver multilingüe la tabla de ajustes. **Ampliación de alcance: acta antes de codificar.** El chip de la B es su sitio reservado y no funciona a propósito | Natalia + Sua | 3 sep | — |
| **D-33** | **Qué cede en la barra entre 1024 y ~1130 px de ancho** (iPad mini y los iPad de 10 pulgadas antiguos, en horizontal): en `inicial` y `atención` los tres módulos suman 1.102 px y la píldora mide 968. Con el logo protegido, a 1024 en inicial la píldora desborda 124 px a la derecha y el documento gana 107 px de desplazamiento horizontal (medido el 5 sep); en scroll cabe (727 px). Opciones: isotipo también en inicial por debajo de ~1150 px, o esconder el texto de «Mi cuenta» en esa franja; ninguna se toma sin Sua. Ya desbordaba antes de la rejilla, **y ahora está en producción** | Sua | 5 sep | — |
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
- **De la fusión con la portada de la P2 (5 sep, mediodía):** el rótulo del video va al pie del hero por decisión de la sesión, no de Ingrid; el commit `239eda0` de Ingrid entró en `main` con un `Co-Authored-By: Claude Opus 5` que el resto de la historia no lleva (reescribirlo exigía forzar su rama publicada y no se hizo sin preguntar); `.cromo-fijo` aparta 7rem la primera sección de las demás páginas con un selector de hermanos (`header + aside + main`) que depende del orden del layout.
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

Sobre **`8e53813` (`main` tras la fusión, con la portada de la P2)**, 5 de septiembre de 2026, mediodía. Las de `main` (`6c1b8b7`) son las del 3 sep salvo donde se indica. **Vuelve a medirlas antes de citarlas** en un documento. **6 sep:** la suite entera no se volvió a correr; sobre `09e8c17` solo corrieron las siete clases de la barra (`NavbarTresEstadosTest`, `ObjetivoTactilTest`, `TemaClaroOscuroTest`, `NavegacionAgrupadaTest`, `MenuMovilTest`, `MovimientoTest`, `EscenaPublicaTest`): 118 casos · 118 pasan · 1.258 aserciones · 24 s.

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
| **Suite en `main`** | **1.032 casos · 1.021 pasan · 11 omitidas · 0 fallos · 4.104 aserciones** · 339 s (5 sep, mediodía, sobre `8e53813`, el árbol que se despliega) | `php artisan test --compact` |
| Suite en `main` | 1.010 casos · 999 pasan · 11 omitidas · 0 fallos · 3.800 aserciones (3 sep, sobre `f83c9ea`) | `php artisan test --compact` |
| Barra B, medida en Chromium (5 sep) | módulo principal 44 px en scroll (antes 39,7) · indicador 44×44 (antes 32×40) · botón de tema 44×44 · chip de idioma 50×44 · filas de popover 45,7 · cabecera móvil 56 px (igual que `main`) · panel «El gremio» 224×155 alcanzable por `elementFromPoint` en los tres estados | `playwright-cli --raw eval` |
| **Barra móvil 2.1, medida en Chromium (6 sep, ya construida)** | inicial: bandeja 56 · fila de pestañas 68 · módulo inferior en 776..844 a 390×844 · pestañas 79,6×68 y 75,6×68 · scroll: 48 y 48 con los rótulos plegados y el isotipo visible · objetivos de 44 en las cuatro esquinas para pestañas, logo (153,5×44), chip (212×47,8) y «Afíliate» (45,7) · sin desbordar a 360, 320 ni 768 · el cambio de estado cae entre 30 y 40 px bajando y subir 13 devuelve · hojas por toque con `pointer: coarse` verdadero, cierran por toque fuera, por 30 px de desplazamiento y por Escape devolviendo el foco · con el video corriendo, 180 fotogramas: p50 6,1 ms, p95 6,3, máximo 6,5 (este equipo, no el teléfono) · velo 72 % en escritorio y 88 % bajo 64rem | `playwright-cli` con toques por CDP |
| Barra móvil anterior, medida en Chromium (6 sep, punto de partida de la 2.1) | cabecera 56 px a 390, 360, 768 y 844 de ancho · logo 153×40 (h-7 más `py-1.5`) · hamburguesa 44×44 · barra lateral de tema a 16 px del borde inferior (322,770 a 390×844) · rótulo del video a **134 px** del borde inferior a 390×844 y a **112** a 360×800 · el hero mide 764 px sobre 390 de alto en apaisado · primera sección de /contacto apartada 112 px · `.cromo` computa `transform: matrix(1, 0, 0, 1, 0, 0)` (bloque contenedor) y `.bandeja` `backdrop-filter: blur(20px) saturate(1.8)` | `playwright-cli run-code` (`f4/movil-base.js`) |
| Video del hero | 10,0 s · 1280×768 · 1.550.175 B · póster 27.188 B | `ffprobe` |
| Producción (5 sep) | Portada **200** en 2,97 s en frío | `curl -o /dev/null -w` |

## 6. Lo siguiente, en orden

1. **Confirmar que el documento de práctica se envió** el 4 sep (Sua). Si no, es 0.0 y hay que hablar con el docente.
2. **Ver la barra en un teléfono real** (Android y un iPhone): Safari de iOS, la barra de direcciones que crece y encoge, el rebote elástico, el teclado y la transparencia reducida son lo único que Chromium no enseña, y es lo que exige la S7 del cronograma. Si algo cede, se corrige sobre `main` y se vuelve a empujar.
3. **Empujar `main`** cuando Sua quiera: lleva el «Afíliate» escondido con sesión (`09e8c17`) y la Parte II; **el push despliega**. Y comprobar en la consola de Cloud que el despliegue de `809c4fa` (5 sep) corrió.
4. **Enseñar a la dirección la barra desplegada** (D-30 ya ejecutada por Sua) y cerrar el Acta 06 (D-26) con el cambio de barra. Antes, si se puede, **D-31**: transparencia reducida en un equipo real y un iPad de verdad; y **D-33**: qué cede entre 1024 y 1130 px, que ya está en producción.
5. **Lo que queda de la fusión:** correr `ContenidoOficialSeeder` una vez en producción **con visto bueno aparte, porque toca datos** (sin él la portada usa los textos de respaldo del rótulo y la frase corta no se pinta); confirmar con Ingrid dónde va el rótulo del video y los dos portadores claros; corregir el documento de práctica, que describe la barra A; y decidir con ella si se reescribe `239eda0` para quitar el `Co-Authored-By`.
6. **Una sola reunión con Natalia** con la tabla del §3 impresa: D-01, D-04 a D-12, D-20, D-27, D-28, D-29. Si está la cuenta de Google del gremio, se hace ahí mismo el SMTP (D-07).
7. **Resolver D-28 antes de la demo**: sin credenciales de afiliado no hay a quién enseñarle el directorio de proveedores ni el banco de talento.
8. **Fijar la demo 2** (D-20) para el jueves 10 o viernes 11; guion de siete pantallas; sitio despierto media hora antes.
9. **Franja visual, lo que queda** (Ingrid): las 19 fotos, pies de foto, D-29.
10. **Backend tras el SMTP**: enlace del correo de ficha publicada; bucket (D-13); disco privado para fotos pendientes; procedencia de semillas (D-14); `noindex` (D-08); filtro de municipios; `lang/es`; medición de rendimiento completa; importar la base de 48 filas desde `D:/Sua_Files/material-asobares/`.
11. Semana 8: dominio y SSL, manual actualizado y en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
