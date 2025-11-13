<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador' || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: horarios.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$accion = $_POST['accion'] ?? '';

try {
    if ($accion == 'crear') {
        // Crear nuevo horario
        $empleado_id = $_POST['empleado_id'] ?? null;
        $dia_semana = $_POST['dia_semana'] ?? null;
        $hora_inicio = $_POST['hora_inicio'] ?? null;
        $hora_fin = $_POST['hora_fin'] ?? null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        // Validar campos requeridos
        if (!$empleado_id || !$dia_semana || !$hora_inicio || !$hora_fin) {
            header("Location: agregar_horario.php?error=required");
            exit();
        }

        // Validar que hora_fin sea mayor que hora_inicio
        if ($hora_inicio >= $hora_fin) {
            header("Location: agregar_horario.php?error=invalid_time");
            exit();
        }

        // Verificar que no exista un horario solapado
        $query = "SELECT COUNT(*) as total FROM horarios_empleados 
                  WHERE empleado_id = :empleado_id 
                  AND dia_semana = :dia_semana 
                  AND activo = 1
                  AND (
                      (:hora_inicio BETWEEN hora_inicio AND hora_fin) OR
                      (:hora_fin BETWEEN hora_inicio AND hora_fin) OR
                      (hora_inicio BETWEEN :hora_inicio AND :hora_fin)
                  )";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':empleado_id' => $empleado_id,
            ':dia_semana' => $dia_semana,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin
        ]);
        
        $resultado = $stmt->fetch();
        
        if ($resultado['total'] > 0) {
            header("Location: agregar_horario.php?error=horario_exists");
            exit();
        }

        // Insertar horario
        $query = "INSERT INTO horarios_empleados (empleado_id, dia_semana, hora_inicio, hora_fin, activo) 
                  VALUES (:empleado_id, :dia_semana, :hora_inicio, :hora_fin, :activo)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':empleado_id' => $empleado_id,
            ':dia_semana' => $dia_semana,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin,
            ':activo' => $activo
        ]);

        header("Location: horarios.php?success=horario_creado");
        exit();

    } elseif ($accion == 'editar') {
        // Editar horario existente
        $horario_id = $_POST['horario_id'] ?? null;
        $empleado_id = $_POST['empleado_id'] ?? null;
        $dia_semana = $_POST['dia_semana'] ?? null;
        $hora_inicio = $_POST['hora_inicio'] ?? null;
        $hora_fin = $_POST['hora_fin'] ?? null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        // Validar campos requeridos
        if (!$horario_id || !$empleado_id || !$dia_semana || !$hora_inicio || !$hora_fin) {
            header("Location: editar_horario.php?id=$horario_id&error=required");
            exit();
        }

        // Validar que hora_fin sea mayor que hora_inicio
        if ($hora_inicio >= $hora_fin) {
            header("Location: editar_horario.php?id=$horario_id&error=invalid_time");
            exit();
        }

        // Verificar que no exista un horario solapado (excepto este mismo)
        $query = "SELECT COUNT(*) as total FROM horarios_empleados 
                  WHERE empleado_id = :empleado_id 
                  AND dia_semana = :dia_semana 
                  AND activo = 1
                  AND id != :horario_id
                  AND (
                      (:hora_inicio BETWEEN hora_inicio AND hora_fin) OR
                      (:hora_fin BETWEEN hora_inicio AND hora_fin) OR
                      (hora_inicio BETWEEN :hora_inicio AND :hora_fin)
                  )";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':empleado_id' => $empleado_id,
            ':dia_semana' => $dia_semana,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin,
            ':horario_id' => $horario_id
        ]);
        
        $resultado = $stmt->fetch();
        
        if ($resultado['total'] > 0) {
            header("Location: editar_horario.php?id=$horario_id&error=horario_exists");
            exit();
        }

        // Actualizar horario
        $query = "UPDATE horarios_empleados 
                  SET empleado_id = :empleado_id,
                      dia_semana = :dia_semana,
                      hora_inicio = :hora_inicio,
                      hora_fin = :hora_fin,
                      activo = :activo
                  WHERE id = :horario_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':empleado_id' => $empleado_id,
            ':dia_semana' => $dia_semana,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin,
            ':activo' => $activo,
            ':horario_id' => $horario_id
        ]);

        header("Location: horarios.php?success=horario_actualizado");
        exit();

    } else {
        header("Location: horarios.php");
        exit();
    }

} catch (Exception $e) {
    error_log("Error en procesar_horario.php: " . $e->getMessage());
    header("Location: horarios.php?error=database");
    exit();
}
?>