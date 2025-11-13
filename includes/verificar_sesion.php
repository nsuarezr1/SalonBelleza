<?php
// Verificar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
    header("Location: ../../index.php");
    exit();
}

// Función para verificar el rol del usuario
function verificarRol($roles_permitidos) {
    if (!in_array($_SESSION['rol'], $roles_permitidos)) {
        // Si no tiene el rol permitido, redirigir a su dashboard correspondiente
        switch($_SESSION['rol']) {
            case 'Administrador':
                header("Location: ../admin/dashboard.php");
                break;
            case 'Empleado':
                header("Location: ../empleado/dashboard.php");
                break;
            case 'Cliente':
                header("Location: ../cliente/dashboard.php");
                break;
            default:
                header("Location: ../../index.php");
                break;
        }
        exit();
    }
}

// Función para obtener el nombre del usuario actual
function obtenerNombreUsuario() {
    return $_SESSION['nombre'] ?? 'Usuario';
}

// Función para obtener el rol del usuario actual
function obtenerRolUsuario() {
    return $_SESSION['rol'] ?? '';
}

// Verificar timeout de sesión (30 minutos de inactividad)
$timeout_duration = 1800; // 30 minutos en segundos

if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_inactivo = time() - $_SESSION['ultimo_acceso'];
    
    if ($tiempo_inactivo > $timeout_duration) {
        // Sesión expirada
        session_unset();
        session_destroy();
        header("Location: ../../index.php?error=session_expired");
        exit();
    }
}

// Actualizar tiempo de último acceso
$_SESSION['ultimo_acceso'] = time();
?>
