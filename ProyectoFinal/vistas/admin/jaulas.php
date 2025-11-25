<?php
// ============================================
// vistas/admin/jaulas.php
// ============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Jaulas</title>
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
            <h2><i class="bi bi-house-door"></i> Gestión de Jaulas</h2>
            <a href="AdminController.php?action=nueva_jaula" class="btn btn-warning">
                <i class="bi bi-plus-square"></i> Nueva Jaula
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Jaula</th>
                                <th>Nombre</th>
                                <th>Tamaño</th>
                                <th>Camino</th>
                                <th>Animales</th>
                                <th>Guardas Asignados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jaulas)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No hay jaulas registradas</td></tr>
                            <?php else: ?>
                                <?php foreach ($jaulas as $j): ?>
                                <tr>
                                    <td><strong>#<?php echo $j['numJaula']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($j['nombre'] ?: 'Sin nombre'); ?></td>
                                    <td><?php echo $j['tamano']; ?> m²</td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($j['nombre_camino'] ?: 'Sin camino'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $j['total_animales'] > 0 ? 'success' : 'secondary'; ?>">
                                            <?php echo $j['total_animales']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($j['guardas']): ?>
                                            <small><?php echo htmlspecialchars($j['guardas']); ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">Sin asignar</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" disabled>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>