<?php
// ============================================
// vistas/admin/nueva_jaula.php
// ============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Jaula</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="AdminController.php?action=jaulas">
                <i class="bi bi-arrow-left-circle"></i> Volver a Jaulas
            </a>
        </div>
    </nav>

    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-house-door"></i> Crear Nueva Jaula</h4>
            </div>
            <div class="card-body">
                <form action="AdminController.php?action=guardar_jaula" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Jaula</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tamaño (m²) *</label>
                        <input type="number" step="0.01" name="tamano" class="form-control" required placeholder="50.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Camino Asignado *</label>
                        <select name="numCamino" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($caminos as $c): ?>
                                <option value="<?php echo $c['numCamino']; ?>">
                                    Camino #<?php echo $c['numCamino']; ?> - <?php echo htmlspecialchars($c['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Crear Jaula
                        </button>
                        <a href="AdminController.php?action=jaulas" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>