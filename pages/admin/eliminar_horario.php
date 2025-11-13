<?php
session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') exit;

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("DELETE FROM horarios_empleados WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);
header("Location: horarios.php?success=1");
?>
