<?php
session_start();
require_once '../config/database.php';

// Verificar que sea POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../register.php?error=invalid");
    exit();
}

// Obtener y limpiar datos
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$password = $_POST['password'] ?? '';

// Validar campos requeridos
if (empty($nombre) || empty($email) || empty($password)) {
    header("Location: ../register.php?error=required&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
    exit();
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    header("Location: ../register.php?error=password&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
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
        header("Location: ../register.php?error=email_exists&nombre=" . urlencode($nombre) . "&telefono=" . urlencode($telefono));
        exit();
    }

    // Hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar nuevo usuario (siempre como Cliente)
    $query = "INSERT INTO usuarios (nombre, email, password, telefono, rol, activo) 
              VALUES (:nombre, :email, :password, :telefono, 'Cliente', 1)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password_hash);
    $stmt->bindParam(':telefono', $telefono);

    if ($stmt->execute()) {
        // Registro exitoso
        header("Location: ../index.php?registered=1");
        exit();
    } else {
        // Error al insertar
        header("Location: ../register.php?error=database&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
        exit();
    }

} catch(PDOException $e) {
    error_log("Error en registro: " . $e->getMessage());
    header("Location: ../register.php?error=database&nombre=" . urlencode($nombre) . "&email=" . urlencode($email) . "&telefono=" . urlencode($telefono));
    exit();
}
?>
