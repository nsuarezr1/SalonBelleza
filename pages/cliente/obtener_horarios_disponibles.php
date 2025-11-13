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
$duracion = intval($_GET['duracion'] ?? 60);

if (!$empleado_id || !$fecha) {
    echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
    exit();
}

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
    exit();
}

// Validar que la fecha no sea pasada
if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'No se pueden agendar citas en fechas pasadas']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar que el empleado existe y está activo
    $query = "SELECT id, nombre FROM usuarios WHERE id = :empleado_id AND rol = 'Empleado' AND activo = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->execute();
    
    $empleado = $stmt->fetch();
    
    if (!$empleado) {
        echo json_encode([
            'success' => false, 
            'message' => 'Empleado no disponible'
        ]);
        exit();
    }

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

    // Obtener TODOS los horarios del empleado para ese día (puede tener varios turnos)
    $query = "SELECT hora_inicio, hora_fin 
              FROM horarios_empleados 
              WHERE empleado_id = :empleado_id 
              AND dia_semana = :dia_semana 
              AND activo = 1
              ORDER BY hora_inicio ASC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->bindParam(':dia_semana', $dia_semana);
    $stmt->execute();
    
    $horarios_trabajo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si el empleado no trabaja ese día
    if (count($horarios_trabajo) === 0) {
        echo json_encode([
            'success' => true, 
            'horarios' => [],
            'message' => "El empleado {$empleado['nombre']} no trabaja los {$dia_semana}"
        ]);
        exit();
    }

    // Obtener todas las citas ya agendadas para ese día y empleado
    $query = "SELECT 
                c.hora_cita, 
                s.duracion
              FROM citas c
              JOIN servicios s ON c.servicio_id = s.id
              WHERE c.empleado_id = :empleado_id 
              AND c.fecha_cita = :fecha 
              AND c.estado IN ('Pendiente', 'Confirmada')
              ORDER BY c.hora_cita ASC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->execute();
    
    $citas_ocupadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crear array de bloques ocupados
    $bloques_ocupados = [];
    foreach ($citas_ocupadas as $cita) {
        $inicio_cita = strtotime($cita['hora_cita']);
        $fin_cita = $inicio_cita + ($cita['duracion'] * 60);
        
        $bloques_ocupados[] = [
            'inicio' => $inicio_cita,
            'fin' => $fin_cita
        ];
    }

    // Generar todos los horarios disponibles
    $horarios_disponibles = [];
    $intervalo_minutos = 30; // Generar horarios cada 30 minutos
    
    // Recorrer cada turno de trabajo
    foreach ($horarios_trabajo as $turno) {
        $hora_inicio_turno = strtotime($turno['hora_inicio']);
        $hora_fin_turno = strtotime($turno['hora_fin']);
        
        // Generar slots dentro de este turno
        $hora_actual = $hora_inicio_turno;
        
        while ($hora_actual < $hora_fin_turno) {
            $hora_fin_servicio = $hora_actual + ($duracion * 60);
            
            // Verificar que el servicio completo quepa antes del fin del turno
            if ($hora_fin_servicio > $hora_fin_turno) {
                break; // No hay tiempo suficiente en este turno
            }
            
            // Verificar si este horario está ocupado
            $esta_ocupado = false;
            
            foreach ($bloques_ocupados as $bloque) {
                // Verificar si hay solapamiento
                if (
                    // El inicio del nuevo servicio cae dentro de una cita ocupada
                    ($hora_actual >= $bloque['inicio'] && $hora_actual < $bloque['fin']) ||
                    // El fin del nuevo servicio cae dentro de una cita ocupada
                    ($hora_fin_servicio > $bloque['inicio'] && $hora_fin_servicio <= $bloque['fin']) ||
                    // El nuevo servicio envuelve completamente una cita ocupada
                    ($hora_actual <= $bloque['inicio'] && $hora_fin_servicio >= $bloque['fin'])
                ) {
                    $esta_ocupado = true;
                    break;
                }
            }
            
            // Si no está ocupado, agregarlo a disponibles
            if (!$esta_ocupado) {
                // Si es hoy, solo mostrar horarios futuros (con margen de 1 hora)
                if ($fecha == date('Y-m-d')) {
                    $hora_minima = strtotime('+1 hour'); // Dar 1 hora de margen
                    if ($hora_actual >= $hora_minima) {
                        $horarios_disponibles[] = date('H:i', $hora_actual);
                    }
                } else {
                    // Para fechas futuras, mostrar todos los horarios del turno
                    $horarios_disponibles[] = date('H:i', $hora_actual);
                }
            }
            
            // Avanzar al siguiente intervalo
            $hora_actual += ($intervalo_minutos * 60);
        }
    }

    // Eliminar duplicados y ordenar
    $horarios_disponibles = array_unique($horarios_disponibles);
    sort($horarios_disponibles);

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'horarios' => array_values($horarios_disponibles),
        'empleado' => $empleado['nombre'],
        'dia' => $dia_semana,
        'turnos' => count($horarios_trabajo),
        'debug' => [
            'fecha' => $fecha,
            'es_hoy' => $fecha == date('Y-m-d'),
            'citas_ocupadas' => count($citas_ocupadas),
            'duracion_servicio' => $duracion
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener horarios disponibles',
        'error' => $e->getMessage()
    ]);
}
?>
