<?php
// vistas/supervisor/dashboard.php

// 1. Carga de configuración y modelos
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/Supervisor.php';

// 2. Protección de sesión
requireAuth(['SUPERVISOR']);

$supervisorModel = new Supervisor();
$nombreEmpleado = SessionManager::getNombreEmpleado();
$nombreCompleto = SessionManager::getSessionInfo()['nombre_completo'];

// 3. Lógica de Selección de Camino (Multicamino)
// Si el controlador no pasó la variable $misCaminos, la buscamos aquí.
if (!isset($misCaminos)) {
    // Nota: Asegúrate de haber renombrado getMiCamino a getMisCaminos en el modelo como indicamos antes.
    // Si no lo hiciste, usa getMiCamino() pero solo verás el primero.
    $misCaminos = method_exists($supervisorModel, 'getMisCaminos') 
        ? $supervisorModel->getMisCaminos($nombreEmpleado) 
        : [$supervisorModel->getMiCamino($nombreEmpleado)];
}

// Determinar cuál camino mostrar (Por ID en URL o el primero por defecto)
$caminoSeleccionadoId = $_GET['camino_id'] ?? ($misCaminos[0]['numCamino'] ?? null);
$miCamino = null;

if ($misCaminos) {
    foreach ($misCaminos as $c) {
        if ($c['numCamino'] == $caminoSeleccionadoId) {
            $miCamino = $c;
            break;
        }
    }
    // Si el ID de la URL no es válido, usar el primero
    if (!$miCamino && !empty($misCaminos)) {
        $miCamino = $misCaminos[0];
    }
}

// 4. Obtener resto de datos (Listas globales del supervisor)
$jaulasCamino = $supervisorModel->getJaulasCamino($nombreEmpleado);
$personalCamino = $supervisorModel->getPersonalCamino($nombreEmpleado);
$estadisticas = $supervisorModel->getEstadisticasCamino($nombreEmpleado);
$alertasMedicas = $supervisorModel->getAlertasMedicas($nombreEmpleado);
$distribucionEspecies = $supervisorModel->getDistribucionEspecies($nombreEmpleado);
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
        .navbar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
        .section-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .stat-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; }
        .jaula-item { border: 2px solid #e0e0e0; border-radius: 12px; padding: 20px; transition: all 0.3s; position: relative; }
        .jaula-item:hover { border-color: var(--primary-color); transform: translateY(-3px); }
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
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow-sm">
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
                <div class="d-flex justify-content-end mb-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-signpost-split"></i> Cambiar Camino
                        </button>
                        <ul class="dropdown-menu">
                            <?php foreach ($misCaminos as $c): ?>
                                <li>
                                    <a class="dropdown-item <?php echo ($c['numCamino'] == $miCamino['numCamino']) ? 'active' : ''; ?>" 
                                       href="?action=dashboard&camino_id=<?php echo $c['numCamino']; ?>">
                                        <?php echo htmlspecialchars($c['nombre_camino']); ?>
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
                    <button class="btn btn-primary btn-lg" onclick="alert('Función de reporte en desarrollo')">
                        <i class="bi bi-file-earmark-pdf"></i> Generar Reporte
                    </button>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">No se encontró información del camino asignado.</div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: #667eea;">
                            <i class="bi bi-house-door"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['total_jaulas'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Total Jaulas</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: #2ecc71;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['jaulas_vacias'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: #3498db;">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['total_guardas'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Guardas</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: #e74c3c;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['animales_criticos'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Alertas Críticas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" id="mainTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#jaulas"><i class="bi bi-house-door"></i> Jaulas</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal"><i class="bi bi-people"></i> Personal</button></li>
            <li class="nav-item">
                <button class="nav-link text-danger" data-bs-toggle="tab" data-bs-target="#alertas">
                    <i class="bi bi-bell"></i> Alertas Médicas 
                    <?php if(!empty($alertasMedicas)): ?><span class="badge bg-danger ms-1"><?php echo count($alertasMedicas); ?></span><?php endif; ?>
                </button>
            </li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#estadisticas"><i class="bi bi-graph-up"></i> Estadísticas</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="jaulas">
                <div class="section-card">
                    <h4 class="mb-4">Estado de Jaulas Asignadas</h4>
                    <?php if (empty($jaulasCamino)): ?>
                        <div class="alert alert-info">No hay jaulas asignadas.</div>
                    <?php else: ?>
                        <div class="jaula-grid">
                            <?php foreach ($jaulasCamino as $jaula): ?>
                                <div class="jaula-item">
                                    <span class="estado-badge <?php echo strtolower($jaula['estado_ocupacion']); ?>">
                                        <?php echo $jaula['estado_ocupacion']; ?>
                                    </span>
                                    <h5 class="mb-2">Jaula #<?php echo $jaula['numJaula']; ?></h5>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($jaula['nombre_jaula'] ?? 'Sin nombre'); ?></p>
                                    <div class="small text-muted mb-2"><i class="bi bi-rulers"></i> <?php echo $jaula['tamano']; ?> m²</div>
                                    <div class="mb-2">
                                        <span class="badge bg-primary"><?php echo $jaula['total_animales'] ?? 0; ?> animales</span>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-person-check"></i> <?php echo htmlspecialchars($jaula['guardas_asignados'] ?? 'Sin asignar'); ?>
                                    </small>
                                    <button class="btn btn-sm btn-outline-primary w-100 mt-3" onclick="verDetalleJaula(<?php echo $jaula['numJaula']; ?>)">Ver Detalle</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="alertas">
                <div class="section-card">
                    <h4 class="mb-4 text-danger"><i class="bi bi-heart-pulse"></i> Alertas Médicas Activas</h4>
                    <?php if (empty($alertasMedicas)): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle"></i> Todo en orden. No hay alertas médicas activas.</div>
                    <?php else: ?>
                        <?php foreach ($alertasMedicas as $alerta): ?>
                            <div class="alerta-item alerta-<?php echo strtolower($alerta['nivel_alerta']); ?>">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="mb-1 text-danger fw-bold">Nivel: <?php echo $alerta['nivel_alerta']; ?></h5>
                                        <p class="mb-1"><strong><?php echo $alerta['total']; ?> animales afectados</strong></p>
                                        <p class="mb-0 small text-muted"><?php echo htmlspecialchars($alerta['detalles']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="personal">
                <div class="section-card">
                    <h4 class="mb-4">Personal a Cargo</h4>
                    <div class="row">
                        <?php foreach ($personalCamino as $guarda): ?>
                            <div class="col-md-6">
                                <div class="personal-card p-3 border rounded mb-3">
                                    <h5><i class="bi bi-person"></i> <?php echo htmlspecialchars($guarda['nombre_completo']); ?></h5>
                                    <p class="text-muted small mb-1">Usuario: <?php echo htmlspecialchars($guarda['nombreEmpleado']); ?></p>
                                    <p class="mb-0"><span class="badge bg-info"><?php echo $guarda['total_jaulas']; ?> jaulas</span></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="estadisticas">
                <div class="section-card">
                    <h4 class="mb-4">Distribución de Especies</h4>
                    <table class="table">
                        <thead><tr><th>Especie</th><th>Cantidad</th><th>Jaulas</th></tr></thead>
                        <tbody>
                            <?php foreach ($distribucionEspecies as $e): ?>
                                <tr>
                                    <td><em><?php echo htmlspecialchars($e['especie']); ?></em></td>
                                    <td><span class="badge bg-primary"><?php echo $e['cantidad']; ?></span></td>
                                    <td><small><?php echo htmlspecialchars($e['jaulas']); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detalleJaulaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Detalle de Jaula</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalJaulaContent">Cargando...</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalleJaula(numJaula) {
            const modal = new bootstrap.Modal(document.getElementById('detalleJaulaModal'));
            modal.show();
            
            fetch(`/dawb/ProyectoFinal/api/supervisor.php?action=detalle_jaula&jaula=${numJaula}`)
                .then(res => res.json())
                .then(data => {
                    if(data.error) { document.getElementById('modalJaulaContent').innerHTML = `<div class="alert alert-danger">${data.error}</div>`; return; }
                    
                    let animalesHtml = '';
                    if(data.animales && data.animales.length > 0) {
                        animalesHtml = '<div class="row mt-3">';
                        data.animales.forEach(a => {
                            animalesHtml += `
                                <div class="col-md-6 mb-2">
                                    <div class="p-2 border rounded">
                                        <strong>${a.nombre_animal}</strong> <small class="text-muted">(${a.nombre_cientifico})</small><br>
                                        <span class="badge bg-secondary">${a.sexo}</span>
                                        ${a.nivel_alerta !== 'SANO' ? `<span class="badge bg-danger">${a.nivel_alerta}</span>` : '<span class="badge bg-success">Sano</span>'}
                                    </div>
                                </div>`;
                        });
                        animalesHtml += '</div>';
                    } else {
                        animalesHtml = '<p class="text-muted mt-3">No hay animales en esta jaula.</p>';
                    }

                    const html = `
                        <div class="row">
                            <div class="col-6">
                                <h6>Datos Generales</h6>
                                <p><strong>Jaula:</strong> #${data.numJaula}<br>
                                <strong>Nombre:</strong> ${data.nombre_jaula}<br>
                                <strong>Tamaño:</strong> ${data.tamano} m²</p>
                            </div>
                            <div class="col-6">
                                <h6>Estado</h6>
                                <p><strong>Ocupación:</strong> ${data.total_animales} animales<br>
                                <strong>Personal:</strong> ${data.guardas_asignados || 'Ninguno'}</p>
                            </div>
                        </div>
                        <hr>
                        <h6>Animales</h6>
                        ${animalesHtml}
                    `;
                    document.getElementById('modalJaulaContent').innerHTML = html;
                })
                .catch(err => document.getElementById('modalJaulaContent').innerHTML = '<p class="text-danger">Error al cargar datos.</p>');
        }
    </script>
</body>
</html>