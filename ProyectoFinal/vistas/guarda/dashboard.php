<?php
// views/guarda/dashboard.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/Guarda.php';

// Proteger la página
requireAuth(['GUARDA']);

$guardaModel = new Guarda();
$nombreEmpleado = SessionManager::getNombreEmpleado();
$nombreCompleto = SessionManager::getSessionInfo()['nombre_completo'];

// Obtener datos
$misJaulas = $guardaModel->getMisJaulas($nombreEmpleado);
$estadisticas = $guardaModel->getEstadisticas($nombreEmpleado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guarda - Zoológico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
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
        .jaula-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .jaula-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .jaula-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .animal-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.2s;
            position: relative;
        }
        .animal-card:hover {
            border-color: var(--primary-color);
            transform: translateX(5px);
        }
        .alerta-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .alerta-critico {
            background-color: var(--danger-color);
            color: white;
            animation: pulse 1.5s infinite;
        }
        .alerta-reciente {
            background-color: var(--warning-color);
            color: white;
        }
        .alerta-historial {
            background-color: #95a5a6;
            color: white;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .btn-search {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        .btn-search:hover {
            background: var(--secondary-color);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-clipboard-check"></i> Dashboard Guarda
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($nombreCompleto); ?>
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

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-house-door"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['total_jaulas'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Mis Jaulas</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
                            <i class="bi bi-bezier2"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['total_animales'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Animales</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['animales_criticos'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Críticos</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?php echo $estadisticas['animales_atencion'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Atención</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buscador Global -->
        <div class="search-box">
            <h5 class="mb-3"><i class="bi bi-search"></i> Buscador Global de Animales</h5>
            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" placeholder="Buscar por nombre, especie o ID...">
                <button class="btn btn-search" type="button" id="searchBtn">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
            <div id="searchResults" class="mt-3"></div>
        </div>

        <!-- Mis Jaulas -->
        <h3 class="mb-4"><i class="bi bi-house-door-fill"></i> Mis Jaulas Asignadas</h3>
        
        <?php if (empty($misJaulas)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No tienes jaulas asignadas actualmente.
            </div>
        <?php else: ?>
            <?php foreach ($misJaulas as $jaula): ?>
                <div class="jaula-card">
                    <div class="jaula-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-1">
                                    <i class="bi bi-house-door"></i> 
                                    Jaula #<?php echo htmlspecialchars($jaula['numJaula']); ?> 
                                    - <?php echo htmlspecialchars($jaula['nombre_jaula'] ?? 'Sin nombre'); ?>
                                </h4>
                                <p class="mb-0">
                                    <i class="bi bi-signpost"></i> <?php echo htmlspecialchars($jaula['nombre_camino'] ?? 'Sin camino'); ?>
                                    | Tamaño: <?php echo htmlspecialchars($jaula['tamano']); ?> m²
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-light text-dark fs-6">
                                    <?php echo $jaula['total_animales'] ?? 0; ?> animales
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div id="animales-jaula-<?php echo $jaula['numJaula']; ?>">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Modal Detalle Animal -->
    <div class="modal fade" id="detalleAnimalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-info-circle"></i> Detalle del Animal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Contenido dinámico -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cargar animales de cada jaula
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($misJaulas as $jaula): ?>
            cargarAnimales(<?php echo $jaula['numJaula']; ?>);
            <?php endforeach; ?>
        });

        function cargarAnimales(numJaula) {
            fetch(`/zoologico/api/guarda.php?action=animales&jaula=${numJaula}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById(`animales-jaula-${numJaula}`);
                    
                    if (data.error) {
                        container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                        return;
                    }

                    if (data.length === 0) {
                        container.innerHTML = '<p class="text-muted"><i class="bi bi-inbox"></i> No hay animales en esta jaula</p>';
                        return;
                    }

                    let html = '';
                    data.forEach(animal => {
                        const alertaClass = animal.nivel_alerta === 'CRITICO' ? 'alerta-critico' :
                                          animal.nivel_alerta === 'RECIENTE' ? 'alerta-reciente' :
                                          animal.nivel_alerta === 'HISTORIAL' ? 'alerta-historial' : '';
                        
                        html += `
                            <div class="animal-card">
                                ${animal.nivel_alerta !== 'SANO' ? `<span class="alerta-badge ${alertaClass}">${animal.nivel_alerta}</span>` : ''}
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-1">${animal.nombre_animal}</h5>
                                        <p class="text-muted mb-1"><em>${animal.nombre_cientifico}</em></p>
                                        <p class="mb-0">
                                            <span class="badge bg-secondary">${animal.sexo}</span>
                                            ${animal.nombre_pais ? `<span class="badge bg-info">${animal.nombre_pais}</span>` : ''}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button class="btn btn-primary btn-sm" onclick="verDetalle('${animal.numIdentif}')">
                                            <i class="bi bi-eye"></i> Ver Detalle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById(`animales-jaula-${numJaula}`).innerHTML = 
                        '<div class="alert alert-danger">Error al cargar animales</div>';
                });
        }

        // Buscar animal
        document.getElementById('searchBtn').addEventListener('click', buscarAnimal);
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') buscarAnimal();
        });

        function buscarAnimal() {
            const termino = document.getElementById('searchInput').value.trim();
            if (termino === '') return;

            fetch(`/zoologico/api/guarda.php?action=buscar&q=${encodeURIComponent(termino)}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('searchResults');
                    
                    if (data.length === 0) {
                        container.innerHTML = '<div class="alert alert-warning">No se encontraron resultados</div>';
                        return;
                    }

                    let html = '<h6 class="mt-3">Resultados:</h6>';
                    data.forEach(animal => {
                        html += `
                            <div class="animal-card">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">${animal.nombre_animal}</h6>
                                        <p class="text-muted mb-0">
                                            <em>${animal.nombre_cientifico}</em> | 
                                            Jaula #${animal.numJaula} (${animal.nombre_jaula})
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button class="btn btn-primary btn-sm" onclick="verDetalle('${animal.numIdentif}')">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        function verDetalle(numIdentif) {
            fetch(`/zoologico/api/guarda.php?action=detalle&id=${numIdentif}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información General</h6>
                                <p><strong>ID:</strong> ${data.numIdentif}</p>
                                <p><strong>Nombre:</strong> ${data.nombre_animal}</p>
                                <p><strong>Especie:</strong> <em>${data.nombre_cientifico}</em></p>
                                <p><strong>Sexo:</strong> ${data.sexo}</p>
                                <p><strong>Fecha de Nacimiento:</strong> ${data.fechaNac || 'Desconocida'}</p>
                                <p><strong>País de Origen:</strong> ${data.pais_origen || 'Desconocido'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Ubicación</h6>
                                <p><strong>Jaula:</strong> #${data.numJaula} - ${data.nombre_jaula || 'Sin nombre'}</p>
                                <p><strong>Estado:</strong> <span class="badge bg-${data.nivel_alerta === 'SANO' ? 'success' : 'warning'}">${data.nivel_alerta}</span></p>
                            </div>
                        </div>
                    `;

                    if (data.historial_medico && data.historial_medico.length > 0) {
                        html += `
                            <hr>
                            <h6>Historial Médico</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Fecha Inicio</th>
                                            <th>Tipo</th>
                                            <th>Estado</th>
                                            <th>Duración</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.historial_medico.forEach(registro => {
                            html += `
                                <tr>
                                    <td>${registro.fechaInicio}</td>
                                    <td>${registro.tipoEnfermedad}</td>
                                    <td><span class="badge bg-${registro.estado === 'ACTIVA' ? 'danger' : 'success'}">${registro.estado}</span></td>
                                    <td>${registro.dias_duracion} días</td>
                                </tr>
                            `;
                        });
                        
                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }

                    document.getElementById('modalContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('detalleAnimalModal')).show();
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>