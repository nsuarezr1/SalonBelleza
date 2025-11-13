<?php
include('../../config/database.php');
include('../../includes/verificar_sesion.php');

// Conexión con PDO
$database = new Database();
$conn = $database->getConnection();

// Actualizar estado si se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['estado'])) {
    $update = $conn->prepare("UPDATE citas SET estado = :estado WHERE id = :id");
    $update->execute([
        ':estado' => $_POST['estado'],
        ':id' => $_POST['id']
    ]);
}

// Obtener todas las citas
$query = $conn->prepare("
    SELECT 
        c.id,
        u.nombre AS cliente,
        e.nombre AS empleado,
        s.nombre AS servicio,
        c.fecha_cita,
        c.hora_cita,
        c.estado,
        c.precio_total
    FROM citas c
    JOIN usuarios u ON c.cliente_id = u.id
    JOIN usuarios e ON c.empleado_id = e.id
    JOIN servicios s ON c.servicio_id = s.id
    ORDER BY c.fecha_cita ASC, c.hora_cita ASC
");
$query->execute();
$citas = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
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
        
        </style>
<div class="header">
        <div class="header-content">
            <h1>Gestion Usuarios</h1>
            <a href="citas.php" class="btn-back">← Volver</a>
        </div>
    </div>
<style>

    h2 { text-align: center; color: #333; }
    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
    th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
    th { background: #007bff; color: #fff; }
    tr:hover { background: #f1f1f1; }
    select, button { padding: 5px 10px; border-radius: 5px; border: 1px solid #ccc; }
    button { background: #28a745; color: #fff; cursor: pointer; }
    button:hover { background: #218838; }
    
</style>
</head>
<body>

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
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($citas as $cita): ?>
        <tr>
            <td><?= htmlspecialchars($cita['id']) ?></td>
            <td><?= htmlspecialchars($cita['cliente']) ?></td>
            <td><?= htmlspecialchars($cita['empleado']) ?></td>
            <td><?= htmlspecialchars($cita['servicio']) ?></td>
            <td><?= htmlspecialchars($cita['fecha_cita']) ?></td>
            <td><?= htmlspecialchars($cita['hora_cita']) ?></td>
            <td><?= htmlspecialchars($cita['estado']) ?></td>
            <td>$<?= number_format($cita['precio_total'], 2) ?></td>
            <td>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?= $cita['id'] ?>">
                    <select name="estado">
                        <?php
                        $estados = ['Pendiente', 'Confirmada', 'Completada', 'Cancelada'];
                        foreach ($estados as $estado) {
                            $selected = $cita['estado'] === $estado ? 'selected' : '';
                            echo "<option value='$estado' $selected>$estado</option>";
                        }
                        ?>
                    </select>
                    <button type="submit">Actualizar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
