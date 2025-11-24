<?php
// vistas/admin/dashboard.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/User.php'; // Usamos User ya que no hay AdminModel aún

// 1. Protección de Ruta
requireAuth(['ADMIN']);

$userModel = new User();
$nombreCompleto = SessionManager::getSessionInfo()['nombre_completo'];

// 2. Obtener estadísticas rápidas (Directo de la BD para no depender de un controlador inexistente)
$db = Database::getInstance()->getConnection();

try {
    // Conteo rápido para el dashboard
    $totalAnimales = $db->query("SELECT COUNT(*) FROM Animales")->fetchColumn();
    $totalJaulas = $db->query("SELECT COUNT(*) FROM Jaulas")->fetchColumn();
    $totalUsuarios = $db->query("SELECT COUNT(*) FROM Empleados")->fetchColumn();
    $alertasActivas = $db->query("SELECT COUNT(*) FROM Enfermedades WHERE fechaFin IS NULL")->fetchColumn();
} catch (Exception $e) {
    $totalAnimales = $totalJaulas = $totalUsuarios = $alertasActivas = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Zoológico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --admin-primary: #e74c3c; --admin-secondary: #c0392b; }
        .navbar-admin { background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); }
        .stat-card { border-radius: 15px; border: none; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { font-size: 2.5rem; opacity: 0.8; }
        .admin-link { text-decoration: none; color: inherit; display: block; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin mb-4 shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shield-lock-fill"></i> Administración</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($nombreCompleto); ?></span>
                <a href="/dawb/ProyectoFinal/controladores/AuthController.php?action=logout" class="btn btn-outline-light btn-sm">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container px-4">
        <h2 class="mb-4 text-secondary">Resumen General</h2>
        
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Animales</h6>
                            <h2 class="mb-0"><?php echo $totalAnimales; ?></h2>
                        </div>
                        <i class="bi bi-paw-fill icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Jaulas</h6>
                            <h2 class="mb-0"><?php echo $totalJaulas; ?></h2>
                        </div>
                        <i class="bi bi-grid-3x3-gap-fill icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-dark p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Personal</h6>
                            <h2 class="mb-0"><?php echo $totalUsuarios; ?></h2>
                        </div>
                        <i class="bi bi-people-fill icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-danger text-white p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Alertas Salud</h6>
                            <h2 class="mb-0"><?php echo $alertasActivas; ?></h2>
                        </div>
                        <i class="bi bi-heart-pulse-fill icon-box"></i>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mb-3 text-secondary">Gestión del Sistema</h4>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center">
                        <i class="bi bi-person-badge-fill text-primary display-4 mb-3"></i>
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="text-muted small">Registrar nuevos empleados, asignar roles y gestionar accesos.</p>
                        <a href="/dawb/ProyectoFinal/controladores/AdminController.php?action=usuarios" class="btn btn-outline-primary btn-sm w-100">Administrar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center">
                        <i class="bi bi-geo-alt-fill text-success display-4 mb-3"></i>
                        <h5 class="card-title">Infraestructura</h5>
                        <p class="text-muted small">Configurar zonas, caminos y características de las jaulas.</p>
                        <a href="/dawb/ProyectoFinal/controladores/AdminController.php?action=infraestructura" class="btn btn-outline-success btn-sm w-100">Configurar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-bar-graph-fill text-info display-4 mb-3"></i>
                        <h5 class="card-title">Reportes Globales</h5>
                        <p class="text-muted small">Ver historial médico completo, movimientos y auditoría.</p>
                        <a href="/dawb/ProyectoFinal/controladores/AdminController.php?action=reportes" class="btn btn-outline-info btn-sm w-100">Ver Reportes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>