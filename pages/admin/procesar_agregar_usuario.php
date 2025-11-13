<?php
session_start();
require_once '../../config/database.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: ../../index.php");
    exit();
}

// Verificar que sea POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: agregar_usuario.php?error=invalid");
    exit();
}

// Obtener y limpiar datos
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? '';

// Validar campos requeridos
if (empty($nombre) || empty($email) || empty($password) || empty($rol)) {
    header("Location: agregar_usuario.php?error=required&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
    exit();
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    header("Location: agregar_usuario.php?error=password&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
    exit();
}

// Validar rol
$roles_validos = ['Cliente', 'Empleado', 'Administrador'];
if (!in_array($rol, $roles_validos)) {
    header("Location: agregar_usuario.php?error=invalid_role&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
    exit();
}

try {
    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Verificar si el email ya existe
    $query = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        header("Location: agregar_usuario.php?error=email_exists&nombre=" . urlencode($nombre) . "&telefono=" . urlencode($telefono));
        exit();
    }

    // Hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $query = "INSERT INTO usuarios (nombre, email, password, telefono, rol, activo) 
              VALUES (:nombre, :email, :password, :telefono, :rol, 1)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password_hash);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':rol', $rol);

    if ($stmt->execute()) {
        // Éxito
        header("Location: usuarios.php?success=1");
        exit();
    } else {
        // Error al insertar
        header("Location: agregar_usuario.php?error=database&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
        exit();
    }

} catch(PDOException $e) {
    error_log("Error al agregar usuario: " . $e->getMessage());
    header("Location: agregar_usuario.php?error=database&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
    exit();
}
?>
