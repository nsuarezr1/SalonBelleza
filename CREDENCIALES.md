# 🔐 CREDENCIALES DE ACCESO AL SISTEMA

## Sistema de Gestión de Salón de Belleza

---

## ⚠️ IMPORTANTE - LEER ANTES DE USAR

Todas las contraseñas de los usuarios de prueba son: **password123**

La contraseña está hasheada con bcrypt en la base de datos para seguridad.

---

## 👨‍💼 ADMINISTRADOR (MUY IMPORTANTE)

Este es el usuario MÁS IMPORTANTE del sistema. Tiene control total.

**Email**: admin@salon.com  
**Contraseña**: password123  
**Rol**: Administrador

### Capacidades del Administrador:
✅ Gestionar todos los usuarios (crear, editar, desactivar)
✅ Crear empleados y asignar roles
✅ Gestionar catálogo de servicios
✅ Ver todas las citas del sistema
✅ Configurar horarios de empleados
✅ Generar reportes e informes
✅ Acceso completo a todas las funcionalidades

**NOTA CRÍTICA**: Si este usuario no puede iniciar sesión, ejecuta esta consulta en phpMyAdmin:

```sql
UPDATE usuarios 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    rol = 'Administrador'
WHERE email = 'admin@salon.com';
```

---

## 👨‍💼 EMPLEADOS

### Empleado 1 - María García
**Email**: maria.garcia@salon.com  
**Contraseña**: password123  
**Rol**: Empleado

**Horario de trabajo**:
- Lunes a Viernes: 9:00 AM - 6:00 PM
- Sábado: 10:00 AM - 2:00 PM

### Empleado 2 - Carlos Rodríguez
**Email**: carlos.rodriguez@salon.com  
**Contraseña**: password123  
**Rol**: Empleado

**Horario de trabajo**:
- Lunes a Viernes: 10:00 AM - 7:00 PM

### Capacidades de los Empleados:
✅ Ver sus citas del día
✅ Ver próximas citas asignadas
✅ Consultar información de clientes
✅ Ver detalles de servicios a realizar

---

## 👤 CLIENTES

### Cliente 1 - Ana Martínez
**Email**: ana.martinez@gmail.com  
**Contraseña**: password123  
**Rol**: Cliente

### Cliente 2 - Luis Fernández
**Email**: luis.fernandez@gmail.com  
**Contraseña**: password123  
**Rol**: Cliente

### Capacidades de los Clientes:
✅ Ver catálogo de servicios
✅ Agendar citas
✅ Ver historial de citas
✅ Consultar detalles de próximas citas

---

## 📝 CÓMO CREAR NUEVOS USUARIOS

### Opción 1: Registro Público (Solo Clientes)
1. Ve a: http://localhost/salon_belleza/register.php
2. Completa el formulario
3. Los nuevos registros automáticamente son "Cliente"

### Opción 2: Panel de Administrador (Cualquier Rol)
1. Inicia sesión como administrador
2. Ve a "Gestionar Usuarios"
3. Click en "Agregar Usuario"
4. Selecciona el rol: Cliente, Empleado o Administrador
5. Completa el formulario y guarda

**IMPORTANTE**: Solo el administrador puede crear empleados y otros administradores.

---

## 🔒 SEGURIDAD DE CONTRASEÑAS

### Almacenamiento
- Todas las contraseñas se almacenan hasheadas con bcrypt
- Factor de costo: 10
- Nunca se almacenan contraseñas en texto plano

### Validación
- Longitud mínima: 6 caracteres
- Se valida en servidor y cliente

### Hash de "password123" (para referencia técnica):
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

Este hash ya está configurado para todos los usuarios de prueba en el archivo schema.sql

---

## 🎯 ROLES Y PERMISOS

### Administrador (admin@salon.com)
- **Acceso**: TOTAL
- **Dashboard**: /pages/admin/dashboard.php
- **Puede**: Todo

### Empleado (maria.garcia@salon.com, carlos.rodriguez@salon.com)
- **Acceso**: LIMITADO a sus propias citas
- **Dashboard**: /pages/empleado/dashboard.php
- **Puede**: Ver agenda, gestionar citas asignadas

### Cliente (ana.martinez@gmail.com, luis.fernandez@gmail.com)
- **Acceso**: LIMITADO a servicios y sus citas
- **Dashboard**: /pages/cliente/dashboard.php
- **Puede**: Ver servicios, agendar citas, ver historial

---

## 🚨 SOLUCIÓN DE PROBLEMAS DE LOGIN

### Error: "Email o contraseña incorrectos"

**Causa común**: El hash de la contraseña no coincide

**Solución rápida**:
1. Abre phpMyAdmin
2. Ve a la tabla `usuarios`
3. Ejecuta esta consulta para resetear la contraseña del administrador:

```sql
UPDATE usuarios 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@salon.com';
```

4. Intenta login nuevamente con:
   - Email: admin@salon.com
   - Password: password123

### Error: "Tu cuenta está inactiva"

**Solución**:
```sql
UPDATE usuarios SET activo = 1 WHERE email = 'admin@salon.com';
```

### Verificar usuarios en la base de datos

```sql
SELECT id, nombre, email, rol, activo FROM usuarios;
```

Deberías ver al menos 5 usuarios:
1. Administrador Sistema (admin@salon.com) - Administrador
2. María García - Empleado
3. Carlos Rodríguez - Empleado
4. Ana Martínez - Cliente
5. Luis Fernández - Cliente

---

## 📊 DATOS DE PRUEBA INCLUIDOS

El sistema incluye:
- ✅ 1 Administrador
- ✅ 2 Empleados con horarios configurados
- ✅ 2 Clientes
- ✅ 10 Servicios diversos (corte, tinte, manicure, etc.)
- ✅ 3 Citas de ejemplo
- ✅ Horarios de trabajo de los empleados

Todo listo para probar inmediatamente después de la instalación.

---

## 🔄 CAMBIAR CONTRASEÑAS

### Para Cambiar tu Contraseña (Futura Implementación)

Por ahora, puedes cambiar contraseñas directamente en la base de datos:

```php
<?php
// Script para generar hash de nueva contraseña
$nueva_password = "tu_nueva_contraseña";
$hash = password_hash($nueva_password, PASSWORD_DEFAULT);
echo $hash;
?>
```

Luego actualiza en la BD:
```sql
UPDATE usuarios SET password = 'HASH_GENERADO' WHERE email = 'tu@email.com';
```

---

## ⚙️ CONFIGURACIÓN RECOMENDADA PARA PRODUCCIÓN

**NO uses estas contraseñas en producción**. Para un entorno real:

1. Cambia TODAS las contraseñas por contraseñas seguras
2. Usa contraseñas de al menos 12 caracteres
3. Incluye mayúsculas, minúsculas, números y símbolos
4. No reutilices contraseñas
5. Activa HTTPS en tu servidor
6. Configura backups automáticos de la base de datos

---

**Fecha de creación**: Noviembre 2025  
**Última actualización**: Noviembre 2025  
**Sistema**: Salón de Belleza v1.0
