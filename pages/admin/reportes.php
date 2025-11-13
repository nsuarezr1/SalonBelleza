<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';
verificarRol(['Administrador']);

$database = new Database();
$db = $database->getConnection();

// Ingresos por mes
$ingresos_mes = $db->query("SELECT DATE_FORMAT(fecha_cita, '%Y-%m') as mes, SUM(precio_total) as total 
    FROM citas WHERE estado IN ('Confirmada','Completada') GROUP BY mes ORDER BY mes DESC LIMIT 12")->fetchAll();

// Servicios más solicitados
$servicios_top = $db->query("SELECT s.nombre, COUNT(c.id) as total FROM servicios s 
    LEFT JOIN citas c ON s.id=c.servicio_id GROUP BY s.id ORDER BY total DESC LIMIT 10")->fetchAll();

// Empleados con más citas
$empleados_top = $db->query("SELECT u.nombre, COUNT(c.id) as total FROM usuarios u 
    LEFT JOIN citas c ON u.id=c.empleado_id WHERE u.rol='Empleado' 
    GROUP BY u.id ORDER BY total DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes e Informes</title>
    <link rel="stylesheet" href="../../assets/admin_styles.css">
    <style>
        .report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
        .report-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .report-card h3 { margin-bottom: 20px; color: #333; }
        .report-item { display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee; }
        .report-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Reportes e Informes</h1>
            <a href="dashboard.php" class="btn-back">← Volver</a>
        </div>
    </div>
    <div class="container">
        <div class="report-grid">
            <div class="report-card">
                <h3>Ingresos por Mes</h3>
                <?php foreach ($ingresos_mes as $i): ?>
                <div class="report-item">
                    <span><?php echo $i['mes']; ?></span>
                    <strong>$<?php echo number_format($i['total'], 2); ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="report-card">
                <h3>Servicios Más Solicitados</h3>
                <?php foreach ($servicios_top as $s): ?>
                <div class="report-item">
                    <span><?php echo htmlspecialchars($s['nombre']); ?></span>
                    <strong><?php echo $s['total']; ?> citas</strong>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="report-card">
                <h3>Empleados con Más Citas</h3>
                <?php foreach ($empleados_top as $e): ?>
                <div class="report-item">
                    <span><?php echo htmlspecialchars($e['nombre']); ?></span>
                    <strong><?php echo $e['total']; ?> citas</strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
