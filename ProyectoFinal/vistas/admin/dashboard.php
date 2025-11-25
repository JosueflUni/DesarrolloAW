<?php
// vistas/admin/dashboard.php - DASHBOARD ADMINISTRATIVO FUNCIONAL

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth(['ADMIN']);

$nombreCompleto = SessionManager::getSessionInfo()['nombre_completo'];
$db = Database::getInstance()->getConnection();

// Estadísticas globales
try {
    $stats = [
        'empleados' => $db->query("SELECT COUNT(*) FROM Empleados WHERE activo = 1")->fetchColumn(),
        'animales' => $db->query("SELECT COUNT(*) FROM Animales")->fetchColumn(),
        'jaulas' => $db->query("SELECT COUNT(*) FROM Jaulas")->fetchColumn(),
        'caminos' => $db->query("SELECT COUNT(*) FROM Caminos")->fetchColumn(),
        'alertas' => $db->query("SELECT COUNT(*) FROM Enfermedades WHERE fechaFin IS NULL")->fetchColumn(),
        'guardas' => $db->query("SELECT COUNT(DISTINCT nombreEmpleado) FROM Guardas")->fetchColumn(),
        'supervisores' => $db->query("SELECT COUNT(DISTINCT nombreEmpleado) FROM Supervisores")->fetchColumn()
    ];
} catch (Exception $e) {
    $stats = array_fill_keys(['empleados','animales','jaulas','caminos','alertas','guardas','supervisores'], 0);
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
        body { background-color: #f8f9fa; }
        .navbar-admin { background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card { border-radius: 15px; transition: transform 0.3s; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .icon-box { font-size: 2.5rem; opacity: 0.9; }
        .action-card { border-radius: 15px; transition: all 0.3s; cursor: pointer; border: 2px solid transparent; }
        .action-card:hover { border-color: var(--admin-primary); transform: scale(1.02); box-shadow: 0 5px 20px rgba(231, 76, 60, 0.2); }
        .section-title { color: #2c3e50; font-weight: 600; margin: 30px 0 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-shield-lock-fill"></i> Panel de Administración
            </a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($nombreCompleto); ?></span>
                <a href="/dawb/ProyectoFinal/controladores/AuthController.php?action=logout" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        
        <?php $flash = SessionManager::getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['tipo'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($flash['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <h2 class="mb-4"><i class="bi bi-graph-up"></i> Resumen del Sistema</h2>
        
        <!-- Estadísticas Principales -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card bg-primary text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Empleados Activos</h6>
                            <h2 class="mb-0"><?php echo $stats['empleados']; ?></h2>
                        </div>
                        <i class="bi bi-people-fill icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card bg-success text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Animales</h6>
                            <h2 class="mb-0"><?php echo $stats['animales']; ?></h2>
                        </div>
                        <i class="bi bi-paw-fill icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card bg-info text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Infraestructura</h6>
                            <h2 class="mb-0"><?php echo $stats['caminos']; ?> / <?php echo $stats['jaulas']; ?></h2>
                            <small>Caminos / Jaulas</small>
                        </div>
                        <i class="bi bi-building icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card bg-danger text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Alertas Médicas</h6>
                            <h2 class="mb-0"><?php echo $stats['alertas']; ?></h2>
                        </div>
                        <i class="bi bi-heart-pulse-fill icon-box"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Asignado -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <h6><i class="bi bi-clipboard-check"></i> Guardas Activos</h6>
                    <h3 class="text-primary mb-0"><?php echo $stats['guardas']; ?></h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <h6><i class="bi bi-diagram-3"></i> Supervisores Activos</h6>
                    <h3 class="text-info mb-0"><?php echo $stats['supervisores']; ?></h3>
                </div>
            </div>
        </div>

        <!-- Gestión de Personal -->
        <h4 class="section-title"><i class="bi bi-people"></i> Gestión de Personal</h4>
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card action-card p-4 text-center" onclick="location.href='AdminController.php?action=usuarios'">
                    <i class="bi bi-person-lines-fill text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Ver Empleados</h5>
                    <p class="text-muted small mb-0">Lista completa de personal</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card action-card p-4 text-center" onclick="location.href='AdminController.php?action=nuevo_empleado'">
                    <i class="bi bi-person-plus-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Nuevo Empleado</h5>
                    <p class="text-muted small mb-0">Registrar y asignar roles</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card action-card p-4 text-center" onclick="alert('Próximamente: Gestión de horarios')">
                    <i class="bi bi-calendar-check text-info" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Horarios</h5>
                    <p class="text-muted small mb-0">Gestionar turnos</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card action-card p-4 text-center" onclick="alert('Próximamente: Reportes de actividad')">
                    <i class="bi bi-file-earmark-text text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Reportes</h5>
                    <p class="text-muted small mb-0">Actividad del personal</p>
                </div>
            </div>
        </div>

        <!-- Gestión de Animales -->
        <h4 class="section-title"><i class="bi bi-bezier2"></i> Gestión de Animales</h4>
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="card action-card p-4 text-center" onclick="location.href='AdminController.php?action=animales'">
                    <i class="bi bi-list-ul text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Ver Animales</h5>
                    <p class="text-muted small mb-0">Inventario completo</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card action-card p-4 text-center" onclick="location.href='AdminController.php?action=nuevo_animal'">
                    <i class="bi bi-plus-circle-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Registrar Animal</h5>
                    <p class="text-muted small mb-0">Agregar nuevo ejemplar</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card action-card p-4 text-center" onclick="alert('Próximamente: Historial médico completo')">
                    <i class="bi bi-heart-pulse text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Salud</h5>
                    <p class="text-muted small mb-0">Historial médico</p>
                </div>
            </div>
        </div>

        <!-- Infraestructura -->
        <h4 class="section-title"><i class="bi bi-building"></i> Infraestructura</h4>
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card action-card p-4 text-center" onclick="location.href='AdminController.php?action=caminos'">
                    <i class="bi bi-signpost-split-fill text-info" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Caminos</h5>
                    <p class="text-muted small mb-0">Gestionar recorridos</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card action-card p-4 text-center" onclick="location.href='AdminController.php?action=jaulas'">
                    <i class="bi bi-house-door-fill text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Jaulas</h5>
                    <p class="text-muted small mb-0">Administrar espacios</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card action-card p-4 text-center" onclick="location.href='AdminController.php?action=nuevo_camino'">
                    <i class="bi bi-plus-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Nuevo Camino</h5>
                    <p class="text-muted small mb-0">Crear ruta</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card action-card p-4 text-center" onclick="location.href='AdminController.php?action=nueva_jaula'">
                    <i class="bi bi-plus-square text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">Nueva Jaula</h5>
                    <p class="text-muted small mb-0">Agregar espacio</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>