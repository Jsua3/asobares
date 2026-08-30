## 1. Objeto

Esta matriz relaciona cada requisito de la Especificación de requisitos de software ERS-ASOBARES-QUINDIO-V3.0 con la prueba automatizada que lo verifica, y declara de forma expresa los requisitos que aún no tienen cobertura. El cronograma firmado por la dirección ejecutiva la exige entre los entregables de cierre de la fase 4.

La suite de pruebas automatizadas es el instrumento de verificación; esta matriz es el registro legible de qué requisito está verificado, con qué prueba y qué queda pendiente. Un requisito sin prueba asociada se reporta como no verificado, aunque el código exista.

## 2. Convenciones

| Estado | Definición |
|---|---|
| Cubierto | Existe una prueba automatizada que falla si se rompe el comportamiento exigido. |
| Parcial | Existe prueba, pero no alcanza la totalidad del criterio de aceptación. Se indica qué parte falta. |
| Sin cobertura | No existe prueba automatizada. Se indica el motivo. |
| Fase II | Fuera del alcance de la primera entrega por decisión de la ERS v3.0. La estructura existe; el comportamiento no se promete en esta fase. |

Los nombres de la columna «Prueba» corresponden a clases y métodos del directorio `tests/` del repositorio. Cuando una clase agrupa varios casos, se indica su número entre paréntesis.

## 3. Resumen de cobertura

| Indicador | Valor |
|---|---|
| Archivos de prueba | 60 |
| Métodos de prueba | 592 |
| Casos ejecutados | 820 (los métodos con proveedor de datos se expanden en varios casos) |
| Resultado de la última ejecución verificada (25 de agosto de 2026) | 809 aprobados, 11 omitidos, 0 fallos |
| Aserciones | 2.904 |
| Duración de la suite completa | 348 s sobre el árbol de trabajo; 200 s sobre un clon limpio |
| Requisitos funcionales de la primera entrega con cobertura total o parcial | 52 de 53 |
| Requisitos no funcionales con cobertura total o parcial | 11 de 14 |

La cobertura de requisitos funcionales se cuenta por filas de la sección 4. La sección tiene 58 filas; cinco corresponden a requisitos diferidos a la fase II (RF-55, RF-58, RF-59, RF-61 y RF-62), lo que deja 53 filas en el alcance de la primera entrega. De ellas, 52 tienen cobertura total o parcial y una (RF-05) no tiene prueba.

Las 11 pruebas omitidas son deliberadas: corresponden a recursos del panel que no tienen página de creación ni de edición, porque sus registros nacen por otra vía. La cartera se alimenta por importación del archivo de la contadora, la postulación la crea el candidato desde el sitio público y la vacante la publica el asociado desde su portal. La omisión documenta que esas puertas están cerradas a propósito.

### 3.1 Registro de ejecuciones

| Fecha | Entorno | Casos | Aprobados | Omitidos | Fallos | Aserciones | Duración | Observación |
|---|---|---:|---:|---:|---:|---:|---:|---|
| 25 de agosto de 2026 | Clon limpio de `origin/main`, PHP 8.4.21 con `intl` y `gd` | 820 | 809 | 11 | 0 | 2.904 | 200 s | Reproducción de la corrida del mismo día fuera de la máquina de desarrollo. |
| 25 de agosto de 2026 | Árbol de trabajo, PHP 8.5.9 | 820 | 809 | 11 | 0 | 2.904 | 348 s | Revisión final de RF-60: cinco pruebas se corrigieron para que ejerciten lo que verifican y se añadió un caso. |
| 24 de agosto de 2026 | Árbol de trabajo, PHP 8.5.9 | 819 | 808 | 11 | 0 | 2.899 | 350 s | Cierre de RF-60: vigencia de la guía normativa en el modelo, en las salidas públicas y en el panel. |
| 23 de agosto de 2026 | Árbol de trabajo, PHP 8.5.9 | 791 | 780 | 11 | 0 | 2.818 | 267 s | Primera corrida que incluye la carga de la base real de asociados. |
| 20 de agosto de 2026 | Clon limpio de `origin/main`, PHP 8.4.21; `composer install` y `npm run build` desde cero | 759 | 748 | 11 | 0 | 2.733 | 193 s | Verificación de reproducibilidad fuera de la máquina de desarrollo. |
| 19 de agosto de 2026 | Árbol de trabajo | 747 | 736 | 11 | 0 | 2.719 | — | Calendario de eventos, foco visible y objetivos táctiles, configuración de despliegue. |
| 18 de agosto de 2026 | Árbol de trabajo | 599 | 588 | 11 | 0 | 1.699 | 169 s | Línea base al inicio de la fase 4. |

Toda cifra de este documento proviene de una ejecución real registrada en esta tabla. Si el código cambia antes de la entrega final, la ejecución debe repetirse y la tabla debe actualizarse.

## 4. Matriz de requisitos funcionales

### EP-01. Sitio institucional

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-01 | HU-INS-01 | La página de inicio responde y aplica el tema | `SitioPublicoTest::test_las_rutas_publicas_responden`, `TemaClaroOscuroTest` | Cubierto |
| RF-02 | HU-INS-02 | «Quiénes somos» responde y su contenido es editable desde el panel | `SitioPublicoTest`, `AccionesDelPanelTest::test_guardar_los_ajustes_actualiza_el_sitio` | Cubierto |
| RF-03, RF-04 | HU-INS-03 | Respaldo nacional, contacto y redes; los enlaces configurables no admiten esquemas peligrosos | `EnlacesDeAjustesTest` (4 casos), `AccionesDelPanelTest` | Cubierto |
| RF-05 | HU-INS-04 | Estructura normativa institucional | — | Sin cobertura. Prioridad baja; el contenido se sirve como página estática. Pendiente de la decisión DPV-05 sobre su posible duplicidad con la guía normativa. |

### EP-02. Directorio de asociados

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-06, RF-07 | HU-DIR-01 | Listado público con filtros por municipio y categoría; los filtros ofrecen opción vacía | `SitioPublicoTest::test_las_rutas_publicas_responden`, `FormulariosPublicosTest::test_el_directorio_no_necesita_opcion_vacia_porque_sus_filtros_ya_la_traen` | Cubierto |
| RF-08 | HU-DIR-02 | La ficha responde y un asociado sin publicar no es visible | `SitioPublicoTest::test_las_fichas_de_detalle_responden`, `::test_un_asociado_sin_publicar_no_es_visible` | Cubierto |
| RF-09 | HU-DIR-03 | Mapa interactivo que respeta la preferencia de movimiento reducido | `MovimientoTest::test_el_mapa_consulta_la_preferencia_de_movimiento` | Parcial. Se verifica el comportamiento del mapa frente a la preferencia de accesibilidad; el renderizado de los marcadores requiere navegador. |
| RF-10 | HU-DIR-04 | Alta y edición de asociados desde el panel sin modificar código | `AccionesDelPanelTest::test_crear_un_asociado_desde_el_formulario_del_panel`, `PanelCompletoTest` (19 recursos) | Cubierto |
| RF-11 | HU-DIR-05 | La ficha pública no muestra los campos marcados como internos | `SitioPublicoTest::test_la_ficha_publica_no_filtra_los_datos_internos_del_asociado` | Cubierto |

### EP-03. Guía normativa por municipio

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-15 | HU-GUI-01 | La guía responde por municipio y está protegida contra abuso | `Panel/ConsultasDeGuiaTest::test_visitar_la_guia_registra_la_consulta_del_municipio`, `SitioPublicoTest::test_la_guia_normativa_tiene_limite_de_peticiones` | Cubierto |
| RF-16 | HU-GUI-02 | Cada trámite lleva entidad responsable, pasos y costo | `Panel/ConsultasDeGuiaTest`, `PanelCompletoTest` (recurso RequisitoApertura) | Cubierto |
| RF-17 | HU-GUI-03 | Descarga de formatos, con métrica de uso y protección | `Panel/ConsultasDeGuiaTest::test_descargar_formato_valido_registra_la_consulta`, `::test_intentar_descargar_sin_adjunto_no_registra`, `::test_intentar_descargar_no_publicado_no_registra`, `SitioPublicoTest::test_descargar_formato_de_la_guia_tiene_limite_de_peticiones` | Cubierto |
| RF-18 | HU-GUI-04 | Gestión de municipios, trámites y formatos desde el panel | `PanelCompletoTest` (Municipios, RequisitoAperturas) | Cubierto |
| RF-60 | ERS v3.0 | Normativa vigente y decretos transitorios por municipio | `VigenciaDeLaGuiaTest`, `VigenciaEnElPanelTest` | Cubierto. Cada trámite registra la fuente y la fecha en que se verificó; los decretos transitorios caducan automáticamente y dejan de mostrarse en la lista, en el selector de municipios, en el mapa del sitio y en la descarga del formato. |

La tabla `consultas_guia` registra el uso del módulo sin datos personales; la prueba `Panel/ConsultasDeGuiaTest::test_la_tabla_no_guarda_ningun_dato_personal` lo verifica. Esa métrica anónima es la que el gremio puede presentar a las alcaldías.

### EP-04. Capacitaciones y eventos

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-19 | HU-EVE-01 | Listado de eventos próximos e históricos y vista de calendario | `CalendarioDeEventosTest` (16 casos), `SitioPublicoTest::test_las_rutas_publicas_responden` | Cubierto. La vista de calendario se construyó el 19 de agosto de 2026: rejilla mensual en escritorio y agenda por día en móvil, navegable entre meses sin JavaScript. Al construirla se corrigió un defecto previo en el listado de eventos de varios días. |
| RF-20 | HU-EVE-02 | Ficha con aforo; el cupo lleno bloquea la inscripción | `CuposDeEventoTest` (4 casos) | Cubierto |
| RF-21 | HU-EVE-03 | Inscripción con autorización de datos obligatoria y constancia | `FormulariosPublicosTest::test_la_inscripcion_exige_la_autorizacion_de_datos`, `EvidenciaDelConsentimientoTest::test_inscribirse_a_un_evento_guarda_la_evidencia_del_consentimiento` | Cubierto |
| RF-22 | HU-EVE-04 | Pago de la inscripción y confirmación solo con transacción aprobada | `FlujoDePagoTest` (26 casos), `ConfirmacionDeInscripcionTest` (5 casos) | Cubierto |
| RF-23 | HU-EVE-05 | Consulta de inscritos desde el panel | `PanelCompletoTest` (recurso Inscripciones) | Cubierto |

### EP-05. Cuentas de usuario y roles

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-41 | HU-CTA-01 | Alta de perfiles públicos con autorización de datos | `SolicitudesDeBolsaTest`, `BolsaDeEmpleoTest::test_dejar_el_perfil_dos_veces_actualiza_en_vez_de_duplicar` | Cubierto |
| RF-42 | HU-CTA-02 | Inicio de sesión del asociado sin fuga por tiempo, con bloqueo por cuenta y sin redirección abierta | `LoginDeAsociadoTest` (5 casos), `PoliticaDeContrasenasTest` (3 casos) | Cubierto |
| RF-43 | HU-CTA-03 | Consultar es público; publicar y ver lo privado exige sesión del rol correcto | `FormulariosPublicosTest::test_mi_cuenta_exige_el_rol_asociado`, `MisVacantesTest::test_el_equipo_del_gremio_no_entra_al_portal_del_asociado`, `PermisosDeBolsaTest`, `PanelAdminTest` | Cubierto |
| RF-44 | HU-CTA-04 | Activar módulos de fase II no exige migraciones destructivas | `BancoDeTalentoTest::test_el_aspirante_ya_no_cuelga_de_una_vacante` | Cubierto. Verificado con un caso real: el banco de talento se independizó de las vacantes sin rehacer la autenticación. |

### EP-06. Bolsa de empleo

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-45 | HU-EMP-01 | Solo el asociado propietario publica; la vacante nace pendiente de aprobación | `MisVacantesTest::test_la_vacante_recien_creada_queda_pendiente_de_aprobacion`, `::test_el_asociado_no_puede_publicar_su_vacante_mandando_el_estado`, `AutorizacionDeVacantesTest` (7 casos) | Cubierto |
| RF-46 | HU-EMP-02 | Listado público con filtros; solo vacantes publicadas y vigentes | `BolsaDeEmpleoTest` (14 casos), `FormulariosPublicosTest` | Cubierto |
| RF-47 | HU-EMP-03 | Postulación con autorización de datos, sin duplicados y a prueba de concurrencia | `BolsaDeEmpleoTest::test_una_condicion_de_carrera_al_postular_no_revienta_ni_duplica_el_correo`, `PostulacionTest` (4 casos), `EvidenciaDelConsentimientoTest` | Cubierto |
| RF-48 | HU-EMP-04 | El propietario recibe aviso y gestiona sus postulaciones; nadie ve las ajenas | `MisVacantesTest::test_un_directivo_que_tambien_es_asociado_no_ve_las_postulaciones_de_otro_establecimiento`, `CorreosDeBolsaTest` (6 casos) | Cubierto |
| RF-49 | HU-EMP-05 | Vencimiento automático y eliminación de datos personales al caducar | `CicloDeVidaDeVacanteTest` (6 casos), `DepuracionDeBolsasTest` (21 casos) | Cubierto |

### EP-07. Directorio de artistas

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-50 | HU-ART-01 | Inscripción pública moderada, con foto validada por tipo real y video legítimo | `SolicitudesDeBolsaTest` (13 casos), `Unit/VideoDeArtistaTest` | Cubierto |
| RF-51 | HU-ART-02 | Solo las fichas publicadas aparecen en consultas públicas | `FichasDeBolsaTest::test_solo_las_fichas_publicadas_salen_en_las_consultas_publicas` | Cubierto |
| RF-52 | HU-ART-03 | Moderación desde el panel, con aviso al solicitante | `ModeracionDeBolsasTest` (21 casos) | Cubierto |

### EP-08. Bolsa de proveedores

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-53 | HU-PRV-01 | Inscripción pública que crea una ficha pendiente | `SolicitudesDeBolsaTest::test_la_inscripcion_de_proveedor_crea_una_ficha_pendiente` | Cubierto |
| RF-54 | HU-PRV-02 | Listado público; el proveedor vencido no aparece aunque esté publicado | `SolicitudesDeBolsaTest::test_un_proveedor_vencido_tampoco_sale_aunque_este_publicado` | Cubierto |
| RF-55 | HU-PRV-03 | Cobro por pertenecer a la bolsa | — | Fase II. El campo de vigencia existe; la tarifa es competencia de la junta directiva (DPV-11). |

### EP-09. Beneficios y aliados

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-12 | HU-BEN-01 | Vitrina pública de aliados | `SitioPublicoTest::test_las_rutas_publicas_responden` | Cubierto |
| RF-13 | HU-BEN-02 | El detalle del convenio solo con sesión de asociado | `FormulariosPublicosTest::test_el_asociado_ve_su_mora_y_el_detalle_privado_de_los_convenios`, `::test_el_detalle_de_convenio_no_aparece_en_el_sitio_publico` | Cubierto |
| RF-14 | HU-BEN-03 | Gestión de aliados y beneficios desde el panel | `PanelCompletoTest` (Aliados, Beneficios) | Cubierto |

### EP-10. Estado de cartera

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-56 | HU-CAR-01 | Importación del archivo de la contadora, tolerante al formato real y sin borrar deuda por celda vacía | `ImportacionDeCarteraTest` (6 casos), `AccionesDelPanelTest::test_la_plantilla_de_cartera_neutraliza_las_formulas_de_excel` | Cubierto |
| RF-57 | HU-CAR-02 | El asociado ve su mora; nadie ve la ajena | `FormulariosPublicosTest::test_el_asociado_ve_su_mora_y_el_detalle_privado_de_los_convenios`, `FlujoDePagoTest::test_pagar_la_mensualidad_deja_la_cartera_al_dia` | Cubierto |
| RF-58 | HU-CAR-03 | Recordatorios automáticos de pago | — | Fase II. No implementado; depende de la decisión DPV-10 sobre el alcance del módulo. |

### EP-11. Pagos en línea

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-32 | HU-PAG-01 | Pago por pasarela; la referencia no es enumerable; la pasarela simulada se rechaza fuera del entorno local | `FlujoDePagoTest::test_la_referencia_de_pago_no_es_enumerable`, `::test_el_contenedor_rechaza_la_pasarela_simulada_fuera_de_local` | Cubierto |
| RF-33 | HU-PAG-01 | Producción se habilita por configuración, sin modificar código | `FlujoDePagoTest::test_sin_payment_driver_el_contenedor_no_adivina_la_pasarela`, `::test_fuera_de_sandbox_una_llave_vacia_rechaza_cualquier_firma` | Cubierto |
| RF-34 | HU-PAG-02 | Notificación de la pasarela con firma validada; idempotencia; monto y moneda verificados | `FlujoDePagoTest::test_el_webhook_de_bold_rechaza_una_firma_invalida`, `::test_aplicar_dos_veces_la_misma_confirmacion_no_duplica_efectos`, `::test_una_confirmacion_en_otra_moneda_no_se_aplica` | Cubierto |
| RF-59 | HU-PAG-03 | Mensualidad tipo suscripción | — | Fase II por decisión de la ERS v3.0. |

### EP-12. Contacto, afiliación y PQR

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-27 | HU-CON-01 | Contacto con autorización de datos, constancia y defensa contra correo no deseado | `FormulariosPublicosTest::test_el_honeypot_descarta_el_envio_sin_dar_pistas`, `EvidenciaDelConsentimientoTest::test_escribir_al_gremio_guarda_la_evidencia_del_consentimiento` | Cubierto |
| RF-28 | HU-CON-02 | La solicitud de afiliación queda registrada con su tipo | `FormulariosPublicosTest::test_la_afiliacion_se_guarda_como_mensaje_del_tipo_correcto` | Cubierto |
| RF-29 | HU-CON-03 | PQR con radicado consecutivo y acuse de recibo | `FormulariosPublicosTest::test_una_pqr_genera_radicado_consecutivo_y_envia_acuse`, `::test_un_mensaje_de_contacto_normal_no_recibe_radicado` | Cubierto |
| RF-30 | HU-CON-04 | Postulación como aliado, diferenciada de la afiliación | `FormulariosPublicosTest` (tipos de mensaje) | Cubierto |
| RF-31 | Transversal | Constancia de autorización de datos con fecha, hora y versión del texto | `EvidenciaDelConsentimientoTest` (7 casos, uno por formulario) | Cubierto |

### EP-13. Contenido editorial y observatorio

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-24, RF-26 | HU-EDI-02 | Publicación de noticias; el contenido se sanea antes de mostrarse | `SitioPublicoTest::test_el_contenido_del_boletin_se_sanea_antes_de_mostrarse`, `::test_el_json_ld_de_una_noticia_tampoco_se_puede_romper` | Cubierto |
| RF-25 | HU-EDI-01 | Cifras del observatorio; cada gráfica se oculta si su muestra no alcanza el umbral | `Panel/ObservatorioTest` (20 casos), `Panel/MetricasDelObservatorioTest` (21 casos), `Panel/ObservatorioBaseVaciaTest` (3 casos) | Cubierto |

### EP-14. Panel de administración

| RF | HU | Criterio verificado | Prueba | Estado |
|---|---|---|---|---|
| RF-35, RF-36 | HU-ADM-01 | Superadministrador con acceso total; subadministrador acotado | `PanelAdminTest` (5 casos), `PermisosDeBolsaTest` (4 casos) | Cubierto |
| RF-37 | HU-ADM-02 | El subadministrador no publica ni forzando el estado; la cola de pendientes consulta las políticas | `FlujoDeAprobacionTest` (9 casos), `AccionesDelPanelTest::test_aunque_la_secretaria_invoque_aprobar_a_la_fuerza_no_publica`, `Panel/ColaDePendientesTest` (9 casos) | Cubierto |
| RF-38 | HU-ADM-03 | Listado, creación y edición de los 19 recursos; ninguna página del panel sin registrar | `PanelCompletoTest::test_no_quedan_paginas_del_panel_sin_registrar` y 3 métodos parametrizados sobre los 19 recursos | Cubierto |
| RF-39 | HU-ADM-04 | Bitácora de actividad, incluida la eliminación automática de datos personales | `DepuracionDeBolsasTest::test_registra_en_la_bitacora_cuando_borra_datos`, `DepuracionDeInscripcionesTest::test_registra_en_la_bitacora_cuando_borra` | Cubierto |
| RF-40 | HU-ADM-05 | Segundo factor obligatorio en el panel; sin él no se accede al escritorio | `LoginDelPanelTest` (10 casos) | Cubierto |
| RF-61 | ERS v3.0 | Certificado automático de afiliación | — | Fase II. |
| RF-62 | ERS v3.0 | Integración de contenidos desde redes sociales | — | Fase II. |

## 5. Matriz de requisitos no funcionales

| RNF | Requisito | Prueba o evidencia | Estado |
|---|---|---|---|
| RNF-01 | Diseño para móvil primero | `MenuMovilTest` (2 casos), `MovimientoTest::test_todo_hover_con_transform_tiene_puerta_tactil` | Parcial. El marcado y el comportamiento táctil están probados; falta la verificación en dispositivos reales (Android e iOS), prevista en la semana 7 del cronograma. |
| RNF-02 | Página principal en menos de 2,5 s | Anexo G (78 mediciones con Chromium, 3 corridas por ruta, caché desactivada) | Cubierto. Medición del 18 de agosto de 2026: 972 ms de LCP en la portada sobre móvil 4G frente al tope de 2.500 ms; las doce rutas públicas cumplen en ambos perfiles y temas; la más lenta es `/boletin` con 2.132 ms. La medición se hizo contra el servidor local y debe repetirse contra el dominio cuando exista despliegue. |
| RNF-03 | SSL/HTTPS | `CabecerasDeSeguridadTest::test_la_cookie_de_sesion_se_marca_segura_en_produccion` | Sin cobertura. La aplicación está preparada, pero sin despliegue no hay certificado que verificar (riesgo R-14). |
| RNF-04 | Autorización de datos en todos los formularios (Ley 1581 de 2012) | `EvidenciaDelConsentimientoTest` (7 casos), `FormulariosPublicosTest`, `DepuracionDeBolsasTest`, `DepuracionDeInscripcionesTest`, `DepuracionDeMensajesTest` (35 casos de retención) | Cubierto. Además del consentimiento, se verifica que los datos se eliminan automáticamente al vencer su plazo. |
| RNF-05 | Imágenes optimizadas (webp y svg) | `SubidaDeImagenesTest` (11 casos, incluida la conversión a webp y la extensión derivada del tipo real) | Cubierto |
| RNF-06 | SEO | `SitioPublicoTest` (JSON-LD en cuatro tipos de ficha, a prueba de inyección) | Parcial. La estructura está verificada; el posicionamiento real no se puede medir sin dominio en producción. |
| RNF-07 | Verificado en Android, iOS, tabletas y escritorio | — | Sin cobertura. Corresponde a la semana 7 del cronograma. |
| RNF-08 | Contraseñas robustas, segundo factor y respaldos | `PoliticaDeContrasenasTest`, `LoginDelPanelTest`, `LoginDeAsociadoTest`, `CabecerasDeSeguridadTest` | Parcial. Contraseñas y segundo factor cubiertos; los respaldos periódicos dependen del alojamiento, que aún no existe. |
| RNF-09 | Ningún dato de negocio escrito en el código | `AccionesDelPanelTest::test_guardar_los_ajustes_actualiza_el_sitio`, `PanelCompletoTest` | Cubierto |
| RNF-10 | Manual de marca aplicado | `Panel/TemaDelPanelTest`, `TemaClaroOscuroTest::test_ninguna_vista_publica_conserva_clases_de_tema_cableadas` | Cubierto. La prueba recorre las vistas y falla si reaparece un color escrito directamente en el código. |
| RNF-11 | Confirmaciones descriptivas semanales | Historial del repositorio `Jsua3/asobares` | Cubierto. Evidencia documental, no automatizable. |
| RNF-12 | Accesibilidad básica | `FocoVisibleTest` (6 casos, uno recalcula el contraste), `ObjetivoTactilTest`, `Panel/ComponentesDelPanelTest::test_la_cola_marca_lo_urgente_con_algo_mas_que_color`, `MovimientoTest::test_el_movimiento_reducido_anula_el_desplazamiento_y_no_el_reloj` | Cubierto. Cerrado el 19 de agosto de 2026: el indicador de foco mide 3,49:1 en tema claro y 5,15:1 en tema oscuro, por encima del mínimo de 3:1 (WCAG 2.1, criterio 1.4.11); 594 objetivos táctiles verificados en 20 rutas a 320, 390 y 1280 px, ninguno por debajo de 44 × 44 px; 26 exceptuados por la propia norma. |
| RNF-13 | Escalabilidad a fase II sin rehacer | `BancoDeTalentoTest` | Cubierto |
| RNF-14 | Operación autónoma del panel por personal no técnico | `PanelCompletoTest` (19 recursos: listado, creación y edición) | Parcial. El software responde; la verificación contractual es la capacitación de la semana 8, con constancia en el Acta 02. |

## 6. Vacíos de cobertura declarados

| Requisito | Situación | Depende de |
|---|---|---|
| RF-05 | Sin prueba propia | Decisión DPV-05 |
| RNF-01 y RNF-07 | Sin verificación en dispositivos reales | Semana 7 del cronograma |
| RNF-02 | Medido contra el servidor local, sin latencia de red | Repetir la medición después del despliegue |
| RNF-03 y RNF-08 (respaldos) | Bloqueados por la ausencia de despliegue | Riesgo R-14: cuenta institucional de alojamiento |
| RNF-06 | Posicionamiento no medible sin dominio | Publicación del sitio |
| RNF-14 | Pendiente la capacitación al personal | Semana 8 del cronograma (Acta 02) |

Durante la fase 4 se cerraron cuatro vacíos que esta matriz declaraba en su primera versión: RNF-02 (medición de rendimiento, 18 de agosto), RF-19 (vista de calendario, 19 de agosto), RNF-12 (foco visible y objetivos táctiles, 19 de agosto) y RF-60 (vigencia de la guía normativa, 24 de agosto).

Los requisitos marcados como Fase II no se consideran vacíos: la ERS v3.0 los difirió con criterios de aceptación escritos, y la arquitectura los admite sin migraciones destructivas, como verifica RNF-13.

## 7. Reproducción de los resultados

Con el repositorio clonado y las dependencias instaladas (`composer install`, `npm run build`), la suite se ejecuta desde la raíz del proyecto:

```
php artisan test                        # suite completa
php artisan test --filter=BolsaDeEmpleo # un solo módulo
php artisan test --testdox              # con el detalle de cada caso
```

Durante el desarrollo se detectó que algunas pruebas pasaban sin ejercitar el comportamiento que decían verificar. Por esa razón, cada prueba nueva se valida alterando el código a propósito y comprobando que la prueba falla, antes de contarla como cobertura en esta matriz.
