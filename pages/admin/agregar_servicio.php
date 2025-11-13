<?php
require_once '../../includes/verificar_sesion.php';
verificarRol(['Administrador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Servicio</title>
    <link rel="stylesheet" href="../../assets/admin_styles.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>➕ Agregar Nuevo Servicio</h1>
            <a href="servicios.php" class="btn-back">← Volver</a>
        </div>
    </div>
    <div class="container">
        <div class="form-container">
            <form action="procesar_servicio.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="form-group">
                    <label>Nombre del Servicio *</label>
                    <input type="text" name="nombre" required>
                </div>
                <div class="form-group">
                    <label>Descripción *</label>
                    <textarea name="descripcion" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label>Duración (minutos) *</label>
                    <input type="number" name="duracion" min="15" step="15" required>
                </div>
                <div class="form-group">
                    <label>Precio *</label>
                    <input type="number" name="precio" step="0.01" min="0" required>
                </div>
                <button type="submit" class="btn-primary">Crear Servicio</button>
            </form>
        </div>
    </div>
</body>
</html>
