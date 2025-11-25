<?php require_once __DIR__ . '/../../config/session.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Camino</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-info text-white"><h4>Editar Camino #<?php echo $camino['numCamino']; ?></h4></div>
            <div class="card-body">
                <form action="AdminController.php?action=actualizar_camino" method="POST">
                    <input type="hidden" name="numCamino" value="<?php echo $camino['numCamino']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($camino['nombre']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitud (metros)</label>
                        <input type="number" step="0.01" name="largo" class="form-control" value="<?php echo $camino['largo']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-info text-white">Actualizar</button>
                    <a href="AdminController.php?action=caminos" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>