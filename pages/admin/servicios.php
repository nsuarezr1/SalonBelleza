<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

// Verificar que sea administrador
verificarRol(['Administrador']);

$database = new Database();
$db = $database->getConnection();

// Obtener todos los servicios
$query = "SELECT * FROM servicios ORDER BY nombre ASC";
$stmt = $db->query($query);
$servicios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios - Salón de Belleza</title>
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

        .services-table {
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

        .btn-toggle {
            background: #10b981;
            color: white;
        }

        .btn-toggle:hover {
            background: #059669;
        }

        .btn-toggle.inactive {
            background: #ef4444;
        }

        .btn-toggle.inactive:hover {
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

        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }

            th, td {
                padding: 8px;
            }

            .btn-action {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Gestión de Servicios</h1>
            <a href="dashboard.php" class="btn-back">← Volver al Dashboard</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                switch($_GET['success']) {
                    case 'servicio_creado':
                        echo " Servicio creado exitosamente";
                        break;
                    case 'servicio_actualizado':
                        echo " Servicio actualizado exitosamente";
                        break;
                    case 'servicio_activado':
                        echo " Servicio activado exitosamente";
                        break;
                    case 'servicio_desactivado':
                        echo " Servicio desactivado exitosamente";
                        break;
                    default:
                        echo " Operación realizada exitosamente";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="actions-bar">
            <h2 style="color: #333;">Todos los Servicios</h2>
            <a href="agregar_servicio.php" class="btn-primary">+ Agregar Servicio</a>
        </div>

        <div class="services-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Duración</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $servicio): ?>
                        <tr>
                            <td>#<?php echo $servicio['id']; ?></td>
                            <td><?php echo htmlspecialchars($servicio['nombre']); ?></td>
                            <td><?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 50)); ?>...</td>
                            <td> <?php echo $servicio['duracion']; ?> min</td>
                            <td>$<?php echo number_format($servicio['precio'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $servicio['activo'] ? 'activo' : 'inactivo'; ?>">
                                    <?php echo $servicio['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-edit" onclick="editarServicio(<?php echo $servicio['id']; ?>)">
                                    ✏️Editar
                                </button>
                                <button class="btn-action btn-toggle <?php echo $servicio['activo'] ? '' : 'inactive'; ?>" 
                                        onclick="toggleEstado(<?php echo $servicio['id']; ?>, <?php echo $servicio['activo']; ?>)">
                                    <?php echo $servicio['activo'] ? '🔒 Desactivar' : ' Activar'; ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editarServicio(id) {
            window.location.href = 'editar_servicio.php?id=' + id;
        }

        function toggleEstado(id, estadoActual) {
            const accion = estadoActual ? 'desactivar' : 'activar';
            if (confirm(`¿Estás seguro de que quieres ${accion} este servicio?`)) {
                window.location.href = 'toggle_servicio.php?id=' + id;
            }
        }
    </script>
</body>
</html>
