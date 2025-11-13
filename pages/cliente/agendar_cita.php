<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

// Verificar que sea cliente
verificarRol(['Cliente']);

$database = new Database();
$db = $database->getConnection();

$servicio_id = $_GET['servicio_id'] ?? null;

// Obtener información del servicio
$servicio = null;
if ($servicio_id) {
    $query = "SELECT * FROM servicios WHERE id = :id AND activo = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $servicio_id);
    $stmt->execute();
    $servicio = $stmt->fetch();
}

// Obtener empleados activos
$query = "SELECT id, nombre FROM usuarios WHERE rol = 'Empleado' AND activo = 1 ORDER BY nombre";
$stmt = $db->query($query);
$empleados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - Salón de Belleza</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border: 2px solid white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: white;
            color: #667eea;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .service-info {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .service-info h3 {
            color: #0369a1;
            margin-bottom: 10px;
        }

        .service-details {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }

        .service-detail {
            flex: 1;
        }

        .service-detail label {
            color: #666;
            font-size: 12px;
            display: block;
            margin-bottom: 5px;
        }

        .service-detail .value {
            font-size: 20px;
            font-weight: bold;
            color: #0369a1;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .horarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .horario-btn {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s;
        }

        .horario-btn:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .horario-btn.selected {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .horario-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f5f5f5;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        #horarios-container {
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📅 Agendar Nueva Cita</h1>
            <a href="dashboard.php" class="btn-back">← Volver</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                switch($_GET['error']) {
                    case 'required':
                        echo "❌ Por favor completa todos los campos";
                        break;
                    case 'horario_ocupado':
                        echo "❌ El horario seleccionado ya no está disponible";
                        break;
                    case 'invalid_date':
                        echo "❌ La fecha seleccionada no es válida";
                        break;
                    default:
                        echo "❌ Error al agendar la cita";
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if ($servicio): ?>
            <div class="form-container">
                <div class="service-info">
                    <h3>📋 Servicio Seleccionado</h3>
                    <p style="font-size: 18px; margin-top: 10px;"><?php echo htmlspecialchars($servicio['nombre']); ?></p>
                    <p style="color: #666; margin-top: 5px;"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                    
                    <div class="service-details">
                        <div class="service-detail">
                            <label>Duración</label>
                            <div class="value">⏱️ <?php echo $servicio['duracion']; ?> min</div>
                        </div>
                        <div class="service-detail">
                            <label>Precio</label>
                            <div class="value">💰 $<?php echo number_format($servicio['precio'], 2); ?></div>
                        </div>
                    </div>
                </div>

                <form id="form-agendar" action="procesar_agendar_cita.php" method="POST">
                    <input type="hidden" name="servicio_id" value="<?php echo $servicio['id']; ?>">
                    <input type="hidden" name="precio_total" value="<?php echo $servicio['precio']; ?>">
                    <input type="hidden" id="hora_seleccionada" name="hora_cita" value="">

                    <div class="form-group">
                        <label for="empleado_id">Selecciona un Profesional *</label>
                        <select id="empleado_id" name="empleado_id" required onchange="cargarHorarios()">
                            <option value="">-- Selecciona un profesional --</option>
                            <?php foreach ($empleados as $empleado): ?>
                                <option value="<?php echo $empleado['id']; ?>">
                                    <?php echo htmlspecialchars($empleado['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha_cita">Fecha de la Cita *</label>
                        <input 
                            type="date" 
                            id="fecha_cita" 
                            name="fecha_cita" 
                            required
                            min="<?php echo date('Y-m-d'); ?>"
                            onchange="cargarHorarios()"
                        >
                    </div>

                    <div id="horarios-container">
                        <div class="form-group">
                            <label>Horarios Disponibles *</label>
                            <div id="horarios-loading" class="loading">
                                Cargando horarios disponibles...
                            </div>
                            <div id="horarios-grid" class="horarios-grid" style="display: none;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notas">Notas Adicionales (Opcional)</label>
                        <textarea 
                            id="notas" 
                            name="notas" 
                            rows="4"
                            placeholder="Ej: Preferencias especiales, alergias, etc."
                        ></textarea>
                    </div>

                    <button type="submit" class="btn-primary" id="btn-submit" disabled>
                        ✅ Confirmar Cita
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                ❌ Servicio no encontrado o no disponible
            </div>
            <a href="dashboard.php" class="btn-back" style="display: inline-block; margin-top: 20px;">← Volver al inicio</a>
        <?php endif; ?>
    </div>

    <script>
        let horaSeleccionada = null;

        function cargarHorarios() {
            const empleadoId = document.getElementById('empleado_id').value;
            const fechaCita = document.getElementById('fecha_cita').value;
            const servicioId = <?php echo $servicio_id; ?>;
            const duracion = <?php echo $servicio['duracion']; ?>;

            if (!empleadoId || !fechaCita) {
                document.getElementById('horarios-container').style.display = 'none';
                return;
            }

            document.getElementById('horarios-container').style.display = 'block';
            document.getElementById('horarios-loading').style.display = 'block';
            document.getElementById('horarios-grid').style.display = 'none';

            // Hacer petición AJAX para obtener horarios disponibles
            fetch(`obtener_horarios_disponibles.php?empleado_id=${empleadoId}&fecha=${fechaCita}&duracion=${duracion}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('horarios-loading').style.display = 'none';
                    
                    if (data.success && data.horarios.length > 0) {
                        mostrarHorarios(data.horarios);
                    } else {
                        document.getElementById('horarios-grid').innerHTML = 
                            '<p style="grid-column: 1/-1; text-align: center; color: #999;">No hay horarios disponibles para esta fecha</p>';
                        document.getElementById('horarios-grid').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('horarios-loading').style.display = 'none';
                    document.getElementById('horarios-grid').innerHTML = 
                        '<p style="grid-column: 1/-1; text-align: center; color: #c33;">Error al cargar horarios</p>';
                    document.getElementById('horarios-grid').style.display = 'block';
                });
        }

        function mostrarHorarios(horarios) {
            const grid = document.getElementById('horarios-grid');
            grid.innerHTML = '';
            
            horarios.forEach(hora => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'horario-btn';
                btn.textContent = hora;
                btn.onclick = () => seleccionarHora(hora, btn);
                grid.appendChild(btn);
            });

            grid.style.display = 'grid';
        }

        function seleccionarHora(hora, btn) {
            // Remover selección anterior
            document.querySelectorAll('.horario-btn').forEach(b => {
                b.classList.remove('selected');
            });

            // Seleccionar nueva hora
            btn.classList.add('selected');
            horaSeleccionada = hora;
            document.getElementById('hora_seleccionada').value = hora;
            document.getElementById('btn-submit').disabled = false;
        }

        // Validación del formulario
        document.getElementById('form-agendar').addEventListener('submit', function(e) {
            if (!horaSeleccionada) {
                e.preventDefault();
                alert('Por favor selecciona un horario');
                return false;
            }
        });
    </script>
</body>
</html>
