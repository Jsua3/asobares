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
| Fecha | Miércoles 9 de septiembre de 2026 (Bogotá), madrugada |
| **Dónde vive este archivo** | En `p1-auditoria-y-metricas`; medido sobre `759b43a`, y este archivo entra en el commit de cierre |
| `main` | `75b7646` **en local**, con `p1-cierre-bolsas` ya fusionada por avance rápido · ⚠️ **`origin/main` sigue en `0594058` y no se ha empujado, a propósito**: en este proyecto **el push a `main` despliega solo** —a los 68 s, con `migrate --force` dentro (bitácora §41, runbook §5)—, así que empujarlo es publicar |
| Rama de trabajo | **`p1-auditoria-y-metricas`, cinco commits sobre `main`. ⚠️ Sin empujar y sin fusionar.** Trae los nueve arreglos de la auditoría del 9 sep, el **Acta 08** y el expediente. **Una migración**: `entradas` en `visitas_diarias` |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5). Suite completa sobre este árbol, el 9 sep de madrugada: **1.209 casos · 1.198 pasan · 11 omitidas · 0 fallos · 5.394 aserciones** en 353 s. El árbol se contó el mismo día (§5) |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud` · **sirve `0594058`**: el panel en el teléfono y nada de lo del 8 ni del 9. Comprobado el 9 sep: las catorce rutas públicas responden 200 entre 0,75 y 1,23 s, el sitemap sale en `https` y las cookies van `secure` |
| **Expediente** | Al día: esta foto, **§45, §46 y §47 de `bitacora.md`**, **once líneas nuevas** en `encargo.md` §13, el **Acta 08** emitida, el **runbook §5.1** nuevo y el **manual en 1.3**. ⚠️ La **matriz de trazabilidad** se quedó en las cifras del 8 sep: hay que rehacerla antes de citarla |

## 1. Qué se exige y cuándo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **Vencido: vie 4 sep, 11:59:59 pm** | Documento de práctica corregido según la revisión CG del 31 ago. Está en el repositorio (`docs/ingenieria/Semana 7 - Documento - Juan Jose Sua - correccion.docx`, `1e3b365`). **El repositorio no registra si se envió**: confirmarlo. Y sigue describiendo los RF de proveedores como públicos y sin el banco de talento (§2.5) | Docente asesor. Tarde = 0.0 |
| **Esta semana: entre hoy y el vie 11 de sep** | **Segunda demostración con la capa visual levantada**, sobre la URL pública, en el teléfono del directivo. Fecha exacta sin fijar: pedir jue 10 o vie 11 (D-20). Ya no es elegir barra A o B —Sua eligió la B el 5 sep y está desplegada—: lo que toca es enseñar el resultado (los seis vídeos siguen en el scratchpad de la sesión del 5 sep) y cerrar el Acta 06 (D-26) con el cambio de barra | Directivo del capítulo (`R24 04:52`) |
| 7 – 11 sep | Pruebas en dispositivos reales y corrección de lo que salga. **Es la semana, y no se ha hecho**: hay dos superficies nuevas que solo un teléfono cierra —la barra pública móvil 2.1 y el riel del panel— | Cronograma firmado (S7) |
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

**La capa visual, en cuatro tramos, todos en producción.** El detalle de cada uno está en `bitacora.md` §39 a §44 y en la spec `docs/ingenieria/navbar-tres-estados-diseno.md`; aquí queda lo que sigue abierto.

- **Barra pública de escritorio (opción B, D-30, 5 sep).** Tres estados sobre un solo DOM, resortes `linear()` con respaldo `@supports`, popover de tema Claro · Oscuro · Sistema, chip **ES** con English «próximamente», y el hero de la portada de Ingrid a pantalla completa con el header fijo encima. Fusionada y desplegada. **Abierto:** `prefers-reduced-transparency` sin verificar en equipo real (**D-31**, Playwright acepta la emulación y no la aplica) y la franja **1024–1130 px**, donde los tres módulos no caben en la píldora (**D-33**, ya en producción).
- **Barra pública del teléfono 2.1 (D-M1 a D-M18, 6–7 sep).** Sin hamburguesa ni panel: arriba marca, tema y «Afíliate» (o el nombre y el rango con sesión); abajo, fija y principal, cinco pestañas con dos hojas que abren al tocar. Once tareas, treinta y una mutaciones, revisión adversaria de seis lectores con diez hallazgos y ocho arreglados —el grave era el velo base subido de 72 a 88 % que cambiaba la barra de **escritorio** sin decisión ni guardia—. **Abierto:** lo que queda de la §13.3 de la spec, y **solo un teléfono real la cierra**.
- **Panel de administración de Ingrid (7 sep).** Identidad visual y tablero, páginas y tablas unificadas, flujos de vacantes y gestión de imágenes, conmutador de tema en el cromo y salida del panel hacia la portada. Llegó con dos guardias rojas de su propia rama —el tracking de titulares y los grises de fábrica en la bandeja de moderación—, ajustadas en commit aparte. El widget de pendientes salía a un sexto de fila por dos causas: la vista propia sin el envoltorio de la rejilla y un `columnSpan` sin desglosar que Filament solo aplica desde `lg`. **Abierto: que Sua e Ingrid miren el panel con ojos**, porque el segundo factor impide que lo vea una sesión automatizada.
- **Barra lateral del panel, escritorio (D-L1 a D-L28, 7–8 sep madrugada).** Dejó de ser una franja burdeos con paleta privada: el fondo es un campo de puntos que huyen del cursor detrás de toda la interfaz, cada apartado es una lámina de cristal, la zona la marca un resplandor rojo que invierte con el tema, la fila subió de 43,5 a 48 px porque ninguno de los 24 destinos pasaba el mínimo táctil, y la cuenta subió al cromo. Las doce tareas del plan están cerradas. **Abierto:** medir **el coste del campo de puntos en marcha**, que solo puede hacer Sua porque el navegador no pinta fotogramas con la ventana detrás.

**8 sep (tarde): el panel en el teléfono es un riel de iconos, y ni el riel ni el cajón tienen suelo.** Seis commits directos a `main`, ya desplegados. Sua mandó una captura —«la barra del panel en el móvil está terrible»— y lo primero que salió no era de diseño: **el tema declaraba `position: relative` en `.fi-sidebar`**, y como el CSS compilado no lleva capas (lightningcss las aplana), esa regla le ganaba al `fixed` de Filament en todas las anchuras. En el teléfono el cajón cerrado dejaba de estar fuera de pantalla y ocupaba sus 252 px en el flujo —la franja vacía de la captura—; **en escritorio la barra llevaba dos días perdiendo su `lg:sticky`** y se iba con el desplazamiento. Se retiró con guardia. Encima de eso, **D-L29** (el riel: por debajo de 64 rem la barra se estrecha en vez de irse, con los nombres escondidos por recorte visual y no por `display: none`, que dejaría enlaces sin nombre accesible) y **D-L30** en dos partes (cromo con el logotipo centrado por rejilla, perfil anclado al pie flotando sobre la lista y pintado en dos ganchos con la copia sobrante apagada por `display: none`, riel de 4 rem para que el aire no se pague con el dedo, y un resorte por icono integrado con muelle que responde a la velocidad del gesto). Después, **las siete correcciones que Sua nombró viendo el teléfono**: el logotipo de 6,5 a 10 rem, el menú de la cuenta abriendo hacia arriba y hacia dentro, el corte de cinco píxeles entre cromo y cajón, «Tablero» —que no tiene grupo y Filament pinta suelto— convertido en módulo, el resorte moviendo el módulo y no la fila, y el cajón que dejó de ser un cuadrado blanco. **Ese último Sua lo rechazó dos veces y la segunda tenía razón**: volverlo lámina de cristal seguía siendo una barra detrás de los módulos, así que el cajón se quedó **sin suelo ninguno**, igual que el riel, y entre los módulos se ve la página atenuada. Eso movió la carga del contraste al cristal de cada módulo, que dentro del cajón sube al **84 %** —al 66 % del riel, sobre página negra, el rótulo del ítem activo da 3,03:1 y no pasa— porque detrás del módulo del riel hay un color conocido y detrás del cajón hay página, que puede ser cualquier cosa. **Abierto: el tacto del resorte en un teléfono de verdad**, que no se puede medir aquí; lo gobiernan `ARRASTRE` y `AMORTIGUACION` y se ajustan en una línea cada una.

**8 sep (noche): el plan de trabajo de Ingrid, medido contra el código y ejecutado en rama propia.** Ingrid mandó un documento que reparte cinco frentes a Sua. Medidos contra el repositorio antes de tocar nada, **tres estaban total o casi totalmente construidos** —las bolsas desde el 4 sep, el orden alfabético de la portada desde antes y con pruebas, y del WhatsApp existían el ajuste, el ayudante y cero números cableados—, y una parte pedía **revertir una decisión escrita** (cerrar `/empleo`, que Ingrid misma descartó el 4 sep). Sua resolvió dejarla pública. Lo que sí faltaba, y el plan no nombraba, eran dos huecos: **los contactos de artistas seguían públicos** mientras los de proveedores ya no, y **el banco de talento no tenía puerta** —quien dejaba su perfil en `/empleo` era visible al instante para todos los afiliados sin que nadie lo mirara—. Los dos cerrados. Beneficios por territorio y analítica no figuran en ningún RF, así que salieron por **Acta 07**, emitida antes del código y aprobada por Sua, con contrapropuesta aceptada en analítica: **anónima y sin visitantes únicos**. Cuarenta y dos casos nuevos, todos vistos rojos antes y rotos después; **tres pasaban por el motivo equivocado** y se arreglaron al mutarlos (bitácora §46). **Nada de esto está en producción**: vive en `p1-cierre-bolsas` sin empujar.

**Lo que este tramo enseñó y conviene no volver a pagar:** en el CSS compilado de este proyecto **el orden no es nuestro** —lightningcss aplana capas, agrupa medias y mueve reglas, así que dos reglas de la misma especificidad no se resuelven como están escritas—; pasó dos veces seguidas y se arregló sin depender del orden. Y **la maqueta mintió tres veces**, que es por lo que el defecto del `position` llevó dos días invisible: declaraba `position` sobre la barra, ponía su `<style>` después de la hoja compilada y no reproducía el `opacity: 1` que el blade le da al contenido. Las tres corregidas; la maqueta pinta ya el cromo entero, el ítem suelto, la cuenta al pie y la cabecera del cajón, y trae `--cerrada` para mirar el riel.

### 2.2 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| **Los 126 ajustes sembrados** | ⚠️ En producción hay **109**. Los **17 nuevos** de la capa visual **no existen todavía en la base de producción** | Correr `ContenidoOficialSeeder` una vez. Dieciséis traen respaldo en la vista; `hero_frase_corta` no se pinta hasta que alguien la teclee (así, a propósito) |
| **Franja «El gremio en cifras»** (D-25, Acta 05) | ✅ Código en producción, vacía de fábrica | **Firmar el Acta 05**; fijar las cuatro cifras; teclearlas |
| **Banda de videos de la portada** | ⚠️ Tres huecos con títulos de fábrica y un solo video real (`src` en `null`) | D-29: recortarla a lo que existe |
| Guía normativa | ⚠️ **1 municipio de 12** (Armenia) | D-21; formatos oficiales por entidad sin llegar |
| Portada | ✅ Todo texto editable | Las 19 fotos autorizadas sin colocar |
| Aliados | ✅ 23 del catálogo oficial | Logos (D-06); cuáles aplican al Quindío (D-18) |
| Beneficios e iniciativas | ✅ 5 y 5, de documento oficial | **Clasificarlos por alcance** (D-39): el código está en la rama y nacen sin clasificar a propósito |
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
| Despliegue de hoy | ✅ Los seis commits del teléfono están en producción; comprobado por contenido servido (§0) |
| **Rama `p1-cierre-bolsas`** | ✅ **Empujada el 8 sep**, siete commits, lista para revisión (`https://github.com/Jsua3/asobares/pull/new/p1-cierre-bolsas`). ⚠️ **Sin fusionar y sin desplegar.** Trae **tres migraciones** (`aprobado_el` en aspirantes, alcance en beneficios, `visitas_diarias`), así que desplegarla exige `migrate` |
| **`main`** | ⚠️ Fusionado **en local** hasta `9a6fef6`; `origin/main` sigue en `0594058`. **El día que se empuje, se despliega**: no hay paso intermedio. Antes de ese push conviene tener resuelta la D-40, o el banco de talento se ve vacío desde el primer minuto |
| Video del hero en producción | ✅ Versionado en `public/videos/`; `VideoDelHeroTest` vigila el índice de git |
| Cuenta de Laravel Cloud | ✅ Existe, con medio de pago del gremio. ⚠️ Organización `juan-sua`: facturación y Natalia como miembro (D-12) |
| Correo saliente (SMTP) | ❌ **Sin contratar: bloque de arriba** |
| Bucket | ❌ Sin crear; condiciona fotos sin moderar y formatos oficiales (D-13) |
| Dominio propio | ❌ Semana 8 (D-09) |
| Indexación | ✅ **Resuelta el 9 sep** (D-08 cerrada, `encargo.md` §13). `SITIO_INDEXABLE`, cerrada de fábrica, gobierna a la vez `robots.txt` y la etiqueta del layout. Se abre el día del dominio propio poniéndola en `true` y redesplegando. ⚠️ **Todavía no está en producción**: vive en la rama |
| **Scheduler (tareas diarias)** | 🚨 **Sin activar, y es lo primero de la lista.** Las tres purgas de datos personales no corren en producción hasta que se le añada un *Scheduler* al entorno en Cloud. Runbook §5.1 (nuevo). `CalendarioDeTareasTest` vigila el lado del código; el del entorno no lo puede ver una prueba |
| Rendimiento contra la URL | ⚠️ Solo portada: **2,97 s en frío** (5 sep). Sin volver a medir desde entonces |
| Dispositivos reales (RNF-01, RNF-07) | ❌ **Sin hacer, y es la semana (S7).** Hay dos superficies que solo cierra un aparato: la barra pública del teléfono y el riel del panel |
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
| Retención automática | ⚠️ **El código está y el disparador no.** Tres purgas diarias escritas, configuradas y probadas —y desde el 9 sep con guardia propia (`CalendarioDeTareasTest`)—, pero **en producción no las llama nadie** hasta que se active el Scheduler (D-42). Mientras tanto, `/politica-de-datos` le promete al titular un borrado automático que no está ocurriendo |
| Banco de talento: quién lo aprueba | ✅ Desde el 9 sep queda **en la bitácora, con nombre y hora**. Era la única decisión del panel sobre datos personales sin rastro: aprobar entrega nombre, teléfono y correo a todos los afiliados, y retirar borraba la única huella |
| Sobrescritura de perfiles ajenos | ✅ **Cerrada el 9 sep.** Un perfil aprobado ya no se toca desde el formulario público: la clave de aquel `updateOrCreate` era un correo tecleado por un anónimo, y con él cualquiera podía reescribir un perfil ajeno o sacarlo del banco |

### 2.5 Académico

| Qué | Estado |
|---|---|
| Corte 1 | ✅ 5.0 |
| Corte 2 | ✅ Entregado a tiempo el 21 ago |
| Corte 3 (60 %) | ⚠️ Documento corregido en el repositorio (`docs/ingenieria/Semana 7 - …correccion.docx`). **Sin confirmación de envío.** Sigue describiendo los RF de proveedores como públicos y sin el banco de talento, y describe la barra A, que ya no es la desplegada |
| Constancias | ✅ Acta 01, Formato 03, planeador. ❌ Acta 02 (S8), **Acta 04 y Acta 05 sin firmar**, **Acta 06 sin emitir** (D-26) |
| Menores | Encuesta de Santiago sin confirmar; el documento de Ingrid es aparte |

## 3. Registro único de decisiones pendientes

Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md` (el 3 sep salieron D-22 y las tres de la rama B; el 5 sep salió D-30; el 6 sep salió D-34, las dieciocho de la barra móvil; el 7 sep salieron D-35, fusionar y empujar, y las dieciocho de la barra lateral; el 8 salió **D-37**, que Sua respondió empujando la barra lateral a producción tras verla en claro y en oscuro). **D-L29 y D-L30, del 8 sep por la tarde, las pidió y las aprobó Sua el mismo día: salen fechadas en `encargo.md` §13.** **Las D-01, D-04 a D-12 y D-20 caben en una sola reunión con Natalia con esta tabla impresa.**

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| **D-38** | **La lista real de la auditoría funcional.** El plan del 8 sep dice que dio 44 PASS, **2 FAIL**, 1 BLOCKED y 3 NOT TESTED, y **no dice cuáles**. Los 2 FAIL son lo más accionable del documento y no se pueden cerrar a ciegas. Bloquea el único bloque del plan sin tocar | Ingrid | 8 sep | — |
| **D-39** | **Clasificar los cinco beneficios por alcance.** El código está y el panel lo permite en un minuto, pero de quién es cada beneficio lo dice un documento del gremio, no el sistema: nacen sin clasificar a propósito y sin clasificar no se anuncian | Natalia | 8 sep | — |
| **D-40** | **Aprobar los perfiles del banco de talento** antes de que alguien lo enseñe: desde hoy nacen pendientes y los que ya existían también, así que el directorio del afiliado sale vacío hasta que la secretaría los apruebe uno por uno | Natalia + secretaría | 8 sep | — |
| **D-41** | **El reparto que propone el plan de trabajo invierte el registrado** (20 ago: Sua lleva plataforma, panel, cartera, pagos, observatorio, infraestructura y suite; Ingrid, módulos públicos y contenido). El plan le da a Ingrid Bold, SMTP, afiliación e infraestructura, y a Sua los módulos públicos. Es la D-16 abierta: conviene que quede dicho y no asumido | Sua + Ingrid | 8 sep | — |
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
| **D-42** | **Activar el Scheduler en Laravel Cloud.** No es una decisión: es un clic que solo puede dar una persona, y sin él las tres purgas de datos personales no corren nunca mientras la política publicada promete que sí. Runbook §5.1 | Sua | 9 sep | — |
| **D-43** | **¿Vuelve la campana del panel?** El 7 sep se apagó (D-L22) y el 9 se retiró a quien escribía en ella, que llevaba dos días guardando avisos invisibles. Hoy se avisa por contador de menú y tablero. Si el gremio quiere avisos de verdad —campana, correo o los dos—, es un frente propio con su decisión, tal como dejó dicho D-L22 | Sua + Natalia | 9 sep | — |
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

- **De la auditoría del 9 sep, lo que se dejó dicho y no cerrado:** las **once capturas del manual** son del 18 de agosto y el panel se rehizo el 7 y el 8 de septiembre, así que enseñan una pantalla que ya no existe — el texto sí está al día, y solo Sua o Ingrid pueden rehacerlas porque el segundo factor no deja entrar a una sesión automatizada (§11 del manual trae el guion). La **matriz de trazabilidad** se quedó en las cifras del 8 sep. Y el conteo de entradas **empieza el 9 de septiembre**: las filas anteriores llevan cero porque el dato no se recogía, no porque no hubiera visitas, y eso hay que decirlo la primera vez que se enseñe la gráfica.
- **Los tres widgets del flujo no se han visto con ojos.** Se entró al panel de verdad —el código del segundo factor sale en `storage/logs/laravel.log` con el correo local— y ahí se acabó: el navegador de esta máquina no compone con la ventana detrás, `IntersectionObserver` no dispara y los nueve widgets diferidos se quedan en «Cargando…». Hay pruebas de renderizado completo, que es el sustituto honesto, no el equivalente.
- **Del panel en el teléfono (8 sep):** el tacto del resorte solo lo cierra un aparato de verdad; se ajusta con `ARRASTRE` y `AMORTIGUACION`, una línea cada una.
- **Del plan de trabajo (8 sep, noche):** las dos gráficas de analítica **no se han visto con ojos**, porque el segundo factor impide que una sesión automatizada abra el panel —mismo hueco que la barra lateral, y sin maqueta esta vez—; el filtro `filament.` del contador de visitas es seguro que hoy no puede dispararse, y queda dicho en el código para que nadie lo lea como algo respaldado por una prueba; y el sello de alcance **etiqueta cada beneficio pero no los agrupa** en las tres pantallas, que es lo mínimo que distingue sin rehacer tres vistas.
- **De la barra pública B, anotado por su revisión final:** la transición de `gap` aporta poco y cuesta un reflow por fotograma durante 620 ms; `backdrop-filter` no se transiciona (aparece de golpe); el brillo de los tres módulos se mueve al unísono (así lo manda la spec; el comentario del marcado dice otra cosa); `$rol`/`$prefijoRol` son dos `match` que recalculan lo mismo.
- **De la fusión con la portada de la P2 (5 sep):** el rótulo del video va al pie del hero por decisión de la sesión, no de Ingrid; el commit `239eda0` de Ingrid entró en `main` con un `Co-Authored-By: Claude Opus 5` que el resto de la historia no lleva (reescribirlo exigía forzar su rama publicada y no se hizo sin preguntar); `.cromo-fijo` aparta 7rem la primera sección de las demás páginas con un selector de hermanos (`header + aside + main`) que depende del orden del layout.
- **De la barra B, visto el 5 sep al medir la rejilla:** la franja 1024–1130 px en inicial y atención (D-33). No se toca sin decidir qué cede.
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

Todas medidas el **8 de septiembre de 2026 sobre `fc2142f`** (la rama), salvo las que llevan otra fecha en su fila. **Vuelve a medirlas antes de citarlas** en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | **402** | `git rev-list --count HEAD` |
| Migraciones | **43** (entra `entradas` en `visitas_diarias`) | `Get-ChildItem database/migrations -File` |
| Modelos | **22** | `Get-ChildItem app/Models/*.php` |
| Sembradores | 21 (+ `Support/`) | `Get-ChildItem database/seeders/*.php` |
| Fábricas | **17** (entra `MensajeFactory`) | `Get-ChildItem database/factories/*.php` |
| Archivos de prueba | **97** | `Get-ChildItem tests -Recurse -Filter *Test.php` |
| Métodos de prueba | **933** | `Select-String '^\s*public function test_'` |
| Vistas Blade | **78** | `Get-ChildItem resources/views -Recurse -Filter *.blade.php` |
| Componentes públicos | **21** | `Get-ChildItem resources/views/components/publico/*.blade.php` |
| Panel | 19 recursos · 6 páginas · 20 policies · **9 widgets** | `Get-ChildItem app/Filament/…` |
| Comandos de Artisan propios | 6 | `Get-ChildItem app/Console/Commands` |
| Enums | **17** | `Get-ChildItem app/Enums` |
| Controladores públicos | **18** | `Get-ChildItem app/Http/Controllers/Publico/*.php` |
| Middleware propio | **3** | `Get-ChildItem app/Http/Middleware` |
| Archivos de configuración | **17** (entra `sitio.php`) | `Get-ChildItem config/*.php` |
| Rutas GET propias | **89** | `php artisan route:list --method=GET --except-vendor --json` |
| Ajustes que siembra `SettingSeeder` | **124** (bajan dos: `hero_subtitulo` y `cifra_afiliados`, jubilados por no leerlos nadie); en producción **109**, del 3 sep | reflexión sobre `SettingSeeder::ajustes()` |
| **Suite completa** | **1.209 casos · 1.198 pasan · 11 omitidas · 0 fallos · 5.394 aserciones** · 353 s (9 sep, madrugada, sobre `759b43a`) | `php artisan test --compact` |
| Clases nuevas de la auditoría | `CalendarioDeTareasTest` 6 · `AjustesQueSirvenParaAlgoTest` 4 · `IndexacionDelSitioTest` 6 · `AvisoDeMensajeAlGremioTest` 15 · `FlujoDeEntradasAlSitioTest` 15 · `Panel\BitacoraTest` 3 · `Panel\AvisosQueSeVenTest` 1 | `php artisan test --compact --filter=` |
| Comprobado contra servidor corriendo (9 sep) | `noindex, nofollow` en la portada servida · `Disallow: /` en el `robots.txt` servido · 5 vacantes en el sitemap · **0** visitas contadas para `sitemap`, `robots` y `guia.formato` | `curl` + `php artisan tinker` sobre `artisan serve` |
| Suite sobre el árbol desplegado (`0594058`) | 1.105 casos · 1.094 pasan · 11 omitidas · 0 fallos · 5.048 aserciones · 317 s (8 sep, tarde) | `php artisan test --compact` |
| Clases nuevas del plan de trabajo | `AccesoDeAsociadosTest` 16 · `ModeracionDeBolsasTest` 27 · `BotonFlotanteDeWhatsappTest` 9 · `BeneficiosPorAlcanceTest` 10 · `AnaliticaDelSitioTest` 14 | `php artisan test --compact --filter=` |
| Botón de WhatsApp, medido en Chromium (8 sep) | 56×56 px · a 375: acaba en 732 con la barra de pestañas empezando en 744, **12 px de holgura y sin solaparse** · a 1.280: 24 px del borde · los cinco puntos del círculo devuelven el botón (las esquinas del rectángulo caen fuera del círculo, que no es defecto) | `javascript_tool` sobre el servidor local |
| Sello de alcance, medido en Chromium (8 sep) | los tres sellos dicen «ASOBARES Colombia», «ASOBARES Quindío» y «Armenia» · contraste **4,53:1** sobre el fondo de la página, el mismo que da `text-apagado` en el resto del sitio | `javascript_tool` con los tres alcances puestos |
| Analítica, comprobada contra el servidor de desarrollo (8 sep) | dos visitas a `/contacto`, una a `/`, una a `/directorio`, el 404 sin dejar fila · columnas exactamente `id, ruta, dia, total, created_at, updated_at` | navegación real + `php artisan tinker` |
| **Panel en el teléfono, medido en la maqueta a 375 px (8 sep)** | riel **64** px · módulo **48** · aire **8** a cada lado, también para el ítem suelto · logotipo a **5 px** del centro de la pantalla · cuenta al pie absoluta, `z-index: 10`, 64×64 · lista reservando **68 px**, último destino acabando en 722 y la cuenta empezando en 748 · cristal del módulo al **66 %** en el riel y al **84 %** dentro del cajón · el resorte moviendo **seis módulos** −3,5 px con un gesto de 10, y las filas a cero | `php artisan maqueta:barra` + `playwright-cli` |
| **Contraste del rótulo del ítem activo dentro del cajón (8 sep)** | al 66 % sobre página negra **3,03:1 — no pasa**; al 84 %, **4,61** sobre negra y **5,49** sobre blanca, y en oscuro **5,30** y **4,78**. Ese 84 es el suelo medido, no una preferencia | `MideContraste`, con el velo de cierre de Filament en medio |
| Barra pública móvil 2.1, medida en Chromium (6 sep) | inicial: bandeja 56 · fila de pestañas 68 · pestañas 79,6×68 y 75,6×68 · scroll: 48 y 48 con los rótulos plegados · objetivos de 44 en las cuatro esquinas · sin desbordar a 360, 320 ni 768 · el cambio de estado cae entre 30 y 40 px bajando y subir 13 devuelve · con el video corriendo, 180 fotogramas: p50 6,1 ms, p95 6,3, máximo 6,5 (este equipo, no el teléfono) · velo 72 % en escritorio y 88 % bajo 64rem | `playwright-cli` con toques por CDP |
| Barra pública B de escritorio, medida en Chromium (5 sep) | módulo principal 44 px en scroll · indicador 44×44 · botón de tema 44×44 · chip de idioma 50×44 · filas de popover 45,7 · panel «El gremio» 224×155 alcanzable por `elementFromPoint` en los tres estados | `playwright-cli --raw eval` |
| Video del hero | 10,0 s · 1280×768 · 1.550.175 B · póster 27.188 B | `ffprobe` |
| Producción (5 sep) | Portada **200** en 2,97 s en frío | `curl -o /dev/null -w` |

## 6. Lo siguiente, en orden

0. 🚨 **ACTIVAR EL SCHEDULER EN EL PANEL DE LARAVEL CLOUD.** Es un clic humano, no lo puede hacer una sesión ni una prueba, y sin él **los datos personales no se borran nunca** — mientras `/politica-de-datos` le promete por escrito al titular que sí, y el manual se lo promete al gremio. Environment → Resources → añadir *Scheduler*. Se verifica con `cloud environment:show -n` y, a las 24 horas, buscando `Depuración de datos` en la Bitácora del panel. El paso entero está en el **runbook §5.1**. Esto es de hoy y no puede esperar a la reunión.

1. **Empujar `main` cuando se quiera desplegar, y no antes.** Hay **dos ramas sin fusionar**: `p1-cierre-bolsas` (ya en `main` local) y `p1-auditoria-y-metricas` (los nueve arreglos del 9 sep). Juntas traen **cuatro migraciones** y, en cuanto entren, tres cosas que se notan y ninguna es un problema: **el banco de talento se ve vacío** hasta que la secretaría apruebe los perfiles (D-40), **los contactos de artistas desaparecen de la parte pública**, y **el sitio deja de ser indexable** hasta que se ponga `SITIO_INDEXABLE=true` el día del dominio propio.
2. **Confirmar que el documento de práctica se envió** el 4 sep (Sua). Si no, es 0.0 y hay que hablar con el docente.
3. **Pedirle a Ingrid la lista de la auditoría** (D-38): sin los 2 FAIL y los 3 NOT TESTED, el bloque de QA del plan no se puede cerrar.
4. **Ver las dos superficies nuevas en un teléfono real** (Android y un iPhone), que es lo que exige la S7 y lo que ya no puede esperar: la barra pública del teléfono —Safari de iOS, la barra de direcciones que crece y encoge, el rebote elástico, el teclado, la transparencia reducida— y el **riel del panel**, sobre todo el tacto del resorte. Si algo cede, se corrige sobre `main` y se vuelve a empujar.
5. **Medir el coste del campo de puntos en marcha** (Sua): no lo puede hacer una sesión automatizada, porque el navegador no pinta fotogramas con la ventana detrás. Si sale caro, se baja la densidad con un token.
6. **Enseñar a la dirección lo desplegado** y cerrar el Acta 06 (D-26) con el cambio de barra. Antes, si se puede, **D-31** (transparencia reducida en un equipo real y un iPad de verdad) y **D-33** (qué cede entre 1024 y 1130 px, que ya está en producción).
7. **Lo que queda de la capa visual:** correr `ContenidoOficialSeeder` una vez en producción **con visto bueno aparte, porque toca datos** (sin él la portada usa los textos de respaldo del rótulo y la frase corta no se pinta); confirmar con Ingrid dónde va el rótulo del video y los dos portadores claros; **que Sua e Ingrid miren el panel con ojos**; corregir el documento de práctica, que describe la barra A; y decidir con ella si se reescribe `239eda0` para quitar el `Co-Authored-By`.
8. **Una sola reunión con Natalia** con la tabla del §3 impresa: D-01, D-04 a D-12, D-20, D-27, D-28, D-29, y las dos nuevas que solo ella cierra: **D-39** (de quién es cada beneficio) y **D-40** (aprobar los perfiles del banco). Si está la cuenta de Google del gremio, se hace ahí mismo el SMTP (D-07). Y el Acta 07, para firmarla.
9. **Resolver D-28 antes de la demo**: sin credenciales de afiliado no hay a quién enseñarle el directorio de proveedores ni el banco de talento.
10. **Fijar la demo 2** (D-20) para el jueves 10 o viernes 11; guion de siete pantallas; sitio despierto media hora antes.
11. **Franja visual, lo que queda** (Ingrid): las 19 fotos, pies de foto, D-29.
12. **Backend tras el SMTP**: enlace del correo de ficha publicada; bucket (D-13); disco privado para fotos pendientes; procedencia de semillas (D-14); `noindex` (D-08); filtro de municipios; `lang/es`; medición de rendimiento completa; importar la base de 48 filas desde `D:/Sua_Files/material-asobares/`.
13. Semana 8: dominio y SSL, manual actualizado y en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
