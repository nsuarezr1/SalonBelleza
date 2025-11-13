# Documentación Técnica - Modelo de Datos

## Sistema de Gestión de Salón de Belleza

---

## 📊 Diagrama Entidad-Relación

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│    USUARIOS     │         │     CITAS       │         │   SERVICIOS     │
├─────────────────┤         ├─────────────────┤         ├─────────────────┤
│ PK id           │◄───────┤ FK cliente_id   │         │ PK id           │
│    nombre       │         │ FK empleado_id  ├────────►│    nombre       │
│    email (UQ)   │         │ FK servicio_id  │         │    descripcion  │
│    password     │         │    fecha_cita   │         │    duracion     │
│    telefono     │         │    hora_cita    │         │    precio       │
│    rol          │         │    estado       │         │    activo       │
│    activo       │         │    notas        │         │    fecha_cre... │
│    fecha_reg... │         │    precio_total │         └─────────────────┘
└─────────────────┘         │    fecha_cre... │
        │                   │    fecha_act... │
        │                   └─────────────────┘
        │
        │                   ┌─────────────────────┐
        └──────────────────►│ HORARIOS_EMPLEADOS │
                            ├─────────────────────┤
                            │ PK id               │
                            │ FK empleado_id      │
                            │    dia_semana       │
                            │    hora_inicio      │
                            │    hora_fin         │
                            │    activo           │
                            └─────────────────────┘
```

---

## 📋 Descripción Detallada de Tablas

### 1. USUARIOS

Tabla central que almacena todos los usuarios del sistema con sus respectivos roles.

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    rol ENUM('Cliente', 'Empleado', 'Administrador') NOT NULL DEFAULT 'Cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
);
```

**Campos:**
- `id`: Identificador único auto-incremental
- `nombre`: Nombre completo del usuario (máx. 100 caracteres)
- `email`: Correo electrónico único para login
- `password`: Hash bcrypt de la contraseña (255 caracteres)
- `telefono`: Número de contacto opcional (20 caracteres)
- `rol`: Tipo de usuario (Cliente, Empleado, Administrador)
- `fecha_registro`: Timestamp automático de creación
- `activo`: Estado del usuario (1=activo, 0=inactivo)

**Índices:**
- PRIMARY KEY en `id`
- UNIQUE INDEX en `email`
- INDEX en `rol` (para búsquedas por tipo de usuario)

**Reglas de Negocio:**
- El email debe ser único en el sistema
- La contraseña se almacena hasheada con bcrypt (factor 10)
- Por defecto, nuevos usuarios son "Cliente" y están activos
- No se eliminan físicamente, solo se desactivan

---

### 2. SERVICIOS

Catálogo de todos los servicios que ofrece el salón.

```sql
CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    duracion INT NOT NULL COMMENT 'Duración en minutos',
    precio DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Campos:**
- `id`: Identificador único
- `nombre`: Nombre del servicio (ej: "Corte de Cabello Dama")
- `descripcion`: Descripción detallada del servicio
- `duracion`: Tiempo estimado en minutos (INT)
- `precio`: Costo del servicio (DECIMAL 10,2)
- `activo`: Si el servicio está disponible
- `fecha_creacion`: Timestamp de registro

**Índices:**
- PRIMARY KEY en `id`
- INDEX en `nombre` (búsquedas rápidas)
- INDEX en `activo` (filtrar servicios disponibles)

**Reglas de Negocio:**
- Duración debe ser múltiplo de 15 minutos
- Precio no puede ser negativo
- Servicios inactivos no se muestran a clientes
- No se eliminan físicamente

---

### 3. CITAS

Registro completo de todas las citas agendadas en el sistema.

```sql
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    empleado_id INT NOT NULL,
    servicio_id INT NOT NULL,
    fecha_cita DATE NOT NULL,
    hora_cita TIME NOT NULL,
    estado ENUM('Pendiente', 'Confirmada', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
    notas TEXT,
    precio_total DECIMAL(10,2) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (empleado_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE
);
```

**Campos:**
- `id`: Identificador único
- `cliente_id`: FK a usuarios (rol Cliente)
- `empleado_id`: FK a usuarios (rol Empleado)
- `servicio_id`: FK a servicios
- `fecha_cita`: Día de la cita (DATE)
- `hora_cita`: Hora de inicio (TIME)
- `estado`: Estado actual de la cita
- `notas`: Observaciones adicionales
- `precio_total`: Precio al momento de agendar
- `fecha_creacion`: Timestamp de creación
- `fecha_actualizacion`: Se actualiza automáticamente

**Índices:**
- PRIMARY KEY en `id`
- INDEX en `fecha_cita` (consultas por fecha)
- INDEX en `hora_cita` (ordenamiento)
- INDEX en `cliente_id` (historial del cliente)
- INDEX en `empleado_id` (agenda del empleado)
- INDEX en `estado` (filtros por estado)

**Relaciones:**
- `cliente_id` → `usuarios.id` (ON DELETE CASCADE)
- `empleado_id` → `usuarios.id` (ON DELETE CASCADE)
- `servicio_id` → `servicios.id` (ON DELETE CASCADE)

**Reglas de Negocio:**
- No puede haber dos citas del mismo empleado al mismo tiempo
- La hora debe estar dentro del horario del empleado
- El precio_total se copia del servicio al agendar
- Estados: Pendiente → Confirmada → Completada (o Cancelada)
- Las citas completadas no se pueden editar

---

### 4. HORARIOS_EMPLEADOS

Define los días y horas de trabajo de cada empleado.

```sql
CREATE TABLE horarios_empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (empleado_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

**Campos:**
- `id`: Identificador único
- `empleado_id`: FK a usuarios (rol Empleado)
- `dia_semana`: Día de la semana (Lunes a Domingo)
- `hora_inicio`: Hora de entrada (TIME)
- `hora_fin`: Hora de salida (TIME)
- `activo`: Si el horario está vigente

**Índices:**
- PRIMARY KEY en `id`
- INDEX compuesto en (`empleado_id`, `dia_semana`)

**Relaciones:**
- `empleado_id` → `usuarios.id` (ON DELETE CASCADE)

**Reglas de Negocio:**
- hora_fin debe ser mayor que hora_inicio
- No puede haber horarios solapados para el mismo empleado en el mismo día
- Se usa para validar disponibilidad al agendar citas
- Un empleado puede tener múltiples horarios por día (turno partido)

---

## 🔐 Seguridad del Modelo

### 1. Integridad Referencial

Todas las Foreign Keys usan `ON DELETE CASCADE`:
- Si se elimina un usuario, se eliminan sus citas
- Si se elimina un servicio, se eliminan sus citas asociadas
- Si se elimina un empleado, se eliminan sus horarios

### 2. Constraints de Datos

- **NOT NULL**: Campos críticos no pueden ser nulos
- **UNIQUE**: Email único en usuarios
- **ENUM**: Valores predefinidos para rol y estado
- **DEFAULT**: Valores por defecto para nuevos registros

### 3. Índices para Performance

- Índices en columnas de búsqueda frecuente
- Índices compuestos para consultas multi-campo
- Índices en Foreign Keys para joins eficientes

---

## 📊 Consultas Comunes

### 1. Ver citas de un empleado en un día específico

```sql
SELECT c.*, u.nombre as cliente_nombre, s.nombre as servicio_nombre
FROM citas c
JOIN usuarios u ON c.cliente_id = u.id
JOIN servicios s ON c.servicio_id = s.id
WHERE c.empleado_id = ? AND c.fecha_cita = ?
ORDER BY c.hora_cita ASC;
```

### 2. Verificar disponibilidad de empleado

```sql
SELECT * FROM horarios_empleados
WHERE empleado_id = ? 
AND dia_semana = DAYNAME(?)
AND ? BETWEEN hora_inicio AND hora_fin
AND activo = 1;
```

### 3. Obtener servicios más solicitados

```sql
SELECT s.nombre, COUNT(c.id) as total_citas
FROM servicios s
LEFT JOIN citas c ON s.id = c.servicio_id
WHERE c.estado IN ('Confirmada', 'Completada')
GROUP BY s.id
ORDER BY total_citas DESC;
```

### 4. Ingresos mensuales

```sql
SELECT DATE_FORMAT(fecha_cita, '%Y-%m') as mes,
       SUM(precio_total) as ingresos
FROM citas
WHERE estado IN ('Confirmada', 'Completada')
GROUP BY mes
ORDER BY mes DESC;
```

---

## 🔄 Migraciones y Versiones

**Versión Actual**: 1.0

### Historial de Cambios

#### v1.0 (Noviembre 2025)
- ✅ Creación inicial de todas las tablas
- ✅ Definición de relaciones
- ✅ Implementación de índices
- ✅ Datos de prueba incluidos

---

## 📝 Notas de Implementación

### Codificación
- Charset: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`
- Engine: `InnoDB` (para transacciones y Foreign Keys)

### Timestamps
- Todos los timestamps usan `CURRENT_TIMESTAMP`
- fecha_actualizacion se actualiza automáticamente con `ON UPDATE CURRENT_TIMESTAMP`

### Tipos de Datos
- Precios: `DECIMAL(10,2)` para precisión financiera
- Textos cortos: `VARCHAR` con límites específicos
- Textos largos: `TEXT` para descripciones y notas
- Fechas/Horas: `DATE`, `TIME`, `TIMESTAMP` según corresponda

---

## 🎯 Mejoras Futuras Planificadas

1. **Tabla de Promociones**
   - Descuentos y ofertas especiales
   - Fechas de vigencia

2. **Tabla de Pagos**
   - Registro de pagos por cita
   - Métodos de pago
   - Estados de facturación

3. **Tabla de Productos**
   - Productos vendidos en el salón
   - Inventario

4. **Tabla de Comentarios/Reviews**
   - Calificaciones de clientes
   - Feedback de servicios

5. **Tabla de Notificaciones**
   - Recordatorios de citas
   - Notificaciones del sistema

---

**Última actualización**: Noviembre 2025
**Mantenedor**: Sistema de Gestión de Salón de Belleza
