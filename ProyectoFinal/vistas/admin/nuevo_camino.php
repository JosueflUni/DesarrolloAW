<?php
// ============================================
// vistas/admin/nuevo_camino.php
// ============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Camino</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="AdminController.php?action=caminos">
                <i class="bi bi-arrow-left-circle"></i> Volver a Caminos
            </a>
        </div>
    </nav>

    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="bi bi-signpost-2"></i> Crear Nuevo Camino</h4>
            </div>
            <div class="card-body">
                <form action="AdminController.php?action=guardar_camino" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Camino *</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Sendero Acuático">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitud (metros) *</label>
                        <input type="number" step="0.01" name="largo" class="form-control" required placeholder="320.50">
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-info text-white">
                            <i class="bi bi-check-circle"></i> Crear Camino
                        </button>
                        <a href="AdminController.php?action=caminos" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>