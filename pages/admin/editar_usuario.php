<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

// Verificar que sea administrador
verificarRol(['Administrador']);

$usuario_id = $_GET['id'] ?? null;

if (!$usuario_id) {
    header("Location: usuarios.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener datos del usuario
$query = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $usuario_id);
$stmt->execute();
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: usuarios.php?error=not_found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Salón de Belleza</title>
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

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .toggle-password:hover {
            background-color: #f0f0f0;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .info-box {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info-box p {
            color: #0c4a6e;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Editar Usuario</h1>
            <a href="usuarios.php" class="btn-back">← Volver</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                switch($_GET['error']) {
                    case 'required':
                        echo "Por favor completa todos los campos obligatorios";
                        break;
                    case 'email_exists':
                        echo "Este correo electrónico ya está en uso";
                        break;
                    case 'database':
                        echo "Error al actualizar el usuario";
                        break;
                    default:
                        echo "Error al procesar la solicitud";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <p><strong>Usuario ID:</strong> #<?php echo $usuario['id']; ?></p>
            <p><strong>Registrado:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></p>
        </div>

        <div class="form-container">
            <form action="procesar_editar_usuario.php" method="POST">
                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">

                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        required
                        value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        value="<?php echo htmlspecialchars($usuario['email']); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono"
                        value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="rol">Rol del Usuario *</label>
                    <select id="rol" name="rol" required>
                        <option value="Cliente" <?php echo $usuario['rol'] == 'Cliente' ? 'selected' : ''; ?>>Cliente</option>
                        <option value="Empleado" <?php echo $usuario['rol'] == 'Empleado' ? 'selected' : ''; ?>>Empleado</option>
                        <option value="Administrador" <?php echo $usuario['rol'] == 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Nueva Contraseña (Opcional)</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            minlength="6"
                            placeholder="Dejar en blanco para mantener la actual"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            Mostrar contraseña
                        </button>
                    </div>
                    <div class="help-text">
                        Solo completa este campo si deseas cambiar la contraseña (mínimo 6 caracteres)
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = event.target;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Ocultar contraseña';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Mostrar contraseña';
            }
        }
    </script>
</body>
</html>
