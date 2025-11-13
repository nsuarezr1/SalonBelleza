<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

verificarRol(['Administrador']);

$database = new Database();
$db = $database->getConnection();

// Filtros
$filtro_estado = $_GET['estado'] ?? '';
$filtro_fecha = $_GET['fecha'] ?? '';
$filtro_empleado = $_GET['empleado'] ?? '';

// Construir query con filtros
$query = "SELECT c.*, 
          u1.nombre as cliente_nombre, 
          u2.nombre as empleado_nombre,
          s.nombre as servicio_nombre
          FROM citas c
          JOIN usuarios u1 ON c.cliente_id = u1.id
          JOIN usuarios u2 ON c.empleado_id = u2.id
          JOIN servicios s ON c.servicio_id = s.id
          WHERE 1=1";

$params = [];

if ($filtro_estado) {
    $query .= " AND c.estado = :estado";
    $params[':estado'] = $filtro_estado;
}

if ($filtro_fecha) {
    $query .= " AND c.fecha_cita = :fecha";
    $params[':fecha'] = $filtro_fecha;
}

if ($filtro_empleado) {
    $query .= " AND c.empleado_id = :empleado";
    $params[':empleado'] = $filtro_empleado;
}

$query .= " ORDER BY c.fecha_cita DESC, c.hora_cita DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$citas = $stmt->fetchAll();

// Obtener empleados para filtro
$empleados = $db->query("SELECT id, nombre FROM usuarios WHERE rol='Empleado' AND activo=1 ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas - Salón de Belleza</title>
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
            flex-wrap: wrap;
            gap: 15px;
        }

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filters select,
        .filters input {
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .citas-table {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f9fafb;
            font-weight: 600;
            color: #666;
            font-size: 13px;
        }

        tr:hover {
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-confirmada {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-completada {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-cancelada {
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
            table {
                font-size: 12px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📅 Gestión de Citas</h1>
            <a href="dashboard.php" class="btn-back">← Volver al Dashboard</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                switch($_GET['success']) {
                    case 'cita_creada':
                        echo "✅ Cita creada exitosamente";
                        break;
                    case 'cita_actualizada':
                        echo "✅ Cita actualizada exitosamente";
                        break;
                    case 'cita_eliminada':
                        echo "✅ Cita eliminada exitosamente";
                        break;
                    default:
                        echo "✅ Operación realizada exitosamente";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="actions-bar">
            <div class="filters">
                <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <select name="estado" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente" <?php echo $filtro_estado == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="Confirmada" <?php echo $filtro_estado == 'Confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="Completada" <?php echo $filtro_estado == 'Completada' ? 'selected' : ''; ?>>Completada</option>
                        <option value="Cancelada" <?php echo $filtro_estado == 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                    </select>

                    <input 
                        type="date" 
                        name="fecha" 
                        value="<?php echo $filtro_fecha; ?>"
                        onchange="this.form.submit()"
                    >

                    <select name="empleado" onchange="this.form.submit()">
                        <option value="">Todos los empleados</option>
                        <?php foreach ($empleados as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>" <?php echo $filtro_empleado == $emp['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ($filtro_estado || $filtro_fecha || $filtro_empleado): ?>
                        <a href="citas.php" class="btn-primary" style="padding: 8px 16px;">🔄 Limpiar filtros</a>
                    <?php endif; ?>
                </form>
            </div>

            <a href="agregar_cita.php" class="btn-primary">➕ Nueva Cita</a>
        </div>

        <div class="citas-table">
            <?php if (count($citas) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Empleado</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas as $cita): ?>
                            <tr>
                                <td>#<?php echo $cita['id']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?></td>
                                <td><?php echo date('H:i', strtotime($cita['hora_cita'])); ?></td>
                                <td><?php echo htmlspecialchars($cita['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['empleado_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['servicio_nombre']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($cita['estado']); ?>">
                                        <?php echo $cita['estado']; ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($cita['precio_total'], 2); ?></td>
                                <td>
                                    <button class="btn-action btn-edit" onclick="editarCita(<?php echo $cita['id']; ?>)">
                                        ✏️ Editar
                                    </button>
                                    <button class="btn-action btn-delete" onclick="eliminarCita(<?php echo $cita['id']; ?>)">
                                        🗑️ Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p style="font-size: 18px;">No se encontraron citas</p>
                    <p>Intenta ajustar los filtros o crear una nueva cita</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function editarCita(id) {
            window.location.href = 'editar_cita.php?id=' + id;
        }

        function eliminarCita(id) {
            if (confirm('¿Estás seguro de que deseas eliminar esta cita?')) {
                window.location.href = 'eliminar_cita.php?id=' + id;
            }
        }
    </script>
</body>
</html>
