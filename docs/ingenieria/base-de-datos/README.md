# Base de datos exportada — Plataforma Web ASOBARES Capítulo Quindío

**Entregable final del cronograma firmado:** *«Código fuente del proyecto y base de datos exportada»*.
**Generado:** 18 de agosto de 2026 · repositorio `main` en `4f15d24` · motor **SQLite 3**

---

## Qué hay aquí

| Archivo | Contenido | Tamaño |
|---|---|---|
| `esquema.sql` | Solo la estructura: 37 tablas con sus índices y claves foráneas. Sin un solo dato | 19 KB |
| `asobares-demo.sql` | Volcado completo: estructura **más** los datos de demostración sembrados | 277 KB |

Ambos se generaron desde una base recién reconstruida con `php artisan migrate:fresh --seed`, de modo que el contenido corresponde exactamente a las semillas del repositorio en el commit indicado.

## Cómo restaurarla

```bash
sqlite3 database/database.sqlite < docs/ingenieria/base-de-datos/asobares-demo.sql
```

O, sin necesidad de ningún volcado, reconstruyéndola desde el propio proyecto — que es la vía recomendada mientras el código esté disponible:

```bash
php artisan migrate:fresh --seed
```

> El volcado existe porque es un entregable contractual y porque congela el estado exacto de la entrega. Para trabajar en el día a día, reconstruir desde las migraciones siempre es preferible.

## Portabilidad a PostgreSQL

El volcado es dialecto SQLite y **no se puede cargar tal cual en PostgreSQL**. No hace falta: las migraciones del proyecto son portables y se verificaron contra **PostgreSQL 17** el 14 de agosto de 2026, con la suite completa en verde. En producción la base se crea con `php artisan migrate`, no restaurando este archivo.

## Inventario de tablas

37 tablas · **1.526 filas** de datos de demostración.

### Dominio del gremio

| Tabla | Columnas | Filas | Qué guarda |
|---|---:|---:|---|
| `asociados` | 25 | 24 | Establecimientos afiliados, con sus campos públicos e internos separados |
| `municipios` | 5 | 8 | Municipios del Quindío con presencia |
| `categorias` | 5 | 6 | Bar, gastrobar, café, discoteca, restaurante bar, rooftop |
| `carteras` | 8 | 24 | Estado de cuenta por asociado (se alimenta por importación de CSV) |
| `transacciones` | 12 | 182 | Historial de pagos: afiliación, eventos y mensualidades |
| `aliados` | 11 | 6 | Marcas con convenio; el detalle es privado |
| `beneficios` | 7 | 5 | Los cinco beneficios institucionales del afiliado |
| `iniciativas` | 12 | 5 | Programas del gremio |

### Guía normativa *(módulo insignia)*

| Tabla | Columnas | Filas | Qué guarda |
|---|---:|---:|---|
| `requisitos_apertura` | 13 | 18 | Trámites por municipio, con checklist, costo y formato descargable |
| `consultas_guia` | 5 | 732 | Métrica **anónima** de uso: ni IP, ni agente, ni sesión |

### Bolsas del sector

| Tabla | Columnas | Filas | Qué guarda |
|---|---:|---:|---|
| `vacantes` | 14 | 7 | Bolsa de empleo; las publica el asociado, el gremio solo modera |
| `postulaciones` | 14 | 4 | Quien se postula a una vacante concreta |
| `aspirantes` | 15 | 7 | Banco de talento: una persona, un registro |
| `artistas` | 22 | 9 | DJs, bandas y solistas, por inscripción pública moderada |
| `proveedores` | 18 | 11 | Proveedores del sector, con vigencia por `visible_hasta` |

### Contenido y contacto

| Tabla | Columnas | Filas | Qué guarda |
|---|---:|---:|---|
| `eventos` | 16 | 6 | Solo eventos del gremio |
| `inscripciones` | 15 | 8 | Inscripciones a eventos, con habeas data |
| `noticias` | 11 | 7 | Boletín de baja frecuencia |
| `mensajes` | 17 | 10 | Contacto, afiliación y PQR con radicado consecutivo |
| `settings` | 8 | 65 | Textos institucionales: nada quemado en las vistas (RNF-09) |
| `media` | 18 | 18 | Imágenes gestionadas por MediaLibrary |

### Acceso y auditoría

| Tabla | Columnas | Filas | Qué guarda |
|---|---:|---:|---|
| `users` | 12 | 3 | Las tres cuentas de demostración |
| `roles` · `permissions` | 5 · 5 | 3 · 80 | `super_admin`, `subadmin`, `asociado` y sus permisos |
| `role_has_permissions` | 2 | 127 | Asignación de permisos por rol |
| `model_has_roles` | 3 | 3 | Rol de cada usuario |
| `activity_log` | 12 | 115 | Bitácora: quién hizo qué y cuándo |

Las once tablas restantes son infraestructura de Laravel (`migrations`, `cache`, `jobs`, `sessions`, `notifications`, `failed_jobs`, `job_batches`, `cache_locks`, `password_reset_tokens`, `model_has_permissions`) y van vacías o con estado de arranque.

---

## ⚠️ Advertencias antes de usar este volcado

1. **Contiene las tres cuentas de demostración con contraseña conocida** (`Asobares2026*`, publicada en el README del proyecto). **Nunca se restaura sobre producción.** Los seeders de cuentas de demostración se niegan a ejecutarse fuera de entornos locales o de pruebas, pero un volcado cargado a mano se salta esa defensa.
2. **Todos los establecimientos, artistas, proveedores, vacantes y personas son ficticios.** Los únicos datos reales son los institucionales del gremio (dirección, contacto, redes) y las cifras del Observatorio Económico. No hay un solo dato personal de una persona real en este archivo.
3. **Los formatos descargables de la guía normativa son PDF de relleno generados por el sembrador**, no los documentos oficiales de las entidades. La guía no puede salir a producción con este contenido — ver §17 del prompt maestro.
