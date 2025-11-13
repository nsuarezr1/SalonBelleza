<?php
include('../../config/database.php');
include('../../includes/verificar_sesion.php');

$database = new Database();
$conn = $database->getConnection();

// Agregar o actualizar horario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empleado_id = $_POST['empleado_id'];
    $dia = $_POST['dia_semana'];
    $inicio = $_POST['hora_inicio'];
    $fin = $_POST['hora_fin'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $id = $_POST['id'] ?? null;

    if ($id) {
        $query = $conn->prepare("UPDATE horarios_empleados SET dia_semana=?, hora_inicio=?, hora_fin=?, activo=? WHERE id=?");
        $query->execute([$dia, $inicio, $fin, $activo, $id]);
    } else {
        $query = $conn->prepare("INSERT INTO horarios_empleados (empleado_id, dia_semana, hora_inicio, hora_fin, activo) VALUES (?, ?, ?, ?, ?)");
        $query->execute([$empleado_id, $dia, $inicio, $fin, $activo]);
    }
}

// Desactivar horario
if (isset($_GET['desactivar'])) {
    $id = $_GET['desactivar'];
    $query = $conn->prepare("UPDATE horarios_empleados SET activo=0 WHERE id=?");
    $query->execute([$id]);
}

// Obtener empleados y horarios
$empleados = $conn->query("SELECT id, nombre FROM usuarios WHERE rol='Empleado' AND activo=1")->fetchAll(PDO::FETCH_ASSOC);
$horarios = $conn->query("
    SELECT h.*, u.nombre AS empleado
    FROM horarios_empleados h
    JOIN usuarios u ON h.empleado_id = u.id
    ORDER BY u.nombre, 
        FIELD(dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<div class="header">
        <div class="header-content">
            <h1>Gestión de Usuarios</h1>
            <a href="dashboard.php" class="btn-back">← Volver al Dashboard</a>
        </div>
    </div>
<style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 20px; }
    h2 { text-align: center; color: #333; }
    form, table { background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
    label { font-weight: bold; }
    select, input { margin: 5px; padding: 6px; border-radius: 5px; border: 1px solid #ccc; }
    button { background: #007bff; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
    button:hover { background: #0056b3; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: center; }
    th { background: #007bff; color: #fff; }
    tr:hover { background: #f1f1f1; }
    .inactivo { color: #999; }
</style>
</head>
<body>

<h2>Gestión de Horarios</h2>

<form method="POST">
    <label>Empleado:</label>
    <select name="empleado_id" required>
        <?php foreach ($empleados as $emp): ?>
            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Día:</label>
    <select name="dia_semana" required>
        <option>Lunes</option><option>Martes</option><option>Miércoles</option>
        <option>Jueves</option><option>Viernes</option><option>Sábado</option><option>Domingo</option>
    </select>

    <label>Inicio:</label>
    <input type="time" name="hora_inicio" required>
    <label>Fin:</label>
    <input type="time" name="hora_fin" required>
    <label>Activo:</label>
    <input type="checkbox" name="activo" checked>

    <button type="submit">Guardar</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Empleado</th>
            <th>Día</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($horarios as $h): ?>
        <tr class="<?= $h['activo'] ? '' : 'inactivo' ?>">
            <td><?= $h['id'] ?></td>
            <td><?= htmlspecialchars($h['empleado']) ?></td>
            <td><?= htmlspecialchars($h['dia_semana']) ?></td>
            <td><?= $h['hora_inicio'] ?></td>
            <td><?= $h['hora_fin'] ?></td>
            <td><?= $h['activo'] ? 'Sí' : 'No' ?></td>
            <td>
                <a href="?desactivar=<?= $h['id'] ?>" onclick="return confirm('¿Desactivar este horario?')">Desactivar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
