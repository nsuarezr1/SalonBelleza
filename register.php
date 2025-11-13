<?php
session_start();

// Si ya está logueado, redirigir
if (isset($_SESSION['usuario_id'])) {
    switch($_SESSION['rol']) {
        case 'Administrador':
            header("Location: pages/admin/dashboard.php");
            break;
        case 'Empleado':
            header("Location: pages/empleado/dashboard.php");
            break;
        case 'Cliente':
            header("Location: pages/cliente/dashboard.php");
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Salón de Belleza</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
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
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus {
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
            margin-top: 10px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: #764ba2;
            text-decoration: underline;
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

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            padding-left: 5px;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>Aldany Spa</h1>
            <p>Crea tu cuenta</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                switch($_GET['error']) {
                    case 'required':
                        echo "Por favor completa todos los campos obligatorios";
                        break;
                    case 'email_exists':
                        echo "Este correo electrónico ya está registrado";
                        break;
                    case 'password':
                        echo "La contraseña debe tener al menos 6 caracteres";
                        break;
                    case 'database':
                        echo "Error al crear la cuenta. Intenta nuevamente";
                        break;
                    default:
                        echo "Error en el registro";
                }
                ?>
            </div>
        <?php endif; ?>

        <form action="auth/register_process.php" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="nombre">Nombre Completo *</label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    required
                    placeholder="Tu nombre completo"
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
                    placeholder="tu@email.com"
                    value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                >
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
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        Mostrar contraseña
                    </button>
                </div>
                <div class="password-requirements">
                    Mínimo 6 caracteres
                </div>
            </div>

            <button type="submit" class="btn-primary">
                Crear Cuenta
            </button>
        </form>

        <div class="login-link">
            ¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
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
            
            if (password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                return false;
            }
            
            return true;
        }
    </script>
</body>
<script src="//code.jivosite.com/widget/W5KU9pbkZi" async></script>
</html>
