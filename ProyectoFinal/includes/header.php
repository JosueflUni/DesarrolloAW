<?php
// includes/header.php

if (!defined('HEADER_LOADED')) {
    define('HEADER_LOADED', true);
    
    require_once __DIR__ . '/../config/session.php';
    
    // Obtener información de la sesión
    $sessionInfo = SessionManager::getSessionInfo();
    $nombreCompleto = $sessionInfo['nombre_completo'] ?? 'Usuario';
    $rolActivo = $sessionInfo['rol_activo'] ?? 'INVITADO';
    
    // Determinar el título según la página actual
    $pageTitle = 'Sistema Zoológico';
    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/zoologico/public/css/styles.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: white !important;
        }
        
        .navbar-custom .nav-link:hover {
            color: #f0f0f0 !important;
        }
        
        .dropdown-menu {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .role-badge {
            font-size: 0.75rem;
            padding: 3px 8px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="/zoologico/public/index.php">
                <i class="bi bi-house-heart-fill"></i> Sistema Zoológico
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (SessionManager::isLoggedIn()): ?>
                    <ul class="navbar-nav me-auto">
                        <?php if ($rolActivo === 'GUARDA'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" 
                                   href="/zoologico/views/guarda/dashboard.php">
                                    <i class="bi bi-speedometer2"></i> Mi Dashboard
                                </a>
                            </li>
                        <?php elseif ($rolActivo === 'SUPERVISOR'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" 
                                   href="/zoologico/views/supervisor/dashboard.php">
                                    <i class="bi bi-speedometer2"></i> Mi Dashboard
                                </a>
                            </li>
                        <?php elseif ($rolActivo === 'ADMIN'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/zoologico/views/admin/dashboard.php">
                                    <i class="bi bi-gear"></i> Panel Admin
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle user-info" href="#" id="userDropdown" 
                               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5"></i>
                                <span><?php echo htmlspecialchars($nombreCompleto); ?></span>
                                <span class="badge bg-light text-dark role-badge">
                                    <?php echo htmlspecialchars($rolActivo); ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <h6 class="dropdown-header">
                                        <i class="bi bi-person-badge"></i> 
                                        <?php echo htmlspecialchars($nombreCompleto); ?>
                                    </h6>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi bi-person-gear"></i> Mi Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi bi-key"></i> Cambiar Contraseña
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="/zoologico/controllers/AuthController.php?action=logout">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Mensajes Flash -->
    <?php
    $flash = SessionManager::getFlash();
    if ($flash):
        $alertClass = $flash['tipo'] === 'error' ? 'alert-danger' : 
                     ($flash['tipo'] === 'success' ? 'alert-success' : 
                     ($flash['tipo'] === 'warning' ? 'alert-warning' : 'alert-info'));
        $iconClass = $flash['tipo'] === 'error' ? 'exclamation-triangle' : 
                    ($flash['tipo'] === 'success' ? 'check-circle' : 
                    ($flash['tipo'] === 'warning' ? 'exclamation-circle' : 'info-circle'));
    ?>
        <div class="container-fluid px-4">
            <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $iconClass; ?>"></i>
                <?php echo htmlspecialchars($flash['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenido Principal -->
    <main>
<?php
} // End if HEADER_LOADED
?>