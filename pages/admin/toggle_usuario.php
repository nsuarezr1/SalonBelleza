<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: usuarios.php");
    exit();
}

$usuario_id = $_GET['id'] ?? null;

if (!$usuario_id) {
    header("Location: usuarios.php");
    exit();
}

// No permitir desactivar el propio usuario
if ($usuario_id == $_SESSION['usuario_id']) {
    header("Location: usuarios.php?error=cannot_deactivate_self");
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Obtener estado actual
    $query = "SELECT activo FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        header("Location: usuarios.php?error=not_found");
        exit();
    }

    // Cambiar estado
    $nuevo_estado = $usuario['activo'] ? 0 : 1;
    
    $query = "UPDATE usuarios SET activo = :activo WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':activo', $nuevo_estado);
    $stmt->bindParam(':id', $usuario_id);
    
    if ($stmt->execute()) {
        $mensaje = $nuevo_estado ? 'usuario_activado' : 'usuario_desactivado';
        header("Location: usuarios.php?success=$mensaje");
        exit();
    } else {
        header("Location: usuarios.php?error=database");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error al cambiar estado de usuario: " . $e->getMessage());
    header("Location: usuarios.php?error=database");
    exit();
}
?>
