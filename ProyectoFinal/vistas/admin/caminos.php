<?php
// ============================================
// vistas/admin/caminos.php
// ============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Caminos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="AdminController.php?action=dashboard">
                <i class="bi bi-arrow-left-circle"></i> Volver al Dashboard
            </a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-signpost-split"></i> Gestión de Caminos</h2>
            <a href="AdminController.php?action=nuevo_camino" class="btn btn-info text-white">
                <i class="bi bi-plus-circle"></i> Nuevo Camino
            </a>
        </div>

        <div class="row">
            <?php if (empty($caminos)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No hay caminos registrados. Crea uno nuevo para comenzar.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($caminos as $c): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-signpost-2-fill text-info"></i> 
                                <?php echo htmlspecialchars($c['nombre']); ?>
                            </h5>
                            <p class="text-muted mb-3">Camino #<?php echo $c['numCamino']; ?></p>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-rulers"></i> Longitud:</span>
                                    <strong><?php echo $c['largo']; ?> metros</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-house-door"></i> Jaulas:</span>
                                    <span class="badge bg-primary"><?php echo $c['total_jaulas']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-bezier2"></i> Animales:</span>
                                    <span class="badge bg-success"><?php echo $c['total_animales']; ?></span>
                                </div>
                            </div>

                            <?php if ($c['supervisor']): ?>
                                <div class="alert alert-light py-2 mb-0">
                                    <small>
                                        <i class="bi bi-person-check"></i> Supervisor:<br>
                                        <strong><?php echo htmlspecialchars($c['supervisor']); ?></strong>
                                    </small>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 mb-0">
                                    <small><i class="bi bi-exclamation-triangle"></i> Sin supervisor asignado</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button class="btn btn-sm btn-outline-primary w-100" disabled>
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>