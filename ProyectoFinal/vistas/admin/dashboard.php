<?php
// vistas/admin/dashboard.php - VISUALMENTE CORREGIDO (MISMO TAMAÑO)
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth(['ADMIN']);

$nombreCompleto = SessionManager::getSessionInfo()['nombre_completo'];
$db = Database::getInstance()->getConnection();
$baseController = '/dawb/ProyectoFinal/controladores/AdminController.php';

// Estadísticas
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
        /* CORRECCIÓN VISUAL: h-100 asegura que la tarjeta ocupe toda la altura de la columna */
        .stat-card { border-radius: 15px; transition: transform 0.3s; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .icon-box { font-size: 2.5rem; opacity: 0.8; }
        .action-card { border-radius: 15px; transition: all 0.3s; cursor: pointer; border: 2px solid transparent; height: 100%; }
        .action-card:hover { border-color: var(--admin-primary); transform: scale(1.02); box-shadow: 0 5px 20px rgba(231, 76, 60, 0.2); }
        .section-title { color: #2c3e50; font-weight: 600; margin: 30px 0 20px; }
        .card-body-stat { display: flex; flex-direction: column; justify-content: center; height: 100%; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shield-lock-fill"></i> Panel de Administración</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($nombreCompleto); ?></span>
                <a href="/dawb/ProyectoFinal/controladores/AuthController.php?action=logout" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Salir</a>
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
        
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-primary text-white p-3">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <h6 class="text-uppercase mb-1">Empleados Activos</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['empleados']; ?></h2>
                        </div>
                        <i class="bi bi-people-fill icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-success text-white p-3">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <h6 class="text-uppercase mb-1">Animales</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['animales']; ?></h2>
                        </div>
                        <i class="bi bi-paw-fill icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-info text-white p-3">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <h6 class="text-uppercase mb-1">Infraestructura</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['caminos']; ?> <span class="fs-6 fw-normal">Caminos</span> / <?php echo $stats['jaulas']; ?> <span class="fs-6 fw-normal">Jaulas</span></h2>
                        </div>
                        <i class="bi bi-building icon-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-danger text-white p-3">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <h6 class="text-uppercase mb-1">Alertas Médicas</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['alertas']; ?></h2>
                        </div>
                        <i class="bi bi-heart-pulse-fill icon-box"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card p-4 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-clipboard-check text-primary fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Guardas Activos</h6>
                            <h3 class="mb-0 fw-bold text-dark"><?php echo $stats['guardas']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-diagram-3 text-info fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Supervisores Activos</h6>
                            <h3 class="mb-0 fw-bold text-dark"><?php echo $stats['supervisores']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="section-title"><i class="bi bi-people"></i> Gestión de Personal</h4>
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card action-card p-4 text-center h-100" onclick="location.href='<?php echo $baseController; ?>?action=usuarios'">
                    <div class="card-body-stat">
                        <i class="bi bi-person-lines-fill text-primary mb-3" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold">Ver Empleados</h5>
                        <p class="text-muted small mb-0">Lista completa de personal</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card action-card p-4 text-center h-100" onclick="location.href='<?php echo $baseController; ?>?action=nuevo_empleado'">
                    <div class="card-body-stat">
                        <i class="bi bi-person-plus-fill text-success mb-3" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold">Nuevo Empleado</h5>
                        <p class="text-muted small mb-0">Registrar y asignar roles</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card action-card p-4 text-center h-100" onclick="alert('Próximamente: Gestión de horarios')">
                    <div class="card-body-stat">
                        <i class="bi bi-calendar-check text-info mb-3" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold">Horarios</h5>
                        <p class="text-muted small mb-0">Gestionar turnos</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card action-card p-4 text-center h-100" onclick="alert('Próximamente: Reportes de actividad')">
                    <div class="card-body-stat">
                        <i class="bi bi-file-earmark-text text-warning mb-3" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold">Reportes</h5>
                        <p class="text-muted small mb-0">Actividad del personal</p>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="section-title"><i class="bi bi-bezier2"></i> Gestión Operativa</h4>
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card action-card p-4 text-center h-100" onclick="location.href='<?php echo $baseController; ?>?action=animales'">
                    <div class="card-body-stat">
                        <i class="bi bi-list-ul text-primary mb-3" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold">Ver Animales</h5>
                        <p class="text-muted small mb-0">Inventario completo</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card action-card p-4 text-center h-100" onclick="location.href='<?php echo $baseController; ?>?action=nuevo_animal'">
                    <div class="card-body-stat">
                        <i class="bi bi-plus-circle-fill text-success mb-3" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold">Registrar Animal</h5>
                        <p class="text-muted small mb-0">Nuevo ingreso</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card action-card p-4 text-center h-100" onclick="location.href='<?php echo $baseController; ?>?action=jaulas'">
                    <div class="card-body-stat">
                        <i class="bi bi-house-door-fill text-primary mb-3" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold">Jaulas</h5>
                        <p class="text-muted small mb-0">Administrar espacios</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card action-card p-4 text-center h-100" onclick="location.href='<?php echo $baseController; ?>?action=caminos'">
                    <div class="card-body-stat">
                        <i class="bi bi-signpost-split-fill text-info mb-3" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold">Caminos</h5>
                        <p class="text-muted small mb-0">Gestionar rutas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>