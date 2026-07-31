# Modelo de datos

## 1. Propósito

Este documento inventaría el esquema definido por las migraciones de Resuelve.
Describe la estructura que produciría la secuencia completa de migraciones, no
el contenido ni el estado real de una base de datos desplegada.

## 2. Motores configurados

Laravel usa `DB_CONNECTION=sqlite` como valor predeterminado en
`.env.example`. `compose.yaml` configura MySQL 8.4 para el entorno Sail. El
archivo `config/database.php` conserva conexiones para SQLite, MySQL, MariaDB,
PostgreSQL y SQL Server provistas por la configuración de Laravel.

Las migraciones de negocio utilizan abstracciones de Schema Builder y no
seleccionan directamente un motor.

## 3. Tablas de negocio

### 3.1 users

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador bigint. |
| `name` | Texto obligatorio. |
| `email` | Texto obligatorio y único. |
| `role` | Texto obligatorio, predeterminado `residente`. |
| `email_verified_at` | Timestamp opcional. |
| `password` | Texto obligatorio. |
| `tower` | Texto opcional, máximo 50. |
| `unit` | Texto opcional, máximo 50. |
| `remember_token` | Token opcional de Laravel. |
| `created_at`, `updated_at` | Timestamps. |

La base de datos no restringe `role` mediante enum o clave foránea. Los valores
permitidos se validan en los controladores.

### 3.2 tipo_pqrs

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `nombre` | Texto obligatorio, máximo 100. |
| `descripcion` | Texto opcional. |
| `created_at`, `updated_at` | Timestamps. |

El nombre no tiene restricción única.

### 3.3 pqrs

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `asunto` | Texto obligatorio, máximo 150. |
| `descripcion` | Texto obligatorio. |
| `fecha_radicacion` | Fecha obligatoria. |
| `fecha_limite_respuesta` | Fecha opcional. |
| `estado` | Enum: `radicada`, `en_revision`, `respondida`, `cerrada`; predeterminado `radicada`. |
| `user_id` | FK obligatoria al usuario radicador. |
| `assigned_to_id` | FK opcional al usuario responsable. |
| `tipo_pqr_id` | FK obligatoria al tipo. |
| `last_reminder_at` | Timestamp opcional. |
| `created_at`, `updated_at` | Timestamps. |

Integridad:

- eliminar el radicador elimina sus PQR;
- eliminar el responsable establece `assigned_to_id` en nulo;
- eliminar el tipo elimina las PQR asociadas.

### 3.4 pqr_attachments

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `pqr_id` | FK obligatoria a PQR. |
| `original_name` | Nombre original. |
| `path` | Ruta física relativa. |
| `mime_type` | Tipo MIME, máximo 120. |
| `size` | Tamaño unsigned bigint. |
| `created_at`, `updated_at` | Timestamps. |

La eliminación de la PQR elimina los metadatos en cascada.

### 3.5 site_settings

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `nombre_conjunto` | Texto obligatorio. |
| `nit` | Texto opcional, máximo 40. |
| `representante_legal` | Texto opcional. |
| `direccion` | Texto opcional. |
| `ciudad` | Texto opcional, máximo 100. |
| `telefono` | Texto opcional, máximo 40. |
| `email` | Texto opcional. |
| `color_principal` | Texto de siete caracteres, predeterminado `#12382f`. |
| `logo_path` | Ruta opcional. |
| `dias_respuesta` | Smallint unsigned, predeterminado 15. |
| `created_at`, `updated_at` | Timestamps. |

No existe restricción que limite la tabla a un solo registro.

### 3.6 pqr_activities

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `pqr_id` | FK obligatoria a PQR, cascada. |
| `user_id` | FK opcional a usuario, queda en nulo al eliminarlo. |
| `action` | Texto obligatorio, máximo 50. |
| `description` | Texto obligatorio. |
| `metadata` | JSON opcional. |
| `created_at`, `updated_at` | Timestamps. |

### 3.7 pqr_replies

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `pqr_id` | FK obligatoria a PQR, cascada. |
| `user_id` | FK opcional a usuario, queda en nulo al eliminarlo. |
| `body` | Texto largo obligatorio. |
| `is_draft` | Booleano, predeterminado falso. |
| `attachments` | JSON opcional. |
| `sent_at` | Timestamp opcional. |
| `created_at`, `updated_at` | Timestamps. |

No existe una restricción que obligue coherencia entre `is_draft` y `sent_at`.

### 3.8 pqr_internal_comments

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `pqr_id` | FK obligatoria a PQR, cascada. |
| `user_id` | FK opcional a usuario, queda en nulo al eliminarlo. |
| `body` | Texto obligatorio. |
| `created_at`, `updated_at` | Timestamps. |

### 3.9 pqr_tags

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `name` | Texto obligatorio, máximo 60 y único. |
| `color` | Texto de siete caracteres, predeterminado `#1f6b57`. |
| `created_at`, `updated_at` | Timestamps. |

El formato hexadecimal se valida en el controlador, no mediante una restricción
de base de datos.

### 3.10 pqr_pqr_tag

| Columna | Definición relevante |
| --- | --- |
| `pqr_id` | FK a PQR, cascada. |
| `pqr_tag_id` | FK a etiqueta, cascada. |

La llave primaria compuesta por ambas columnas impide repetir una etiqueta en
la misma PQR. La tabla no tiene identificador ni timestamps.

### 3.11 response_templates

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `name` | Texto obligatorio, máximo 100. |
| `subject` | Texto opcional, máximo 150. |
| `body` | Texto obligatorio. |
| `created_by` | FK opcional a usuario, queda en nulo al eliminarlo. |
| `created_at`, `updated_at` | Timestamps. |

### 3.12 satisfaction_surveys

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `pqr_id` | FK única a PQR, cascada. |
| `user_id` | FK obligatoria a usuario, cascada. |
| `rating` | Tinyint unsigned. |
| `comment` | Texto opcional. |
| `created_at`, `updated_at` | Timestamps. |

La unicidad de `pqr_id` impone una encuesta por PQR. El rango de 1 a 5 se
valida en el controlador y no mediante un `check`.

### 3.13 automation_rules

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `name` | Texto obligatorio, máximo 100. |
| `tipo_pqr_id` | FK opcional a tipo, cascada. |
| `assign_to_id` | FK opcional a usuario, queda en nulo al eliminarlo. |
| `set_status` | Texto obligatorio, máximo 30, predeterminado `en_revision`. |
| `active` | Booleano, predeterminado verdadero. |
| `created_at`, `updated_at` | Timestamps. |

Los estados válidos de una regla se validan en el controlador; la columna no
es enum.

### 3.14 audit_logs

| Columna | Definición relevante |
| --- | --- |
| `id` | Identificador. |
| `user_id` | FK opcional a usuario, queda en nulo al eliminarlo. |
| `action` | Texto obligatorio, máximo 80. |
| `auditable_type` | Texto opcional. |
| `auditable_id` | Bigint unsigned opcional. |
| `ip_address` | Texto opcional, máximo 45. |
| `metadata` | JSON opcional. |
| `created_at`, `updated_at` | Timestamps. |

Existe un índice compuesto sobre `auditable_type` y `auditable_id`, pero no una
clave foránea ni una relación morph declarada.

## 4. Tablas de infraestructura

### 4.1 Autenticación y sesión

| Tabla | Finalidad |
| --- | --- |
| `password_reset_tokens` | Token por correo y fecha de creación opcional. |
| `sessions` | Sesiones con usuario opcional, IP, agente, payload y actividad. |

`password_reset_tokens.email` y `sessions.id` son llaves primarias.

### 4.2 Caché

| Tabla | Finalidad |
| --- | --- |
| `cache` | Valor y expiración por llave. |
| `cache_locks` | Propietario y expiración de bloqueos. |

### 4.3 Colas

| Tabla | Finalidad |
| --- | --- |
| `jobs` | Jobs pendientes con cola, payload, intentos y tiempos. |
| `job_batches` | Estado agregado de lotes. |
| `failed_jobs` | Jobs fallidos con UUID, conexión, payload y excepción. |

El repositorio no contiene clases de job propias, aunque la infraestructura
está disponible y `QUEUE_CONNECTION` usa `database` por defecto.

### 4.4 notifications

Tabla estándar de notificaciones de Laravel:

- UUID como llave primaria;
- clase de notificación;
- relación polimórfica `notifiable`;
- datos serializados;
- fecha de lectura;
- timestamps.

## 5. Secuencia de evolución del esquema

Las migraciones reflejan esta evolución:

1. infraestructura base de usuarios, caché y jobs;
2. creación de tipos y PQR;
3. incorporación del rol de usuario;
4. adjuntos y configuración institucional;
5. logo institucional;
6. responsable de PQR;
7. actividades, respuestas, notificaciones y comentarios;
8. promoción puntual de un usuario gestor a administrador;
9. funciones complementarias: unidad, recordatorios, etiquetas, plantillas,
   encuestas, reglas y auditoría.

La migración `2026_07_24_260000_promote_primary_manager_to_admin.php` modifica
datos buscando el correo `gestionpqrs7@gmail.com`. No es una modificación
estructural.

## 6. Datos iniciales declarados

`DatabaseSeeder`:

- crea la configuración predeterminada si no existe;
- crea o actualiza una cuenta administradora;
- crea o actualiza tres residentes;
- crea cinco tipos de PQR;
- elimina un lote de demostración heredado bajo condiciones específicas;
- crea o actualiza doce casos de demostración;
- asigna esos casos a la cuenta administradora.

Las credenciales y registros del seeder son datos de demostración incluidos en
el código. Este documento no recomienda su uso en un entorno expuesto.

## 7. Hallazgos del esquema

1. No existe una tabla de copropiedades ni claves de partición por
   copropiedad.
2. `site_settings` admite múltiples filas, aunque la aplicación usa la primera.
3. Los roles no están restringidos en la base de datos.
4. Eliminar un tipo elimina sus PQR y reglas automáticas.
5. Eliminar un radicador elimina sus PQR.
6. Los adjuntos de respuesta se almacenan como JSON y no tienen integridad
   referencial propia.
7. `ResponseTemplate.created_by` y `SatisfactionSurvey.user_id` no tienen
   relación declarada en sus modelos.
8. `audit_logs` simula una referencia auditable sin relación polimórfica.
9. La migración de promoción contiene un correo específico.

## 8. Evidencias utilizadas

- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/0001_01_01_000001_create_cache_table.php`
- `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `database/migrations/2026_07_07_003332_create_tipo_pqrs_table.php`
- `database/migrations/2026_07_07_003347_create_pqrs_table.php`
- `database/migrations/2026_07_24_170000_add_role_to_users_table.php`
- `database/migrations/2026_07_24_180000_create_pqr_attachments_table.php`
- `database/migrations/2026_07_24_190000_create_site_settings_table.php`
- `database/migrations/2026_07_24_200000_add_logo_path_to_site_settings_table.php`
- `database/migrations/2026_07_24_210000_add_assignee_to_pqrs_table.php`
- `database/migrations/2026_07_24_220000_create_pqr_activities_table.php`
- `database/migrations/2026_07_24_230000_create_pqr_replies_table.php`
- `database/migrations/2026_07_24_240000_create_notifications_table.php`
- `database/migrations/2026_07_24_250000_create_pqr_internal_comments_table.php`
- `database/migrations/2026_07_24_260000_promote_primary_manager_to_admin.php`
- `database/migrations/2026_07_24_270000_create_complementary_features.php`
- `database/seeders/DatabaseSeeder.php`
- `app/Models/`
- `.env.example`
- `config/database.php`
- `compose.yaml`

## Control documental

- **Versión:** v1.0
- **Fecha de creación:** 30 de julio de 2026
- **Fecha de última actualización:** 30 de julio de 2026
