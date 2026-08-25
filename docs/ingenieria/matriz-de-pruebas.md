# Matriz de pruebas — Plataforma Web ASOBARES Capítulo Quindío

**Versión:** 1.0 · **Fecha:** 18 de agosto de 2026 · **Elaboró:** Juan José Sua Gómez (práctica empresarial, Universidad Alexander von Humboldt)
**Línea base de requisitos:** ERS v3.0 (RF-01 a RF-62, RNF-01 a RNF-14) · **Espejo técnico:** product backlog v2 (14 épicas, 53 historias de usuario)
**Estado del repositorio:** `main` en `4f15d24` · repositorio `Jsua3/asobares`

---

## 1. Qué es este documento

El cronograma firmado por la dirección ejecutiva exige, en la Fase 4, una **matriz de pruebas** entre los entregables de cierre. Este es ese artefacto.

No se confunde con la suite automatizada: la suite es el **instrumento**, esta matriz es la **evidencia legible** de qué requisito contratado está verificado, por qué prueba, y qué queda sin verificar. Un requisito sin prueba asociada no está probado aunque el código exista — y este documento lo dice cuando ocurre.

**Cómo leer el estado:**

| Símbolo | Significado |
|---|---|
| ✅ | Cubierto por prueba automatizada que falla si se rompe el comportamiento |
| ⚠️ | Cubierto parcialmente: hay prueba, pero no alcanza todo el criterio de aceptación |
| ❌ | Sin cobertura automatizada (con el motivo indicado) |
| ➖ | Fuera del alcance V1 por decisión de la ERS v3 (Fase II/III); la estructura existe, el comportamiento no se promete |

---

## 2. Resumen de cobertura

| Métrica | Valor |
|---|---|
| Archivos de prueba | 60 |
| **Métodos de prueba** | **591** |
| **Casos ejecutados** | **819** (los métodos con proveedor de datos expanden a varios casos cada uno) |
| Resultado de la última ejecución **verificada** | 808 pasan · 11 omitidas · **0 fallos** |
| Aserciones | **2.899** |
| Duración de la suite completa | 350 s |
| Requisitos funcionales V1 con cobertura total o parcial | 46 de 50 |
| Requisitos no funcionales con cobertura total o parcial | 11 de 14 |

> ✅ **Última ejecución — 24 de agosto de 2026.** Sobre el árbol de trabajo, con `php artisan test --compact` y PHP 8.5.9 con `intl` y `gd`: **819 casos · 808 pasan · 11 omitidas · 0 fallos · 2.899 aserciones · 350 s**. Los casos nuevos respecto al 23 de agosto cierran RF-60: la vigencia de la guía normativa en el modelo, en las cuatro puertas por las que sale y en el panel.
>
> ✅ **Última ejecución — 23 de agosto de 2026.** Sobre el árbol de trabajo, con `php artisan test --compact` y PHP 8.5.9 con `intl` y `gd`: **791 casos · 780 pasan · 11 omitidas · 0 fallos · 2.818 aserciones · 267 s**. Es la corrida que respalda la tabla de arriba, y la primera que incluye la carga de la base real de asociados.
>
> ✅ **Reproducibilidad — 20 de agosto de 2026.** La suite se re-ejecutó sobre un **clon limpio de `origin/main`** en un entorno montado desde cero (PHP 8.4.21 con `intl` y `gd`, `composer install` y `npm run build` de cero), no sobre el árbol de trabajo. La salida de aquel día: **759 casos · 748 pasan · 11 omitidas · 0 fallos · 2.733 aserciones · 193 s**. Reproducir el verde fuera de la máquina donde se programó es lo que la auditoría del 19 de agosto no pudo hacer, y es lo que convierte estas cifras en evidencia. La duración baja de 372 a 193 s porque es otra máquina, no porque el proyecto haya mejorado.
>
> ⚠️ **Las dos notas no son la misma corrida y no hay que fundirlas.** El clon limpio del 20 de agosto verificó los 759 casos que entonces vivían en `origin/main`; los 32 que añade el bloque de importación se verifican hoy sobre el árbol de trabajo, porque hasta este commit no existían en el repositorio. Cuando el bloque esté publicado, **la corrida sobre clon limpio hay que repetirla**: una cifra de árbol de trabajo no sustituye a una reproducida fuera.
>
> **Los 44 casos nuevos respecto al 19 de agosto** se reparten en cuatro frentes, todos del bloque de la Persona 1: las extensiones de PHP declaradas en la raíz y en el candado (10), las transacciones fijadas como recurso de solo lectura (2), la carga de la base real de asociados (15) y la frontera entre datos internos y ficha pública del asociado (17), que hasta ahora era una declaración en el modelo que **ninguna prueba ejercitaba**.
>
> **Ejecución anterior.** El 19 de agosto de 2026 con `php artisan test --compact`, y la salida confirma las cifras de esta tabla: **747 casos · 736 pasan · 11 omitidas · 0 fallos · 2.719 aserciones**. El conteo de 537 métodos y 56 archivos se verificó directamente sobre el árbol de trabajo. Ya no queda ninguna cifra de este documento sin respaldo de una ejecución real.
>
> **Crecimiento respecto al 18 de agosto:** de 599 a 747 casos, **+148 pruebas y cero regresiones**. Las nuevas cubren el calendario de eventos (RF-19), el foco visible y los objetivos táctiles (RNF-12), la escala tipográfica, los portadores de acuse al pulsar, la configuración de despliegue, el almacenamiento de archivos y las transiciones de vista.
>
> Si el código cambia antes de la entrega final, hay que repetir la ejecución y actualizar esta tabla: una matriz que cita una corrida vieja es exactamente el tipo de falso verde que este proyecto ya pagó caro.

**Las 11 pruebas omitidas** corresponden a recursos del panel que deliberadamente no tienen página de creación o edición: Cartera (se alimenta por importación de CSV), Postulación (la crea el candidato desde el sitio público, nunca el personal) y Vacante (la publica el asociado desde su portal). La omisión es la prueba de que esas puertas están cerradas a propósito.

---

## 3. Matriz de requisitos funcionales

### EP-01 · Sitio institucional

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-01 | HU-INS-01 | La página de inicio responde y aplica el tema | `SitioPublicoTest::test_las_rutas_publicas_responden` · `TemaClaroOscuroTest` | ✅ |
| RF-02 | HU-INS-02 | «Quiénes somos» responde y su contenido es editable desde el panel | `SitioPublicoTest` · `AccionesDelPanelTest::test_guardar_los_ajustes_actualiza_el_sitio` | ✅ |
| RF-03, RF-04 | HU-INS-03 | Respaldo nacional, contacto y redes; los enlaces configurables no admiten esquemas peligrosos | `EnlacesDeAjustesTest` (4 casos) · `AccionesDelPanelTest` | ✅ |
| RF-05 | HU-INS-04 | Estructura normativa institucional | — | ⚠️ Prioridad Could; contenido servido como página estática, sin prueba propia. Pendiente **DPV-05**: la ERS pregunta si duplica la guía normativa |

### EP-02 · Directorio de asociados *(top-3 del cliente)*

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-06, RF-07 | HU-DIR-01 | Listado público con filtros por municipio y categoría; los filtros ofrecen opción vacía | `SitioPublicoTest::test_las_rutas_publicas_responden` · `FormulariosPublicosTest::test_el_directorio_no_necesita_opcion_vacia_porque_sus_filtros_ya_la_traen` | ✅ |
| RF-08 | HU-DIR-02 | La ficha responde y un asociado sin publicar no es visible | `SitioPublicoTest::test_las_fichas_de_detalle_responden` · `::test_un_asociado_sin_publicar_no_es_visible` | ✅ |
| RF-09 | HU-DIR-03 | Mapa interactivo que respeta la preferencia de movimiento reducido | `MovimientoTest::test_el_mapa_consulta_la_preferencia_de_movimiento` | ⚠️ Se prueba el comportamiento del mapa ante accesibilidad, no el renderizado de los marcadores (requiere navegador) |
| RF-10 | HU-DIR-04 | Alta y edición de asociados desde el panel sin tocar código | `AccionesDelPanelTest::test_crear_un_asociado_desde_el_formulario_del_panel` · `PanelCompletoTest` (19 recursos) | ✅ |
| RF-11 | HU-DIR-05 | La ficha pública no filtra los campos marcados como internos | `SitioPublicoTest::test_la_ficha_publica_no_filtra_los_datos_internos_del_asociado` | ✅ |

### EP-03 · Guía normativa por municipio ★ *producto insignia*

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-15 | HU-GUI-01 | La guía responde por municipio y está protegida contra abuso | `Panel/ConsultasDeGuiaTest::test_visitar_la_guia_registra_la_consulta_del_municipio` · `SitioPublicoTest::test_la_guia_normativa_tiene_limite_de_peticiones` | ✅ |
| RF-16 | HU-GUI-02 | Cada trámite lleva entidad, pasos y costo | `Panel/ConsultasDeGuiaTest` · `PanelCompletoTest` (recurso RequisitoApertura) | ✅ |
| RF-17 | HU-GUI-03 | Descarga de formatos, con métrica de uso y protección | `Panel/ConsultasDeGuiaTest::test_descargar_formato_valido_registra_la_consulta` · `::test_intentar_descargar_sin_adjunto_no_registra` · `::test_intentar_descargar_no_publicado_no_registra` · `SitioPublicoTest::test_descargar_formato_de_la_guia_tiene_limite_de_peticiones` | ✅ |
| RF-18 | HU-GUI-04 | CRUD de municipios, trámites y formatos sin código | `PanelCompletoTest` (Municipios, RequisitoAperturas) | ✅ |
| RF-60 | *(nuevo ERS v3)* | Normativa vigente y decretos transitorios por municipio | `VigenciaDeLaGuiaTest`, `VigenciaEnElPanelTest` | ✅ Cada trámite guarda con qué fuente y en qué fecha se verificó; los decretos transitorios caducan solos y desaparecen del sitio por las cuatro puertas, incluida la descarga del formato |

> **Métrica anónima.** `consultas_guia` demuestra el uso del módulo insignia sin registrar a quien consulta: `Panel/ConsultasDeGuiaTest::test_la_tabla_no_guarda_ningun_dato_personal` lo verifica. Es la evidencia que la dirección puede llevar a las alcaldías.

### EP-04 · Capacitaciones y eventos

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-19 | HU-EVE-01 | Listado de eventos próximos e históricos, **y vista de calendario** | `CalendarioDeEventosTest` (16 casos) · `SitioPublicoTest::test_las_rutas_publicas_responden` | ✅ **Cerrado el 19 de agosto de 2026.** Se construyó la vista de calendario que pide el cronograma firmado: rejilla mensual en escritorio y agenda por día en móvil, navegable entre meses sin JavaScript. La grilla Próximos/Pasados se conserva y el conmutador pasa a tres destinos. Al construirlo se corrigió un defecto anterior: un evento de varios días en curso se listaba como pasado desde su segundo día |
| RF-20 | HU-EVE-02 | Ficha con aforo; el cupo lleno bloquea la inscripción | `CuposDeEventoTest` (4 casos) | ✅ |
| RF-21 | HU-EVE-03 | Inscripción con habeas data obligatorio y constancia | `FormulariosPublicosTest::test_la_inscripcion_exige_la_autorizacion_de_datos` · `EvidenciaDelConsentimientoTest::test_inscribirse_a_un_evento_guarda_la_evidencia_del_consentimiento` | ✅ |
| RF-22 | HU-EVE-04 | Pago de la inscripción y confirmación solo con transacción aprobada | `FlujoDePagoTest` (26 casos) · `ConfirmacionDeInscripcionTest` (5 casos) | ✅ |
| RF-23 | HU-EVE-05 | Consulta de inscritos desde el panel | `PanelCompletoTest` (recurso Inscripciones) | ✅ |

### EP-05 · Cuentas de usuario y roles

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-41 | HU-CTA-01 | Alta de perfiles públicos con autorización de datos | `SolicitudesDeBolsaTest` · `BolsaDeEmpleoTest::test_dejar_el_perfil_dos_veces_actualiza_en_vez_de_duplicar` | ✅ |
| RF-42 | HU-CTA-02 | Login del asociado, sin fuga por tiempo, con bloqueo por cuenta y sin redirección abierta | `LoginDeAsociadoTest` (5 casos) · `PoliticaDeContrasenasTest` (3 casos) | ✅ |
| RF-43 | HU-CTA-03 | Consultar es público; publicar y ver lo privado exige sesión del rol correcto | `FormulariosPublicosTest::test_mi_cuenta_exige_el_rol_asociado` · `MisVacantesTest::test_el_equipo_del_gremio_no_entra_al_portal_del_asociado` · `PermisosDeBolsaTest` · `PanelAdminTest` | ✅ |
| RF-44 | HU-CTA-04 | Activar módulos de Fase II no exige migraciones destructivas | `BancoDeTalentoTest::test_el_aspirante_ya_no_cuelga_de_una_vacante` | ✅ Verificado con el caso real: el banco de talento se independizó sin rehacer la autenticación |

### EP-06 · Bolsa de empleo *(prioridad n.º 1 del cliente)*

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-45 | HU-EMP-01 | Solo el asociado dueño publica; la vacante nace pendiente de aprobación | `MisVacantesTest::test_la_vacante_recien_creada_queda_pendiente_de_aprobacion` · `::test_el_asociado_no_puede_publicar_su_vacante_mandando_el_estado` · `AutorizacionDeVacantesTest` (7 casos) | ✅ |
| RF-46 | HU-EMP-02 | Muro público con filtros; solo vacantes publicadas y vigentes | `BolsaDeEmpleoTest` (14 casos) · `FormulariosPublicosTest` | ✅ |
| RF-47 | HU-EMP-03 | Postulación con habeas data, sin duplicados y a prueba de carrera | `BolsaDeEmpleoTest::test_una_condicion_de_carrera_al_postular_no_revienta_ni_duplica_el_correo` · `PostulacionTest` (4 casos) · `EvidenciaDelConsentimientoTest` | ✅ |
| RF-48 | HU-EMP-04 | El dueño recibe aviso y gestiona sus postulaciones; nadie ve las ajenas | `MisVacantesTest::test_un_directivo_que_tambien_es_asociado_no_ve_las_postulaciones_de_otro_establecimiento` · `CorreosDeBolsaTest` (6 casos) | ✅ |
| RF-49 | HU-EMP-05 | Vencimiento automático y purga de datos personales al caducar | `CicloDeVidaDeVacanteTest` (6 casos) · `DepuracionDeBolsasTest` (21 casos) | ✅ |

### EP-07 · Directorio de artistas

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-50 | HU-ART-01 | Inscripción pública moderada, con foto validada por tipo real y video legítimo | `SolicitudesDeBolsaTest` (13 casos) · `Unit/VideoDeArtistaTest` | ✅ |
| RF-51 | HU-ART-02 | Solo las fichas publicadas salen en consultas públicas | `FichasDeBolsaTest::test_solo_las_fichas_publicadas_salen_en_las_consultas_publicas` | ✅ |
| RF-52 | HU-ART-03 | Moderación desde el panel, con aviso al solicitante | `ModeracionDeBolsasTest` (21 casos) | ✅ |

### EP-08 · Bolsa de proveedores

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-53 | HU-PRV-01 | Inscripción pública que crea ficha pendiente | `SolicitudesDeBolsaTest::test_la_inscripcion_de_proveedor_crea_una_ficha_pendiente` | ✅ |
| RF-54 | HU-PRV-02 | Listado público; el proveedor vencido no aparece aunque esté publicado | `SolicitudesDeBolsaTest::test_un_proveedor_vencido_tampoco_sale_aunque_este_publicado` | ✅ |
| RF-55 | HU-PRV-03 | Cobro por pertenecer a la bolsa | — | ➖ Fase II por decisión de la ERS v3. El campo de vigencia existe; la tarifa es competencia de la junta (**DPV-11**) |

### EP-09 · Beneficios y aliados

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-12 | HU-BEN-01 | Vitrina pública de aliados | `SitioPublicoTest::test_las_rutas_publicas_responden` | ✅ |
| RF-13 | HU-BEN-02 | El detalle del convenio solo con sesión de asociado | `FormulariosPublicosTest::test_el_asociado_ve_su_mora_y_el_detalle_privado_de_los_convenios` · `::test_el_detalle_de_convenio_no_aparece_en_el_sitio_publico` | ✅ |
| RF-14 | HU-BEN-03 | CRUD de aliados y beneficios sin código | `PanelCompletoTest` (Aliados, Beneficios) | ✅ |

### EP-10 · Estado de cartera

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-56 | HU-CAR-01 | Importación del CSV de la contadora, tolerante a formato real y sin borrar deuda por celda vacía | `ImportacionDeCarteraTest` (6 casos) · `AccionesDelPanelTest::test_la_plantilla_de_cartera_neutraliza_las_formulas_de_excel` | ✅ |
| RF-57 | HU-CAR-02 | El asociado ve su mora; nadie ve la ajena | `FormulariosPublicosTest::test_el_asociado_ve_su_mora_y_el_detalle_privado_de_los_convenios` · `FlujoDePagoTest::test_pagar_la_mensualidad_deja_la_cartera_al_dia` | ✅ |
| RF-58 | HU-CAR-03 | Recordatorios automáticos de pago | — | ➖ Fase II. No implementado; depende de **DPV-10** (alcance real del módulo, entrevista a la contadora pendiente) |

### EP-11 · Pagos en línea

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-32 | HU-PAG-01 | Pago por pasarela; la referencia no es enumerable; la pasarela simulada se niega fuera de local | `FlujoDePagoTest::test_la_referencia_de_pago_no_es_enumerable` · `::test_el_contenedor_rechaza_la_pasarela_simulada_fuera_de_local` | ✅ |
| RF-33 | HU-PAG-01 | Producción se habilita por configuración, sin tocar código | `FlujoDePagoTest::test_sin_payment_driver_el_contenedor_no_adivina_la_pasarela` · `::test_fuera_de_sandbox_una_llave_vacia_rechaza_cualquier_firma` | ✅ |
| RF-34 | HU-PAG-02 | Webhook con firma HMAC validada; idempotencia; monto y moneda verificados | `FlujoDePagoTest::test_el_webhook_de_bold_rechaza_una_firma_invalida` · `::test_aplicar_dos_veces_la_misma_confirmacion_no_duplica_efectos` · `::test_una_confirmacion_en_otra_moneda_no_se_aplica` | ✅ |
| RF-59 | HU-PAG-03 | Mensualidad tipo suscripción | — | ➖ Fase II por decisión de la ERS v3 |

### EP-12 · Contacto, afiliación y PQR

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-27 | HU-CON-01 | Contacto con habeas data, constancia y defensa antispam | `FormulariosPublicosTest::test_el_honeypot_descarta_el_envio_sin_dar_pistas` · `EvidenciaDelConsentimientoTest::test_escribir_al_gremio_guarda_la_evidencia_del_consentimiento` | ✅ |
| RF-28 | HU-CON-02 | La solicitud de afiliación queda registrada con su tipo | `FormulariosPublicosTest::test_la_afiliacion_se_guarda_como_mensaje_del_tipo_correcto` | ✅ |
| RF-29 | HU-CON-03 | PQR con radicado consecutivo y acuse de recibo | `FormulariosPublicosTest::test_una_pqr_genera_radicado_consecutivo_y_envia_acuse` · `::test_un_mensaje_de_contacto_normal_no_recibe_radicado` | ✅ |
| RF-30 | HU-CON-04 | Postulación como aliado, diferenciada de la afiliación | `FormulariosPublicosTest` (tipos de mensaje) | ✅ |
| RF-31 | *(transversal)* | Constancia de habeas data con fecha, hora y versión del texto | `EvidenciaDelConsentimientoTest` (7 casos, uno por formulario) | ✅ |

### EP-13 · Contenido editorial y observatorio

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-24, RF-26 | HU-EDI-02 | Publicación de noticias; el contenido se sanea antes de mostrarse | `SitioPublicoTest::test_el_contenido_del_boletin_se_sanea_antes_de_mostrarse` · `::test_el_json_ld_de_una_noticia_tampoco_se_puede_romper` | ✅ |
| RF-25 | HU-EDI-01 | Cifras del observatorio; cada gráfica calla si su muestra no alcanza el umbral | `Panel/ObservatorioTest` (20 casos) · `Panel/MetricasDelObservatorioTest` (21 casos) · `Panel/ObservatorioBaseVaciaTest` (3 casos) | ✅ |

### EP-14 · Panel de administración

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-35, RF-36 | HU-ADM-01 | Súper administrador con acceso total; subadministrador acotado | `PanelAdminTest` (5 casos) · `PermisosDeBolsaTest` (4 casos) | ✅ |
| RF-37 | HU-ADM-02 | El subadministrador no publica ni siquiera forzando el estado; la cola de pendientes pregunta a las policies | `FlujoDeAprobacionTest` (9 casos) · `AccionesDelPanelTest::test_aunque_la_secretaria_invoque_aprobar_a_la_fuerza_no_publica` · `Panel/ColaDePendientesTest` (9 casos) | ✅ |
| RF-38 | HU-ADM-03 | Listado, creación y edición de los 19 recursos; ninguna página del panel sin registrar | `PanelCompletoTest::test_no_quedan_paginas_del_panel_sin_registrar` + 3 métodos parametrizados sobre los 19 recursos | ✅ |
| RF-39 | HU-ADM-04 | Bitácora de actividad, incluida la de borrado automático de datos personales | `DepuracionDeBolsasTest::test_registra_en_la_bitacora_cuando_borra_datos` · `DepuracionDeInscripcionesTest::test_registra_en_la_bitacora_cuando_borra` | ✅ |
| RF-40 | HU-ADM-05 | Segundo factor obligatorio en el panel; sin él no se llega al escritorio | `LoginDelPanelTest` (10 casos) | ✅ |
| RF-61 | *(nuevo ERS v3)* | Certificado automático de afiliación | — | ➖ Fase II |
| RF-62 | *(nuevo ERS v3)* | Integración de contenidos desde redes sociales | — | ➖ Fase II |

---

## 4. Matriz de requisitos no funcionales

| RNF | Requisito | Prueba o evidencia | Estado |
|---|---|---|---|
| RNF-01 | Móvil primero | `MenuMovilTest` (2 casos) · `MovimientoTest::test_todo_hover_con_transform_tiene_puerta_tactil` | ⚠️ El marcado y el comportamiento táctil están probados; **falta verificación en dispositivos reales** (Android/iOS), que el cronograma exige en la S7 |
| RNF-02 | Portada en menos de 2,5 s | `medicion-de-rendimiento.md` — 78 mediciones con Chromium real, 3 corridas por ruta, caché en frío | ✅ **Medido el 18 de agosto: 972 ms de LCP en la portada sobre móvil 4G**, contra un techo de 2.500 ms. Las 12 rutas públicas cumplen en los dos perfiles y los dos temas. La más lenta es `/boletin` con 2.132 ms. ⚠️ Medido contra `localhost`: falta repetirlo contra el dominio real cuando exista despliegue |
| RNF-03 | SSL/HTTPS | `CabecerasDeSeguridadTest::test_la_cookie_de_sesion_se_marca_segura_en_produccion` | ❌ La aplicación está preparada, pero **no hay despliegue**: sin hosting no hay certificado que verificar (riesgo R-14) |
| RNF-04 | Habeas data en todos los formularios (Ley 1581/2012) | `EvidenciaDelConsentimientoTest` (7 casos) · `FormulariosPublicosTest` · `DepuracionDeBolsasTest`, `DepuracionDeInscripcionesTest`, `DepuracionDeMensajesTest` (35 casos de retención) | ✅ Cobertura por encima de lo exigido: además de capturar el consentimiento, se prueba que los datos **se borran solos** al vencer su plazo |
| RNF-05 | Imágenes optimizadas (.webp/.svg) | `SubidaDeImagenesTest` (11 casos, incluida la conversión webp y la extensión derivada del tipo real) | ✅ |
| RNF-06 | SEO | `SitioPublicoTest` (JSON-LD en cuatro tipos de ficha, a prueba de inyección) | ⚠️ Estructura verificada; posicionamiento real no medible sin dominio en producción |
| RNF-07 | Verificado en Android, iOS, tabletas y escritorio | — | ❌ Mismo hueco que RNF-01. Corresponde a la S7 del cronograma |
| RNF-08 | Contraseñas robustas, doble factor, respaldos | `PoliticaDeContrasenasTest` · `LoginDelPanelTest` · `LoginDeAsociadoTest` · `CabecerasDeSeguridadTest` | ⚠️ Contraseñas y 2FA cubiertos; **los respaldos periódicos dependen del hosting** y no existen todavía |
| RNF-09 | Nada quemado en código | `AccionesDelPanelTest::test_guardar_los_ajustes_actualiza_el_sitio` · `PanelCompletoTest` | ✅ |
| RNF-10 | Manual de marca aplicado | `Panel/TemaDelPanelTest` · `TemaClaroOscuroTest::test_ninguna_vista_publica_conserva_clases_de_tema_cableadas` | ✅ La prueba recorre las vistas y falla si reaparece un color cableado |
| RNF-11 | Commits descriptivos semanales | Historial de `Jsua3/asobares` | ✅ Evidencia documental, no automatizable. `origin/main` sincronizado al 18 de agosto |
| RNF-12 | Accesibilidad básica | `FocoVisibleTest` (6 casos, uno **recalcula el contraste**) · `ObjetivoTactilTest` · `Panel/ComponentesDelPanelTest::test_la_cola_marca_lo_urgente_con_algo_mas_que_color` · `MovimientoTest::test_el_movimiento_reducido_anula_el_desplazamiento_y_no_el_reloj` | ✅ **Cerrado el 19 de agosto de 2026.** El foco visible se restituyó en todo el sitio: el indicador mide **3,49:1 en tema claro y 5,15:1 en oscuro**, por encima del mínimo de 3:1 de WCAG 2.1 §1.4.11. Objetivos táctiles: **594 elementos interactivos verificados en 20 rutas a 320, 390 y 1280 px, ninguno por debajo de 44×44 px**; 26 quedan exceptuados por la propia norma (enlace dentro de una frase, o equivalente mayor al mismo destino). Se comprobó además con `elementFromPoint` que ningún objetivo ampliado le roba la pulsación a su vecino |
| RNF-13 | Escalabilidad a Fase II sin rehacer | `BancoDeTalentoTest` | ✅ |
| RNF-14 | Operación autónoma del panel por personal no técnico | `PanelCompletoTest` (19 recursos, listado + creación + edición) | ⚠️ El software responde; **la verificación contractual es la capacitación**: que el personal publique un asociado, un evento y una noticia sin ayuda. Pendiente (S8) |

---

## 5. Lo que esta matriz deja sin cubrir, y por qué

Un documento de pruebas que solo enumera lo que pasa no sirve para gestionar. Estos son los huecos reales, en orden de urgencia:

1. ~~**RNF-02 — Ningún dato de rendimiento.**~~ **Cerrado el 18 de agosto de 2026.** Se midieron las 12 rutas públicas con Chromium real, en móvil 4G estrangulado y escritorio, en los dos temas, tres corridas por combinación y con la caché desactivada: 78 mediciones, 0 errores. La portada pinta en **972 ms** contra un techo de 2.500. Informe completo en `medicion-de-rendimiento.md`. **Queda un residuo honesto:** la medición es contra `localhost`, así que no incluye la latencia real de red hasta un servidor — hay que repetirla tras el despliegue (bloqueado por R-14).
2. **RNF-01 y RNF-07 — Sin dispositivos reales.** Las pruebas verifican marcado y comportamiento; ninguna abre un teléfono. Es contenido explícito de la Semana 7.
3. **RNF-03 y RNF-08 (respaldos) — Bloqueados por el despliegue.** No son deuda técnica: dependen del riesgo R-14 (cuenta institucional del gremio pendiente de la junta).
4. ~~**RF-19 — El calendario de eventos.**~~ **Cerrado el 19 de agosto de 2026** por la vía de construirlo, no de negociarlo: existe la vista de calendario que pide el cronograma firmado, con 16 pruebas propias. Dos de ellas cubren defectos que no se ven: que la ficha por slug no capture la ruta del calendario —un evento con slug «calendario» daría 404 sin que nada avise— y que la rejilla no consulte dentro del bucle de celdas.
5. ~~**RNF-12 — Foco visible y objetivos táctiles.**~~ **Cerrado el 19 de agosto de 2026.** Cifras en la fila de RNF-12. Una excepción declarada y razonada: las pastillas de evento dentro de la rejilla mensual miden 27 px de alto. Es deliberado —la rejilla solo existe de `sm:` para arriba, donde el puntero es un ratón, y el mismo evento tiene su objetivo de 44 px en la agenda móvil— y supera el mínimo de 24×24 px que exige WCAG 2.5.8; los 44 px son el listón propio del proyecto, más exigente que la norma.
6. ~~**RF-60 — Sin cobertura.**~~ **Cerrado el 24 de agosto de 2026.** La tabla admitía el dato pero no probaba la vigencia; ahora `vigente_hasta` saca del sitio lo que venció, con prueba en las cuatro puertas por las que sale la guía —lista, selector de municipios, sitemap y **descarga del formato**, que era la única con consecuencia de seguridad—. **RF-05 sigue sin cobertura** a la espera de la decisión DPV-05.

**Los requisitos marcados ➖ no son huecos:** la ERS v3 los movió a Fase II con criterios de aceptación escritos, y la arquitectura los soporta sin migraciones destructivas (RNF-13, verificado).

---

## 6. Cómo reproducir estos resultados

```bash
# suite completa
php artisan test

# solo un módulo
php artisan test --filter=BolsaDeEmpleo

# con detalle de cada caso
php artisan test --testdox
```

**Método de verificación que se siguió durante el desarrollo, y que conviene mantener:** escribir la prueba no basta. De los defectos atrapados en el proyecto, cinco fueron pruebas en falso verde —aserciones sobre texto en vez de sobre comportamiento— que pasaban con el error reintroducido. Ninguna se detectó leyendo el código; todas salieron al **mutar el código a propósito y comprobar que la prueba se enteraba**. Toda prueba nueva de esta matriz debería pasar por ese filtro antes de contarse como cobertura.

---

*Documento elaborado como entregable de la Fase 4 del cronograma firmado por la dirección ejecutiva de ASOBARES Capítulo Quindío. Trazabilidad completa RF ↔ HU en el product backlog v2; definición de cada requisito en la ERS v3.0.*
