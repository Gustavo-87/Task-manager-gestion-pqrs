# Gestión de PQRs para Conjuntos Residenciales

Proyecto desarrollado en Laravel y MySQL como aplicación del proyecto guía Task Manager del seminario.

## Descripción

Este sistema permite gestionar PQRs presentadas por residentes de conjuntos residenciales en Cartago, Valle del Cauca.

El proyecto implementa operaciones CRUD sobre las PQRs, permitiendo crear, listar, editar, eliminar, buscar y filtrar registros según su estado.

## Correspondencia con el proyecto Task Manager

| Proyecto Task Manager | Proyecto Gestión PQRs |
|---|---|
| Task | Pqr |
| Category | TipoPqr |
| tasks | pqrs |
| categories | tipo_pqrs |
| TaskController | PqrController |
| resources/views/tasks | resources/views/pqrs |
| category_id | tipo_pqr_id |
| user_id | user_id |

## Entidades principales

### TipoPqr

Representa la clasificación de la solicitud:

- Petición
- Queja
- Reclamo
- Sugerencia
- Solicitud

### Pqr

Representa la solicitud presentada por un residente o usuario del sistema.

Campos principales:

- asunto
- descripcion
- fecha_radicacion
- fecha_limite_respuesta
- estado
- user_id
- tipo_pqr_id

## Estados de una PQR

- radicada
- en_revision
- respondida
- cerrada

## Ejecución del proyecto

Levantar los contenedores:

```bash
./vendor/bin/sail up -d
```
## Ejecutar migraciones y seeders:

./vendor/bin/sail php artisan migrate:fresh --seed

Abrir en el navegador: http://localhost:8085/pqrs

## Funcionalidades implementadas

- Listado de PQRs
- Creación de PQRs
- Edición de PQRs
- Eliminación de PQRs
- Búsqueda por asunto
- Filtro por estado
- Relación entre PQR y TipoPqr
- Relación entre PQR y User
