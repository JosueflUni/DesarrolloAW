<?php
// views/supervisor/dashboard.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/Supervisor.php';

// Proteger la página
requireAuth(['SUPERVISOR']);

$supervisorModel = new Supervisor();
$nombreEmpleado = SessionManager::getNombreEmpleado();
$nombreCompleto = SessionManager::getSessionInfo()['nombre_completo'];

// Obtener datos
$miCamino = $supervisorModel->getMiCamino($nombreEmpleado);
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
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .section-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .jaula-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .jaula-item {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            position: relative;
        }
        .jaula-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        }
        .estado-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .disponible { background-color: var(--success-color); color: white; }
        .ocupada { background-color: var(--warning-color); color: white; }
        .llena { background-color: var(--danger-color); color: white; }
        .personal-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.2s;
        }
        .personal-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .chart-container {
            max-height: 400px;
        }
        .alerta-item {
            padding: 15px;
            border-left: 4px solid;
            margin-bottom: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .alerta-critico { border-left-color: var(--danger-color); }
        .alerta-reciente { border-left-color: var(--warning-color); }
        .tab-content {
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-diagram-3"></i> Dashboard Supervisor
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($nombreCompleto); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/zoologico/controllers/AuthController.php?action=logout">
                            <i class="bi bi-box-arrow-right"></i> Salir
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <!-- Mensajes Flash -->
        <?php
        $flash = SessionManager::getFlash();
        if ($flash):
        ?>
            <div class="alert alert-<?php echo $flash['tipo'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($flash['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Información del Camino -->
        <?php if ($miCamino): ?>
        <div class="section-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">
                        <i class="bi bi-signpost-2"></i> 
                        <?php echo htmlspecialchars($miCamino['nombre_camino']); ?>
                    </h2>
                    <p class="text-muted mb-0">
                        Camino #<?php echo $miCamino['numCamino']; ?> | 
                        Longitud: <?php echo $miCamino['largo']; ?> metros
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reporteModal">
                        <i class="bi bi-file-earmark-pdf"></i> Generar Reporte
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
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
                        <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
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
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
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
                        <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['animales_criticos'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Alertas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="jaulas-tab" data-bs-toggle="tab" data-bs-target="#jaulas" type="button">
                    <i class="bi bi-house-door"></i> Gestión de Jaulas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">
                    <i class="bi bi-people"></i> Personal
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="alertas-tab" data-bs-toggle="tab" data-bs-target="#alertas" type="button">
                    <i class="bi bi-bell"></i> Alertas Médicas
                    <?php if (!empty($alertasMedicas)): ?>
                        <span class="badge bg-danger"><?php echo count($alertasMedicas); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="estadisticas-tab" data-bs-toggle="tab" data-bs-target="#estadisticas-tab-content" type="button">
                    <i class="bi bi-graph-up"></i> Estadísticas
                </button>
            </li>
        </ul>

        <div class="tab-content" id="mainTabsContent">
            <!-- Tab Jaulas -->
            <div class="tab-pane fade show active" id="jaulas" role="tabpanel">
                <div class="section-card">
                    <h4 class="mb-4"><i class="bi bi-house-door-fill"></i> Estado de Jaulas</h4>
                    
                    <?php if (empty($jaulasCamino)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No hay jaulas en este camino.
                        </div>
                    <?php else: ?>
                        <div class="jaula-grid">
                            <?php foreach ($jaulasCamino as $jaula): ?>
                                <div class="jaula-item">
                                    <span class="estado-badge <?php echo strtolower($jaula['estado_ocupacion']); ?>">
                                        <?php echo $jaula['estado_ocupacion']; ?>
                                    </span>
                                    
                                    <h5 class="mb-2">Jaula #<?php echo $jaula['numJaula']; ?></h5>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($jaula['nombre_jaula'] ?? 'Sin nombre'); ?></p>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-rulers"></i> Tamaño: <?php echo $jaula['tamano']; ?> m²
                                        </small>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <span class="badge bg-primary">
                                            <?php echo $jaula['total_animales'] ?? 0; ?> animales
                                        </span>
                                        <span class="badge bg-secondary">
                                            <?php echo $jaula['estado_personal']; ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($jaula['guardas_asignados']): ?>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-person-check"></i> 
                                            <?php echo htmlspecialchars($jaula['guardas_asignados']); ?>
                                        </small>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-sm btn-outline-primary w-100 mt-3" onclick="verDetalleJaula(<?php echo $jaula['numJaula']; ?>)">
                                        <i class="bi bi-eye"></i> Ver Detalle
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Personal -->
            <div class="tab-pane fade" id="personal" role="tabpanel">
                <div class="section-card">
                    <h4 class="mb-4"><i class="bi bi-people-fill"></i> Personal a Cargo</h4>
                    
                    <?php if (empty($personalCamino)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No hay personal asignado a las jaulas de este camino.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($personalCamino as $guarda): ?>
                                <div class="col-md-6">
                                    <div class="personal-card">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1">
                                                    <i class="bi bi-person-circle"></i>
                                                    <?php echo htmlspecialchars($guarda['nombre_completo']); ?>
                                                </h5>
                                                <p class="text-muted small mb-2">
                                                    Usuario: <?php echo htmlspecialchars($guarda['nombreEmpleado']); ?>
                                                </p>
                                                <div>
                                                    <span class="badge bg-primary">
                                                        <?php echo $guarda['total_jaulas']; ?> jaulas
                                                    </span>
                                                    <span class="badge bg-success">
                                                        <?php echo $guarda['total_animales_cargo']; ?> animales
                                                    </span>
                                                </div>
                                                <small class="text-muted d-block mt-2">
                                                    <i class="bi bi-house-door"></i> 
                                                    Jaulas: <?php echo htmlspecialchars($guarda['jaulas_asignadas']); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <button class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-envelope"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Alertas -->
            <div class="tab-pane fade" id="alertas" role="tabpanel">
                <div class="section-card">
                    <h4 class="mb-4"><i class="bi bi-bell-fill"></i> Alertas Médicas Activas</h4>
                    
                    <?php if (empty($alertasMedicas)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> No hay alertas médicas activas en este momento.
                        </div>
                    <?php else: ?>
                        <?php foreach ($alertasMedicas as $alerta): ?>
                            <div class="alerta-item alerta-<?php echo strtolower($alerta['nivel_alerta']); ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Nivel: <?php echo $alerta['nivel_alerta']; ?>
                                        </h5>
                                        <p class="mb-1"><strong><?php echo $alerta['total']; ?> animales afectados</strong></p>
                                        <p class="mb-0 small"><?php echo htmlspecialchars($alerta['detalles']); ?></p>
                                    </div>
                                    <span class="badge bg-<?php echo $alerta['nivel_alerta'] === 'CRITICO' ? 'danger' : 'warning'; ?> fs-6">
                                        <?php echo $alerta['total']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Estadísticas -->
            <div class="tab-pane fade" id="estadisticas-tab-content" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="section-card">
                            <h4 class="mb-4"><i class="bi bi-graph-up"></i> Distribución de Especies</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Especie</th>
                                            <th>Cantidad</th>
                                            <th>Jaulas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($distribucionEspecies as $especie): ?>
                                            <tr>
                                                <td><em><?php echo htmlspecialchars($especie['especie']); ?></em></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $especie['cantidad']; ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo htmlspecialchars($especie['jaulas']); ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="section-card">
                            <h4 class="mb-4"><i class="bi bi-pie-chart"></i> Resumen General</h4>
                            <canvas id="resumenChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Jaula -->
    <div class="modal fade" id="detalleJaulaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-house-door"></i> Detalle de Jaula</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalJaulaContent">
                    <!-- Contenido dinámico -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Ver detalle de jaula
        function verDetalleJaula(numJaula) {
            fetch(`/zoologico/api/supervisor.php?action=detalle_jaula&jaula=${numJaula}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información de la Jaula</h6>
                                <p><strong>Número:</strong> ${data.numJaula}</p>
                                <p><strong>Nombre:</strong> ${data.nombre_jaula || 'Sin nombre'}</p>
                                <p><strong>Tamaño:</strong> ${data.tamano} m²</p>
                                <p><strong>Camino:</strong> ${data.nombre_camino}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Estado</h6>
                                <p><strong>Animales:</strong> ${data.total_animales || 0}</p>
                                <p><strong>Guardas:</strong> ${data.guardas_asignados || 'Sin asignar'}</p>
                            </div>
                        </div>
                    `;

                    if (data.animales && data.animales.length > 0) {
                        html += '<hr><h6>Animales en la Jaula</h6><div class="row">';
                        data.animales.forEach(animal => {
                            html += `
                                <div class="col-md-6 mb-2">
                                    <div class="border rounded p-2">
                                        <strong>${animal.nombre_animal}</strong>
                                        <br><small><em>${animal.nombre_cientifico}</em></small>
                                        <br><span class="badge bg-secondary">${animal.sexo}</span>
                                        ${animal.nivel_alerta !== 'SANO' ? `<span class="badge bg-warning">${animal.nivel_alerta}</span>` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                    }

                    document.getElementById('modalJaulaContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('detalleJaulaModal')).show();
                })
                .catch(error => console.error('Error:', error));
        }

        // Gráfico de resumen
        const ctx = document.getElementById('resumenChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Jaulas Ocupadas', 'Jaulas Disponibles', 'Animales Críticos'],
                datasets: [{
                    data: [
                        <?php echo $estadisticas['jaulas_ocupadas'] ?? 0; ?>,
                        <?php echo $estadisticas['jaulas_vacias'] ?? 0; ?>,
                        <?php echo $estadisticas['animales_criticos'] ?? 0; ?>
                    ],
                    backgroundColor: ['#3498db', '#2ecc71', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
    </script>
</body>
</html>