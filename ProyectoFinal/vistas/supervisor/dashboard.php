<?php
// vistas/supervisor/dashboard.php - VERSIÓN CORREGIDA

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/Supervisor.php';

requireAuth(['SUPERVISOR']);

$supervisorModel = new Supervisor();
$nombreEmpleado = SessionManager::getNombreEmpleado();
$nombreCompleto = SessionManager::getSessionInfo()['nombre_completo'];

// ⭐ CORRECCIÓN: Obtener todos los caminos del supervisor
$misCaminos = $supervisorModel->getMisCaminos($nombreEmpleado);

// Determinar qué camino mostrar
$caminoSeleccionadoId = $_GET['camino_id'] ?? ($misCaminos[0]['numCamino'] ?? null);

$miCamino = null;
if ($misCaminos) {
    foreach ($misCaminos as $c) {
        if ($c['numCamino'] == $caminoSeleccionadoId) {
            $miCamino = $c;
            break;
        }
    }
    if (!$miCamino) {
        $miCamino = $misCaminos[0];
        $caminoSeleccionadoId = $miCamino['numCamino'];
    }
}

// ⭐ CORRECCIÓN CRÍTICA: Pasar el ID del camino correcto a cada método
$jaulasCamino = $supervisorModel->getJaulasCaminoPorId($caminoSeleccionadoId);
$personalCamino = $supervisorModel->getPersonalCaminoPorId($caminoSeleccionadoId);
$estadisticas = $supervisorModel->getEstadisticasCamino($caminoSeleccionadoId);
$alertasMedicas = $supervisorModel->getAlertasMedicas($caminoSeleccionadoId);
$distribucionEspecies = $supervisorModel->getDistribucionEspeciesPorId($caminoSeleccionadoId);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supervisor - Zoológico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .stat-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; }
        .jaula-item { border: 2px solid #e0e0e0; border-radius: 12px; padding: 20px; transition: all 0.3s; position: relative; }
        .jaula-item:hover { border-color: var(--primary-color); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .estado-badge { position: absolute; top: 10px; right: 10px; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .disponible { background-color: var(--success-color); color: white; }
        .ocupada { background-color: var(--warning-color); color: white; }
        .llena { background-color: var(--danger-color); color: white; }
        .alerta-item { padding: 15px; border-left: 4px solid; margin-bottom: 10px; border-radius: 5px; background: #f8f9fa; }
        .alerta-critico { border-left-color: var(--danger-color); }
        .alerta-reciente { border-left-color: var(--warning-color); }
        .jaula-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-diagram-3"></i> Dashboard Supervisor</a>
            <div class="d-flex text-white align-items-center">
                <span class="me-3"><i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($nombreCompleto); ?></span>
                <a href="/dawb/ProyectoFinal/controladores/AuthController.php?action=logout" class="btn btn-outline-light btn-sm">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        
        <?php if ($miCamino): ?>
        <div class="section-card">
            <?php if (count($misCaminos) > 1): ?>
                <div class="d-flex justify-content-end mb-3">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-signpost-split"></i> Cambiar Camino
                        </button>
                        <ul class="dropdown-menu">
                            <?php foreach ($misCaminos as $c): ?>
                                <li>
                                    <a class="dropdown-item <?php echo ($c['numCamino'] == $caminoSeleccionadoId) ? 'active' : ''; ?>" 
                                       href="?camino_id=<?php echo $c['numCamino']; ?>">
                                        <i class="bi bi-signpost-2"></i> <?php echo htmlspecialchars($c['nombre_camino']); ?>
                                        <small class="text-muted d-block">
                                            <?php echo $c['total_jaulas']; ?> jaulas | <?php echo $c['total_animales']; ?> animales
                                        </small>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 text-primary">
                        <i class="bi bi-signpost-2-fill"></i> 
                        <?php echo htmlspecialchars($miCamino['nombre_camino']); ?>
                    </h2>
                    <p class="text-muted mb-0 fs-5">
                        Camino #<?php echo $miCamino['numCamino']; ?> | 
                        Longitud: <strong><?php echo $miCamino['largo']; ?></strong> metros
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary btn-lg" onclick="generarReporte()">
                        <i class="bi bi-file-earmark-pdf"></i> Generar Reporte
                    </button>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> No tienes caminos asignados.
            </div>
        <?php endif; ?>

        <!-- ⭐ ESTADÍSTICAS CORREGIDAS -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-house-door"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['total_jaulas']; ?></h3>
                            <p class="text-muted mb-0">Total Jaulas</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['jaulas_vacias']; ?></h3>
                            <p class="text-muted mb-0">Disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['total_guardas']; ?></h3>
                            <p class="text-muted mb-0">Guardas</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['animales_criticos']; ?></h3>
                            <p class="text-muted mb-0">Alertas Críticas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" id="mainTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#jaulas">
                    <i class="bi bi-house-door"></i> Jaulas
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal">
                    <i class="bi bi-people"></i> Personal
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?php echo !empty($alertasMedicas) ? 'text-danger' : ''; ?>" data-bs-toggle="tab" data-bs-target="#alertas">
                    <i class="bi bi-bell"></i> Alertas Médicas 
                    <?php if(!empty($alertasMedicas)): ?>
                        <span class="badge bg-danger ms-1"><?php echo array_sum(array_column($alertasMedicas, 'total')); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#estadisticas">
                    <i class="bi bi-graph-up"></i> Estadísticas
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB JAULAS -->
            <div class="tab-pane fade show active" id="jaulas">
                <div class="section-card">
                    <h4 class="mb-4">
                        <i class="bi bi-house-door-fill"></i> Jaulas del Camino
                        <span class="badge bg-primary ms-2"><?php echo count($jaulasCamino); ?></span>
                    </h4>
                    <?php if (empty($jaulasCamino)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Este camino no tiene jaulas asignadas.
                        </div>
                    <?php else: ?>
                        <div class="jaula-grid">
                            <?php foreach ($jaulasCamino as $jaula): ?>
                                <div class="jaula-item">
                                    <span class="estado-badge <?php echo strtolower($jaula['estado_ocupacion']); ?>">
                                        <?php echo $jaula['estado_ocupacion']; ?>
                                    </span>
                                    <h5 class="mb-2">
                                        <i class="bi bi-house-door"></i> Jaula #<?php echo $jaula['numJaula']; ?>
                                    </h5>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($jaula['nombre_jaula'] ?? 'Sin nombre'); ?></p>
                                    <div class="small text-muted mb-2">
                                        <i class="bi bi-rulers"></i> <?php echo $jaula['tamano']; ?> m²
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge bg-primary">
                                            <?php echo $jaula['total_animales'] ?? 0; ?> animales
                                        </span>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-person-check"></i> 
                                        <?php echo htmlspecialchars($jaula['guardas_asignados'] ?? 'Sin asignar'); ?>
                                    </small>
                                    <button class="btn btn-sm btn-outline-primary w-100 mt-3" onclick="verDetalleJaula(<?php echo $jaula['numJaula']; ?>)">
                                        <i class="bi bi-eye"></i> Ver Detalle
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB ALERTAS -->
            <div class="tab-pane fade" id="alertas">
                <div class="section-card">
                    <h4 class="mb-4 text-danger">
                        <i class="bi bi-heart-pulse"></i> Alertas Médicas Activas
                    </h4>
                    <?php if (empty($alertasMedicas)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> Todo en orden. No hay alertas médicas activas.
                        </div>
                    <?php else: ?>
                        <?php foreach ($alertasMedicas as $alerta): ?>
                            <div class="alerta-item alerta-<?php echo strtolower($alerta['nivel_alerta']); ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1 text-<?php echo $alerta['nivel_alerta'] === 'CRITICO' ? 'danger' : 'warning'; ?> fw-bold">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Nivel: <?php echo $alerta['nivel_alerta']; ?>
                                        </h5>
                                        <p class="mb-1">
                                            <strong><?php echo $alerta['total']; ?> animales afectados</strong>
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            <?php echo htmlspecialchars($alerta['detalles']); ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-<?php echo $alerta['nivel_alerta'] === 'CRITICO' ? 'danger' : 'warning'; ?> fs-5">
                                        <?php echo $alerta['total']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB PERSONAL -->
            <div class="tab-pane fade" id="personal">
                <div class="section-card">
                    <h4 class="mb-4">
                        <i class="bi bi-people-fill"></i> Personal a Cargo
                        <span class="badge bg-primary ms-2"><?php echo count($personalCamino); ?></span>
                    </h4>
                    <?php if (empty($personalCamino)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> No hay guardas asignados a este camino.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($personalCamino as $guarda): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="personal-card p-3 border rounded shadow-sm">
                                        <div class="d-flex align-items-start">
                                            <div class="stat-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #3498db, #2980b9);">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1"><?php echo htmlspecialchars($guarda['nombre_completo']); ?></h5>
                                                <p class="text-muted small mb-1">
                                                    <i class="bi bi-at"></i> <?php echo htmlspecialchars($guarda['nombreEmpleado']); ?>
                                                </p>
                                                <p class="mb-0">
                                                    <span class="badge bg-info me-1">
                                                        <?php echo $guarda['total_jaulas']; ?> jaulas
                                                    </span>
                                                    <span class="badge bg-secondary">
                                                        <?php echo $guarda['total_animales_cargo']; ?> animales
                                                    </span>
                                                </p>
                                                <small class="text-muted d-block mt-2">
                                                    <i class="bi bi-house-door"></i> Jaulas: <?php echo htmlspecialchars($guarda['jaulas_asignadas']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB ESTADÍSTICAS -->
            <div class="tab-pane fade" id="estadisticas">
                <div class="section-card">
                    <h4 class="mb-4">
                        <i class="bi bi-bar-chart-fill"></i> Distribución de Especies
                    </h4>
                    <?php if (empty($distribucionEspecies)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No hay datos de especies en este camino.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="bi bi-flower1"></i> Especie</th>
                                        <th class="text-center"><i class="bi bi-hash"></i> Cantidad</th>
                                        <th><i class="bi bi-house-door"></i> Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($distribucionEspecies as $e): ?>
                                        <tr>
                                            <td><em><?php echo htmlspecialchars($e['especie']); ?></em></td>
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6"><?php echo $e['cantidad']; ?></span>
                                            </td>
                                            <td><small class="text-muted"><?php echo htmlspecialchars($e['jaulas']); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Jaula -->
    <div class="modal fade" id="detalleJaulaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-house-door-fill"></i> Detalle de Jaula</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalJaulaContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalleJaula(numJaula) {
            const modal = new bootstrap.Modal(document.getElementById('detalleJaulaModal'));
            modal.show();
            
            document.getElementById('modalJaulaContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            `;
            
            fetch(`/dawb/ProyectoFinal/api/supervisor.php?action=detalle_jaula&jaula=${numJaula}`)
                .then(res => res.json())
                .then(data => {
                    if(data.error) { 
                        document.getElementById('modalJaulaContent').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> ${data.error}
                            </div>
                        `; 
                        return; 
                    }
                    
                    let animalesHtml = '';
                    if(data.animales && data.animales.length > 0) {
                        animalesHtml = '<div class="row mt-3">';
                        data.animales.forEach(a => {
                            const alertaBadge = a.nivel_alerta !== 'SANO' 
                                ? `<span class="badge bg-danger">${a.nivel_alerta}</span>` 
                                : '<span class="badge bg-success">Sano</span>';
                            
                            animalesHtml += `
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded shadow-sm">
                                        <h6 class="mb-1">${a.nombre_animal}</h6>
                                        <p class="text-muted small mb-2"><em>${a.nombre_cientifico}</em></p>
                                        <div>
                                            <span class="badge bg-secondary">${a.sexo}</span>
                                            ${alertaBadge}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        animalesHtml += '</div>';
                    } else {
                        animalesHtml = `
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-inbox"></i> Esta jaula está vacía.
                            </div>
                        `;
                    }

                    const html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-info-circle"></i> Datos Generales</h6>
                                <table class="table table-sm">
                                    <tr><th>Jaula:</th><td>#${data.numJaula}</td></tr>
                                    <tr><th>Nombre:</th><td>${data.nombre_jaula || 'Sin nombre'}</td></tr>
                                    <tr><th>Tamaño:</th><td>${data.tamano} m²</td></tr>
                                    <tr><th>Camino:</th><td>${data.nombre_camino || 'Sin asignar'}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-clipboard-data"></i> Estado</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Ocupación:</th>
                                        <td>
                                            <span class="badge bg-primary">${data.total_animales || 0} animales</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Personal:</th>
                                        <td>${data.guardas_asignados || 'Sin asignar'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <h6><i class="bi bi-list-ul"></i> Animales en esta Jaula</h6>
                        ${animalesHtml}
                    `;
                    document.getElementById('modalJaulaContent').innerHTML = html;
                })
                .catch(err => {
                    console.error('Error:', err);
                    document.getElementById('modalJaulaContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle"></i> Error al cargar datos.
                        </div>
                    `;
                });
        }

        function generarReporte() {
            alert('Funcionalidad de reportes en desarrollo.\n\nEsta función generará un PDF con:\n- Estadísticas del camino\n- Lista de jaulas y animales\n- Alertas médicas activas\n- Personal asignado');
        }
    </script>
</body>
</html>