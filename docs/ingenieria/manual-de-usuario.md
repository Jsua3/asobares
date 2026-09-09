# Manual de usuario — Panel de administración
## Plataforma Web ASOBARES Capítulo Quindío

**Versión:** 1.3 · **Fecha:** 9 de septiembre de 2026
**Dirigido a:** dirección ejecutiva, secretaría y practicantes del capítulo
**No necesita conocimientos técnicos.** Si sabe usar el correo, sabe usar esto.

> 🚨 **LAS ONCE CAPTURAS ESTÁN CADUCADAS Y HAY QUE VOLVER A TOMARLAS ANTES DE LA CAPACITACIÓN.**
>
> Son del **18 de agosto**, y entre el 7 y el 8 de septiembre el panel se rehízo entero: identidad visual, tablero, tablas, la barra lateral de escritorio —que pasó de franja burdeos a láminas de cristal sobre un campo de puntos— y, en el teléfono, un riel de iconos que antes no existía. Las capturas enseñan un panel que ya no está.
>
> Una captura vieja es peor que ninguna: el lector busca en pantalla algo que no va a encontrar y concluye que se equivocó él. **Solo Sua o Ingrid pueden rehacerlas**, porque el segundo factor impide que una sesión automatizada abra el panel. Once imágenes, tema claro, 1440 px, base de demostración: media hora.
>
> El texto de este manual **sí está al día** al 9 de septiembre de 2026.

> 📸 Ninguna imagen contiene datos personales de una persona real: todos los establecimientos y nombres que aparecen son ficticios.

> **Qué cambió en la versión 1.3** (9 de septiembre de 2026), todo del 8 y el 9 de septiembre:
>
> - **§6** — el banco de talento ahora **hay que aprobarlo perfil por perfil**, y si nadie lo hace el módulo se ve vacío. Es lo más importante de esta revisión.
> - **§6** — la ficha pública del artista conserva el escaparate y pierde el contacto.
> - **§6** — el borrado automático de datos personales depende de un recurso del hosting que hay que activar una vez; cómo comprobarlo.
> - **§7** — el reloj de los quince días hábiles de la PQR, y quién se entera de que entró un mensaje.
> - **§8 bis** — nueva: las métricas de flujo del sitio que pidió la dirección.

---

## 1. Antes de empezar

### Cómo entrar

1. Abra `​/admin` en el navegador.
2. Escriba su correo y su contraseña.
3. El sistema le pedirá un **código de seis dígitos** que llega a su correo. Escríbalo.

![Pantalla de acceso al panel](capturas/01-inicio-de-sesion.png)

*Pantalla de acceso al panel.*

**El código por correo no es opcional.** Es una exigencia de seguridad del proyecto: protege los datos personales de los asociados, de los candidatos a empleo y de quienes escriben al gremio. Si nunca ha configurado el segundo factor, el sistema lo guía la primera vez en lugar de dejarlo por fuera.

> **Si no le llega el código:** revise correo no deseado. Si sigue sin llegar, avise a quien administre la plataforma — puede ser que el servicio de correo no esté configurado en ese servidor, y hay otra forma de obtenerlo.

### Los dos tipos de cuenta

| | **Dirección** (súper administrador) | **Secretaría** (subadministrador) |
|---|---|---|
| Ver todo el panel | Sí | Sí, salvo Cartera, Transacciones y Usuarios |
| Redactar contenido | Sí | Sí |
| **Publicar al sitio** | **Sí** | **No — queda pendiente de aprobación** |
| Aprobar lo que redactó otra persona | Sí | Solo en las Bolsas |
| Cambiar la configuración del sitio | Sí | No |

Esta separación es deliberada y es uno de los requisitos centrales del proyecto: **la secretaría redacta, la dirección publica.** No es una restricción del formulario que se pueda saltar — vive en el corazón del sistema. Si la secretaría marca «Publicado» y guarda, el registro queda igualmente en «Pendiente de aprobación».

### Cómo está organizado el menú

![Menú lateral completo, con la sesión de la Dirección](capturas/02-menu-lateral.png)

*Menú lateral completo, con la sesión de la Dirección.*

| Grupo | Qué contiene | Para qué |
|---|---|---|
| **Contenido** | Asociados · Eventos · Noticias · Iniciativas · Requisitos de apertura | Lo que se ve en el sitio público |
| **Gremio** | Aliados · Beneficios · Cartera · Transacciones | Afiliación, convenios y dinero |
| **Bolsas** | Vacantes · Artistas · Proveedores · Banco de talento | Lo que publican terceros y usted modera |
| **Bandejas** | Inscripciones · Mensajes y PQR · Postulaciones | Lo que llega de afuera y hay que atender |
| **Configuración** | Municipios · Categorías · Usuarios · Ajustes del sitio · Bitácora | Los cimientos, se tocan poco |

**Regla práctica:** si se pregunta «¿dónde está tal cosa?», piense en si es algo que **usted publica** (Contenido), algo que **le llega** (Bandejas) o algo que **otro publicó y usted revisa** (Bolsas).

---

## 2. Publicar un asociado nuevo

Es la tarea más frecuente: el gremio crece mes a mes.

1. **Contenido → Asociados → Crear**.
2. Llene el nombre, el municipio y la categoría (bar, gastrobar, café, discoteca).
3. Suba la foto de portada. **No se preocupe por el tamaño ni el formato:** el sistema convierte y optimiza las imágenes solo.
4. Distinga los dos bloques de contacto:
   - **Contacto público** — sale en la ficha que ve todo el mundo.
   - **Contacto interno** — solo lo ve el equipo del gremio. Nunca aparece en el sitio.
5. En «Publicación», escoja el estado y guarde.

![Formulario de asociado: arriba lo que ve el público, abajo los datos internos del gremio](capturas/03-asociado-publico-vs-interno.png)

*Formulario de asociado: arriba lo que ve el público, abajo los datos internos del gremio.*

> ⚠️ **El propietario decide qué se publica, no el gremio.** Es un acuerdo con la directiva y una obligación legal: antes de publicar datos de un establecimiento hay que tener su autorización. El bloque «contacto interno» existe justamente para guardar lo que el dueño no autorizó mostrar.

**Si usted es secretaría:** al guardar, el registro queda en «Pendiente de aprobación» y la dirección recibe un aviso. Es lo esperado, no un error.

---

## 3. Publicar un evento y recibir inscripciones

1. **Contenido → Eventos → Crear**.
2. Fecha, hora, lugar, descripción.
3. **Aforo:** si escribe un número, el sistema cierra las inscripciones al llenarse y avisa a quien llegue tarde. Si lo deja vacío, no hay límite.
4. **Valor:** si el evento tiene costo, quien se inscriba pasa por la pasarela de pago. Si es gratuito, se inscribe directo.

![Formulario de evento, con el bloque de inscripción, cupos y precio](capturas/04-evento-aforo-y-valor.png)

*Formulario de evento, con el bloque de inscripción, cupos y precio.*

Las inscripciones llegan a **Bandejas → Inscripciones**, con el estado de pago de cada una.

> **Solo eventos del gremio.** Es una regla editorial acordada con la directiva: ExpoBar, congresos, capacitaciones propias. Los eventos de bares individuales no se publican, y lo nacional se enlaza al registro de la Nacional.

---

## 4. Mantener la guía normativa

Es el módulo más valioso del sitio: **ningún otro gremio del país lo tiene**, y es la razón principal por la que alguien va a visitar la página. También es el que más se desactualiza si nadie lo cuida.

1. **Contenido → Requisitos de apertura**.
2. Cada trámite pertenece a un municipio y lleva: entidad responsable, pasos, costo aproximado y el formato descargable.
3. Suba el formato oficial como archivo adjunto.

![Ficha de un trámite de la guía normativa con su formato descargable](capturas/05-requisito-con-formato.png)

*Ficha de un trámite de la guía normativa con su formato descargable.*

**Al reemplazar un formato viejo por uno nuevo, los enlaces que ya circulan siguen funcionando.** Cámbielo con confianza.

### Cómo saber si la guía sirve

**Observatorio → Consultas de la guía** muestra cuántas personas consultaron cada municipio y cuántos formatos se descargaron.

Ese registro **no guarda ningún dato de quién consultó** — es un conteo anónimo. Sirve para dos cosas: saber qué municipio priorizar, y tener una cifra concreta que mostrarle a una alcaldía cuando el gremio negocie con ella.

---

## 5. Aprobar lo que redactó otra persona

*(Solo dirección.)*

Cuando la secretaría redacta algo, usted recibe un aviso en la **campana** de arriba a la derecha.

1. Pulse la campana → **Revisar**.
2. O entre al **Escritorio**: la primera banda muestra todo lo pendiente, ordenado, con lo más viejo marcado como urgente.
3. En la fila del registro, use **«Aprobar y publicar»** o **«Devolver a borrador»**.

![Tablero: la banda «Te está esperando» solo muestra lo que le toca aprobar a quien entró](capturas/06-escritorio-pendientes.png)

*Tablero: la banda «Te está esperando» solo muestra lo que le toca aprobar a quien entró.*
![Solo la fila pendiente ofrece «Aprobar y publicar»; las ya publicadas solo se pueden devolver](capturas/07-acciones-aprobar-devolver.png)

*Solo la fila pendiente ofrece «Aprobar y publicar»; las ya publicadas solo se pueden devolver.*

Si devuelve algo, **el motivo es obligatorio**. No es burocracia: quien lo redactó recibe ese motivo y puede corregir sin adivinar.

---

## 6. Moderar las bolsas

Las vacantes, los artistas y los proveedores **no los crea el gremio**: los publica quien tiene la necesidad, y ustedes aprueban o devuelven.

- **Bolsas → Vacantes.** Las publica el asociado desde su portal. Usted aprueba o devuelve con motivo. Puede aprobar varias a la vez seleccionándolas.
- **Bolsas → Artistas** y **→ Proveedores.** Entran por un formulario público. Al aprobar, el sistema avisa por correo a quien se inscribió.
- **Bandejas → Postulaciones.** Los candidatos a las vacantes. Aquí solo se consultan: **quien gestiona los candidatos es el establecimiento dueño de la vacante**, no el gremio.

### ⚠️ El banco de talento hay que aprobarlo, uno por uno

**Esto es nuevo desde el 8 de septiembre de 2026 y es lo que más se nota si nadie lo hace.**

Cualquiera puede dejar su hoja de vida en `/empleo`. Antes, ese perfil quedaba visible **al instante** para todos los establecimientos afiliados: nombre, teléfono y correo de una persona, publicados sin que nadie los mirara. Ahora no. Un perfil nace **sin aprobar** y no lo ve nadie hasta que ustedes digan que sí.

En **Bolsas → Banco de talento**:

1. El filtro **«Sin aprobar»** deja a la vista lo que está esperando.
2. Se abre el perfil con **«Ver perfil»** y se lee lo que dejó la persona.
3. **«Aprobar»** lo pone a disposición de los afiliados. El aviso lo dice con todas las letras: *«Los establecimientos afiliados verán su nombre, su teléfono y su correo»*.
4. **«Retirar del banco»** deshace lo anterior sin borrar el perfil.

> **Si nadie aprueba, el directorio de talento del afiliado se ve vacío**, y no está roto: está esperándolos. Es el modo de fallo más silencioso del panel, porque la pantalla responde y no enseña a nadie.

> 🕮 **Aprobar y retirar quedan en la Bitácora, con nombre y hora.** Es la decisión de mostrar o esconder los datos de contacto de una persona, así que tiene que poder auditarse. Ninguna otra acción de este módulo se registra: la bitácora anota la decisión, no el expediente.

### La ficha del artista, por partes

Desde el 8 de septiembre, la ficha pública de un artista **conserva el escaparate** —nombre, foto, género, vídeo— y **pierde el contacto**. El teléfono y el correo se ven en `/mi-cuenta/artistas`, o sea solo con sesión de afiliado. Lo mismo pasa con los proveedores: `/proveedores` sigue siendo pública y no muestra ni un contacto.

![Aprobación en lote: seleccione las vacantes y abra las acciones](capturas/08-vacantes-aprobacion-en-lote.png)

*Aprobación en lote: seleccione las vacantes y abra las acciones.*

> **Ni la secretaría ni la dirección editan la vacante de un establecimiento.** Aprobarla o devolverla, sí. Cambiarle el texto, no. El contenido es del asociado.

> 🔒 **Los datos personales de las bolsas se borran solos.** Las postulaciones y los perfiles del banco de talento se eliminan automáticamente al vencer el plazo de conservación. Es una obligación de la Ley 1581 de 2012 y el sistema la cumple sin que nadie tenga que acordarse. Cada borrado queda anotado en la Bitácora.
>
> ⚠️ **Eso exige una cosa del lado del servidor, y hay que comprobarla una vez.** El borrado lo hacen tres tareas que corren de madrugada, y en el hosting eso es un recurso que se activa aparte —no viene puesto de fábrica—. Está explicado en `runbook-despliegue.md` §5.1. Para verificar que funciona: entrar a **Configuración → Bitácora** y buscar `Depuración de datos`. Ojo con leer mal el silencio: si no había nada que borrar tampoco escribe, así que la ausencia no prueba que esté roto, pero su presencia sí prueba que funciona.

---

## 7. Atender mensajes y PQR

**Bandejas → Mensajes y PQR** reúne todo lo que llega por el sitio: contacto general, solicitudes de afiliación, postulaciones de aliados y PQR.

Las **PQR reciben un radicado automático** con formato `PQR-2026-0001`, consecutivo y sin saltos, y el remitente recibe acuse por correo. Eso importa porque las PQR tienen plazos legales de respuesta y el radicado es la prueba de la fecha.

Al responder, use **«Marcar respondido»**: pide una nota que queda como constancia de qué se contestó y cuándo.

### El reloj de la PQR

**Nuevo desde el 9 de septiembre de 2026.** La ley da **quince días hábiles** para responder una PQR (Ley 1755 de 2015), y ahora el panel lleva esa cuenta:

- La columna **«Plazo de ley»** dice la fecha de vencimiento de cada PQR. Verde si sobra tiempo, **ámbar cuando quedan tres días hábiles o menos**, y **«Vencida»** escrito —no solo en rojo— cuando ya pasó.
- El filtro **«PQR pasadas de plazo»** las junta todas. Es el primer clic de un lunes.
- El **número junto a «Mensajes y PQR» en el menú** cuenta lo que espera respuesta, y se pone rojo si alguna ya venció. Así se ve sin entrar a la bandeja.

> Los festivos colombianos **no se descuentan** de esa cuenta, porque el sistema no tiene el calendario de festivos. El error va a favor: la fecha que muestra es igual o anterior a la legal, nunca posterior. Para una respuesta formal ante la SIC, cuente los días con el calendario en la mano.

### Quién se entera de que llegó algo

**También nuevo del 9 de septiembre.** Antes, un mensaje entraba y no avisaba a nadie: había que abrir el panel y mirar. Ahora:

- El **contador del menú** lo anuncia en cuanto se entra al panel, a lo que sea.
- Sale un **correo al buzón del gremio**, al que diga **Configuración → Ajustes del sitio → Contacto → «Correo que recibe los formularios»**. Cambiar ahí la dirección cambia de verdad a dónde llega el aviso.

> ⚠️ **Ese correo no está saliendo todavía.** Falta contratar el servicio de correo saliente. Mientras tanto el contador del menú sí funciona, y el aviso queda anotado como fallido en el registro. El día que se configure el correo, empieza a salir sin tocar nada.
>
> El aviso **no copia el texto del mensaje ni el teléfono de quien escribe**, a propósito: sacar esos datos hacia un buzón de correo los pondría fuera del sistema que sabe borrarlos cuando vence su plazo. El contenido se lee aquí, en el panel.

![Bandeja de mensajes con el panel de filtros abierto](capturas/09-mensajes-filtro-por-tipo.png)

*Bandeja de mensajes con el panel de filtros abierto.*

---

## 8. Cartera y pagos

*(Solo dirección.)*

### Subir el estado de cuenta del mes

1. **Gremio → Cartera → «Descargar plantilla»**. Baja un archivo con el estado actual y las columnas exactas.
2. Pásele el archivo a la contadora, o llénelo usted.
3. **«Importar CSV de la contadora»** → suba el archivo → confirme.
4. Sale un resumen de cuántos estados se actualizaron. **Si una fila viene mal, el sistema dice cuál y por qué, y las demás sí entran.** Un error no daña toda la carga.

![La importación aplica las filas correctas y explica, una por una, las que rechazó](capturas/10-importacion-con-fila-rechazada.png)

*La importación aplica las filas correctas y explica, una por una, las que rechazó.*

El importador tolera lo que llega en la vida real: encabezados con tildes y mayúsculas, montos como `$1.250.000`, fechas en cualquiera de los dos formatos comunes.

Cargada la cartera, el asociado entra a `/mi-cuenta`, ve **«Debes 3 meses · $150.000»** y paga ahí mismo. Ese era el objetivo declarado por la directiva: *«la gente no paga porque no sabe cuánto debe, entonces todo el mundo llama a Natalia»*.

### Consultar los pagos

**Gremio → Transacciones**, en solo lectura: referencia, fecha, valor y estado. No se editan a mano a propósito — un pago es un hecho, no un dato editable.

---

## 8 bis. Las métricas del sitio

*(Solo dirección.)*

En el **Tablero**, debajo de lo operativo, hay cuatro piezas que responden cuánta gente usa el sitio. Se pidieron el 9 de septiembre de 2026 y están amparadas por el **Acta 07** y el **Acta 08**.

**Lo primero, porque de aquí salen todos los malentendidos: esto no cuenta personas.** El sitio no guarda IP, ni cookie, ni sesión de quien visita —esa fue una decisión escrita, para no entrar en el terreno de la Ley 1581 sin la política de tratamiento publicada—. Lo que se cuenta es:

| Pieza | Qué responde |
|---|---|
| **Flujo del sitio** (tres números) | Cuánta gente entró esta semana, si eso sube o baja respecto a la anterior, y cuántas páginas mira cada quien |
| **Flujo del sitio, 30 días** (curva) | Dos líneas: **entradas** (llegadas al sitio) y **páginas servidas** (todo lo que se abre). Si suben las entradas y no las páginas, llega más gente y se va enseguida. Si suben las páginas y no las entradas, la misma gente mira más |
| **Por dónde entran** | La primera página de cada visita, ordenada. Es la que dice dónde poner el esfuerzo: si la mayoría llega por la guía normativa, el producto insignia es la guía |
| **Secciones más visitadas** | Qué se mira una vez dentro. Agrupa por sección, no por dirección: todas las fichas del directorio cuentan juntas |

> **«Entradas» no es «visitantes únicos».** Alguien que entra hoy y vuelve mañana cuenta dos. Si a la dirección le hace falta el número de personas distintas, es una decisión aparte: exige identificar a quien visita y publicar antes la política de tratamiento de datos.

> Lo que **no** se cuenta, para que nadie lea de más: el panel, el portal del afiliado, las páginas de pago, los rastreadores de los buscadores, las descargas de formatos y todo lo que no sea una página. Y el conteo empezó el **9 de septiembre de 2026**: antes de esa fecha las entradas figuran en cero porque el dato no se recogía, no porque no hubiera visitas.

---

## 9. Configuración

*(Solo dirección. Se toca poco.)*

- **Ajustes del sitio** — datos de contacto, redes sociales y textos que aparecen en todo el sitio. Cambiarlos aquí los cambia en todas partes. **Nada del sitio está escrito a fuego en el código:** ese fue un requisito explícito del cronograma.
- **Usuarios** — dar de alta a quien entra al panel y con qué rol. Las contraseñas deben ser robustas; el sistema rechaza las débiles.
- **Municipios y Categorías** — las listas de las que se alimentan los formularios.
- **Bitácora** — quién hizo qué y cuándo. Consúltela cuando algo cambió y no sepa quién lo cambió. Con practicantes que rotan, es su red de seguridad.

![Bitácora: quién hizo qué y cuándo](capturas/11-bitacora.png)

*Bitácora: quién hizo qué y cuándo.*

---

## 10. Preguntas frecuentes

**Publiqué algo y no aparece en el sitio.**
Si usted es secretaría, quedó pendiente de aprobación: avise a la dirección. Si es dirección, revise que el estado sea «Publicado» y no «Borrador».

**Me equivoqué al publicar.**
Cambie el estado a «Borrador» y guarde. Sale del sitio de inmediato.

**Se me perdió un mensaje.**
No se pierde: todo lo que entra por el sitio queda en Bandejas. Use los filtros por tipo y por fecha. Tenga en cuenta que los mensajes muy antiguos ya respondidos se eliminan solos por la política de datos.

**¿Puedo borrar un asociado?**
Puede, pero **casi siempre es mejor despublicarlo**: pasarlo a «Borrador» lo saca del sitio y conserva su historial de pagos. Borrarlo se lleva por delante lo asociado a él.

**¿Cómo cambio el tema claro/oscuro?**
Arriba a la derecha. Su elección se recuerda y vale también para el sitio público.

---

## 11. Qué falta para dar este manual por terminado

**Contenido, capturas y PDF: completos** (19 de agosto de 2026). Las once imágenes se tomaron del panel real con la base de demostración cargada. Queda:

1. ✅ ~~Exportar a PDF~~ — hecho. `Manual de usuario - Panel ASOBARES Quindio.pdf`, 24 páginas, en esta misma carpeta. Se regenera con `node docs/ingenieria/herramientas/manual-a-pdf.mjs`; **edite siempre este `.md`, nunca el PDF.**
2. **Complementar con vídeo** si se prefiere: un recorrido de 10 minutos que siga las secciones 2, 3, 5 y 8 cubre el 90 % del uso diario.
3. **Verificar contra la realidad de la capacitación:** el criterio contractual no es que el manual exista, sino que **al terminar la sesión el personal publique un asociado, un evento y una noticia sin ayuda**. Lo que falle en esa prueba es lo que hay que reescribir aquí. Mientras esa sesión no ocurra, este manual está probado contra el software pero no contra sus lectores. El formato para dejar constancia de esa sesión es `constancias/Acta 02 - Constancia de capacitacion.pdf`.

> 🚨 **Las once capturas están caducadas desde el 7 y 8 de septiembre de 2026, y esta es la tarea que bloquea dar el manual por terminado.**
>
> Eran fotografías de `main` en `4f15d24`. El trabajo de interfaz del 19 de agosto no las invalidaba —tocaba solo el sitio público— pero el del **7 y 8 de septiembre sí**: el panel se rehízo entero (identidad, tablero, tablas, barra lateral de escritorio, riel del teléfono). Las once enseñan una pantalla que ya no existe.
>
> **Cómo rehacerlas**, y solo puede hacerlo una persona porque el segundo factor no deja entrar a una sesión automatizada:
>
> 1. `php artisan migrate:fresh --seed` en local, para tener la base de demostración.
> 2. Entrar a `/admin` con `direccion@asobaresquindio.test` · `Asobares2026*`. El código del segundo factor sale en `storage/logs/laravel.log`.
> 3. Tema claro, ventana a 1440 px, y repetir las once de `capturas/` con el mismo encuadre y el mismo nombre de archivo.
> 4. Añadir una duodécima de **Bolsas → Banco de talento con el filtro «Sin aprobar»**, que es la pantalla nueva que más falta hace en la §6.
> 5. Regenerar el PDF: `node docs/ingenieria/herramientas/manual-a-pdf.mjs`.

---

*Elaborado como entregable de la Fase 4 del cronograma firmado por la dirección ejecutiva de ASOBARES Capítulo Quindío.*
