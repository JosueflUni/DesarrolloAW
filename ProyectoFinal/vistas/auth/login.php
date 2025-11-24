<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión Zoológico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header i {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-shield-lock"></i>
                <h2 class="mb-0">Sistema de Gestión</h2>
                <p class="mb-0">Zoológico</p>
            </div>
            
            <div class="login-body">
                <?php
                // Mostrar mensajes flash
                $flash = SessionManager::getFlash();
                if ($flash):
                    $alertClass = $flash['tipo'] === 'error' ? 'alert-danger' : 
                                 ($flash['tipo'] === 'success' ? 'alert-success' : 'alert-info');
                ?>
                    <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?php echo $flash['tipo'] === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                        <?php echo htmlspecialchars($flash['mensaje']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/zoologico/controllers/AuthController.php?action=login" id="loginForm">
                    <div class="mb-3">
                        <label for="nombreEmpleado" class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="nombreEmpleado" 
                                   name="nombreEmpleado" 
                                   placeholder="Ingresa tu nombre de usuario"
                                   required 
                                   autocomplete="username"
                                   autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="contrasena" 
                                   name="contrasena" 
                                   placeholder="Ingresa tu contraseña"
                                   required
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="togglePassword"
                                    title="Mostrar/Ocultar contraseña">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </button>
                </form>

                <hr class="my-4">

                <div class="text-center text-muted small">
                    <p class="mb-0">
                        <i class="bi bi-info-circle"></i> 
                        Usuarios de prueba:
                    </p>
                    <p class="mb-0">
                        <strong>Usuario:</strong> admin | <strong>Contraseña:</strong> zoo2024
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 text-white">
            <small>
                <i class="bi bi-shield-check"></i> 
                Conexión segura | © 2025 Sistema Zoológico
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('contrasena');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });

        // Validación del formulario
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const usuario = document.getElementById('nombreEmpleado').value.trim();
            const contrasena = document.getElementById('contrasena').value;

            if (usuario === '' || contrasena === '') {
                e.preventDefault();
                alert('Por favor completa todos los campos');
                return false;
            }
        });
    </script>
</body>
</html>