# ⏱ TimeControl
### Sistema de Control Horario y Gestión de Proyectos

---

## Requisitos

| Software | Versión mínima |
|----------|---------------|
| PHP      | 8.0+          |
| MySQL    | 5.7+ / MariaDB 10.4+ |
| Apache   | 2.4+ (con mod_rewrite) |

---

## Instalación

### 1. Copiar archivos
```bash
cp -r timecontrol/ /var/www/html/timecontrol
```

### 2. Crear la base de datos
```bash
mysql -u root -p < sql/schema.sql
```

O desde phpMyAdmin: importar el archivo `sql/schema.sql`.

### 3. Configurar conexión
Edita `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'timecontrol');
define('DB_USER', 'tu_usuario_mysql');
define('DB_PASS', 'tu_password_mysql');
define('APP_URL',  'http://localhost/timecontrol');
```

### 4. Configurar email (opcional)
```php
define('MAIL_HOST', 'smtp.tuempresa.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'timecontrol@tuempresa.com');
define('MAIL_PASS', 'password_smtp');
```

> ⚠️ El envío de emails usa `mail()` nativo de PHP. Para producción se recomienda instalar [PHPMailer](https://github.com/PHPMailer/PHPMailer) y adaptar `EmailHelper.php`.

### 5. Permisos
```bash
chmod 755 /var/www/html/timecontrol
chmod -R 644 /var/www/html/timecontrol/public/
```

### 6. Activar mod_rewrite en Apache
```bash
a2enmod rewrite
service apache2 restart
```

Asegúrate de tener `AllowOverride All` en tu VirtualHost.

### 7. Configurar cron job
```bash
crontab -e
```
Añadir (detectar olvidos de fichaje cada día a las 23:00):
```
0 23 * * 1-5 /usr/bin/php /var/www/html/timecontrol/cron/detectar_olvidos.php >> /var/log/timecontrol.log 2>&1
```

---

## Acceso inicial

| URL | `http://localhost/timecontrol` |
|-----|-------------------------------|
| Email | `admin@empresa.com` |
| Contraseña | `Admin1234!` |

> ⚠️ **Cambia la contraseña del admin tras el primer acceso.**

---

## Estructura de carpetas

```
timecontrol/
├── index.php                  # Front controller (router)
├── .htaccess                  # Rewrite rules de Apache
├── config/
│   ├── config.php             # Configuración global
│   └── Database.php           # Clase de conexión PDO (Singleton)
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── FichajeController.php
│   │   ├── ImputacionController.php
│   │   ├── UsuarioController.php
│   │   ├── ProyectoController.php
│   │   ├── OtherControllers.php   # Informe, Incidencia, Horario
│   │   └── ...
│   ├── models/
│   │   └── FichajeModel.php
│   ├── helpers/
│   │   └── EmailHelper.php
│   └── views/
│       ├── shared/
│       │   ├── header.php
│       │   ├── footer.php
│       │   ├── login.php
│       │   ├── 403.php
│       │   └── 404.php
│       ├── employee/
│       │   ├── dashboard.php
│       │   ├── historial.php
│       │   └── imputaciones.php
│       ├── boss/
│       │   ├── dashboard.php
│       │   ├── informes.php
│       │   └── incidencias.php
│       └── admin/
│           ├── dashboard.php
│           ├── usuarios.php
│           ├── proyectos.php
│           └── horarios.php
├── public/
│   ├── css/
│   │   └── main.css
│   └── js/
│       ├── main.js
│       ├── fichaje.js
│       ├── imputaciones.js
│       ├── boss.js
│       ├── usuarios.js
│       ├── proyectos.js
│       ├── horarios.js
│       └── informes.js
├── cron/
│   └── detectar_olvidos.php
└── sql/
    └── schema.sql
```

---

## Roles y permisos

| Rol | Dashboard | Historial | Imputar | Supervisión | Informes | Admin |
|-----|-----------|-----------|---------|-------------|----------|-------|
| `empleado` | ✓ | ✓ | ✓ | — | — | — |
| `jefe` | ✓ | ✓ | ✓ | ✓ | ✓ | — |
| `jefe_departamento` | ✓ | ✓ | ✓ | ✓ (solo su dpto.) | ✓ | — |
| `admin` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |

---

## Funcionalidades principales

### Fichaje
- Botón único entrada/salida (AJAX, sin recarga)
- Detección automática de **retrasos** según horario asignado + tolerancia
- Detección de **salida anticipada**
- Timeline visual del día

### Incidencias automáticas
- Retraso al fichar entrada
- Salida anticipada
- Olvido de entrada/salida (vía cron job nocturno)
- Email automático al empleado en cada incidencia

### Imputación de horas
- Asociar horas trabajadas a proyectos
- Validación: máximo 12h/día
- Resumen visual por proyecto
- Historial mensual con filtros

### Informes (jefes/admin)
- Tipo: **Diario**, **Semanal**, **Mensual**
- Filtros: empleado, proyecto, rango de fechas
- 3 tabs: Imputaciones, Asistencia, Incidencias
- **Exportar a CSV**

### Administración
- CRUD completo de usuarios con asignación de rol, departamento y horario
- Asignación de empleados a proyectos
- Gestión de horarios laborales con tolerancia configurable
- Panel de incidencias con estados: pendiente / revisada / resuelta

---

## Seguridad implementada

- Contraseñas hasheadas con **bcrypt** (cost 12)
- **Prepared statements** en todas las consultas SQL
- Regeneración de session ID en login (anti session fixation)
- Validación de roles en cada controlador
- Cabeceras de seguridad HTTP via `.htaccess`
- Sanitización de output con `htmlspecialchars()`

---

## Personalización rápida

### Añadir un departamento
1. Actualizar el `ENUM` en `ALTER TABLE usuarios MODIFY departamento ENUM(...)` en MySQL
2. Añadir la entrada en `$depto_labels` en las vistas correspondientes
3. Añadir el color en `.depto-nuevodpto` en `main.css`

### Cambiar el logo/nombre
Editar `APP_NAME` en `config/config.php` y el ícono `⏱` en `views/shared/header.php`.

---

## Licencia
Proyecto de uso interno. Adaptar según necesidades de la empresa.
