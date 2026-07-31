# Arquitectura actual

## 1. Propósito

Este documento describe la arquitectura implementada de Resuelve a partir de
la estructura y las dependencias del repositorio. No constituye una decisión
arquitectónica futura ni reemplaza un ADR.

## 2. Estilo arquitectónico observado

Resuelve es una aplicación web monolítica basada en Laravel:

- un único proyecto contiene interfaz, lógica de aplicación, dominio y acceso
  a datos;
- las solicitudes de negocio se reciben por rutas web;
- los controladores coordinan validación, autorización, persistencia y efectos
  secundarios;
- Eloquent representa y persiste las entidades;
- Blade renderiza HTML en el servidor;
- JavaScript agrega interacciones de interfaz sin implementar una SPA;
- no existe una API de aplicación separada.

```text
Navegador
   │ HTTP + sesión + CSRF
   ▼
Rutas web / Middleware
   │
   ▼
Controladores ─────► Políticas y reglas de autorización
   │
   ├───────────────► Notificaciones / correo
   ├───────────────► Almacenamiento de archivos
   ▼
Modelos Eloquent
   │
   ▼
Base de datos

Controladores ─────► Vistas Blade ─────► HTML + CSS + JavaScript
Scheduler ─────────► Comando de recordatorios ─► Notificaciones
```

## 3. Capas y responsabilidades

### 3.1 Entrada HTTP

`routes/web.php` define:

- rutas públicas para autenticación y recuperación;
- rutas protegidas por `auth` para toda la operación;
- descarga autorizada de adjuntos;
- recursos y acciones específicas de PQR;
- configuración, usuarios, informes y gestión complementaria.

No existen archivos de rutas para API. `bootstrap/app.php` configura el
renderizado JSON para solicitudes cuyo path coincida con `api/*`, pero no
registra rutas de aplicación bajo ese prefijo.

### 3.2 Middleware

La aplicación configura:

- middleware estándar de los grupos `web`, `auth` y `guest`;
- confianza en todos los proxies;
- `AuditMutations` agregado al grupo `web`.

`AuditMutations` ejecuta primero la solicitud y registra después las mutaciones
HTTP exitosas realizadas por un usuario autenticado cuando existe la tabla
`audit_logs`.

### 3.3 Controladores

| Controlador | Responsabilidad principal |
| --- | --- |
| `AuthController` | Sesión y recuperación de contraseña. |
| `PqrController` | CRUD, panel, búsqueda, filtros, adjuntos y reglas al crear. |
| `PqrReplyController` | Respuestas, borradores y sus archivos. |
| `PqrInternalCommentController` | Comentarios privados. |
| `PqrQuickActionController` | Cambios rápidos de estado y responsable. |
| `NotificationController` | Consulta y lectura de notificaciones. |
| `ProfileController` | Perfil y contraseña del usuario actual. |
| `UserManagementController` | Administración de cuentas y roles. |
| `SettingsController` | Identidad de la copropiedad y logo. |
| `ReportController` | CSV, XLSX y PDF. |
| `ComplementaryController` | Carga, herramientas, etiquetas, encuesta, residentes y auditoría. |

La lógica está concentrada principalmente en controladores y modelos. No existe
una capa propia de servicios, acciones, casos de uso o repositorios.

### 3.4 Dominio y persistencia

Los modelos de `app/Models` usan Eloquent y concentran:

- asignación masiva;
- casts;
- relaciones;
- scopes y propiedades derivadas;
- capacidades auxiliares del usuario;
- valores predeterminados de configuración.

Las migraciones definen integridad referencial y las factories y el seeder
preparan datos de prueba o demostración.

### 3.5 Autorización

La autorización combina:

- `PqrPolicy` para operaciones principales de PQR;
- métodos del modelo `User`;
- verificaciones directas de rol en controladores;
- condiciones de visibilidad en Blade.

No existe un mecanismo central único para todas las capacidades.

### 3.6 Presentación

Las vistas Blade se organizan por:

- autenticación;
- layout;
- PQR;
- usuarios;
- perfil;
- configuración;
- notificaciones;
- informes;
- gestión complementaria.

`AppServiceProvider` usa un view composer global para compartir
`SiteSetting::current()` con todas las vistas.

### 3.7 Frontend

Vite compila:

- `resources/css/app.css`;
- `resources/js/app.js`.

El JavaScript nativo gestiona menú, tema, confirmaciones, campos de archivo,
previsualización del logo, contador de caracteres y selección de plantillas.
Las preferencias de tema y menú se guardan en `localStorage`.

### 3.8 Notificaciones

`PqrEventNotification` utiliza:

- canal `database`;
- canal `mail`.

La notificación no implementa `ShouldQueue`, aunque usa el trait `Queueable`.
Por ello, el código no acredita que sus envíos se despachen como jobs en cola.

### 3.9 Automatización programada

`routes/console.php` define el comando closure
`pqrs:send-reminders` y lo programa diariamente a las 08:00 mediante el
scheduler de Laravel.

No existen clases propias en `app/Console`, `app/Jobs`, `app/Events` o
`app/Listeners`.

### 3.10 Informes

`ReportController` consulta directamente modelos Eloquent y construye:

- streams CSV;
- libros XLSX mediante PhpSpreadsheet;
- PDF mediante una vista Blade y DomPDF.

No existe una capa de consulta o servicio compartido fuera del controlador.

## 4. Flujos principales

### 4.1 Solicitud web autenticada

1. El navegador envía una solicitud con sesión y token CSRF cuando corresponde.
2. El middleware web resuelve sesión, autenticación y demás funciones estándar.
3. La ruta invoca un controlador o closure.
4. El controlador valida y autoriza.
5. Eloquent consulta o modifica datos.
6. El controlador devuelve una vista, redirección, descarga o stream.
7. Para una mutación exitosa, `AuditMutations` intenta crear un registro de
   auditoría.

### 4.2 Radicación de PQR

1. `PqrController::store()` valida la entrada.
2. Autoriza `create`.
3. Inicia una transacción.
4. Crea la PQR.
5. aplica una regla automática opcional;
6. guarda adjuntos y actividad;
7. confirma la transacción;
8. notifica a administradores y gestores;
9. redirige al detalle.

Las notificaciones ocurren después de la transacción y no están envueltas en
ella.

### 4.3 Envío de respuesta

1. El controlador comprueba `canManagePqrs()`.
2. Valida cuerpo, acción y archivos.
3. almacena los archivos;
4. crea la respuesta;
5. registra una actividad;
6. si se envía, cambia la PQR a `respondida` y notifica al radicador.

Este flujo no usa una transacción explícita.

### 4.4 Exportación

1. Se autoriza la consulta de PQR.
2. Se aplica el alcance visible por rol.
3. Se reproducen filtros.
4. Se cargan relaciones.
5. El formato solicitado se genera en la misma solicitud HTTP.

## 5. Persistencia y servicios de infraestructura

| Recurso | Configuración observada |
| --- | --- |
| Base de datos | SQLite por defecto en `.env.example`; MySQL 8.4 en Sail. |
| Sesión | Base de datos por defecto. |
| Caché | Base de datos por defecto. |
| Cola | Base de datos por defecto. |
| Correo | Driver `log` por defecto. |
| Archivos privados | Disco `local`. |
| Archivos públicos | Disco `public`. |
| Logs | Configuración estándar de Laravel. |

La disponibilidad efectiva depende de las variables del entorno desplegado.
Los valores sensibles del archivo `.env` no fueron inspeccionados ni se
documentan.

## 6. Empaquetado y ejecución

### Backend

- PHP `^8.3`;
- Laravel `^13.8`;
- Composer para dependencias y scripts.

### Frontend

- Node.js/npm como herramientas de construcción;
- Vite 8;
- Tailwind CSS 4.

### Contenedores

`compose.yaml` usa Laravel Sail:

- contenedor de aplicación construido desde runtime PHP 8.5 de Sail;
- contenedor MySQL 8.4;
- volúmenes para código y datos;
- puertos configurables para HTTP, Vite y MySQL.

### Demostración

El directorio `scripts` contiene una definición `launchd` y un script para
operar un túnel temporal de Cloudflare hacia `http://localhost:80`.

Este mecanismo es auxiliar y específico de macOS; no constituye una plataforma
de despliegue productivo documentada.

## 7. Seguridad observable

- autenticación basada en sesión;
- regeneración de sesión al iniciar;
- invalidación al cerrar;
- protección CSRF en formularios Blade;
- contraseñas casteadas como `hashed`;
- recuperación mediante broker de Laravel;
- descargas verificadas contra la visibilidad de la PQR;
- validación de tipo y tamaño de archivos;
- cookies HTTP-only por defecto;
- confianza en proxies configurada con `*`.

La confianza indiscriminada en proxies y la configuración efectiva de cookies,
correo y entorno deben evaluarse según el despliegue. Este documento solo
registra la configuración actual.

## 8. Dependencias arquitectónicas

| Dependencia | Uso |
| --- | --- |
| Laravel Framework | HTTP, Eloquent, Blade, autenticación, validación, scheduler y notificaciones. |
| DomPDF para Laravel | Generación de PDF. |
| PhpSpreadsheet | Generación de XLSX. |
| Vite | Construcción de activos. |
| Tailwind CSS | Utilidades y procesamiento CSS. |
| Laravel Sail | Entorno opcional con contenedores. |
| PHPUnit | Pruebas automatizadas. |

## 9. Restricciones y hallazgos

1. La arquitectura no implementa multi-copropiedad.
2. No existe API de aplicación.
3. No existe módulo de IA.
4. La lógica de negocio y los efectos secundarios se concentran en
   controladores.
5. La autorización está distribuida entre política, modelo, controladores y
   vistas.
6. Algunos flujos con varias escrituras no utilizan transacción explícita.
7. Notificaciones y exportaciones se generan sin evidencia de procesamiento
   asíncrono.
8. La configuración global usa el primer registro de `site_settings`.
9. `trustProxies(at: '*')` confía en todos los proxies.
10. El archivo `plist` del túnel contiene rutas distintas de la ubicación real
    inspeccionada del repositorio.

Estos hallazgos no definen una arquitectura objetivo.

## 10. Evidencias utilizadas

### Arranque, rutas y aplicación

- `bootstrap/app.php`
- `bootstrap/providers.php`
- `routes/web.php`
- `routes/console.php`
- `app/Http/Controllers/`
- `app/Http/Middleware/AuditMutations.php`
- `app/Policies/PqrPolicy.php`
- `app/Providers/AppServiceProvider.php`
- `app/Notifications/PqrEventNotification.php`
- `app/Models/`

### Presentación

- `resources/views/`
- `resources/js/app.js`
- `resources/css/app.css`
- `vite.config.js`

### Persistencia y configuración

- `database/migrations/`
- `database/factories/`
- `database/seeders/DatabaseSeeder.php`
- `config/`
- `.env.example`

### Dependencias y operación

- `composer.json`
- `package.json`
- `compose.yaml`
- `scripts/demo-tunnel.sh`
- `scripts/com.resuelve-pqrs.demo-tunnel.plist`
- `tests/`

## Control documental

- **Versión:** v1.0
- **Fecha de creación:** 30 de julio de 2026
- **Fecha de última actualización:** 30 de julio de 2026
