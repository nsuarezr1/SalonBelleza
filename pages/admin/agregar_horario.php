<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

verificarRol(['Administrador']);

$database = new Database();
$db = $database->getConnection();

// Obtener empleado seleccionado si viene por parámetro
$empleado_seleccionado = $_GET['empleado_id'] ?? null;

// Obtener todos los empleados activos
$empleados = $db->query("SELECT id, nombre, email FROM usuarios WHERE rol='Empleado' AND activo=1 ORDER BY nombre")->fetchAll();

$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Horario - Salón de Belleza</title>
    <link rel="stylesheet" href="../../assets/admin_styles.css">
    <style>
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>➕ Agregar Horario de Trabajo</h1>
            <a href="horarios.php" class="btn-back">← Volver</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                switch($_GET['error']) {
                    case 'required':
                        echo "❌ Por favor completa todos los campos obligatorios";
                        break;
                    case 'invalid_time':
                        echo "❌ La hora de fin debe ser mayor que la hora de inicio";
                        break;
                    case 'horario_exists':
                        echo "❌ Ya existe un horario para este empleado en este día y hora";
                        break;
                    default:
                        echo "❌ Error al crear el horario";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="procesar_horario.php" method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="accion" value="crear">

                <div class="form-group">
                    <label for="empleado_id">Empleado *</label>
                    <select id="empleado_id" name="empleado_id" required>
                        <option value="">-- Selecciona un empleado --</option>
                        <?php foreach ($empleados as $empleado): ?>
                            <option value="<?php echo $empleado['id']; ?>" <?php echo $empleado_seleccionado == $empleado['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($empleado['nombre']) . ' - ' . htmlspecialchars($empleado['email']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help-text">Selecciona el empleado al que le asignarás este horario</div>
                </div>

                <div class="form-group">
                    <label for="dia_semana">Día de la Semana *</label>
                    <select id="dia_semana" name="dia_semana" required>
                        <option value="">-- Selecciona un día --</option>
                        <?php foreach ($dias_semana as $dia): ?>
                            <option value="<?php echo $dia; ?>"><?php echo $dia; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="hora_inicio">Hora de Inicio *</label>
                        <input 
                            type="time" 
                            id="hora_inicio" 
                            name="hora_inicio" 
                            required
                            step="900"
                        >
                        <div class="help-text">Formato 24 horas (ej: 09:00)</div>
                    </div>

                    <div class="form-group">
                        <label for="hora_fin">Hora de Fin *</label>
                        <input 
                            type="time" 
                            id="hora_fin" 
                            name="hora_fin" 
                            required
                            step="900"
                        >
                        <div class="help-text">Formato 24 horas (ej: 18:00)</div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input 
                            type="checkbox" 
                            id="activo" 
                            name="activo" 
                            value="1"
                            checked
                        >
                        <label for="activo" style="margin: 0;">Horario activo</label>
                    </div>
                    <div class="help-text">Los horarios inactivos no se usarán para agendar citas</div>
                </div>

                <button type="submit" class="btn-primary">✅ Crear Horario</button>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFin = document.getElementById('hora_fin').value;

            if (!horaInicio || !horaFin) {
                alert('Por favor completa las horas de inicio y fin');
                return false;
            }

            if (horaInicio >= horaFin) {
                alert('La hora de fin debe ser mayor que la hora de inicio');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
