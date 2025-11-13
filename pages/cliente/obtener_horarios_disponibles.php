<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$empleado_id = $_GET['empleado_id'] ?? null;
$fecha = $_GET['fecha'] ?? null;
$duracion = $_GET['duracion'] ?? 60;

if (!$empleado_id || !$fecha) {
    echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Obtener día de la semana en español
    $dias_map = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    
    $dia_ingles = date('l', strtotime($fecha));
    $dia_semana = $dias_map[$dia_ingles];

    // Obtener horario del empleado para ese día
    $query = "SELECT hora_inicio, hora_fin 
              FROM horarios_empleados 
              WHERE empleado_id = :empleado_id 
              AND dia_semana = :dia_semana 
              AND activo = 1
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->bindParam(':dia_semana', $dia_semana);
    $stmt->execute();
    
    $horario = $stmt->fetch();
    
    if (!$horario) {
        echo json_encode(['success' => true, 'horarios' => []]);
        exit();
    }

    // Obtener citas ya agendadas para ese día y empleado
    $query = "SELECT hora_cita, 
              (SELECT duracion FROM servicios WHERE id = citas.servicio_id) as duracion
              FROM citas 
              WHERE empleado_id = :empleado_id 
              AND fecha_cita = :fecha 
              AND estado IN ('Pendiente', 'Confirmada')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->execute();
    
    $citas_ocupadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar horarios disponibles cada 30 minutos
    $horarios_disponibles = [];
    $hora_actual = strtotime($horario['hora_inicio']);
    $hora_fin = strtotime($horario['hora_fin']);
    
    while ($hora_actual < $hora_fin) {
        $hora_str = date('H:i:00', $hora_actual);
        
        // Verificar si el horario está ocupado
        $esta_ocupado = false;
        foreach ($citas_ocupadas as $cita) {
            $hora_cita = strtotime($cita['hora_cita']);
            $duracion_cita = $cita['duracion'] ?? 60;
            $hora_fin_cita = $hora_cita + ($duracion_cita * 60);
            
            $hora_fin_nueva = $hora_actual + ($duracion * 60);
            
            // Verificar si hay solapamiento
            if (($hora_actual >= $hora_cita && $hora_actual < $hora_fin_cita) ||
                ($hora_fin_nueva > $hora_cita && $hora_fin_nueva <= $hora_fin_cita) ||
                ($hora_actual <= $hora_cita && $hora_fin_nueva >= $hora_fin_cita)) {
                $esta_ocupado = true;
                break;
            }
        }
        
        // Verificar que haya tiempo suficiente antes del cierre
        if (($hora_actual + ($duracion * 60)) <= $hora_fin && !$esta_ocupado) {
            // Si es hoy, solo mostrar horarios futuros
            if ($fecha == date('Y-m-d')) {
                $hora_actual_real = strtotime(date('H:i:00'));
                if ($hora_actual > $hora_actual_real) {
                    $horarios_disponibles[] = date('H:i', $hora_actual);
                }
            } else {
                $horarios_disponibles[] = date('H:i', $hora_actual);
            }
        }
        
        $hora_actual += 1800; // Incrementar 30 minutos
    }

    echo json_encode([
        'success' => true,
        'horarios' => $horarios_disponibles
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener horarios: ' . $e->getMessage()
    ]);
}
?>
