# Documentación oficial de Resuelve

Este directorio contiene la documentación técnica, funcional y de producto de
Resuelve. Su propósito es describir el comportamiento verificable del software,
registrar las decisiones de producto aprobadas y mantener su trazabilidad.

## Línea base

La línea base inicial corresponde al estado del árbol de trabajo inspeccionado
el 30 de julio de 2026. En esta fase:

- el código fuente es la fuente de verdad para documentar el estado actual;
- las decisiones explícitas del Product Owner son la fuente de verdad para la
  visión estratégica del producto;
- solo se documentan funcionalidades implementadas y verificables;
- las inconsistencias se registran como hallazgos y no se corrigen mediante la
  documentación;
- no se presentan funcionalidades futuras como parte del sistema actual.

La implementación inspeccionada es una aplicación web monolítica construida
con Laravel para gestionar PQR de una copropiedad configurable. El repositorio
no contiene actualmente una API de aplicación ni una implementación de Agente
IA. Estas ausencias se documentarán expresamente en sus secciones respectivas.

## Cómo consultar esta documentación

La documentación se organiza por ámbito. Cada documento debe incluir una
sección de evidencia con referencias a los modelos, migraciones, controladores,
políticas, rutas, vistas, pruebas u otros archivos que respaldan su contenido.

Los estados utilizados en este índice son:

- **Completado:** documento creado y validado.
- **Aprobado:** documento estratégico consolidado con decisiones aprobadas por
  el Product Owner.
- **En construcción:** documento creado cuyo contenido aprobado aún está
  pendiente de completar.
- **Pendiente:** documento aprobado en el inventario, aún no creado.

## Índice general

### 01. Producto

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Visión del Producto](01-producto/vision-del-producto.md) | Aprobado | Servir como referencia estratégica para las decisiones funcionales, arquitectónicas y de desarrollo de Resuelve. |
| [Modelo de negocio](01-producto/modelo-de-negocio.md) | En construcción | Documentar el cliente, los usuarios, el modelo comercial y las estrategias de evolución del producto. |
| [Dominio del negocio](01-producto/dominio-del-negocio.md) | En construcción | Definir los conceptos, relaciones, límites y reglas generales del dominio funcional de Resuelve. |
| [Modelo del dominio](01-producto/modelo-del-dominio.md) | En construcción | Organizar el producto en dominios funcionales, capacidades y dependencias estratégicas. |
| [Línea base del estado actual](01-producto/linea-base-estado-actual.md) | Completado | Delimitar el alcance implementado, las exclusiones y el estado comprobado del producto. |

### 02. Documentación funcional

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Catálogo funcional](02-funcional/catalogo-funcional.md) | Completado | Presentar los módulos, actores y capacidades existentes. |
| [Gestión de PQR](02-funcional/gestion-pqr.md) | Completado | Describir la radicación, consulta, gestión, respuesta, cierre, adjuntos e historial. |
| [Roles y permisos](02-funcional/roles-y-permisos.md) | Completado | Registrar la matriz de acceso y las restricciones implementadas. |
| [Ciclo de vida de una PQR](02-funcional/ciclo-de-vida-pqr.md) | Pendiente | Documentar estados, transiciones y efectos secundarios. |
| [Reglas de negocio](02-funcional/reglas-de-negocio.md) | Pendiente | Consolidar validaciones, plazos, vencimientos, visibilidad y eliminación. |
| [Autenticación y cuentas](02-funcional/autenticacion-y-cuentas.md) | Pendiente | Describir acceso, recuperación de contraseña y gestión del perfil. |
| [Informes e indicadores](02-funcional/informes-e-indicadores.md) | Pendiente | Documentar filtros, métricas y exportaciones. |
| [Configuración de la copropiedad](02-funcional/configuracion-copropiedad.md) | Pendiente | Registrar la identidad institucional y los parámetros configurables. |
| [Herramientas operativas](02-funcional/herramientas-operativas.md) | Pendiente | Describir plantillas, etiquetas y reglas automáticas. |
| [Gestión de residentes](02-funcional/gestion-de-residentes.md) | Pendiente | Documentar cuentas de residentes, torre y unidad privada. |

### 03. Documentación técnica

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Modelo de dominio](03-tecnica/modelo-de-dominio.md) | Completado | Describir entidades, relaciones y cardinalidades. |
| [Modelo de datos](03-tecnica/modelo-de-datos.md) | Completado | Documentar tablas, columnas, claves y reglas de integridad. |
| [Rutas web](03-tecnica/rutas-web.md) | Pendiente | Inventariar métodos HTTP, URI, controladores y autorización. |
| [Notificaciones y automatizaciones](03-tecnica/notificaciones-y-automatizaciones.md) | Pendiente | Describir eventos, destinatarios, canales y tareas programadas. |
| [Archivos y almacenamiento](03-tecnica/archivos-y-almacenamiento.md) | Pendiente | Registrar discos, adjuntos, límites, acceso y eliminación. |
| [Auditoría y trazabilidad](03-tecnica/auditoria-y-trazabilidad.md) | Pendiente | Documentar actividades de PQR y auditoría de mutaciones HTTP. |
| [Interfaz web](03-tecnica/interfaz-web.md) | Pendiente | Inventariar vistas Blade y comportamiento de la interfaz. |
| [Dependencias](03-tecnica/dependencias.md) | Pendiente | Registrar paquetes de backend y frontend y su uso verificable. |
| [Configuración técnica](03-tecnica/configuracion-tecnica.md) | Pendiente | Describir base de datos, sesión, caché, colas, correo y filesystem. |

### 04. Desarrollo ágil

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Product Backlog](04-desarrollo-agil/product-backlog.md) | En construcción | Organizar las Épicas e Historias de Usuario iniciales desde la perspectiva del negocio. |
| [Pruebas automatizadas](04-desarrollo-agil/pruebas-automatizadas.md) | Pendiente | Inventariar la cobertura existente y sus límites. |

### 05. Arquitectura

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Arquitectura actual](05-arquitectura/arquitectura-actual.md) | Completado | Describir la estructura monolítica Laravel, sus capas y flujos. |

### 06. Inteligencia artificial

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Estado actual de IA](06-ia/estado-actual.md) | Pendiente | Dejar constancia del alcance verificable actual respecto a IA. |

### 07. API

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Estado actual de la API](07-api/estado-actual-api.md) | Pendiente | Registrar el alcance verificable actual de interfaces API. |

### 08. Despliegue

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Entorno local](08-despliegue/entorno-local.md) | Pendiente | Documentar requisitos y mecanismos de ejecución local declarados. |
| [Túnel de demostración](08-despliegue/tunel-demostracion.md) | Pendiente | Describir el servicio de demostración y sus limitaciones. |

### 09. Historial

| Documento | Estado | Propósito |
| --- | --- | --- |
| [Hallazgos de la línea base](09-historial/hallazgos-linea-base.md) | Pendiente | Registrar inconsistencias y deuda técnica observada sin corregirla. |

### 10. Registros de decisiones arquitectónicas

Esta sección alojará registros de decisiones arquitectónicas únicamente cuando
exista evidencia de una decisión tomada. La línea base no presume decisiones
que no estén registradas o implementadas.

### 11. Glosario

Esta sección alojará términos funcionales y técnicos consolidados a partir de
los documentos de la línea base.

### 12. Decisiones funcionales

Esta sección alojará decisiones funcionales aprobadas y trazables. No contiene
por defecto propuestas ni comportamiento futuro.

## Prioridad de construcción

La documentación se construye incrementalmente en este orden:

1. **P0:** línea base, catálogo funcional, gestión de PQR, roles y permisos,
   modelo de dominio, modelo de datos y arquitectura actual.
2. **P1:** ciclo de vida, reglas de negocio, rutas, autenticación,
   notificaciones, almacenamiento, informes, auditoría y configuración.
3. **P2:** herramientas operativas, residentes, interfaz, dependencias,
   configuración técnica, entorno local, túnel de demostración, pruebas y
   hallazgos.
4. **P3:** estado actual de API e IA.

## Evidencia de este índice

El alcance y la organización inicial se determinaron mediante inspección de:

- `app/Models/`
- `app/Http/Controllers/`
- `app/Http/Middleware/`
- `app/Notifications/`
- `app/Policies/`
- `app/Providers/`
- `bootstrap/app.php`
- `config/`
- `database/factories/`
- `database/migrations/`
- `database/seeders/`
- `resources/js/`
- `resources/views/`
- `routes/`
- `scripts/`
- `tests/`
- `composer.json`
- `compose.yaml`
- `package.json`
- `README.md`

## Mantenimiento

Cuando cambie una funcionalidad, deben revisarse el documento funcional, el
documento técnico y los registros de trazabilidad relacionados. Los enlaces y
estados de este índice deben actualizarse en el mismo cambio documental.

## Control documental

- **Versión:** v1.5
- **Fecha de creación:** 30 de julio de 2026
- **Fecha de última actualización:** 31 de julio de 2026
