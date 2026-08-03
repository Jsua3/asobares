# Estado del proyecto — Plataforma Web Asobares Quindío
_Actualizado: 1 de agosto de 2026 · Mantener este doc al día; la investigación de fondo está en `claude/investigacion-asobares-quindio.md`_

## Qué es el proyecto
Desarrollo de la **Plataforma Web Oficial de Asobares Capítulo Quindío** en el marco de una **práctica universitaria con la Universidad Alexander von Humboldt** (Armenia). El documento rector es el "Cronograma Prácticas Asobares" firmado por Natalia Gutiérrez (directora ejecutiva) — está subido a este proyecto como PDF (`CRONOGRAMA SITIO WEB 2026.pdf` + `IMAGENES CRONOGRAMA WEB SITE.pdf`). Hay un **tutor de práctica** que revisa avances y reporta bugs. **Fecha límite dura de entrega: 22 de septiembre de 2026.**

## Enfoque estratégico (actualizado tras Reunión 2)
- Reunión 1 (Natalia): dos propósitos — (1) **visibilizar los establecimientos** y (2) **guiar a quien quiera abrir uno**.
- Reunión 2 (directivo): **re-ponderación**. Aprox. textual: «Yo no creo que Asobares sea una plataforma de comercialización de bares… no creo que la fuerza de mercadeo de los bares se haga a través de Asobares». El valor está en la **representatividad ante instituciones** y en la **guía normativa como producto insignia** («ningún gremio la tiene», «es el punto donde caen los negocios y los cierran», «haría que la página se visite»). Usuario objetivo de la guía: el dueño pequeño que «es el mismo dueño, el bartender y el que hace todo». Escepticismo explícito con noticias/boletines como sección viva («anticipándome un poco: eso va a fracasar»); contenido editorial mínimo, actualización ~mensual con datos que envía la Nacional.
- **Pendiente: validar este giro con Natalia** (viernes) y reflejarlo en el requisitos v2.

## Equipo y roles (v3 — vigente)
- **Juan José (Sua)** — Frente A: arquitectura, repositorio GitHub, hosting/BD/SSL/dominio, **pasarela de pagos**, **panel CMS**, SEO técnico, documentación técnica. Herramienta clave: Claude MAX.
- **Compañera** — Frente B: maquetación mobile-first de secciones públicas, **directorio de asociados** (filtros por municipio), **módulo capacitaciones/eventos** (formularios con Habeas Data), optimización de imágenes (.webp/.svg), matriz de pruebas y manual de usuario. También desarrolla (mismo rol que Juan José, frentes distintos).
- **Los insumos visuales y de contenido los entrega la empresa** (textos, fotos, logos, datos de asociados) — el equipo los solicita e integra, no los produce. Hay tabla de insumos con fechas en el Plan v3.
- Trabajo por ramas con revisión cruzada en GitHub; commits descriptivos mínimo semanales (requisito del cronograma).

## Alcance obligatorio (del cronograma oficial firmado)
1. Sitio institucional (quiénes somos, beneficios, aliados con carrusel, enlace a Asobares Nacional).
2. **Directorio dinámico de asociados** con filtros por municipio (Armenia, Salento, Filandia, etc.) y mapas.
3. **Módulo de capacitaciones y eventos** con calendario e inscripciones.
4. **Pasarela de pagos** para cuotas de afiliación y eventos — primero en sandbox, nada de dinero real hasta validar.
5. **Panel CMS**: el personal debe crear/editar/eliminar contenido **sin código** ("no se aceptarán secciones clave" quemadas).
Requisitos transversales: mobile-first (>80% usuarios móviles; diseñar y probar primero en celular), SSL/HTTPS, Habeas Data (Ley 1581/2012) en formularios, imágenes .webp/.svg, carga <2,5 s, SEO para "Asobares Quindío" y "bares en Armenia y Quindío", GitHub con commits semanales.
Entregables finales: manual de usuario (PDF o video), documentación técnica (credenciales, estructura BD, tecnologías), código fuente + BD exportada, capacitación al personal.

## Alcance ampliado pedido en Reunión 2 (⚠️ NO está en el cronograma firmado — requiere requisitos v2 con priorización y doble firma)
- **Bolsa de empleo** del sector (administrador, chef, bartender, meseros): tipo «muro»; **solo asociados publican** vacantes; buscadores se registran; tiempo completo o por franjas. Contexto: «conseguir bartenders acá es lo más difícil».
- **Directorio de artistas** (separado de empleo: «el DJ es artista, el mesero es empleo»): ficha con género musical, contacto, **tarifas** y **video embebido**.
- **Bolsa de proveedores** (hielo, licor, comida, aseo, seguridad, mantenimiento): **monetizable — cobrar por entrar a la base de datos** («no cobrar poquito… es para que les salga trabajo»). Distinguir aliados (convenio) de proveedores (pagan).
- **Estado de cartera del asociado**: login → «debes X meses → paga aquí» + recordatorios. Motivo: «la gente no paga porque no sabe cuánto debe, entonces todo el mundo llama a Natalia». Los datos los tiene la **contadora** → requiere dashboard de carga o hoja vinculada. Tratar como **integración de datos, no promesa de módulo**, hasta ver el archivo real.
- **Registro/roles públicos**: proveedor, buscador de empleo, artista, asociado. Consultar bolsas es público sin registro; publicar y ver detalle de beneficios requiere cuenta. **Construir sesiones desde el día uno aunque no se usen** («dejarlo hecho así no se utilice»).
- **Beneficios con detalle solo para asociados logueados** («son convenios, no visible para todo el mundo») — estaba diferido a fase 2; el cliente lo volvió a pedir.
- Recomendación registrada en `acta-reunion-2.md` §7: MoSCoW contra el 22-sept — Must: institucional + directorio + guía normativa (Armenia primero) + formatos descargables; Should: bolsa de empleo simple; Could: artistas y proveedores (estructura lista, carga fase 2); Won't/fase 2: cartera con pagos reales, monetización, certificado automático.

## Cronograma oficial: 8 semanas, 4 fases
- **F1 (S1–S2)** Planificación y diseño → S1: textos, sitemap, **elección de stack**; S2: wireframes móvil/escritorio, paleta y tipografías. **HITO: aprobación del diseño por la directiva (fin S2)**.
- **F2 (S3–S4)** Desarrollo base → S3: proyecto, hosting pruebas, BD, menú, quiénes somos, beneficios/aliados; S4: directorio + módulo eventos.
- **F3 (S5–S6)** → S5: pasarela en sandbox; S6: panel CMS.
- **F4 (S7–S8)** → S7: pruebas globales y corrección de bugs del tutor; S8: dominio + SSL, capacitación, entrega de documentación.
- Nota de riesgo: con fecha límite dura conviene **invertir S5↔S6** (panel CMS antes que pasarela; la pasarela se demuestra en sandbox).
- **Posición actual (1 ago)**: S1 = 27–31 jul (levantamiento hecho + Reuniones 1 y 2). **S2 = 3–7 ago** → objetivos en `Avances y Objetivos - Semana 3 al 7 de agosto`: (1) aprobar requisitos —ahora **v2** con lo de Reunión 2—, (2) definir stack + presupuesto con la junta, (3) wireframes móvil/escritorio, (4) repo GitHub con ramas, (5) insumos críticos, (6) académico: retroalimentación + cap. 5. **Nuevo: trabajo presencial los miércoles** en el establecimiento del directivo (horario por confirmar; se mencionó «¿de dos a ocho?»). Próximas citas: **miércoles 5 ago (presencial, confirmar hora)** y **viernes 7 ago, 9:00 a. m.** con Natalia.

## Acuerdos y datos clave de la Reunión 1 (28 jul; `transcripcion-reunion-1.md`)
⚠️ Transcripción automática (Whisper small): confunde nombres y cifras — contrastar antes de usar como fuente oficial.
- **Seguimiento**: reunión presencial **todos los viernes 9:00 a. m.** con la dirección ejecutiva (lugar se confirma cada semana); trabajo por objetivos, no por horario; se firman horas y el acuerdo de prácticas. Entregables/avances semanales.
- **Fin de la práctica**: ~**21–22 de septiembre de 2026** ("dos meses exactos") — verificar fecha exacta.
- **Lanzamiento**: la dirección quiere que **los practicantes presenten la página en el evento del gremio** (premios/Expobar). La transcripción mezcla dos fechas (24 sept / 26 nov) — **sigue sin confirmarse** (no se tocó en Reunión 2).
- **Stack (insumo)**: WordPress última opción para el equipo; Juan propuso despliegue propio (AWS/Google) con dominio. Condiciones de Natalia: presupuesto razonable (mensual ok, aprueba la junta), autonomía total de edición tras capacitación, facilidad para no técnicos.
- **Contenido**: Natalia tiene los textos de "quiénes somos" (TED gremial + plan estratégico, ambos en este proyecto). Referente de diseño: sitio de la Cámara de Comercio de Armenia; Alcaldía/Gobernación le parecen confusos. El gremio tiene diseñador propio y apoyo de marketing (Zasca).
- **Certificado de afiliación**: hoy Natalia lo hace a mano; la generación automática le entusiasmó — fase 2, prioridad de esa fase.

## Acuerdos y datos clave de la Reunión 2 (31 jul, 2:00 p. m., Discoteca El Balcón de la 14; `transcripcion-reunion-2.md` + `acta-reunion-2.md`)
⚠️ La grabación propia (teléfono en bolsillo) fue irrecuperable; la transcripción sale de 3 audios de la compañera (Whisper small). Asistió un **directivo del capítulo (presumiblemente Jorge Iván Botero, presidente — confirmar nombre/cargo)**.
- **Giro estratégico** (ver sección Enfoque): representatividad + guía normativa como producto insignia; escepticismo con noticias/boletines.
- **Prioridades top 3 del directivo** (respuesta a «¿las 3 más importantes si no alcanzamos todo?»): **(1) bolsa de empleo, (2) guía de requisitos con documentos descargables, (3) directorio de asociados**. Ratificar con Natalia y formalizar en requisitos v2 (la bolsa de empleo ni siquiera está en el alcance firmado).
- **Guía normativa: separada por municipio** («es diferente»; ej.: certificado de bomberos ~$100.000 en un municipio). Formatos **descargables** (bomberos, policía) con **checklist** por trámite; debe verse formal («un Google Docs ahí es súper feo»). Podría llevar logos de las alcaldías. Detalle real: el dueño debe **enviar carta a Bomberos para pedir la visita**, y la Policía exige el certificado (o el radicado) en las visitas.
- **Natalia compila los requisitos de Armenia**: ya pide checklists oficiales a cada secretaría (**Bomberos y Salud ya los envían**); línea directa con los secretarios («ustedes me dicen "para tal día necesito X" y yo la consigo»). Entidades citadas: Secretarías de Salud, Gobierno y Planeación, Bomberos, derechos de autor (Sayco/OSA).
- **Directorio**: ficha por establecimiento con «reseñita», contacto, teléfono, dirección + **enlaces a Google Business y TripAdvisor** (se habló de reclamar perfiles de Google no reclamados). **Quién decide qué se publica: el propietario del establecimiento**, no Asobares.
- **Eventos: solo eventos de Asobares** (ExpoBar, Congreso Nacional), no los de cada bar; lo no-local se enlaza al registro de la Nacional. **Observatorio/datos: los envía Asobares Colombia**, actualización al menos mensual.
- **Pagos**: Bold para **certificados y mensualidad tipo suscripción** ligada a cada usuario; preferencia fuerte por **PSE** («un PSE sería fantástico»); cuenta del gremio en **Itaú**; la gente quiere «transferir fácil desde el celular». El equipo mostró una **simulación de Bold funcionando**.
- **Logística**: invitación a **trabajar los miércoles en el sitio** («la reunión es de ustedes… yo me quedo acá y pueden preguntar»). El directivo tiene otros practicantes en sus establecimientos; elogió el avance («segunda semana y ya está con la marca, todo implementado — brutal»). Se explicó el kit SVG (editable pero no debe modificarse) y el trade-off WordPress vs. código propio (AWS/Google Cloud); quedó planteado un **prototipo**.
- **Compromiso de cierre de Juan**: actualizar el **documento de requisitos a v2** con lo nuevo y «dejar completamente claros los requisitos y ya no cambiarlos» (congelamiento tras doble firma).

## Decisiones tomadas
- **Pasarela de pagos: Bold, ya contratada por el gremio**. Integración al final con los datos bancarios; la dirección pide claridad jurídica. Pendientes los documentos (RUT, cámara de comercio, cuenta **Itaú**). Preferencia de método: **PSE**.
- **Guía normativa por municipio** (no generalizada) con formatos descargables y checklist.
- **El propietario del establecimiento decide** qué información suya se publica.
- **Eventos solo del gremio**; contenido editorial mínimo, actualización ~mensual con insumos de la Nacional.
- **Sesiones/registro se construyen desde el inicio** aunque no se usen el día uno.
- Diseño base: **aprobado por la jefe** — landing oscura (negro + rojo #EE4036) con logo oficial. `index.html` v1 existe, **auditada y corregida para móvil** (breakpoint 600px, tap targets, scroll lock del menú, `aria-expanded`, `prefers-reduced-motion`).
- Roles v3 acordados. Logo extraído del membrete (rojo + blanca, kit en zip); rojo oficial **#EE4036**.
- **Documento de levantamiento de requisitos v1 producido**: 40 RF + 12 RNF priorizados, 14 preguntas abiertas, bloque de doble firma. → Varias preguntas quedaron respondidas en Reunión 2 (ver acta §3); pasar a **v2**.

## Decisiones pendientes
- **Stack** (objetivo n.º 2 de la S2, con el docente y la junta): finalistas **WordPress** vs **Laravel + Filament**. Reuniones 1 y 2 inclinan a código propio (el equipo explicó al directivo las limitaciones de plantillas), sujeto a presupuesto. Astro+Strapi descartado salvo experiencia Node.
- **Dominio** (asobaresquindio.com / .com.co ¿o subdominio de asobares.org?) y **quién lo paga / titularidad** (debe quedar a nombre del gremio con correo institucional). Nada registrado aún.
- **Hosting de producción** (presupuesto: ~COP 300–700 mil/año según stack; pago mensual aceptable). **Monto y aprobación de junta sin resolver** (no se tocó en Reunión 2).
- **Fecha oficial de lanzamiento** (24 sept vs 26 nov / evento del gremio) — sin resolver.
- **Entrega de datos de los ~60 asociados** (archivo, formato, fecha) y **autorizaciones de publicación** (habeas data) — sin resolver.
- **Claude Max**: el pedido de patrocinio no aparece en los audios de la Reunión 2; retomar con Natalia el viernes.
- **Horario de los miércoles presenciales** y **tutor empresarial** (nombre, cargo, correo) + firmas del planeador.

## Entregables académicos (práctica CUE — actualizado 31 jul 2026)
- Datos: **primera práctica**, área núcleo **Desarrollo de software**, semestre **cuarto**. Docente asesor: **César Augusto Granada Muñoz**. Tutor empresarial: **pendiente de asignación**.
- **Planeador FO-DO-100**: diligenciado el 30 jul con el proyecto ASOBARES y ajustado con la Reunión 1; faltan tutor empresarial, firmas y No. de proyecto. Se envía al docente con asunto **"PLANEADOR - Nombre del estudiante - Semana 1"**.
- **Proyecto de práctica (GU-DO-007 v7)**: borrador con portada, tabla de contenido, caps 1–4 y bibliografía APA (11 fuentes), ajustado con la Reunión 1; entrega del 31 jul (caps 1–3 + opcional 4) cubierta. Caps 5 y 6 se redactan durante la práctica según los 6 objetivos específicos. PDF final a **proyectosing@cue.edu.co**.

## Archivos producidos
- `acta-reunion-2.md` — **síntesis oficial de la Reunión 2**: giro estratégico, alcance nuevo, respuestas, compromisos, pendientes y recomendación MoSCoW (1 ago).
- `transcripcion-reunion-2.md` — transcripción de los 3 audios de la compañera en orden cronológico verificado + fragmentos rescatados de la grabación propia (1 ago).
- `guion-reunion-2-asobares.html` — formulario interactivo de preguntas usado para preparar la Reunión 2 (con guion del pedido Claude Max).
- `Avances y Objetivos - Semana 3 al 7 de agosto - Proyecto Web ASOBARES.docx` — avances S1 + 6 objetivos de la S2 (31 jul).
- Documento de **levantamiento de requisitos v1** (40 RF, 12 RNF, 14 preguntas, doble firma) → actualizar a v2.
- `FO-DO-100 Planeador de Practica - Juan Jose Sua.xlsx` — planeador oficial (30 jul).
- `Proyecto de Practica - Plataforma Web ASOBARES Quindio - Juan Jose Sua.docx` — borrador GU-DO-007 caps 1–4 (30 jul).
- `index.html` — landing v1 (self-contained, logo y rojo oficiales), **corregida tras auditoría móvil con Playwright**.
- `kit-marca-asobares-quindio.zip` — logo rojo/blanca + @2x + monograma + logo Nightlife.
- `Plan-de-trabajo-v3-8-semanas.docx` — plan vigente (frentes A/B, checklist, insumos, riesgos).
- `Comparativa-Stacks-Asobares-Quindio.docx` — comparativa con costos y preguntas para el tutor.
- (Obsoletos: planes v1 y v2.)

## Notas para futuras conversaciones
- **Protocolo de grabación de reuniones**: teléfono sobre la mesa, boca arriba, micrófono hacia los participantes — **nunca en el bolsillo** (la grabación propia de la Reunión 2 se perdió así: sin energía sobre ~3.400 Hz, consonantes irrecuperables incluso con EQ y modelo mediano). Redundancia: que la compañera también grabe.
- El botón "Afíliate" de la v1 apunta a WhatsApp 321 5549513; el proceso oficial de afiliación está por definir.
- La noticia "Armenia sede de Expobar 2026" (armenia.gov.co) está caída (404): conseguir enlace o soporte antes de publicarla.
- El enlace de la bio de Instagram es del Foro Quindío Nocturno (11 nov) — evento, no formulario general de afiliación.
- Cifras del sitio: usar solo las del Panorama marzo 2026 citando "Observatorio Económico de Asobares".
- Confirmar por escrito nombre y cargo del directivo de la Reunión 2 antes de citarlo en documentos formales.
