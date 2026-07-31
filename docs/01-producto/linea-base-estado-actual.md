# Línea base del estado actual

## 1. Propósito

Este documento delimita el estado funcional y técnico verificable de Resuelve.
Sirve como referencia inicial para mantener sincronizados el software y su
documentación.

La línea base se construyó mediante inspección estática del árbol de trabajo
del repositorio. No certifica el estado de una base de datos desplegada ni el
resultado de ejecutar la aplicación.

## 2. Identificación del producto

- **Nombre del proyecto:** Resuelve.
- **Tipo de solución actual:** aplicación web para gestionar peticiones,
  quejas, reclamos y sugerencias.
- **Ámbito funcional observado:** operación de PQR en una copropiedad
  configurable.
- **Arquitectura observada:** aplicación monolítica Laravel con interfaz
  renderizada mediante Blade.
- **Usuarios representados en el código:** administrador, gestor, apoyo,
  auditor y residente.

Aunque el sistema permite que residentes tengan una cuenta y radiquen sus
propias solicitudes, la operación administrativa de PQR constituye el núcleo
de la implementación.

## 3. Alcance implementado

La aplicación contiene capacidades verificables para:

1. autenticar usuarios mediante correo y contraseña;
2. recuperar y restablecer contraseñas;
3. administrar cuentas, roles y datos de unidad privada;
4. radicar, consultar, filtrar, asignar, actualizar y eliminar PQR;
5. manejar los estados `radicada`, `en_revision`, `respondida` y `cerrada`;
6. adjuntar archivos a una PQR y a sus respuestas;
7. enviar respuestas o guardarlas como borrador;
8. registrar comentarios internos y actividades de gestión;
9. clasificar PQR mediante tipos y etiquetas;
10. crear plantillas de respuesta y reglas de asignación automática;
11. notificar eventos por base de datos y correo;
12. emitir recordatorios diarios para solicitudes próximas a vencer;
13. recibir una valoración de satisfacción para solicitudes respondidas o
    cerradas;
14. consultar carga de trabajo y registros de auditoría;
15. configurar la identidad visible de una copropiedad;
16. consultar indicadores y exportar informes en CSV, XLSX y PDF;
17. personalizar aspectos de la interfaz, como logo, color y tema visual.

## 4. Delimitación organizacional actual

La configuración institucional se obtiene del primer registro de
`site_settings` mediante `SiteSetting::current()`. Las PQR, usuarios y demás
entidades no tienen una clave que identifique una copropiedad.

Por lo anterior, el código inspeccionado representa una única copropiedad
configurable por instancia. No existe evidencia de aislamiento de datos entre
copropiedades ni de una cuenta que administre varias copropiedades.

## 5. Componentes principales

| Componente | Responsabilidad observable |
| --- | --- |
| Autenticación | Inicio y cierre de sesión, recuperación y cambio de contraseña. |
| Gestión de usuarios | Creación, edición, cambio de rol y eliminación controlada. |
| Gestión de PQR | Radicación, seguimiento, búsqueda, filtros, asignación y cambio de estado. |
| Atención de PQR | Respuestas, borradores, adjuntos, comentarios internos y etiquetas. |
| Trazabilidad | Actividades propias de cada PQR y auditoría de mutaciones HTTP. |
| Automatización | Reglas aplicadas al crear PQR y recordatorio diario de vencimientos. |
| Notificaciones | Avisos persistidos en base de datos y mensajes de correo. |
| Informes | Exportaciones CSV, XLSX y PDF basadas en los filtros de PQR. |
| Configuración | Identidad institucional, plazo estándar, logo y color principal. |
| Interfaz | Vistas Blade adaptables, navegación por rol y preferencias locales de tema. |

## 6. Límites y ausencias verificadas

No se encontraron en el repositorio:

- archivo `routes/api.php` ni rutas de aplicación bajo un prefijo API;
- controladores o recursos destinados a una API;
- modelos, servicios, clientes o configuración de inteligencia artificial;
- un Agente IA;
- procesamiento de reglamentos o documentos normativos;
- integración con la Ley 675 de 2001;
- entidades de copropiedad, membresía o asociación usuario-copropiedad;
- aislamiento multiarrendatario;
- eventos, listeners o jobs propios de la aplicación;
- componentes frontend basados en un framework JavaScript.

Estas ausencias delimitan el estado actual; no constituyen compromisos de
implementación futura.

## 7. Persistencia y almacenamiento

El proyecto contiene migraciones para usuarios, sesiones, caché, colas, PQR y
sus entidades relacionadas. La conexión predeterminada del archivo
`.env.example` es SQLite, mientras `compose.yaml` define un servicio MySQL 8.4
para Laravel Sail.

Los adjuntos de PQR y respuestas se guardan en el disco `local`. El logo de la
copropiedad se guarda en el disco `public`. Las notificaciones se persisten en
la tabla `notifications`.

## 8. Automatizaciones actuales

Existen dos mecanismos de automatización propios:

- al crear una PQR se aplica la primera regla automática activa cuyo tipo sea
  general o coincida con el tipo de la solicitud;
- el comando `pqrs:send-reminders`, programado diariamente a las 08:00,
  notifica solicitudes abiertas que vencen entre el día actual y los tres días
  siguientes.

El envío programado depende de que el scheduler de Laravel sea ejecutado por el
entorno operativo. El repositorio define la programación, pero la inspección
estática no acredita que exista un scheduler activo en un despliegue.

## 9. Calidad y verificación existente

El repositorio contiene pruebas funcionales para autenticación, permisos,
configuración, gestión prioritaria de PQR, funciones complementarias e
informes. También contiene pruebas de ejemplo.

Las pruebas no fueron ejecutadas durante el levantamiento porque utilizan
`RefreshDatabase`, lo cual ejecuta migraciones y estaba excluido expresamente
del proceso documental. En consecuencia, esta línea base distingue entre:

- comportamiento respaldado por implementación;
- comportamiento adicionalmente expresado en pruebas;
- estado de ejecución no verificado.

## 10. Condiciones del árbol de trabajo

Durante el levantamiento se observaron cambios locales sin confirmar en
`.gitignore`, `app/Http/Controllers/AuthController.php` y `scripts/`. Esta línea
base describe el árbol de trabajo inspeccionado, no exclusivamente el último
commit de la rama.

Los cambios locales no fueron alterados por el proceso documental.

## 11. Criterio de mantenimiento

Este documento debe revisarse cuando ocurra alguno de los siguientes cambios:

- incorporación o retiro de un módulo;
- cambio de arquitectura;
- introducción de API, IA o multi-copropiedad;
- modificación del esquema general de roles;
- cambio en la estrategia de persistencia o despliegue;
- creación de una decisión arquitectónica que altere el alcance vigente.

## 12. Evidencias utilizadas

### Código de aplicación

- `app/Models/`
- `app/Http/Controllers/`
- `app/Http/Middleware/AuditMutations.php`
- `app/Notifications/PqrEventNotification.php`
- `app/Policies/PqrPolicy.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`

### Persistencia, rutas e interfaz

- `database/migrations/`
- `database/seeders/DatabaseSeeder.php`
- `routes/web.php`
- `routes/console.php`
- `resources/views/`
- `resources/js/app.js`

### Configuración y operación

- `.env.example`
- `composer.json`
- `package.json`
- `compose.yaml`
- `config/`
- `scripts/`

### Verificación declarada

- `tests/Feature/`
- `tests/Unit/`

## Control documental

- **Versión:** v1.0
- **Fecha de creación:** 30 de julio de 2026
- **Fecha de última actualización:** 30 de julio de 2026
