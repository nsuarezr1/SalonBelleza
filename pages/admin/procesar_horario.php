<?php
include('../../config/database.php');
include('../../includes/verificar_sesion.php');

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = $_POST['id'] ?? null;

    switch ($accion) {
        case 'crear':
            $stmt = $conn->prepare("
                INSERT INTO horarios_empleados (empleado_id, dia_semana, hora_inicio, hora_fin, activo)
                VALUES (:empleado_id, :dia_semana, :hora_inicio, :hora_fin, :activo)
            ");
            $stmt->execute([
                ':empleado_id' => $_POST['empleado_id'],
                ':dia_semana' => $_POST['dia_semana'],
                ':hora_inicio' => $_POST['hora_inicio'],
                ':hora_fin' => $_POST['hora_fin'],
                ':activo' => isset($_POST['activo']) ? 1 : 0
            ]);
            break;

        case 'editar':
            $stmt = $conn->prepare("
                UPDATE horarios_empleados
                SET dia_semana = :dia_semana, hora_inicio = :hora_inicio, hora_fin = :hora_fin, activo = :activo
                WHERE id = :id
            ");
            $stmt->execute([
                ':dia_semana' => $_POST['dia_semana'],
                ':hora_inicio' => $_POST['hora_inicio'],
                ':hora_fin' => $_POST['hora_fin'],
                ':activo' => isset($_POST['activo']) ? 1 : 0,
                ':id' => $id
            ]);
            break;

        case 'desactivar':
            $stmt = $conn->prepare("UPDATE horarios_empleados SET activo = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            break;
    }

    header("Location: horarios.php?msg=ok");
    exit;
} else {
    header("Location: horarios.php");
    exit;
}
?>
