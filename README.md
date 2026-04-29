# TimeControl — Sistema de Control Horario

Sistema completo de gestión de fichajes, proyectos e imputación de horas para empresas.

---

## Estructura del proyecto

```
timecontrol/
├── index.php                    ← Front Controller (router principal)
├── .htaccess                    ← Configuración Apache
├── config/
│   └── database.php             ← Configuración BD y app
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── FichajeController.php
│   │   ├── ImputacionController.php
│   │   ├── ManagerController.php
│   │   ├── AdminController.php
│   │   └── InformeController.php
│   ├── models/
│   │   ├── Usuario.php
│   │   ├── Fichaje.php
│   │   ├── Proyecto.php
│   │   ├── Imputacion.php
│   │   └── Incidencia.php
│   └── views/
│       ├── auth/       login.php
│       ├── employee/   dashboard.php | historial.php | imputacion.php | mis_imputaciones.php
│       ├── manager/    dashboard.php | empleados.php | incidencias.php | informes.php
│       ├── admin/      dashboard.php | usuarios.php | nuevo_usuario.php | editar_usuario.php
│       │               proyectos.php | horarios.php
│       └── shared/     header.php | footer.php | 404.php | error.php
├── public/
│   ├── css/style.css
│   └── js/app.js
├── mail/
│   └── Mailer.php
├── cron/
│   └── detectar_olvidos.php
└── sql/
    └── database.sql
```

---

## Instalación

### 1. Requisitos
- PHP 8.0 o superior
- MySQL 8.0 o superior
- Apache con mod_rewrite activado

### 2. Base de datos
```sql
-- Ejecutar el script completo:
mysql -u root -p < sql/database.sql
```

### 3. Configuración
Editar `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'timecontrol');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('APP_URL', 'http://tu-dominio.com/timecontrol');
```

### 4. Permisos de Apache
Asegúrate de que `AllowOverride All` está activado en tu VirtualHost.

### 5. Email (opcional)
En `config/database.php`, configura las constantes MAIL_* para habilitar el envío real de correos. Por defecto, en modo dev solo loguea.

---

## Acceso al sistema

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@empresa.com | password |
| Jefe Dpto. | carlos@empresa.com | password |
| Jefe | maria@empresa.com | password |
| Empleado | pedro@empresa.com | password |

> ⚠️ Cambiar las contraseñas antes de poner en producción.

---

## Funcionalidades por rol

### Empleado
- Fichar entrada/salida (AJAX, sin recargar página)
- Ver estado actual y fichajes del día
- Historial de fichajes con resumen diario
- Imputar horas por proyecto
- Ver historial de horas imputadas

### Jefe / Jefe de Departamento
- Dashboard en tiempo real: quién está dentro, quién no ha fichado
- Ver empleados con horas trabajadas y tardanzas
- Gestionar incidencias (resolver con notas)
- Informes de horas: diario, semanal, mensual, personalizado
- Filtrar por empleado, proyecto y fechas

### Administrador
- Todo lo anterior más:
- CRUD completo de usuarios
- Gestión de proyectos (crear, editar, asignar empleados)
- Definir horarios laborales con tolerancia de tardanza

---

## Cron Jobs

Añadir a crontab del servidor:
```
# Detectar olvidos de fichaje cada mañana a las 09:15
15 9 * * 1-5 php /var/www/timecontrol/cron/detectar_olvidos.php >> /var/log/timecontrol_cron.log 2>&1
```

---

## Seguridad implementada
- Prepared statements en todas las consultas SQL (prevención SQL Injection)
- `password_hash()` / `password_verify()` para contraseñas (bcrypt)
- Sesiones PHP con `session_regenerate_id()` en login
- Control de roles en cada ruta
- `htmlspecialchars()` en todas las salidas de datos de usuario (XSS)
- Cabeceras HTTP de seguridad (.htaccess)
- Soft-delete de usuarios (no se borran físicamente los registros)
