-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql207.infinityfree.com
-- Tiempo de generación: 12-11-2025 a las 22:12:35
-- Versión del servidor: 11.4.7-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_40376657_aldanys`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_cita` time NOT NULL,
  `estado` enum('Pendiente','Confirmada','Completada','Cancelada') DEFAULT 'Pendiente',
  `notas` text DEFAULT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `cliente_id`, `empleado_id`, `servicio_id`, `fecha_cita`, `hora_cita`, `estado`, `notas`, `precio_total`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 4, 2, 1, '2025-11-15', '10:00:00', 'Confirmada', NULL, '250.00', '2025-11-11 01:05:41', '2025-11-11 01:05:41'),
(2, 5, 3, 2, '2025-11-15', '11:00:00', 'Pendiente', NULL, '150.00', '2025-11-11 01:05:41', '2025-11-11 01:05:41'),
(3, 4, 2, 5, '2025-11-16', '14:00:00', 'Pendiente', NULL, '180.00', '2025-11-11 01:05:41', '2025-11-11 01:05:41'),
(4, 4, 2, 2, '2025-11-12', '11:30:00', 'Pendiente', '', '150.00', '2025-11-11 02:38:32', '2025-11-11 02:38:32'),
(5, 4, 2, 5, '2025-11-12', '09:30:00', 'Pendiente', '', '180.00', '2025-11-12 02:58:52', '2025-11-12 02:58:52'),
(6, 4, 2, 4, '2025-11-13', '13:00:00', 'Pendiente', 'ff', '450.00', '2025-11-12 14:50:25', '2025-11-12 14:50:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_empleados`
--

CREATE TABLE `horarios_empleados` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios_empleados`
--

INSERT INTO `horarios_empleados` (`id`, `empleado_id`, `dia_semana`, `hora_inicio`, `hora_fin`, `activo`) VALUES
(1, 2, 'Lunes', '09:00:00', '18:00:00', 1),
(2, 2, 'Martes', '09:00:00', '18:00:00', 1),
(3, 2, 'Miércoles', '09:00:00', '18:00:00', 1),
(4, 2, 'Jueves', '09:00:00', '18:00:00', 1),
(5, 2, 'Viernes', '09:00:00', '18:00:00', 1),
(6, 2, 'Sábado', '10:00:00', '14:00:00', 0),
(7, 3, 'Lunes', '10:00:00', '19:00:00', 0),
(8, 3, 'Martes', '10:00:00', '19:00:00', 0),
(9, 3, 'Miércoles', '10:00:00', '19:00:00', 0),
(10, 3, 'Jueves', '10:00:00', '19:00:00', 0),
(11, 3, 'Viernes', '10:00:00', '19:00:00', 0),
(12, 2, 'Sábado', '09:00:00', '17:30:00', 1),
(13, 3, 'Lunes', '09:00:00', '19:00:00', 0),
(14, 2, 'Martes', '09:00:00', '19:00:00', 0),
(15, 2, 'Martes', '09:00:00', '19:00:00', 1),
(16, 3, 'Lunes', '09:00:00', '19:00:00', 1),
(17, 3, 'Martes', '09:00:00', '19:00:00', 1),
(18, 3, 'Miércoles', '09:00:00', '19:00:00', 1),
(19, 3, 'Jueves', '09:00:00', '19:00:00', 1),
(20, 3, 'Viernes', '09:00:00', '19:00:00', 1),
(21, 3, 'Sábado', '09:00:00', '17:30:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion` int(11) NOT NULL COMMENT 'Duración en minutos',
  `precio` decimal(10,2) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `duracion`, `precio`, `activo`, `fecha_creacion`) VALUES
(1, 'Corte de Cabello Dama', 'Corte de cabello para mujer', 30, '20000.00', 1, '2025-11-11 01:05:41'),
(2, 'Semipermanente', 'Esmaltado semi con cualquier diseño', 45, '45000.00', 1, '2025-11-11 01:05:41'),
(3, 'Tinte Completo', 'Aplicación de tinte en todo el cabello', 120, '100000.00', 1, '2025-11-11 01:05:41'),
(4, 'Base Rubber', 'Aplicación de esmaltado rubber mas semi con cualquier diseño', 90, '75000.00', 1, '2025-11-11 01:05:41'),
(5, 'Manicure Tradicional', 'Limpieza y esmaltado de uñas ', 45, '22000.00', 1, '2025-11-11 01:05:41'),
(6, 'Pedicure', 'Limpieza y esmaltado de uñas de pies', 60, '28000.00', 1, '2025-11-11 01:05:41'),
(7, 'Acrilicas ', 'Alargamiento de uña hasta el #2 con semi más diseño', 180, '120000.00', 1, '2025-11-11 01:05:41'),
(8, 'Jelly Tips', 'Alargamiento de uña en gel más esmaltado semi y diseño', 135, '90000.00', 1, '2025-11-11 01:05:41'),
(9, 'Depilación Facial', 'Depilación de cejas y labio superior', 15, '20000.00', 1, '2025-11-11 01:05:41'),
(10, 'Cepillado', 'Lavado de cabello, cepillado y alisado', 60, '35000.00', 1, '2025-11-11 01:05:41'),
(11, 'Pestañas pelo a pelo', 'Extensión de pestañas pelo a pelo', 120, '85000.00', 1, '2025-11-12 21:02:50'),
(12, 'Pestañas Punto a punto', 'Extensión de pestañas Punto a punto', 30, '30000.00', 1, '2025-11-12 21:03:33'),
(13, 'Keratina', 'El precio puede variar dependiendo la cantidad de cabello y de largo', 300, '250000.00', 1, '2025-11-12 21:05:11'),
(14, 'Pies semi', 'Limpieza con esmaltado semi', 60, '50000.00', 1, '2025-11-12 21:06:01'),
(15, 'Henna Cejas', 'Pigmentación temporal de cejas', 45, '30000.00', 1, '2025-11-12 21:06:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('Cliente','Empleado','Administrador') NOT NULL DEFAULT 'Cliente',
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `rol`, `fecha_registro`, `activo`) VALUES
(1, 'Paulina', 'admin@gmail.com', '$2y$10$xNYOkWsxngx2BJrqFEKft.W9HzlSY.YLJEJluFmCRnzUm0yXB1Ksi', '3115620806', 'Administrador', '2025-11-11 01:05:41', 1),
(2, 'Jasbleidy', 'jas@gmail.com', '$2y$10$wIN77y2WY3.r.Y/1PlXUDeITdrAUwPJ/JERS7BgoXX/eLfU4vt36O', '3236657384', 'Empleado', '2025-11-11 01:05:41', 1),
(3, 'Jessica', 'Jeka@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3208212153', 'Empleado', '2025-11-11 01:05:41', 1),
(4, 'lizeth', 'liz@gmail.com', '$2y$10$ZmHWmUBMaCTG4OYs6AnWuudkd2Q4reI0eNQx/yn/OD2nIFDVGV6sK', '3208047439', 'Cliente', '2025-11-11 01:05:41', 1),
(5, 'Erika', 'Erika12@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3202707603', 'Cliente', '2025-11-11 01:05:41', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servicio_id` (`servicio_id`),
  ADD KEY `idx_fecha_cita` (`fecha_cita`),
  ADD KEY `idx_hora_cita` (`hora_cita`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empleado_dia` (`empleado_id`,`dia_semana`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  ADD CONSTRAINT `horarios_empleados_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
