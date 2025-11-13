<?php
session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') exit;

$database = new Database();
$db = $database->getConnection();
$id = $_GET['id'];

$query = "UPDATE servicios SET activo = NOT activo WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $id]);
header("Location: servicios.php?success=servicio_actualizado");
?>
