<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: horarios.php");
    exit();
}

$horario_id = $_GET['id'] ?? null;
$accion = $_GET['accion'] ?? null;

if (!$horario_id || !$accion) {
    header("Location: horarios.php");
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Cambiar estado del horario (activar o desactivar)
    if ($accion == 'desactivar') {
        $query = "UPDATE horarios_empleados SET activo = 0 WHERE id = :id";
        $mensaje = 'horario_desactivado';
    } elseif ($accion == 'activar') {
        $query = "UPDATE horarios_empleados SET activo = 1 WHERE id = :id";
        $mensaje = 'horario_activado';
    } else {
        header("Location: horarios.php?error=invalid_action");
        exit();
    }

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $horario_id);
    
    if ($stmt->execute()) {
        header("Location: horarios.php?success=$mensaje");
        exit();
    } else {
        header("Location: horarios.php?error=database");
        exit();
    }

} catch (Exception $e) {
    error_log("Error al cambiar estado del horario: " . $e->getMessage());
    header("Location: horarios.php?error=database");
    exit();
}
?>