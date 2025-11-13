<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador' || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: citas.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$accion = $_POST['accion'] ?? '';

try {
    if ($accion == 'crear') {
        // Crear nueva cita
        $cliente_id = $_POST['cliente_id'] ?? null;
        $empleado_id = $_POST['empleado_id'] ?? null;
        $servicio_id = $_POST['servicio_id'] ?? null;
        $fecha_cita = $_POST['fecha_cita'] ?? null;
        $hora_cita = $_POST['hora_cita'] ?? null;
        $estado = $_POST['estado'] ?? 'Pendiente';
        $notas = trim($_POST['notas'] ?? '');
        $precio_total = $_POST['precio_total'] ?? null;

        // Validar campos requeridos
        if (!$cliente_id || !$empleado_id || !$servicio_id || !$fecha_cita || !$hora_cita || !$precio_total) {
            header("Location: agregar_cita.php?error=required");
            exit();
        }

        // Verificar disponibilidad del horario
        $query = "SELECT COUNT(*) as total FROM citas 
                  WHERE empleado_id = :empleado_id 
                  AND fecha_cita = :fecha_cita 
                  AND hora_cita = :hora_cita 
                  AND estado IN ('Pendiente', 'Confirmada')";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':empleado_id' => $empleado_id,
            ':fecha_cita' => $fecha_cita,
            ':hora_cita' => $hora_cita
        ]);
        
        $resultado = $stmt->fetch();
        
        if ($resultado['total'] > 0) {
            header("Location: agregar_cita.php?error=horario_ocupado");
            exit();
        }

        // Insertar cita
        $query = "INSERT INTO citas (cliente_id, empleado_id, servicio_id, fecha_cita, hora_cita, estado, notas, precio_total) 
                  VALUES (:cliente_id, :empleado_id, :servicio_id, :fecha_cita, :hora_cita, :estado, :notas, :precio_total)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':cliente_id' => $cliente_id,
            ':empleado_id' => $empleado_id,
            ':servicio_id' => $servicio_id,
            ':fecha_cita' => $fecha_cita,
            ':hora_cita' => $hora_cita,
            ':estado' => $estado,
            ':notas' => $notas,
            ':precio_total' => $precio_total
        ]);

        header("Location: citas.php?success=cita_creada");
        exit();

    } elseif ($accion == 'editar') {
        // Editar cita existente
        $cita_id = $_POST['cita_id'] ?? null;
        $cliente_id = $_POST['cliente_id'] ?? null;
        $empleado_id = $_POST['empleado_id'] ?? null;
        $servicio_id = $_POST['servicio_id'] ?? null;
        $fecha_cita = $_POST['fecha_cita'] ?? null;
        $hora_cita = $_POST['hora_cita'] ?? null;
        $estado = $_POST['estado'] ?? 'Pendiente';
        $notas = trim($_POST['notas'] ?? '');
        $precio_total = $_POST['precio_total'] ?? null;

        // Validar campos requeridos
        if (!$cita_id || !$cliente_id || !$empleado_id || !$servicio_id || !$fecha_cita || !$hora_cita || !$precio_total) {
            header("Location: editar_cita.php?id=$cita_id&error=required");
            exit();
        }

        // Verificar disponibilidad del horario (excepto para esta cita)
        $query = "SELECT COUNT(*) as total FROM citas 
                  WHERE empleado_id = :empleado_id 
                  AND fecha_cita = :fecha_cita 
                  AND hora_cita = :hora_cita 
                  AND estado IN ('Pendiente', 'Confirmada')
                  AND id != :cita_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':empleado_id' => $empleado_id,
            ':fecha_cita' => $fecha_cita,
            ':hora_cita' => $hora_cita,
            ':cita_id' => $cita_id
        ]);
        
        $resultado = $stmt->fetch();
        
        if ($resultado['total'] > 0) {
            header("Location: editar_cita.php?id=$cita_id&error=horario_ocupado");
            exit();
        }

        // Actualizar cita
        $query = "UPDATE citas 
                  SET cliente_id = :cliente_id,
                      empleado_id = :empleado_id,
                      servicio_id = :servicio_id,
                      fecha_cita = :fecha_cita,
                      hora_cita = :hora_cita,
                      estado = :estado,
                      notas = :notas,
                      precio_total = :precio_total
                  WHERE id = :cita_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':cliente_id' => $cliente_id,
            ':empleado_id' => $empleado_id,
            ':servicio_id' => $servicio_id,
            ':fecha_cita' => $fecha_cita,
            ':hora_cita' => $hora_cita,
            ':estado' => $estado,
            ':notas' => $notas,
            ':precio_total' => $precio_total,
            ':cita_id' => $cita_id
        ]);

        header("Location: citas.php?success=cita_actualizada");
        exit();

    } else {
        header("Location: citas.php");
        exit();
    }

} catch (Exception $e) {
    error_log("Error en procesar_cita.php: " . $e->getMessage());
    header("Location: citas.php?error=database");
    exit();
}
?>