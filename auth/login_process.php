<?php
session_start();
require_once '../config/database.php';

// Verificar que se recibieron los datos
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../index.php?error=invalid");
    exit();
}

// Obtener y limpiar datos del formulario
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validar que no estén vacíos
if (empty($email) || empty($password)) {
    header("Location: ../index.php?error=required");
    exit();
}

try {
    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Buscar el usuario por email
    $query = "SELECT id, nombre, email, password, rol, activo FROM usuarios WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Verificar si el usuario existe
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar la contraseña
        if (password_verify($password, $usuario['password'])) {
            
            // Verificar si el usuario está activo
            if ($usuario['activo'] != 1) {
                header("Location: ../index.php?error=inactive&email=" . urlencode($email));
                exit();
            }

            // Login exitoso - Crear sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['ultimo_acceso'] = time();

            // Redirigir según el rol
            switch($usuario['rol']) {
                case 'Administrador':
                    header("Location: ../pages/admin/dashboard.php");
                    break;
                case 'Empleado':
                    header("Location: ../pages/empleado/dashboard.php");
                    break;
                case 'Cliente':
                    header("Location: ../pages/cliente/dashboard.php");
                    break;
                default:
                    // Si por alguna razón el rol no coincide, cerrar sesión
                    session_destroy();
                    header("Location: ../index.php?error=invalid");
                    break;
            }
            exit();
            
        } else {
            // Contraseña incorrecta
            header("Location: ../index.php?error=invalid&email=" . urlencode($email));
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: ../index.php?error=invalid");
        exit();
    }

} catch(PDOException $e) {
    // Error en la base de datos
    error_log("Error en login: " . $e->getMessage());
    header("Location: ../index.php?error=database");
    exit();
}
?>
