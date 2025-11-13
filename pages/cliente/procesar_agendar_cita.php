<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea cliente y que sea POST
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Cliente' || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: dashboard.php");
    exit();
}

// Obtener datos del formulario
$cliente_id = $_SESSION['usuario_id'];
$servicio_id = $_POST['servicio_id'] ?? null;
$empleado_id = $_POST['empleado_id'] ?? null;
$fecha_cita = $_POST['fecha_cita'] ?? null;
$hora_cita = $_POST['hora_cita'] ?? null;
$notas = trim($_POST['notas'] ?? '');
$precio_total = $_POST['precio_total'] ?? null;

// Validar campos requeridos
if (!$servicio_id || !$empleado_id || !$fecha_cita || !$hora_cita || !$precio_total) {
    header("Location: agendar_cita.php?servicio_id=$servicio_id&error=required");
    exit();
}

// Validar que la fecha no sea pasada
if (strtotime($fecha_cita) < strtotime(date('Y-m-d'))) {
    header("Location: agendar_cita.php?servicio_id=$servicio_id&error=invalid_date");
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar que el horario siga disponible
    $query = "SELECT COUNT(*) as total FROM citas 
              WHERE empleado_id = :empleado_id 
              AND fecha_cita = :fecha_cita 
              AND hora_cita = :hora_cita 
              AND estado IN ('Pendiente', 'Confirmada')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->bindParam(':fecha_cita', $fecha_cita);
    $stmt->bindParam(':hora_cita', $hora_cita);
    $stmt->execute();
    
    $resultado = $stmt->fetch();
    
    if ($resultado['total'] > 0) {
        header("Location: agendar_cita.php?servicio_id=$servicio_id&error=horario_ocupado");
        exit();
    }

    // Insertar la cita
    $query = "INSERT INTO citas (cliente_id, empleado_id, servicio_id, fecha_cita, hora_cita, estado, notas, precio_total) 
              VALUES (:cliente_id, :empleado_id, :servicio_id, :fecha_cita, :hora_cita, 'Pendiente', :notas, :precio_total)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':cliente_id', $cliente_id);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->bindParam(':servicio_id', $servicio_id);
    $stmt->bindParam(':fecha_cita', $fecha_cita);
    $stmt->bindParam(':hora_cita', $hora_cita);
    $stmt->bindParam(':notas', $notas);
    $stmt->bindParam(':precio_total', $precio_total);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?success=cita_agendada");
        exit();
    } else {
        header("Location: agendar_cita.php?servicio_id=$servicio_id&error=database");
        exit();
    }

} catch (Exception $e) {
    error_log("Error al agendar cita: " . $e->getMessage());
    header("Location: agendar_cita.php?servicio_id=$servicio_id&error=database");
    exit();
}
?>
