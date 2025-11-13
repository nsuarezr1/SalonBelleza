<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

verificarRol(['Administrador']);

$database = new Database();
$db = $database->getConnection();

// Obtener todos los empleados activos
$empleados = $db->query("SELECT id, nombre, email FROM usuarios WHERE rol='Empleado' AND activo=1 ORDER BY nombre")->fetchAll();

// Obtener todos los horarios
$query = "SELECT h.*, u.nombre AS empleado_nombre
          FROM horarios_empleados h
          JOIN usuarios u ON h.empleado_id = u.id
          ORDER BY u.nombre, 
          FIELD(h.dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')";

$horarios = $db->query($query)->fetchAll();

// Agrupar horarios por empleado
$horarios_por_empleado = [];
foreach ($horarios as $horario) {
    $horarios_por_empleado[$horario['empleado_id']][] = $horario;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Horarios - Salón de Belleza</title>
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
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .actions-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .empleado-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .empleado-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .empleado-nombre {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .horarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        .horario-card {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s;
        }

        .horario-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .horario-dia {
            font-size: 16px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .horario-horas {
            color: #666;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .badge-activo {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactivo {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-toggle {
            background: #10b981;
            color: white;
        }

        .btn-toggle:hover {
            background: #059669;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .horarios-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🕐 Gestión de Horarios de Empleados</h1>
            <a href="dashboard.php" class="btn-back">← Volver al Dashboard</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                switch($_GET['success']) {
                    case 'horario_creado':
                        echo "✅ Horario creado exitosamente";
                        break;
                    case 'horario_actualizado':
                        echo "✅ Horario actualizado exitosamente";
                        break;
                    case 'horario_desactivado':
                        echo "✅ Horario desactivado exitosamente";
                        break;
                    case 'horario_activado':
                        echo "✅ Horario activado exitosamente";
                        break;
                    default:
                        echo "✅ Operación realizada exitosamente";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="actions-bar">
            <h2 style="color: #333;">Horarios por Empleado</h2>
            <a href="agregar_horario.php" class="btn-primary">➕ Agregar Horario</a>
        </div>

        <?php if (count($empleados) > 0): ?>
            <?php foreach ($empleados as $empleado): ?>
                <div class="empleado-section">
                    <div class="empleado-header">
                        <div>
                            <div class="empleado-nombre">👤 <?php echo htmlspecialchars($empleado['nombre']); ?></div>
                            <div style="color: #666; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($empleado['email']); ?>
                            </div>
                        </div>
                        <a href="agregar_horario.php?empleado_id=<?php echo $empleado['id']; ?>" class="btn-primary" style="padding: 8px 16px; font-size: 14px;">
                            ➕ Agregar Horario
                        </a>
                    </div>

                    <?php if (isset($horarios_por_empleado[$empleado['id']])): ?>
                        <div class="horarios-grid">
                            <?php foreach ($horarios_por_empleado[$empleado['id']] as $horario): ?>
                                <div class="horario-card">
                                    <div class="horario-dia">📅 <?php echo $horario['dia_semana']; ?></div>
                                    <div class="horario-horas">
                                        ⏰ <?php echo substr($horario['hora_inicio'], 0, 5); ?> - 
                                        <?php echo substr($horario['hora_fin'], 0, 5); ?>
                                    </div>
                                    <span class="badge badge-<?php echo $horario['activo'] ? 'activo' : 'inactivo'; ?>">
                                        <?php echo $horario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                    <div style="margin-top: 10px;">
                                        <button class="btn-action btn-edit" onclick="editarHorario(<?php echo $horario['id']; ?>)">
                                            ✏️ Editar
                                        </button>
                                        <?php if ($horario['activo']): ?>
                                            <button class="btn-action btn-delete" onclick="desactivarHorario(<?php echo $horario['id']; ?>)">
                                                🔒 Desactivar
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-toggle" onclick="activarHorario(<?php echo $horario['id']; ?>)">
                                                ✅ Activar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Este empleado no tiene horarios configurados</p>
                            <a href="agregar_horario.php?empleado_id=<?php echo $empleado['id']; ?>" class="btn-primary" style="display: inline-block; margin-top: 15px;">
                                ➕ Agregar Primer Horario
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empleado-section">
                <div class="empty-state">
                    <p style="font-size: 18px;">No hay empleados activos</p>
                    <p>Primero debes crear empleados en Gestionar Usuarios</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function editarHorario(id) {
            window.location.href = 'editar_horario.php?id=' + id;
        }

        function desactivarHorario(id) {
            if (confirm('¿Estás seguro de que deseas desactivar este horario?\n\nEl horario no se eliminará, solo se marcará como inactivo y no se usará para agendar citas.')) {
                window.location.href = 'toggle_horario.php?id=' + id + '&accion=desactivar';
            }
        }

        function activarHorario(id) {
            if (confirm('¿Deseas activar este horario?\n\nEl horario volverá a estar disponible para agendar citas.')) {
                window.location.href = 'toggle_horario.php?id=' + id + '&accion=activar';
            }
        }
    </script>
</body>
</html>
