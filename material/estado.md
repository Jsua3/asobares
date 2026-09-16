# Estado vigente — Plataforma Web ASOBARES Capítulo Quindío

_La foto del proyecto hoy. **Se reescribe entero** al cerrar toda sesión que cambie algo: sin tachones, sin «superado», sin «esta línea decía». Lo que se cierra sale de aquí y queda contado en `bitacora.md`; lo que se decide sale de aquí y entra en «Decisiones que rigen» de `encargo.md`. Si el commit del encabezado está atrás de `main`, lee las entradas de bitácora posteriores a él y actualiza esto **antes** de tocar nada. `tests/Feature/GuardiaDelEstadoTest.php` comprueba que el commit del encabezado exista de verdad._

---

## ✅ TODO ESTÁ EN `main` Y EN PRODUCCIÓN (16 sep, 23:23 UTC)

> **Una sola rama viva.** Entraron a `main` el trabajo de Ingrid de `a6ebcca` (por `cherry-pick`, con su autoría y **sin la firma de Cursor**), la unificación visual y nueve arreglos de esta sesión. Push `a57f044..6197c92` comprobado con `git ls-remote`; despliegue terminado **84 s después**. Antes, a las 22:08, se había adelantado solo el arreglo de `/empleo`, que estaba roto en producción.
>
> **Comprobado sobre la URL pública, por contenido servido y no por el repositorio:**
>
> - `/empleo`: el formulario de perfil ofrece las **7 áreas** (antes, una sola opción deshabilitada: nadie podía dejar su perfil).
> - `/eventos` y el calendario sirven la entradilla nueva: la migración corrió en el despliegue y limpió la caché de ajustes.
> - Las cabeceras se sirven en **WebP** (145 KB la de eventos, 126 KB la de la guía, 163 KB la del directorio) y el PNG viejo da **404**.
> - `/abre-tu-negocio` sirve la escena fundida y el marco del logotipo; `/contacto` sirve el formulario con radio; la hoja principal lleva el velo de fila translúcido con su máscara.
> - **Quince rutas en 200** entre 0,79 y 2,18 s, incluidos `/admin/login`, `/mi-cuenta/entrar` y `/sitemap.xml`; `/eventos/calendario` en 302 hacia el mes en curso, como debe.
>
> **Dos cosas que decidir mirando la URL, no leyendo código:** la barra en scroll (D-50) y que los eventos de aliados ya están publicables sin acta (D-47). Y una para Ingrid: **empezar lo siguiente desde `main`** (D-48).

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
| Fecha | **Martes 16 de septiembre de 2026** (Bogotá). Push 23:21:59 UTC, despliegue terminado 23:23:23, comprobación contra la URL pública inmediatamente después |
| **Dónde vive este archivo** | En `main`, medido sobre `6197c92`, que es lo que sirve producción |
| `main` | `6197c92` · ✅ **Al día con `origin/main` y desplegado.** 11 commits sobre `a57f044`: el de Ingrid, la unificación y nueve de esta sesión. ⚠️ El hash va **primero y entre acentos graves**: `GuardiaDelEstadoTest` lee esta fila, y adornarla por delante la deja sin nada que comprobar |
| Ramas | ✅ **Nada sin fusionar de nuestro lado**: `fix/banco-de-talento`, `visual/lenguaje-unico` e `integracion/lenguaje-unico` están contenidas en `main`. ⚠️ `origin/cierre/visual03-directorio-login` sigue en `a6ebcca`, con su contenido ya integrado bajo otro hash (D-48) |
| Suite sobre lo desplegado | **1.738 casos · 1.738 pasan · 0 fallos · 12.319 aserciones · 616 s**, con los activos compilados y sin `npm run dev`. Antes de empezar el día: 1.617 casos y 8.541 aserciones |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5), sobre el worktree `.claude/worktrees/ingrid02`; capturas y auditoría visual con Playwright (Chromium) a 1440, 1024, 390, 360 y 320 px, en claro y oscuro |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud` · ✅ **sirve `6197c92`**. Tres migraciones nuevas corrieron en el despliegue: las dos de origen de eventos de Ingrid y la de la entradilla |
| **Expediente** | Al día: esta foto, la **§57 de `bitacora.md`**, tres decisiones nuevas en `encargo.md` §13 y el manual de usuario §3 (eventos de aliados). ⚠️ La **matriz de trazabilidad** sigue con las cifras del 8 sep. ⚠️ El **Acta 09** (D-47) sigue sin emitir y ahora cubre también los eventos de aliados |

## 1. Qué se exige y cuándo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **22 sep — quedan 6 días** | **Entrega dura al gremio** | Cronograma firmado |
| **Vencido: vie 11 sep, 11:59:59 pm** | ⚠️ **Confirmar que se envió.** El expediente no registra envíos y los archivos siguen **sin versionar** en `docs/ingenieria/entrega-2026-09-04/`. Presentación de la socialización final, 10 diapositivas sobre la plantilla oficial del gremio | Docente asesor (correo del 7 sep) |
| **Vencido: vie 4 sep, 11:59:59 pm** | Documento de práctica corregido según la revisión CG del 31 ago. **El repositorio no registra si se envió**: confirmarlo. Sigue describiendo los RF de proveedores como públicos, sin el banco de talento, y **describe la barra A** | Docente asesor. Tarde = 0.0 |
| **Vencida sin fijar: era para el vie 11 sep** | ⚠️ **Segunda demostración** sobre la URL pública, en el teléfono del directivo (D-20). Hoy hay más que enseñar que nunca: la guía de los doce, el lenguaje visual único y `/empleo` funcionando | Directivo del capítulo |
| 14 – 18 sep | Dominio, SSL, capacitación y Acta 02 firmada; manual actualizado | Cronograma firmado (S8) |
| Por confirmar | Fecha de cierre del corte 3 y del PDF final a `proyectosing@cue.edu.co` | Docente asesor |

## 2. Inventario por frente

### 2.1 Producto

| Ref. | Estado | Qué falta, y de quién depende |
|---|---|---|
| OBS3-01 a 06, 08, 09, 12, 13, 14 | ✅ Cerrados | — |
| OBS3-07 (fotos y video del gremio) | ⚠️ El video está en el sitio. Las imágenes generadas **ya están permitidas** (§13, 16 sep). **Fotos reales de siete establecimientos** en `material/sep15material/` (ignorada) | Pies de foto y **autorización de imagen (D-03)** para usar las reales |
| OBS3-10 (enlaces al trámite) | ⚠️ Dos enlaces buenos sembrados; de veinticinco, **dieciocho abren la portada de la alcaldía** y no entran | Las **7 URL de trámite de Armenia** (D-04) |
| OBS3-11 | ⚠️ Código puesto (todo editable) | Texto propio de «Quiénes somos» y cargos (D-05) |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar** (D-01) |

**Lenguaje visual único (16 sep).** El ritmo editorial de Ingrid —bandas, folios, filetes, fotografía fundida— en El gremio, boletín, contacto, eventos, empleo, artistas, proveedores y la guía, con las superficies redondeadas y el movimiento de la casa. Un auditor en navegador recorrió trece páginas en los dos temas a 1440 y 390 px: **no queda ninguna caja con fondo o borde propio y esquina recta a la vista**; lo que la herramienta aún lista son piezas interiores recortadas por su tarjeta, celdas de una tabla redondeada y degradados de borde. **Abierto:** que Sua e Ingrid lo miren con ojos sobre la URL, en especial la barra (D-50) y la cabecera de la guía en claro.

**Eventos de aliados (16 sep).** Publicables desde el panel con **Origen → Aliado**. Un evento solo sale si su aliado sale (publicado, activo, alcaldías todas o ninguna), nunca se atribuye a ASOBARES si el aliado se borró, **no admite inscripción ni cobro del gremio**, y la portada solo pinta los del gremio. **Abierto:** acta (D-47) y la pregunta del cobro (D-10). Producción no tiene ningún evento hoy.

**Filament 5.8.2 / Livewire 4.4.5 (15 sep).** **Abierto:** el panel en un teléfono real bajo la versión nueva, y el tacto del resorte del riel.

### 2.2 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| **Guía normativa** | ✅ **12 municipios y 151 fichas en producción** desde el 15 sep. Cero costos publicados | Confirmación por escrito de la dirección (D-21) y las 7 URL de Armenia (D-04) |
| **Ajustes** | ✅ 200 en producción. El 16 sep una migración corrigió `eventos_intro` («Solo eventos del gremio…») porque seguía siendo lo sembrado | ⚠️ `hero_frase_corta` sigue pintándose en la portada y es de cosecha propia: **D-29** |
| **Franja «El gremio en cifras»** (D-25, Acta 05) | ✅ Código en producción, vacía de fábrica | Firmar el Acta 05; fijar y teclear las cuatro cifras |
| Portada | ✅ Todo texto editable; solo eventos del gremio | Las 19 fotos autorizadas sin colocar |
| Aliados | ⚠️ 23 del catálogo nacional, ninguno del Quindío | Nombres y `detalle_convenio` de los 18 departamentales (D-18) |
| Beneficios e iniciativas | ✅ 5 y 5, de documento oficial | Clasificarlos por alcance (D-39) |
| «Quiénes somos» | ⚠️ Propuesta de valor, lema y presidente ya son del gremio | Historia, qué hacemos, visión, cargos y junta (D-05) |
| Directorio | ⚠️ **0 fichas publicadas** en producción. La base del gremio vive **fuera del árbol** | `asociados:importar` y autorización de cada titular |
| Boletín laboral, formulario oficial de registro, certificado de afiliación | ❌ Sin publicar | D-18 |
| Cifra pública de afiliados | ⚠️ El sitio dice 60; la base 48; el directivo 60 | Natalia (D-18) |

### 2.3 Infraestructura

| Qué | Estado |
|---|---|
| Sitio | ✅ **200** sobre PostgreSQL 17, **50 migraciones**, sirviendo `6197c92` |
| Despliegues del 16 sep | ✅ Dos: `a57f044` (22:06 → 22:08 UTC) y `6197c92` (23:21:59 → 23:23:23). Recordatorio que no caduca: **el push a `main` despliega solo** y **no siembra nada** |
| Peso de las cabeceras | ✅ Siete fotos de 1672×941 de **13,1 MB en PNG a 0,86 MB en WebP**, sin pérdida visible (PSNR 36 dB sobre un recorte) |
| Correo saliente (SMTP) | ❌ **Sin contratar: bloque de arriba** |
| Bucket | ❌ Sin crear (D-13) |
| Dominio propio | ❌ Semana 8 (D-09) |
| Indexación | ✅ Cerrada de fábrica (`SITIO_INDEXABLE`); se abre el día del dominio |
| Scheduler | ✅ Tres purgas en producción |
| Rendimiento contra la URL | ⚠️ Solo tiempo total en caliente (0,79–2,18 s en quince rutas). La medición completa en frío sigue siendo la del 5 sep |
| Dispositivos reales | ✅ Parte pública cerrada el 11 sep. ⚠️ Sin ver **iOS/Safari**, **el panel bajo Filament 5 en el teléfono** y **el velo de la barra** en un equipo real |
| Repositorio | ⚠️ `Jsua3/asobares`, público. **Cero PR y cero CI.** `INGRIDMONWARTSKI` con `write`, sin segundo administrador (D-12) |

### 2.4 Datos personales

| Qué | Estado |
|---|---|
| **Imágenes generadas por IA** | ✅ **Permitidas y promovidas por la dirección** (§13, 16 sep; D-49 cerrada). Regla que sigue: no se presentan como fotografía de un local real |
| **Fotos de siete establecimientos reales** | ⚠️ En `material/sep15material/` (ignorada). **Personas identificables**, logotipos de terceros y un certificado de afiliación: **autorización de imagen de cada titular (D-03)** |
| **Inscripciones a eventos de aliados** | ✅ **El gremio no las recoge**: un evento de aliado no admite inscripción en línea (bitácora §57.3) |
| **Banco de talento** | ⚠️ `/empleo` vuelve a recibir perfiles desde el 16 sep. Los 7 perfiles previos aceptaron con una versión anterior de la política (D-27) |
| Fichas de asociados | ✅ Nacen en borrador; cero publicadas en producción |
| Base de establecimientos (`.xlsx`) | ✅ Fuera del árbol |
| Política de tratamiento de datos | ❌ D-19; bloquea al banco de talento |
| `nuevomaterial/`, `materialnoviembre/`, `sep15material/` | ✅ Las tres en `.gitignore` |
| Retención automática | ✅ Cerrada el 9 sep |

### 2.5 Académico

| Qué | Estado |
|---|---|
| Corte 1 | ✅ 5.0 |
| Corte 2 | ✅ Entregado a tiempo el 21 ago |
| Corte 3 (60 %) | ⚠️ Documento corregido en el repositorio. **Sin confirmación de envío.** Describe los RF de proveedores como públicos, sin el banco de talento, **la barra A** y, desde el 16 sep, «solo eventos del gremio» en el anexo F |
| Constancias | ✅ Acta 01, Formato 03, planeador. ❌ Acta 02 (S8), **Acta 04 y Acta 05 sin firmar**, **Acta 06 sin emitir** (D-26), **Acta 09 sin emitir** (D-47) |

## 3. Registro único de decisiones pendientes

Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md`. **El 16 sep entraron tres** en `encargo.md` §13: las imágenes generadas con IA, aprobadas y promovidas por Natalia (**cierra D-49**); **un solo lenguaje visual** sin superficies rectas; y **los eventos de aliados en producción**, sin acta todavía y sin cobro del gremio a nombre de un aliado. Contadas en la bitácora §57.

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| **D-50** | **La barra de escritorio en scroll: ¿velo que se desvanece o banda?** Ingrid la había cerrado con una banda opaca de lado a lado; la sesión del 16 sep la cambió por un velo translúcido y desenfocado que se desvanece bajo las píldoras, porque la banda dibujaba una raya recta y dejaba las píldoras planas (bitácora §57.4). Es diseño: lo confirman Sua e Ingrid mirando la URL pública | Sua + Ingrid | **16 sep** | — |
| **D-48** | **La rama de Ingrid.** Todo su trabajo de `a6ebcca` **ya está en `main`** (`d3aefd9`, sin la firma de Cursor), pero `origin/cierre/visual03-directorio-login` sigue apuntando a los commits viejos. **Lo siguiente lo empieza desde `main`**: si vuelve a esa rama y se fusiona, regresan la firma y la historia duplicada. La rama remota no se tocó: rehacerla exige un push forzado sobre su trabajo y eso lo decide ella | Ingrid | 15 sep | **Contenido integrado el 16 sep**; falta que ella cambie de base |
| **D-21** | **Municipios 2 a 12 de la guía** | Natalia | 1 sep | **Materia prima entregada el 15 sep** y código hecho. Falta que la dirección **confirme por escrito** que es la versión vigente: el archivo trae las doce filas en «Pendiente» |
| **D-47** | **Acta 09 — ampliación de alcance de la capa visual**, del salto de versión y, desde el 16 sep, **de los eventos de aliados**, que revierten «solo eventos del gremio» acordado con la directiva. El §9 del encargo exige registrar por escrito toda ampliación **antes** de codificarla | Sua | 9 sep | — |
| **D-46** | **Los dos tokens compartidos de la capa visual** (`--asb-apagado` y `--asb-accion`): cambian cómo se ven Afiliación, Publicidad, Directorio y Mi Cuenta, que son suyos. El 4,5:1 de RNF-12 no es negociable | Ingrid | 9 sep | — |
| **D-44** | **¿Cuál es la cuota vigente?** La cartera implica **$70.000/mes**; el formulario oficial dice **$30.000 los dos primeros meses y $50.000 desde el tercero**. Nada se siembra ni se publica hasta que Natalia lo diga por escrito | Natalia | 9 sep | — |
| **D-45** | **Los 41 nombres de la cartera contra la base de asociados**: hoy no cruza ninguno por *slug* | Natalia + Sua | 9 sep | — |
| **D-38** | **La lista real de la auditoría funcional**: 2 FAIL y 3 NOT TESTED sin nombrar | Ingrid | 8 sep | — |
| **D-39** | **Clasificar los cinco beneficios por alcance** | Natalia | 8 sep | — |
| **D-40** | **Aprobar los perfiles del banco de talento**, que nacen pendientes | Natalia + secretaría | 8 sep | — |
| **D-41** | **El reparto que propone el plan de trabajo invierte el registrado** | Sua + Ingrid | 8 sep | — |
| **D-31** | **`prefers-reduced-transparency` en un equipo real**: Playwright acepta la emulación y no la aplica | Sua | 5 sep | — |
| **D-32** | **Idiomas como subsistema propio.** Ampliación de alcance: acta antes de codificar | Natalia + Sua | 3 sep | — |
| D-26 | **Acta 06** de la ampliación de las bolsas; retirar o reubicar `docs/ingenieria/decisiones/` | Sua + Ingrid | 3 sep | — |
| D-27 | **Política de tratamiento y los 7 perfiles** que aceptaron con otra versión | Natalia + aliado jurídico | 3 sep | — |
| D-28 | **Alta de credenciales de afiliado**: sin ella nadie ve proveedores ni banco de talento | Natalia + Sua | 3 sep | — |
| D-29 | **`hero_frase_corta`** y la **banda de tres videos** que promete piezas inexistentes | Ingrid | 3 sep | — |
| D-01 | Firma del **Acta 04** y del **Acta 05** con sus cuatro cifras | Natalia + directivo | 30 ago / 1 sep | — |
| D-03 | **Pies de foto y autorización de imagen**. Siete establecimientos reales llegaron el 15 sep con personas identificables. Las imágenes generadas ya no esperan a esto (§13, 16 sep); las fotos reales sí | Natalia | 26 ago | Autorización de las 19 del gremio, 1 sep ✅ · el resto: — |
| D-04 | **Las 7 URL de trámite** de Armenia (OBS3-10) | Natalia / Alcaldía | 28 ago | — |
| D-05 | **Texto propio de «Quiénes somos»**; nombres y cargos | Natalia + Nacional | 5 ago / 28 ago | Materia prima completa el 9 sep; falta aprobación |
| D-06 | **Logos** de aliados en buena resolución y cuáles aplican en el Quindío | Natalia | 31 ago | Institucional resuelto el 9 sep |
| D-07 | **SMTP con el correo del gremio** (bloque de arriba) | Natalia + Sua | 15 ago | Buzón conocido; falta la credencial |
| D-09 | **Dominio propio** | Natalia | 5 ago | — |
| D-10 | **Pasarela**: «solo Bold» por escrito; PSE o QR. Y desde el 16 sep: **¿el gremio cobra alguna vez a nombre de un aliado?** Hoy no puede: un evento de aliado no admite inscripción ni cobro | Natalia + contadora | 28 ago | — |
| D-11 | **Cartera**: mecanismo de actualización del archivo de la contadora | Luisa + Natalia | 28 ago | Archivo recibido el 9 sep |
| D-12 | **Titularidad de la infraestructura**: facturación, Natalia miembro, segundo admin en GitHub | Sua + Natalia | 30 ago | — |
| D-13 | **Bucket y fotos pendientes** | Sua | 31 ago | — |
| D-14 | **Marca de procedencia en el contenido sembrado**, y qué ajustes tocó la oficina | Sua | 1 sep | — |
| D-18 | **Confirmaciones del plan del material**: condiciones de los 18 aliados departamentales | Natalia | 26 ago | Aliados de hecho 9 sep ⚠️ · condiciones: — |
| D-19 | **Política de tratamiento de datos** (ver D-27) | Natalia / aliado jurídico | 5 ago | — |
| D-20 | **Fecha de la segunda demostración** | Directivo + Natalia | 28 ago | — |
| D-43 | **¿Vuelve la campana del panel?** | Sua + Natalia | 9 sep | — |

## 4. Deuda diferida a propósito

No se «arregla de paso»:

- **Los tokens de radio compartidos** (`--asb-radio-celda`, `--asb-radio-pieza`…) que pidieron los agentes de la unificación no se crearon. Cada hoja tiene su escala con guardia; renombrar en todas a seis días de la entrega no compra nada que se vea.
- **El conmutador de `/eventos` en el teléfono** parte «Calendario» a una segunda línea dentro de la píldora. Se lee bien; se ve menos limpio que en escritorio.
- **La cabecera de la guía en claro** funde una foto nocturna sobre crema con una sombra redonda para el neón. Resuelve los cantos; el gusto lo confirman Sua e Ingrid, junto con D-50.
- **Ninguna prueba ve una imagen**: la procedencia (C2PA) y el peso de un archivo nuevo se miran a mano.
- **Guardias sobre `.js`** acotadas con `[\s\S]*` sobre el archivo entero: se arreglaron las dos conocidas; el patrón puede repetirse.
- **`hamcrest` 3.0.0** llegó con el salto de versión; entra por Mockery.
- **Del panel en el teléfono:** el tacto del resorte solo lo cierra un aparato; se ajusta con `ARRASTRE` y `AMORTIGUACION`.
- **De la barra pública B:** `$rol`/`$prefijoRol` en `menu-usuario.blade.php` son dos `match` que recalculan lo mismo.
- **Preexistente:** tabular hacia el header estando desplazado devuelve la página al tope (Chromium).
- **El correo de ficha de bolsa publicada enlaza a `/proveedores`**, que no nombra al proveedor. Se arregla cuando haya SMTP; es decisión de contenido.
- El consecutivo de PQR bajo concurrencia falla cerrado. No existe `lang/` (D-32).
- Cuatro `index.lock.huerfano*` y `.git/huerfanos-cowork-2026-09-01/` en `.git/`: **los borra Sua a mano**.

## 5. Cifras medidas del árbol

Medidas el **16 de septiembre de 2026 sobre `6197c92`**, que es lo desplegado. **Vuelve a medirlas antes de citarlas** en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | **655** | `git rev-list --count main` |
| Migraciones | **50** | `ls database/migrations/*.php` |
| Modelos | **24** | `ls app/Models/*.php` |
| Sembradores | **21** | `ls database/seeders/*.php` |
| Fábricas | **19** | `ls database/factories/*.php` |
| Archivos de prueba | **146** | `find tests -name '*Test.php'` |
| Métodos de prueba | **1.349** | `grep -rhE '^\s*public function test_' tests` |
| Vistas Blade | **107** | `find resources/views -name '*.blade.php'` |
| Componentes públicos | **29** | `ls resources/views/components/publico/*.blade.php` |
| Hojas editoriales | **8** | `ls resources/css/*-editorial.css` |
| Panel | **21** recursos · **6** páginas · **22** policies · **16** widgets | `find app/Filament/Resources -maxdepth 1 -mindepth 1 -type d`, `ls app/Policies/*.php` |
| Comandos de Artisan propios | **6** | `ls app/Console/Commands/*.php` |
| Enums | **22** | `ls app/Enums/*.php` |
| Controladores públicos | **19** | `ls app/Http/Controllers/Publico/*.php` |
| Middleware propio | **3** | `ls app/Http/Middleware/*.php` |
| Archivos de configuración | **18** | `ls config/*.php` |
| Rutas GET propias | **96** | `php artisan route:list --method=GET --except-vendor --json` |
| **Suite completa** | **1.738 casos · 1.738 pasan · 0 fallos · 12.319 aserciones · 616 s** | `php artisan test --compact` |
| Suite antes del día | 1.617 casos · 1.617 pasan · 8.541 aserciones (`a57f044`) | `php artisan test --compact` |
| Peso de las cabeceras servidas | **812.360 bytes** en siete WebP (eran 13,1 MB en PNG) | `du -cb public/img/*/hero-*.webp public/media/abre-tu-negocio/*.webp` |
| Despliegue del 16 sep | push 23:21:59 · terminado 23:23:23 · **84 s** · 11 commits · 3 migraciones | `git ls-remote` + `cloud deployment:list` + `curl` |
| Ajustes | **200** en producción | `Setting::all()` por `cloud command:run` (15 sep) |
| Permisos | **88** · super_admin **88** · subadmin **52** | producción, 15 sep |
| Fichas de la guía | **151 servidas en producción** | `curl` a las doce URL (15 sep) |

## 6. Lo siguiente, en orden

1. **Mirar la URL pública con ojos, Sua e Ingrid**, en claro y oscuro, en escritorio y en el teléfono: la barra en scroll (**D-50**), la cabecera de la guía, `/contacto`, `/eventos`. Es lo único que ninguna prueba cubre, y cambió mucho en un día.
2. **Decirle a Ingrid** que su trabajo está en `main` y que **empiece lo siguiente desde `main`** (D-48): `git fetch` y una rama nueva desde `origin/main`, no desde `cierre/visual03-directorio-login`.
3. **Emitir el Acta 09** (D-47), que cubre la capa visual, el salto de versión y **los eventos de aliados**.
4. **Confirmar los dos envíos a la universidad** (4 y 11 sep). Si no salieron, es 0.0 y hay que hablar con el docente hoy.
5. **Fijar la demo 2** (D-20) con la guía, el lenguaje visual nuevo y `/empleo` funcionando.
6. **Una sola reunión con Natalia** con la tabla del §3 impresa, incluida la pregunta nueva del cobro a nombre de aliados (D-10). Si está la cuenta de Google del gremio, se hace ahí mismo el SMTP.
7. **Apagar `hero_frase_corta`** si Ingrid lo decide (D-29): `Setting::where('clave', 'hero_frase_corta')->update(['valor' => null])`.
8. **Rehacer la matriz de trazabilidad** y corregir el documento de práctica (barra A, «solo eventos del gremio»).
9. Semana 8: dominio y SSL, manual en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).
