<?php
// ============================================
// vistas/admin/usuarios.php - LISTA COMPLETA
// ============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="AdminController.php?action=dashboard">
                <i class="bi bi-arrow-left-circle"></i> Volver al Dashboard
            </a>
            <span class="navbar-text text-white">Gestión de Personal</span>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people"></i> Lista de Empleados</h2>
            <a href="AdminController.php?action=nuevo_empleado" class="btn btn-success">
                <i class="bi bi-person-plus"></i> Nuevo Empleado
            </a>
        </div>

        <?php $flash = SessionManager::getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['tipo'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($flash['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Rol Base</th>
                                <th>Email</th>
                                <th>Asignaciones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No hay empleados registrados</td></tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($u['nombreEmpleado']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $u['rol_base'] === 'ADMIN' ? 'danger' : 
                                                ($u['rol_base'] === 'SUPERVISOR' ? 'info' : 'secondary'); 
                                        ?>">
                                            <?php echo htmlspecialchars($u['rol_base']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email'] ?: 'N/A'); ?></td>
                                    <td>
                                        <?php if ($u['jaulas_asignadas']): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-house-door"></i> Jaulas: <?php echo htmlspecialchars($u['jaulas_asignadas']); ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if ($u['caminos_asignados']): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-signpost-2"></i> Camino: <?php echo htmlspecialchars($u['caminos_asignados']); ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!$u['jaulas_asignadas'] && !$u['caminos_asignados']): ?>
                                            <small class="text-muted">Sin asignaciones</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($u['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="AdminController.php?action=editar_empleado&id=<?php echo urlencode($u['nombreEmpleado']); ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmarEliminar('<?php echo htmlspecialchars($u['nombreEmpleado']); ?>')" 
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
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
    <script>
        function confirmarEliminar(usuario) {
            if (confirm(`¿Estás seguro de eliminar al empleado "${usuario}"?\n\nEsta acción no se puede deshacer.`)) {
                window.location.href = `AdminController.php?action=eliminar_empleado&id=${encodeURIComponent(usuario)}`;
            }
        }
    </script>
</body>
</html>