# Gestión de PQR

## 1. Propósito

Este documento describe el proceso funcional implementado para radicar,
consultar y atender PQR en Resuelve.

## 2. Concepto de PQR en el sistema

Una PQR es un registro asociado a:

- un usuario radicador;
- un tipo de PQR;
- un responsable opcional;
- un estado;
- fechas de radicación y límite;
- adjuntos, actividades, respuestas, comentarios, etiquetas y una encuesta
  opcional.

El identificador visible se construye con el prefijo `PQR-` y el identificador
numérico completado a cuatro posiciones, por ejemplo `PQR-0007`.

## 3. Tipos y estados

Los tipos se almacenan en `tipo_pqrs`. El seeder crea Petición, Queja, Reclamo,
Sugerencia y Solicitud, pero el código no limita el dominio a esos cinco
valores: una PQR referencia cualquier tipo existente en la tabla.

Los estados persistidos son:

| Valor | Etiqueta visible | Interpretación utilizada por el código |
| --- | --- | --- |
| `radicada` | Radicada | Estado inicial predeterminado. |
| `en_revision` | En revisión | Solicitud abierta en gestión. |
| `respondida` | Respondida | Solicitud con respuesta o marcada manualmente como respondida. |
| `cerrada` | Cerrada | Solicitud finalizada manualmente. |

La base de datos restringe el estado a estos cuatro valores.

## 4. Radicación

### 4.1 Acceso

La política `PqrPolicy::create()` devuelve `true` para cualquier usuario
autenticado. No existe restricción técnica que reserve la radicación al rol
residente.

### 4.2 Datos de entrada

| Campo | Regla implementada |
| --- | --- |
| Asunto | Obligatorio, texto, máximo 150 caracteres. |
| Descripción | Obligatoria, texto. |
| Fecha de radicación | Obligatoria y válida como fecha. |
| Fecha límite | Opcional y válida como fecha. |
| Tipo | Obligatorio y existente en `tipo_pqrs`. |
| Adjuntos | Opcionales, arreglo de máximo ocho archivos. |
| Cada adjunto | Máximo 10 MB y extensión permitida. |

Extensiones aceptadas: JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX, TXT,
CSV y ZIP.

El formulario propone como fecha límite la fecha actual más
`site_settings.dias_respuesta`. Esta propuesta ocurre en la vista. Si el
cliente omite la fecha, el controlador no la calcula automáticamente.

### 4.3 Proceso de creación

La creación se realiza dentro de una transacción:

1. se crea la PQR con el usuario autenticado como radicador;
2. se busca una regla automática activa aplicable;
3. si existe, se asignan responsable y estado;
4. se almacenan los adjuntos;
5. se registra una actividad `created`.

Después de confirmar la transacción, se notifica a todos los usuarios con rol
administrador o gestor.

### 4.4 Regla automática

La consulta toma la primera regla activa cuyo `tipo_pqr_id` sea nulo o coincida
con el tipo de la PQR. No se define una prioridad explícita ni un orden en la
consulta. Solo se aplica una regla.

Una regla puede:

- asignar un responsable o dejar la PQR sin responsable;
- establecer `radicada` o `en_revision`.

## 5. Consulta

### 5.1 Visibilidad

- Administrador, gestor, auditor y apoyo pueden consultar todas las PQR.
- Un residente solo consulta PQR cuyo `user_id` corresponde a su cuenta.
- El detalle y las descargas principales aplican `PqrPolicy::view()`.

### 5.2 Panel e indicadores

El panel calcula, dentro del universo visible para el usuario:

- total;
- pendientes: `radicada` o `en_revision`;
- respondidas;
- próximas a vencer: abiertas con fecha límite entre hoy y tres días después;
- solicitudes creadas por mes durante los últimos seis meses;
- distribución por estado.

### 5.3 Búsqueda

La búsqueda coincide parcialmente con:

- asunto;
- descripción;
- nombre o correo del radicador;
- nombre del responsable.

También intenta coincidencia exacta del texto contra el identificador numérico.

### 5.4 Filtros

Se implementan filtros por:

- estado individual;
- agrupación `pendientes`;
- agrupación `por_vencer`;
- tipo;
- responsable;
- fecha de radicación desde;
- fecha de radicación hasta.

La lista se ordena por fecha de creación descendente y muestra diez registros
por página.

## 6. Detalle y trazabilidad

El detalle carga:

- radicador;
- tipo;
- responsable;
- adjuntos;
- actividades y sus usuarios;
- respuestas y sus usuarios;
- comentarios internos y sus usuarios;
- etiquetas;
- encuesta de satisfacción.

La vista presenta fechas, días transcurridos, días restantes o vencidos,
responsable, archivos, conversación e historial.

Una PQR se considera vencida si la fecha límite está en el pasado y el estado
no es `respondida` ni `cerrada`.

## 7. Actualización

### 7.1 Autorización

Pueden actualizar:

- administrador;
- gestor;
- apoyo, cuando la PQR no tiene responsable o está asignada a su propia cuenta.

El auditor y el residente no pueden actualizar una PQR mediante las acciones
que invocan la política.

### 7.2 Datos modificables

La edición completa admite:

- asunto;
- descripción;
- fecha de radicación;
- fecha límite;
- estado;
- tipo;
- responsable;
- nuevos adjuntos.

El sistema registra los valores anteriores de estado y responsable. Cuando
alguno cambia, crea una descripción específica en el historial.

### 7.3 Efectos secundarios

- Un cambio de estado notifica al radicador.
- Un cambio de responsable notifica al nuevo responsable.
- Se crea una actividad `updated`.
- Los archivos nuevos se agregan; no existe eliminación individual de adjuntos.

### 7.4 Acción rápida

La acción rápida permite actualizar estado y responsable y crea una actividad
`quick_action`. Si cambia el estado, notifica al radicador.

El campo `assigned_to_id` es validado como opcional. Debido a la forma en que
Laravel valida datos ausentes, la acción puede actualizar solo uno de los dos
campos.

## 8. Respuestas

### 8.1 Acceso

El controlador permite responder a cualquier usuario cuyo rol sea
administrador, gestor o apoyo mediante `canManagePqrs()`.

No invoca `PqrPolicy::update()`. Por ello, la restricción de asignación del rol
apoyo no se aplica en este flujo. Este es el comportamiento actual y debe
tratarse como hallazgo de autorización, no como permiso de negocio aprobado.

### 8.2 Borrador

Un borrador:

- guarda cuerpo, autor y adjuntos;
- conserva `sent_at` en nulo;
- crea una actividad `drafted_reply`;
- no cambia el estado;
- no notifica al radicador;
- solo se muestra a quienes pueden gestionar PQR.

No existe una operación para editar, enviar o eliminar posteriormente un
borrador existente.

### 8.3 Envío

Una respuesta enviada:

- registra `sent_at`;
- crea una actividad `sent_reply`;
- cambia la PQR a `respondida`;
- notifica al radicador.

El cambio a `respondida` ocurre directamente y no genera una actividad
adicional distinta de `sent_reply`.

### 8.4 Adjuntos de respuesta

Se admiten hasta cinco archivos, cada uno de máximo 10 MB, con las mismas
extensiones de los adjuntos principales. Sus metadatos se guardan como JSON en
la respuesta.

La descarga verifica que el usuario pueda ver la PQR, que la respuesta
pertenezca a ella y que exista el índice solicitado.

## 9. Comentarios internos

Administrador, gestor y apoyo pueden agregar comentarios de máximo 3.000
caracteres. Cada comentario crea una actividad `internal_comment`.

El controlador usa `canManagePqrs()` y no la política de actualización, por lo
que presenta la misma diferencia de autorización indicada para las respuestas.
La vista no muestra estos comentarios a residentes ni auditores.

## 10. Etiquetas

Un usuario autorizado por `PqrPolicy::update()` puede sincronizar el conjunto
completo de etiquetas de una PQR. Los identificadores deben existir en
`pqr_tags`. La operación crea una actividad `tags_updated`.

## 11. Encuesta de satisfacción

Solo el radicador puede valorar una PQR que esté `respondida` o `cerrada`.

- Calificación: entero entre 1 y 5.
- Comentario: opcional, máximo 2.000 caracteres.
- Cardinalidad: una encuesta por PQR.

El controlador utiliza `updateOrCreate`. La interfaz permite crear la
valoración cuando no existe y muestra el resultado cuando ya existe.

## 12. Eliminación

La eliminación está permitida para administrador y gestor.

Antes de eliminar la PQR, el controlador elimina del disco local los archivos
registrados en `pqr_attachments`. La eliminación de la PQR activa eliminaciones
en cascada para varias entidades relacionadas.

No se encontró eliminación explícita de los archivos físicos guardados en las
respuestas. Como los metadatos de estos archivos viven en JSON, la cascada de
base de datos no elimina los archivos del almacenamiento.

## 13. Notificaciones relacionadas

| Evento | Destinatario |
| --- | --- |
| Nueva PQR | Todos los administradores y gestores. |
| Cambio de estado | Radicador. |
| Asignación | Nuevo responsable. |
| Respuesta enviada | Radicador. |
| Recordatorio de vencimiento | Radicador y responsable, si existen. |

Cada notificación de evento usa base de datos y correo.

## 14. Evidencias utilizadas

### Dominio y persistencia

- `app/Models/Pqr.php`
- `app/Models/TipoPqr.php`
- `app/Models/PqrAttachment.php`
- `app/Models/PqrActivity.php`
- `app/Models/PqrReply.php`
- `app/Models/PqrInternalComment.php`
- `app/Models/PqrTag.php`
- `app/Models/SatisfactionSurvey.php`
- `app/Models/AutomationRule.php`
- `database/migrations/2026_07_07_003332_create_tipo_pqrs_table.php`
- `database/migrations/2026_07_07_003347_create_pqrs_table.php`
- `database/migrations/2026_07_24_180000_create_pqr_attachments_table.php`
- `database/migrations/2026_07_24_210000_add_assignee_to_pqrs_table.php`
- `database/migrations/2026_07_24_220000_create_pqr_activities_table.php`
- `database/migrations/2026_07_24_230000_create_pqr_replies_table.php`
- `database/migrations/2026_07_24_250000_create_pqr_internal_comments_table.php`
- `database/migrations/2026_07_24_270000_create_complementary_features.php`

### Aplicación e interfaz

- `app/Http/Controllers/PqrController.php`
- `app/Http/Controllers/PqrQuickActionController.php`
- `app/Http/Controllers/PqrReplyController.php`
- `app/Http/Controllers/PqrInternalCommentController.php`
- `app/Http/Controllers/ComplementaryController.php`
- `app/Policies/PqrPolicy.php`
- `app/Notifications/PqrEventNotification.php`
- `routes/web.php`
- `routes/console.php`
- `resources/views/pqrs/`

### Pruebas relacionadas

- `tests/Feature/PqrPermissionsTest.php`
- `tests/Feature/PqrPriorityFeaturesTest.php`
- `tests/Feature/PqrMediumFeaturesTest.php`
- `tests/Feature/PqrComplementaryFeaturesTest.php`

## Control documental

- **Versión:** v1.0
- **Fecha de creación:** 30 de julio de 2026
- **Fecha de última actualización:** 30 de julio de 2026
