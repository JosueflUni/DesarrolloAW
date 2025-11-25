<?php
// ============================================
// vistas/admin/animales.php
// ============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Animales</title>
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
            <h2><i class="bi bi-paw"></i> Gestión de Animales</h2>
            <a href="AdminController.php?action=nuevo_animal" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Registrar Animal
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Especie</th>
                                <th>Sexo</th>
                                <th>Jaula</th>
                                <th>País</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($animales)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No hay animales registrados</td></tr>
                            <?php else: ?>
                                <?php foreach ($animales as $a): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($a['numIdentif']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($a['nombre']); ?></strong></td>
                                    <td><em><?php echo htmlspecialchars($a['nombre_cientifico']); ?></em></td>
                                    <td>
                                        <span class="badge bg-<?php echo $a['sexo'] === 'M' ? 'primary' : 'danger'; ?>">
                                            <?php echo $a['sexo']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($a['nombre_jaula'] ?? 'Sin jaula'); ?></td>
                                    <td><?php echo htmlspecialchars($a['nombre_pais'] ?? 'Desconocido'); ?></td>
                                    <td>
                                        <?php
                                            $estadoReal = strtoupper($a['estado'] ?? '');
                                            if ($estadoReal === 'SANO'): ?>
                                                <span class="badge bg-success">Sano</span>
                                            <?php elseif ($a['nivel_alerta'] === 'CRITICO' || $estadoReal === 'CRITICO'): ?>
                                                <span class="badge bg-danger">Crítico</span>
                                            <?php elseif ($a['nivel_alerta'] === 'RECIENTE' || $estadoReal === 'ENFERMO' || $estadoReal === 'CUARENTENA'): ?>
                                                <span class="badge bg-warning">Atención</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($estadoReal ?: 'Desconocido'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="AdminController.php?action=editar_animal&id=<?php echo $a['numIdentif']; ?>" 
                                            class="btn btn-sm btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="AdminController.php?action=eliminar_animal&id=<?php echo $a['numIdentif']; ?>" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('¿Eliminar a <?php echo $a['nombre']; ?>? Esta acción no se puede deshacer.');"
                                            title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
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