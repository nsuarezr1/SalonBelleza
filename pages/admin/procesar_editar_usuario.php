<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador' || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: usuarios.php");
    exit();
}

$usuario_id = $_POST['usuario_id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$rol = $_POST['rol'] ?? '';
$password = $_POST['password'] ?? '';

// Validar campos requeridos
if (!$usuario_id || !$nombre || !$email || !$rol) {
    header("Location: editar_usuario.php?id=$usuario_id&error=required");
    exit();
}

// Validar rol
$roles_validos = ['Cliente', 'Empleado', 'Administrador'];
if (!in_array($rol, $roles_validos)) {
    header("Location: editar_usuario.php?id=$usuario_id&error=invalid_role");
    exit();
}

// Si hay contraseña nueva, validar longitud
if (!empty($password) && strlen($password) < 6) {
    header("Location: editar_usuario.php?id=$usuario_id&error=password");
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar si el email ya existe (excepto para este usuario)
    $query = "SELECT id FROM usuarios WHERE email = :email AND id != :usuario_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        header("Location: editar_usuario.php?id=$usuario_id&error=email_exists");
        exit();
    }

    // Preparar query de actualización
    if (!empty($password)) {
        // Actualizar con nueva contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE usuarios 
                  SET nombre = :nombre, email = :email, telefono = :telefono, rol = :rol, password = :password
                  WHERE id = :usuario_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $password_hash);
    } else {
        // Actualizar sin cambiar contraseña
        $query = "UPDATE usuarios 
                  SET nombre = :nombre, email = :email, telefono = :telefono, rol = :rol
                  WHERE id = :usuario_id";
        
        $stmt = $db->prepare($query);
    }

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':rol', $rol);
    $stmt->bindParam(':usuario_id', $usuario_id);

    if ($stmt->execute()) {
        header("Location: usuarios.php?success=usuario_actualizado");
        exit();
    } else {
        header("Location: editar_usuario.php?id=$usuario_id&error=database");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error al editar usuario: " . $e->getMessage());
    header("Location: editar_usuario.php?id=$usuario_id&error=database");
    exit();
}
?>
