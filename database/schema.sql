-- Base de datos para Sistema de Gestión de Salón de Belleza
-- Versión: 1.0
-- Fecha: 2025

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS salon_belleza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salon_belleza;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    rol ENUM('Cliente', 'Empleado', 'Administrador') NOT NULL DEFAULT 'Cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de servicios
CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    duracion INT NOT NULL COMMENT 'Duración en minutos',
    precio DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de citas
CREATE TABLE IF NOT EXISTS citas (
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
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    INDEX idx_fecha_cita (fecha_cita),
    INDEX idx_hora_cita (hora_cita),
    INDEX idx_cliente (cliente_id),
    INDEX idx_empleado (empleado_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de horarios de empleados
CREATE TABLE IF NOT EXISTS horarios_empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (empleado_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_empleado_dia (empleado_id, dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS DE PRUEBA
-- ============================================

-- Insertar usuarios de prueba
-- NOTA: Todas las contraseñas son: password123
-- Hash bcrypt generado para: password123
INSERT INTO usuarios (nombre, email, password, telefono, rol, activo) VALUES
('Administrador Sistema', 'admin@salon.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0000', 'Administrador', 1),
('María García', 'maria.garcia@salon.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0001', 'Empleado', 1),
('Carlos Rodríguez', 'carlos.rodriguez@salon.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0002', 'Empleado', 1),
('Ana Martínez', 'ana.martinez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0003', 'Cliente', 1),
('Luis Fernández', 'luis.fernandez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0004', 'Cliente', 1);

-- Insertar servicios
INSERT INTO servicios (nombre, descripcion, duracion, precio, activo) VALUES
('Corte de Cabello Dama', 'Corte de cabello para mujer con lavado y secado', 60, 250.00, 1),
('Corte de Cabello Caballero', 'Corte de cabello para hombre', 30, 150.00, 1),
('Tinte Completo', 'Aplicación de tinte en todo el cabello', 120, 500.00, 1),
('Mechas', 'Aplicación de mechas o reflejos', 90, 450.00, 1),
('Manicure', 'Cuidado y esmaltado de uñas de manos', 45, 180.00, 1),
('Pedicure', 'Cuidado y esmaltado de uñas de pies', 60, 220.00, 1),
('Tratamiento Capilar', 'Tratamiento de hidratación profunda', 45, 300.00, 1),
('Peinado para Evento', 'Peinado especial para eventos', 90, 400.00, 1),
('Depilación Facial', 'Depilación de cejas y labio superior', 30, 120.00, 1),
('Maquillaje Social', 'Maquillaje para eventos sociales', 60, 350.00, 1);

-- Insertar horarios para empleados (María García - ID 2)
INSERT INTO horarios_empleados (empleado_id, dia_semana, hora_inicio, hora_fin, activo) VALUES
(2, 'Lunes', '09:00:00', '18:00:00', 1),
(2, 'Martes', '09:00:00', '18:00:00', 1),
(2, 'Miércoles', '09:00:00', '18:00:00', 1),
(2, 'Jueves', '09:00:00', '18:00:00', 1),
(2, 'Viernes', '09:00:00', '18:00:00', 1),
(2, 'Sábado', '10:00:00', '14:00:00', 1);

-- Insertar horarios para empleados (Carlos Rodríguez - ID 3)
INSERT INTO horarios_empleados (empleado_id, dia_semana, hora_inicio, hora_fin, activo) VALUES
(3, 'Lunes', '10:00:00', '19:00:00', 1),
(3, 'Martes', '10:00:00', '19:00:00', 1),
(3, 'Miércoles', '10:00:00', '19:00:00', 1),
(3, 'Jueves', '10:00:00', '19:00:00', 1),
(3, 'Viernes', '10:00:00', '19:00:00', 1);

-- Insertar algunas citas de ejemplo
INSERT INTO citas (cliente_id, empleado_id, servicio_id, fecha_cita, hora_cita, estado, precio_total) VALUES
(4, 2, 1, '2025-11-15', '10:00:00', 'Confirmada', 250.00),
(5, 3, 2, '2025-11-15', '11:00:00', 'Pendiente', 150.00),
(4, 2, 5, '2025-11-16', '14:00:00', 'Pendiente', 180.00);

-- Verificación de datos insertados
SELECT 'Usuarios creados:' as Info;
SELECT id, nombre, email, rol FROM usuarios;

SELECT 'Servicios creados:' as Info;
SELECT id, nombre, precio, duracion FROM servicios;

SELECT 'CREDENCIALES DE ACCESO:' as Info;
SELECT 
    '========================' as '========================',
    'ADMINISTRADOR' as Rol,
    'Email: admin@salon.com' as Credenciales,
    'Password: password123' as Password,
    '========================' as '========================'
UNION ALL
SELECT 
    '========================',
    'EMPLEADO',
    'Email: maria.garcia@salon.com',
    'Password: password123',
    '========================'
UNION ALL
SELECT 
    '========================',
    'CLIENTE',
    'Email: ana.martinez@gmail.com',
    'Password: password123',
    '========================';
