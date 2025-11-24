<?php
// vistas/auth/select_rol.php

if (!isset($_SESSION['temp_usuario'])) {
    header('Location: /dawb/ProyectoFinal/public/index.php');
    exit;
}

$usuario = $_SESSION['temp_usuario'];
$rolesDisponibles = $roles ?? $usuario['roles_disponibles'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Rol - Sistema Zoológico</title>
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
        .selector-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }
        .selector-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .selector-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .selector-body {
            padding: 40px 30px;
        }
        .rol-card {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        .rol-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        .rol-card input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        .rol-card input[type="radio"]:checked + .rol-content {
            border-left: 5px solid #667eea;
            padding-left: 20px;
        }
        .rol-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 15px;
        }
        .btn-select {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .btn-select:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="selector-container">
        <div class="selector-card">
            <div class="selector-header">
                <i class="bi bi-person-gear" style="font-size: 48px;"></i>
                <h2 class="mt-3 mb-1">Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h2>
                <p class="mb-0">Tienes múltiples roles asignados. Selecciona cómo deseas acceder:</p>
            </div>
            
            <div class="selector-body">
                <form method="POST" action="/dawb/ProyectoFinal/controladores/AuthController.php?action=seleccionar_rol" id="selectorForm">
                    <div class="roles-list">
                        <?php foreach ($rolesDisponibles as $rol): ?>
                            <label class="rol-card">
                                <input type="radio" name="rol" value="<?php echo $rol; ?>" required>
                                <div class="rol-content">
                                    <?php if ($rol === 'GUARDA'): ?>
                                        <div class="rol-icon" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <h4 class="mb-2">Vista de Guarda</h4>
                                        <p class="text-muted mb-0">
                                            <small>Gestión operativa diaria de animales. Acceso a jaulas asignadas, alertas médicas y búsqueda de animales.</small>
                                        </p>
                                        <?php if (isset($usuario['jaulas']) && !empty($usuario['jaulas'])): ?>
                                            <div class="mt-2">
                                                <span class="badge bg-success">
                                                    <?php echo count($usuario['jaulas']); ?> jaulas asignadas
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    
                                    <?php elseif ($rol === 'SUPERVISOR'): ?>
                                        <div class="rol-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                                            <i class="bi bi-diagram-3"></i>
                                        </div>
                                        <h4 class="mb-2">Vista de Supervisor</h4>
                                        <p class="text-muted mb-0">
                                            <small>Gestión táctica de infraestructura y personal. Control de caminos, jaulas, guardas y reportes.</small>
                                        </p>
                                        <?php if (isset($usuario['camino'])): ?>
                                            <div class="mt-2">
                                                <span class="badge bg-primary">
                                                    Camino #<?php echo $usuario['camino']; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    
                                    <?php elseif ($rol === 'ADMIN'): ?>
                                        <div class="rol-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                                            <i class="bi bi-shield-check"></i>
                                        </div>
                                        <h4 class="mb-2">Vista de Administrador</h4>
                                        <p class="text-muted mb-0">
                                            <small>Acceso total al sistema. Gestión de usuarios, configuración y acceso a todas las funcionalidades.</small>
                                        </p>
                                        <div class="mt-2">
                                            <span class="badge bg-danger">Permisos completos</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn btn-select w-100 mt-3">
                        <i class="bi bi-arrow-right-circle"></i> Continuar con el rol seleccionado
                    </button>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <a href="/dawb/ProyectoFinal/controladores/AuthController.php?action=logout" class="btn btn-link text-muted">
                        <i class="bi bi-arrow-left"></i> Volver al login
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 text-white">
            <small>
                <i class="bi bi-info-circle"></i> 
                Puedes cambiar de rol en cualquier momento desde tu dashboard
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Seleccionar automáticamente al hacer clic en toda la tarjeta
        document.querySelectorAll('.rol-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Remover selección visual de otras tarjetas
                document.querySelectorAll('.rol-card').forEach(c => {
                    c.style.borderColor = '#e0e0e0';
                });
                
                // Aplicar selección visual a esta tarjeta
                this.style.borderColor = '#667eea';
            });
        });

        // Validación del formulario
        document.getElementById('selectorForm').addEventListener('submit', function(e) {
            const selected = document.querySelector('input[name="rol"]:checked');
            
            if (!selected) {
                e.preventDefault();
                alert('Por favor selecciona un rol para continuar');
                return false;
            }
        });
    </script>
</body>
</html>