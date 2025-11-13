<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

verificarRol(['Administrador']);

$database = new Database();
$db = $database->getConnection();

// Obtener clientes
$clientes = $db->query("SELECT id, nombre, email FROM usuarios WHERE rol='Cliente' AND activo=1 ORDER BY nombre")->fetchAll();

// Obtener empleados
$empleados = $db->query("SELECT id, nombre FROM usuarios WHERE rol='Empleado' AND activo=1 ORDER BY nombre")->fetchAll();

// Obtener servicios
$servicios = $db->query("SELECT id, nombre, precio, duracion FROM servicios WHERE activo=1 ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cita - Salón de Belleza</title>
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

        .info-box {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info-box h3 {
            color: #0369a1;
            margin-bottom: 10px;
        }

        #precio-total {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
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
            <h1>➕ Agregar Nueva Cita</h1>
            <a href="citas.php" class="btn-back">← Volver</a>
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
                    case 'horario_ocupado':
                        echo "❌ El horario seleccionado ya está ocupado";
                        break;
                    case 'invalid_date':
                        echo "❌ La fecha seleccionada no es válida";
                        break;
                    default:
                        echo "❌ Error al crear la cita";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="procesar_cita.php" method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="accion" value="crear">

                <div class="form-group">
                    <label for="cliente_id">Cliente *</label>
                    <select id="cliente_id" name="cliente_id" required>
                        <option value="">-- Selecciona un cliente --</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id']; ?>">
                                <?php echo htmlspecialchars($cliente['nombre']) . ' (' . htmlspecialchars($cliente['email']) . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help-text">Si el cliente no existe, primero debes crearlo en Gestionar Usuarios</div>
                </div>

                <div class="form-group">
                    <label for="servicio_id">Servicio *</label>
                    <select id="servicio_id" name="servicio_id" required onchange="actualizarPrecio()">
                        <option value="">-- Selecciona un servicio --</option>
                        <?php foreach ($servicios as $servicio): ?>
                            <option 
                                value="<?php echo $servicio['id']; ?>" 
                                data-precio="<?php echo $servicio['precio']; ?>"
                                data-duracion="<?php echo $servicio['duracion']; ?>"
                            >
                                <?php echo htmlspecialchars($servicio['nombre']); ?> - 
                                $<?php echo number_format($servicio['precio'], 2); ?> 
                                (<?php echo $servicio['duracion']; ?> min)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="empleado_id">Empleado *</label>
                    <select id="empleado_id" name="empleado_id" required>
                        <option value="">-- Selecciona un empleado --</option>
                        <?php foreach ($empleados as $empleado): ?>
                            <option value="<?php echo $empleado['id']; ?>">
                                <?php echo htmlspecialchars($empleado['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_cita">Fecha *</label>
                        <input 
                            type="date" 
                            id="fecha_cita" 
                            name="fecha_cita" 
                            required
                            min="<?php echo date('Y-m-d'); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="hora_cita">Hora *</label>
                        <input 
                            type="time" 
                            id="hora_cita" 
                            name="hora_cita" 
                            required
                            step="900"
                        >
                        <div class="help-text">Formato 24 horas (ej: 14:30)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="estado">Estado de la Cita *</label>
                    <select id="estado" name="estado" required>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Confirmada" selected>Confirmada</option>
                        <option value="Completada">Completada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notas">Notas Adicionales</label>
                    <textarea id="notas" name="notas" rows="3" placeholder="Observaciones, requerimientos especiales, alergias, etc."></textarea>
                </div>

                <div class="info-box">
                    <h3>💰 Precio Total</h3>
                    <div id="precio-total">$0.00</div>
                    <input type="hidden" id="precio_total_hidden" name="precio_total" value="0">
                </div>

                <button type="submit" class="btn-primary">✅ Crear Cita</button>
            </form>
        </div>
    </div>

    <script>
        function actualizarPrecio() {
            const servicioSelect = document.getElementById('servicio_id');
            const selectedOption = servicioSelect.options[servicioSelect.selectedIndex];
            const precio = selectedOption.dataset.precio || 0;
            
            document.getElementById('precio-total').textContent = '$' + parseFloat(precio).toFixed(2);
            document.getElementById('precio_total_hidden').value = precio;
        }

        function validateForm() {
            const fecha = document.getElementById('fecha_cita').value;
            const hora = document.getElementById('hora_cita').value;
            const precio = document.getElementById('precio_total_hidden').value;

            if (!fecha || !hora) {
                alert('Por favor completa la fecha y hora de la cita');
                return false;
            }

            if (parseFloat(precio) <= 0) {
                alert('Por favor selecciona un servicio válido');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
