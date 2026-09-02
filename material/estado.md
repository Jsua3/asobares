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
| Fecha | Martes 1 de septiembre de 2026, quinta sesión del día (Bogotá) |
| `main` | `18bdeea` · 282 confirmaciones · este archivo entra en el commit siguiente por avance rápido y **`main` se sube a `origin` al cerrar la sesión** |
| Rama de trabajo | Ninguna: la sesión cerró sobre `main` |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5.9). **La suite sí se ejecutó** (§5) |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud` responde **200** (petición `HEAD`, segunda sesión de hoy); contenido releído por última vez el 1 sep por la mañana (§31.7). **Nada de hoy está desplegado todavía**: con el `push` de esta sesión `origin` queda al día, y el siguiente despliegue de Cloud lleva D-23 y D-24 |

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
| OBS3-02 | ⚠️ Ranura y velo del hero puestos, **vacía** | Ya hay medio autorizado y en local (OBS3-07): elegir entre las 19 fotos y el video; si es el video, recortarlo y comprimirlo primero (D-22); medir contraste en los dos temas |
| OBS3-07 | ⚠️ **Insumo en mano y en local** desde el 1 sep: 19 fotos y el video (48 s, 55,7 MB) en `material/nuevomaterial/` | Pies de foto (D-03); recortar y comprimir el video (D-22); ponerlos en el sitio (franja visual) |
| OBS3-09 | ⚠️ Mitad cerrada (acuse al postulante) | La otra mitad —que el afiliado consulte aspirantes— **prohibida** hasta releer `encargo.md` §9: datos de terceros |
| OBS3-10 | ⚠️ Código puesto | Las **7 URL de trámite** de Armenia. Insumo del gremio (D-04) |
| OBS3-11 | ⚠️ Código puesto (todo editable) | El **texto propio** de «Quiénes somos» y nombres/cargos confirmados (D-05) |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar** (D-01). Una fila sin marcar no aplaza: deja sin decidir |

De catorce, diez cerrados y cuatro vivos (07, 09, 10, 11); **ninguno de los cuatro se cierra escribiendo código**, y el 07 ya tiene el insumo en local: es trabajo de la franja visual. La franja de diseño que nadie ha hecho: dirección de arte escrita, hero con medio real (el fondo generativo queda como respaldo), estados vacíos que anuncien en vez de esconder (destacados y eventos no se pintan hoy), iconos distintivos de los cinco beneficios, tratamiento de los aliados sin logo, especificación de fotos para el manual. Es de la Persona 2 y puede correr en su worktree esta semana.

### 2.2 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| Guía normativa | ⚠️ **1 municipio de 12** (Armenia: 7 trámites + lista de verificación Ley 1801/decreto 119, fechados 20 ago, sin costos) | Salento y Filandia perdieron su contenido inventado; el selector ofrece solo Armenia y la página no dice por qué. Los otros 11: orden y fuente (D-21). **Formatos oficiales por entidad: habilitados el 1 sep, pero no han llegado** (ningún PDF de Bomberos ni de otra entidad en el Drive, §35.3). ⚠️ Los dos PDF de `storage/app/private/formatos/` (`formato-solicitud-visita-bomberos.pdf`, `formato-registro-policia.pdf`, 2–3 KB, 3 ago) son los **formatos de ejemplo del prototipo** que generó `GeneradorPdf`, no los oficiales: dicen «Documento de ejemplo generado para el prototipo» y no van a producción (regla 5). Pedir los reales a la Alcaldía o al gremio. Cuando lleguen, se suben desde el panel al requisito («Formato oficial (PDF)», disco privado, hasta 5 MB), **después del bucket** (D-13), porque en Cloud lo subido desde el panel se pierde al redesplegar. Dueño del mantenimiento: sin nombrar |
| Portada | ✅ Todo texto editable; promesas ajustadas a lo que existe | Medios: 6 imágenes y ningún video en la portada publicada; desde el 1 sep hay 19 fotos y el video autorizados y en local (OBS3-07). La franja «La noche en cifras» son cuatro ajustes editables con el estudio del Observatorio de la Nacional; que muestre cifras del gremio quincenales es D-25 |
| Aliados | ✅ 23 sembrados del catálogo oficial (19 comerciales con condición real privada + 4 institucionales) | Logos en buena resolución (D-06); cuáles aplican al Quindío y convenios locales (D-18) |
| Beneficios e iniciativas | ✅ 5 y 5, de documento oficial (`BENEFICIOS AFILIADOS PDF.pdf` está en `nuevomaterial/`) | — |
| «Quiénes somos» | ⚠️ Editable entero; texto provisional | Texto propio, nombres y cargos, relación con el Eje Cafetero y la Nacional (D-05, D-18) |
| Directorio | ⚠️ **0 fichas publicadas** en producción (correcto sin autorizaciones). En local, 41 en borrador (23 ago). **Las dos versiones de la base (48 filas del 26 ago y 41 del 23 ago) están en `nuevomaterial/` desde el 1 sep** | Importar la de 48 con `asociados:importar` desde **fuera del árbol** y depurar (nombres repetidos, NIT normalizado, categorías reales); autorización de cada titular (propuesta: sección VIII del formulario oficial) |
| Lista de verificación de funcionamiento (Ley 1801) | ✅ Sembrada como ficha de Armenia | Confirmar qué norma es el «decreto 119» (D-18) |
| Boletín laboral (Ley 2466 de 2025) | ❌ Sin publicar (`Ley laboral.pdf`, 5,4 MB, está en `nuevomaterial/`) | Redactarlo como calendario vigente a 2026; revisión del aliado jurídico (D-18); categoría `normativa` en el enum (Persona 1) |
| Formulario oficial de registro | ❌ No está en `/afiliate`. `Registro Establecimiento.xlsx` es ese formulario maquetado en Excel (§35.3), no una tabla | Decidir descargable (opción A, dentro del alcance: se exporta a PDF y se sube) o en línea por pasos (B, Fase II) (D-18) |
| Certificado de afiliación | ❌ Molde en `Certificado de afiliacion SALSABOR.docx` | Es ampliación (D-18): no se codifica sin constancia |
| Cifra pública de afiliados | ⚠️ El sitio dice 60; la base 48; el directivo 60 | La fija Natalia (D-18) |

### 2.3 Infraestructura

| Qué | Estado |
|---|---|
| Sitio | ✅ **200** sobre PostgreSQL 17.11 (`bold-leaf-62673759`, esquema `production`), 39 migraciones aplicadas, base con contenido oficial (1 sep) |
| Cuenta de Laravel Cloud | ✅ Existe, con medio de pago del gremio (30 ago). ⚠️ La organización se llama `juan-sua`: **comprobar a nombre de quién está la facturación**, añadir a Natalia como miembro y anotar el traspaso en el runbook §12 (D-12) |
| Correo saliente (SMTP) | ❌ **Sin contratar: ver el bloque «Arreglo pendiente» de arriba.** Con el transporte caído ya no se rompe nada: formularios públicos (D-23, `707e21e`) ni acciones del panel (D-24, `07a3033`), `CorreoSalienteCaidoTest` |
| Bucket | ❌ Sin crear. Política acotada a `publico/*` (runbook §8.3) **antes** de crearlo. Arrastra el disco público con fotos sin moderar (D-13), y desde el 1 sep condiciona también los **formatos oficiales** de la guía y el **video del hero** si se sirve desde el sitio (D-22) |
| Dominio propio | ❌ Semana 8; sin nombre (D-09) |
| Indexación | ⚠️ Hoy `robots.txt` responde `Allow: /` con sitemap de 14 URL, sin `X-Robots-Tag` ni `<meta name="robots">`: el sitio invita a indexar un directorio y una bolsa vacíos bajo el nombre del gremio. Decidir `noindex` hasta el lanzamiento (D-08) |
| Rendimiento contra la URL | ❌ Sin medir. Los 972 ms del expediente son contra `localhost`; medir en caliente (scale-to-zero) |
| Dispositivos reales (RNF-01, RNF-07) | ❌ Sin hacer (S7) |
| Límite de gasto de Cloud (~US$10) y `LOG_STACK=stderr` | ⚠️ Confirmar en la consola |
| Repositorio | ⚠️ `Jsua3/asobares`, público, en cuenta personal; sin segundo administrador (D-12). `origin/main` al día al cerrar esta sesión |

### 2.4 Datos personales

| Qué | Estado |
|---|---|
| Fichas de asociados | ✅ Nacen en borrador; cero publicadas en producción; R-02 no se materializó |
| Fotos del propietario pendientes o rechazadas | ⚠️ Viven en el disco público, servidas por URL no enumerable (ULID). Decidir: disco privado con controlador, o aceptar el riesgo por escrito (D-13) |
| 19 fotografías del gremio y el video | ✅ **Uso autorizado por el gremio el 1 sep** (`encargo.md` §13). Los dos en `material/nuevomaterial/`, que no se versiona. Pies de foto pendientes (D-03). La regla del §9 sigue en pie para cualquier foto nueva |
| Base de establecimientos del gremio (`.xlsx`) | ⚠️ **Dos copias dentro del árbol** desde el 1 sep, 11:00 p. m. (`Asobares Quindio - Base de datos.xlsx`, 48 filas, y `Base de datos Cap. Quindio.xlsx`, 41): ignoradas por git y sin rastrear, pero `DatosInternosDelAsociadoTest` las detecta por nombre y **la suite está en rojo hasta que salgan del árbol**. La guardia mira el disco a propósito: la base se guarda fuera del repositorio y se carga con `asociados:importar` |
| Política de tratamiento de datos | ❌ Texto legal definitivo (P-15), encargados que la política no nombra, canal de supresión y revisión legal (G12) (D-19) |
| `material/nuevomaterial/` | ✅ En `.gitignore`; no se versiona. Desde el 1 sep contiene la copia entera del Drive del gremio (§35.3) |
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

Cada decisión con su dueño y la fecha en que se pidió. Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md` (el 1 sep salieron D-02, la autorización de D-03, D-23 y D-24). Consolida las DPV abiertas de la ERS v3 (5 ago), las doce confirmaciones del plan del material (26 ago), el bloque D del acta 3 (28 ago), el §31.8, el §33.3, el §34.3 y el §35.4. **Las D-01, D-04 a D-12 y D-20 caben en una sola reunión con Natalia con esta tabla impresa.**

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| D-01 | **Firma del Acta 04** (`docs/ingenieria/constancias/`): cada fila de OBS3-15 a 18 marcada antes del 22 / Fase II / se descarta, y las dos contrapropuestas del equipo (bitácora sin reversión genérica; sección de documentos del gremio en el portal en vez de módulo financiero) | Natalia + directivo | 30 ago | — |
| D-03 | **Pies de foto** de las 19 fotografías y del video (evento, fecha, lugar, quiénes). La autorización de uso ya está (1 sep, `encargo.md` §13) y el video ya está en local | Natalia | 26 ago | Autorización: 1 sep ✅ · video en local: 1 sep ✅ · pies: — |
| D-04 | **Las 7 URL de trámite** de Armenia, o el contacto en la Alcaldía que las tenga (OBS3-10) | Natalia / Alcaldía | 28 ago | — |
| D-05 | **Texto propio de «Quiénes somos»**; nombre completo y orden de apellidos del presidente; cargo con el que firma Natalia; corrección de los datos del capítulo en el sitio de la Nacional (OBS3-11, OBS3-21, DPV-09, P-11) | Natalia + Nacional | 5 ago / 28 ago | — |
| D-06 | **Logos institucionales** en buena resolución (Asobares Colombia, Cámara, Comité Intergremial, Gobernación) y de los aliados comerciales (OBS3-04) | Natalia | 31 ago | — |
| D-07 | **SMTP con el correo del gremio.** Los pasos exactos están en el bloque «Arreglo pendiente» de arriba: opción A, contraseña de aplicación de Google del buzón del gremio (no toca DNS); B, credenciales de Brevo o Mailgun de la Nacional; C, la Nacional añade los registros de Resend. Se hace con la cuenta de Google del gremio delante; luego variables → despliegue → probar segundo factor, acuse de PQR, correos de postulación y aviso de vacante aprobada | Natalia + Sua (A) · Nacional (B, C) | 15 ago / 30 ago / 1 sep | — |
| D-08 | **Indexación antes del lanzamiento**: `noindex` hasta el lanzamiento oficial (recomendado) o dejar indexar | Natalia + equipo | 30 ago | — |
| D-09 | **Dominio propio**: nombre, compra (cobro anual), titularidad y autorización de marca a la Nacional (DPV-08, OBS3-22) | Natalia | 5 ago / 28 ago | — |
| D-10 | **Pasarela**: confirmación escrita de «solo Bold» y cierre de la cuenta de BBVA (OBS3-23); medio PSE o QR y cuenta receptora (DPV-04); documentos para producción de Bold (RUT, cámara de comercio, cuenta bancaria) | Natalia + contadora | 28 ago | — |
| D-11 | **Cartera**: Excel con el formato real de la contadora, aunque sea con datos ficticios (OBS3-19) —**no vino con el Drive** (§35.3); hoy el importador lee CSV con `establecimiento`, `saldo_pendiente`, `meses_mora` y `ultimo_pago`—; Drive vinculado o carga manual (OBS3-20); periodicidad (semanal por ahora) | Luisa + Natalia | 28 ago | — |
| D-12 | **Titularidad de la infraestructura**: a nombre de quién está la facturación de Cloud; Natalia como miembro de la organización; segundo administrador en GitHub; fecha de traspaso en el runbook §12 | Sua + Natalia | 30 ago | — |
| D-13 | **Bucket y fotos pendientes o rechazadas**: al disco privado servidas por controlador, o riesgo aceptado por escrito (§30.3). Desde el 1 sep el bucket condiciona también los formatos oficiales de la guía y el video del hero | Sua (técnica, se deja escrita) | 31 ago | — |
| D-14 | **Marca de procedencia en el contenido sembrado** (`origen` sembrador/oficina, o no sobrescribir filas editadas): sin ella, resembrar pisa lo que la oficina corrigió (§31.4) | Sua | 1 sep | — |
| D-15 | `GeneradorPdf`: borrar (cero consumidores) o conservar como molde para los formatos oficiales | Sua | 1 sep | — |
| D-16 | **Reparto Persona 1 / Persona 2**: reescribirlo como quedó de hecho o declarar su suspensión, antes del informe final y la Acta 02 (§30.3) | Sua + Ingrid | 31 ago | — |
| D-17 | **ERS v3 sin firma** desde el 5 ago y sus DPV abiertas: DPV-01 fecha oficial de lanzamiento; **DPV-02 qué se ve con sesión y qué sin ella** (condiciona 8 RF ya codificados); DPV-03 bolsa de empleo en V1 (ratificación); DPV-05 RF-05; DPV-10 alcance de cartera; DPV-11 tarifa de proveedores; **DPV-13 soporte después del 22 de septiembre** | Natalia + directivo | 5 ago | — |
| D-18 | **Las confirmaciones del plan del material (26 ago)** que siguen sin respuesta: base vigente y las 7 filas sin municipio; cifra de afiliados; relación con el capítulo Eje Cafetero; aliados que aplican al Quindío y convenios locales; programas nacionales que operan aquí; cuota y cuenta bancaria del formulario; qué norma es el «decreto 119»; revisión jurídica del boletín laboral; certificado desde el portal (ampliación; ya hay molde); formulario oficial descargable (ya hay `Registro Establecimiento.xlsx`) o en línea | Natalia | 26 ago | — |
| D-19 | **Política de tratamiento de datos**: texto legal (P-15), encargados, canal de supresión, revisión legal (G12) | Natalia / aliado jurídico | 5 ago | — |
| D-20 | **Fecha de la segunda demostración** (entre el 4 y el 11; pedir jue 10 o vie 11) | Directivo + Natalia | 28 ago | — |
| D-21 | **Municipios 2 a 12 de la guía**: orden (los seis con afiliados primero), fuente por alcaldía, y qué se declara Fase II | Natalia | 1 sep | — |
| D-22 | **El video del hero**: el original dura 48 s y pesa 55,7 MB. Recortarlo a un bucle de 10–15 s sin audio, comprimirlo a 2–5 MB (720p/1080p), póster para el primer cuadro y `prefers-reduced-motion`; servirlo desde el bucket público (D-13); contraste bajo el velo en los dos temas. Sin `ffmpeg` en la máquina de Sua | Sua (técnica) + Ingrid (visual) | 1 sep | — |
| D-25 | **Cifras del gremio en la portada desde un archivo quincenal de la contadora** (§35.4). Hoy la franja «La noche en cifras» son cuatro ajustes editables con el estudio del Observatorio de la Nacional, y el único archivo de la contadora que el sistema lee es el CSV de cartera. Es funcionalidad nueva y el alcance está congelado: **se escribe la constancia antes de codificar**. **Decidido el 1 sep (opción a): franja nueva del gremio junto a la del Observatorio, que se queda.** Falta el cómo —ajustes editables desde el panel, importar un archivo pequeño cada 15 días, o derivarlas de la cartera— y las cifras exactas; con eso se escribe la constancia (§36.2) | Natalia + Sua | 1 sep | Qué: a) ✅ 1 sep · cómo: — |

## 4. Deuda diferida a propósito

No se «arregla de paso»:

- Los chips de filtro repetidos y los 104 `leading-*`/`tracking-*` sueltos viven en las vistas que la franja visual va a rehacer: **después del 11 de septiembre**.
- `@alpinejs/collapse` importado sin consumidor (retirarlo toca `package.json`, que exige aprobación).
- El consecutivo de PQR bajo concurrencia en PostgreSQL falla cerrado (error, no duplicado); arreglarlo toca el expediente de PQR: decisión del dueño.
- No existe `lang/`: las reglas sin mensaje propio salen en inglés; los siete formularios públicos están cubiertos por sus `messages()`. Pendiente `php artisan lang:publish` + `lang/es/validation.php`.
- Salento y Filandia sin guía tras retirar lo inventado: regresión visible aceptada; se resuelve con D-21 y con decirlo en la página.
- El filtro de municipios del **directorio** lista todos los de la tabla tengan o no fichas publicadas (causa raíz del §30.7); la guía ya filtra bien.
- Cuatro `index.lock.huerfano*` y la carpeta `.git/huerfanos-cowork-2026-09-01/` siguen en `.git/` (no estorban: hoy se confirmó cinco veces sin problema): **los borra Sua a mano**. `hs_err_pid48556.log` en la raíz y los `~$*.docx` de `docs/ingenieria/` están ignorados por git; basura de IntelliJ y de Word, borrar cuando se quiera.

## 5. Cifras medidas del árbol

Sobre `18bdeea`, 1 de septiembre de 2026, quinta sesión (desde `07a3033` solo hubo documentación: el árbol de código es el mismo). Cada cifra con el comando que la produjo; **vuelve a medirlas antes de citarlas** en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | 282 (278 de Sua, 4 de Ingrid; la última de Ingrid el 25 ago) | `git rev-list --count HEAD` · `git shortlog -sn HEAD` |
| Migraciones | 39 (todas verificadas contra PostgreSQL 17.11) | `ls database/migrations \| wc -l` |
| Modelos | 21 | `ls app/Models/*.php \| wc -l` |
| Sembradores | 21 (+ `Support/`) | `ls database/seeders/*.php \| wc -l` |
| Archivos de prueba | 80 | `find tests -name '*Test.php' \| wc -l` |
| Vistas Blade | 66 | `find resources/views -name '*.blade.php' \| wc -l` |
| Panel | 19 recursos · 6 páginas · 20 policies | `ls app/Filament/Resources app/Filament/Pages app/Policies` |
| Comandos de Artisan propios | 5 (`asobares:crear-usuario`, `asociados:importar`, `bolsas:depurar`, `mensajes:depurar`, `inscripciones:depurar`) | `ls app/Console/Commands` |
| Enums | 16 | `ls app/Enums` |
| Rutas GET | 86 propias · 96 con las de vendor | `php artisan route:list --method=GET --except-vendor --json` · sin `--except-vendor` |
| **Suite** | **980 casos · 968 pasan · 11 omitidas · 1 fallo · 3.619 aserciones** (medida en esta sesión sobre `07a3033`; cuatro casos más que el §34: la sección del panel de `CorreoSalienteCaidoTest`). **El fallo es ajeno al código**: `DatosInternosDelAsociadoTest::test_la_base_de_datos_del_gremio_no_vive_en_el_repositorio` detecta los dos `.xlsx` de la base del gremio en `material/nuevomaterial/` (§2.4). Vuelve a verde al sacarlos del árbol. La duración no se cita | `php artisan test --compact` |
| Producción (1 sep, mañana) | 23 aliados · 8 requisitos · 100 ajustes · 8 municipios · 6 categorías · 5 beneficios · 5 iniciativas · 3 roles · 80 permisos · 3 usuarios · **0** asociados, PQR, transacciones, noticias, eventos, vacantes y artistas (correcto) | consola de Cloud / `tinker` (§31.7); hoy solo se comprobó el `200` |
| Portada publicada (1 sep, mañana) | 6 `<img>`, 0 `<video>`; destacados y eventos no se pintan (sin datos) | `curl` + conteo de etiquetas (§32.3) |

## 6. Lo siguiente, en orden

1. **Documento de práctica** (Sua, mar–jue): revisión CG del 31 ago comentario a comentario; 5.5, Tabla 5 y 6.2 con el despliegue en pie; cifras medidas ese día; enviar el jueves 3.
2. **Sacar del árbol los dos `.xlsx` de la base del gremio** (Sua, un minuto): a una carpeta fuera de `D:\Sua_Files\IdeaProjects\Asobares3`. La suite vuelve a verde y `asociados:importar` los lee desde donde estén.
3. **Desplegar** (Sua): con el `push` de esta sesión `origin` está al día; comprobar en la consola de Cloud que el despliegue arrancó (o lanzarlo) y, en la URL pública, que una PQR de prueba devuelve el aviso «No pudimos enviarte el acuse» en vez de la página de error.
4. **D-25 por escrito antes de tocar código**: elegido el qué (franja nueva del gremio); falta elegir el cómo entre las tres vías del §36.2 de la bitácora, fijar las cifras y dejar la constancia en `docs/ingenieria/constancias/` (Acta 04 sigue sin firmar: se le añade la fila, o se emite Acta 05).
5. **Una sola reunión con Natalia** con la tabla del §3 impresa: D-01, D-04 a D-12 y D-20. Si en la reunión está la cuenta de Google del gremio, se hace ahí mismo el bloque «Arreglo pendiente» de arriba (D-07).
6. **Franja visual, ya con insumo real** (Ingrid, en su worktree): recortar y comprimir el video (D-22), elegir el medio del hero entre las 19 fotos y el video, medir contraste en los dos temas (`VeloDelHeroTest`), pies de foto, estados vacíos, iconos de beneficios, chips para aliados sin logo, dirección de arte escrita.
7. **Fijar la demo 2** (D-20) para el jueves 10 o viernes 11; guion de siete pantallas en este archivo; sitio despierto media hora antes.
8. **Backend tras el SMTP**: bucket con política `publico/*` y prefijo privado (D-13) → subir los formatos oficiales cuando lleguen; disco privado para fotos pendientes; procedencia de semillas (D-14); `noindex` por variable (D-08); filtro de municipios del directorio; `lang/es`; medición de rendimiento contra la URL en caliente; importar la base de 48 filas desde fuera del árbol y depurarla.
9. Semana 8: dominio y SSL, manual actualizado (crear usuario, moderar fotos, cargar aliados, publicar ficha con autorización, importar cartera, subir un formato oficial) y en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
