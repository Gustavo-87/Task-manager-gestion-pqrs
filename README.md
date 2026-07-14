# Gestión de PQRs para Conjuntos Residenciales

Proyecto desarrollado con Laravel 13 y MySQL como aplicación del proyecto guía Task Manager del seminario.

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

## Requisitos

- Docker con Docker Compose
- Git
- Composer, necesario para instalar inicialmente Laravel Sail

El proyecto utiliza las siguientes tecnologías principales:

- PHP 8.3 o superior
- Laravel 13
- MySQL 8.4
- Laravel Sail
- Vite, Tailwind CSS y Alpine.js

## Instalación y ejecución

Instalar las dependencias de PHP:

```bash
composer install
```

Crear el archivo de entorno:

```bash
cp .env.example .env
```

El archivo `.env.example` ya incluye la configuración de MySQL para la red interna de Sail:

```dotenv
APP_URL=http://localhost:8085
APP_PORT=8085

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

Levantar los contenedores de la aplicación, MySQL y el planificador de tareas:

```bash
./vendor/bin/sail up -d
```

Generar la clave de la aplicación:

```bash
./vendor/bin/sail artisan key:generate
```

Ejecutar las migraciones y cargar los datos iniciales:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

Instalar y compilar los recursos del frontend:

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

La aplicación queda disponible en [http://localhost:8085](http://localhost:8085). Al ingresar, el sistema redirige a la pantalla de inicio de sesión.

## Autenticación y control de acceso

El sistema requiere autenticación para acceder al dashboard y gestionar las PQR. Incluye las siguientes funciones:

- Registro de residentes
- Inicio y cierre de sesión
- Recuperación y restablecimiento de contraseña
- Actualización del perfil y la contraseña
- Confirmación de contraseña para operaciones protegidas
- Control de acceso para cuentas activas

Los usuarios registrados desde el formulario público reciben automáticamente el rol `residente`.

### Roles y permisos

El sistema maneja dos roles:

| Función | Administrador | Residente |
|---|:---:|:---:|
| Consultar PQR | Todas | Solo las propias |
| Crear PQR | Sí | Sí |
| Editar PQR | Todas | Solo las propias |
| Cambiar el estado de una PQR | Sí | No |
| Eliminar PQR | Sí | No |
| Gestionar usuarios | Sí | No |
| Consultar auditoría | Sí | No |
| Generar reportes | Sí | No |
| Administrar categorías y configuración | Sí | No |

Las políticas de autorización impiden que un residente consulte o modifique PQR pertenecientes a otros usuarios.

### Estado de las cuentas

Cada usuario puede encontrarse activo o inactivo. Cuando una cuenta es desactivada:

- No puede iniciar sesión.
- Si tenía una sesión abierta, se cierra automáticamente.
- Se muestra un mensaje indicando que debe comunicarse con el administrador.

Un administrador no puede desactivar su propia cuenta, quitarse el rol de administrador ni eliminar su propio usuario desde el módulo administrativo.

## Funcionalidades implementadas

- Listado de PQRs
- Creación de PQRs
- Edición de PQRs
- Eliminación de PQRs
- Búsqueda por asunto
- Filtro por estado
- Relación entre PQR y TipoPqr
- Relación entre PQR y User

## Evidencia de funcionamiento

![Listado de PQRs](docs/evidencias/listado_pqrs.png)
