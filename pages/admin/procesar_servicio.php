<?php
session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') exit;

$accion = $_POST['accion'] ?? '';
$database = new Database();
$db = $database->getConnection();

if ($accion == 'crear') {
    $query = "INSERT INTO servicios (nombre, descripcion, duracion, precio, activo) VALUES (:nombre, :descripcion, :duracion, :precio, 1)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':nombre' => $_POST['nombre'],
        ':descripcion' => $_POST['descripcion'],
        ':duracion' => $_POST['duracion'],
        ':precio' => $_POST['precio']
    ]);
    header("Location: servicios.php?success=servicio_creado");
} elseif ($accion == 'editar') {
    $query = "UPDATE servicios SET nombre=:nombre, descripcion=:descripcion, duracion=:duracion, precio=:precio WHERE id=:id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':nombre' => $_POST['nombre'],
        ':descripcion' => $_POST['descripcion'],
        ':duracion' => $_POST['duracion'],
        ':precio' => $_POST['precio'],
        ':id' => $_POST['servicio_id']
    ]);
    header("Location: servicios.php?success=servicio_actualizado");
}
?>
