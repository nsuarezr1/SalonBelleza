<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

// Verificar que sea empleado
verificarRol(['Empleado']);

$database = new Database();
$db = $database->getConnection();

$empleado_id = $_SESSION['usuario_id'];
$hoy = date('Y-m-d');

// Citas del día
$query = "SELECT c.*, u.nombre as cliente_nombre, s.nombre as servicio_nombre
          FROM citas c
          JOIN usuarios u ON c.cliente_id = u.id
          JOIN servicios s ON c.servicio_id = s.id
          WHERE c.empleado_id = :empleado_id AND c.fecha_cita = :hoy
          ORDER BY c.hora_cita ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->bindParam(':hoy', $hoy);
$stmt->execute();
$citas_hoy = $stmt->fetchAll();

// Próximas citas
$query = "SELECT c.*, u.nombre as cliente_nombre, s.nombre as servicio_nombre
          FROM citas c
          JOIN usuarios u ON c.cliente_id = u.id
          JOIN servicios s ON c.servicio_id = s.id
          WHERE c.empleado_id = :empleado_id AND c.fecha_cita > :hoy
          ORDER BY c.fecha_cita ASC, c.hora_cita ASC
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(':empleado_id', $empleado_id);
$stmt->bindParam(':hoy', $hoy);
$stmt->execute();
$proximas_citas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Empleado - Salón de Belleza</title>
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

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .citas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .cita-card {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
        }

        .cita-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .cita-hora {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .cita-info {
            margin: 10px 0;
            color: #666;
        }

        .cita-info strong {
            color: #333;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
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

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>💼 Panel de Empleado</h1>
            <div class="user-info">
                <span>Hola, <strong><?php echo htmlspecialchars(obtenerNombreUsuario()); ?></strong></span>
                <a href="../../auth/logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Citas de Hoy -->
        <div class="section">
            <h2>📅 Tus Citas de Hoy (<?php echo date('d/m/Y'); ?>)</h2>
            <?php if (count($citas_hoy) > 0): ?>
                <div class="citas-grid">
                    <?php foreach ($citas_hoy as $cita): ?>
                        <div class="cita-card">
                            <div class="cita-hora"><?php echo date('H:i', strtotime($cita['hora_cita'])); ?></div>
                            <div class="cita-info">
                                <strong>Cliente:</strong> <?php echo htmlspecialchars($cita['cliente_nombre']); ?>
                            </div>
                            <div class="cita-info">
                                <strong>Servicio:</strong> <?php echo htmlspecialchars($cita['servicio_nombre']); ?>
                            </div>
                            <div class="cita-info">
                                <strong>Precio:</strong> $<?php echo number_format($cita['precio_total'], 2); ?>
                            </div>
                            <?php if ($cita['notas']): ?>
                                <div class="cita-info">
                                    <strong>Notas:</strong> <?php echo htmlspecialchars($cita['notas']); ?>
                                </div>
                            <?php endif; ?>
                            <span class="badge badge-<?php echo strtolower($cita['estado']); ?>">
                                <?php echo $cita['estado']; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p style="font-size: 18px;">No tienes citas programadas para hoy</p>
                    <p>¡Disfruta tu día! 🎉</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Próximas Citas -->
        <div class="section">
            <h2>📆 Próximas Citas</h2>
            <?php if (count($proximas_citas) > 0): ?>
                <div class="citas-grid">
                    <?php foreach ($proximas_citas as $cita): ?>
                        <div class="cita-card">
                            <div class="cita-hora">
                                <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?> - 
                                <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                            </div>
                            <div class="cita-info">
                                <strong>Cliente:</strong> <?php echo htmlspecialchars($cita['cliente_nombre']); ?>
                            </div>
                            <div class="cita-info">
                                <strong>Servicio:</strong> <?php echo htmlspecialchars($cita['servicio_nombre']); ?>
                            </div>
                            <div class="cita-info">
                                <strong>Precio:</strong> $<?php echo number_format($cita['precio_total'], 2); ?>
                            </div>
                            <span class="badge badge-<?php echo strtolower($cita['estado']); ?>">
                                <?php echo $cita['estado']; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p style="font-size: 18px;">No tienes citas próximas programadas</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
