<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: citas.php");
    exit();
}

$cita_id = $_GET['id'] ?? null;

if (!$cita_id) {
    header("Location: citas.php");
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Eliminar la cita
    $query = "DELETE FROM citas WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $cita_id);
    
    if ($stmt->execute()) {
        header("Location: citas.php?success=cita_eliminada");
        exit();
    } else {
        header("Location: citas.php?error=database");
        exit();
    }

} catch (Exception $e) {
    error_log("Error al eliminar cita: " . $e->getMessage());
    header("Location: citas.php?error=database");
    exit();
}
?>
