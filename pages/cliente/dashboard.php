<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

// Verificar que sea cliente
verificarRol(['Cliente']);

$database = new Database();
$db = $database->getConnection();

$cliente_id = $_SESSION['usuario_id'];

// Obtener servicios activos
$query = "SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre ASC";
$stmt = $db->query($query);
$servicios = $stmt->fetchAll();

// Obtener citas del cliente
$query = "SELECT c.*, u.nombre as empleado_nombre, s.nombre as servicio_nombre
          FROM citas c
          JOIN usuarios u ON c.empleado_id = u.id
          JOIN servicios s ON c.servicio_id = s.id
          WHERE c.cliente_id = :cliente_id
          ORDER BY c.fecha_cita DESC, c.hora_cita DESC
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$mis_citas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cliente - Salón de Belleza</title>
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

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .service-card {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
        }

        .service-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .service-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .service-desc {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .service-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .service-price {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
        }

        .service-duration {
            color: #666;
            font-size: 14px;
        }

        .btn-agendar {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-agendar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }

        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>💇‍♀️ Bienvenido al Salón</h1>
            <div class="user-info">
                <span>Hola, <strong><?php echo htmlspecialchars(obtenerNombreUsuario()); ?></strong></span>
                <a href="../../auth/logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['success']) && $_GET['success'] == 'cita_agendada'): ?>
            <div class="alert alert-success">
                ✅ ¡Cita agendada exitosamente! Pronto recibirás la confirmación.
            </div>
        <?php endif; ?>

        <!-- Servicios Disponibles -->
        <div class="section">
            <h2>✨ Nuestros Servicios</h2>
            <div class="services-grid">
                <?php foreach ($servicios as $servicio): ?>
                    <div class="service-card">
                        <div class="service-name"><?php echo htmlspecialchars($servicio['nombre']); ?></div>
                        <div class="service-desc"><?php echo htmlspecialchars($servicio['descripcion']); ?></div>
                        <div class="service-details">
                            <div class="service-price">$<?php echo number_format($servicio['precio'], 2); ?></div>
                            <div class="service-duration">⏱️ <?php echo $servicio['duracion']; ?> min</div>
                        </div>
                        <button class="btn-agendar" onclick="agendarCita(<?php echo $servicio['id']; ?>)">
                            📅 Agendar Cita
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Mis Citas -->
        <div class="section">
            <h2>📋 Mis Citas</h2>
            <?php if (count($mis_citas) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Servicio</th>
                            <th>Empleado</th>
                            <th>Estado</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mis_citas as $cita): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?></td>
                                <td><?php echo date('H:i', strtotime($cita['hora_cita'])); ?></td>
                                <td><?php echo htmlspecialchars($cita['servicio_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['empleado_nombre']); ?></td>
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
                <div class="empty-state">
                    <p style="font-size: 18px;">Aún no tienes citas agendadas</p>
                    <p>¡Agenda tu primera cita y disfruta de nuestros servicios!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function agendarCita(servicioId) {
            window.location.href = 'agendar_cita.php?servicio_id=' + servicioId;
        }
    </script>
</body>
</html>
