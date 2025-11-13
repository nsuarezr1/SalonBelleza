<?php
include('../../config/database.php');
include('../../includes/verificar_sesion.php');

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $estado = $_POST['estado'] ?? null;

    if ($id && $estado) {
        $query = $conn->prepare("UPDATE citas SET estado = :estado WHERE id = :id");
        $query->execute([
            ':estado' => $estado,
            ':id' => $id
        ]);

        header("Location: citas.php?msg=ok");
        exit;
    } else {
        header("Location: citas.php?error=parametros");
        exit;
    }
} else {
    header("Location: citas.php");
    exit;
}
?>
