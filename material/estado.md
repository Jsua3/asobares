# Estado vigente — Plataforma Web ASOBARES Capítulo Quindío

_La foto del proyecto hoy. **Se reescribe entero** al cerrar toda sesión que cambie algo: sin tachones, sin «superado», sin «esta línea decía». Lo que se cierra sale de aquí y queda contado en `bitacora.md`; lo que se decide sale de aquí y entra en «Decisiones que rigen» de `encargo.md`. Si el commit del encabezado está atrás de `main`, lee las entradas de bitácora posteriores a él y actualiza esto **antes** de tocar nada. `tests/Feature/GuardiaDelEstadoTest.php` comprueba que el commit del encabezado exista de verdad._

## 0. Medición

| | |
|---|---|
| Fecha | Martes 1 de septiembre de 2026, segunda sesión del día (Bogotá) |
| `main` | `54ddcbb` · 276 confirmaciones · al cerrar esta sesión `division-prompt-maestro` se fusionó a `main` por avance rápido, con este archivo en el commit siguiente · **`origin/main` quedó atrás: no se hizo `push`** |
| Rama de trabajo | Ninguna: la sesión cerró sobre `main` y borró `division-prompt-maestro` |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5.9). **La suite sí se ejecutó** (§5) |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud` responde **200** a una petición `HEAD` (esta sesión); contenido releído por última vez el 1 sep por la mañana (§31.7) |

## 1. Qué se exige y cuándo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **Jue 3 – vie 4 sep, 11:59:59 pm** | Documento de práctica corregido según la revisión CG del 31 ago («veo muy bien el documento»), en `.docx` con sus anexos, en el hilo «Entrega de documento de practicas». Meta interna: jueves 3 | Docente asesor. Tarde = 0.0 |
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
| OBS3-03 | ✅ Cerrado por decisión (1 sep): el sitio arranca en el tema del dispositivo, `system`, como estaba. Sin cambio de código (`encargo.md` §13) | — |
| OBS3-02 | ⚠️ Ranura y velo del hero puestos, **vacía** | Ya hay medio autorizado (OBS3-07): elegir entre las 19 fotos y el video, medir contraste en los dos temas; si es el video, decidir cómo se sirve (D-22) |
| OBS3-07 | ⚠️ **Insumo en mano** desde el 1 sep: uso autorizado de las 19 fotos y del video del Drive | Bajar el video del Drive a `material/nuevomaterial/`; pies de foto (D-03); ponerlos en el sitio (franja visual) |
| OBS3-09 | ⚠️ Mitad cerrada (acuse al postulante) | La otra mitad —que el afiliado consulte aspirantes— **prohibida** hasta releer `encargo.md` §9: datos de terceros |
| OBS3-10 | ⚠️ Código puesto | Las **7 URL de trámite** de Armenia. Insumo del gremio (D-04) |
| OBS3-11 | ⚠️ Código puesto (todo editable) | El **texto propio** de «Quiénes somos» y nombres/cargos confirmados (D-05) |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar** (D-01). Una fila sin marcar no aplaza: deja sin decidir |

De catorce, diez cerrados y cuatro vivos (07, 09, 10, 11); **ninguno de los cuatro se cierra escribiendo código**, y el 07 ya no está bloqueado: es trabajo de la franja visual con el insumo en mano. La franja de diseño que nadie ha hecho: dirección de arte escrita, hero con medio real (el fondo generativo queda como respaldo), estados vacíos que anuncien en vez de esconder (destacados y eventos no se pintan hoy), iconos distintivos de los cinco beneficios, tratamiento de los aliados sin logo, especificación de fotos para el manual. Es de la Persona 2 y puede correr en su worktree esta semana.

### 2.2 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| Guía normativa | ⚠️ **1 municipio de 12** (Armenia: 7 trámites + lista de verificación Ley 1801/decreto 119, fechados 20 ago, sin costos) | Salento y Filandia perdieron su contenido inventado; el selector ofrece solo Armenia y la página no dice por qué. Los otros 11: orden y fuente (D-21). **Formatos oficiales por entidad: habilitados el 1 sep**, Bomberos primero: se suben desde el panel al requisito («Formato oficial (PDF)», disco privado, hasta 5 MB) y la guía los sirve por `GuiaController`; **antes hace falta el bucket** (D-13), porque en Cloud lo subido desde el panel se pierde al redesplegar. Los PDF no están en local ni en el Drive enlazado: pedirlos. Dueño del mantenimiento: sin nombrar |
| Portada | ✅ Todo texto editable; promesas ajustadas a lo que existe | Medios: 6 imágenes y ningún video en la portada publicada; desde el 1 sep hay 19 fotos y un video autorizados (OBS3-07) |
| Aliados | ✅ 23 sembrados del catálogo oficial (19 comerciales con condición real privada + 4 institucionales) | Logos en buena resolución (D-06); cuáles aplican al Quindío y convenios locales (D-18) |
| Beneficios e iniciativas | ✅ 5 y 5, de documento oficial | — |
| «Quiénes somos» | ⚠️ Editable entero; texto provisional | Texto propio, nombres y cargos, relación con el Eje Cafetero y la Nacional (D-05, D-18) |
| Directorio | ⚠️ **0 fichas publicadas** en producción (correcto sin autorizaciones). En local, 41 en borrador (23 ago); la versión de 48 filas del 26 ago pendiente de importar y depurar | Autorización de cada titular (propuesta: con la sección VIII del formulario oficial); categorías reales ficha a ficha; nombres repetidos y NIT normalizado en el importador |
| Lista de verificación de funcionamiento (Ley 1801) | ✅ Sembrada como ficha de Armenia | Confirmar qué norma es el «decreto 119» (D-18) |
| Boletín laboral (Ley 2466 de 2025) | ❌ Sin publicar | Redactarlo como calendario vigente a 2026; revisión del aliado jurídico (D-18); categoría `normativa` en el enum (Persona 1) |
| Formulario oficial de registro | ❌ No está en `/afiliate` | Decidir descargable (opción A, dentro del alcance) o en línea por pasos (B, Fase II) (D-18) |
| Cifra pública de afiliados | ⚠️ El sitio dice 60; la base 48; el directivo 60 | La fija Natalia (D-18) |

### 2.3 Infraestructura

| Qué | Estado |
|---|---|
| Sitio | ✅ **200** sobre PostgreSQL 17.11 (`bold-leaf-62673759`, esquema `production`), 39 migraciones aplicadas, base con contenido oficial (1 sep) |
| Cuenta de Laravel Cloud | ✅ Existe, con medio de pago del gremio (30 ago). ⚠️ La organización se llama `juan-sua`: **comprobar a nombre de quién está la facturación**, añadir a Natalia como miembro y anotar el traspaso en el runbook §12 (D-12) |
| Correo saliente (SMTP) | ❌ **Sin contratar.** Lo que cuesta hoy, por lectura del código (la suite corre con `MAIL_MAILER=array` y no lo ve): el código del segundo factor **por correo** no sale —quien tenga la app TOTP configurada entra; nadie puede dar de alta el factor por correo—, y **PQR y postulación quedan guardadas pero el ciudadano ve la página de error**, porque `ContactoController:39` y `EmpleoController:148,158` llaman a `Mail::send()` sin `try/catch` (D-23). **El DNS de `asobares.org`, medido el 1 sep, descarta el Resend de `.env.staging.example`**: correo en Google Workspace, SPF solo para Google y Mailgun, DKIM de Google y de Brevo, DMARC `p=reject`. Vía sin tocar DNS: contraseña de aplicación de Google del buzón del gremio (D-07, replanteada; bitácora §33.4) |
| Bucket | ❌ Sin crear. Política acotada a `publico/*` (runbook §8.3) **antes** de crearlo. Arrastra el disco público con fotos sin moderar (D-13), y desde el 1 sep condiciona también los **formatos oficiales** de la guía y el **video del hero** si se sirve desde el sitio (D-22) |
| Dominio propio | ❌ Semana 8; sin nombre (D-09) |
| Indexación | ⚠️ Hoy `robots.txt` responde `Allow: /` con sitemap de 14 URL, sin `X-Robots-Tag` ni `<meta name="robots">`: el sitio invita a indexar un directorio y una bolsa vacíos bajo el nombre del gremio. Decidir `noindex` hasta el lanzamiento (D-08) |
| Rendimiento contra la URL | ❌ Sin medir. Los 972 ms del expediente son contra `localhost`; medir en caliente (scale-to-zero) |
| Dispositivos reales (RNF-01, RNF-07) | ❌ Sin hacer (S7) |
| Límite de gasto de Cloud (~US$10) y `LOG_STACK=stderr` | ⚠️ Confirmar en la consola |
| Repositorio | ⚠️ `Jsua3/asobares`, público, en cuenta personal; sin segundo administrador (D-12). `origin/main` atrás de `main` desde esta sesión |

### 2.4 Datos personales

| Qué | Estado |
|---|---|
| Fichas de asociados | ✅ Nacen en borrador; cero publicadas en producción; R-02 no se materializó |
| Fotos del propietario pendientes o rechazadas | ⚠️ Viven en el disco público, servidas por URL no enumerable (ULID). Decidir: disco privado con controlador, o aceptar el riesgo por escrito (D-13) |
| 19 fotografías del gremio y el video del Drive | ✅ **Uso autorizado por el gremio el 1 sep** (`encargo.md` §13). Viven en `material/nuevomaterial/`, que no se versiona; el video aún hay que bajarlo. Pies de foto pendientes (D-03). La regla del §9 sigue en pie para cualquier foto nueva |
| Política de tratamiento de datos | ❌ Texto legal definitivo (P-15), encargados que la política no nombra, canal de supresión y revisión legal (G12) (D-19) |
| `material/nuevomaterial/` | ✅ En `.gitignore`; no se versiona |
| Retención automática | ✅ Tres purgas diarias con `subMonthsNoOverflow()` (§28) |

### 2.5 Académico

| Qué | Estado |
|---|---|
| Corte 1 | ✅ 5.0 |
| Corte 2 | ✅ Entregado a tiempo el 21 ago (no revisado por César por uso evidente de IA) |
| Corte 3 (60 %) | ⚠️ Documento completo enviado el 27 ago; **revisado el 31 ago con buena valoración** (`Semana 7 - Documento - Juan José Sua - Revisión CG.docx`, comentarios anclados). Falta abrir la revisión, aplicar cada comentario sin tocar lo no observado, releer los capítulos 5 y 6 en voz propia, actualizar 5.5, la Tabla 5 y 6.2 con el despliegue en pie y las cifras medidas ese día, y **enviar el jueves 3** |
| Constancias | ✅ Acta 01 (diseño) y Formato 03 (retroalimentación) firmados el 25 ago; planeador firmado. ❌ Acta 02 (capacitación, S8) y **Acta 04 (ampliación de alcance, emitida 30 ago) sin firmar** |
| Menores | Encuesta de Santiago (19 ago) sin confirmar; el documento de Ingrid es aparte e individual |

## 3. Registro único de decisiones pendientes

Cada decisión con su dueño y la fecha en que se pidió. Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md` (el 1 sep salieron D-02 y la autorización de D-03). Consolida las DPV abiertas de la ERS v3 (5 ago), las doce confirmaciones del plan del material (26 ago), el bloque D del acta 3 (28 ago), el §31.8 y el §33.3. **Las D-01, D-04 a D-12 y D-20 caben en una sola reunión con Natalia con esta tabla impresa.**

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| D-01 | **Firma del Acta 04** (`docs/ingenieria/constancias/`): cada fila de OBS3-15 a 18 marcada antes del 22 / Fase II / se descarta, y las dos contrapropuestas del equipo (bitácora sin reversión genérica; sección de documentos del gremio en el portal en vez de módulo financiero) | Natalia + directivo | 30 ago | — |
| D-03 | **Pies de foto** de las 19 fotografías y del video (evento, fecha, lugar, quiénes), y el **video bajado** del Drive del gremio a `material/nuevomaterial/`. La autorización de uso ya está (1 sep, `encargo.md` §13) | Natalia (pies) · Sua (bajarlo) | 26 ago | Autorización: 1 sep ✅ · resto: — |
| D-04 | **Las 7 URL de trámite** de Armenia, o el contacto en la Alcaldía que las tenga (OBS3-10) | Natalia / Alcaldía | 28 ago | — |
| D-05 | **Texto propio de «Quiénes somos»**; nombre completo y orden de apellidos del presidente; cargo con el que firma Natalia; corrección de los datos del capítulo en el sitio de la Nacional (OBS3-11, OBS3-21, DPV-09, P-11) | Natalia + Nacional | 5 ago / 28 ago | — |
| D-06 | **Logos institucionales** en buena resolución (Asobares Colombia, Cámara, Comité Intergremial, Gobernación) y de los aliados comerciales (OBS3-04) | Natalia | 31 ago | — |
| D-07 | **SMTP con el correo del gremio — replanteada por el DNS** (bitácora §33.4). `asobares.org` tiene el correo en Google Workspace y DMARC `p=reject`: Resend, Postmark o una cuenta nueva de Brevo rebotan sin registros DNS que solo la Nacional puede añadir. **Opción A (sin DNS, recomendada):** verificación en dos pasos en `asobaresquindio@asobares.org` y una **contraseña de aplicación** de Google → `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_USERNAME=asobaresquindio@asobares.org`, `MAIL_PASSWORD=<la contraseña de aplicación>`; requiere que el administrador del Workspace no las tenga bloqueadas. **Opción B:** credenciales SMTP de la cuenta de Brevo o de Mailgun de la Nacional, que ya firman o están en el SPF. **Opción C:** la Nacional añade los registros de Resend. Se crea en la reunión; luego variables → despliegue → probar segundo factor y acuse | Natalia + Sua (A) · Nacional (B, C) | 15 ago / 30 ago / 1 sep | — |
| D-08 | **Indexación antes del lanzamiento**: `noindex` hasta el lanzamiento oficial (recomendado) o dejar indexar | Natalia + equipo | 30 ago | — |
| D-09 | **Dominio propio**: nombre, compra (cobro anual), titularidad y autorización de marca a la Nacional (DPV-08, OBS3-22) | Natalia | 5 ago / 28 ago | — |
| D-10 | **Pasarela**: confirmación escrita de «solo Bold» y cierre de la cuenta de BBVA (OBS3-23); medio PSE o QR y cuenta receptora (DPV-04); documentos para producción de Bold (RUT, cámara de comercio, cuenta bancaria) | Natalia + contadora | 28 ago | — |
| D-11 | **Cartera**: Excel con el formato real de la contadora, aunque sea con datos ficticios (OBS3-19); Drive vinculado o carga manual (OBS3-20); periodicidad (semanal por ahora) | Luisa + Natalia | 28 ago | — |
| D-12 | **Titularidad de la infraestructura**: a nombre de quién está la facturación de Cloud; Natalia como miembro de la organización; segundo administrador en GitHub; fecha de traspaso en el runbook §12 | Sua + Natalia | 30 ago | — |
| D-13 | **Bucket y fotos pendientes o rechazadas**: al disco privado servidas por controlador, o riesgo aceptado por escrito (§30.3). Desde el 1 sep el bucket condiciona también los formatos oficiales de la guía y el video del hero | Sua (técnica, se deja escrita) | 31 ago | — |
| D-14 | **Marca de procedencia en el contenido sembrado** (`origen` sembrador/oficina, o no sobrescribir filas editadas): sin ella, resembrar pisa lo que la oficina corrigió (§31.4) | Sua | 1 sep | — |
| D-15 | `GeneradorPdf`: borrar (cero consumidores) o conservar como molde para los formatos oficiales | Sua | 1 sep | — |
| D-16 | **Reparto Persona 1 / Persona 2**: reescribirlo como quedó de hecho o declarar su suspensión, antes del informe final y la Acta 02 (§30.3) | Sua + Ingrid | 31 ago | — |
| D-17 | **ERS v3 sin firma** desde el 5 ago y sus DPV abiertas: DPV-01 fecha oficial de lanzamiento; **DPV-02 qué se ve con sesión y qué sin ella** (condiciona 8 RF ya codificados); DPV-03 bolsa de empleo en V1 (ratificación); DPV-05 RF-05; DPV-10 alcance de cartera; DPV-11 tarifa de proveedores; **DPV-13 soporte después del 22 de septiembre** | Natalia + directivo | 5 ago | — |
| D-18 | **Las confirmaciones del plan del material (26 ago)** que siguen sin respuesta: base vigente y las 7 filas sin municipio; cifra de afiliados; relación con el capítulo Eje Cafetero; aliados que aplican al Quindío y convenios locales; programas nacionales que operan aquí; cuota y cuenta bancaria del formulario; qué norma es el «decreto 119»; revisión jurídica del boletín laboral; certificado desde el portal (ampliación); formulario oficial descargable o en línea | Natalia | 26 ago | — |
| D-19 | **Política de tratamiento de datos**: texto legal (P-15), encargados, canal de supresión, revisión legal (G12) | Natalia / aliado jurídico | 5 ago | — |
| D-20 | **Fecha de la segunda demostración** (entre el 4 y el 11; pedir jue 10 o vie 11) | Directivo + Natalia | 28 ago | — |
| D-21 | **Municipios 2 a 12 de la guía**: orden (los seis con afiliados primero), fuente por alcaldía, y qué se declara Fase II | Natalia | 1 sep | — |
| D-22 | **Cómo se sirve el video del hero**: peso y formato (MP4 corto, silencioso, con póster), desde el bucket público (D-13) o embebido; respeto de `prefers-reduced-motion` y contraste bajo el velo en los dos temas | Sua (técnica) + Ingrid (visual) | 1 sep | — |
| D-23 | **Qué ve el ciudadano cuando el acuse no sale**: capturar el fallo del transporte en PQR y postulación (registro guardado, mensaje sin acuse, fallo registrado) o dejar que falle. Hoy ve la página de error después de guardar (bitácora §33.4). Se corrige con su prueba en rojo | Sua | 1 sep | — |

## 4. Deuda diferida a propósito

No se «arregla de paso»:

- Los chips de filtro repetidos y los 104 `leading-*`/`tracking-*` sueltos viven en las vistas que la franja visual va a rehacer: **después del 11 de septiembre**.
- `@alpinejs/collapse` importado sin consumidor (retirarlo toca `package.json`, que exige aprobación).
- El consecutivo de PQR bajo concurrencia en PostgreSQL falla cerrado (error, no duplicado); arreglarlo toca el expediente de PQR: decisión del dueño.
- No existe `lang/`: las reglas sin mensaje propio salen en inglés; los siete formularios públicos están cubiertos por sus `messages()`. Pendiente `php artisan lang:publish` + `lang/es/validation.php`.
- Salento y Filandia sin guía tras retirar lo inventado: regresión visible aceptada; se resuelve con D-21 y con decirlo en la página.
- El filtro de municipios del **directorio** lista todos los de la tabla tengan o no fichas publicadas (causa raíz del §30.7); la guía ya filtra bien.
- Los envíos de correo de PQR y postulación sin `try/catch` (D-23): no se tocan hasta decidirlo, y se corrigen con su prueba en rojo.
- Cuatro `index.lock.huerfano*` y la carpeta `.git/huerfanos-cowork-2026-09-01/` siguen en `.git/` (comprobado en esta sesión; no estorban: aquí se confirmó sin problema): **los borra Sua a mano**. `hs_err_pid48556.log` en la raíz y los `~$*.docx` de `docs/ingenieria/` están ignorados por git; basura de IntelliJ y de Word, borrar cuando se quiera.

## 5. Cifras medidas del árbol

Sobre `54ddcbb`, 1 de septiembre de 2026, segunda sesión. Cada cifra con el comando que la produjo; **vuelve a medirlas antes de citarlas** en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | 276 (272 de Sua, 4 de Ingrid; la última de Ingrid el 25 ago) | `git rev-list --count HEAD` · `git shortlog -sn HEAD` |
| Migraciones | 39 (todas verificadas contra PostgreSQL 17.11) | `ls database/migrations \| wc -l` |
| Modelos | 21 | `ls app/Models/*.php \| wc -l` |
| Sembradores | 21 (+ `Support/`) | `ls database/seeders/*.php \| wc -l` |
| Archivos de prueba | 79 | `find tests -name '*Test.php' \| wc -l` |
| Vistas Blade | 66 | `find resources/views -name '*.blade.php' \| wc -l` |
| Panel | 19 recursos · 6 páginas · 20 policies | `ls app/Filament/Resources app/Filament/Pages app/Policies` |
| Comandos de Artisan propios | 5 (`asobares:crear-usuario`, `asociados:importar`, `bolsas:depurar`, `mensajes:depurar`, `inscripciones:depurar`) | `ls app/Console/Commands` |
| Enums | 16 | `ls app/Enums` |
| Rutas GET | 86 propias · 96 con las de vendor | `php artisan route:list --method=GET --except-vendor --json` · sin `--except-vendor` |
| **Suite** | **973 casos · 962 pasan · 11 omitidas · 0 fallos · 3.568 aserciones** (medida en esta sesión sobre `54ddcbb`; tres casos más que el §31.7: los de la guardia). La duración no se cita | `php artisan test --compact` |
| Producción (1 sep, mañana) | 23 aliados · 8 requisitos · 100 ajustes · 8 municipios · 6 categorías · 5 beneficios · 5 iniciativas · 3 roles · 80 permisos · 3 usuarios · **0** asociados, PQR, transacciones, noticias, eventos, vacantes y artistas (correcto) | consola de Cloud / `tinker` (§31.7); esta sesión solo comprobó el `200` |
| Portada publicada (1 sep, mañana) | 6 `<img>`, 0 `<video>`; destacados y eventos no se pintan (sin datos) | `curl` + conteo de etiquetas (§32.3) |

## 6. Lo siguiente, en orden

1. **Documento de práctica** (Sua, mar–jue): revisión CG del 31 ago comentario a comentario; 5.5, Tabla 5 y 6.2 con el despliegue en pie; cifras medidas ese día; enviar el jueves 3.
2. **Una sola reunión con Natalia** con la tabla del §3 impresa: D-01, D-04 a D-12 y D-20. El SMTP (D-07) se resuelve en la misma reunión por la opción A —verificación en dos pasos y contraseña de aplicación del buzón del gremio—; después variables en Cloud → despliegue → probar el segundo factor por correo y el acuse de una PQR de prueba.
3. **Franja visual, ya con insumo real** (Ingrid, en su worktree): bajar el video del Drive, elegir el medio del hero entre las 19 fotos y el video, medir contraste en los dos temas (`VeloDelHeroTest`), pies de foto, estados vacíos, iconos de beneficios, chips para aliados sin logo, dirección de arte escrita.
4. **Fijar la demo 2** (D-20) para el jueves 10 o viernes 11; guion de siete pantallas en este archivo; sitio despierto media hora antes.
5. **`git push origin main`** (Sua): esta sesión fusionó y no subió.
6. **Backend tras el SMTP**: bucket con política `publico/*` y prefijo privado (D-13) → **subir el formato de Bomberos y los demás PDF oficiales** desde el panel; disco privado para fotos pendientes; procedencia de semillas (D-14); `noindex` por variable (D-08); D-23; filtro de municipios del directorio; `lang/es`; medición de rendimiento contra la URL en caliente.
7. Semana 8: dominio y SSL, manual actualizado (crear usuario, moderar fotos, cargar aliados, publicar ficha con autorización, importar cartera, subir un formato oficial) y en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
