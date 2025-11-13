<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

// Verificar que sea administrador
verificarRol(['Administrador']);

// Obtener estadísticas
$database = new Database();
$db = $database->getConnection();

// Total de usuarios
$query = "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1";
$stmt = $db->query($query);
$total_usuarios = $stmt->fetch()['total'];

// Total de clientes
$query = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'Cliente' AND activo = 1";
$stmt = $db->query($query);
$total_clientes = $stmt->fetch()['total'];

// Total de empleados
$query = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'Empleado' AND activo = 1";
$stmt = $db->query($query);
$total_empleados = $stmt->fetch()['total'];

// Total de servicios
$query = "SELECT COUNT(*) as total FROM servicios WHERE activo = 1";
$stmt = $db->query($query);
$total_servicios = $stmt->fetch()['total'];

// Citas de hoy
$hoy = date('Y-m-d');
$query = "SELECT COUNT(*) as total FROM citas WHERE fecha_cita = :hoy";
$stmt = $db->prepare($query);
$stmt->bindParam(':hoy', $hoy);
$stmt->execute();
$citas_hoy = $stmt->fetch()['total'];

// Citas pendientes
$query = "SELECT COUNT(*) as total FROM citas WHERE estado = 'Pendiente'";
$stmt = $db->query($query);
$citas_pendientes = $stmt->fetch()['total'];

// Ingresos del mes
$mes_actual = date('Y-m');
$query = "SELECT COALESCE(SUM(precio_total), 0) as total 
          FROM citas 
          WHERE DATE_FORMAT(fecha_cita, '%Y-%m') = :mes 
          AND estado IN ('Confirmada', 'Completada')";
$stmt = $db->prepare($query);
$stmt->bindParam(':mes', $mes_actual);
$stmt->execute();
$ingresos_mes = $stmt->fetch()['total'];

// Últimas citas
$query = "SELECT c.*, 
          u1.nombre as cliente_nombre, 
          u2.nombre as empleado_nombre,
          s.nombre as servicio_nombre
          FROM citas c
          JOIN usuarios u1 ON c.cliente_id = u1.id
          JOIN usuarios u2 ON c.empleado_id = u2.id
          JOIN servicios s ON c.servicio_id = s.id
          ORDER BY c.fecha_creacion DESC
          LIMIT 10";
$stmt = $db->query($query);
$ultimas_citas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - Salón de Belleza</title>
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

        /* Header */
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border: 2px solid white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: white;
            color: #667eea;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-card.green .number { color: #10b981; }
        .stat-card.orange .number { color: #f59e0b; }
        .stat-card.purple .number { color: #8b5cf6; }

        /* Menu de navegación */
        .nav-menu {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .nav-menu h2 {
            margin-bottom: 15px;
            color: #333;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .menu-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
        }

        .menu-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Tabla de citas */
        .citas-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .citas-section h2 {
            margin-bottom: 20px;
            color: #333;
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

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1>Panel de Administrador</h1>
            <div class="user-info">
                <span>Hola, <strong><?php echo htmlspecialchars(obtenerNombreUsuario()); ?></strong></span>
                <a href="../../auth/logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <!-- Container Principal -->
    <div class="container">
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Usuarios</h3>
                <div class="number"><?php echo $total_usuarios; ?></div>
            </div>

            <div class="stat-card green">
                <h3>Clientes Activos</h3>
                <div class="number"><?php echo $total_clientes; ?></div>
            </div>

            <div class="stat-card orange">
                <h3>Empleados</h3>
                <div class="number"><?php echo $total_empleados; ?></div>
            </div>

            <div class="stat-card purple">
                <h3>Servicios Disponibles</h3>
                <div class="number"><?php echo $total_servicios; ?></div>
            </div>

            <div class="stat-card">
                <h3>Citas Hoy</h3>
                <div class="number"><?php echo $citas_hoy; ?></div>
            </div>

            <div class="stat-card orange">
                <h3>Citas Pendientes</h3>
                <div class="number"><?php echo $citas_pendientes; ?></div>
            </div>

            <div class="stat-card green">
                <h3>Ingresos del Mes</h3>
                <div class="number">$<?php echo number_format($ingresos_mes, 2); ?></div>
            </div>
        </div>

        <!-- Menú de Navegación -->
        <div class="nav-menu">
            <h2>Gestión del Sistema</h2>
            <div class="menu-grid">
                <a href="usuarios.php" class="menu-item">
                    Gestionar Usuarios
                </a>
                <a href="servicios.php" class="menu-item">
                     Gestionar Servicios
                </a>
                <a href="citas.php" class="menu-item">
                     Ver Todas las Citas
                </a>
                <a href="horarios.php" class="menu-item">
                     Gestionar Horarios
                </a>
                <a href="reportes.php" class="menu-item">
                     Reportes e Informes
                </a>
            </div>
        </div>

        <!-- Últimas Citas -->
        <div class="citas-section">
            <h2>Últimas Citas Registradas</h2>
            <?php if (count($ultimas_citas) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Empleado</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimas_citas as $cita): ?>
                            <tr>
                                <td>#<?php echo $cita['id']; ?></td>
                                <td><?php echo htmlspecialchars($cita['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['empleado_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['servicio_nombre']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?></td>
                                <td><?php echo date('H:i', strtotime($cita['hora_cita'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($cita['estado']); ?>">
                                        <?php echo $cita['estado']; ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($cita['precio_total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">
                    No hay citas registradas todavía
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
