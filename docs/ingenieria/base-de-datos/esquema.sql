-- Esquema sin datos · Plataforma Web ASOBARES Capitulo Quindio
-- Generado el 18 de agosto de 2026

CREATE TABLE "activity_log" ("id" integer primary key autoincrement not null, "log_name" varchar, "description" text not null, "subject_type" varchar, "subject_id" integer, "event" varchar, "causer_type" varchar, "causer_id" integer, "attribute_changes" text, "properties" text, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "aliados" ("id" integer primary key autoincrement not null, "nombre" varchar not null, "logo" varchar, "url" varchar, "descripcion" text, "detalle_convenio" text, "orden" integer not null default '0', "estado" varchar not null default 'borrador', "activo" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime);

CREATE TABLE "artistas" ("id" integer primary key autoincrement not null, "nombre" varchar not null, "slug" varchar not null, "tipo" varchar not null default ('dj'), "genero_musical" varchar, "descripcion" text, "tarifa_desde" numeric, "video_url" varchar, "whatsapp" varchar, "instagram_url" varchar, "foto" varchar, "municipio_id" integer, "estado" varchar not null default ('borrador'), "created_at" datetime, "updated_at" datetime, "user_id" integer, "correo" varchar, "acepta_datos" tinyint(1) not null default '0', "consentimiento_at" datetime, "consentimiento_ip" varchar, "consentimiento_agente" varchar, "consentimiento_politica" varchar, foreign key("municipio_id") references municipios("id") on delete set null on update cascade, foreign key("user_id") references "users"("id") on delete set null);

CREATE TABLE "asociados" ("id" integer primary key autoincrement not null, "nombre" varchar not null, "slug" varchar not null, "categoria_id" integer not null, "municipio_id" integer not null, "descripcion" text, "direccion" varchar, "whatsapp" varchar, "instagram_url" varchar, "sitio_web" varchar, "google_maps_url" varchar, "tripadvisor_url" varchar, "horario" varchar, "lat" numeric, "lng" numeric, "foto_portada" varchar, "destacado" tinyint(1) not null default '0', "estado" varchar not null default 'borrador', "representante" varchar, "correo_interno" varchar, "telefono_interno" varchar, "fecha_afiliacion" date, "notas_internas" text, "created_at" datetime, "updated_at" datetime, foreign key("categoria_id") references "categorias"("id") on delete restrict on update cascade, foreign key("municipio_id") references "municipios"("id") on delete restrict on update cascade);

CREATE TABLE "aspirantes" ("id" integer primary key autoincrement not null, "nombre" varchar not null, "correo" varchar not null, "telefono" varchar, "cargo_interes" varchar not null, "categoria_cargo" varchar not null default 'otros', "experiencia" text, "estado" varchar not null default 'nuevo', "acepta_datos" tinyint(1) not null default '0', "consentimiento_at" datetime, "created_at" datetime, "updated_at" datetime, "consentimiento_ip" varchar, "consentimiento_agente" varchar, "consentimiento_politica" varchar);

CREATE TABLE "beneficios" ("id" integer primary key autoincrement not null, "titulo" varchar not null, "descripcion" text not null, "icono" varchar not null default 'heroicon-o-check-badge', "orden" integer not null default '0', "created_at" datetime, "updated_at" datetime);

CREATE TABLE "cache" ("key" varchar not null, "value" text not null, "expiration" integer not null, primary key ("key"));

CREATE TABLE "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" integer not null, primary key ("key"));

CREATE TABLE "carteras" ("id" integer primary key autoincrement not null, "asociado_id" integer not null, "saldo_pendiente" numeric not null default '0', "meses_mora" integer not null default '0', "ultimo_pago_at" date, "actualizado_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("asociado_id") references "asociados"("id") on delete cascade on update cascade);

CREATE TABLE "categorias" ("id" integer primary key autoincrement not null, "nombre" varchar not null, "slug" varchar not null, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "consultas_guia" ("id" integer primary key autoincrement not null, "municipio_id" integer not null, "requisito_apertura_id" integer, "created_at" datetime, "updated_at" datetime, foreign key("municipio_id") references "municipios"("id") on delete cascade, foreign key("requisito_apertura_id") references "requisitos_apertura"("id") on delete set null);

CREATE TABLE "eventos" ("id" integer primary key autoincrement not null, "titulo" varchar not null, "slug" varchar not null, "tipo" varchar not null default 'evento', "descripcion" text, "lugar" varchar, "fecha_inicio" datetime not null, "fecha_fin" datetime, "imagen" varchar, "cupos" integer, "precio" numeric not null default '0', "permite_inscripcion" tinyint(1) not null default '1', "enlace_externo" varchar, "estado" varchar not null default 'borrador', "created_at" datetime, "updated_at" datetime);

CREATE TABLE "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" varchar not null, "queue" varchar not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);

CREATE TABLE "iniciativas" ("id" integer primary key autoincrement not null, "nombre" varchar not null, "slug" varchar not null, "resumen" varchar not null, "descripcion" text, "estado_iniciativa" varchar not null default 'formulacion', "linea" varchar, "lugar" varchar, "orden" integer not null default '0', "estado" varchar not null default 'borrador', "created_at" datetime, "updated_at" datetime);

CREATE TABLE "inscripciones" ("id" integer primary key autoincrement not null, "evento_id" integer not null, "nombre" varchar not null, "correo" varchar not null, "telefono" varchar, "establecimiento" varchar, "acepta_datos" tinyint(1) not null default ('0'), "consentimiento_at" datetime, "estado" varchar not null default ('registrada'), "transaccion_id" integer, "created_at" datetime, "updated_at" datetime, "consentimiento_ip" varchar, "consentimiento_agente" varchar, "consentimiento_politica" varchar, foreign key("evento_id") references eventos("id") on delete cascade on update cascade, foreign key("transaccion_id") references "transacciones"("id") on delete set null on update cascade);

CREATE TABLE "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text, "cancelled_at" integer, "created_at" integer not null, "finished_at" integer, primary key ("id"));

CREATE TABLE "jobs" ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);

CREATE TABLE "media" ("id" integer primary key autoincrement not null, "model_type" varchar not null, "model_id" integer not null, "uuid" varchar, "collection_name" varchar not null, "name" varchar not null, "file_name" varchar not null, "mime_type" varchar, "disk" varchar not null, "conversions_disk" varchar, "size" integer not null, "manipulations" text not null, "custom_properties" text not null, "generated_conversions" text not null, "responsive_images" text not null, "order_column" integer, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "mensajes" ("id" integer primary key autoincrement not null, "tipo" varchar not null default 'contacto', "nombre" varchar not null, "correo" varchar not null, "telefono" varchar, "mensaje" text not null, "acepta_datos" tinyint(1) not null default '0', "consentimiento_at" datetime, "radicado" varchar, "estado" varchar not null default 'nuevo', "nota_respuesta" text, "respondido_at" datetime, "created_at" datetime, "updated_at" datetime, "consentimiento_ip" varchar, "consentimiento_agente" varchar, "consentimiento_politica" varchar);

CREATE TABLE "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);

CREATE TABLE "model_has_permissions" ("permission_id" integer not null, "model_type" varchar not null, "model_id" integer not null, foreign key("permission_id") references "permissions"("id") on delete cascade, primary key ("permission_id", "model_id", "model_type"));

CREATE TABLE "model_has_roles" ("role_id" integer not null, "model_type" varchar not null, "model_id" integer not null, foreign key("role_id") references "roles"("id") on delete cascade, primary key ("role_id", "model_id", "model_type"));

CREATE TABLE "municipios" ("id" integer primary key autoincrement not null, "nombre" varchar not null, "slug" varchar not null, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "noticias" ("id" integer primary key autoincrement not null, "titulo" varchar not null, "slug" varchar not null, "extracto" text, "contenido" text, "imagen" varchar, "categoria" varchar not null default 'noticia', "publicado_at" datetime, "estado" varchar not null default 'borrador', "created_at" datetime, "updated_at" datetime);

CREATE TABLE "notifications" ("id" varchar not null, "type" varchar not null, "notifiable_type" varchar not null, "notifiable_id" integer not null, "data" text not null, "read_at" datetime, "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email"));

CREATE TABLE "permissions" ("id" integer primary key autoincrement not null, "name" varchar not null, "guard_name" varchar not null, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "postulaciones" ("id" integer primary key autoincrement not null, "vacante_id" integer not null, "nombre" varchar not null, "correo" varchar not null, "telefono" varchar, "experiencia" text, "estado" varchar not null default 'nuevo', "acepta_datos" tinyint(1) not null default '0', "consentimiento_at" datetime, "created_at" datetime, "updated_at" datetime, "consentimiento_ip" varchar, "consentimiento_agente" varchar, "consentimiento_politica" varchar, foreign key("vacante_id") references "vacantes"("id") on delete cascade on update cascade);

CREATE TABLE "proveedores" ("id" integer primary key autoincrement not null, "nombre" varchar not null, "slug" varchar not null, "categoria_proveedor" varchar not null default ('otros'), "descripcion" text, "whatsapp" varchar, "correo" varchar, "municipio_id" integer, "visible_hasta" date, "estado" varchar not null default ('borrador'), "created_at" datetime, "updated_at" datetime, "user_id" integer, "acepta_datos" tinyint(1) not null default '0', "consentimiento_at" datetime, "consentimiento_ip" varchar, "consentimiento_agente" varchar, "consentimiento_politica" varchar, foreign key("municipio_id") references municipios("id") on delete set null on update cascade, foreign key("user_id") references "users"("id") on delete set null);

CREATE TABLE "requisitos_apertura" ("id" integer primary key autoincrement not null, "municipio_id" integer not null, "entidad" varchar not null, "descripcion" text, "checklist" text, "enlace_externo" varchar, "adjunto" varchar, "adjunto_nombre" varchar, "costo_aproximado" numeric, "orden" integer not null default '0', "estado" varchar not null default 'borrador', "created_at" datetime, "updated_at" datetime, foreign key("municipio_id") references "municipios"("id") on delete cascade on update cascade);

CREATE TABLE "role_has_permissions" ("permission_id" integer not null, "role_id" integer not null, foreign key("permission_id") references "permissions"("id") on delete cascade, foreign key("role_id") references "roles"("id") on delete cascade, primary key ("permission_id", "role_id"));

CREATE TABLE "roles" ("id" integer primary key autoincrement not null, "name" varchar not null, "guard_name" varchar not null, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "sessions" ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));

CREATE TABLE "settings" ("id" integer primary key autoincrement not null, "clave" varchar not null, "valor" text, "tipo" varchar not null default 'string', "grupo" varchar not null default 'general', "etiqueta" varchar, "created_at" datetime, "updated_at" datetime);

CREATE TABLE sqlite_sequence(name,seq);

CREATE TABLE "transacciones" ("id" integer primary key autoincrement not null, "referencia" varchar not null, "concepto" varchar not null, "inscripcion_id" integer, "asociado_id" integer, "monto" numeric not null, "moneda" varchar not null default 'COP', "estado" varchar not null default 'pendiente', "metodo" varchar not null default 'pse', "payload" text, "created_at" datetime, "updated_at" datetime, foreign key("inscripcion_id") references "inscripciones"("id") on delete set null on update cascade, foreign key("asociado_id") references "asociados"("id") on delete set null on update cascade);

CREATE TABLE "users" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, "asociado_id" integer, "app_authentication_secret" text, "app_authentication_recovery_codes" text, "has_email_authentication" tinyint(1) not null default '0', foreign key("asociado_id") references "asociados"("id") on delete set null on update cascade);

CREATE TABLE "vacantes" ("id" integer primary key autoincrement not null, "asociado_id" integer not null, "cargo" varchar not null, "tipo" varchar not null default 'tiempo_completo', "descripcion" text, "franja_horaria" varchar, "whatsapp_contacto" varchar, "estado" varchar not null default 'borrador', "created_at" datetime, "updated_at" datetime, "fecha_limite" date, "cerrada_at" datetime, "motivo_devolucion" text, "categoria_cargo" varchar not null default 'otros', foreign key("asociado_id") references "asociados"("id") on delete cascade on update cascade);

CREATE INDEX "activity_log_log_name_index" on "activity_log" ("log_name");

CREATE INDEX "aliados_estado_activo_orden_index" on "aliados" ("estado", "activo", "orden");

CREATE INDEX "artistas_estado_tipo_index" on "artistas" ("estado", "tipo");

CREATE UNIQUE INDEX "artistas_slug_unique" on "artistas" ("slug");

CREATE INDEX "asociados_estado_destacado_index" on "asociados" ("estado", "destacado");

CREATE INDEX "asociados_municipio_id_categoria_id_index" on "asociados" ("municipio_id", "categoria_id");

CREATE UNIQUE INDEX "asociados_slug_unique" on "asociados" ("slug");

CREATE INDEX "aspirantes_categoria_cargo_index" on "aspirantes" ("categoria_cargo");

CREATE UNIQUE INDEX "aspirantes_correo_unique" on "aspirantes" ("correo");

CREATE INDEX "beneficios_orden_index" on "beneficios" ("orden");

CREATE INDEX "cache_expiration_index" on "cache" ("expiration");

CREATE INDEX "cache_locks_expiration_index" on "cache_locks" ("expiration");

CREATE UNIQUE INDEX "carteras_asociado_id_unique" on "carteras" ("asociado_id");

CREATE INDEX "carteras_meses_mora_index" on "carteras" ("meses_mora");

CREATE UNIQUE INDEX "categorias_slug_unique" on "categorias" ("slug");

CREATE INDEX "causer" on "activity_log" ("causer_type", "causer_id");

CREATE INDEX "consultas_guia_municipio_id_created_at_index" on "consultas_guia" ("municipio_id", "created_at");

CREATE INDEX "eventos_estado_fecha_inicio_index" on "eventos" ("estado", "fecha_inicio");

CREATE UNIQUE INDEX "eventos_slug_unique" on "eventos" ("slug");

CREATE INDEX "failed_jobs_connection_queue_failed_at_index" on "failed_jobs" ("connection", "queue", "failed_at");

CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");

CREATE INDEX "iniciativas_estado_iniciativa_index" on "iniciativas" ("estado_iniciativa");

CREATE INDEX "iniciativas_estado_orden_index" on "iniciativas" ("estado", "orden");

CREATE UNIQUE INDEX "iniciativas_slug_unique" on "iniciativas" ("slug");

CREATE INDEX "inscripciones_evento_id_estado_index" on "inscripciones" ("evento_id", "estado");

CREATE INDEX "inscripciones_transaccion_id_index" on "inscripciones" ("transaccion_id");

CREATE INDEX "jobs_queue_index" on "jobs" ("queue");

CREATE INDEX "media_model_type_model_id_index" on "media" ("model_type", "model_id");

CREATE INDEX "media_order_column_index" on "media" ("order_column");

CREATE UNIQUE INDEX "media_uuid_unique" on "media" ("uuid");

CREATE UNIQUE INDEX "mensajes_radicado_unique" on "mensajes" ("radicado");

CREATE INDEX "mensajes_tipo_estado_index" on "mensajes" ("tipo", "estado");

CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions" ("model_id", "model_type");

CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles" ("model_id", "model_type");

CREATE UNIQUE INDEX "municipios_slug_unique" on "municipios" ("slug");

CREATE INDEX "noticias_categoria_index" on "noticias" ("categoria");

CREATE INDEX "noticias_estado_publicado_at_index" on "noticias" ("estado", "publicado_at");

CREATE UNIQUE INDEX "noticias_slug_unique" on "noticias" ("slug");

CREATE INDEX "notifications_notifiable_type_notifiable_id_index" on "notifications" ("notifiable_type", "notifiable_id");

CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions" ("name", "guard_name");

CREATE INDEX "postulaciones_estado_index" on "postulaciones" ("estado");

CREATE UNIQUE INDEX "postulaciones_vacante_id_correo_unique" on "postulaciones" ("vacante_id", "correo");

CREATE INDEX "proveedores_estado_categoria_proveedor_index" on "proveedores" ("estado", "categoria_proveedor");

CREATE UNIQUE INDEX "proveedores_slug_unique" on "proveedores" ("slug");

CREATE INDEX "proveedores_visible_hasta_index" on "proveedores" ("visible_hasta");

CREATE INDEX "requisitos_apertura_estado_index" on "requisitos_apertura" ("estado");

CREATE INDEX "requisitos_apertura_municipio_id_orden_index" on "requisitos_apertura" ("municipio_id", "orden");

CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles" ("name", "guard_name");

CREATE INDEX "sessions_last_activity_index" on "sessions" ("last_activity");

CREATE INDEX "sessions_user_id_index" on "sessions" ("user_id");

CREATE UNIQUE INDEX "settings_clave_unique" on "settings" ("clave");

CREATE INDEX "settings_grupo_index" on "settings" ("grupo");

CREATE INDEX "subject" on "activity_log" ("subject_type", "subject_id");

CREATE INDEX "transacciones_created_at_index" on "transacciones" ("created_at");

CREATE INDEX "transacciones_estado_concepto_index" on "transacciones" ("estado", "concepto");

CREATE UNIQUE INDEX "transacciones_referencia_unique" on "transacciones" ("referencia");

CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");

CREATE INDEX "vacantes_estado_cargo_index" on "vacantes" ("estado", "cargo");

CREATE INDEX "vacantes_estado_categoria_cargo_index" on "vacantes" ("estado", "categoria_cargo");

CREATE INDEX "vacantes_fecha_limite_index" on "vacantes" ("fecha_limite");

