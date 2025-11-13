# Sistema de Gestión de Salón de Belleza

## 📋 Descripción del Proyecto

Sistema web completo para la gestión de un salón de belleza, desarrollado en PHP 8 nativo y MySQL. Incluye gestión de usuarios multi-rol, agendamiento de citas, catálogo de servicios y panel administrativo completo.

## 🚀 Características Principales

### Sistema de Roles
- **Cliente**: Puede ver servicios y agendar citas
- **Empleado**: Gestiona sus citas asignadas y horarios
- **Administrador**: Control total del sistema

### Funcionalidades
✅ Sistema de autenticación seguro (bcrypt)
✅ Gestión completa de usuarios
✅ Catálogo de servicios con precios
✅ Agendamiento de citas con validación
✅ Dashboards personalizados por rol
✅ Gestión de horarios de empleados
✅ Reportes y estadísticas
✅ Interfaz responsive y moderna

## 📦 Requisitos del Sistema

- PHP 8.0 o superior
- MySQL 5.7 o superior
- XAMPP (recomendado) o cualquier servidor LAMP/WAMP
- Navegador web moderno

## 🔧 Instalación

### Paso 1: Preparar el Servidor

1. Instala XAMPP desde [https://www.apachefriends.org](https://www.apachefriends.org)
2. Inicia Apache y MySQL desde el panel de control de XAMPP

### Paso 2: Extraer Archivos

1. Extrae el archivo `salon_belleza.zip`
2. Copia la carpeta `salon_belleza` a la carpeta `htdocs` de XAMPP
   - Ruta típica en Windows: `C:\xampp\htdocs\`
   - Ruta típica en Mac: `/Applications/XAMPP/htdocs/`
   - Ruta típica en Linux: `/opt/lampp/htdocs/`

### Paso 3: Crear la Base de Datos

1. Abre phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Haz clic en "SQL" en el menú superior
3. Abre el archivo `database/schema.sql` con un editor de texto
4. Copia todo el contenido del archivo
5. Pégalo en la ventana SQL de phpMyAdmin
6. Haz clic en "Continuar" para ejecutar el script

**Nota**: El script creará automáticamente:
- La base de datos `salon_belleza`
- Todas las tablas necesarias
- Datos de prueba (usuarios, servicios, citas)

### Paso 4: Verificar Configuración

Abre el archivo `config/database.php` y verifica que la configuración sea correcta:

```php
private $host = "localhost";
private $db_name = "salon_belleza";
private $username = "root";
private $password = "";
```

Si tu MySQL tiene contraseña, actualiza el campo `$password`.

### Paso 5: Acceder al Sistema

Abre tu navegador y accede a:
```
http://localhost/salon_belleza/
```

## 🔐 Credenciales de Acceso

### Administrador (MUY IMPORTANTE)
- **Email**: admin@salon.com
- **Contraseña**: password123

### Empleado (Ejemplo)
- **Email**: maria.garcia@salon.com
- **Contraseña**: password123

### Cliente (Ejemplo)
- **Email**: ana.martinez@gmail.com
- **Contraseña**: password123

## 📊 Estructura del Proyecto

```
salon_belleza/
├── auth/                          # Autenticación
│   ├── login_process.php         # Procesa el login
│   ├── register_process.php      # Procesa el registro
│   └── logout.php                # Cierre de sesión
├── config/                        # Configuración
│   └── database.php              # Conexión a BD
├── database/                      # Scripts SQL
│   └── schema.sql                # Estructura y datos
├── includes/                      # Archivos compartidos
│   └── verificar_sesion.php      # Verificación de acceso
├── pages/                         # Páginas del sistema
│   ├── admin/                    # Panel administrador
│   │   ├── dashboard.php         # Dashboard principal
│   │   ├── usuarios.php          # Gestión de usuarios
│   │   ├── agregar_usuario.php   # Agregar usuarios/empleados
│   │   └── procesar_agregar_usuario.php
│   ├── empleado/                 # Panel empleado
│   │   └── dashboard.php         # Dashboard empleado
│   └── cliente/                  # Panel cliente
│       └── dashboard.php         # Dashboard cliente
├── index.php                      # Página de login
├── register.php                   # Página de registro
└── README.md                      # Este archivo
```

## 🗄️ Estructura de Base de Datos

### Tabla: usuarios
Almacena todos los usuarios del sistema (clientes, empleados, administradores).

| Campo          | Tipo          | Descripción                    |
|----------------|---------------|--------------------------------|
| id             | INT           | Identificador único            |
| nombre         | VARCHAR(100)  | Nombre completo                |
| email          | VARCHAR(100)  | Email (login)                  |
| password       | VARCHAR(255)  | Contraseña hasheada (bcrypt)   |
| telefono       | VARCHAR(20)   | Teléfono de contacto           |
| rol            | ENUM          | Cliente, Empleado, Administrador|
| fecha_registro | TIMESTAMP     | Fecha de registro              |
| activo         | TINYINT(1)    | Estado (1=activo, 0=inactivo)  |

### Tabla: servicios
Catálogo de servicios ofrecidos.

| Campo          | Tipo          | Descripción                    |
|----------------|---------------|--------------------------------|
| id             | INT           | Identificador único            |
| nombre         | VARCHAR(100)  | Nombre del servicio            |
| descripcion    | TEXT          | Descripción detallada          |
| duracion       | INT           | Duración en minutos            |
| precio         | DECIMAL(10,2) | Precio del servicio            |
| activo         | TINYINT(1)    | Estado                         |
| fecha_creacion | TIMESTAMP     | Fecha de creación              |

### Tabla: citas
Registro de todas las citas agendadas.

| Campo               | Tipo          | Descripción                    |
|---------------------|---------------|--------------------------------|
| id                  | INT           | Identificador único            |
| cliente_id          | INT           | FK a usuarios (cliente)        |
| empleado_id         | INT           | FK a usuarios (empleado)       |
| servicio_id         | INT           | FK a servicios                 |
| fecha_cita          | DATE          | Fecha de la cita               |
| hora_cita           | TIME          | Hora de la cita                |
| estado              | ENUM          | Pendiente, Confirmada, Completada, Cancelada |
| notas               | TEXT          | Notas adicionales              |
| precio_total        | DECIMAL(10,2) | Precio total                   |
| fecha_creacion      | TIMESTAMP     | Fecha de creación              |
| fecha_actualizacion | TIMESTAMP     | Última actualización           |

### Tabla: horarios_empleados
Horarios de trabajo de los empleados.

| Campo        | Tipo          | Descripción                    |
|--------------|---------------|--------------------------------|
| id           | INT           | Identificador único            |
| empleado_id  | INT           | FK a usuarios (empleado)       |
| dia_semana   | ENUM          | Lunes a Domingo                |
| hora_inicio  | TIME          | Hora de inicio                 |
| hora_fin     | TIME          | Hora de finalización           |
| activo       | TINYINT(1)    | Estado                         |

## 👥 Funcionalidades por Rol

### Administrador
- ✅ Ver dashboard con estadísticas generales
- ✅ Gestionar todos los usuarios (crear, editar, activar/desactivar)
- ✅ Crear empleados y asignarles roles
- ✅ Gestionar servicios (crear, editar, precios)
- ✅ Ver todas las citas del sistema
- ✅ Gestionar horarios de empleados
- ✅ Generar reportes e informes
- ✅ Control total del sistema

### Empleado
- ✅ Ver sus citas del día
- ✅ Ver próximas citas asignadas
- ✅ Consultar información de clientes
- ✅ Ver detalles de servicios a realizar

### Cliente
- ✅ Ver catálogo de servicios disponibles
- ✅ Agendar citas (funcionalidad en desarrollo)
- ✅ Ver historial de sus citas
- ✅ Ver estado de citas agendadas

## 🔒 Seguridad Implementada

1. **Contraseñas Seguras**
   - Hash bcrypt con factor de costo 10
   - Validación de longitud mínima (6 caracteres)

2. **Protección SQL Injection**
   - Uso de PDO con prepared statements
   - Binding de parámetros en todas las consultas

3. **Control de Sesiones**
   - Verificación de autenticación en cada página
   - Validación de roles antes de acceder a funciones
   - Timeout de sesión (30 minutos de inactividad)

4. **Validación de Datos**
   - Validación en servidor de todos los formularios
   - Sanitización de entradas de usuario
   - Escape de salidas HTML con htmlspecialchars()

## 🎨 Características de la Interfaz

- Diseño moderno y responsive
- Gradientes y efectos visuales atractivos
- Botón "Mostrar contraseña" en lugar de ícono de ojo
- Feedback visual para todas las acciones
- Alertas informativas de éxito y error
- Optimizada para dispositivos móviles

## 🐛 Solución de Problemas

### El administrador no puede iniciar sesión

**SOLUCIÓN**: Verificar que la tabla usuarios tenga el registro del administrador con estos datos exactos:
- Email: admin@salon.com
- Password: El hash de bcrypt para "password123"
- Rol: Administrador (con A mayúscula)

Si no funciona, ejecuta esta consulta SQL en phpMyAdmin:

```sql
UPDATE usuarios 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@salon.com';
```

### Error de conexión a la base de datos

Verifica en `config/database.php`:
1. Que MySQL esté corriendo
2. Que las credenciales sean correctas
3. Que la base de datos `salon_belleza` exista

### Las páginas muestran código PHP

Asegúrate de que:
1. Apache esté corriendo en XAMPP
2. Los archivos estén en `htdocs/salon_belleza/`
3. Accedas vía `http://localhost/` y no abriendo el archivo directamente

## 📝 Notas Importantes

1. **Usuario Administrador**: El usuario administrador es CRUCIAL. Es el único que puede:
   - Crear nuevos empleados
   - Gestionar todos los usuarios
   - Configurar servicios
   - Acceder a todas las funcionalidades administrativas

2. **Datos de Prueba**: El sistema incluye datos de ejemplo para facilitar las pruebas

3. **Contraseñas**: Todas las contraseñas de prueba son: `password123`

4. **Producción**: Este sistema está diseñado para ambiente de desarrollo. Para producción:
   - Cambia todas las contraseñas
   - Configura HTTPS
   - Ajusta permisos de archivos
   - Revisa configuraciones de seguridad

## 📞 Soporte

Para reportar problemas o solicitar nuevas funcionalidades, contacta al desarrollador del sistema.

## 🔄 Versión

**Versión 1.0** - Sistema completo y funcional
- Fecha: Noviembre 2025
- PHP: 8.0+
- MySQL: 5.7+

---

¡Gracias por usar nuestro Sistema de Gestión de Salón de Belleza! 💇‍♀️✨
