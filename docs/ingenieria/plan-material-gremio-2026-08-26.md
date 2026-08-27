# Material del gremio recibido el 26 de agosto de 2026 — inventario y plan de implementación en la plataforma

_Plataforma Web ASOBARES Capítulo Quindío · redactado el miércoles 26 de agosto de 2026 (semana 5 del cronograma) sobre la carpeta `material/nuevomaterial/` del repositorio y el estado técnico verificado del 25 de agosto. Todas las cifras de este documento salen de leer los archivos (conteos con `openpyxl`, `python-docx`, `pdfinfo`/`pdftotext`, `PIL`); las estimaciones de esfuerzo están marcadas como tales._

---

## 0. Resumen

El gremio entregó diez archivos y una carpeta con veinte fotografías. De todo eso, **tres cosas ya las teníamos** (el manual de marca, el documento de requisitos de apertura de Armenia y la base de 41 establecimientos cargada el 23 de agosto: los tres son copias idénticas byte a byte de lo que ya estaba en `material/`). Lo **nuevo de verdad** son siete insumos:

1. Una **versión más reciente de la base de establecimientos** (48 filas: 8 fichas nuevas, 1 que desaparece, 6 con cambios, 7 que traen solo el nombre).
2. El **formulario oficial «Registro de Establecimientos»** con el que el capítulo afilia y actualiza (8 secciones, unos 80 campos, la cuota, los anexos exigidos y la cláusula de datos).
3. El **portafolio nacional de beneficios y aliados** (31 láminas, 2024): resuelve en buena parte el insumo P-09 que llevaba semanas pendiente.
4. La **plantilla real del certificado de afiliación** que expide la dirección.
5. La **lista de chequeo de funcionamiento** (20 documentos, Ley 1801 y «decreto 119») que el gremio usa en visitas.
6. El **boletín nacional sobre la reforma laboral** (Ley 2466 de 2025).
7. **Diecinueve fotografías distintas** de la gestión gremial 2024-2025 (Expobar, Famtrip, ANATO, Alcaldía, Gobernación, junta directiva, etc.).

Nada de esto exige rehacer módulos: la plataforma ya tiene dónde poner cada cosa (directorio, afiliación, beneficios y aliados, guía normativa, boletín, «Quiénes somos», iniciativas). El trabajo es de **datos, contenido y confirmaciones**, más dos ampliaciones pequeñas que conviene registrar por escrito antes de construirlas (formulario oficial en línea y certificado de afiliación desde el portal). Lo primero, hoy mismo, es **proteger la carpeta**: contiene datos personales de unas 48 personas y el repositorio es público.

---

## 1. Acciones inmediatas (hoy, 26 de agosto)

| # | Acción | Por qué | Quién |
|---|---|---|---|
| 1 | Añadir `/material/nuevomaterial/` al `.gitignore` **antes de cualquier `git add`** | La carpeta estaba sin versionar (`??` en `git status`) y el `.gitignore` solo excluía `/material/*.xlsx` en el primer nivel. Contiene nombres, cédulas/NIT, correos y celulares personales de los propietarios, el certificado de un afiliado con su NIT, fotos de personas identificables y un PDF de 47 MB. El repositorio `Jsua3/asobares` es público. Nada de esta carpeta debe entrar al repositorio: los datos entran por el importador, el contenido confirmado por sembradores y las fotos por `storage`/medios. | **Hecho** en esta sesión (ver §9); falta confirmarlo en el próximo commit |
| 2 | Borrar los `.git/index.lock.huerfano-*` (tres archivos vacíos) | Los deja cualquier `git status` ejecutado desde Cowork, que no puede borrar archivos; el de hoy ya se renombró para desbloquear git. Regla para Cowork: usar `GIT_OPTIONAL_LOCKS=0 git status`. | Sua (cuando quiera; no bloquea) |
| 3 | Registrar los insumos en `claude/estado-unificado.md` (§4.6) y en el prompt maestro | Para que el estado del proyecto y el documento de práctica citen lo recibido con fecha | Sua (Project actualizado en esta sesión) |
| 4 | Enviar a Natalia la lista de confirmaciones del §6 | Casi todo el plan depende de doce respuestas suyas, y la semana que viene es la del documento de práctica | Sua |

---

## 2. Inventario del material

| Archivo | Qué es (verificado) | Frente a lo que ya teníamos | Destino en la plataforma |
|---|---|---|---|
| `Asobares Quindio - Base de datos.xlsx` (52 KB) | Hoja «Base de Datos 2025», 14 columnas, **48 filas** con nombre (47 nombres distintos: «El Cantinazo» aparece dos veces). Columnas públicas: nombre, descripción, dirección, municipio, horario, género musical, servicios, Instagram. Columnas internas: nombre del propietario, NIT/cédula, teléfono, correo, menciones adicionales (posicionamiento en redes/Tripadvisor). | **Versión nueva** de la base cargada el 23 ago. Respecto a ella: **+8 fichas** (una es el establecimiento del presidente del capítulo), **−1** (una de Circasia), **6 con cambios** (tres correos nuevos, una dirección corregida, una ficha que pierde NIT y dirección), **7 filas con solo el nombre y sin municipio** (el importador las rechaza porque el municipio es obligatorio). Completitud: descripción 40/48, NIT 40, dirección 40, municipio 41, teléfono 40, correo 31, horario 38, género 39, servicios 39, Instagram 40, menciones 28. Municipios: Armenia 29, La Tebaida 4, Salento 2, Calarcá 2, Circasia 2, Quimbaya 2 (todos en el catálogo). | Directorio de asociados (`asociados:importar`, bloque B1) |
| `Base de datos Cap. Quindio.xlsx` (50 KB) | Misma hoja, 41 filas, 6 municipios | **Es exactamente la base ya cargada el 23 de agosto** (P-06). Solo sirve para confirmar cuál de las dos es la vigente. | Ninguno |
| `Registro Establecimiento.xlsx` (57 KB) | **Formulario oficial «Registro de Establecimientos»** del capítulo (afiliación / actualización), con membrete (oficina en el piso 3 de la Cámara de Comercio, tel. 321 554 9513), 8 secciones: I datos de la empresa · II datos del establecimiento (incluye redes, sucursales, edad de empleados, aprendices SENA, parafiscales, discapacidad, local propio/arrendado, área) · III documentación (Cámara de Comercio, cédula, póliza RC, RUT, bomberos, uso del suelo, RIT, sanidad, licencia de construcción, resolución de facturación, RNT) · IV funcionamiento (actividad CIIU, **tipo de negocio**: taberna-bar, café-bar, bar-restaurante, bar, discoteca, club; **concepto musical**: rock, salsa, vallenato, crossover, mariachis, otro; música en vivo, edad de clientes, horario, cover, medios de pago, aforo, salidas, baños, plan de evacuación, CCTV, insonorización, parqueadero, seguridad) · V contactos (gerente, RR. HH., contable, capacitación) · VI temas de interés · **VII términos: cuota mensual de $30.000 los dos primeros meses y $50.000 desde el tercero**, título ejecutivo, renovación anual, estatutos · VIII anexos (RUT, certificado de existencia ≤ 1 mes, cédula, uso de suelo, pago de afiliación y primer mes) y cláusula de datos que remite a la política de `www.asobares.org`. Firma: Natalia Gutiérrez Leguizamón. | Nuevo. El formulario de `/afiliate` hoy tiene 4 campos (nombre, correo, teléfono, mensaje). | Afiliación (B2), taxonomías del directorio (B1/B2), cartera (cuota) |
| `BENEFICIOS AFILIADOS PDF.pdf` (4,8 MB) | Presentación nacional 2024, 31 láminas **sin texto extraíble** (imágenes). Seis bloques: beneficios exclusivos (presentación gremial, orientación jurídica, misiones empresariales, Nightlife International, invitación VIP a Expobar, orientación para emprendedores, capacitaciones, visibilidad de marca, inventario normativo, mesas de trabajo SENA/DANE/Viceministerio, boletines) · asesorías (jurídica, contable, licencia/uso de suelo, saneamiento, SG-SST, ruido, requisitos legales) · descuentos (derechos de autor con OSA 16,6 %/8,3 % y Sayco 6 %, manillas 5 %, exámenes médicos, planes de emergencia, insumos) · programas y estrategias (Pet Friendly, Bar Ideal, Top 100 INA, Área en Vivo al Barrio, DJs a Volar, Pregunta por Ángela, Mi Destino tu Noche, Zonas de Rumba Segura, Red de Cuidado Ciudadano, Misión HIP Madrid & Barcelona 2025, Tardeo en la Ciudad) · mesas sectoriales (CTUR, Consejo Consultivo de Turismo, Comité de Formalización Turística del MinCIT, SENA, consejos intergremiales, clústeres en cámaras de comercio) · aliados: **19 aliados estratégicos con descuento y contacto** (Conacústica, Delta Servicios, Energy Economics Experts, OSA, Sayco, Audionics, Manillas para Eventos, Smart Language Systems, Colsubsidio, Calidad de Energía, Hielo Iglú, Colmédicos, Securitas, Wiwo, Doble A, Tikipal, Manchego Álvarez, Cooserpark, Escargoth), 7 aliados nacionales de licores y cerveza (Diageo, Bavaria, Postobón, Surtiapp, Sulicor, Makro, Pernod Ricard), 8 internacionales (Nightlife International, Nightlife Iberoamérica, Hosteleo, NTIA UK, RHI USA, Spain Nightlife, Linkers, Vibelab), mapa de **17 capítulos regionales** con su correo (`asobaresquindio@asobares.org`) y fichas de presidentes (Quindío: «Jorge Iván Ángel, Presidente Capítulo Quindío»). El aliado jurídico Cortés Romero & Asociados aparece en el boletín laboral, no aquí. | Nuevo. Resuelve en parte **P-09** (lista de aliados con beneficio). Los 6 aliados del sembrador actual son ficticios. | Beneficios y aliados (B3), «Quiénes somos» (B5), iniciativas (B5) |
| `Certificado de afiliacion SALSABOR.docx` (32 KB) | Plantilla real: certifica que el establecimiento, con su NIT, «se encuentra activo como afiliado», dirección, fecha (27 de marzo de 2026), firma **«Natalia Gutiérrez, Presidente Capítulo Quindío»**, correo `asobaresquindio@asobares.org`. | Nuevo. El certificado de afiliación figura en el prompt maestro §24.5 entre lo que **no** debe asumirse como pendiente. | Portal del asociado (B7, ampliación por escrito) |
| `REQUERIMIENTOS BASICOS GENERALES - ESTABLECIMIENTO NOCTURNO.docx` (62 KB) | Carta-lista de chequeo para la visita al afiliado: **20 documentos** «según la Ley 1801 (Código Nacional de Policía) … para cumplimiento del decreto 119»: Cámara de Comercio, RUT (CIIU 5630-5611-9007-9008), bomberos, concepto sanitario, notificación de apertura a la Policía, uso de suelos, derechos de autor, plan de saneamiento, lavado de tanques, control de plagas, manipulación de alimentos, certificados médicos, RETIE-RETILAP, resolución de facturación, SG-SST, avisos (espacio libre de humo, no venta a menores, «usted está siendo grabado»), acta de propinas. | Nuevo. Complementa los 7 trámites de apertura ya transcritos: aquellos son para **abrir**; esto es para **seguir funcionando**. | Guía normativa (B4) |
| `REQUISITOS APERTURA - ARMENIA.docx` (191 KB) | Los 7 trámites de la campaña «Blindemos tu Negocio Armenia» | **Idéntico** (md5) al recibido el 20 ago y ya transcrito en `docs/ingenieria/guia-normativa-armenia-fuente-oficial.md`; Natalia los confirmó vigentes el 25 ago. | Ya en curso (Ingrid) |
| `Ley laboral.pdf` (5,5 MB) | Boletín informativo nacional del 27 de junio de 2025, 5 páginas **sin texto extraíble**: ABC de la Ley 2466 de 2025 con el aliado Cortés Romero & Asociados. Puntos: procesos disciplinarios en empresas de más de 10 trabajadores; reglamento de trabajo actualizado **antes del 25 de junio de 2026** y publicado en la web de la empresa; horas extra sin permiso previo (máx. 2 diarias/12 semanales); jornada máxima 44 h desde jul 2025 y **42 h desde jul 2026**; recargo dominical 80 % (jul 2025), **90 % (jul 2026)**, 100 % (jul 2027); **jornada nocturna desde las 7:00 pm con recargo del 35 %, vigente desde el 25 de diciembre de 2025**; contrato de aprendizaje como contrato laboral especial; seguridad social parcial para microempresas (art. 34); subsidio de hasta 25 % de 1 smmlv por nuevos empleos de mujeres, jóvenes y mayores de 50; Programa de Empleo Nocturno (PEN, parágrafo 6). | Nuevo. Hoy (ago 2026) varias de esas fechas ya pasaron: el contenido debe publicarse como **calendario vigente**, no como noticia de 2025. | Boletín (B4) |
| `Manual_de_Marca_Asobares_Colombia_pdf.pdf` (47,6 MB) | Manual nacional (Quida Studio, 2024), 20 páginas: construcción, área de respeto (la «B»), aplicación mínima (226 px / 80 mm; símbolo 28 px / 10 mm), co-branding, **Pub Red #EE4137, Ambient White #F5F3F4, Pub Black #0B090A**; secundarios Wine #A4161A, Pub Grey #282628, Ambient Rose #EA698B, Ambient Purple #C05299; **Poppins** (Light–Black) y complementarias Rage Italic / Patrick Hand solo para activaciones; escala de grises #A7A7A7; usos no permitidos (no estirar, no contorno, no inclinar, no degradados, no vertical, no solo letras, no girar el símbolo, no transparencias, no colores externos). | **Idéntico** al ya versionado en `material/`. `resources/css/tokens.css` ya implementa exactamente esta paleta (`marca-500 #ee4137`, `noche-50 #f5f3f4`, `noche-950 #0b090a`, `noche-700 #282628`, `vino`, `purpura`, `rosa`) y Poppins. | Cierra **DPV-12**; queda una auditoría de usos (B5) |
| `Apoyos visuales/` (20 JPG, 7,1 MB) | **19 fotos distintas** («Encuentro Nacional de turismo» y «encuentro turismo receptivo» son el mismo archivo). Tamaños desde 960×1280 hasta 3120×4160 y 4160×1826; 105 KB a 1,3 MB; **sin EXIF** (la fecha solo aparece en marcas de agua: Cumbre Empresarial 14/11/2024, Expobar Risaralda 21/11/2024, imagen de WhatsApp 03/03/2025). Temas: Expobar Bogotá (2), Expobar Risaralda (2), Famtrip (3), 5.º Encuentro Nacional de Turismo, ANATO, convenio con la Alcaldía, Gobernación, junta directiva, lanzamiento «Quindío Competitivo», socialización «Pregunta por Ángela», «Quindío Café y Sabor» (Acodrés), turismo con periodistas, turismo senior, Cumbre Empresarial, una imagen de WhatsApp sin título. La mayoría muestra personas identificables. | Nuevo. Hoy el sitio no tiene fotografía institucional real. | «Quiénes somos», portada, boletín, iniciativas (B6) |

---

## 3. Cruce con lo construido: qué existe y qué falta

| Bloque | Lo que ya existe en el repositorio (25 ago) | La brecha que abre el material |
|---|---|---|
| **Directorio** | `asociados` con ficha pública + campos internos (representante, documento, teléfono y correo internos, notas); `asociados:importar` con mapa de columnas tolerante a tildes y encabezados, que crea en borrador, actualiza por *slug* del nombre y **nunca publica ni otorga autorización**; frontera datos internos / ficha pública con 17 pruebas; 41 fichas en borrador. Catálogo de 8 municipios y 6 categorías (Bar, Gastrobar, Café, Discoteca, Restaurante bar, Rooftop). | Cargar la versión nueva; depurar (§ Anexo A); decidir categorías ficha a ficha (todo entró como «Bar» y hay restaurantes, licoreras, discotecas, un café y un festival); resolver dos sedes con el mismo nombre; conseguir la autorización de cada titular antes de publicar. |
| **Afiliación** | `/afiliate` con 4 campos, consentimiento con fecha y versión, mensaje tipo afiliación en el panel y botón a WhatsApp. Cartera con mensualidad de $50.000. | El formulario oficial tiene ~80 campos y define cuota, anexos y términos. Mínimo: publicarlo descargable y explicar cuota y documentos. Máximo: formulario por pasos (ampliación). |
| **Beneficios y aliados** | `aliados` (logo, url, descripción, `detalle_convenio` visible solo con sesión), `beneficios` (título, descripción, icono, orden); CRUD en el panel; 6 aliados y 5 beneficios **de demostración**. | Sustituir por los reales: 6 bloques de valor, 19 aliados estratégicos con su descuento (texto en `detalle_convenio`), más el aliado jurídico del boletín; aliados nacionales e internacionales como respaldo. Falta saber cuáles aplican en el Quindío y conseguir logos en buena resolución. |
| **Guía normativa** | `requisitos_apertura` por municipio con `checklist` JSON, adjunto descargable, vigencia y procedencia (RF-60); 7 trámites de Armenia confirmados, pendientes de sembrar. | Añadir la lista de chequeo de funcionamiento (20 ítems) como trámite descargable; en fase II, autodiagnóstico guardado en el portal. Confirmar qué es el «decreto 119». |
| **Boletín** | `noticias` con categorías `noticia`, `observatorio`, `proyecto`. | Publicar la reforma laboral como calendario vigente; probablemente añadir la categoría `normativa` (cambio de enum, Persona 1). |
| **Institucional** | `/quienes-somos` con 21 ajustes editables (historia, misión, visión, líneas, programas nacionales, dirección, presidente, directora), iniciativas sembradas (Vibrarte, Bares Verdes, Blindando tu Negocio, Noche Segura y Competitiva, Diplomado), `cifra_afiliados = 60`. Logos: `logo-asobares.svg/png`, blanco y monograma. | Textos definitivos (P-11) con el respaldo nacional real (20 capítulos), nombre y cargo del presidente confirmados, programas nacionales que sí operan aquí, cifra de afiliados coherente con la base; auditoría del logo contra el manual. |
| **Fotografía** | Ninguna foto institucional real; medios por asociado (`galeria`). | Pipeline de optimización (webp, tamaños, alt), pies de foto y fechas, autorizaciones de imagen; sección «Gestión gremial». |
| **Certificado** | No existe. Cartera sabe si el asociado está al día; la ficha tiene nombre, NIT y dirección. | Función nueva pequeña, fuera del alcance congelado: exige registro por escrito. |

---

## 4. Plan por bloques

Convenciones: **P1** = Sua (plataforma, PHP, migraciones, panel, cartera, pagos); **P2** = Ingrid (módulos públicos y su contenido); **N** = Natalia (confirmaciones, textos, autorizaciones). El esfuerzo es una estimación en horas de trabajo efectivo, no una medida.

### B1 · Base de establecimientos (P1 · esta semana)

| Tarea | Detalle | Esfuerzo |
|---|---|---|
| B1.1 Confirmar la base vigente | Preguntar a N cuál de los dos archivos manda, qué significan las 7 filas con solo nombre (¿afiliados recientes sin ficha?) y qué pasó con la ficha de Circasia que desaparece. Sin municipio no entran. | 0 (correo) |
| B1.2 Importar en local | `php artisan asociados:importar "material/nuevomaterial/Asobares Quindio - Base de datos.xlsx" --categoria=Bar`. Resultado esperado: 8 fichas nuevas en borrador, 6 actualizadas, 7 errores de fila, la ficha que desapareció **no** se borra (el importador nunca borra: decidir su estado a mano). Guardar el resumen del comando como evidencia. | 0,5 h |
| B1.3 Depurar antes de publicar | Aplicar el Anexo A (uso interno): correos inválidos, Instagram repetido en tres fichas, teléfonos repetidos, direcciones incoherentes con el municipio, dos sedes con el mismo nombre. Corregir en el panel o pedir corrección a la oficina; no inventar datos. | 2–3 h |
| B1.4 Categorías reales | Reclasificar ficha a ficha con las categorías actuales; proponer a N añadir «Licorera» (hay al menos dos) y decidir si un festival anual va en el directorio o en eventos. Alinear con el «tipo de negocio» del formulario oficial (B2.3). | 1 h + decisión N |
| B1.5 Nombres repetidos y NIT | Dos sedes con el mismo nombre chocan en el *slug* (la segunda sobrescribe a la primera). Ajustar el importador para que un nombre repetido con NIT distinto cree una segunda ficha con sufijo de sede, y normalizar el NIT (con y sin dígito de verificación) para comparar. Con prueba. | 2 h |
| B1.6 Autorización y publicación | Publicar solo con soporte firmado (el formulario oficial, sección VIII, sirve de soporte): `--autorizacion=AAAA-MM-DD --origen="Formulario de registro firmado"`. Registrar la fecha en cada ficha. | según lleguen |
| B1.7 Cifra de afiliados | El sitio dice 60; la base tiene 48 filas y 41 con datos. Poner la cifra que N confirme. | 0,1 h |

### B2 · Afiliación con el formulario oficial (P2, migraciones P1 · S6)

| Tarea | Detalle | Esfuerzo |
|---|---|---|
| B2.1 Opción A — dentro del alcance (recomendada ahora) | En `/afiliate`: explicar los pasos reales (formulario, anexos, cuota de $30.000 los dos primeros meses y $50.000 desde el tercero, renovación anual), ofrecer el formulario oficial descargable (PDF generado desde el XLSX, sin los datos de ejemplo) y mantener el registro corto actual como «pre-inscripción». Mensaje precargado de WhatsApp coherente. Mismo enlace en `/mi-cuenta` para la actualización anual. | 3–4 h |
| B2.2 Opción B — ampliación por escrito (fase II o si N la prioriza) | Formulario por pasos con las secciones I, II, IV y V (unos 30 campos útiles para el directorio y la estadística gremial) y adjuntos (RUT, Cámara, cédula, uso de suelo); crea el `Asociado` en borrador y el mensaje de afiliación; consentimiento con versión de texto. Migración nueva para los campos estadísticos (P1). | 16–24 h P1+P2 |
| B2.3 Taxonomías únicas | Decidir con N un solo catálogo de tipo de negocio (formulario: taberna-bar, café-bar, bar-restaurante, bar, discoteca, club; sitio: bar, gastrobar, café, discoteca, restaurante bar, rooftop) y normalizar `genero_musical` a la lista del formulario (rock, salsa, vallenato, crossover, mariachis, otro) para que el filtro del directorio sea útil. | 1 h + decisión N |
| B2.4 Cuota en cartera | Confirmar que la mensualidad configurada ($50.000) y la regla de los dos primeros meses ($30.000) son las vigentes; si la regla aplica, la importación de cartera debe reflejarla (no hay que codificarla). | 0,5 h |

### B3 · Beneficios y aliados reales (P2 · contenido N · S6)

| Tarea | Detalle | Esfuerzo |
|---|---|---|
| B3.1 Qué aplica en el Quindío | Enviar a N la lista de los 19 aliados estratégicos (más Hosteleo y Cortés Romero & Asociados) y pedir: cuáles aplican al capítulo, cuáles tienen convenio local propio (en las fotos aparecen Acodrés Quindío, la Alcaldía, la Gobernación y la Cámara de Comercio), y los logos en buena resolución (los del PDF son capturas). | decisión N |
| B3.2 Sembrar aliados reales | Reemplazar los 6 aliados ficticios por los confirmados: `nombre`, `url`, `descripcion` pública (una línea) y `detalle_convenio` con el descuento textual y el contacto (solo con sesión). Sembrador idempotente por nombre. | 2–3 h |
| B3.3 Beneficios en seis bloques | Reescribir los 5 beneficios como los 6 bloques del portafolio (beneficios exclusivos, asesorías, descuentos, programas, mesas sectoriales, aliados) con sus sub-ítems en la descripción; misma estructura en `/afiliate` y en «Quiénes somos». | 2 h |
| B3.4 Respaldo nacional e internacional | Nombres de los aliados nacionales e internacionales solo como texto o con logo autorizado; sin inventar convenios locales. | 1 h |

### B4 · Guía normativa y contenido legal (P2 · S5–S6)

| Tarea | Detalle | Esfuerzo |
|---|---|---|
| B4.1 Sembrar los 7 trámites de Armenia | Ya confirmados el 25 ago; fuente, fecha de verificación y vigencia según RF-60. (En curso.) | 2 h |
| B4.2 Lista de chequeo de funcionamiento | Nuevo `RequisitoApertura` para Armenia (y luego para cada municipio): entidad «Asobares Quindío — verificación de funcionamiento (Ley 1801 de 2016)», los 20 ítems como `checklist`, el formato en PDF como adjunto descargable, `verificado_con` = documento del gremio de 2026. Confirmar con N qué norma es el «decreto 119» antes de citarlo. | 2 h |
| B4.3 Reforma laboral en el boletín | Una noticia «Reforma laboral: obligaciones vigentes para bares y restaurantes» con tabla de fechas ya actualizada a 2026 (jornada nocturna desde las 7 pm vigente; recargo dominical 90 % desde julio de 2026; 42 horas desde julio de 2026; reglamento de trabajo que debía estar actualizado el 25 de junio de 2026), enlace al aliado jurídico y nota de «verificado el …». Antes de publicar, que N o el aliado jurídico la revise. Requiere la categoría `normativa` en el enum (P1, 15 min). | 2 h + revisión |
| B4.4 Fase II: autodiagnóstico | Checklist marcable y guardada por asociado en `/mi-cuenta` con porcentaje de cumplimiento y recordatorio de lo que falta. Fuera del alcance V1. | 8–12 h |

### B5 · Identidad institucional y marca (P2 · textos N · S6–S7)

| Tarea | Detalle | Esfuerzo |
|---|---|---|
| B5.1 Nombre y cargo de la dirección | El sitio dice «Jorge Iván Botero Ángel»; el portafolio nacional dice «Jorge Iván Ángel» y la base registra al propietario como «Jorge Iván Ángel Botero». El certificado firma «Natalia Gutiérrez, Presidente Capítulo Quindío» y el sitio la llama directora ejecutiva. **Confirmar por escrito nombres y cargos** y corregir los ajustes. | 0,2 h + N |
| B5.2 Respaldo nacional | Reescribir el bloque «Somos el capítulo regional» con lo verificable: 17 capítulos regionales en el mapa nacional, correo oficial, Quindío como capítulo propio (el portafolio además describe un capítulo Eje Cafetero con sede en Manizales que «abarca Caldas, Risaralda y Quindío»: pedir a N cómo describirlo). | 1 h |
| B5.3 Programas nacionales | Cruzar las 5 iniciativas sembradas con los programas del portafolio; dejar solo los que operan en el capítulo (hay foto de «Pregunta por Ángela» en el Quindío) y describirlos con su texto real. | 1 h + N |
| B5.4 Auditoría de marca | Con el manual en la mano: área de respeto, tamaño mínimo (226 px / símbolo 28 px), versión en escala de grises (#A7A7A7) si se usa en documentos, y la lista de usos no permitidos revisada en portada, navbar, PDF del manual y correos. Registrar DPV-12 como cerrada. | 1–2 h |

### B6 · Fotografías (P2 · autorizaciones N · S7)

| Tarea | Detalle | Esfuerzo |
|---|---|---|
| B6.1 Curaduría y pies de foto | Pedir a N, por cada foto, evento, fecha, lugar y quiénes aparecen (no hay EXIF). Excluir la duplicada y las que muestren particulares sin autorización (las de Famtrip, por ejemplo). | N |
| B6.2 Autorización de imagen | Las fotos muestran personas identificables: la política de tratamiento de datos debe cubrir imágenes de eventos institucionales y N debe confirmar que existe la autorización (registro del evento o consentimiento). | N |
| B6.3 Pipeline | Renombrar a *slugs*, quitar metadatos, redimensionar (1600 px de lado mayor, 800 px para tarjetas), convertir a **webp** (mandato del cronograma), texto alternativo por foto, `loading="lazy"`. Guardar en `storage` o `public/img/gestion/`, nunca los originales en el repositorio. | 2 h |
| B6.4 Dónde van | Portada: una foto real en el héroe o en el bloque de cifras. «Quiénes somos»: sección «Gestión gremial 2024-2025» con 8–12 fotos y pie. Boletín: una noticia por hito (Expobar, Famtrip, convenio con la Alcaldía) si N quiere contarlos. Iniciativas: foto de «Pregunta por Ángela». Repetir la medición de rendimiento después (RNF-02). | 3–4 h |

### B7 · Certificado de afiliación desde el portal (P1 · ampliación por escrito · después del despliegue)

| Tarea | Detalle | Esfuerzo |
|---|---|---|
| B7.1 Registrar la ampliación | Está en la lista de lo que no se asume pendiente (prompt maestro §24.5). Si N lo quiere, se anota por escrito con criterio de aceptación: el asociado publicado y al día en cartera descarga su certificado con número consecutivo, fecha y firma de la dirección; quien no está al día ve el motivo. | acta |
| B7.2 Construir | Vista imprimible en HTML (sin dependencia nueva) con el texto de la plantilla, número de certificado, código de verificación público `/certificados/{codigo}` y registro en bitácora; PDF con `dompdf` solo si N exige archivo. Nombre y cargo de quien firma desde ajustes. Pruebas: al día / en mora / borrador / código falso. | 6–8 h |

---

## 5. Orden propuesto

| Cuándo | Qué | Quién |
|---|---|---|
| **Mié 26 – vie 28 ago (S5)** | Acciones inmediatas §1 · B1.1–B1.3 (importar y listar la depuración) · B4.1 · enviar las confirmaciones del §6 · B2.1 si hay tiempo | Sua · Ingrid |
| **Lun 31 ago – vie 4 sep (S6)** | Semana del documento de práctica (95 % el viernes). Solo contenido que N ya haya confirmado: B3.2–B3.3, B5.1–B5.3, B4.2–B4.3, B2.1, B2.3 | Ingrid (contenido) · Sua (enum, importador B1.5, cartera B2.4) |
| **Lun 7 – vie 11 sep (S7)** | B6 completo (fotos), B1.6 publicación de fichas con autorización, pruebas globales con la tutora sobre el contenido real, medición de rendimiento con imágenes | Ingrid · Sua |
| **Lun 14 – vie 18 sep (S8)** | Dominio, SSL, capacitación: el manual de usuario debe explicar cómo importar la base, cómo cargar aliados y cómo publicar una ficha con su autorización. B7 solo si está aprobado por escrito y el despliegue existe. | Sua |
| **Después del 22 sep (fase II)** | B2.2 formulario oficial en línea, B4.4 autodiagnóstico, B7 si no alcanzó | Por acordar (DPV-13) |

---

## 6. Confirmaciones que hay que pedirle a Natalia (una sola lista)

1. ¿Cuál de las dos bases es la vigente y qué son las 7 filas que traen solo el nombre? ¿Sigue afiliado el establecimiento de Circasia que desaparece?
2. ¿Cuántos afiliados activos hay hoy (el sitio dice 60; la base, 48)?
3. Nombre completo y cargo del presidente; cargo con el que firma Natalia (presidente en el certificado, directora ejecutiva en el sitio).
4. ¿Cómo describir la relación con el capítulo Eje Cafetero (Manizales) y con Asobares Colombia?
5. ¿Cuáles de los 19 aliados estratégicos (y el jurídico) aplican en el Quindío? ¿Hay aliados locales con convenio (Acodrés, Cámara de Comercio, Alcaldía, Gobernación)? ¿Logos originales?
6. ¿Cuáles programas nacionales operan en el capítulo (Pregunta por Ángela, Tardeo en la Ciudad, Bar Ideal, Pet Friendly…)?
7. Cuota vigente: $30.000 los dos primeros meses y $50.000 desde el tercero, renovación anual. ¿Y la cuenta bancaria del formulario (dice «XXXX»)?
8. ¿Qué norma es el «decreto 119» de la lista de chequeo?
9. ¿Puede el aliado jurídico revisar el calendario laboral antes de publicarlo?
10. Por cada foto: evento, fecha, lugar, quiénes aparecen, y si existe autorización de imagen.
11. ¿Quiere el certificado de afiliación descargable desde el portal (ampliación por escrito)? ¿Quién firma?
12. ¿Prefiere el formulario oficial descargable (opción A) o en línea por pasos (opción B, fase II)?

---

## 7. Lo que esto aporta al documento de práctica (4 de septiembre)

Para el capítulo 5: el apartado de datos puede citar que el gremio entregó la base de establecimientos en dos versiones (41 y 48 filas) y el formulario oficial de registro, y que la carga se hizo con un comando idempotente que no publica ni otorga autorización (evidencia: resumen del comando). Para el capítulo 6: como recomendación, formalizar la actualización anual de la base a través del formulario oficial y del importador, y adoptar la lista de chequeo de funcionamiento como parte de la guía normativa. Para el Anexo E (matriz): los casos nuevos de B1.5 (nombres repetidos, NIT normalizado). Las cifras de este documento (48/41 filas, 8/1/6/7, 19 aliados, 20 ítems, 19 fotos) son reproducibles con los archivos.

---

## 8. Reglas que aplican a todo el plan

- **Nada de `material/nuevomaterial/` se versiona.** Datos por importador, contenido confirmado por sembradores, fotos optimizadas en `storage`/`public` sin originales ni metadatos.
- **Nada se publica sin autorización**: fichas (soporte firmado del titular), fotos (autorización de imagen), textos legales (revisión del gremio o del aliado jurídico).
- **El alcance sigue congelado** (14 ago): B2.2, B4.4 y B7 son ampliaciones y se registran por escrito con criterio de aceptación antes de tocar código; lo demás es contenido y datos dentro de módulos existentes.
- **Frontera Persona 1 / Persona 2**: migraciones, enum, importador y cartera los toca Sua; sembradores y vistas de módulos públicos, Ingrid.
- **Cifras reportables solo reproducibles**: cada cifra que entre al documento de práctica debe salir de un comando o un conteo repetible.

---

## 9. Registro de lo hecho en esta sesión (26 ago)

- Inventario y lectura completa del material (bases, formulario, portafolio, certificado, lista de chequeo, boletín laboral, manual, fotos).
- Comparación programática de las dos bases y del material contra `material/` (md5) y contra el repositorio (migraciones, importador, sembradores, ajustes, tokens de marca, rutas).
- `.gitignore`: añadida la regla `/material/nuevomaterial/` (verificada con `git check-ignore`). Queda un cambio sin confirmar en `.gitignore` que conviene incluir en el próximo commit.
- `.git/index.lock`: el archivo vacío que dejó esta sesión se renombró a `.git/index.lock.huerfano-2026-08-26` (git ya no está bloqueado); se puede borrar junto con los dos huérfanos anteriores del 20 y el 25 de agosto.
- Hoja de contactos de las fotos generada en `material/nuevomaterial/hoja-de-contactos-apoyos-visuales.jpg` (queda dentro de la carpeta ignorada).
- `claude/estado-unificado.md` del Project actualizado (§1, §4.6 y §8). Este plan se guardó completo en el Project (`claude/plan-material-gremio-26-ago.md`) y **sin el Anexo A** en `docs/ingenieria/plan-material-gremio-2026-08-26.md` del repositorio, porque el repositorio es público.

---

## Anexo A · Depuración de la base nueva

_Retirado de la copia del repositorio porque identifica fichas y datos de contacto de terceros. La versión completa está en el Project de Cowork (`claude/plan-material-gremio-26-ago.md`) y en la carpeta ignorada `material/nuevomaterial/`._
