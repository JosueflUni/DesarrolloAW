<?php require_once __DIR__ . '/../../config/session.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Jaula</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-warning"><h4>Editar Jaula #<?php echo $jaula['numJaula']; ?></h4></div>
            <div class="card-body">
                <form action="AdminController.php?action=actualizar_jaula" method="POST">
                    <input type="hidden" name="numJaula" value="<?php echo $jaula['numJaula']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($jaula['nombre_jaula']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tamaño (m²)</label>
                        <input type="number" step="0.01" name="tamano" class="form-control" value="<?php echo $jaula['tamano']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Camino</label>
                        <select name="numCamino" class="form-select" required>
                            <?php foreach ($caminos as $c): ?>
                                <option value="<?php echo $c['numCamino']; ?>" <?php echo $jaula['numCamino'] == $c['numCamino'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                    <a href="AdminController.php?action=jaulas" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>