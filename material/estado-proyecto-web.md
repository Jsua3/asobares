# Estado del proyecto — Plataforma Web ASOBARES Capítulo Quindío

**Actualizado:** 25 de agosto de 2026 · **Anclado a:** `main` en `bad0143` (230 commits), sincronizado con `origin`

> ⚠️ **PARCIALMENTE SUPERADO. El estado vigente es el §31 del prompt maestro** (1 de septiembre de 2026, `main` en `493790d`, 270 commits, suite en 970 casos). De este documento se actualizaron los puntos **1** y **3** de la lista de pendientes, que habían dejado de ser ciertos; el resto conserva la redacción del 25 de agosto. El **§31.2** trae el inventario consolidado de lo cumplido y lo que falta.
**Documento único de estado.** La investigación de fondo está en [`investigacion-asobares-quindio.md`](investigacion-asobares-quindio.md); el historial de decisiones técnicas y sus trampas, en [`prompt-maestro-laravel-filament.md`](prompt-maestro-laravel-filament.md) §15–§23.

> ⚠️ **Este documento envejece en horas, no en semanas.** Su versión anterior estuvo dieciséis días sin tocarse mientras se ordenaba a sí misma «mantener este doc al día», y llegó a describir un proyecto tres semanas atrasado. Cómo re-verificarlo está al final, en §12.
>
> **El árbol de trabajo está limpio al escribir esto**, y `main` está empujado. La única entrada sin rastrear es `docs/ingenieria/reuniones/`, que no es de esta sesión.
>
> ⚠️ **Hay una rama viva que no es de este hilo:** `origin/p2-directorio`, con tres commits de Ingrid Montoya. **No está fusionada en `main`** y hoy no fusionaría limpio — ver §11.

---

## 1. Qué es el proyecto

Plataforma Web Oficial de **ASOBARES Capítulo Quindío**, el gremio de la vida nocturna del departamento, desarrollada en el marco de una **práctica universitaria con la Universidad Alexander von Humboldt** (Armenia).

El documento rector es **`material/CRONOGRAMA SITIO WEB - 2026.pdf`** (7 páginas, firmado por Natalia Gutiérrez, directora ejecutiva). Este es el único sitio del expediente donde consta esa ruta: el informe de cumplimiento lo cita sin ella.

⚠️ **Ese PDF no contiene ninguna fecha de calendario.** Habla de «Semanas 1 a 8» sin anclarlas. **La fecha límite del 22 de septiembre de 2026 y el mapeo S1…S8 a fechas concretas son acuerdo interno, no cláusula firmada.** Conviene saberlo antes de invocar el cronograma como contrato en una discusión de plazos.

**Tutora empresarial:** Natalia Gutiérrez, directora ejecutiva — es quien revisa el producto y reporta errores. **Docente asesor:** César Augusto Granada Muñoz, que **no revisa código**: califica el documento de práctica GU-DO-007.

---

## 2. El estado en una línea

**El producto va dos o tres semanas por delante del cronograma; la evidencia contractual va por detrás.** Lo que falta no es código de funcionalidad: es un servidor, dispositivos reales, capacitación y firmas. El cuello de botella dejó de ser técnico el 15 de agosto, cuando el despliegue quedó bloqueado por un trámite que no es de ingeniería.

---

## 3. Qué está construido (verificado el 25 de agosto)

Cifras contadas sobre el árbol de trabajo, no heredadas de documentos anteriores.

| Medida | Valor |
|---|---|
| Commits | **230**, del 1 al 25 de agosto. En `main`, autor único `Jsua3`; hay tres más de Ingrid en una rama sin fusionar (§11) |
| Modelos Eloquent | 21 |
| Migraciones | 35 |
| Recursos de Filament | **19** (las versiones anteriores de este documento decían 18 y omitían el Observatorio) |
| Rutas registradas | 101 propias del proyecto (`route:list --except-vendor`). La cifra de 114 de la versión anterior incluía rutas de paquetes |
| Archivos de prueba | 60 |
| Suite | **820 casos · 809 pasan · 11 omitidas · 0 fallos · 2.904 aserciones** |

**Stack real:** Laravel **13.23.0** + Filament **4.12.5** + Livewire **3.8.3** sobre PHP **8.5.9**, PHPUnit 12.5.33, SQLite en desarrollo. Es la única afirmación técnica de las versiones anteriores de este documento que sigue siendo exacta.

> Las 11 pruebas omitidas son deliberadas: Cartera, Postulación y Vacante no tienen página de creación en el panel porque ese contenido no lo teclea la oficina. **Nunca citar «0 fallos» sin decir que hay 11 omitidas.** Y no citar la duración de la suite como cifra exacta: tres corridas del mismo código dieron 169, 173 y 179 segundos.

**Módulos entregados:** sitio institucional, directorio con mapa y filtros, guía normativa por municipio con formatos descargables, las tres bolsas (empleo, artistas, proveedores), eventos con inscripción y pago, boletín, afiliación, contacto con radicado PQR, portal `/mi-cuenta` con cartera y «Mis vacantes», panel `/admin` completo con bitácora y MFA obligatoria, Observatorio del gremio, y pasarela Bold conmutable con una simulada para demostración.

⚠️ **La ruta `asobares-web/` no existe y no existió nunca en el historial.** Era una carpeta local anterior a la unificación del repositorio (commit `7a1009e`, 3 de agosto). **El producto es la raíz del repositorio.** Las versiones anteriores de este documento mandaban a una carpeta inexistente.

**Documentación de entrega** — `docs/ingenieria/`, commiteada el 19 de agosto: matriz de pruebas con trazabilidad RF/RNF, manual de usuario con 11 capturas del panel real, medición de rendimiento, base de datos exportada (37 tablas, 1.526 filas), 7 diagramas UML/BPMN con sus fuentes PlantUML, e informe de cumplimiento del cronograma.

---

## 4. Los dos calendarios

Corren en paralelo y no son el mismo.

### Carril empresa — cronograma firmado

| Semana | Fechas (acuerdo interno) | Estado |
|---|---|---|
| S1 · Levantamiento, sitemap, stack | 27–31 jul | ✅ |
| S2 · Wireframes, paleta, tipografías | 3–7 ago | ✅ construido · ⚠️ **el hito de aprobación del diseño nunca se firmó** |
| S3 · Proyecto, BD, menú, institucional, **hosting de pruebas** | 10–14 ago | ⚠️ todo menos el hosting (R-14a) |
| **S4 · Directorio + eventos** | **17–21 ago** | ✅ desde el 5 de agosto |
| S5 · Pasarela en sandbox | 24–28 ago | ⚠️ **código sí, exigencia contractual no** — ver abajo |
| S6 · Panel CMS | 31 ago–4 sep | ✅ 19 recursos |
| S7 · Pruebas globales, dispositivos reales, bugs de la tutora | 7–11 sep | ❌ sin empezar |
| S8 · Dominio + SSL, capacitación, documentación, BD exportada | 14–18 sep | ⚠️ solo la documentación y la BD |

⚠️ **El hito de aprobación del diseño es el único hito que el documento firmado declara**, y cierra la Fase 1 entera (S1 y S2 juntas). Sin constancia escrita, la Fase 1 no está formalmente aceptada por más que su trabajo esté hecho.

⚠️ **S5 merece precisión.** Está construida la interfaz de pasarela, la implementación de Bold con verificación de firma en el webhook, y una simulada para demostrar. Lo que el cronograma pide es otra cosa: **«pruebas de transacción en entorno Sandbox» del proveedor**. Las llaves de Bold están vacías en `.env` y el callback necesita una URL pública alcanzable. **Sin hosting no hay ensayo en sandbox, así que R-14 bloquea también la S5**, no solo S3, S7 y S8.

### Carril universidad — asesoría de César Granada

- Avances **todos los viernes 11:59 p. m.**, sin excepción; tarde vale 0.0. Cortes: C1 20 % · C2 20 % · C3 60 %.
- **Por el sismo del 10 de agosto la entrega se corrió al viernes 21 de agosto, 23:59:59**, que es el cierre del corte 2.
- El corte 2 evalúa cuatro cosas: fundamentación teórica terminada; **≥ 80 % del capítulo 5**; **retroalimentación del empresario**; y cumplimiento de entregas.
- Instrucciones vigentes del 10 de agosto: **capítulos 1 a 4 completos** e iniciar 5, 7 y anexos; **documentos distintos a los de la compañera**; respetar la longitud por capítulo; **entregar en `.docx`, no en PDF**.
- Lo único que César dejó abierto por escrito (18 de agosto): **«haga firmar el documento de la tutora empresarial y me lo vuelve a enviar»** — el planeador FO-DO-100 necesita la firma de **Natalia**, no la del practicante.

---

## 5. Lo que está abierto, en una sola lista

Ordenado por lo que bloquea a más cosas. Cada uno con su dueño, porque un pendiente sin dueño no se cierra.

1. ~~**R-14a · Cuenta institucional de hosting.**~~ ✅ **CERRADO.** La cuenta existe desde el 30 de agosto y **el sitio está desplegado, sirviendo 200 sobre PostgreSQL 17.11 con contenido oficial** desde el 1 de septiembre (§31 del prompt maestro). Ya no bloquea S3, S5, S7 ni S8. ⚠️ **Queda un rescoldo del mismo riesgo:** la organización de Laravel Cloud es `juan-sua`, personal, y **el repositorio sigue en cuenta personal de GitHub y es público**. El modo de fallo que describía este punto —«las llaves se van con el practicante el 22 de septiembre»— no se ha conjurado, solo se ha movido de sitio.
2. **R-14b · Dominio.** *Dueño: dirección ejecutiva.* Cuál (`asobaresquindio.com`/`.com.co` o subdominio de `asobares.org`), quién lo paga y **a nombre de quién queda registrado**. Nada registrado. Es exigencia de la S8 y es distinto de la cuenta de hosting: se puede tener servidor y no tener dominio.
3. **Contenido normativo real.** *Dueño: Natalia + quien el gremio designe.* ⚠️ **Actualizado el 1 de septiembre de 2026 — tres frases de este punto dejaron de ser ciertas.** Ver §31 del prompt maestro.

   **Lo que ya NO es verdad:** que haya «3 municipios con contenido» (hoy es **1**, Armenia, y es el único con fuente oficial); que los trámites estén «escritos a mano, sin verificar contra la fuente y sin fechar» (los **siete de Armenia** salieron del documento de la Alcaldía, van **fechados el 20 de agosto** y cada ficha nombra su fuente); y que «los formatos descargables son PDF de relleno generados por el sembrador» (**se retiraron**, junto con los doce costos inventados que tenía la guía).

   **Lo que sigue abierto, y es lo que hay que reclamar:** los **otros once municipios** —no hay documento para ninguno—, los **formatos oficiales** de cada entidad, las **siete URL de trámite** para que los enlaces dejen de caer en la portada (OBS3-10), y **quién queda responsable** de revisar esto cuando la normatividad se mueva. Sin ese último, la guía se vuelve desinformación con el tiempo.

   El esquema no estorba: RF-60 lo resolvió el 25 de agosto y la tabla guarda `verificado_el`, `verificado_con` y `vigente_hasta`. Una ficha sin fechar se publica igual pero lo dice en su cara — que es exactamente lo que hace hoy la octava, la lista de la ley 1801.
4. **Correo saliente.** *Dueño: dirección + equipo.* Laravel Cloud no lo incluye. Sin proveedor (Resend/Postmark/SES a nombre del gremio), **los códigos MFA mueren en el log y el login del panel no se puede demostrar** ante la dirección aunque haya hosting.
5. **Resto de G12, Ley 1581.** *Sin dueño asignado — riesgo jurídico.* Nombrar a los encargados del tratamiento (incluida la pasarela), abrir el canal de supresión a petición del titular, y que **alguien que responda legalmente por el gremio revise el texto** de la política, hoy redactada por un agente describiendo el comportamiento del código.
6. **Datos de los ~60 asociados y sus autorizaciones de publicación.** *Dueño: Natalia.* Sin ellos el directorio se lanza vacío. Se lleva pidiendo desde principios de agosto; conviene fecha comprometida en acta, no otra petición de palabra.
7. **Manual de usuario en formato contractual.** *Dueño: equipo.* El cronograma admite **PDF o vídeo**; hoy es un Markdown dentro del repositorio, que no es ninguno de los dos y cuyo destinatario declarado es personal no técnico.
8. **Capacitación y su constancia.** *Dueño: equipo + gremio.* El criterio no es que el manual exista, sino que al terminar la sesión **el personal publique un asociado, un evento y una noticia sin ayuda**.
9. **Hito de aprobación del diseño (S2)** y **firma de la ERS v3.0.** *Dueño: dirección.* Ambos son papel, no código.
10. **Calendario de eventos (RF-19).** *Dueño: dirección.* El cronograma firmado dice «calendario + formularios»; hay grilla Próximos/Pasados. O se construye la vista, o se acuerda por escrito que la grilla lo sustituye.
11. **Accesibilidad medida y no corregida.** *Dueño: equipo.* **41 de 75 objetivos táctiles por debajo de 44 px**, y el indicador de foco anulado en **dos** archivos, no en uno: `components/publico/campo.blade.php:16` y `publico/mi-cuenta/vacantes/show.blade.php:59`. Choca con la exigencia del cronograma de que el uso táctil sea «100 % cómodo».
12. **Enlace a Asobares Nacional.** *Dueño: equipo.* El cronograma lo pide «en el menú de navegación»; hoy vive en el pie de página. Es literalidad, pero es literalidad de un documento firmado.
13. **Tres decisiones técnicas de despliegue sin tomar ni anotar.** *Dueño: equipo.* `TRUSTED_PROXIES` (sin él las URLs salen en `http` —incluida la que recibe Bold— y los límites por IP colapsan), el régimen de indexación del entorno de pruebas (hoy `robots.txt` permite todo sin condición de entorno y no hay `noindex`), y la estrategia de respaldos.
14. **Fecha oficial de lanzamiento.** *Dueño: dirección.* Siguen conviviendo el 24 de septiembre y el 26 de noviembre, y **las dos son posteriores al 22 de septiembre en que termina la práctica**, siendo que la dirección espera que los practicantes presenten la página en el evento del gremio. Hay que decidirla y decidir quién presenta si cae después.
15. **Traslado del repositorio a una organización del gremio.** *Dueño: dirección + equipo.* `Jsua3/asobares` vive en cuenta personal de GitHub: misma familia de problema que R-14a.

**Congelado a propósito:** el pase de interfaz iOS y sus siete hallazgos vivos. Siguen siendo válidos, pero ninguno bloquea la entrega, ninguno está en el cronograma y ninguno lo califica el docente.

---

## 6. Decisiones tomadas, con fecha

| Decisión | Cuándo | Detalle |
|---|---|---|
| **Stack: Laravel + Filament** | cerrada de hecho | **No hay finalistas WordPress vs Laravel.** Son 196 commits sobre Laravel 13.23 + Filament 4.12, y el encargo prohíbe expresamente WordPress. Lo que sigue abierto ante la junta es el **presupuesto de hosting**, no el stack. Presentarlo como decisión pendiente invita a reabrir lo que la realidad cerró hace tres semanas |
| **Hosting: Laravel Cloud, plan Starter** | 15 ago | Con condición inseparable: **la cuenta nace institucional**. Escalado a riesgo R-14 el 18 de agosto |
| **Pasarela: Bold** | Reunión 1 y 2 | Ya contratada por el gremio. Preferencia fuerte por **PSE**; cuenta del gremio en **Itaú**. Faltan RUT, cámara de comercio y datos bancarios |
| **Guía normativa por municipio**, no generalizada | Reunión 2 | Con checklist y formatos descargables; acabado formal |
| **El propietario del establecimiento decide** qué se publica de él | Reunión 2 | No lo decide Asobares |
| **Eventos solo del gremio**; boletín de baja frecuencia | Reunión 2 | La directiva es escéptica de secciones que exijan alimentación constante |
| **Las bolsas las escribe quien tiene la necesidad** | 4 ago | El asociado publica su vacante desde `/mi-cuenta/vacantes`; el gremio aprueba o devuelve **con motivo obligatorio**, y no reescribe lo ajeno |
| **Sitio bicromático** con tokens semánticos | 4 ago | Claro y oscuro al mismo nivel; cero colores cableados en las vistas |
| **Identificadores y comentarios en español** | 5 ago | `CLAUDE.md` se contradice; gobierna la convención existente del código |

---

## 7. Identidad de marca — corregida

Las versiones anteriores de este documento daban **`#EE4036`**, muestreado a ojo del logo. **Es incorrecto.** Los valores del Manual de Marca Asobares Colombia, que es la fuente, y los tokens reales del código (`resources/css/tokens.css`):

| Color | Valor |
|---|---|
| Pub Red | **`#EE4137`** |
| Pub Black | `#0B090A` |
| Ambient White | `#F5F3F4` |
| Acento accesible — claro | `#B71F18` |
| Acento accesible — oscuro | `#F27166` |

El Pub Red puro **no alcanza contraste AA como texto sobre fondo claro**; por eso el token de acento es distinto en cada tema. Tipografía: **Poppins** (Light 300, Medium 500, Bold 700, Black 900). Quien pinte algo copiando del documento viejo usa el color equivocado.

---

## 8. Enfoque estratégico

- **Reunión 1** (Natalia): dos propósitos — visibilizar los establecimientos y guiar a quien quiera abrir uno.
- **Reunión 2** (31 jul, un directivo del capítulo): **re-ponderación**. «Yo no creo que Asobares sea una plataforma de comercialización de bares… no creo que la fuerza de mercadeo de los bares se haga a través de Asobares». El valor está en la **representatividad ante instituciones** y en la **guía normativa como producto insignia** («ningún gremio la tiene; es donde caen los negocios y los cierran»). Usuario objetivo: el dueño pequeño que «es el mismo dueño, el bartender y el que hace todo».
- **Prioridades top 3 del directivo:** (1) bolsa de empleo, (2) guía de requisitos con formatos descargables, (3) directorio de asociados.

⚠️ **Este giro nunca se validó por escrito con Natalia**, pese a que el acta lo ordenaba, y **la voz que lo pronunció sigue sin identificarse formalmente** («presumiblemente Jorge Iván Botero, presidente — confirmar nombre y cargo», sin confirmar en diecinueve días). Sobre esa voz descansan la priorización y seis módulos que no están en el cronograma firmado.

---

## 9. El agujero contractual, sin eufemismos

- **Seis módulos se construyeron fuera del alcance firmado**: bolsa de empleo, artistas, proveedores, cartera del asociado, portal `/mi-cuenta` y Observatorio.
- **El documento de requisitos v2 nunca se produjo**, pese a que en la Reunión 2 se cerró con el compromiso de «dejar completamente claros los requisitos y ya no cambiarlos».
- **La ERS v3.0 está pendiente de firma** y no vive en este repositorio, igual que el v1. **Ningún identificador RF-xx o RNF-xx que use la documentación de entrega es auditable contra su fuente desde aquí.**

No es un problema de calidad del software; es que el software entregado y el papel firmado describen cosas distintas, y solo el papel se puede invocar en una discusión.

---

## 10. Notas operativas que no viven en ningún otro sitio

- **Protocolo de grabación de reuniones:** teléfono sobre la mesa, boca arriba, micrófono hacia los participantes — **nunca en el bolsillo**. Así se perdió la grabación de la Reunión 2 (sin energía sobre ~3.400 Hz; consonantes irrecuperables incluso con EQ). Que grabe también la compañera.
- El botón **«Afíliate» apunta a WhatsApp 321 5549513**; el proceso administrativo oficial de afiliación sigue sin definir.
- La noticia **«Armenia sede de Expobar 2026»** de `armenia.gov.co` está **caída (404)**: conseguir soporte oficial antes de publicarla. Hay un evento «ExpoBar Quindío 2026» en la base de demostración.
- El enlace de la bio de Instagram es del **Foro Quindío Nocturno** (11 nov) — es un evento, no el formulario general de afiliación.
- **Cifras públicas:** usar solo las del Panorama de marzo 2026, citando «Observatorio Económico de Asobares».
- **El tutorial visual `Tutorial Plataforma ASOBARES Quindio.html` está OBSOLETO.** Tiene 32 láminas de las 34 pedidas, no se toca desde el 3 de agosto, y **enseña el modelo contrario al construido**: dice que la Secretaría crea las vacantes, cuando hoy las escribe el empresario desde su portal. Cero menciones a «Mis vacantes», postulaciones o banco de talento. Su propio prompt lo declara pendiente de regenerar.
- **El Observatorio dibuja 1 de 6 gráficas**; las otras cinco declaran «aún sin muestra suficiente». Es honesto, pero **la única que dibuja se calcula sobre datos sembrados** (182 transacciones y 24 carteras ficticias), no sobre operación real. Decidir antes del 22 de septiembre cómo se enseña.
- **La base de datos exportada es la de demostración**, con 24 establecimientos ficticios y tres cuentas de contraseña publicada. Su propio README prohíbe restaurarla sobre producción. La base real del gremio no existe todavía porque depende del punto 6 de §5.
- **Residuos sin registrar:** el worktree `.claude/worktrees/suspicious-colden-d9e9e8` sigue vivo en `82398a6` (detached) aunque la rama ya no exista; y el repositorio **no tiene integración continua ni etiquetas de versión**, así que ninguna cifra de calidad está respaldada por una ejecución automática auditable.

---

## 11. Lo que este documento NO puede afirmar

Esta sección existe porque su ausencia es lo que convirtió a la versión anterior en un documento engañoso. **Nada de lo siguiente es verificable desde este repositorio**, y ninguna de estas cosas debe darse por hecha al citar este documento.

**Fuera del repositorio.** Todo el espacio de trabajo de claude.ai. Las versiones anteriores listaban dieciséis «archivos producidos» y **diez no existen aquí ni han existido nunca en el historial**: el planeador FO-DO-100, el documento de práctica, el de requisitos v1, `index.html`, el kit de marca, el plan de trabajo v3, la comparativa de stacks, el guion de la Reunión 2, los avances semanales y `transcripcion-reunion-1.md`. Cuatro de ellos los cita el informe de cumplimiento como «Evidencia» ante la universidad.

**La Reunión 1 entera.** Su única fuente citada no existe aquí y no hay acta. Nada de lo que se le atribuye —la cita fija de los viernes a las 9:00, las condiciones de Natalia sobre presupuesto, el certificado hecho a mano— es auditable, y el propio documento viejo advertía que la transcripción confundía nombres y cifras.

**Todo lo posterior al 19 de agosto a las 08:05.** Si la reunión del viernes 21 se confirmó tras el sismo y qué salió de ella; si Natalia firmó el planeador; si se consiguió la retroalimentación escrita del empresario; si se entregó el corte 2 y con qué nota; si la dirección respondió sobre la cuenta de hosting; si existe ya despliegue, dominio o SSL. **La ausencia de anotación no prueba que no ocurriera: prueba que nadie escribió el resultado.**

~~**El trabajo de la compañera de práctica.** 196 commits con autor único, sin ramas, sin PRs y sin coautoría.~~ **Dejó de ser cierto el 25 de agosto**, y se mueve aquí con su fecha en vez de borrarse, como manda el cierre de esta sección.

Existe `origin/p2-directorio` con **tres commits de Ingrid Montoya** (`imontoya_624@unihumboldt.edu.co`), del 20 y el 25 de agosto, sobre su propio bloque del reparto —directorio, bolsa de empleo y portal `/mi-cuenta`— y **con pruebas propias**: `DirectorioTest` son 168 líneas nuevas, más casos añadidos a `BolsaDeEmpleoTest` y `FormulariosPublicosTest`. Eso es exactamente la evidencia de autoría separada que el corte pide.

⚠️ **El riesgo baja, no se cierra**, y la diferencia importa:

- **Su rama NO está en `main`.** Mientras no se fusione, un clon del proyecto sigue enseñando autoría única.
- **Hoy no fusionaría limpio.** Sale de `1ff87d0` (19 de agosto) y choca en cuatro vistas —`navbar`, `directorio/index`, `directorio/show` y `mi-cuenta/index`— **todas contra el mismo commit**, `09e3e33`, que es el pase de foco visible y objetivos táctiles. Otros nueve ficheros se fusionan solos y nada de RF-60 estorba. Medido con `git merge-tree`, sin tocar su rama.
- **Resolver esos conflictos a la ligera revierte RNF-12 en silencio**, que es un requisito no funcional contratado y hoy declarado verificado. Las guardias `FocoVisibleTest` y `ObjetivoTactilTest` lo cazarían, pero sólo si alguien corre la suite después de resolver.
- **El expediente todavía afirma lo contrario.** El informe de avance del 21 de agosto §2 justifica su alcance diciendo que los commits tienen autor único. No se corrige retroactivamente un informe ya entregado: la nota va en el siguiente.

**Otras cosas sin rastro:** si alguien ha visto el archivo de cartera de la contadora; si se abrió el registro de bugs de la tutora; el patrocinio de Claude Max; el horario de los miércoles presenciales; los 51 hallazgos restantes de la auditoría de interfaz; y si la integración con Bold funciona contra el sandbox real, con las dos incógnitas de su documentación aún sin resolver.

**Reproducibilidad de la medición de rendimiento.** Las 78 mediciones y los 972 ms de la portada están en `docs/ingenieria/medicion-de-rendimiento.md`, pero **el guion que las produjo no está en el repositorio** ni hay artefacto crudo. Hay que creerlas: no se pueden re-derivar aquí. Y están tomadas **contra `localhost`**, así que no incluyen la latencia real hasta un servidor: el requisito contractual habla de un usuario, no de un navegador conversando con su propia máquina.

---

## 12. Cómo mantener este documento

No basta con la buena intención de «mantenerlo al día»: eso ya se incumplió dieciséis días. Antes de citarlo, re-verificar con estos comandos y actualizar §3 y la cabecera:

```bash
git rev-parse --short HEAD && git log --oneline | wc -l && git status --porcelain && php artisan test --compact
```

Lo que envejece más rápido, por orden: **el estado del árbol** (cambia en horas, y hay más de una sesión trabajando aquí), **las cifras de la suite**, el hash de anclaje, y el reparto de lo abierto en §5. Lo que casi no envejece: §7 (marca), §9 (agujero contractual) y §10 (notas operativas).

Si algo de §11 deja de ser incierto, **muévelo a §5 o §6 con su fecha** en vez de borrarlo en silencio: la lista de lo que no se sabe vale tanto como la de lo que sí.
