<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';
verificarRol(['Administrador']);

$database = new Database();
$db = $database->getConnection();
$id = $_GET['id'] ?? null;

$query = "SELECT * FROM servicios WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $id]);
$servicio = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Servicio</title>
    <link rel="stylesheet" href="../../assets/admin_styles.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>✏️ Editar Servicio</h1>
            <a href="servicios.php" class="btn-back">← Volver</a>
        </div>
    </div>
    <div class="container">
        <div class="form-container">
            <form action="procesar_servicio.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="servicio_id" value="<?php echo $servicio['id']; ?>">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($servicio['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Descripción *</label>
                    <textarea name="descripcion" rows="4" required><?php echo htmlspecialchars($servicio['descripcion']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Duración (min) *</label>
                    <input type="number" name="duracion" value="<?php echo $servicio['duracion']; ?>" min="15" step="15" required>
                </div>
                <div class="form-group">
                    <label>Precio *</label>
                    <input type="number" name="precio" value="<?php echo $servicio['precio']; ?>" step="0.01" required>
                </div>
                <button type="submit" class="btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>
</body>
</html>
