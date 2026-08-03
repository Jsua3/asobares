# Prompt maestro para Claude Design — Tutorial de la Plataforma ASOBARES Quindío

> **Cómo usar este archivo:** copia desde la línea «EMPIEZA EL PROMPT» hasta el final y pégalo
> como instrucción en Claude Design (o cualquier herramienta de generación visual).
> Es autocontenido: no necesita acceso al código ni a este repositorio.
> La referencia funcional ya existente es `material/tutorial-plataforma-asobares.html`
> (32 diapositivas interactivas); este prompt sirve para producir una versión visual
> superior — presentación animada, video explicativo o deck — sin perder ningún detalle.

---

## EMPIEZA EL PROMPT

Diseña una **presentación-tutorial interactiva y animada** (formato diapositivas, 16:9,
navegable con flechas y con índice) que enseñe a usar la plataforma web de **ASOBARES
Capítulo Quindío** a sus tres tipos de usuario. El contenido de abajo es la fuente de
verdad: **no inventes funciones, textos de interfaz, ni datos que no estén aquí**.
Todo el texto va en **español colombiano, tono cercano pero profesional** (se le habla
al usuario de "tú").

### 1 · Identidad de marca (obligatoria, sin excepciones)

- **Paleta oficial** (Manual de Marca Asobares Colombia):
  - Pub Red `#EE4137` (color protagonista y de acción)
  - Pub Black `#0B090A` (fondo base: la estética es nocturna, oscura)
  - Ambient White `#F5F3F4` (texto principal)
  - Pub Grey `#282628` (superficies secundarias)
  - Wine `#A4161A`, Ambient Purple `#C05299`, Ambient Rose `#EA698B` (secundarios)
- **Tipografía: Poppins** en pesos Light 300 (texto), Medium 500, Bold 700 y Black 900
  (títulos). No usar otras familias.
- **Lema del capítulo:** «La noche construye territorio». Puede usarse en portada y cierre.
- **Logo:** usar el SVG oficial del capítulo tal cual. Prohibido recolorearlo, deformarlo,
  reorganizar sus elementos o aplicarle transparencias.
- **Código de color por rol** (mantenerlo consistente en toda la pieza):
  - Dirección (súper admin) → Pub Red `#EE4137`
  - Secretaría (admin) → Ambient Rose `#EA698B`
  - Empresario (asociado) → Ambient Purple `#C05299`
  - Fundamentos → gris neutro · Sitio público → un acento frío (verde agua)
- Estados del contenido, siempre con el mismo semáforo: Borrador = gris,
  Pendiente de aprobación = ámbar, Publicado/Aprobada = verde, Rechazada/mora = rojo.

### 2 · Contexto del producto

La plataforma tiene **tres puertas**:

1. **Sitio público** (`/`) — lo ve cualquiera: directorio de establecimientos, guía
   normativa «Abre tu negocio», bolsa de empleo, artistas, proveedores, eventos,
   boletín, afiliación y contacto.
2. **Panel del gremio** (`/admin`) — la oficina digital donde Dirección y Secretaría
   administran todo (construido con Filament; estética de panel de administración).
3. **Mi cuenta** (`/mi-cuenta`) — portal privado del empresario afiliado: estado de
   cuenta, pago de mensualidad, historial y convenios.

**Roles internos:** `super_admin` (Dirección), `subadmin` (Secretaría), `asociado`
(Empresario). Frontera clave del sistema: **publicar es un permiso distinto de editar**.
La Secretaría redacta; solo la Dirección publica. El empresario no entra al panel.

**Credenciales del demo** (pueden mostrarse en una lámina):
`direccion@asobaresquindio.test`, `oficina@asobaresquindio.test`,
`asociado@asobaresquindio.test` — contraseña `Asobares2026*`. El usuario asociado está
vinculado a «Bruma Gastrobar», con 3 meses de mora (guion del demo de pago).

### 3 · Estructura pedida — 5 módulos, ~32 pantallas

Respeta este orden y contenido. Cada pantalla: un titular fuerte, poco texto en pantalla,
y el detalle en notas del orador o capas expandibles. Usa maquetas simplificadas de la
interfaz (mockups) donde se indica.

**MÓDULO F · FUNDAMENTOS (5 pantallas)**

1. **Portada.** Logo, lema, título «Tutorial de la plataforma», chips de los tres roles.
2. **Las tres puertas.** Diagrama de `/`, `/admin`, `/mi-cuenta` (contenido del punto 2).
   Regla de oro: cada rol entra solo por su puerta; si un empresario intenta `/admin` el
   sistema lo bloquea; si el equipo entra por `/mi-cuenta/entrar` recibe: «Esta entrada es
   para establecimientos afiliados. El equipo del gremio ingresa por /admin».
3. **Matriz de roles.** Tabla comparativa: entrar al panel (D✓ S✓ E✗) · ver/crear/editar
   contenido (D✓ S✓ E✗) · publicar (solo D; a S "le queda pendiente") · eliminar (solo D) ·
   bandejas (D✓ S✓ sin eliminar) · cartera e importación CSV (solo D; E solo la suya) ·
   transacciones (solo D; E las suyas) · usuarios/ajustes/bitácora (solo D) ·
   portal mi-cuenta (solo E).
4. **El flujo editorial** (animar este diagrama): Borrador → (la Secretaría guarda) →
   Pendiente de aprobación → (la Dirección aprueba) → Publicado. Al guardar: si quien
   guarda puede publicar, su elección se respeta; si no, el sistema baja el estado a
   Pendiente automáticamente — la regla vive en el modelo, no en el formulario, y no se
   puede burlar manipulando la página. Cada envío notifica a la campana de todos los
   súper admins: «[Nombre] envió “[Título]” para revisión», con botón **Revisar**;
   la campana se refresca cada 30 s. La Dirección también puede devolver a Borrador
   (sale del sitio público). Aplica a 9 tipos: asociados, eventos, boletín, guía
   normativa, iniciativas, vacantes, artistas, proveedores, aliados.
5. **Seguridad y demo.** Credenciales (arriba). MFA opcional por usuario: app de
   autenticación (8 códigos de recuperación) o código al correo. Bitácora de auditoría.
   Tres candados: «la interfaz esconde, la política impide, el sistema garantiza».
   Formularios públicos: máx. 6 envíos/min; login de mi-cuenta: 5 intentos/min.
   Buscadores no indexan /admin, /mi-cuenta ni páginas de pago.

**MÓDULO A · DIRECCIÓN — SÚPER ADMIN (14 pantallas, acento rojo)**

6. **Entrar y proteger la cuenta.** `/admin`, correo+contraseña, MFA si está activa.
   Desde el perfil: cambiar clave, activar MFA (guardar los 8 códigos). Recomendación:
   la cuenta de la Dirección es la más valiosa — activar MFA cuanto antes.
7. **Dashboard «El gremio hoy»**, 6 indicadores: Asociados publicados (+ municipios con
   presencia) · Afiliados en mora (+ $ por recaudar, rojo si hay) · Recaudado este mes
   (+ nº transacciones aprobadas) · Mensajes nuevos (+ PQR sin responder) · Aspirantes de
   la semana · Inscripciones de la semana. Widgets: Inscripciones del mes, Asociados por
   municipio, Últimas transacciones (solo Dirección).
8. **La campana de pendientes** (mockup de notificación con botón Revisar). Flujo:
   campana → Revisar → editar si hace falta → decidir. Alternativa: filtro
   Estado → Pendiente de aprobación en cualquier listado.
9. **Aprobar y devolver.** Acción «Aprobar y publicar» (modal: «Publicar este contenido —
   Quedará visible en el sitio público de inmediato» / botón «Sí, publicar»). Acción
   «Devolver a borrador» (modal: «Sale del sitio público y vuelve a quedar en edición»).
   Aprobación en lote con casillas; el sistema re-verifica permiso registro por registro.
10. **Mapa del menú completo** (5 grupos): Contenido (Asociados, Eventos y capacitaciones,
    Boletín, Guía normativa, Iniciativas del gremio) · Bolsas (Bolsa de empleo, Aspirantes,
    Artistas, Proveedores) · Bandejas (Mensajes y PQR, Inscripciones) · Gremio (Aliados y
    convenios, Beneficios del afiliado, Cartera —con badge rojo del nº de morosos—,
    Transacciones) · Configuración plegado (Municipios, Categorías, Usuarios, Ajustes del
    sitio, Bitácora). Nota: la Secretaría no ve Cartera, Transacciones, Usuarios, Ajustes
    ni Bitácora, y no puede eliminar nada.
11. **Ficha del asociado — 6 secciones:** ① Identificación: nombre (el slug/URL
    `/directorio/…` se genera solo), categoría y municipio obligatorios con buscador,
    reseña hasta 2000 caracteres. ② Contacto público — texto literal del formulario:
    «El propietario decide qué datos suyos se publican: deja en blanco lo que no quiera
    mostrar» — dirección, horario, WhatsApp, sitio web, Instagram, Google Maps/Business,
    TripAdvisor. ③ Ubicación: lat/lng copiables de Google Maps para el pin. ④ Imágenes:
    portada JPG/PNG/WebP máx. 5 MB; galería múltiple reordenable, conversión automática
    a WebP. ⑤ Publicación: estado + interruptor «Destacar en el inicio» (franja de
    destacados de la portada). ⑥ Datos internos 🔒 «Uso exclusivo de la oficina. Nada de
    esta sección sale al sitio público»: representante, correo y teléfono internos, fecha
    de afiliación, notas. Advertencia: el nombre debe coincidir con el que usa la
    contadora (el CSV de cartera cruza por nombre).
12. **Resto de Contenido.** Eventos: título, tipo evento/capacitación, lugar, fechas,
    cupos, precio (0 = gratuito), interruptor «permite inscripción», enlace externo para
    eventos de la Nacional; si es de pago, la inscripción solo se confirma con pago
    aprobado. Boletín: título, extracto, contenido, imagen, fecha; categorías Noticia /
    Observatorio económico / Próximos proyectos; actualización ~mensual con datos de la
    Nacional. Guía normativa: un registro = un requisito de una entidad en un municipio
    (entidad, descripción, checklist, enlace, formato adjunto descargable con nombre
    limpio, costo aproximado, orden); en público solo aparecen municipios con requisitos
    publicados y se muestra el costo total. Iniciativas: nombre, resumen, descripción,
    lugar, orden; línea Seguridad/Cultura/Sostenibilidad; estado En formulación /
    Escalando / En ejecución.
13. **Bolsas.** Vacantes: solo asociados publican (cada vacante va a nombre de uno);
    cargo, tipo tiempo completo/por turnos, franja horaria, WhatsApp de contacto; el muro
    público filtra por cargo y municipio. Aspirantes: se registran solos desde /empleo
    (nombre, correo, teléfono, cargo de interés, experiencia) con consentimiento Habeas
    Data fechado. Artistas: tipo DJ/banda/solista/otro, género musical, tarifa desde,
    video de YouTube incrustado, WhatsApp, Instagram, foto, municipio. Proveedores:
    categoría hielo/licores/alimentos/aseo/seguridad/mantenimiento/otros, WhatsApp,
    correo, municipio y fecha «visible hasta» (palanca del cobro por aparecer).
14. **Bandejas.** Mensajes: tipos Contacto general / Solicitud de afiliación / PQR /
    Quiero ser aliado / Quiero aparecer en la bolsa de proveedores; estados Nuevo (rojo),
    En trámite (ámbar), Respondido (verde); las PQR generan radicado automático copiable
    y acuse por correo al remitente. Acción «Marcar respondido»: exige la nota «¿Qué se
    respondió?» (constancia interna); en PQR el modal dice «Cerrar [radicado]».
    Inscripciones: Registrada (basta para eventos gratis) → Confirmada (solo la dispara
    el pago aprobado; nunca confirmar a mano una inscripción paga).
15. **Cartera — el CSV de la contadora.** La cartera no se edita a mano: entra por CSV y
    se salda con pagos aprobados. Pasos: Gremio → Cartera → «Descargar plantilla» (CSV
    con datos actuales) → «Importar CSV de la contadora». Columnas: `establecimiento`,
    `saldo_pendiente`, `meses_mora`, opcional `ultimo_pago`. Tolerancias: encabezados con
    mayúsculas/tildes/espacios; números colombianos (`1.250.000`, `$`); fechas
    `AAAA-MM-DD` o `DD/MM/AAAA`. Nunca aborta por una fila mala: procesa lo válido y
    muestra hasta 10 errores con nº de fila («No existe un asociado llamado “X”»…).
    Tabla: mora Al día (verde) / 1–2 meses (ámbar) / 3+ (rojo), saldo con total sumado,
    último pago, actualizado hace X, filtro «Solo en mora». Badge rojo en el menú.
16. **Transacciones (solo lectura).** Referencia única, concepto Afiliación / Inscripción
    a evento / Mensualidad, monto COP, método PSE/tarjeta/otro, estado Pendiente/
    Aprobada/Rechazada. Efectos de un pago aprobado: mensualidad → cartera al día;
    evento → inscripción confirmada; idempotente (reintentos de la pasarela no duplican).
    En demo: pasarela simulada con la marca del gremio (aprobar/rechazar a mano) que
    recorre el mismo camino que Bold (preferencia PSE) en producción.
17. **Usuarios.** Solo la Dirección. Crear subadmin: nombre, correo, clave temporal, rol
    subadmin, pedir cambio de clave y MFA al primer ingreso. Crear empresario: primero
    debe existir su establecimiento en Asociados; crear usuario con el correo del
    propietario, **vincularlo al asociado** (sin vínculo, mi-cuenta dice «Tu usuario
    todavía no está vinculado a un establecimiento»), rol asociado; entra por
    /mi-cuenta/entrar, nunca por /admin.
18. **Ajustes del sitio.** 12 grupos de textos editables sin código: Identidad ·
    Página de inicio · Manifiesto del gremio · Cifras del Observatorio · Quiénes somos ·
    Contacto · Guía normativa · Bolsa de empleo · Artistas y proveedores · Boletín ·
    Afiliación · Legal. Al guardar: «Los cambios ya se ven en el sitio público».
19. **Bitácora.** Frases en español sin jerga: «Natalia actualizó el asociado La Cava del
    Yipao — hace 2 horas». Columnas: cuándo (relativo, exacto en tooltip), qué pasó, tipo,
    campos modificados. Filtros: tipo de contenido, usuario, últimos 7 días. Si algo fue
    eliminado, conserva el nombre de la instantánea.

**MÓDULO B · SECRETARÍA — ADMIN (6 pantallas, acento rosa)**

20. **Qué ves y qué no.** Ve: Contenido completo, Bolsas, Bandejas, Aliados, Beneficios,
    Municipios y Categorías, dashboard sin widget de dinero. No ve (deliberado): Cartera y
    Transacciones (el dinero es de la Dirección), Usuarios (las llaves las da la
    Dirección), Ajustes (el discurso institucional lo firma la Dirección), Bitácora; y
    ningún botón de eliminar. Su perfil sí es suyo: clave y MFA.
21. **Ciclo de trabajo.** Redactar en Borrador (nada es público) → guardar → el sistema
    convierte «Publicado» en «Pendiente de aprobación» (el formulario lo avisa: «Al
    guardar, el contenido quedará pendiente de aprobación de la dirección») → la
    Dirección recibe la notificación con nombre y título → aprueba (sale al aire) o
    devuelve a Borrador (ajustar y reenviar). Sin atajos posibles.
22. **Paso a paso: cargar un establecimiento** (versión operativa de la pantalla 11,
    en 7 pasos, terminando en «queda Pendiente de aprobación y la Dirección ya fue
    notificada»).
23. **Resto del contenido que redacta** (resumen de eventos —solo eventos del gremio—,
    boletín, guía normativa como producto insignia, vacantes/artistas/proveedores/aliados
    con nota: el «detalle del convenio» de los aliados es privado, solo se ve en
    mi-cuenta). Alerta: Municipios, Categorías y Beneficios son catálogos SIN flujo
    editorial — quedan vivos al guardar.
24. **Las bandejas sí son suyas** de principio a fin: atender Nuevos a diario, responder
    por el canal que sea, «Marcar respondido» con nota obligatoria; PQR con radicado;
    no confirmar a mano inscripciones pagas; contactar aspirantes cuando un asociado
    pida un cargo.
25. **Su frontera en una tabla:** redactar ✓ · publicar ✗ (envía a revisión) ·
    despublicar ✗ (pedir a Dirección) · eliminar ✗ (pedir a Dirección) · bandejas ✓ ·
    catálogos ✓ (efecto inmediato) · ver cartera ✗ · crear usuarios ✗.

**MÓDULO C · EMPRESARIO — ASOCIADO (4 pantallas, acento púrpura)**

26. **Tu cuenta.** La oficina crea el usuario y lo vincula al establecimiento (sin
    autorregistro); primera entrada → cambiar clave. Puerta: `/mi-cuenta/entrar` (login
    oscuro propio, con «recordarme»). /admin no es su puerta. Mensajes literales del
    sistema para errores (vínculo faltante, cuenta del equipo).
27. **Su pantalla, sección por sección** (mockup): saludo con nombre, establecimiento,
    categoría · municipio · «Afiliado desde…», botón Cerrar sesión. Estado de cuenta:
    verde «Estás al día» (+ fecha del último pago) o rojo «Debes N meses» + saldo exacto
    + botón «Pagar ahora» (PSE o tarjeta). Siempre: «Información actualizada hace X. Si
    no coincide con tus registros, escríbenos a [correo]». Últimos 5 movimientos
    (referencia, fecha, monto, estado). Convenios vigentes: condiciones privadas de los
    aliados, solo para afiliados.
28. **Pagar la mensualidad en 4 pasos:** Pagar ahora → pasarela (PSE recomendado o
    tarjeta) → pantalla de resultado → si aprobado, cartera al día automáticamente; si
    rechazado, la deuda sigue y puede reintentar. Si ya estaba al día: «Tu cuenta ya está
    al día» (no cobra doble). En demo: pasarela simulada, mismo camino que Bold.
29. **Su presencia pública.** Su página `/directorio/su-slug` con reseña, fotos, contacto,
    mapa y enlaces. Él decide qué se publica (lo que no quiera mostrar se deja en blanco).
    Cambios: se piden a la oficina y la Dirección los aprueba. También puede pedir:
    publicar vacantes a nombre de su establecimiento, salir destacado en la portada,
    e inscribirse a eventos del gremio (pagando en línea si son de pago). Cerrar sesión
    al terminar en computadores compartidos.

**MÓDULO D · SITIO PÚBLICO + CIERRE (3 pantallas)**

30. **Recorrido público:** Inicio (hero, cifras del Observatorio, destacados, manifiesto) ·
    Directorio con filtros y mapa · Abre tu negocio (guía por municipio con checklist,
    costos y formatos descargables — el producto insignia) · Empleo (muro + registro de
    aspirantes) · Artistas y Proveedores · Eventos (inscripción en línea; cobro si es de
    pago) · Boletín · Afíliate (beneficios + formulario) · Contacto (tipos, PQR con
    radicado y acuse).
31. **Del panel al sitio** (tabla causa→efecto): Asociados→/directorio y destacados
    (publicado) · Guía→/abre-tu-negocio (municipio visible solo con requisitos
    publicados) · Eventos→/eventos (inscripción si la permite) · Boletín→/boletin ·
    Vacantes→/empleo (a nombre de un asociado) · Artistas/Proveedores→sus directorios
    (proveedores dentro de su «visible hasta») · Aliados→inicio + convenios privados en
    mi-cuenta · Beneficios→/afiliate (directo) · Ajustes→textos de todo el sitio
    (directo, solo Dirección). Formularios→bandejas con Habeas Data y límite de envíos.
32. **Cierre — reglas de oro por rol** (tres tarjetas con el color de cada rol):
    Dirección: revisa la campana a diario, aprueba o devuelve, importa la cartera, da y
    quita llaves; solo ella publica, elimina y ve el dinero. Secretaría: redacta sin
    miedo (nada sale sin aprobación) y las bandejas son suyas — que no se acumulen PQR.
    Empresario: entra por /mi-cuenta, mira cuánto debe, paga con PSE y queda al día al
    instante; él decide qué datos suyos se publican. Lema al cierre.

### 4 · Requisitos de forma

- **Modo claro y modo oscuro:** la pieza debe incluir un **conmutador de tema visible y
  persistente** (recordar la elección del usuario entre sesiones). El **oscuro es el
  predeterminado** — es la estética nocturna de la marca: fondo Pub Black `#0B090A`,
  texto Ambient White `#F5F3F4`. El **modo claro** invierte la base: fondo Ambient White
  `#F5F3F4`, texto Pub Black `#0B090A`, superficies blancas para las maquetas. En claro,
  los colores funcionales suben de contraste (verde `#047857`, ámbar `#b45309`, rojo
  `#dc2626`, azul `#1d4ed8`) y los acentos de rol se oscurecen (~72 % del color mezclado
  con negro) cuando se usan como texto, para que sigan siendo legibles sobre fondo claro.
  Pub Red `#EE4137` se mantiene idéntico en ambos temas como color de acción.
- **Animaciones con propósito:** el diagrama del flujo editorial (pantalla 4) y el flujo
  de pago (28) se animan por etapas; las notificaciones "llegan" a la campana (8);
  las transiciones entre módulos cambian el color de acento. Nada de animación
  decorativa que distraiga; respetar `prefers-reduced-motion` si el medio es web.
- **Mockups, no capturas:** representa la interfaz con maquetas simplificadas (tablas,
  badges, modales, notificaciones) usando la paleta; no inventes pantallas que no estén
  descritas aquí.
- **Navegación:** índice por módulos, progreso visible, saltos directos por rol. Cada
  módulo debe poder verse de forma independiente (un empresario solo verá F + C).
- **Accesibilidad:** contraste AA sobre fondo oscuro, cuerpo de texto ≥ 16 px, español
  claro sin anglicismos técnicos ("bandeja", no "inbox").
- **Entregable:** presentación completa + versión imprimible/PDF como manual de usuario.

### 5 · Prohibiciones

- No cambiar la paleta, la tipografía ni el logo. No usar degradados ajenos a la marca.
- No inventar funciones, permisos, textos de interfaz, precios ni datos.
- No mostrar contraseñas reales distintas a las del demo.
- No usar imágenes de archivo de personas; preferir iconografía y mockups.

## TERMINA EL PROMPT
