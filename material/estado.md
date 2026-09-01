# Estado vigente — Plataforma Web ASOBARES Capítulo Quindío

_La foto del proyecto hoy. **Se reescribe entero** al cerrar toda sesión que cambie algo: sin tachones, sin «superado», sin «esta línea decía». Lo que se cierra sale de aquí y queda contado en `bitacora.md`; lo que se decide sale de aquí y entra en «Decisiones que rigen» de `encargo.md`. Si el commit del encabezado está atrás de `main`, lee las entradas de bitácora posteriores a él y actualiza esto **antes** de tocar nada._

## 0. Medición

| | |
|---|---|
| Fecha | Martes 1 de septiembre de 2026, tarde (Bogotá) |
| `main` | `ed9bec2` · 271 confirmaciones · sincronizado con `origin` |
| Rama de trabajo de esta sesión | `division-prompt-maestro` (solo documentación: la división del prompt maestro) |
| Quién midió | Sesión de Cowork con Sua. **La suite no se ejecutó en esta sesión** (la máquina enlazada no expone PHP): la cifra de pruebas de §5 es la del 1 sep por la mañana (§31.7), no una re-medición |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud` responde **200**; portada de 76,9 kB servida en 0,80 s desde un servidor en EE. UU. (no es medición de RNF-02) |

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
| OBS3-02 | ⚠️ Ranura y velo del hero puestos, **vacía** | Medio para el hero: fotos (OBS3-07) o, mientras no lleguen, un fondo generativo sin fotografía |
| OBS3-09 | ⚠️ Mitad cerrada (acuse al postulante) | La otra mitad —que el afiliado consulte aspirantes— **prohibida** hasta releer `encargo.md` §9: datos de terceros |
| OBS3-10 | ⚠️ Código puesto | Las **7 URL de trámite** de Armenia. Insumo del gremio (D-04) |
| OBS3-11 | ⚠️ Código puesto (todo editable) | El **texto propio** de «Quiénes somos» y nombres/cargos confirmados (D-05) |
| OBS3-03 | ❌ Sin decidir | **Arranque del tema.** Decisión de Natalia, por escrito (D-02) |
| OBS3-07 | ❌ Bloqueado | **Autorización de imagen** de las 19 fotos (D-03) |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar** (D-01). Una fila sin marcar no aplaza: deja sin decidir |

De catorce, nueve cerrados y cinco vivos; **ninguno de los cinco se cierra escribiendo código**. Aparte, la franja de diseño que no depende de insumos y nadie ha hecho: dirección de arte escrita, hero con fondo generativo sobre la ranura existente, estados vacíos que anuncien en vez de esconder (destacados y eventos no se pintan hoy), iconos distintivos de los cinco beneficios, tratamiento de los aliados sin logo, especificación de fotos para el manual. Es de la Persona 2 y puede correr en su worktree esta semana.

### 2.2 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| Guía normativa | ⚠️ **1 municipio de 12** (Armenia: 7 trámites + lista de verificación Ley 1801/decreto 119, fechados 20 ago, sin costos) | Salento y Filandia perdieron su contenido inventado; el selector ofrece solo Armenia y la página no dice por qué. Los otros 11: orden y fuente (D-21). Formatos oficiales por entidad: sin empezar. Dueño del mantenimiento: sin nombrar |
| Portada | ✅ Todo texto editable; promesas ajustadas a lo que existe | Medios: 6 imágenes y ningún video en la portada publicada |
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
| Correo saliente (SMTP) | ❌ **Sin contratar.** Único bloqueo técnico con consecuencia visible: sin él no salen los códigos del segundo factor (el panel no se puede demostrar) ni el acuse al postulante (D-07) |
| Bucket | ❌ Sin crear. Política acotada a `publico/*` (runbook §8.3) **antes** de crearlo. Arrastra el disco público con fotos sin moderar (D-13) |
| Dominio propio | ❌ Semana 8; sin nombre (D-09) |
| Indexación | ⚠️ Hoy `robots.txt` responde `Allow: /` con sitemap de 14 URL, sin `X-Robots-Tag` ni `<meta name="robots">`: el sitio invita a indexar un directorio y una bolsa vacíos bajo el nombre del gremio. Decidir `noindex` hasta el lanzamiento (D-08) |
| Rendimiento contra la URL | ❌ Sin medir. Los 972 ms del expediente son contra `localhost`; medir en caliente (scale-to-zero) |
| Dispositivos reales (RNF-01, RNF-07) | ❌ Sin hacer (S7) |
| Límite de gasto de Cloud (~US$10) y `LOG_STACK=stderr` | ⚠️ Confirmar en la consola |
| Repositorio | ⚠️ `Jsua3/asobares`, público, en cuenta personal; sin segundo administrador (D-12) |

### 2.4 Datos personales

| Qué | Estado |
|---|---|
| Fichas de asociados | ✅ Nacen en borrador; cero publicadas en producción; R-02 no se materializó |
| Fotos del propietario pendientes o rechazadas | ⚠️ Viven en el disco público, servidas por URL no enumerable (ULID). Decidir: disco privado con controlador, o aceptar el riesgo por escrito (D-13) |
| 19 fotografías del gremio | ❌ Personas identificables, sin autorización de imagen documentada; no suben a ningún sitio (D-03) |
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

Cada decisión con su dueño y la fecha en que se pidió. Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md`. Consolida las DPV abiertas de la ERS v3 (5 ago), las doce confirmaciones del plan del material (26 ago), el bloque D del acta 3 (28 ago) y el §31.8 (1 sep). **Las D-01 a D-12 y D-20 caben en una sola reunión con Natalia con esta tabla impresa.**

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| D-01 | **Firma del Acta 04** (`docs/ingenieria/constancias/`): cada fila de OBS3-15 a 18 marcada antes del 22 / Fase II / se descarta, y las dos contrapropuestas del equipo (bitácora sin reversión genérica; sección de documentos del gremio en el portal en vez de módulo financiero) | Natalia + directivo | 30 ago | — |
| D-02 | **Tema inicial del sitio** (OBS3-03): el directivo prefiere oscuro; hoy arranca en `system`. Recomendación del equipo: oscuro. Toca `localStorage.theme`, compartida con el panel | Natalia | 28 ago | — |
| D-03 | **Autorizaciones de imagen** de las 19 fotos, o cuáles se pueden usar sin ellas (OBS3-07); pies de foto (evento, fecha, lugar, quiénes) | Natalia | 26 ago | — |
| D-04 | **Las 7 URL de trámite** de Armenia, o el contacto en la Alcaldía que las tenga (OBS3-10) | Natalia / Alcaldía | 28 ago | — |
| D-05 | **Texto propio de «Quiénes somos»**; nombre completo y orden de apellidos del presidente; cargo con el que firma Natalia; corrección de los datos del capítulo en el sitio de la Nacional (OBS3-11, OBS3-21, DPV-09, P-11) | Natalia + Nacional | 5 ago / 28 ago | — |
| D-06 | **Logos institucionales** en buena resolución (Asobares Colombia, Cámara, Comité Intergremial, Gobernación) y de los aliados comerciales (OBS3-04) | Natalia | 31 ago | — |
| D-07 | **SMTP con el correo del gremio** (Brevo, Resend o Postmark, plan gratuito): se crea en la reunión y quedan cargadas `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD` | Natalia + Sua | 15 ago / 30 ago | — |
| D-08 | **Indexación antes del lanzamiento**: `noindex` hasta el lanzamiento oficial (recomendado) o dejar indexar | Natalia + equipo | 30 ago | — |
| D-09 | **Dominio propio**: nombre, compra (cobro anual), titularidad y autorización de marca a la Nacional (DPV-08, OBS3-22) | Natalia | 5 ago / 28 ago | — |
| D-10 | **Pasarela**: confirmación escrita de «solo Bold» y cierre de la cuenta de BBVA (OBS3-23); medio PSE o QR y cuenta receptora (DPV-04); documentos para producción de Bold (RUT, cámara de comercio, cuenta bancaria) | Natalia + contadora | 28 ago | — |
| D-11 | **Cartera**: Excel con el formato real de la contadora, aunque sea con datos ficticios (OBS3-19); Drive vinculado o carga manual (OBS3-20); periodicidad (semanal por ahora) | Luisa + Natalia | 28 ago | — |
| D-12 | **Titularidad de la infraestructura**: a nombre de quién está la facturación de Cloud; Natalia como miembro de la organización; segundo administrador en GitHub; fecha de traspaso en el runbook §12 | Sua + Natalia | 30 ago | — |
| D-13 | **Fotos pendientes y rechazadas**: al disco privado servidas por controlador, o riesgo aceptado por escrito (§30.3) | Sua (técnica, se deja escrita) | 31 ago | — |
| D-14 | **Marca de procedencia en el contenido sembrado** (`origen` sembrador/oficina, o no sobrescribir filas editadas): sin ella, resembrar pisa lo que la oficina corrigió (§31.4) | Sua | 1 sep | — |
| D-15 | `GeneradorPdf`: borrar (cero consumidores) o conservar como molde para los formatos oficiales | Sua | 1 sep | — |
| D-16 | **Reparto Persona 1 / Persona 2**: reescribirlo como quedó de hecho o declarar su suspensión, antes del informe final y la Acta 02 (§30.3) | Sua + Ingrid | 31 ago | — |
| D-17 | **ERS v3 sin firma** desde el 5 ago y sus DPV abiertas: DPV-01 fecha oficial de lanzamiento; **DPV-02 qué se ve con sesión y qué sin ella** (condiciona 8 RF ya codificados); DPV-03 bolsa de empleo en V1 (ratificación); DPV-05 RF-05; DPV-10 alcance de cartera; DPV-11 tarifa de proveedores; **DPV-13 soporte después del 22 de septiembre** | Natalia + directivo | 5 ago | — |
| D-18 | **Las confirmaciones del plan del material (26 ago)** que siguen sin respuesta: base vigente y las 7 filas sin municipio; cifra de afiliados; relación con el capítulo Eje Cafetero; aliados que aplican al Quindío y convenios locales; programas nacionales que operan aquí; cuota y cuenta bancaria del formulario; qué norma es el «decreto 119»; revisión jurídica del boletín laboral; certificado desde el portal (ampliación); formulario oficial descargable o en línea | Natalia | 26 ago | — |
| D-19 | **Política de tratamiento de datos**: texto legal (P-15), encargados, canal de supresión, revisión legal (G12) | Natalia / aliado jurídico | 5 ago | — |
| D-20 | **Fecha de la segunda demostración** (entre el 4 y el 11; pedir jue 10 o vie 11) | Directivo + Natalia | 28 ago | — |
| D-21 | **Municipios 2 a 12 de la guía**: orden (los seis con afiliados primero), fuente por alcaldía, y qué se declara Fase II | Natalia | 1 sep | — |

## 4. Deuda diferida a propósito

No se «arregla de paso»:

- Los chips de filtro repetidos y los 104 `leading-*`/`tracking-*` sueltos viven en las vistas que la franja visual va a rehacer: **después del 11 de septiembre**.
- `@alpinejs/collapse` importado sin consumidor (retirarlo toca `package.json`, que exige aprobación).
- El consecutivo de PQR bajo concurrencia en PostgreSQL falla cerrado (error, no duplicado); arreglarlo toca el expediente de PQR: decisión del dueño.
- No existe `lang/`: las reglas sin mensaje propio salen en inglés; los siete formularios públicos están cubiertos por sus `messages()`. Pendiente `php artisan lang:publish` + `lang/es/validation.php`.
- Salento y Filandia sin guía tras retirar lo inventado: regresión visible aceptada; se resuelve con D-21 y con decirlo en la página.
- El filtro de municipios del **directorio** lista todos los de la tabla tengan o no fichas publicadas (causa raíz del §30.7); la guía ya filtra bien.
- Cuatro `index.lock` huérfanos en `.git/` y la carpeta `.git/huerfanos-cowork-2026-09-01/` (locks y objetos temporales que las sesiones remotas no pueden borrar): **los borra Sua a mano**. `hs_err_pid48556.log` en la raíz y los `~$*.docx` de `docs/ingenieria/` están ignorados por git; basura de IntelliJ y de Word, borrar cuando se quiera.
- Prueba de guardia del propio estado (que el commit del encabezado exista y que el prompt maestro no lleve cifras de la suite): **propuesta, no escrita**, porque esta sesión no podía ejecutar la suite y una prueba que no se vio en rojo no cuenta.

## 5. Cifras medidas del árbol

Sobre `main` en `ed9bec2`, 1 de septiembre de 2026. Cada cifra con el comando que la produjo; **vuelve a medirlas antes de citarlas** en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | 271 (267 de Sua, 4 de Ingrid; la última de Ingrid el 25 ago) | `git rev-list --count HEAD` · `git shortlog -sn --all` |
| Migraciones | 39 (todas verificadas contra PostgreSQL 17.11) | `ls database/migrations \| wc -l` |
| Modelos | 21 | `ls app/Models/*.php \| wc -l` |
| Sembradores | 21 (+ `Support/`) | `ls database/seeders/*.php \| wc -l` |
| Archivos de prueba | 78 | `find tests -name '*Test.php' \| wc -l` |
| Vistas Blade | 66 | `find resources/views -name '*.blade.php' \| wc -l` |
| Panel | 19 recursos · 6 páginas · 20 policies | `ls app/Filament/Resources app/Filament/Pages app/Policies` |
| Comandos de Artisan propios | 5 (`asobares:crear-usuario`, `asociados:importar`, `bolsas:depurar`, `mensajes:depurar`, `inscripciones:depurar`) | `ls app/Console/Commands` |
| Enums | 16 | `ls app/Enums` |
| Rutas GET | 86 (§31.7; no re-medido hoy) | `php artisan route:list --method=GET` |
| **Suite** | **970 casos · 959 pasan · 11 omitidas · 0 fallos · 3.563 aserciones** (medida el 1 sep por la mañana sobre `493790d`, §31.7; **no re-medida en esta sesión**). La duración no se cita | `php artisan test --compact` |
| Producción (1 sep) | 23 aliados · 8 requisitos · 100 ajustes · 8 municipios · 6 categorías · 5 beneficios · 5 iniciativas · 3 roles · 80 permisos · 3 usuarios · **0** asociados, PQR, transacciones, noticias, eventos, vacantes y artistas (correcto) | consola de Cloud / `tinker` (§31.7); portada re-leída hoy: 23 aliados y 5 iniciativas confirmados |
| Portada publicada | 6 `<img>`, 0 `<video>`; destacados y eventos no se pintan (sin datos) | `curl` + conteo de etiquetas |

## 6. Lo siguiente, en orden

1. **Documento de práctica** (Sua, mar–jue): revisión CG del 31 ago comentario a comentario; 5.5, Tabla 5 y 6.2 con el despliegue en pie; cifras medidas ese día; enviar el jueves 3.
2. **Una sola reunión con Natalia** con la tabla del §3 impresa: D-01 a D-12 y D-20. El SMTP (D-07) se crea en la misma reunión; después variables → despliegue → probar segundo factor y acuse.
3. **Franja visual sin insumos** (Ingrid, en su worktree): dirección de arte escrita, hero con fondo generativo, estados vacíos, iconos de beneficios, chips para aliados sin logo, especificación de fotos.
4. **Fijar la demo 2** (D-20) para el jueves 10 o viernes 11; guion de siete pantallas en este archivo; sitio despierto media hora antes.
5. **Backend tras el SMTP**: bucket con política `publico/*` y disco privado para fotos (D-13); procedencia de semillas (D-14); `noindex` por variable (D-08); filtro de municipios del directorio; `lang/es`; medición de rendimiento contra la URL en caliente.
6. **Revisar y fusionar la rama `division-prompt-maestro`** (Sua), y escribir la prueba de guardia del estado viéndola en rojo primero.
7. Semana 8: dominio y SSL, manual actualizado (crear usuario, moderar fotos, cargar aliados, publicar ficha con autorización, importar cartera) y en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
