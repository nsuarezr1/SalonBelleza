<?php
require_once '../../includes/verificar_sesion.php';
require_once '../../config/database.php';

// Verificar que sea administrador
verificarRol(['Administrador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario - Salón de Belleza</title>
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

        .role-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .role-info h3 {
            color: #0369a1;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .role-info ul {
            margin-left: 20px;
            color: #0c4a6e;
        }

        .role-info li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>➕ Agregar Nuevo Usuario</h1>
            <a href="usuarios.php" class="btn-back">← Volver</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                switch($_GET['error']) {
                    case 'required':
                        echo "❌ Por favor completa todos los campos obligatorios";
                        break;
                    case 'email_exists':
                        echo "❌ Este correo electrónico ya está registrado";
                        break;
                    case 'password':
                        echo "❌ La contraseña debe tener al menos 6 caracteres";
                        break;
                    case 'database':
                        echo "❌ Error al crear el usuario. Intenta nuevamente";
                        break;
                    default:
                        echo "❌ Error al crear el usuario";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="role-info">
            <h3>ℹ️ Información sobre Roles</h3>
            <ul>
                <li><strong>Cliente:</strong> Puede ver servicios y agendar citas</li>
                <li><strong>Empleado:</strong> Puede ver sus citas asignadas y gestionar horarios</li>
                <li><strong>Administrador:</strong> Control total del sistema (usuarios, servicios, citas, etc.)</li>
            </ul>
        </div>

        <div class="form-container">
            <form action="procesar_agregar_usuario.php" method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        required
                        placeholder="Nombre completo del usuario"
                        value="<?php echo isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        placeholder="email@ejemplo.com"
                        value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                    >
                    <div class="help-text">Este correo será usado para iniciar sesión</div>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        placeholder="555-1234"
                        value="<?php echo isset($_GET['telefono']) ? htmlspecialchars($_GET['telefono']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="rol">Rol del Usuario *</label>
                    <select id="rol" name="rol" required>
                        <option value="">-- Selecciona un rol --</option>
                        <option value="Cliente">Cliente</option>
                        <option value="Empleado">Empleado</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                    <div class="help-text">Define los permisos que tendrá el usuario en el sistema</div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            minlength="6"
                            placeholder="Mínimo 6 caracteres"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            Mostrar contraseña
                        </button>
                    </div>
                    <div class="help-text">La contraseña debe tener al menos 6 caracteres</div>
                </div>

                <button type="submit" class="btn-primary">
                    ✅ Crear Usuario
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

        function validateForm() {
            const password = document.getElementById('password').value;
            const rol = document.getElementById('rol').value;
            
            if (password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                return false;
            }

            if (rol === '') {
                alert('Por favor selecciona un rol para el usuario');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>
