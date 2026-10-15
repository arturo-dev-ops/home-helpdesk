# Especificación del Proyecto: Helpdesk Doméstico ("Incidencias Casa")

## 1. Objetivo y Alcance
Sistema local en PHP puro (con enfoque POO y arquitectura MVC ligera) para gestionar incidencias del hogar entre los miembros de la familia (mamá, hermana) y el administrador/técnico (Arturo).

## 2. Base de Datos (MySQL)
- **Tabla `users`**:
  - `id` (PK, AutoIncrement)
  - `nombre` (VARCHAR)
  - `email` (VARCHAR, Unique)
  - `password` (VARCHAR)
  - `rol` (ENUM: 'admin', 'familiar')

- **Tabla `incidencias`**:
  - `id` (PK, AutoIncrement)
  - `user_id` (FK a users.id)
  - `titulo` (VARCHAR, máx 3-4 palabras, ej: "Arreglar grifo cocina")
  - `estado` (ENUM: 'sin_hacer', 'en_proceso', 'hecho')
  - `fecha_entrada` (DATETIME, automático)
  - `fecha_salida` (DATETIME, nulo por defecto)

## 3. Principios de Arquitectura
- **Principio DRY**: Centralización de la conexión a base de datos (Singleton) y abstracción de consultas en Repositorios.
- **POO**: Clases independientes con responsabilidades únicas (`User`, `Incident`).



helpdesk/
├── app/
│   ├── Controllers/AuthController.php y IncidentController.php
│   ├── Models/User.php y Incident.php
│   ├── Repositories/UserRepository.php y IncidentRepository.php
│   ├── Support/helpers.php
│   ├── Database.php
│   └── bootstrap.php
├── assets/css/styles.css
├── assets/js/confirm-delete.js
├── assets/js/register-service-worker.js
├── assets/icons/                  <-- Iconos de instalación móvil
├── manifest.json y service-worker.js
├── offline.html
├── tools/set_password.php   <-- Configura contraseñas desde consola
├── views/auth/login.php
├── views/incidents/listado.php
├── index.php               <-- Entrada HTTP de la página principal
├── login.php y logout.php  <-- Entradas HTTP de autenticación
├── guardar_incidencia.php  <-- Entrada HTTP para formularios
└── database.sql            <-- Esquema de las tablas MySQL

## 4. Puesta en marcha local
1. Importa `database.sql` en MySQL (por ejemplo, desde phpMyAdmin).
2. En Laragon, los valores por defecto son `127.0.0.1`, base de datos `home_helpdesk`, usuario `root` y contraseña vacía. Se pueden cambiar con las variables de entorno `HOME_HELPDESK_DB_HOST`, `HOME_HELPDESK_DB_NAME`, `HOME_HELPDESK_DB_USER` y `HOME_HELPDESK_DB_PASSWORD`.
3. `database.sql` crea inicialmente a Arturo (`admin`), Mamá (`familiar`) e Inés (`familiar`) con correos locales `@casa.local`, sin contraseñas. Configura una contraseña distinta para cada uno desde una terminal local, usando `php tools\set_password.php arturo@casa.local`, `php tools\set_password.php mama@casa.local` y `php tools\set_password.php ines@casa.local`. El script pide cada contraseña dos veces (mínimo 12 caracteres) y guarda únicamente su hash.
4. Inicia sesión en `login.php` con el correo local y la contraseña que configuraste. Mamá e Inés pueden crear incidencias para sí mismas y consultar su propio listado. Arturo no crea incidencias: su panel abre en «Sin hacer» y puede filtrar entre «Sin hacer», «En proceso» y «Hecho», con el total de cada estado. Desde ahí gestiona y resuelve las incidencias.
5. Arturo puede eliminar manualmente desde el panel las incidencias en estado `hecho`; la eliminación borra el registro de MySQL y no se puede deshacer.
6. Sirve la carpeta con Apache/PHP. Los títulos aceptan hasta cuatro palabras. Al marcar una incidencia como `hecho`, se registra `fecha_salida`; al reabrirla, esa fecha se limpia.
7. La aplicación se puede instalar como PWA desde el navegador del móvil. Necesita servirse desde HTTPS (o localhost durante desarrollo). En Android, abre el sitio en Chrome y elige «Instalar aplicación»; en iPhone, abre el sitio en Safari y elige «Añadir a pantalla de inicio». El dominio local `home-helpdesk.test` no estará disponible desde el móvil: para ello hay que publicar la app en un dominio con HTTPS. El service worker solo almacena recursos estáticos; las páginas con sesión y las operaciones de incidencias siempre requieren conexión, y sin red se muestra una página informativa offline.